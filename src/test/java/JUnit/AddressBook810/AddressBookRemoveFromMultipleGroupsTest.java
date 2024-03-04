package JUnit.AddressBook810;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.support.ui.Select;

// it was AddressBookUnassignFromMultipleGroupsTest
public class AddressBookRemoveFromMultipleGroupsTest {

	private  WebDriver driver = new ChromeDriver();
	JavascriptExecutor js = (JavascriptExecutor) driver;


	public void setUp(WebDriver driver) {
		this.driver.quit();
		this.driver=driver;
		js = (JavascriptExecutor) driver;
	}
	@Test
	public void addressBookRemoveFromMultipleGroups() throws Exception {
		driver.get("http://localhost:3000/index.php");
		//driver.findElement(By.name("user")).sendKeys("admin");
		//driver.findElement(By.name("pass")).sendKeys("secret");
		//driver.findElement(By.xpath(".//*[@id='content']/form/input[3]")).click();
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup1");
		driver.findElement(By.xpath("html/body/div[1]/div[4]/form[2]/table/tbody/tr[2]/td[1]/input")).click();
		driver.findElement(By.name("remove")).click();
		driver.findElement(By.linkText("homepage")).click();
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup2");
		driver.findElement(By.xpath("html/body/div[1]/div[4]/form[2]/table/tbody/tr[2]/td[1]/input")).click();
		driver.findElement(By.name("remove")).click();
		driver.findElement(By.linkText("homepage")).click();
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup3");
		driver.findElement(By.xpath("html/body/div[1]/div[4]/form[2]/table/tbody/tr[2]/td[1]/input")).click();
		driver.findElement(By.name("remove")).click();
		driver.findElement(By.linkText("homepage")).click();
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup1");
		assertEquals("Numero di risultati: 0",
				driver.findElement(By.xpath(".//*[@id='content']/label/strong")).getText());
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup2");
		assertEquals("Numero di risultati: 0",
				driver.findElement(By.xpath(".//*[@id='content']/label/strong")).getText());
		new Select(driver.findElement(By.name("group"))).selectByVisibleText("NewGroup3");
		assertEquals("Numero di risultati: 0",
				driver.findElement(By.xpath(".//*[@id='content']/label/strong")).getText());
	}

	public void tearDown() throws Exception {
		driver.quit();
	}

}
