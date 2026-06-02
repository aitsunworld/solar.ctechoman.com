"""Focused Quantity Button Click Test — verifies single-increment behavior"""
import os, time, json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

ARTIFACT_DIR = r"C:\Users\Sagar\.gemini\antigravity-ide\brain\a6d54b4f-6860-4fb1-8499-2a6a2b6ba3ca"

def run_qty_test():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    driver.set_window_size(1920, 1080)
    
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file:///" + file_path.replace("\\", "/")
    
    results = {}
    
    try:
        driver.get(url)
        time.sleep(3)  # Wait for JS initialization
        
        # 1. Verify Appliance Auditor is now active
        app_tab = driver.find_element(By.ID, "tab-appliances")
        results["tab_appliances_active"] = "active" in app_tab.get_attribute("class")
        
        # 2. Scroll to calculator section and wait
        calc = driver.find_element(By.ID, "calculator")
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", calc)
        time.sleep(2)
        
        # 3. Check that appliance cards are rendered
        cards = driver.find_elements(By.CSS_SELECTOR, ".appliance-item")
        results["visible_appliance_cards"] = len(cards)
        
        # 4. Find all qty-btn.plus buttons that are visible
        plus_btns = driver.find_elements(By.CSS_SELECTOR, ".qty-btn.plus")
        results["total_plus_buttons"] = len(plus_btns)
        
        if len(plus_btns) == 0:
            results["verdict"] = "FAIL - No plus buttons found"
            print(json.dumps(results, indent=2))
            return
        
        # 5. Scroll to and test the first VISIBLE plus button
        first_plus = plus_btns[0]
        app_id = first_plus.get_attribute("data-id")
        results["test_appliance_id"] = app_id
        
        # Scroll the button into view
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", first_plus)
        time.sleep(0.5)
        
        # Screenshot before click
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "qty_before_click.png"))
        
        # Read qty before
        qty_el = driver.find_element(By.ID, f"qty-{app_id}")
        qty_before = int(qty_el.text)
        results["qty_before"] = qty_before
        
        # 6. Click ONCE using JS to avoid intercept issues
        driver.execute_script("arguments[0].click();", first_plus)
        time.sleep(0.5)
        
        qty_after_1 = int(driver.find_element(By.ID, f"qty-{app_id}").text)
        results["qty_after_1_click"] = qty_after_1
        results["first_click_incremented_by"] = qty_after_1 - qty_before
        
        # Screenshot after first click
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "qty_after_1_click.png"))
        
        # 7. Click AGAIN
        driver.execute_script("arguments[0].click();", first_plus)
        time.sleep(0.5)
        
        qty_after_2 = int(driver.find_element(By.ID, f"qty-{app_id}").text)
        results["qty_after_2_clicks"] = qty_after_2
        results["second_click_incremented_by"] = qty_after_2 - qty_after_1
        
        # Screenshot after second click
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "qty_after_2_clicks.png"))
        
        # 8. Click MINUS to decrement
        minus_btn = driver.find_element(By.CSS_SELECTOR, f".qty-btn.minus[data-id='{app_id}']")
        driver.execute_script("arguments[0].click();", minus_btn)
        time.sleep(0.5)
        
        qty_after_minus = int(driver.find_element(By.ID, f"qty-{app_id}").text)
        results["qty_after_minus"] = qty_after_minus
        results["minus_decremented_by"] = qty_after_2 - qty_after_minus
        
        # 9. Rapid-fire test: click + 5 times quickly
        for i in range(5):
            driver.execute_script("arguments[0].click();", first_plus)
        time.sleep(1)
        
        qty_after_rapid = int(driver.find_element(By.ID, f"qty-{app_id}").text)
        results["qty_after_5_rapid_clicks"] = qty_after_rapid
        expected_after_rapid = qty_after_minus + 5
        results["expected_after_rapid"] = expected_after_rapid
        results["rapid_fire_correct"] = qty_after_rapid == expected_after_rapid
        
        # Screenshot after rapid fire
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "qty_after_rapid_fire.png"))
        
        # 10. Final verdict
        all_correct = (
            results["first_click_incremented_by"] == 1 and
            results["second_click_incremented_by"] == 1 and
            results["minus_decremented_by"] == 1 and
            results["rapid_fire_correct"]
        )
        results["verdict"] = "PASS ✅ — All qty operations increment/decrement by exactly 1" if all_correct else "FAIL ❌ — Unexpected qty changes detected"
        
    except Exception as e:
        results["error"] = str(e)
        results["verdict"] = f"ERROR — {str(e)}"
    finally:
        driver.quit()
    
    # Save results
    results_path = os.path.join(ARTIFACT_DIR, "qty_test_results.json")
    with open(results_path, "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2)
    
    print("=== QTY CLICK TEST RESULTS ===")
    print(json.dumps(results, indent=2))

if __name__ == "__main__":
    run_qty_test()
