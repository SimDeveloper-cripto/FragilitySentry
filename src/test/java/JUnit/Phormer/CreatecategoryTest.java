package JUnit.Phormer; // Generated by Selenium IDE

import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.interactions.Actions;

import static org.junit.Assert.*;
import static org.hamcrest.CoreMatchers.is;

public class CreatecategoryTest {
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
  public void createcategory() {
    driver.get("http://localhost/");
    driver.findElement(By.linkText("Admin")).click();

    driver.findElement(By.name("passwd")).click();
    driver.findElement(By.name("passwd")).sendKeys("admin");
    driver.findElement(By.cssSelector(".submit")).click();

    driver.findElement(By.linkText("Manage Categories")).click();
    driver.findElement(By.cssSelector(".inside")).click();
    driver.findElement(By.id("name")).click();
    driver.findElement(By.id("name")).sendKeys("New category");
    driver.findElement(By.name("desc")).sendKeys("Category description");
    driver.findElement(By.cssSelector("tr:nth-child(3) .radio:nth-child(4)")).click();
    driver.findElement(By.cssSelector("tr:nth-child(4) .radio:nth-child(4)")).click();
    driver.findElement(By.id("password")).click();
    driver.findElement(By.id("password")).sendKeys("admin");
    {
      WebElement dropdown = driver.findElement(By.name("sub"));
      dropdown.findElement(By.xpath("//option[. = '1: Default Category']")).click();
    }
    {
      WebElement element = driver.findElement(By.name("sub"));
      Actions builder = new Actions(driver);
      builder.moveToElement(element).clickAndHold().perform();
    }
    {
      WebElement element = driver.findElement(By.name("sub"));
      Actions builder = new Actions(driver);
      builder.moveToElement(element).perform();
    }
    {
      WebElement element = driver.findElement(By.name("sub"));
      Actions builder = new Actions(driver);
      builder.moveToElement(element).release().perform();
    }
    driver.findElement(By.cssSelector(".submit")).click();
    assertThat(driver.findElement(By.cssSelector(".note_valid")).getText(), is("Category \"New category\" added succesfully!"));
  }
}