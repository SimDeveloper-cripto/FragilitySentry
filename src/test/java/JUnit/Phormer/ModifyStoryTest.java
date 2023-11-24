package JUnit.Phormer; // Generated by Selenium IDE

import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.chrome.ChromeDriver;

import static org.junit.Assert.*;
import static org.hamcrest.CoreMatchers.is;

public class ModifyStoryTest {
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
  public void modifyStory() {
    driver.get("http://localhost/");
    driver.findElement(By.linkText("Admin")).click();
    driver.findElement(By.linkText("Manage Stories")).click();
    driver.findElement(By.id("name")).click();
    driver.findElement(By.id("name")).sendKeys("Story Edit");
    driver.findElement(By.name("desc")).sendKeys("Description Edit");
    driver.findElement(By.cssSelector("tr:nth-child(4) .radio:nth-child(4)")).click();
    driver.findElement(By.name("list")).click();
    driver.findElement(By.id("public")).click();
    driver.findElement(By.cssSelector(".submit")).click();
    assertThat(driver.findElement(By.cssSelector(".note_valid")).getText(), is("Story \\\"Story Edit\\\" added succesfully!"));
  }
}