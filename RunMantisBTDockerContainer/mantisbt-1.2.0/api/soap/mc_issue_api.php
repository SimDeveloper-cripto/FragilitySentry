<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2010  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'mc_core.php' );

/**
 * Check if an issue with the given id exists.
 *
 * @param string $p_username  The name of the user trying to access the issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to check.
 * @return boolean  true if there is an issue with the given id, false otherwise.
 */
function mc_issue_exists( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return false;
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {

		// if we return an error here, then we answered the question!
		return false;
	}

	return true;
}

/**
 * Get all details about an issue.
 *
 * @param string $p_username  The name of the user trying to access the issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to retrieve.
 * @return Array that represents an IssueData structure
 */
function mc_issue_get( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	
	$t_lang = mci_get_user_lang( $t_user_id );

	if( !bug_exists( $p_issue_id ) ) {
		return new soap_fault( 'Client', '', 'Issue does not exist.' );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_bug = bug_get( $p_issue_id, true );
	$t_issue_data = array();

	$t_issue_data['id'] = $p_issue_id;
	$t_issue_data['view_state'] = mci_enum_get_array_by_id( $t_bug->view_state, 'view_state', $t_lang );
	$t_issue_data['last_updated'] = timestamp_to_iso8601( $t_bug->last_updated );

	$t_issue_data['project'] = mci_project_as_array_by_id( $t_bug->project_id );
	$t_issue_data['category'] = mci_get_category( $t_bug->category_id );
	$t_issue_data['priority'] = mci_enum_get_array_by_id( $t_bug->priority, 'priority', $t_lang );
	$t_issue_data['severity'] = mci_enum_get_array_by_id( $t_bug->severity, 'severity', $t_lang );
	$t_issue_data['status'] = mci_enum_get_array_by_id( $t_bug->status, 'status', $t_lang );

	$t_issue_data['reporter'] = mci_account_get_array_by_id( $t_bug->reporter_id );
	$t_issue_data['summary'] = $t_bug->summary;
	$t_issue_data['version'] = mci_null_if_empty( $t_bug->version );
	$t_issue_data['build'] = mci_null_if_empty( $t_bug->build );
	$t_issue_data['platform'] = mci_null_if_empty( $t_bug->platform );
	$t_issue_data['os'] = mci_null_if_empty( $t_bug->os );
	$t_issue_data['os_build'] = mci_null_if_empty( $t_bug->os_build );
	$t_issue_data['reproducibility'] = mci_enum_get_array_by_id( $t_bug->reproducibility, 'reproducibility', $t_lang );
	$t_issue_data['date_submitted'] = timestamp_to_iso8601( $t_bug->date_submitted );

	$t_issue_data['sponsorship_total'] = $t_bug->sponsorship_total;

	if( !empty( $t_bug->handler_id ) ) {
		$t_issue_data['handler'] = mci_account_get_array_by_id( $t_bug->handler_id );
	}

	$t_issue_data['projection'] = mci_enum_get_array_by_id( $t_bug->projection, 'projection', $t_lang );
	$t_issue_data['eta'] = mci_enum_get_array_by_id( $t_bug->eta, 'eta', $t_lang );

	$t_issue_data['resolution'] = mci_enum_get_array_by_id( $t_bug->resolution, 'resolution', $t_lang );
	$t_issue_data['fixed_in_version'] = mci_null_if_empty( $t_bug->fixed_in_version );
	$t_issue_data['target_version'] = mci_null_if_empty( $t_bug->target_version );
	$t_issue_data['due_date'] = mci_issue_get_due_date( $t_bug );

	$t_issue_data['description'] = $t_bug->description;
	$t_issue_data['steps_to_reproduce'] = mci_null_if_empty( $t_bug->steps_to_reproduce );
	$t_issue_data['additional_information'] = mci_null_if_empty( $t_bug->additional_information );

	$t_issue_data['attachments'] = mci_issue_get_attachments( $p_issue_id );
	$t_issue_data['relationships'] = mci_issue_get_relationships( $p_issue_id, $t_user_id );
	$t_issue_data['notes'] = mci_issue_get_notes( $p_issue_id );
	$t_issue_data['custom_fields'] = mci_issue_get_custom_fields( $p_issue_id );
	
	return $t_issue_data;
}

/**
 * Returns the category name, possibly null if no category is assigned
 * 
 * @param int $p_category_id
 * @return string 
 */
function mci_get_category( $p_category_id ) {
	if ( $p_category_id == 0 )
		return '';
		
	return mci_null_if_empty( category_get_name( $p_category_id ) );
}

/**
 * 
 * @param BugData $bug
 * @return soapval the value to be encoded as the due date
 */
function mci_issue_get_due_date( $p_bug ) {
	if ( access_has_bug_level( config_get( 'due_date_view_threshold' ), $p_bug->id )  && !date_is_null( $p_bug->due_date ) ) {
		return new soapval( 'due_date', 'xsd:dateTime', timestamp_to_iso8601( $p_bug->due_date ) );
	} else {
		return new soapval( 'due_date','xsd:dateTime', null );
	}
	
}

/**
 * Sets the supplied array of custom field values to the specified issue id.
 *
 * @param $p_issue_id   Issue id to apply custom field values to.
 * @param $p_custom_fields  The array of custom field values as described in the webservice complex types.
 * @param boolean $p_log_insert create history logs for new values
 */
function mci_issue_set_custom_fields( $p_issue_id, &$p_custom_fields, $p_log_insert ) {
	# set custom field values on the submitted issue
	if( isset( $p_custom_fields ) && is_array( $p_custom_fields ) ) {
		foreach( $p_custom_fields as $t_custom_field ) {
			# get custom field id from object ref
			$t_custom_field_id = mci_get_custom_field_id_from_objectref( $t_custom_field['field'] );

			if( $t_custom_field_id == 0 ) {
				return new soap_fault( 'Client', '', 'Custom field ' . $t_custom_field['field']['name'] . ' not found.' );
			}

			# skip if current user doesn't have login access.
			if( !custom_field_has_write_access( $t_custom_field_id, $p_issue_id ) ) {
				continue;
			}

			$t_value = $t_custom_field['value'];

			if( !custom_field_validate( $t_custom_field_id, $t_value ) ) {
				return new soap_fault( 'Client', '', 'Invalid custom field value for field id ' . $t_custom_field_id . ' .');
			}

			if( !custom_field_set_value( $t_custom_field_id, $p_issue_id, $t_value, $p_log_insert  ) ) {
				return new soap_fault( 'Server', '', 'Unable to set custom field value for field id ' . $t_custom_field_id . ' to issue ' . $p_issue_id. ' .' );
			}
		}
	}
}

/**
 * Get the custom field values associated with the specified issue id.
 *
 * @param $p_issue_id   Issue id to get the custom field values for.
 *
 * @return null if no custom field defined for the project that contains the issue, or if no custom
 *              fields are accessible to the current user.
 */
function mci_issue_get_custom_fields( $p_issue_id ) {
	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	$t_custom_fields = array();
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );

		if( custom_field_has_read_access( $t_id, $p_issue_id ) ) {

			# user has not access to read this custom field.
			$t_value = custom_field_get_value( $t_id, $p_issue_id );
			if( $t_value === false ) {
				continue;
			}

			$t_custom_field_value = array();
			$t_custom_field_value['field'] = array();
			$t_custom_field_value['field']['id'] = $t_id;
			$t_custom_field_value['field']['name'] = $t_def['name'];
			$t_custom_field_value['value'] = $t_value;

			$t_custom_fields[] = $t_custom_field_value;
		}
	}

	# foreach

	return( count( $t_custom_fields ) == 0 ? null : $t_custom_fields );
}

