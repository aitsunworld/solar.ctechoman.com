# selenium_prod_audit.py
import sys
import os
import time
import urllib.request
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def run_prod_audit():
    prod_url = "https://solar.ctechoman.com"
    print(f"Directly loading production URL: {prod_url}")
    
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    
    try:
        driver.get(prod_url)
        time.sleep(2) # wait for page and scripts load
        
        # 1. Capture loaded CSS link tags and their versions
        css_links = driver.find_elements(By.XPATH, "//link[@rel='stylesheet']")
        loaded_css_files = []
        for link in css_links:
            href = link.get_attribute("href")
            loaded_css_files.append(href)
            
        print("\nLoaded CSS Files in Production:")
        for href in loaded_css_files:
            print(f"- {href}")
            
        # 2. Try fetching Last-Modified/Date HTTP headers for loaded CSS files
        print("\nFetching HTTP Headers for Production CSS Files:")
        css_timestamps = {}
        for href in loaded_css_files:
            try:
                req = urllib.request.Request(
                    href, 
                    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
                )
                with urllib.request.urlopen(req, timeout=5) as response:
                    info = response.info()
                    last_mod = info.get("Last-Modified", "Not Provided")
                    cache_ctrl = info.get("Cache-Control", "Not Provided")
                    date = info.get("Date", "Not Provided")
                    css_timestamps[href] = {
                        "Last-Modified": last_mod,
                        "Cache-Control": cache_ctrl,
                        "Server-Date": date
                    }
                    print(f"- {href}: Last-Modified='{last_mod}', Cache-Control='{cache_ctrl}', Server-Date='{date}'")
            except Exception as e:
                css_timestamps[href] = {"Error": str(e)}
                print(f"- Could not fetch headers for {href}: {e}")
                
        # 3. Trigger Appliance Auditor tab
        tab_appliances = driver.find_element(By.ID, "tab-appliances")
        tab_appliances.click()
        time.sleep(2) # wait for DOM update
        
        # 4. Measure elements for each mode
        def capture_prod_metrics(prop_name):
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(2) # wait for DOM layout settle
            
            js_script = """
            const wrapper = document.querySelector('.calculator-wrapper');
            const form = document.querySelector('.calc-form');
            const filterBar = document.querySelector('.appliance-filter-bar');
            const grid = document.querySelector('.appliance-grid');
            
            return {
                wrapperWidth: wrapper ? wrapper.offsetWidth : 0,
                formWidth: form ? form.offsetWidth : 0,
                filterBarWidth: filterBar ? filterBar.offsetWidth : 0,
                filterBarScrollWidth: filterBar ? filterBar.scrollWidth : 0,
                gridWidth: grid ? grid.offsetWidth : 0,
                gridComputedColumns: grid ? window.getComputedStyle(grid).gridTemplateColumns : "",
                gridColumnsCount: grid ? window.getComputedStyle(grid).gridTemplateColumns.split(' ').length : 0
            };
            """
            return driver.execute_script(js_script)
            
        print("\nMeasuring production widths for Residential...")
        res = capture_prod_metrics("residential")
        
        print("Measuring production widths for Commercial...")
        com = capture_prod_metrics("commercial")
        
        print("Measuring production widths for Industrial...")
        ind = capture_prod_metrics("industrial")
        
        # Write report
        report = []
        report.append("# PRODUCTION LAYOUT AUDIT REPORT")
        report.append(f"\nLive URL Audited: `{prod_url}`")
        
        report.append("\n## 1. Loaded Stylesheets on Production")
        for href in loaded_css_files:
            report.append(f"- `{href}`")
            
        report.append("\n## 2. Production CSS Server HTTP Headers")
        for href, headers in css_timestamps.items():
            report.append(f"\n### `{href.split('/')[-1]}`")
            for k, v in headers.items():
                report.append(f"- **{k}**: `{v}`")
                
        report.append("\n## 3. Production Computed Widths (at 1200px viewport)")
        report.append("| Mode | `.calculator-wrapper` | `.calc-form` (Right) | `.appliance-filter-bar` (Visible / Scroll) | `.appliance-grid` (Visible) | Grid Columns count / template |")
        report.append("| --- | --- | --- | --- | --- | --- |")
        report.append(f"| **Residential** | {res['wrapperWidth']}px | {res['formWidth']}px | {res['filterBarWidth']}px / {res['filterBarScrollWidth']}px | {res['gridWidth']}px | {res['gridColumnsCount']} cols (`{res['gridComputedColumns']}`) |")
        report.append(f"| **Commercial** | {com['wrapperWidth']}px | {com['formWidth']}px | {com['filterBarWidth']}px / {com['filterBarScrollWidth']}px | {com['gridWidth']}px | {com['gridColumnsCount']} cols (`{com['gridComputedColumns']}`) |")
        report.append(f"| **Industrial** | {ind['wrapperWidth']}px | {ind['formWidth']}px | {ind['filterBarWidth']}px / {ind['filterBarScrollWidth']}px | {ind['gridWidth']}px | {ind['gridColumnsCount']} cols (`{ind['gridComputedColumns']}`) |")
        
        # Compare with baseline local measurements
        report.append("\n## 4. Comparison with Local Workspace Measurements")
        report.append("\nSince our local headless baseline measured:")
        report.append("- Commercial `.calculator-wrapper`: `1103px`")
        report.append("- Commercial `.calc-form`: `537px`")
        report.append("- Commercial `.appliance-grid`: `519px` (2 columns)")
        
        diff_wrapper = com['wrapperWidth'] - 1103
        diff_form = com['formWidth'] - 537
        diff_grid = com['gridWidth'] - 519
        
        report.append(f"\nProduction vs Local Baseline difference:")
        report.append(f"- **Wrapper Width Difference**: `{diff_wrapper}px`")
        report.append(f"- **Form Width Difference**: `{diff_form}px`")
        report.append(f"- **Grid Width Difference**: `{diff_grid}px`")
        
        if diff_form > 100 or com['gridColumnsCount'] > 2:
            report.append("\n🚨 **CONFIRMED DIFFERENCE**: Production exhibits horizontal stretching (**{}px** right panel width, **{}** columns) that does not occur in local headless tests!".format(com['formWidth'], com['gridColumnsCount']))
            report.append("This points to a styling mismatch (either production has CSS caching or is serving a different stylesheet).")
        else:
            report.append("\n✅ **METRICS MATCH**: Production and local measurements match. The horizontal stretching in headless Chrome is bounded at both environments, meaning the blowout occurs only when viewport sizing lets the track expand.")
            
        with open("selenium_prod_report.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
        print("\nProduction audit report written successfully as selenium_prod_report.md.")
        
    finally:
        driver.quit()

if __name__ == "__main__":
    run_prod_audit()
