# check_console_logs.py
import os
from selenium import webdriver
from selenium.webdriver.common.by import By

options = webdriver.ChromeOptions()
options.add_argument("--headless")
options.add_argument("--disable-gpu")
options.add_argument("--no-sandbox")
options.add_argument("--disable-dev-shm-usage")

driver = webdriver.Chrome(options=options)
try:
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file:///" + file_path.replace("\\", "/")
    
    driver.get(url)
    import time
    time.sleep(2)
    
    print("=== Console Logs ===")
    for entry in driver.get_log('browser'):
        print(entry)
        
    print("\n=== Elements Status ===")
    try:
        prop_type = driver.find_element(By.ID, "property-type")
        print("property-type value:", prop_type.get_attribute("value"))
    except Exception as e:
        print("No property-type:", e)
        
    try:
        journey = driver.find_element(By.ID, "residential-discovery-journey")
        print("journey displayed:", journey.is_displayed())
        print("journey style display:", driver.execute_script("return window.getComputedStyle(arguments[0]).display;", journey))
    except Exception as e:
        print("No journey:", e)
        
finally:
    driver.quit()
