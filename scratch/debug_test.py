# scratch/debug_test.py
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

def debug():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    
    driver = webdriver.Chrome(options=options)
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file:///" + file_path.replace("\\", "/")
    
    try:
        driver.get(url)
        time.sleep(2)
        
        # Go through the discovery steps to trigger updates
        driver.execute_script("arguments[0].click();", driver.find_element(By.CSS_SELECTOR, '.discovery-appliance-card[data-id="window_ac"]'))
        time.sleep(0.1)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-goto-step2"))
        time.sleep(0.1)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-goto-step3"))
        time.sleep(0.1)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-goto-step4"))
        time.sleep(0.1)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-goto-step5"))
        time.sleep(0.1)
        bill_slider = driver.find_element(By.ID, "discovery-bill-slider")
        driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
        time.sleep(0.3)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-calibrate-bill"))
        time.sleep(1.5) # Wait for calibration
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "btn-goto-step7"))
        time.sleep(1.5) # Wait for dashboard animation
        
        el = driver.find_element(By.ID, "score-suitability-label")
        print("--- DEBUG SUITABILITY LABEL ---")
        print("innerHTML:", el.get_attribute("innerHTML"))
        print("outerHTML:", el.get_attribute("outerHTML"))
        print("is_displayed:", el.is_displayed())
        print("text:", el.text)
        print("parent display:", el.find_element(By.XPATH, "..").value_of_css_property("display"))
        print("parent parent display:", el.find_element(By.XPATH, "../..").value_of_css_property("display"))
        print("parent parent parent display:", el.find_element(By.XPATH, "../../..").value_of_css_property("display"))
        
        print("\n--- CONSOLE LOGS ---")
        for entry in driver.get_log('browser'):
            print(entry)
            
    except Exception as e:
        print("Error:", e)
    finally:
        driver.quit()

if __name__ == "__main__":
    debug()
