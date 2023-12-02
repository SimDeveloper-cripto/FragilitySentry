package JUnit.PasswordManager; // Generated by Selenium IDE

import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.chrome.ChromeDriver;

import static org.junit.Assert.*;
import static org.hamcrest.CoreMatchers.is;

public class RegisterTest {
  private WebDriver driver = new ChromeDriver();
  JavascriptExecutor js;

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
  public void register() {
    driver.get("http://localhost:8000/");
    driver.findElement(By.cssSelector(".btn:nth-child(10)")).click();

    driver.findElement(By.id("user")).click();
    driver.findElement(By.id("user")).sendKeys("MikeFonseta");

    driver.findElement(By.id("pwd")).click();
    driver.findElement(By.id("pwd")).sendKeys("1231231");

    driver.findElement(By.id("pwd1")).click();
    driver.findElement(By.id("pwd1")).sendKeys("1231231");

    driver.findElement(By.id("email")).click();
    driver.findElement(By.id("email")).sendKeys("m.fonseta@studenti.unina.it");

    driver.findElement(By.id("chk")).click();

    // TODO: Login and Registration do not work

    driver.switchTo().alert().accept();

    {
      String value = driver.findElement(By.id("chk")).getAttribute("value");
      assertThat(value, is("Login"));
    }

    driver.close();
  }
}