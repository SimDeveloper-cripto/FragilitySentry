package JUnit.Magento;// Generated by Selenium IDE
import org.junit.BeforeClass;
import org.junit.Test;
import org.junit.Before;
import org.junit.After;
import org.junit.runner.RunWith;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.interactions.Actions;

import java.util.*;

public class CreateAccount1Test {
  private  WebDriver driver=new ChromeDriver();
  private Map<String, Object> vars=new HashMap<String, Object>();
  JavascriptExecutor js= (JavascriptExecutor) driver;


  public void setUp(WebDriver driver) {
    this.driver.quit();
    this.driver=driver;
    js = (JavascriptExecutor) driver;
    vars = new HashMap<String, Object>();
  }
  @After
  public void tearDown() {

    driver.quit();
  }
  @Test
  public void createAccount1() throws InterruptedException {
    driver.get("http://localhost/");
    driver.manage().window().setSize(new Dimension(945, 1020));
    Thread.sleep(1000);
    driver.findElement(By.linkText("Create an Account")).click();
    Thread.sleep(1000);
    driver.findElement(By.id("firstname")).click();
    driver.findElement(By.id("firstname")).sendKeys("Mario");
    driver.findElement(By.id("lastname")).sendKeys("Rossi");
    driver.findElement(By.id("email_address")).click();
    driver.findElement(By.id("email_address")).sendKeys("prova@prova.com");
    driver.findElement(By.id("password")).sendKeys("Pupazzo1");
    driver.findElement(By.id("password-confirmation")).click();
    driver.findElement(By.id("password-confirmation")).sendKeys("Pupazzo1");
    Thread.sleep(1000);
    driver.findElement(By.cssSelector(".submit > span")).click();
    Thread.sleep(1000);
    driver.findElement(By.linkText("click here")).click();
    driver.findElement(By.id("email_address")).click();
    driver.findElement(By.id("captcha_user_forgotpassword")).click();
  }
}
