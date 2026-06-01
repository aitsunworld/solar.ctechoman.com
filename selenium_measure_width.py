# selenium_measure_width.py
import sys
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

# Ensure UTF-8 output on Windows terminal
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def run_measurements():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file://" + file_path.replace("\\", "/")
    print(f"Loading URL: {url}")
    
    driver = webdriver.Chrome(options=options)
    
    try:
        driver.get(url)
        time.sleep(1) # wait for page load
        
        # Click the tab to open Appliance Auditor
        tab_appliances = driver.find_element(By.ID, "tab-appliances")
        tab_appliances.click()
        time.sleep(1) # wait for sizer init
        
        # Elements to measure
        def get_computed_widths(prop_name):
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(1) # wait for DOM update
            
            js_script = """
            const wrapper = document.querySelector('.calculator-wrapper');
            const info = document.querySelector('.calc-info');
            const form = document.querySelector('.calc-form');
            const container = document.getElementById('appliance-inputs-container');
            const filterBar = container.querySelector('.appliance-filter-bar');
            const grid = container.querySelector('.appliance-grid');
            
            return {
                wrapperWidth: wrapper ? wrapper.offsetWidth : 0,
                infoWidth: info ? info.offsetWidth : 0,
                formWidth: form ? form.offsetWidth : 0,
                containerWidth: container ? container.offsetWidth : 0,
                filterBarWidth: filterBar ? filterBar.offsetWidth : 0,
                filterBarScrollWidth: filterBar ? filterBar.scrollWidth : 0,
                gridWidth: grid ? grid.offsetWidth : 0,
                cardsCount: grid ? grid.querySelectorAll('.appliance-item').length : 0,
                columnsCount: grid ? window.getComputedStyle(grid).gridTemplateColumns.split(' ').length : 0
            };
            """
            return driver.execute_script(js_script)
            
        print("Measuring widths for Residential...")
        res_metrics = get_computed_widths("residential")
        
        print("Measuring widths for Commercial...")
        com_metrics = get_computed_widths("commercial")
        
        print("Measuring widths for Industrial...")
        ind_metrics = get_computed_widths("industrial")
        
        # Output console report
        print("\n" + "="*60)
        print("COMPUTED WIDTHS MEASURED IN HEADLESS CHROME (Window = 1200px)")
        print("="*60)
        print(f"Residential: wrapper={res_metrics['wrapperWidth']}px, info={res_metrics['infoWidth']}px, form={res_metrics['formWidth']}px, filterBar={res_metrics['filterBarWidth']}px (scroll={res_metrics['filterBarScrollWidth']}px), grid={res_metrics['gridWidth']}px, cols={res_metrics['columnsCount']}")
        print(f"Commercial:  wrapper={com_metrics['wrapperWidth']}px, info={com_metrics['infoWidth']}px, form={com_metrics['formWidth']}px, filterBar={com_metrics['filterBarWidth']}px (scroll={com_metrics['filterBarScrollWidth']}px), grid={com_metrics['gridWidth']}px, cols={com_metrics['columnsCount']}")
        print(f"Industrial:  wrapper={ind_metrics['wrapperWidth']}px, info={ind_metrics['infoWidth']}px, form={ind_metrics['formWidth']}px, filterBar={ind_metrics['filterBarWidth']}px (scroll={ind_metrics['filterBarScrollWidth']}px), grid={ind_metrics['gridWidth']}px, cols={ind_metrics['columnsCount']}")
        print("="*60)
        
        # Write report to markdown file
        report = []
        report.append("# COMPUTED BROWSER WIDTH METRICS REPORT")
        report.append("\nThis report compiles the actual measured widths inside a headless Chrome browser instance at 1200px window width:")
        report.append("\n| Property Configuration | Wrapper Width | Info (Left) Width | Form (Right) Width | Filter Bar Width | Filter Bar Scroll Width | Grid Width | Columns |")
        report.append("| --- | --- | --- | --- | --- | --- | --- | --- |")
        report.append(f"| **Residential > All** | {res_metrics['wrapperWidth']}px | {res_metrics['infoWidth']}px | {res_metrics['formWidth']}px | {res_metrics['filterBarWidth']}px | {res_metrics['filterBarScrollWidth']}px | {res_metrics['gridWidth']}px | {res_metrics['columnsCount']} |")
        report.append(f"| **Commercial > All** | {com_metrics['wrapperWidth']}px | {com_metrics['infoWidth']}px | {com_metrics['formWidth']}px | {com_metrics['filterBarWidth']}px | {com_metrics['filterBarScrollWidth']}px | {com_metrics['gridWidth']}px | {com_metrics['columnsCount']} |")
        report.append(f"| **Industrial > All** | {ind_metrics['wrapperWidth']}px | {ind_metrics['infoWidth']}px | {ind_metrics['formWidth']}px | {ind_metrics['filterBarWidth']}px | {ind_metrics['filterBarScrollWidth']}px | {ind_metrics['gridWidth']}px | {ind_metrics['columnsCount']} |")
        
        with open("selenium_width_results.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
        print("Report written successfully as selenium_width_results.md.")
        
    finally:
        driver.quit()

if __name__ == "__main__":
    run_measurements()