/**
 * Get the attachments of an issue.
 *
 * @param integer $p_issue_id  The id of the issue to retrieve the attachments for
 * @return Array that represents an AttachmentData structure
 */
function mci_issue_get_attachments( $p_issue_id ) {
	$t_attachment_rows = bug_get_attachments( $p_issue_id );
	
	if ( $t_attachment_rows == null) {
		return array();
	}
	
	$t_result = array();
	foreach( $t_attachment_rows as $t_attachment_row ) {
		$t_attachment = array();
		$t_attachment['id'] = $t_attachment_row['id'];
		$t_attachment['filename'] = $t_attachment_row['filename'];
		$t_attachment['size'] = $t_attachment_row['filesize'];
		$t_attachment['content_type'] = $t_attachment_row['file_type'];
		$t_attachment['date_submitted'] = timestamp_to_iso8601( $t_attachment_row['date_added'] );
		$t_attachment['download_url'] = mci_get_mantis_path() . 'file_download.php?file_id=' . $t_attachment_row['id'] . '&amp;type=bug';
		$t_result[] = $t_attachment;
	}

	return $t_result;
}

/**
 * Get the relationships of an issue.
 *
 * @param integer $p_issue_id  The id of the issue to retrieve the relationships for
 * @return Array that represents an RelationShipData structure
 */
function mci_issue_get_relationships( $p_issue_id, $p_user_id ) {
	$t_relationships = array();

	$t_src_relationships = relationship_get_all_src( $p_issue_id );
	foreach( $t_src_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->dest_bug_id, $p_user_id ) ) {
			$t_relationship = array();
			$t_reltype = array();
			$t_relationship['id'] = $t_relship_row->id;
			$t_reltype['id'] = $t_relship_row->type;
			$t_reltype['name'] = relationship_get_description_src_side( $t_relship_row->type );
			$t_relationship['type'] = $t_reltype;
			$t_relationship['target_id'] = $t_relship_row->dest_bug_id;
			$t_relationships[] = $t_relationship;
		}
	}

	$t_dest_relationships = relationship_get_all_dest( $p_issue_id );
	foreach( $t_dest_relationships as $t_relship_row ) {
		if( access_has_bug_level( config_get( 'mc_readonly_access_level_threshold' ), $t_relship_row->src_bug_id, $p_user_id ) ) {
			$t_relationship = array();
			$t_relationship['id'] = $t_relship_row->id;
			$t_reltype = array();
			$t_reltype['id'] = relationship_get_complementary_type( $t_relship_row->type );
			$t_reltype['name'] = relationship_get_description_dest_side( $t_relship_row->type );
			$t_relationship['type'] = $t_reltype;
			$t_relationship['target_id'] = $t_relship_row->src_bug_id;
			$t_relationships[] = $t_relationship;
		}
	}

	return (count( $t_relationships ) == 0 ? null : $t_relationships );
}

/**
 * Get all visible notes for a specific issue
 *
 * @param integer $p_issue_id  The id of the issue to retrieve the notes for
 * @return Array that represents an IssueNoteData structure
 */
function mci_issue_get_notes( $p_issue_id ) {
	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );
	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	$t_user_bugnote_order = 'ASC'; // always get the notes in ascending order for consistency to the calling application.
	$t_has_time_tracking_access = access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $p_issue_id );
	
	$t_result = array();
	foreach( bugnote_get_all_visible_bugnotes( $p_issue_id, $t_user_bugnote_order, 0 ) as $t_value ) {
		$t_bugnote = array();
		$t_bugnote['id'] = $t_value->id;
		$t_bugnote['reporter'] = mci_account_get_array_by_id( $t_value->reporter_id );
		$t_bugnote['date_submitted'] = timestamp_to_iso8601( $t_value->date_submitted );
		$t_bugnote['last_modified'] = timestamp_to_iso8601( $t_value->last_modified );
		$t_bugnote['text'] = $t_value->note;
		$t_bugnote['view_state'] = mci_enum_get_array_by_id( $t_value->view_state, 'view_state', $t_lang );
		$t_bugnote['time_tracking'] = $t_has_time_tracking_access ? $t_value->time_tracking : 0;
		
		$t_result[] = $t_bugnote;
	}

	return (count( $t_result ) == 0 ? null : $t_result );
}

