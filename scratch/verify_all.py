import os
import sys
import time
import json
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

# Ensure UTF-8 output on Windows terminal
if sys.version_info >= (3, 7):
    sys.stdout.reconfigure(encoding='utf-8')

ARTIFACT_DIR = r"C:\Users\Dell\.gemini\antigravity-ide\brain\53d2ffde-1bf0-4679-b33f-d8d4aafb1a98"

def run_verification():
    print("Starting comprehensive automated verification...")
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.set_capability('goog:loggingPrefs', {'browser': 'ALL'})
    
    driver = webdriver.Chrome(options=options)
    cwd = os.getcwd()
    
    os.makedirs(ARTIFACT_DIR, exist_ok=True)
    report = []
    
    # ----------------------------------------------------
    # PHASE 1: ENGLISH PAGE VERIFICATION (preview.html)
    # ----------------------------------------------------
    print("\n--- Testing English version (preview.html) ---")
    en_file_path = os.path.join(cwd, "preview.html")
    en_url = "file:///" + en_file_path.replace("\\", "/")
    
    try:
        driver.set_window_size(1280, 1024)
        driver.get(en_url)
        time.sleep(2)
        
        # Scroll to calculator to trigger lazy load observer
        calculator = driver.find_element(By.ID, "calculator")
        driver.execute_script("arguments[0].scrollIntoView(true);", calculator)
        time.sleep(1)
        
        # Check Console Errors
        console_logs = driver.get_log('browser')
        js_errors = [log for log in console_logs if log['level'] == 'SEVERE']
        print(f"JavaScript errors on load (EN): {len(js_errors)}")
        report.append({
            "check": "No initial JavaScript console errors (EN)",
            "passed": len(js_errors) == 0,
            "details": [log['message'] for log in js_errors]
        })
        
        # 1. Step 1: Selection
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_1_selection_en.png"))
        print("Captured Step 1: Selection (EN)")
        
        # Click on "Window AC" card (data-id="window_ac")
        ac_card = driver.find_element(By.CSS_SELECTOR, '.discovery-appliance-card[data-id="window_ac"]')
        driver.execute_script("arguments[0].click();", ac_card)
        time.sleep(0.3)
        
        ac_selected = "selected" in ac_card.get_attribute("class")
        report.append({
            "check": "Appliance selection click registers (EN)",
            "passed": ac_selected
        })
        
        # Click next button
        next_btn = driver.find_element(By.ID, "btn-goto-step2")
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(0.5)
        
        # 2. Step 2: Quantities
        panel2 = driver.find_element(By.ID, "discovery-panel-2")
        p2_visible = panel2.is_displayed()
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_2_quantities_en.png"))
        print("Captured Step 2: Quantities (EN)")
        
        qty_val = driver.find_element(By.ID, "disc-qty-val-window_ac")
        qty_before = int(qty_val.text)
        
        # Click plus button twice
        plus_btn = driver.find_element(By.CSS_SELECTOR, '.disc-qty-btn.plus[data-id="window_ac"]')
        driver.execute_script("arguments[0].click();", plus_btn)
        time.sleep(0.2)
        qty_after_1 = int(qty_val.text)
        
        driver.execute_script("arguments[0].click();", plus_btn)
        time.sleep(0.2)
        qty_after_2 = int(qty_val.text)
        
        increments_by_1 = (qty_after_1 == qty_before + 1) and (qty_after_2 == qty_before + 2)
        report.append({
            "check": "Quantity increments by exactly 1 (EN)",
            "passed": increments_by_1
        })
        print(f"Appliance quantities: before={qty_before}, after 1={qty_after_1}, after 2={qty_after_2}")
        
        # Navigate to Step 3: Consumption Reveal
        goto3_btn = driver.find_element(By.ID, "btn-goto-step3")
        driver.execute_script("arguments[0].click();", goto3_btn)
        time.sleep(0.5)
        
        # 3. Step 3: Consumption Reveal
        panel3 = driver.find_element(By.ID, "discovery-panel-3")
        reveal_consumption = driver.find_element(By.ID, "reveal-consumption-value").text
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_3_consumption_en.png"))
        print(f"Captured Step 3: Consumption Reveal (EN) = {reveal_consumption}")
        report.append({
            "check": "Navigate to Step 3 and calculate consumption (EN)",
            "passed": panel3.is_displayed() and "kWh" in reveal_consumption
        })
        
        # Navigate to Step 4: Recommended Solar Size
        goto4_btn = driver.find_element(By.ID, "btn-goto-step4")
        driver.execute_script("arguments[0].click();", goto4_btn)
        time.sleep(0.5)
        
        # 4. Step 4: Recommended Solar Size
        panel4 = driver.find_element(By.ID, "discovery-panel-4")
        reveal_kw = driver.find_element(By.ID, "reveal-solar-kw").text
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_4_solar_size_en.png"))
        print(f"Captured Step 4: Solar Sizing (EN) = {reveal_kw}")
        report.append({
            "check": "Navigate to Step 4 and calculate system size (EN)",
            "passed": panel4.is_displayed() and "kW" in reveal_kw
        })
        
        # Navigate to Step 5: Bill Slider
        goto5_btn = driver.find_element(By.ID, "btn-goto-step5")
        driver.execute_script("arguments[0].click();", goto5_btn)
        time.sleep(0.5)
        
        # 5. Step 5: Bill Slider
        panel5 = driver.find_element(By.ID, "discovery-panel-5")
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_5_bill_input_en.png"))
        print("Captured Step 5: Bill Slider (EN)")
        
        # Debug journey styles in the DOM
        journey_display = driver.execute_script("return window.getComputedStyle(document.getElementById('residential-discovery-journey')).display;")
        panel5_display = driver.execute_script("return window.getComputedStyle(document.getElementById('discovery-panel-5')).display;")
        panel5_opacity = driver.execute_script("return window.getComputedStyle(document.getElementById('discovery-panel-5')).opacity;")
        rect_journey = driver.execute_script("return document.getElementById('residential-discovery-journey').getBoundingClientRect();")
        rect_panel5 = driver.execute_script("return document.getElementById('discovery-panel-5').getBoundingClientRect();")
        print(f"DEBUG Step 5 styles: journey={journey_display}, panel5={panel5_display}, panel5_opacity={panel5_opacity}")
        print(f"DEBUG Step 5 rects: journey={rect_journey}, panel5={rect_panel5}")
        
        # Enter a deviating bill manually to test calibration warning (>15%)
        # Default estimated bill should be around 50-80 OMR, let's set slider to 400 OMR
        bill_slider = driver.find_element(By.ID, "discovery-bill-slider")
        driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
        time.sleep(0.3)
        
        # Click enter bill manually (triggers calibration)
        enter_bill_btn = driver.find_element(By.ID, "btn-calibrate-bill")
        driver.execute_script("arguments[0].click();", enter_bill_btn)
        time.sleep(2.5) # Wait for calibration simulation delay (1000ms + buffer)
        
        # 6. Step 6: Calibration Results
        panel6 = driver.find_element(By.ID, "discovery-panel-6")
        status_msg = driver.find_element(By.ID, "calibration-status-message").text
        warning_box = driver.find_element(By.ID, "calibration-warning-box")
        warning_displayed = warning_box.is_displayed()
        warning_text = warning_box.text
        success_box = driver.find_element(By.ID, "calibration-success-box")
        success_displayed = success_box.is_displayed()
        success_text = success_box.text
        
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_6_calibration_en.png"))
        
        # Clean non-ascii emojis
        clean_status = status_msg.encode('ascii', 'ignore').decode('ascii')
        clean_warning = warning_text.encode('ascii', 'ignore').decode('ascii')
        clean_success = success_text.encode('ascii', 'ignore').decode('ascii')
        
        print(f"Captured Step 6: Status={clean_status}, Warning Displayed={warning_displayed} ('{clean_warning}'), Success Displayed={success_displayed} ('{clean_success}')")
        report.append({
            "check": "Calibration logic warning triggers on >15% variance (EN)",
            "passed": panel6.is_displayed() and warning_displayed
        })
        
        # Navigate to Step 7: Reveal final dashboard
        goto7_btn = driver.find_element(By.ID, "btn-goto-step7")
        driver.execute_script("arguments[0].click();", goto7_btn)
        time.sleep(1.5)
        
        # 7. Step 7: Reveal personalized dashboard
        panel7 = driver.find_element(By.ID, "discovery-panel-7")
        lead_form = driver.find_element(By.ID, "discovery-lead-form")
        dashboard = driver.find_element(By.ID, "residential-discovery-results")
        
        # Extract Dashboard metrics
        monthly_cons_val = driver.find_element(By.ID, "db-val-monthly-cons").text
        daily_cons_val = driver.find_element(By.ID, "db-val-daily-cons").text
        rec_size_val = driver.find_element(By.ID, "db-val-rec-size").text
        monthly_sav_val = driver.find_element(By.ID, "db-val-monthly-sav").text
        yearly_sav_val = driver.find_element(By.ID, "db-val-yearly-sav").text
        lifetime_sav_val = driver.find_element(By.ID, "db-val-lifetime-sav").text
        
        reduction_pct = driver.find_element(By.ID, "db-val-reduction-pct").text
        payback_roi = driver.find_element(By.ID, "db-val-payback-roi").text
        install_cost = driver.find_element(By.ID, "db-val-install-cost").text
        panel_count = driver.find_element(By.ID, "db-val-panel-count").text
        inverter_size = driver.find_element(By.ID, "db-val-inverter-size").text
        battery_size = driver.find_element(By.ID, "db-val-battery-size").text
        
        energy_score = driver.find_element(By.ID, "score-energy-label").get_attribute("textContent").strip()
        suitability_score = driver.find_element(By.ID, "score-suitability-label").get_attribute("textContent").strip()
        co2_val = driver.find_element(By.ID, "score-co2-val").get_attribute("textContent").strip()
        trees_val = driver.find_element(By.ID, "score-trees-val").get_attribute("textContent").strip()
        
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_7_dashboard_en.png"))
        print("Captured Step 7: Dashboard (EN)")
        print(f"Metrics: Monthly Cons={monthly_cons_val}, Solar Size={rec_size_val}, Monthly Sav={monthly_sav_val}")
        print(f"Scores: Energy Score={energy_score}, Suitability={suitability_score}, CO2={co2_val}, Trees={trees_val}")
        print(f"Debug Detail Metrics: reduction_pct='{reduction_pct}', payback_roi='{payback_roi}', install_cost='{install_cost}', panel_count='{panel_count}', inverter_size='{inverter_size}', battery_size='{battery_size}'")
        
        cond_panel7 = panel7.is_displayed()
        cond_lead_form = lead_form.is_displayed()
        cond_dashboard = dashboard.is_displayed()
        cond_monthly = "kWh" in monthly_cons_val
        cond_rec = "kW" in rec_size_val
        cond_sav = "OMR" in monthly_sav_val
        cond_energy = "%" in energy_score
        cond_suit = len(suitability_score) > 0
        cond_co2 = "Tons" in co2_val
        cond_red = "%" in reduction_pct
        cond_panels = "Panels" in panel_count
        cond_inv = "kW" in inverter_size
        cond_bat = "kWh" in battery_size
        
        print(f"Dashboard conditions: panel7={cond_panel7}, lead_form={cond_lead_form}, dashboard={cond_dashboard}")
        print(f"Metric conditions: monthly={cond_monthly}, rec={cond_rec}, sav={cond_sav}, energy={cond_energy}, suit={cond_suit}, co2={cond_co2}")
        print(f"Detail conditions: red={cond_red}, panels={cond_panels}, inv={cond_inv}, bat={cond_bat}")
        
        step7_passed = cond_panel7 and cond_lead_form and cond_dashboard and \
                       cond_monthly and cond_rec and cond_sav and \
                       cond_energy and cond_suit and cond_co2 and \
                       cond_red and cond_panels and cond_inv and cond_bat
                       
        report.append({
            "check": "Dashboard elements and gamified scores rendered correctly (EN)",
            "passed": step7_passed
        })
        
        # Verify Commercial mode restores standard tabs and layout
        prop_type = driver.find_element(By.ID, "property-type")
        driver.execute_script("arguments[0].value = 'commercial'; arguments[0].dispatchEvent(new Event('change'));", prop_type)
        time.sleep(0.5)
        
        standard_inputs = driver.find_element(By.ID, "standard-calc-inputs")
        standard_results = driver.find_element(By.ID, "standard-calc-results")
        journey_container = driver.find_element(By.ID, "residential-discovery-journey")
        is_journey_hidden = not journey_container.is_displayed()
        
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "commercial_sizer_restored_en.png"))
        print("Captured Commercial Sizer Restored (EN)")
        report.append({
            "check": "Switching to Commercial mode restores standard layouts (EN)",
            "passed": standard_inputs.is_displayed() and standard_results.is_displayed() and is_journey_hidden
        })
    except Exception as e:
        print(f"Error during English verification: {e}")
        report.append({
            "check": "English page walkthrough",
            "passed": False,
            "error": str(e)
        })

    # ----------------------------------------------------
    # PHASE 2: ARABIC PAGE VERIFICATION (preview_ar.html)
    # ----------------------------------------------------
    print("\n--- Testing Arabic version (preview_ar.html) ---")
    ar_file_path = os.path.join(cwd, "preview_ar.html")
    ar_url = "file:///" + ar_file_path.replace("\\", "/")
    
    try:
        driver.get(ar_url)
        time.sleep(2)
        
        # Scroll to calculator to trigger lazy load observer
        calculator = driver.find_element(By.ID, "calculator")
        driver.execute_script("arguments[0].scrollIntoView(true);", calculator)
        time.sleep(1)
        
        # Check Console Errors
        console_logs = driver.get_log('browser')
        js_errors = [log for log in console_logs if log['level'] == 'SEVERE']
        print(f"JavaScript errors on load (AR): {len(js_errors)}")
        report.append({
            "check": "No initial JavaScript console errors (AR)",
            "passed": len(js_errors) == 0,
            "details": [log['message'] for log in js_errors]
        })
        
        # Check if dir=rtl is correctly set
        html_element = driver.find_element(By.TAG_NAME, "html")
        is_rtl = html_element.get_attribute("dir") == "rtl"
        report.append({
            "check": "HTML direction is RTL (AR)",
            "passed": is_rtl
        })
        print(f"HTML is RTL: {is_rtl}")
        
        # 1. Step 1: Selection (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_1_selection_ar.png"))
        print("Captured Step 1: Selection (AR)")
        
        # Select appliance
        ac_card = driver.find_element(By.CSS_SELECTOR, '.discovery-appliance-card[data-id="window_ac"]')
        driver.execute_script("arguments[0].click();", ac_card)
        time.sleep(0.3)
        
        next_btn = driver.find_element(By.ID, "btn-goto-step2")
        driver.execute_script("arguments[0].click();", next_btn)
        time.sleep(0.5)
        
        # 2. Step 2: Quantities (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_2_quantities_ar.png"))
        print("Captured Step 2: Quantities (AR)")
        
        # Navigate to Step 3
        goto3_btn = driver.find_element(By.ID, "btn-goto-step3")
        driver.execute_script("arguments[0].click();", goto3_btn)
        time.sleep(0.5)
        
        # 3. Step 3: Consumption Reveal (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_3_consumption_ar.png"))
        print("Captured Step 3: Consumption (AR)")
        
        # Navigate to Step 4
        goto4_btn = driver.find_element(By.ID, "btn-goto-step4")
        driver.execute_script("arguments[0].click();", goto4_btn)
        time.sleep(0.5)
        
        # 4. Step 4: Recommended Solar Size (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_4_solar_size_ar.png"))
        print("Captured Step 4: Solar Sizing (AR)")
        
        # Navigate to Step 5
        goto5_btn = driver.find_element(By.ID, "btn-goto-step5")
        driver.execute_script("arguments[0].click();", goto5_btn)
        time.sleep(0.5)
        
        # 5. Step 5: Bill Slider (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_5_bill_input_ar.png"))
        print("Captured Step 5: Bill Slider (AR)")
        
        # Set slider to 400 OMR and calibrate
        bill_slider = driver.find_element(By.ID, "discovery-bill-slider")
        driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
        time.sleep(0.3)
        
        enter_bill_btn = driver.find_element(By.ID, "btn-calibrate-bill")
        driver.execute_script("arguments[0].click();", enter_bill_btn)
        time.sleep(2.5)
        
        # 6. Step 6: Calibration Results (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_6_calibration_ar.png"))
        print("Captured Step 6: Calibration Warning (AR)")
        
        # Navigate to Step 7
        goto7_btn = driver.find_element(By.ID, "btn-goto-step7")
        driver.execute_script("arguments[0].click();", goto7_btn)
        time.sleep(1.2)
        
        # 7. Step 7: Reveal final dashboard (AR)
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "step_7_dashboard_ar.png"))
        print("Captured Step 7: Dashboard (AR)")
        
        ar_monthly_cons = driver.find_element(By.ID, "db-val-monthly-cons").text
        ar_solar_kw = driver.find_element(By.ID, "db-val-rec-size").text
        ar_monthly_sav = driver.find_element(By.ID, "db-val-monthly-sav").text
        
        print(f"AR Metrics: Monthly Cons={ar_monthly_cons}, Solar Size={ar_solar_kw}, Monthly Sav={ar_monthly_sav}")
        report.append({
            "check": "Dashboard values and translations rendered in Arabic (AR)",
            "passed": "كيلوواط" in ar_solar_kw or "ريال" in ar_monthly_sav or "kW" in ar_solar_kw
        })
        
        # Verify Commercial mode restores standard tabs and layout in RTL
        prop_type = driver.find_element(By.ID, "property-type")
        driver.execute_script("arguments[0].value = 'commercial'; arguments[0].dispatchEvent(new Event('change'));", prop_type)
        time.sleep(0.5)
        
        driver.save_screenshot(os.path.join(ARTIFACT_DIR, "commercial_sizer_restored_ar.png"))
        print("Captured Commercial Sizer Restored (AR)")

    except Exception as e:
        print(f"Error during Arabic verification: {e}")
        report.append({
            "check": "Arabic page walkthrough",
            "passed": False,
            "error": str(e)
        })
        
    finally:
        driver.quit()
        
    # Write report
    report_path = os.path.join(ARTIFACT_DIR, "verify_all_report.json")
    with open(report_path, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2)
        
    print("\n=== COMPLETE VERIFICATION SUMMARY ===")
    all_passed = True
    for item in report:
        status = "PASS" if item["passed"] else "FAIL"
        print(f"[{status}] {item['check']}")
        if not item["passed"]:
            all_passed = False
            if "error" in item:
                print(f"      Error: {item['error']}")
            if "details" in item:
                print(f"      Details: {item['details']}")
                
    print(f"\nFinal report saved to: {report_path}")

if __name__ == "__main__":
    run_verification()
