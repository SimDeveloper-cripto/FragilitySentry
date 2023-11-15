package JUnit.MantisBT;// Generated by Selenium IDE
import org.junit.Test;
import org.junit.Before;
import org.junit.After;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.core.IsNot.not;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.Dimension;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.JavascriptExecutor;

import java.util.*;

public class UpdateWorkflow1Test {
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
  public void updateWorkflow1() throws InterruptedException {
    driver.get("http://localhost:8989/login_page.php");
    driver.manage().window().setSize(new Dimension(640, 400));
    driver.findElement(By.id("username")).sendKeys("administrator");
    driver.findElement(By.cssSelector(".width-40")).click();
    driver.findElement(By.id("password")).sendKeys("root");
    driver.findElement(By.cssSelector(".width-40")).click();
    JavascriptExecutor executor = (JavascriptExecutor)driver;
    executor.executeScript("document.body.style.zoom = '0.9'");
    Thread.sleep(1000);
    driver.findElement(By.id("menu-toggler")).click();
    Thread.sleep(1000);
    WebElement element = driver.findElement(By.cssSelector("li:nth-child(7) .menu-text"));
    executor.executeScript("arguments[0].click();", element);
    Thread.sleep(1000);
    driver.findElement(By.linkText("Configurazione")).click();
    driver.findElement(By.linkText("Workflow")).click();
    driver.findElement(By.xpath("//form/div/input")).click();
  }
}