/**
 * Get the biggest issue id currently used.
 *
 * @param string $p_username  The name of the user trying to retrieve the information
 * @param string $p_password  The password of the user.
 * @param int    $p_project_id	-1 default project, 0 for all projects, otherwise project id.
 * @return integer  The biggest used issue id.
 */
function mc_issue_get_biggest_id( $p_username, $p_password, $p_project_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_any = defined( 'META_FILTER_ANY' ) ? META_FILTER_ANY : 'any';
	$t_none = defined( 'META_FILTER_NONE' ) ? META_FILTER_NONE : 'none';

	$t_filter = array(
		'show_category' => Array(
			'0' => $t_any,
		),
		'show_severity' => Array(
			'0' => $t_any,
		),
		'show_status' => Array(
			'0' => $t_any,
		),
		'highlight_changed' => 0,
		'reporter_id' => Array(
			'0' => $t_any,
		),
		'handler_id' => Array(
			'0' => $t_any,
		),
		'show_resolution' => Array(
			'0' => $t_any,
		),
		'show_build' => Array(
			'0' => $t_any,
		),
		'show_version' => Array(
			'0' => $t_any,
		),
		'hide_status' => Array(
			'0' => $t_none,
		),
		'user_monitor' => Array(
			'0' => $t_any,
		),
		'dir' => 'DESC',
		'sort' => 'date_submitted',
	);

	$t_page_number = 1;
	$t_per_page = 1;
	$t_bug_count = 0;
	$t_page_count = 0;

	# Get project id, if -1, then retrieve the current which will be the default since there is no cookie.
	$t_project_id = $p_project_id;
	if( $t_project_id == -1 ) {
		$t_project_id = helper_get_current_project();
	}

	if(( $t_project_id > 0 ) && !project_exists( $t_project_id ) ) {
		return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
	}

	if( !mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_rows = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter, $t_project_id, $t_user_id );
	if( count( $t_rows ) == 0 ) {
		return 0;
	} else {
		return $t_rows[0]->id;
	}
}

/**
 * Get the id of an issue via the issue's summary.
 *
 * @param string $p_username  The name of the user trying to delete the issue.
 * @param string $p_password  The password of the user.
 * @param string $p_summary  The summary of the issue to retrieve.
 * @return integer  The id of the issue with the given summary, 0 if there is no such issue.
 */
function mc_issue_get_id_from_summary( $p_username, $p_password, $p_summary ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "SELECT id
		FROM $t_bug_table
		WHERE summary = " . db_param();

	$result = db_query_bound( $query, Array( $p_summary ), 1 );

	if( db_num_rows( $result ) == 0 ) {
		return 0;
	} else {
		while(( $row = db_fetch_array( $result ) ) !== false ) {
			$t_issue_id = (int) $row['id'];
			$t_project_id = bug_get_field( $t_issue_id, 'project_id' );

			if( mci_has_readonly_access( $t_user_id, $t_project_id ) ) {
				return $t_issue_id;
			}
		}

		// no issue found that belongs to a project that the user has read access to.
		return 0;
	}
}

/**
 * Add an issue to the database.
 *
 * @param string $p_username  The name of the user trying to add the issue.
 * @param string $p_password  The password of the user.
 * @param Array $p_issue  A IssueData structure containing information about the new issue.
 * @return integer  The id of the created issue.
 */
