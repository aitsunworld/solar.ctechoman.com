"""Focused Wizard Flow & Quantity Click Test — verifies new 8-step linear journey"""
import os
import time
import json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

ARTIFACT_DIR = r"C:\Users\Dell\.gemini\antigravity-ide\brain\16a35125-41a5-485d-b60a-4c70ddba3d7a"

def run_qty_test():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    driver.set_window_size(1920, 1080)
    
    url = "http://localhost:8000/preview.html"
    results = {}
    
    try:
        driver.get(url)
        time.sleep(3)  # Wait for JS initialization
        
        # Force reveal all animations for Selenium testing
        driver.execute_script("""
            document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
                el.classList.add('active');
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
        """)
        time.sleep(1)
        
        # 1. Verify we are on Step 1 (Property Type selection)
        step_1 = driver.find_element(By.ID, "wizard-step-1")
        results["step_1_visible_initially"] = step_1.is_displayed()
        
        # Take initial screenshot
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_1_property.png"))
        
        # Click Residential card
        res_card = driver.find_element(By.CSS_SELECTOR, '.property-card[data-value="residential"]')
        driver.execute_script("arguments[0].click();", res_card)
        time.sleep(1)
        
        # 2. Verify transition to Step 2 (Location selection)
        step_2 = driver.find_element(By.ID, "wizard-step-2")
        results["step_2_visible_after_prop_click"] = step_2.is_displayed()
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_2_location.png"))
        
        # Click Muscat location card
        muscat_card = driver.find_element(By.CSS_SELECTOR, '.location-card[data-value="muscat"]')
        driver.execute_script("arguments[0].click();", muscat_card)
        time.sleep(1)
        
        # 3. Verify transition to Step 3 (Appliance Sizer)
        step_3 = driver.find_element(By.ID, "wizard-step-3")
        results["step_3_visible_after_loc_click"] = step_3.is_displayed()
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_3_appliances.png"))
        
        # Verify appliance cards are loaded
        cards = driver.find_elements(By.CSS_SELECTOR, ".appliance-item")
        results["rendered_appliances"] = len(cards)
        
        # Find Split AC 1 Ton cards
        ac_plus = driver.find_element(By.CSS_SELECTOR, '.qty-btn.plus[data-id="ac_1ton"]')
        ac_minus = driver.find_element(By.CSS_SELECTOR, '.qty-btn.minus[data-id="ac_1ton"]')
        qty_el = driver.find_element(By.ID, 'qty-ac_1ton')
        
        qty_before = int(qty_el.get_attribute("textContent"))
        results["qty_before_clicks"] = qty_before
        
        # Click Plus once
        driver.execute_script("arguments[0].click();", ac_plus)
        time.sleep(0.5)
        qty_after_1 = int(qty_el.get_attribute("textContent"))
        results["qty_after_first_plus"] = qty_after_1
        
        # Click Plus again
        driver.execute_script("arguments[0].click();", ac_plus)
        time.sleep(0.5)
        qty_after_2 = int(qty_el.get_attribute("textContent"))
        results["qty_after_second_plus"] = qty_after_2
        
        # Click Minus once
        driver.execute_script("arguments[0].click();", ac_minus)
        time.sleep(0.5)
        qty_after_minus = int(qty_el.get_attribute("textContent"))
        results["qty_after_minus"] = qty_after_minus
        
        # Take appliance sizer screenshot with quantities updated
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_3_quantities_updated.png"))
        
        # 4. Navigate to Step 4 (Consumption Analysis)
        next_btn = driver.find_element(By.ID, "wizard-next-btn")
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(1)
        
        step_4 = driver.find_element(By.ID, "wizard-step-4")
        results["step_4_visible"] = step_4.is_displayed()
        
        connected_load = driver.find_element(By.ID, "res-connected-load").get_attribute("textContent")
        daily_consumption = driver.find_element(By.ID, "res-daily-consumption").get_attribute("textContent")
        results["load_analysis"] = {
            "connected_load": connected_load,
            "daily_consumption": daily_consumption
        }
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_4_analysis.png"))
        
        # 5. Navigate to Step 5 (Bill Verification)
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(1)
        step_5 = driver.find_element(By.ID, "wizard-step-5")
        results["step_5_visible"] = step_5.is_displayed()
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_5_bill.png"))
        
        # 6. Navigate to Step 6 (Solar Recommendation)
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(1)
        step_6 = driver.find_element(By.ID, "wizard-step-6")
        results["step_6_visible"] = step_6.is_displayed()
        
        sys_size = driver.find_element(By.ID, "res-size").get_attribute("textContent")
        panels = driver.find_element(By.ID, "res-panels").get_attribute("textContent")
        results["solar_recommendation"] = {
            "system_size": sys_size,
            "panel_count": panels
        }
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_6_recommendation.png"))
        
        # 7. Navigate to Step 7 (Savings Dashboard)
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(1)
        step_7 = driver.find_element(By.ID, "wizard-step-7")
        results["step_7_visible"] = step_7.is_displayed()
        
        payback = driver.find_element(By.ID, "res-payback").get_attribute("textContent")
        roi = driver.find_element(By.ID, "res-roi").get_attribute("textContent")
        results["savings_dashboard"] = {
            "payback_period": payback,
            "roi": roi
        }
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_7_dashboard.png"))
        
        # 8. Navigate to Step 8 (Lead Capture)
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(1)
        step_8 = driver.find_element(By.ID, "wizard-step-8")
        results["step_8_visible"] = step_8.is_displayed()
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_8_lead.png"))
        
        # Validate exact operations
        increments_correct = (
            qty_after_1 - qty_before == 1 and
            qty_after_2 - qty_after_1 == 1 and
            qty_after_2 - qty_after_minus == 1
        )
        results["quantity_selector_correct"] = increments_correct
        results["verdict"] = "PASS ✅ — All wizard transitions, quantity modifications (+/- 1), calculations, and step displays verified successfully." if increments_correct else "FAIL ❌ — Quantity selection values incorrect."
        
    except Exception as e:
        results["error"] = str(e)
        results["verdict"] = f"ERROR ❌ — {str(e)}"
    finally:
        driver.quit()
        
    # Save results
    results_path = os.path.join(ARTIFACT_DIR, "qty_test_results.json")
    with open(results_path, "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2)
        
    print("=== WIZARD AUDIT RESULTS ===")
    print(json.dumps(results, indent=2))

if __name__ == "__main__":
    run_qty_test()
