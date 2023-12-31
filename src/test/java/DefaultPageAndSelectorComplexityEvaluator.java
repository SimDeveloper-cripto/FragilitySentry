
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

import java.util.List;

public class DefaultPageAndSelectorComplexityEvaluator implements IPageAndSelectorScoreStrategy {
    @Override
    public float evaluatePageAndSelectorComplexity(Selector selector, Page page, WebDriver driver) {
        String selectorType   = selector.getType();
        String selectorString = selector.getSelector();

        int nElemFirstMatch, nElemSecondMatch;
        float ratio;

        String newSelectorString;
        switch (selectorType) {
            case "CssSelector":
                nElemFirstMatch   = getNumberOfMatches(selectorString, selectorType, driver);
                System.out.println("(Debug) Old css selector: " + selectorString);
                newSelectorString = removeFirstLevelCssSelector(selectorString);
                System.out.println("(Debug) New css selector: " + newSelectorString);
                nElemSecondMatch  = getNumberOfMatches(newSelectorString, selectorType, driver);
                break;
            case "XPath":
                nElemFirstMatch   = getNumberOfMatches(selectorString, selectorType, driver);
                System.out.println("(Debug) Old xpath selector: " + selectorString);
                newSelectorString = removeFirstLevelXPath(selectorString);
                System.out.println("(Debug) New xpath selector: " + newSelectorString);
                nElemSecondMatch  = getNumberOfMatches(newSelectorString, selectorType, driver);
                break;
            default:
                return 0;
        }

        ratio = (float) nElemFirstMatch / (float) nElemSecondMatch;
        return (1 - ratio);

        /*
            if (nElemSecondMatch == 0) {
                 ratio = 0.2f;
            } else {
                ratio = (float) nElemFirstMatch / (float) nElemSecondMatch;
            }
            return (1 - ratio);
        */
    }

    private String removeFirstLevelCssSelector(String selectorString) {
        String[] combinators = { ">", "+", "~", " " };

        for (String combinator : combinators) {
            int index = selectorString.indexOf(combinator);
            if (index != -1 && index < selectorString.length() - 1) {
                return selectorString.substring(index + 1).trim();
            }
        }
        return selectorString.trim();
    }

    private String removeFirstLevelXPath(String selectorString) {
        if (selectorString.startsWith("//")) {
            int index = selectorString.indexOf("/", 2); // The XPath string starts with "//"
            if (index != -1)
                return "//" + selectorString.substring(index + 1);
        }
        return selectorString;
    }

    private static int getNumberOfMatches(String selectorString, String type, WebDriver driver) {
        System.err.println("getNumberOfMatches(), selector received: " + selectorString);

        List<WebElement> list;
        if (type.equals("CssSelector")) {
            list = driver.findElements(By.cssSelector(selectorString));
        } else {
            list = driver.findElements(By.xpath(selectorString));
        }

        return list.size();
    }

    public static float getPageAndSelectorScoreWeight() {
        return 0.1f;
    }
}