function mc_issue_add( $p_username, $p_password, $p_issue ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project = $p_issue['project'];

	$t_project_id = mci_get_project_id( $t_project );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_handler_id = isset( $p_issue['handler'] ) ? mci_get_user_id( $p_issue['handler'] ) : 0;
	$t_priority_id = isset( $p_issue['priority'] ) ? mci_get_priority_id( $p_issue['priority'] ) : config_get( 'default_bug_priority' );
	$t_severity_id = isset( $p_issue['severity'] ) ?  mci_get_severity_id( $p_issue['severity'] ) : config_get( 'default_bug_severity' );
	$t_status_id = isset( $p_issue['status'] ) ? mci_get_status_id( $p_issue['status'] ) : config_get( 'bug_submit_status' );
	$t_reproducibility_id = isset( $p_issue['reproducibility'] ) ?  mci_get_reproducibility_id( $p_issue['reproducibility'] ) : config_get( 'default_bug_reproducibility' );
	$t_resolution_id =  isset( $p_issue['resolution'] ) ? mci_get_resolution_id( $p_issue['resolution'] ) : config_get('default_bug_resolution');
	$t_projection_id = isset( $p_issue['projection'] ) ? mci_get_projection_id( $p_issue['projection'] ) : config_get('default_bug_resolution');
	$t_eta_id = isset( $p_issue['eta'] ) ? mci_get_eta_id( $p_issue['eta'] ) : config_get('default_bug_eta');
	$t_view_state_id = isset( $p_issue['view_state'] ) ?  mci_get_view_state_id( $p_issue['view_state'] ) : config_get( 'default_bug_view_status' );
	$t_reporter_id = isset( $p_issue['reporter'] ) ? mci_get_user_id( $p_issue['reporter'] )  : 0;
	$t_summary = $p_issue['summary'];
	$t_description = $p_issue['description'];
	$t_notes = isset( $p_issue['notes'] ) ? $p_issue['notes'] : array();

	if( $t_reporter_id == 0 ) {
		$t_reporter_id = $t_user_id;
	} else {
		if( $t_reporter_id != $t_user_id ) {

			# Make sure that active user has access level required to specify a different reporter.
			$t_specify_reporter_access_level = config_get( 'mc_specify_reporter_on_add_access_level_threshold' );
			if( !access_has_project_level( $t_specify_reporter_access_level, $t_project_id, $t_user_id ) ) {
				return mci_soap_fault_access_denied( $t_user_id, "Active user does not have access level required to specify a different issue reporter" );
			}
		}
	}

	if(( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
		if( $t_project_id == 0 ) {
			return new soap_fault( 'Client', '', "Project '" . $t_project['name'] . "' does not exist." );
		} else {
			return new soap_fault( 'Client', '', "Project with id '" . $t_project_id . "' does not exist." );
		}
	}

	if( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( "User '$t_user_id' does not have access right to report issues" );
	}

	#if ( !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id ) ||
	#	!access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $v_reporter ) ) {
	#	return new soap_fault( 'Client', '', "User does not have access right to report issues." );
	#}

	if(( $t_handler_id != 0 ) && !user_exists( $t_handler_id ) ) {
		return new soap_fault( 'Client', '', "User '$t_handler_id' does not exist." );
	}

	$t_category = isset ( $p_issue['category'] ) ? $p_issue['category'] : null;
	
	$t_category_id = translate_category_name_to_id( $t_category, $t_project_id );
	if ( $t_category_id == 0 && !config_get( 'allow_no_category' ) ) {
		if ( !isset( $p_issue['category'] ) || is_blank( $p_issue['category'] ) ) {
			return new soap_fault( 'Client', '', "Category field must be supplied." );
		} else {
			return new soap_fault( 'Client', '', "Category '" . $p_issue['category'] . "' not found for project '$t_project_id'." );
		}
	}

	if ( isset( $p_issue['version'] ) && !is_blank( $p_issue['version'] ) && !version_get_id( $p_issue['version'], $t_project_id ) ) {
		$t_version = $p_issue['version'];

		$t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
		if( $t_error_when_version_not_found == ON ) {
			$t_project_name = project_get_name( $t_project_id );
			return new soap_fault( 'Client', '', "Version '$t_version' does not exist in project '$t_project_name'." );
		} else {
			$t_version_when_not_found = config_get( 'mc_version_when_not_found' );
			$t_version = $t_version_when_not_found;
		}
	}

	if ( is_blank( $t_summary ) ) {
		return new soap_fault( 'Client', '', "Mandatory field 'summary' is missing." );
	}

	if ( is_blank( $t_description ) ) {
		return new soap_fault( 'Client', '', "Mandatory field 'description' is missing." );
	}

	$t_bug_data = new BugData;
	$t_bug_data->profile_id = 0;
	$t_bug_data->project_id = $t_project_id;
	$t_bug_data->reporter_id = $t_reporter_id;
	$t_bug_data->handler_id = $t_handler_id;
	$t_bug_data->priority = $t_priority_id;
	$t_bug_data->severity = $t_severity_id;
	$t_bug_data->reproducibility = $t_reproducibility_id;
	$t_bug_data->status = $t_status_id;
	$t_bug_data->resolution = $t_resolution_id;
	$t_bug_data->projection = $t_projection_id;
	$t_bug_data->category_id = $t_category_id;
	$t_bug_data->date_submitted = isset( $p_issue['date_submitted'] ) ? $p_issue['date_submitted'] : '';
	$t_bug_data->last_updated = isset( $p_issue['last_updated'] ) ? $p_issue['last_updated'] : '';
	$t_bug_data->eta = $t_eta_id;
	$t_bug_data->os = isset( $p_issue['os'] ) ? $p_issue['os'] : '';
	$t_bug_data->os_build = isset( $p_issue['os_build'] ) ? $p_issue['os_build'] : '';
	$t_bug_data->platform = isset( $p_issue['platform'] ) ? $p_issue['platform'] : '';
	$t_bug_data->version = isset( $p_issue['version'] ) ? $p_issue['version'] : '';
	$t_bug_data->fixed_in_version = isset( $p_issue['fixed_in_version'] ) ? $p_issue['fixed_in_version'] : '';
	$t_bug_data->build = isset( $p_issue['build'] ) ? $p_issue['build'] : '';
	$t_bug_data->view_state = $t_view_state_id;
	$t_bug_data->summary = $t_summary;
	$t_bug_data->sponsorship_total = isset( $p_issue['sponsorship_total'] ) ? $p_issue['sponsorship_total'] : 0;
	
	if ( isset( $p_issue['due_date'] ) && access_has_global_level( config_get( 'due_date_update_threshold' ) ) ) {
		$t_bug_data->due_date = mci_iso8601_to_timestamp( $p_issue['due_date'] );
	} else {
		$t_bug_data->due_date = date_get_null();
	}

	if( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id, $t_user_id ) ) {
		$t_bug_data->target_version = isset( $p_issue['target_version'] ) ? $p_issue['target_version'] : '';
	}

	# omitted:
	# var $bug_text_id
	# $t_bug_data->profile_id;
	# extended info
	$t_bug_data->description = $t_description;
	$t_bug_data->steps_to_reproduce = isset( $p_issue['steps_to_reproduce'] ) ? $p_issue['steps_to_reproduce'] : '';
	$t_bug_data->additional_information = isset( $p_issue['additional_information'] ) ? $p_issue['additional_information'] : '';

	# submit the issue
	$t_issue_id = $t_bug_data->create();

	mci_issue_set_custom_fields( $t_issue_id, $p_issue['custom_fields'], false );

	if( isset( $t_notes ) && is_array( $t_notes ) ) {
		foreach( $t_notes as $t_note ) {
			if( isset( $t_note['view_state'] ) ) {
				$t_view_state = $t_note['view_state'];
			} else {
				$t_view_state = config_get( 'default_bugnote_view_status' );
			}

			$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
			bugnote_add( $t_issue_id, $t_note['text'], mci_get_time_tracking_from_note( $t_issue_id, $t_note ), $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id, FALSE );
		}
	}

	email_new_bug( $t_issue_id );

	return $t_issue_id;
}

