# scratch/capture_all_viewports.py
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

ARTIFACT_DIR = r"C:\Users\Dell\.gemini\antigravity-ide\brain\53d2ffde-1bf0-4679-b33f-d8d4aafb1a98"

viewports = {
    "desktop": (1440, 900),
    "tablet": (768, 1024),
    "mobile_414": (414, 896),
    "mobile_390": (390, 844),
    "mobile_375": (375, 812),
    "mobile_320": (320, 568)
}

def capture():
    print("Starting screenshot automation across all viewports and languages...")
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    os.makedirs(ARTIFACT_DIR, exist_ok=True)
    cwd = os.getcwd()
    
    languages = {
        "en": "preview.html",
        "ar": "preview_ar.html"
    }
    
    for lang, filename in languages.items():
        file_path = os.path.join(cwd, filename)
        url = "file:///" + file_path.replace("\\", "/")
        
        for vp_name, (width, height) in viewports.items():
            print(f"Processing language: {lang}, viewport: {vp_name} ({width}x{height})...")
            driver = webdriver.Chrome(options=options)
            driver.set_window_size(width, height)
            
            try:
                driver.get(url)
                time.sleep(1.5)
                
                # Step 1
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_1_{lang}_{vp_name}.png"))
                
                # Navigate to Step 2
                ac_card = driver.find_element(By.CSS_SELECTOR, '.discovery-appliance-card[data-id="window_ac"]')
                driver.execute_script("arguments[0].click();", ac_card)
                time.sleep(0.1)
                next_btn = driver.find_element(By.ID, "btn-goto-step2")
                driver.execute_script("arguments[0].click();", next_btn)
                time.sleep(0.5)
                
                # Step 2
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_2_{lang}_{vp_name}.png"))
                
                # Navigate to Step 3
                plus_btn = driver.find_element(By.CSS_SELECTOR, '.disc-qty-btn.plus[data-id="window_ac"]')
                driver.execute_script("arguments[0].click();", plus_btn)
                time.sleep(0.1)
                next_btn = driver.find_element(By.ID, "btn-goto-step3")
                driver.execute_script("arguments[0].click();", next_btn)
                time.sleep(0.5)
                
                # Step 3
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_3_{lang}_{vp_name}.png"))
                
                # Navigate to Step 4
                next_btn = driver.find_element(By.ID, "btn-goto-step4")
                driver.execute_script("arguments[0].click();", next_btn)
                time.sleep(0.5)
                
                # Step 4
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_4_{lang}_{vp_name}.png"))
                
                # Navigate to Step 5
                next_btn = driver.find_element(By.ID, "btn-goto-step5")
                driver.execute_script("arguments[0].click();", next_btn)
                time.sleep(0.5)
                
                # Step 5
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_5_{lang}_{vp_name}.png"))
                
                # Navigate to Step 6
                bill_slider = driver.find_element(By.ID, "discovery-bill-slider")
                driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
                time.sleep(0.2)
                calibrate_btn = driver.find_element(By.ID, "btn-calibrate-bill")
                driver.execute_script("arguments[0].click();", calibrate_btn)
                time.sleep(1.8) # Wait for calibration scan simulation to finish
                
                # Step 6
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_6_{lang}_{vp_name}.png"))
                
                # Navigate to Step 7
                next_btn = driver.find_element(By.ID, "btn-goto-step7")
                driver.execute_script("arguments[0].click();", next_btn)
                time.sleep(1.8) # Wait for final dashboard animation
                
                # Step 7
                driver.save_screenshot(os.path.join(ARTIFACT_DIR, f"step_7_{lang}_{vp_name}.png"))
                
            except Exception as e:
                print(f"Error processing {lang} - {vp_name}: {e}")
            finally:
                driver.quit()
                
    print("Screenshot automation completed!")

if __name__ == "__main__":
    capture()
