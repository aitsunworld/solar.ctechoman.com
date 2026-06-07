"""
verify_phase2.py
Quick Selenium verification of Phase 2: Upload Bill option in Step 5
"""
import sys, os, time
sys.stdout.reconfigure(encoding='utf-8')

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

ARTIFACT_DIR = r"C:\Users\Dell\.gemini\antigravity-ide\brain\e52226d4-d4b2-4c2c-9e3f-69865bd44d80"
os.makedirs(ARTIFACT_DIR, exist_ok=True)

opts = Options()
opts.add_argument("--headless")
opts.add_argument("--disable-gpu")
opts.add_argument("--no-sandbox")
opts.add_argument("--disable-dev-shm-usage")
opts.set_capability('goog:loggingPrefs', {'browser': 'ALL'})

driver = webdriver.Chrome(options=opts)
cwd = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
url = "file:///" + os.path.join(cwd, "preview.html").replace("\\", "/")

try:
    driver.set_window_size(1280, 900)
    driver.get(url)
    time.sleep(2)

    # Scroll to calculator
    calc = driver.find_element(By.ID, "calculator")
    driver.execute_script("arguments[0].scrollIntoView(true);", calc)
    time.sleep(0.5)

    # Step 1: select first appliance
    cards = driver.find_elements(By.CSS_SELECTOR, '.discovery-appliance-card')
    if cards:
        driver.execute_script("arguments[0].click();", cards[0])
        time.sleep(0.3)

    # Go to step 2
    btn2 = driver.find_element(By.ID, "btn-goto-step2")
    driver.execute_script("arguments[0].click();", btn2)
    time.sleep(0.4)

    # Go to step 3
    btn3 = driver.find_element(By.ID, "btn-goto-step3")
    driver.execute_script("arguments[0].click();", btn3)
    time.sleep(0.4)

    # Go to step 4
    btn4 = driver.find_element(By.ID, "btn-goto-step4")
    driver.execute_script("arguments[0].click();", btn4)
    time.sleep(0.4)

    # Go to step 5
    btn5 = driver.find_element(By.ID, "btn-goto-step5")
    driver.execute_script("arguments[0].click();", btn5)
    time.sleep(0.5)

    driver.save_screenshot(os.path.join(ARTIFACT_DIR, "phase2_step5.png"))
    print("Captured Step 5 screenshot")

    # Verify upload button exists
    upload_btn = driver.find_element(By.ID, "btn-show-upload-bill")
    print(f"Upload button found: text='{upload_btn.text}', visible={upload_btn.is_displayed()}")

    # Click upload button
    driver.execute_script("arguments[0].click();", upload_btn)
    time.sleep(0.4)

    driver.save_screenshot(os.path.join(ARTIFACT_DIR, "phase2_upload_panel.png"))
    print("Captured upload panel screenshot")

    # Check panel visible
    panel = driver.find_element(By.ID, "upload-bill-panel")
    panel_visible = panel.is_displayed()
    print(f"Upload panel visible: {panel_visible}")

    # Check file input
    file_input = driver.find_element(By.ID, "db-bill-file")
    print(f"File input found: {file_input is not None}")

    # Check success message element
    success_div = driver.find_element(By.ID, "upload-bill-success")
    print(f"Success div present (hidden): {success_div is not None}")

    # Check no JS errors
    errors = [l for l in driver.get_log('browser') if l['level'] == 'SEVERE']
    print(f"JS console errors: {len(errors)}")
    for e in errors:
        print(f"  ERROR: {e['message']}")

    print("\n=== PHASE 2 VERIFICATION: PASS ===" if panel_visible else "\n=== PHASE 2 VERIFICATION: FAIL ===")

except Exception as ex:
    print(f"ERROR: {ex}")
    import traceback; traceback.print_exc()
finally:
    driver.quit()
