"""Measure wizard step element dimensions via Selenium."""
import json, time, os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

options = Options()
options.add_argument('--headless')
options.add_argument('--disable-gpu')
options.add_argument('--no-sandbox')
options.add_argument('--disable-dev-shm-usage')

cwd = os.getcwd()
url = 'file:///' + os.path.join(cwd, 'preview.html').replace('\\', '/')

driver = webdriver.Chrome(options=options)
results = {}

# Step 5
driver.get(url)
time.sleep(1)
for i in range(1, 5):
    btn = driver.find_element(By.ID, f'btn-goto-step{i+1}')
    driver.execute_script('arguments[0].click();', btn)
    time.sleep(0.3)
time.sleep(1)

step5 = {}
for name, sel in {
    'panel5': '#discovery-panel-5',
    'manual_bill': '#manual-bill-panel',
    'calibration_choice': '.calibration-choice-zone',
    'slider_container': '.slider-container',
    'btn_skip': '#btn-skip-calibration',
    'btn_manual_entry': '#btn-calibrate-bill',
}.items():
    try:
        el = driver.find_element(By.CSS_SELECTOR, sel)
        rect = driver.execute_script('''
            var el = arguments[0];
            var r = el.getBoundingClientRect();
            var cs = getComputedStyle(el);
            return {
                x: Math.round(r.x), y: Math.round(r.y),
                width: Math.round(r.width), height: Math.round(r.height),
                display: cs.display, visibility: cs.visibility,
                opacity: parseFloat(cs.opacity),
                minHeight: cs.minHeight, maxHeight: cs.maxHeight,
                overflow: cs.overflow
            };
        ''', el)
        step5[name] = rect
    except Exception as e:
        step5[name] = {'error': str(e)}
results['step5'] = step5

# Step 6
bill_slider = driver.find_element(By.ID, 'discovery-bill-slider')
driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
time.sleep(0.3)
cal_btn = driver.find_element(By.ID, 'btn-calibrate-bill')
driver.execute_script('arguments[0].click();', cal_btn)
time.sleep(2)

step6 = {}
for name, sel in {
    'panel6': '#discovery-panel-6',
    'calibration_container': '.calibration-container',
    'progress_ring': '.progress-ring-wrapper',
    'calibration_metrics': '.calibration-metrics-grid',
    'calibration_status': '.calibration-status-wrapper',
}.items():
    try:
        el = driver.find_element(By.CSS_SELECTOR, sel)
        rect = driver.execute_script('''
            var el = arguments[0];
            var r = el.getBoundingClientRect();
            var cs = getComputedStyle(el);
            return {
                x: Math.round(r.x), y: Math.round(r.y),
                width: Math.round(r.width), height: Math.round(r.height),
                display: cs.display, visibility: cs.visibility,
                opacity: parseFloat(cs.opacity),
                position: cs.position, zIndex: cs.zIndex
            };
        ''', el)
        step6[name] = rect
    except Exception as e:
        step6[name] = {'error': str(e)}
results['step6'] = step6

# Step 7
next_btn = driver.find_element(By.ID, 'btn-goto-step7')
driver.execute_script('arguments[0].click();', next_btn)
time.sleep(2)

step7 = {}
for name, sel in {
    'panel7': '#discovery-panel-7',
    'discovery_dashboard': '.discovery-dashboard',
    'summary_row': '.summary-row',
    'insights_row': '.insights-row',
    'col_insights_1': '.col-insights:nth-child(1)',
    'col_insights_2': '.col-insights:nth-child(2)',
    'col_insights_3': '.col-insights:nth-child(3)',
    'form_row': '.form-row-row',
    'wizard_footer': '.wizard-footer-controls',
    'wizard_restart': '.wizard-restart-row',
}.items():
    try:
        el = driver.find_element(By.CSS_SELECTOR, sel)
        rect = driver.execute_script('''
            var el = arguments[0];
            var r = el.getBoundingClientRect();
            var cs = getComputedStyle(el);
            return {
                x: Math.round(r.x), y: Math.round(r.y),
                width: Math.round(r.width), height: Math.round(r.height),
                display: cs.display,
                minHeight: cs.minHeight, maxHeight: cs.maxHeight,
                overflow: cs.overflow
            };
        ''', el)
        step7[name] = rect
    except Exception as e:
        step7[name] = {'error': str(e)}
results['step7'] = step7

driver.quit()

out = os.path.join(cwd, 'scratch', 'wizard_measurements.json')
with open(out, 'w', encoding='utf-8') as f:
    json.dump(results, f, indent=2)
print(json.dumps(results, indent=2))
print(f'\nSaved to: {out}')