/**
 * Update Issue in database
 *
 * Created By KGB
 * @param string $p_username The name of the user trying to add the issue.
 * @param string $p_password The password of the user.
 * @param Array $p_issue A IssueData structure containing information about the new issue.
 * @return integer The id of the created issue.
 */
function mc_issue_update( $p_username, $p_password, $p_issue_id, $p_issue ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return new soap_fault( 'Client', '', "Issue '$p_issue_id' does not exist." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );

	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	$t_project_id = mci_get_project_id( $p_issue['project'] );
	$t_handler_id = isset( $p_issue['handler'] ) ? mci_get_user_id( $p_issue['handler'] ) : 0;
	$t_priority_id = isset( $p_issue['priority'] ) ? mci_get_priority_id( $p_issue['priority'] ) : config_get( 'default_bug_priority' );
	$t_severity_id = isset( $p_issue['severity'] ) ?  mci_get_severity_id( $p_issue['severity'] ) : config_get( 'default_bug_severity' );
	$t_status_id = isset( $p_issue['status'] ) ? mci_get_status_id( $p_issue['status'] ) : config_get( 'bug_submit_status' );
	$t_reproducibility_id = isset( $p_issue['reproducibility'] ) ?  mci_get_reproducibility_id( $p_issue['reproducibility'] ) : config_get( 'default_bug_reproducibility' );
	$t_resolution_id =  isset( $p_issue['resolution'] ) ? mci_get_resolution_id( $p_issue['resolution'] ) : config_get('default_bug_resolution');
	$t_projection_id = isset( $p_issue['projection'] ) ? mci_get_projection_id( $p_issue['projection'] ) : config_get('default_bug_resolution');
	$t_eta_id = isset( $p_issue['eta'] ) ? mci_get_eta_id( $p_issue['eta'] ) : config_get('default_bug_eta');
	$t_view_state_id = isset( $p_issue['view_state'] ) ?  mci_get_view_state_id( $p_issue['view_state'] ) : config_get( 'default_bug_view_status' );
	$t_reporter_id = isset( $p_issue['reporter'] ) ? mci_get_user_id( $p_issue['reporter'] )  : 0;
	$t_project = $p_issue['project'];
	$t_summary = isset( $p_issue['summary'] ) ? $p_issue['summary'] : '';
	$t_description = isset( $p_issue['description'] ) ? $p_issue['description'] : '';
	$t_additional_information = isset( $p_issue['additional_information'] ) ? $p_issue['additional_information'] : '';
	$t_steps_to_reproduce = isset( $p_issue['steps_to_reproduce'] ) ? $p_issue['steps_to_reproduce'] : '';
	$t_build = isset( $p_issue['build'] ) ? $p_issue['build'] : '';
	$t_platform = isset( $p_issue['platform'] ) ? $p_issue['platform'] : '';
	$t_os = isset( $p_issue['os'] ) ? $p_issue['os'] : '';
	$t_os_build = isset( $p_issue['os_build'] ) ? $p_issue['os_build'] : '';
	$t_sponsorship_total = isset( $p_issue['sponsorship_total'] ) ? $p_issue['sponsorship_total'] : '';

	if( $t_reporter_id == 0 ) {
		$t_reporter_id = $t_user_id;
	}

	if(( $t_project_id == 0 ) || !project_exists( $t_project_id ) ) {
		if( $t_project_id == 0 ) {
			return new soap_fault( 'Client', '', "Project '" . $t_project['name'] . "' does not exist." );
		}
		return new soap_fault( 'Client', '', "Project '$t_project_id' does not exist." );
	}

	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id,  "Not enough rights to update issues" );
	}

	if(( $t_handler_id != 0 ) && !user_exists( $t_handler_id ) ) {
		return new soap_fault( 'Client', '', "User '$t_handler_id' does not exist." );
	}

	$t_category = isset ( $p_issue['category'] ) ? $p_issue['category'] : null;
	
	$t_category_id = translate_category_name_to_id( $t_category, $t_project_id );
	if ( $t_category_id == 0 && !config_get( 'allow_no_category' ) ) {
		if ( isset( $p_issue['category'] ) && !is_blank( $p_issue['category'] ) ) {
			return new soap_fault( 'Client', '', "Category field must be supplied." );
		} else {
			return new soap_fault( 'Client', '', "Category '" . $p_issue['category'] . "' not found for project '$t_project_name'." );
		}
	}

	if ( isset( $p_issue['version'] ) && !is_blank( $p_issue['version'] ) && !version_get_id( $p_issue['version'], $t_project_id ) ) {
		$t_error_when_version_not_found = config_get( 'mc_error_when_version_not_found' );
		if( $t_error_when_version_not_found == ON ) {
			$t_project_name = project_get_name( $t_project_id );
			return new soap_fault( 'Client', '', "Version '" . $p_issue['version'] . "' does not exist in project '$t_project_name'." );
		} else {
			$t_version_when_not_found = config_get( 'mc_version_when_not_found' );
			$p_issue['version'] = $t_version_when_not_found;
		}
	}

	if ( is_blank( $t_summary ) ) {
		return new soap_fault( 'Client', '', "Mandatory field 'summary' is missing." );
	}

	if ( is_blank( $t_description ) ) {
		return new soap_fault( 'Client', '', "Mandatory field 'description' is missing." );
	}

	if ( $t_priority_id == 0 ) {
		$t_priority_id = config_get( 'default_bug_priority' );
	}

	if ( $t_severity_id == 0 ) {
		$t_severity_id = config_get( 'default_bug_severity' );
	}

	if ( $t_view_state_id == 0 ) {
		$t_view_state_id = config_get( 'default_bug_view_status' );
	}

	if ( $t_reproducibility_id == 0 ) {
		$t_reproducibility_id = config_get( 'default_bug_reproducibility' );
	}

	$t_bug_data = new BugData;
	$t_bug_data->id = $p_issue_id;
	$t_bug_data->project_id = $t_project_id;
	$t_bug_data->reporter_id = $t_reporter_id;
	$t_bug_data->handler_id = $t_handler_id;
	$t_bug_data->priority = $t_priority_id;
	$t_bug_data->severity = $t_severity_id;
	$t_bug_data->reproducibility = $t_reproducibility_id;
	$t_bug_data->status = $t_status_id;
	$t_bug_data->resolution = $t_resolution_id;
	$t_bug_data->projection = $t_projection_id;
	$t_bug_data->category_id = $t_category_id;
	$t_bug_data->date_submitted = isset( $v_date_submitted ) ? $v_date_submitted : '';
	$t_bug_data->last_updated = isset( $v_last_updated ) ? $v_last_updated : '';
	$t_bug_data->eta = $t_eta_id;
	$t_bug_data->os = $t_os;
	$t_bug_data->os_build = $t_os_build;
	$t_bug_data->platform = $t_platform;
	$t_bug_data->version = isset( $p_issue['version'] ) ? $p_issue['version'] : '';
	$t_bug_data->fixed_in_version = isset( $p_issue['fixed_in_version'] ) ? $p_issue['fixed_in_version'] : '';
	$t_bug_data->build = $t_build;
	$t_bug_data->view_state = $t_view_state_id;
	$t_bug_data->summary = $t_summary;
	$t_bug_data->sponsorship_total = $t_sponsorship_total;

	if ( isset( $p_issue['due_date'] ) && access_has_global_level( config_get( 'due_date_update_threshold' ) ) ) {
		$t_bug_data->due_date = mci_iso8601_to_timestamp( $p_issue['due_date'] );
	} else {
		$t_bug_data->due_date = date_get_null();
	}

	if( access_has_project_level( config_get( 'roadmap_update_threshold' ), $t_bug_data->project_id, $t_user_id ) ) {
		$t_bug_data->target_version = isset( $p_issue['target_version'] ) ? $p_issue['target_version'] : '';
	}

	# omitted:
	# var $bug_text_id
	# $t_bug_data->profile_id;
	# extended info
	$t_bug_data->description = $t_description;
	$t_bug_data->steps_to_reproduce = isset( $t_steps_to_reproduce ) ? $t_steps_to_reproduce : '';
	$t_bug_data->additional_information = isset( $t_additional_information ) ? $t_additional_information : '';

	# submit the issue
	$t_is_success = $t_bug_data->update( /* update_extended */ true, /* bypass_email */ true );
	
	mci_issue_set_custom_fields( $p_issue_id, $p_issue['custom_fields'], true );

	if ( isset( $p_issue['notes'] ) && is_array( $p_issue['notes'] ) ) {
		foreach ( $p_issue['notes'] as $t_note ) {
			if ( isset( $t_note['view_state'] ) ) {
				$t_view_state = $t_note['view_state'];
			} else {
				$t_view_state = config_get( 'default_bugnote_view_status' );
			}

			if ( isset( $t_note['id'] ) && ( (int)$t_note['id'] > 0 ) ) {
				$t_bugnote_id = (integer)$t_note['id'];

				if ( bugnote_exists( $t_bugnote_id ) ) {
					bugnote_set_text( $t_bugnote_id, $t_note['text'] );
					bugnote_set_view_state( $t_bugnote_id, $t_view_state_id == VS_PRIVATE );
					bugnote_date_update( $t_bugnote_id );
					if ( isset( $t_note['time_tracking'] ) )
						bugnote_set_time_tracking( $t_bugnote_id, mci_get_time_tracking_from_note( $p_issue_id, $t_note ) );
				}
			} else {
				$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
				
				bugnote_add( $p_issue_id, $t_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $t_note ), $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id, FALSE );
			}
		}
	}

	return $t_is_success;
}

