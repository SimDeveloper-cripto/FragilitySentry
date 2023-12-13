package JUnit.MantisBT; // Generated by Selenium IDE

import org.junit.Test;
import org.junit.After;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;

public class ReadAdminInfo1Test {
  private  WebDriver driver = new ChromeDriver();
  JavascriptExecutor js = (JavascriptExecutor) driver;

  public void setUp(WebDriver driver) {
    this.driver.quit();
    this.driver=driver;
    js = (JavascriptExecutor) driver;
  }

  @After
  public void tearDown() {
    driver.quit();
  }

  @Test
  public void readAdminInfo1() {
    driver.get("http://localhost:8989/login_page.php");
    driver.manage().window().setSize(new Dimension(945, 1020));

    driver.findElement(By.name("username")).click();
    driver.findElement(By.name("username")).sendKeys("Lory");
    driver.findElement(By.cssSelector(".width-40")).click();
    driver.findElement(By.name("password")).click();
    driver.findElement(By.name("password")).sendKeys("root");
    driver.findElement(By.name("password")).sendKeys(Keys.ENTER);

    driver.findElement(By.linkText("Glitch grafico")).click(); // Old Selector: "Glitch grafico che prima non si verificava"
    driver.findElement(By.linkText("administrator")).click();

    driver.findElement(By.cssSelector(".padding-8")).click();
    driver.findElement(By.cssSelector("td")).click();

    driver.findElement(By.xpath("//div[@id=\"main-container\"]/div[5]/div/div/div/address/small")).click();
    driver.findElement(By.xpath("//div[@id=\"main-container\"]/div[5]/div/div/div/address/small[2]")).click();

    driver.close();
  }
}