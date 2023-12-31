package JUnit.Phormer; // Generated by Selenium IDE

import java.util.List;
import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.chrome.ChromeDriver;

public class DeleteCategoryTest {
  private WebDriver driver = new ChromeDriver();
  JavascriptExecutor js = (JavascriptExecutor) driver;

  public void setUp(WebDriver driver) {
    this.driver.quit();
    this.driver = driver;
    js = (JavascriptExecutor) driver;
  }

  @After
  public void tearDown() {
    driver.quit();
  }

  @Test
  public void deleteCategory() {
    driver.get("http://localhost/");
    driver.findElement(By.linkText("Admin")).click();

    driver.findElement(By.name("passwd")).click();
    driver.findElement(By.name("passwd")).sendKeys("admin");
    driver.findElement(By.cssSelector(".submit")).click();

    driver.findElement(By.linkText("Manage Categories")).click();
    driver.findElement(By.cssSelector("span:nth-child(10) > a:nth-child(2)")).click();
    driver.switchTo().alert().accept();

    {
      List<WebElement> elements = driver.findElements(By.cssSelector(".note_valid"));
      assert(!elements.isEmpty());
    }
  }
}