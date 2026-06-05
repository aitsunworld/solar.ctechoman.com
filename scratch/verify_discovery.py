# verify_discovery.py
import os
import time
import json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

ARTIFACT_DIR = r"C:\Users\Dell\.gemini\antigravity-ide\brain\53d2ffde-1bf0-4679-b33f-d8d4aafb1a98"

def run_verification():
    print("Starting automated verification of Residential Solar Discovery Experience...")
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file:///" + file_path.replace("\\", "/")
    
    os.makedirs(ARTIFACT_DIR, exist_ok=True)
    report = []
    
    try:
        driver.set_window_size(1280, 1024)
        driver.get(url)
        time.sleep(2)
        
        # 1. Verify Sizer switches to Discovery Journey when property-type is residential
        prop_type = driver.find_element(By.ID, "property-type")
        is_res_default = prop_type.get_attribute("value") == "residential"
        
        journey_container = driver.find_element(By.ID, "residential-discovery-journey")
        is_journey_visible = journey_container.is_displayed()
        
        report.append({
            "check": "Residential Discovery initialized automatically",
            "passed": is_res_default and is_journey_visible
        })
        print(f"Residential Discovery initialized: {is_journey_visible}")

        # Capture initial state
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_1_selection.png"))

        # 2. Select appliances in Step 1
        # Click on "Window AC" card (data-id="window_ac") which is not selected by default
        ac_card = driver.find_element(By.CSS_SELECTOR, '.discovery-appliance-card[data-id="window_ac"]')
        driver.execute_script("arguments[0].click();", ac_card)
        time.sleep(0.3)
        
        ac_selected = "selected" in ac_card.get_attribute("class")
        report.append({
            "check": "Appliance selection click registers",
            "passed": ac_selected
        })
        print(f"Window AC Card selected: {ac_selected}")
        
        # Click next button
        next_btn = driver.find_element(By.ID, "btn-goto-step2")
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(0.5)
        
        # 3. Verify Step 2 (Enter Qty) is active
        panel2 = driver.find_element(By.ID, "discovery-panel-2")
        p2_visible = panel2.is_displayed()
        report.append({
            "check": "Navigate to Step 2 (Quantities)",
            "passed": p2_visible
        })
        print(f"Panel 2 visible: {p2_visible}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_2_quantities.png"))

        # 4. Check Qty selector increments by exactly 1
        qty_val = driver.find_element(By.ID, "disc-qty-val-window_ac")
        qty_before = int(qty_val.text)
        
        plus_btn = driver.find_element(By.CSS_SELECTOR, '.disc-qty-btn.plus[data-id="window_ac"]')
        driver.execute_script("arguments[0].click();", plus_btn)
        time.sleep(0.2)
        qty_after_1 = int(qty_val.text)
        
        driver.execute_script("arguments[0].click();", plus_btn)
        time.sleep(0.2)
        qty_after_2 = int(qty_val.text)
        
        increments_by_1 = (qty_after_1 == qty_before + 1) and (qty_after_2 == qty_before + 2)
        report.append({
            "check": "Quantity increments by exactly 1 per click",
            "passed": increments_by_1
        })
        print(f"Quantity before: {qty_before}, after 1 click: {qty_after_1}, after 2 clicks: {qty_after_2}")

        # 5. Navigate to Step 3 (Consumption)
        goto3_btn = driver.find_element(By.ID, "btn-goto-step3")
        driver.execute_script("arguments[0].click();", goto3_btn)
        time.sleep(0.5)
        
        panel3 = driver.find_element(By.ID, "discovery-panel-3")
        p3_visible = panel3.is_displayed()
        reveal_consumption = driver.find_element(By.ID, "reveal-consumption-value").text
        
        report.append({
            "check": "Navigate to Step 3 (Consumption reveal)",
            "passed": p3_visible and "kWh" in reveal_consumption
        })
        print(f"Consumption calculated: {reveal_consumption}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_3_consumption.png"))

        # 6. Navigate to Step 4 (Recommended Solar)
        goto4_btn = driver.find_element(By.ID, "btn-goto-step4")
        driver.execute_script("arguments[0].click();", goto4_btn)
        time.sleep(0.5)
        
        panel4 = driver.find_element(By.ID, "discovery-panel-4")
        p4_visible = panel4.is_displayed()
        reveal_kw = driver.find_element(By.ID, "reveal-solar-kw").text
        
        report.append({
            "check": "Navigate to Step 4 (Solar capacity recommendation)",
            "passed": p4_visible and "kW" in reveal_kw
        })
        print(f"Solar system capacity calculated: {reveal_kw}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_4_solar_size.png"))

        # 7. Navigate to Step 5 (Bill entry)
        goto5_btn = driver.find_element(By.ID, "btn-goto-step5")
        driver.execute_script("arguments[0].click();", goto5_btn)
        time.sleep(0.5)
        
        panel5 = driver.find_element(By.ID, "discovery-panel-5")
        p5_visible = panel5.is_displayed()
        report.append({
            "check": "Navigate to Step 5 (Bill input slider)",
            "passed": p5_visible
        })
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_5_bill_input.png"))

        # 8. Enter a deviating bill manually to test calibration warning (>15%)
        # Let's set the slider value to a very high bill (e.g. 500 OMR) so that it deviates >15%
        bill_slider = driver.find_element(By.ID, "discovery-bill-slider")
        driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
        time.sleep(0.3)
        
        # Click enter bill manually (triggers calibration)
        enter_bill_btn = driver.find_element(By.ID, "btn-calibrate-bill")
        driver.execute_script("arguments[0].click();", enter_bill_btn)
        time.sleep(1.5) # Wait for simulation delay (1000ms)
        
        panel6 = driver.find_element(By.ID, "discovery-panel-6")
        warning_box = driver.find_element(By.ID, "calibration-warning-box")
        warning_displayed = warning_box.is_displayed()
        
        report.append({
            "check": "Calibration logic warning triggers on >15% variance",
            "passed": panel6.is_displayed() and warning_displayed
        })
        print(f"Calibration warning displayed: {warning_displayed}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_6_calibration.png"))

        # 9. Navigate to Step 7 (Reveal final dashboard and lead capture)
        goto7_btn = driver.find_element(By.ID, "btn-goto-step7")
        driver.execute_script("arguments[0].click();", goto7_btn)
        time.sleep(1.2)
        
        panel7 = driver.find_element(By.ID, "discovery-panel-7")
        lead_form = driver.find_element(By.ID, "discovery-lead-form")
        dashboard = driver.find_element(By.ID, "residential-discovery-results")
        
        report.append({
            "check": "Step 7 reveals final dashboard and assessment lead form",
            "passed": panel7.is_displayed() and lead_form.is_displayed() and dashboard.is_displayed()
        })
        print(f"Dashboard revealed: {dashboard.is_displayed()}, Lead form revealed: {lead_form.is_displayed()}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_7_dashboard.png"))

        # 10. Check gamification score updates
        energy_score = driver.find_element(By.ID, "score-energy-label").text
        suitability_score = driver.find_element(By.ID, "score-suitability-label").text
        co2_val = driver.find_element(By.ID, "score-co2-val").text
        trees_val = driver.find_element(By.ID, "score-trees-val").text
        
        report.append({
            "check": "Gamification scores calculated and rendered",
            "passed": len(energy_score) > 0 and len(suitability_score) > 0 and len(co2_val) > 0 and len(trees_val) > 0
        })
        print(f"Energy Independence: {energy_score}")
        print(f"Solar Suitability: {suitability_score}")
        print(f"Green Impact: {co2_val} avoided, {trees_val} trees equivalent")

        # 11. Verify Commercial mode restores standard tabs and layout
        driver.execute_script("arguments[0].value = 'commercial'; arguments[0].dispatchEvent(new Event('change'));", prop_type)
        time.sleep(0.5)
        
        standard_inputs = driver.find_element(By.ID, "standard-calc-inputs")
        standard_results = driver.find_element(By.ID, "standard-calc-results")
        is_journey_hidden = not journey_container.is_displayed()
        
        report.append({
            "check": "Switching property type restores standard sizer for Commercial/Industrial",
            "passed": standard_inputs.is_displayed() and standard_results.is_displayed() and is_journey_hidden
        })
        print(f"Commercial Sizer restored: {standard_inputs.is_displayed()}")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "commercial_sizer_restored.png"))

    except Exception as e:
        report.append({
            "check": "Fatal verification error",
            "passed": False,
            "error": str(e)
        })
        print(f"Error during verification: {e}")
        
    finally:
        driver.quit()

    # Save verification report
    report_path = os.path.join(ARTIFACT_DIR, "verify_discovery_report.json")
    with open(report_path, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2)
        
    print("\n=== VERIFICATION RESULTS ===")
    for item in report:
        status = "PASS" if item["passed"] else "FAIL"
        print(f"[{status}] {item['check']}")
        if "error" in item:
            print(f"      Error: {item['error']}")
            
    print(f"\nReport written to: {report_path}")

if __name__ == "__main__":
    run_verification()