/**
 * Delete the specified issue.
 *
 * @param string $p_username  The name of the user trying to delete the issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to delete.
 * @return boolean  True if the issue has been deleted successfully, false otherwise.
 */
function mc_issue_delete( $p_username, $p_password, $p_issue_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return new soap_fault( 'Client', '', "Issue '$p_issue_id' does not exist.");
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return bug_delete( $p_issue_id );
}

/**
 * Add a note to an existing issue.
 *
 * @param string $p_username  The name of the user trying to add a note to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to add the note to.
 * @param IssueNoteData $p_note  The note to add.
 * @return integer The id of the added note.
 */
function mc_issue_note_add( $p_username, $p_password, $p_issue_id, $p_note ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( (integer) $p_issue_id < 1 ) {
		return new soap_fault( 'Client', '', "Invalid issue id '$p_issue_id'" );
	}

	if( !bug_exists( $p_issue_id ) ) {
		return new soap_fault( 'Client', '', "Issue '$p_issue_id' does not exist." );
	}

	if ( !isset( $p_note['text'] ) || is_blank( $p_note['text'] ) ) {
		return new soap_fault( 'Client', '', "Issue note text must not be blank." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	if( !access_has_bug_level( config_get( 'add_bugnote_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "You do not have access rights to add notes to this issue" );
	}

	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Issue '$p_issue_id' is readonly" );
	}

	if( isset( $p_note['view_state'] ) ) {
		$t_view_state = $p_note['view_state'];
	} else {
		$t_view_state = array(
			'id' => config_get( 'default_bug_view_status' ),
		);
	}
	
	$t_view_state_id = mci_get_enum_id_from_objectref( 'view_state', $t_view_state );
	return bugnote_add( $p_issue_id, $p_note['text'], mci_get_time_tracking_from_note( $p_issue_id, $p_note ), $t_view_state_id == VS_PRIVATE, BUGNOTE, '', $t_user_id );
}

/**
 * Delete a note given its id.
 *
 * @param string $p_username  The name of the user trying to add a note to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_note_id  The id of the note to be deleted.
 * @return true: success, false: failure
 */
function mc_issue_note_delete( $p_username, $p_password, $p_issue_note_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( (integer) $p_issue_note_id < 1 ) {
		return new soap_fault( 'Client', '', "Invalid issue note id '$p_issue_note_id'.");
	}

	if( !bugnote_exists( $p_issue_note_id ) ) {
		return new soap_fault( 'Client', '', "Issue note '$p_issue_note_id' does not exist.");
	}

	$t_issue_id = bugnote_get_field( $p_issue_note_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	return bugnote_delete( $p_issue_note_id );
}

/**
 * Submit a new relationship.
 *
 * @param string $p_username  The name of the user trying to add a note to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue of the source issue.
 * @param RelationshipData $p_relationship  The relationship to add.
 * @return integer The id of the added relationship.
 */
function mc_issue_relationship_add( $p_username, $p_password, $p_issue_id, $p_relationship ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	$t_dest_issue_id = $p_relationship['target_id'];
	$t_rel_type = $p_relationship['type'];

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "Active user does not have access level required to add a relationship to this issue" );
	}

	# source and destination bugs are the same bug...
	if( $p_issue_id == $t_dest_issue_id ) {
		return new soap_fault( 'Client', '', "An issue can't be related to itself." );
	}

	# the related bug exists...
	if( !bug_exists( $t_dest_issue_id ) ) {
		return new soap_fault( 'Client', '', "Issue '$t_dest_issue_id' not found." );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return new mci_soap_fault_access_denied( $t_user_id, "Issue '$p_issue_id' is readonly" );
	}

	# user can access to the related bug at least as viewer...
	if( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id, "The issue '$t_dest_issue_id' requires higher access level" );
	}

	$t_old_id_relationship = relationship_same_type_exists( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );

	if( $t_old_id_relationship == 0 ) {
		relationship_add( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );

		// The above function call into MantisBT does not seem to return a valid BugRelationshipData object.
		// So we call db_insert_id in order to find the id of the created relationship.
		$t_relationship_id = db_insert_id( db_get_table( 'mantis_bug_relationship_table' ) );

		# Add log line to the history (both bugs)
		history_log_event_special( $p_issue_id, BUG_ADD_RELATIONSHIP, $t_rel_type['id'], $t_dest_issue_id );
		history_log_event_special( $t_dest_issue_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $t_rel_type['id'] ), $p_issue_id );

		# update bug last updated (just for the src bug)
		bug_update_date( $p_issue_id );

		# send email notification to the users addressed by both the bugs
		email_relationship_added( $p_issue_id, $t_dest_issue_id, $t_rel_type['id'] );
		email_relationship_added( $t_dest_issue_id, $p_issue_id, relationship_get_complementary_type( $t_rel_type['id'] ) );

		return $t_relationship_id;
	} else {
		return new soap_fault( 'Client', '', "Relationship already exists." );
	}
}

