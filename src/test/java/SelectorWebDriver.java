
import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;

import org.openqa.selenium.*;
import org.openqa.selenium.support.events.WebDriverListener;

import java.util.List;

public class SelectorWebDriver implements WebDriverListener {
	private List<Selector> visitedSelectors;
	private List<Page> visitedPages;
	private final Judge judge;
	private final Log log;

	public SelectorWebDriver(Judge judge, Log log) {
		this.judge = judge;
		this.log = log;
	}

	/* [DESCRIPTION]
		- When this method is called, the driver is about to visit a new page.
		- The source of the page is obtained and then, a new Selector object is created with the locator used and an identifying string "url".
		- Also, a new Page object (current visited page) is created using the parsed HTML document.
		- After that, page's complexity and the selector score are calculated.
	* */
/*
	@Override
	public void beforeGet(WebDriver driver, String url) {
		String pageSource     = driver.getPageSource();
		Document pageContent  = Jsoup.parse(pageSource);

		Selector selector = new Selector(url,"url");
		Page page         = new Page(pageContent);

		// Scores calculation
		page.setPageComplexity(judge.getStrategy().getPageComplexityScore(page));
		selector.setSelectorFinalScore(judge.getElementScore(selector, page));
		System.out.println(selector + "  " + page);

		System.out.println("URL: " + url);

		visitedSelectors.add(selector);
		visitedPages.add(page);
		WebDriverListener.super.beforeGet(driver, url);
	}
*/

	/* [DESCRIPTION]
		- This method is called when using a selector, and not when performing a GET request. Therefore, in the Selector object, there will be the locator used in the command within the page.
	* */
	@Override
	public void beforeFindElement(WebDriver driver, By locator) {
		String pageSource    = driver.getPageSource();
		Document pageContent = Jsoup.parse(pageSource);

		Selector selector = new Selector(locator);
		Page page         = new Page(pageContent);

		// Scores calculation
		float selectorComplexityScore        = judge.applyMetricToSelector(selector, pageContent, driver);
		float pageComplexityScore            = judge.applyMetricToPage(page);
		float pageAndSelectorComplexityScore = judge.applyMetricToPageAndSelector(selector, page, driver);

		/* Weighted Average Calculation */
		float result = (selectorComplexityScore * DefaultSelectorComplexityEvaluator.getSelectorScoreWeight())
				+ (pageComplexityScore * DefaultPageComplexityEvaluator.getPageScoreWeight())
				+ (pageAndSelectorComplexityScore * DefaultPageAndSelectorComplexityEvaluator.getPageAndSelectorScoreWeight()); // [0-1]
		// float result = (selectorComplexityScore + pageComplexityScore + pageAndSelectorComplexityScore) / 3; // [0-1]

		page.setPageComplexity(pageComplexityScore); // TODO: This is useless now (refactor)

		selector.setSimpleScore(selectorComplexityScore);
		selector.setPageAndSelectorScore(pageAndSelectorComplexityScore);
		selector.setSelectorFinalScore(result);
		selector.setPageScore(pageComplexityScore);

		System.out.println("(Analyzed) " + selector + "  " + page + "  " + "PageAndSelectorComplexityScore = " + pageAndSelectorComplexityScore + "\n");

		visitedSelectors.add(selector);
		visitedPages.add(page);
	}

	@Override
	public void beforeFindElements(WebDriver driver, By locator) {
		this.beforeFindElement(driver,locator);
		WebDriverListener.super.beforeFindElements(driver, locator);
	}

	@Override
	public void afterFindElement(WebDriver driver, By locator, WebElement result) {
		WebDriverListener.super.afterFindElement(driver, locator, result);
	}

	public void setVisitedSelectors(List<Selector> selectors) {
		this.visitedSelectors = selectors;
	}
	public List<Selector> getVisitedSelectors() {
		return visitedSelectors;
	}

	public List<Page> getVisitedPages() {
		return visitedPages;
	}
	public void setVisitedPages(List<Page> pages) {
		this.visitedPages = pages;
	}
}