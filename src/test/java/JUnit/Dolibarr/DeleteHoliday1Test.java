package JUnit.Dolibarr;// Generated by Selenium IDE
import org.junit.Test;
import org.junit.Before;
import org.junit.After;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.core.IsNot.not;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.Dimension;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.Keys;
import java.util.*;

public class DeleteHoliday1Test {
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
  public void deleteHoliday1() {
    driver.get("http://localhost:8080/");
    driver.manage().window().setSize(new Dimension(945, 1020));
    driver.findElement(By.id("username")).sendKeys("admin");
    driver.findElement(By.id("password")).sendKeys("dolibarr");
    driver.findElement(By.id("password")).sendKeys(Keys.ENTER);
    driver.findElement(By.cssSelector(".menu")).click();
    driver.findElement(By.linkText("Impostazioni")).click();
    driver.findElement(By.cssSelector(".menu")).click();
    driver.findElement(By.linkText("Moduli/Applicazioni")).click();
    driver.findElement(By.name("buttonreset")).click();
    driver.findElement(By.id("search_keyword")).sendKeys("ferie");
    driver.findElement(By.id("search_keyword")).sendKeys(Keys.ENTER);
    driver.findElement(By.cssSelector(".fa-toggle-on")).click();
    driver.findElement(By.cssSelector(".reposition > .fas")).click();
    //Eliminazione di un bottone
    driver.findElement(By.name("buttonreset")).click();
    driver.findElement(By.xpath("//form[@id=\'searchFormList\']/div[2]/table/tbody/tr/td/div")).click();
    driver.findElement(By.xpath("//form[@id=\'searchFormList\']/div[2]/div/div[2]/span")).click();
  }
}
