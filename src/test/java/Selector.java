
import org.openqa.selenium.By;

public class Selector {
    private String selector, type;
    private float selectorFinalScore;

    public Selector(String value, String type) {
        this.selector = value;
        this.type     = type;
    }

    public Selector(By locator) {
        this.selector = createSelector(locator);
        this.type     = locator.getClass().getSimpleName().replaceFirst("By", "");
    }

    @Override
    public String toString() {
        return "Selector = '" + selector + '\'' + ", Type = '" + type + '\'' + ", SelectorComplexityScore = " + getSelectorFinalScore() + '\'';
    }

    private String createSelector(By locator) {
        String stringLocator = locator.toString();
        int index = stringLocator.indexOf(":");

        return stringLocator.substring(index + 2);
    }

    public String getSelector() {
        return this.selector;
    }

    public void setSelector(String selector) {
        this.selector = selector;
    }

    public String getType() {
        return this.type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public float getSelectorFinalScore() {
        return this.selectorFinalScore;
    }

    public void setSelectorFinalScore(float selectorFinalScore) {
        this.selectorFinalScore = selectorFinalScore;
    }
}