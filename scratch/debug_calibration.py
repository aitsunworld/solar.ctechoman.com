"""Debug calibration container height."""
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

# Navigate to step 6
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

# Get ALL children of calibration-container with their computed styles
children_data = driver.execute_script('''
    var container = document.querySelector('.calibration-container');
    var children = Array.from(container.children);
    var totalHeight = 0;
    return children.map(function(child, idx) {
        var r = child.getBoundingClientRect();
        var cs = getComputedStyle(child);
        var marginTop = parseFloat(cs.marginTop);
        var marginBottom = parseFloat(cs.marginBottom);
        var paddingTop = parseFloat(cs.paddingTop);
        var paddingBottom = parseFloat(cs.paddingBottom);
        var h = r.height + marginTop + marginBottom;
        totalHeight += h;
        return {
            idx: idx,
            tag: child.tagName,
            className: child.className,
            id: child.id,
            x: Math.round(r.x),
            y: Math.round(r.y),
            width: Math.round(r.width),
            height: Math.round(r.height),
            marginTop: marginTop,
            marginBottom: marginBottom,
            paddingTop: paddingTop,
            paddingBottom: paddingBottom,
            display: cs.display,
            visibility: cs.visibility,
            minHeight: cs.minHeight,
            maxHeight: cs.maxHeight,
            totalContribution: Math.round(h)
        };
    });
''')

driver.quit()
print(json.dumps(children_data, indent=2))
