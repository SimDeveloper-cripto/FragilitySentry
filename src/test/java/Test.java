import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.BufferedReader;

import java.util.List;
import java.util.ArrayList;

public class Test {
    private final String className;
    private List<Selector> selectors;
    private List<Page> pages;
    private double testScore;
    private final boolean laterGetSuccess;

    public Test(String className) {
        this.className = className;
        this.testScore = 0;
        this.laterGetSuccess = getStatusResult(this.getFullTestName(),5);
    }

    @Override
    public String toString() {
        return "Test {" + "className = '" + className + '\'' + ", testScore = " + getTestScore() + ", laterGetSuccess = " + laterGetSuccess + '}';
    }

    private boolean getStatusResult(String testName, int column) {
        // Questo metodo prende il file Result.csv dell'applicativo che si sta utilizzando e restituisce lo status del test preso in ingresso per
        // le versioni successive

        boolean status = false;
        String directory = "src/test/java/XMLResult/" + JUnitRunner.SoftwareUsed + "/Result.csv";

        try (BufferedReader br = new BufferedReader(new FileReader(directory))) {
            String line;
            while ((line = br.readLine()) != null) {
                String[] values = line.split(",");
                String currentTestName = values[0]; // Test's name first column

                if (currentTestName.equals(TestRunner.decapitalize(testName))) {
                    String statusString = values[column];
                    if(statusString.equals("passed")) status = true;
                    break;
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }

        return status;
    }

    public static List<Test> getAllTests(String directory) {
        List<Test> listOfTests = new ArrayList<>();
        List<String> fileNames = getFileNames(directory);

        for (String className : fileNames) {
            Test newTest = new Test(className);
            listOfTests.add(newTest);
        }
        return listOfTests;
    }

    public static List<String> getFileNames(String directoryPath) {
        List<String> fileNames = new ArrayList<>();
        File directory = new File(directoryPath);

        if (directory.exists() && directory.isDirectory()) {
            File[] files = directory.listFiles();
            if (files != null) {
                for (File file : files) {
                    if (file.isFile()) {
                        fileNames.add(directoryPath + "/" + file.getName());
                    }
                }
            }
        }
        return fileNames;
    }

    public String getClassName() {
        return this.className;
    }

    public String getTestName() {
        int lastIndex = className.lastIndexOf("/");
        int endIndex  = className.lastIndexOf(".java");

        if (lastIndex >= 0 && lastIndex < className.length() - 1) {
            if (endIndex > lastIndex) {
                return className.substring(lastIndex + 1, endIndex);
            } else {
                return className.substring(lastIndex + 1);
            }
        } else {
            return className;
        }
    }

    public String getFullTestName() {
        int lastIndex = className.lastIndexOf("/");
        int endIndex = className.lastIndexOf("Test.java");

        if (lastIndex >= 0 && lastIndex < className.length() - 1) {
            if (endIndex > lastIndex) {
                return className.substring(lastIndex + 1, endIndex);
            } else {
                return className.substring(lastIndex + 1);
            }
        } else {
            return className;
        }
    }

    public double getTestScore() {
        return this.testScore;
    }

    public void setTestScore(double testScore) {
        this.testScore = testScore;
    }

    public List<Selector> getSelectors() {
        return this.selectors;
    }

    public void setSelectors(List<Selector> selectors) {
        this.selectors = selectors;
    }

    public List<Page> getPages() {
        return this.pages;
    }

    public void setPages(List<Page> page) {
        this.pages = page;
    }

    public boolean isLaterGetSuccess() {
        return this.laterGetSuccess;
    }
}