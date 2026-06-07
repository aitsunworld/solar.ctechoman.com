"""Check computed grid-template-columns on insights-row."""
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

# Navigate to step 7
driver.set_window_size(1440, 900)
driver.get(url)
time.sleep(1)
# Steps 1->2->3->4->5
for i in range(1, 5):
    btn = driver.find_element(By.ID, f'btn-goto-step{i+1}')
    driver.execute_script('arguments[0].click();', btn)
    time.sleep(0.3)
# Step 5: set bill then calibrate to step 6
bill_slider = driver.find_element(By.ID, 'discovery-bill-slider')
driver.execute_script("arguments[0].value = 400; arguments[0].dispatchEvent(new Event('input'));", bill_slider)
time.sleep(0.2)
cal_btn = driver.find_element(By.ID, 'btn-calibrate-bill')
driver.execute_script('arguments[0].click();', cal_btn)
time.sleep(2)
# Step 6 -> 7
btn = driver.find_element(By.ID, 'btn-goto-step7')
driver.execute_script('arguments[0].click();', btn)
time.sleep(2)

el = driver.find_element(By.CSS_SELECTOR, '.insights-row')
computed = driver.execute_script('''
    var el = arguments[0];
    var cs = getComputedStyle(el);
    return {
        display: cs.display,
        gridTemplateColumns: cs.gridTemplateColumns,
        gridTemplateRows: cs.gridTemplateRows,
        gridAutoColumns: cs.gridAutoColumns,
        gridAutoRows: cs.gridAutoRows,
        gridTemplateAreas: cs.gridTemplateAreas,
        gridArea: cs.gridArea,
        flexDirection: cs.flexDirection,
        width: cs.width,
        height: cs.height,
        clientWidth: el.clientWidth,
        clientHeight: el.clientHeight,
        scrollWidth: el.scrollWidth,
        scrollHeight: el.scrollHeight,
        children: el.children.length,
        childTags: Array.from(el.children).map(c => c.tagName + '.' + c.className).join(' | ')
    };
''', el)

driver.quit()
print(json.dumps(computed, indent=2))
