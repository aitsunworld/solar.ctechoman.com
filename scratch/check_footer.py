"""Check wizard footer selector styling."""
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
driver.set_window_size(1440, 900)
driver.get(url)
time.sleep(1)

# Navigate to step 5
for i in range(1, 4):
    btn = driver.find_element(By.ID, f'btn-goto-step{i+1}')
    driver.execute_script('arguments[0].click();', btn)
    time.sleep(0.3)
bill_slider = driver.find_element(By.ID, 'discovery-bill-slider')
driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
time.sleep(0.2)
cal_btn = driver.find_element(By.ID, 'btn-calibrate-bill')
driver.execute_script('arguments[0].click();', cal_btn)
time.sleep(2)
btn = driver.find_element(By.ID, 'btn-goto-step7')
driver.execute_script('arguments[0].click();', btn)
time.sleep(2)

# Check footer selectors
footer_data = driver.execute_script('''
    var footer = document.querySelector('.wizard-footer-controls');
    var selects = footer ? Array.from(footer.querySelectorAll('select')) : [];
    return {
        footer_display: footer ? getComputedStyle(footer).display : 'not_found',
        footer_height: footer ? footer.getBoundingClientRect().height : 0,
        select_count: selects.length,
        selects: selects.map(function(s) {
            var r = s.getBoundingClientRect();
            var cs = getComputedStyle(s);
            return {
                x: Math.round(r.x),
                y: Math.round(r.y),
                width: Math.round(r.width),
                height: Math.round(r.height),
                minHeight: cs.minHeight,
                display: cs.display
            };
        }),
        restart_btn: (function() {
            var btn = document.getElementById('btn-reset-discovery');
            if (!btn) return null;
            var r = btn.getBoundingClientRect();
            var cs = getComputedStyle(btn);
            return {
                x: Math.round(r.x),
                y: Math.round(r.y),
                width: Math.round(r.width),
                height: Math.round(r.height),
                minHeight: cs.minHeight,
                display: cs.display,
                fontSize: cs.fontSize,
                fontWeight: cs.fontWeight,
                borderRadius: cs.borderRadius,
                padding: cs.padding
            };
        })()
    };
''')

driver.quit()
print(json.dumps(footer_data, indent=2))