/**
 * Delete the relationship with the specified target id.
 *
 * @param string $p_username  The name of the user trying to add a note to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the source issue for the relationship
 * @param integer $p_relationship_id  The id of relationship to delete.
 * @return true: success, false: failure
 */
function mc_issue_relationship_delete( $p_username, $p_password, $p_issue_id, $p_relationship_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	# user has access to update the bug...
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id , "Active user does not have access level required to remove a relationship from this issue." );
	}

	# bug is not read-only...
	if( bug_is_readonly( $p_issue_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id , "Issue '$p_issue_id' is readonly." );
	}

	# retrieve the destination bug of the relationship
	$t_dest_issue_id = relationship_get_linked_bug_id( $p_relationship_id, $p_issue_id );

	# user can access to the related bug at least as viewer, if it's exist...
	if( bug_exists( $t_dest_issue_id ) ) {
		if( !access_has_bug_level( VIEWER, $t_dest_issue_id, $t_user_id ) ) {
			return mci_soap_fault_access_denied( $t_user_id , "The issue '$t_dest_issue_id' requires higher access level." );
		}
	}

	$t_bug_relationship_data = relationship_get( $p_relationship_id );
	$t_rel_type = $t_bug_relationship_data->type;

	# delete relationship from the DB
	relationship_delete( $p_relationship_id );

	# update bug last updated (just for the src bug)
	bug_update_date( $p_issue_id );

	# set the rel_type for both bug and dest_bug based on $t_rel_type and on who is the dest bug
	if( $p_issue_id == $t_bug_relationship_data->src_bug_id ) {
		$t_bug_rel_type = $t_rel_type;
		$t_dest_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
	} else {
		$t_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
		$t_dest_bug_rel_type = $t_rel_type;
	}

	# send email and update the history for the src issue
	history_log_event_special( $p_issue_id, BUG_DEL_RELATIONSHIP, $t_bug_rel_type, $t_dest_issue_id );
	email_relationship_deleted( $p_issue_id, $t_dest_issue_id, $t_bug_rel_type );

	if( bug_exists( $t_dest_issue_id ) ) {

		# send email and update the history for the dest issue
		history_log_event_special( $t_dest_issue_id, BUG_DEL_RELATIONSHIP, $t_dest_bug_rel_type, $p_issue_id );
		email_relationship_deleted( $t_dest_issue_id, $p_issue_id, $t_dest_bug_rel_type );
	}

	return true;
}

