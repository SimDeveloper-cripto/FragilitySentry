package JUnit.JTrac; // Generated by Selenium IDE

import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.Keys;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.chrome.ChromeDriver;

import static org.junit.Assert.*;
import static org.hamcrest.CoreMatchers.is;

public class CreateUser1Test {
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
  public void createUser1() {
    driver.get("http://127.0.0.1:8888/app");
    driver.findElement(By.linkText("OPZIONI")).click();
    driver.findElement(By.linkText("Gestione Utenti")).click();
    driver.findElement(By.linkText("Crea Nuovo Utente")).click();
    driver.findElement(By.name("user.loginName")).sendKeys("Mike");
    driver.findElement(By.name("user.name")).click();
    driver.findElement(By.name("user.name")).sendKeys("Mike Fonseta");
    driver.findElement(By.name("user.email")).click();
    driver.findElement(By.name("user.email")).sendKeys("m.fonseta@studenti.unina.it");
    driver.findElement(By.name("password")).click();
    driver.findElement(By.name("password")).sendKeys("123456789");
    driver.findElement(By.name("passwordConfirm")).click();
    driver.findElement(By.name("passwordConfirm")).sendKeys("123456789");
    driver.findElement(By.name("passwordConfirm")).sendKeys(Keys.ENTER);
    driver.findElement(By.cssSelector("input:nth-child(4)")).click();
    assertThat(driver.findElement(By.cssSelector(".selected > td:nth-child(2)")).getText(), is("Mike"));
    driver.close();
  }
}