/**
 * Log a checkin event on the issue
 *
 * @param string $p_username  The name of the user trying to access the issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id The id of the issue to log a checkin.
 * @param string $p_comment   The comment to add
 * @param boolean $p_fixed    True if the issue is to be set to fixed
 * @return boolean  true success, false otherwise.
 */
function mc_issue_checkin( $p_username, $p_password, $p_issue_id, $p_comment, $p_fixed ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	if( !bug_exists( $p_issue_id ) ) {
		return new soap_fault( 'Client', '', "Issue '$p_issue_id' not found." );
	}

	$t_project_id = bug_get_field( $p_issue_id, 'project_id' );
	if( !mci_has_readwrite_access( $t_user_id, $t_project_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}

	helper_call_custom_function( 'checkin', array( $p_issue_id, $p_comment, '', '', $p_fixed ) );

	return true;
}

/**
 * Returns the date in iso8601 format, with proper timezone offset applied
 * 
 * @param string $p_date the date in iso8601 format
 * @return int the timestamp
 */
function mci_iso8601_to_timestamp( $p_date ) {
	
	// retrieve the offset, seems to be lost by nusoap
	$t_utc_date = new DateTime( $p_date, new DateTimeZone( 'UTC' ) );
	$t_timezone = new DateTimeZone( date_default_timezone_get() );
	$t_offset = $t_timezone->getOffset( $t_utc_date ); 
	
	$t_raw_timestamp = iso8601_to_timestamp( $p_date );
	
	return $t_raw_timestamp - $t_offset;
	
}


/**
 * Returns an array for SOAP encoding from a BugData object
 * 
 * @param BugData $p_issue_data
 * @param int $p_user_id
 * @param string $p_lang
 * @return array The issue as an array
 */
function mci_issue_data_as_array( $p_issue_data, $p_user_id, $p_lang ) {
	
		$t_id = $p_issue_data->id;

		$t_issue = array();
		$t_issue['id'] = $t_id;
		$t_issue['view_state'] = mci_enum_get_array_by_id( $p_issue_data->view_state, 'view_state', $p_lang );
		$t_issue['last_updated'] = timestamp_to_iso8601( $p_issue_data->last_updated );

		$t_issue['project'] = mci_project_as_array_by_id( $p_issue_data->project_id );
		$t_issue['category'] = mci_get_category( $p_issue_data->category_id );
		$t_issue['priority'] = mci_enum_get_array_by_id( $p_issue_data->priority, 'priority', $p_lang );
		$t_issue['severity'] = mci_enum_get_array_by_id( $p_issue_data->severity, 'severity', $p_lang );
		$t_issue['status'] = mci_enum_get_array_by_id( $p_issue_data->status, 'status', $p_lang );

		$t_issue['reporter'] = mci_account_get_array_by_id( $p_issue_data->reporter_id );
		$t_issue['summary'] = $p_issue_data->summary;
		$t_issue['version'] = mci_null_if_empty( $p_issue_data->version );
		$t_issue['build'] = mci_null_if_empty( $p_issue_data->build );
		$t_issue['platform'] = mci_null_if_empty( $p_issue_data->platform );
		$t_issue['os'] = mci_null_if_empty( $p_issue_data->os );
		$t_issue['os_build'] = mci_null_if_empty( $p_issue_data->os_build );
		$t_issue['reproducibility'] = mci_enum_get_array_by_id( $p_issue_data->reproducibility, 'reproducibility', $p_lang );
		$t_issue['date_submitted'] = timestamp_to_iso8601( $p_issue_data->date_submitted );
		$t_issue['sponsorship_total'] = $p_issue_data->sponsorship_total;

		if( !empty( $p_issue_data->handler_id ) ) {
			$t_issue['handler'] = mci_account_get_array_by_id( $p_issue_data->handler_id );
		}
		$t_issue['projection'] = mci_enum_get_array_by_id( $p_issue_data->projection, 'projection', $p_lang );
		$t_issue['eta'] = mci_enum_get_array_by_id( $p_issue_data->eta, 'eta', $p_lang );

		$t_issue['resolution'] = mci_enum_get_array_by_id( $p_issue_data->resolution, 'resolution', $p_lang );
		$t_issue['fixed_in_version'] = mci_null_if_empty( $p_issue_data->fixed_in_version );
		$t_issue['target_version'] = mci_null_if_empty( $p_issue_data->target_version );

		$t_issue['description'] = bug_get_text_field( $t_id, 'description' );

		$t_steps_to_reproduce = bug_get_text_field( $t_id, 'steps_to_reproduce' );
		$t_issue['steps_to_reproduce'] = mci_null_if_empty( $t_steps_to_reproduce );

		$t_additional_information = bug_get_text_field( $t_id, 'additional_information' );
		$t_issue['additional_information'] = mci_null_if_empty( $t_additional_information );

		$t_issue['attachments'] = mci_issue_get_attachments( $p_issue_data->id );
		$t_issue['relationships'] = mci_issue_get_relationships( $p_issue_data->id, $p_user_id );
		$t_issue['notes'] = mci_issue_get_notes( $p_issue_data->id );
		$t_issue['custom_fields'] = mci_issue_get_custom_fields( $p_issue_data->id );
		
		return $t_issue;
}