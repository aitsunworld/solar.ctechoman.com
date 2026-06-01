# selenium_audit.py
import sys
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def run_audit():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    cwd = os.getcwd()
    file_path = os.path.join(cwd, "preview.html")
    url = "file://" + file_path.replace("\\", "/")
    
    driver = webdriver.Chrome(options=options)
    
    try:
        driver.get(url)
        time.sleep(1)
        
        tab_appliances = driver.find_element(By.ID, "tab-appliances")
        tab_appliances.click()
        time.sleep(1)
        
        def audit_mode(prop_name):
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(1) # wait for DOM update
            
            js_script = """
            const wrapper = document.querySelector('.calculator-wrapper');
            const form = document.querySelector('.calc-form');
            const filterBar = document.querySelector('.appliance-filter-bar');
            const tabs = document.querySelectorAll('.filter-tab');
            const grid = document.querySelector('.appliance-grid');
            
            // Sum of individual tab widths (including padding/margins)
            let tabsSumWidth = 0;
            tabs.forEach(t => {
                tabsSumWidth += t.offsetWidth;
            });
            
            return {
                wrapperWidth: wrapper ? wrapper.offsetWidth : 0,
                formWidth: form ? form.offsetWidth : 0,
                filterBarWidth: filterBar ? filterBar.offsetWidth : 0,
                filterBarScrollWidth: filterBar ? filterBar.scrollWidth : 0,
                tabsCount: tabs.length,
                tabsSumWidth: tabsSumWidth,
                gridWidth: grid ? grid.offsetWidth : 0,
                gridComputedColumns: grid ? window.getComputedStyle(grid).gridTemplateColumns : "",
                gridColumnsCount: grid ? window.getComputedStyle(grid).gridTemplateColumns.split(' ').length : 0
            };
            """
            return driver.execute_script(js_script)
            
        print("Auditing Residential baseline...")
        res = audit_mode("residential")
        
        print("Auditing Commercial baseline...")
        com = audit_mode("commercial")
        
        print("Auditing Industrial baseline...")
        ind = audit_mode("industrial")
        
        print("\n" + "="*70)
        print("OFFICIAL COMPUTED AUDIT REPORT - ORIGINAL STATE")
        print("="*70)
        
        print(f"\n[Residential Mode]")
        print(f"- .calculator-wrapper width: {res['wrapperWidth']}px")
        print(f"- .calc-form width: {res['formWidth']}px")
        print(f"- .appliance-filter-bar width: {res['filterBarWidth']}px (ScrollWidth: {res['filterBarScrollWidth']}px)")
        print(f"- tabsCount: {res['tabsCount']}, Sum of tab widths: {res['tabsSumWidth']}px")
        print(f"- .appliance-grid width: {res['gridWidth']}px")
        print(f"- .appliance-grid template columns: {res['gridComputedColumns']} (Columns Count: {res['gridColumnsCount']})")
        
        print(f"\n[Commercial Mode]")
        print(f"- .calculator-wrapper width: {com['wrapperWidth']}px")
        print(f"- .calc-form width: {com['formWidth']}px")
        print(f"- .appliance-filter-bar width: {com['filterBarWidth']}px (ScrollWidth: {com['filterBarScrollWidth']}px)")
        print(f"- tabsCount: {com['tabsCount']}, Sum of tab widths: {com['tabsSumWidth']}px")
        print(f"- .appliance-grid width: {com['gridWidth']}px")
        print(f"- .appliance-grid template columns: {com['gridComputedColumns']} (Columns Count: {com['gridColumnsCount']})")
        
        print(f"\n[Industrial Mode]")
        print(f"- .calculator-wrapper width: {ind['wrapperWidth']}px")
        print(f"- .calc-form width: {ind['formWidth']}px")
        print(f"- .appliance-filter-bar width: {ind['filterBarWidth']}px (ScrollWidth: {ind['filterBarScrollWidth']}px)")
        print(f"- tabsCount: {ind['tabsCount']}, Sum of tab widths: {ind['tabsSumWidth']}px")
        print(f"- .appliance-grid width: {ind['gridWidth']}px")
        print(f"- .appliance-grid template columns: {ind['gridComputedColumns']} (Columns Count: {ind['gridColumnsCount']})")
        
        print("\n" + "="*70)
        
        # Compile report to markdown file
        report = []
        report.append("# COMPUTED LAYOUT AUDIT REPORT")
        report.append("\nThis audit compiles computed browser widths for Residential, Commercial, and Industrial configurations:")
        
        report.append("\n## 1. Width Compilation Table")
        report.append("| Element | Residential | Commercial | Industrial |")
        report.append("| --- | --- | --- | --- |")
        report.append(f"| `.calculator-wrapper` | {res['wrapperWidth']}px | {com['wrapperWidth']}px | {ind['wrapperWidth']}px |")
        report.append(f"| `.calc-form` | {res['formWidth']}px | {com['formWidth']}px | {ind['formWidth']}px |")
        report.append(f"| `.appliance-filter-bar` (offsetWidth) | {res['filterBarWidth']}px | {com['filterBarWidth']}px | {ind['filterBarWidth']}px |")
        report.append(f"| `.appliance-filter-bar` (scrollWidth) | {res['filterBarScrollWidth']}px | {com['filterBarScrollWidth']}px | {ind['filterBarScrollWidth']}px |")
        report.append(f"| Sum of `.filter-tab` widths | {res['tabsSumWidth']}px | {com['tabsSumWidth']}px | {ind['tabsSumWidth']}px |")
        report.append(f"| `.appliance-grid` (offsetWidth) | {res['gridWidth']}px | {com['gridWidth']}px | {ind['gridWidth']}px |")
        report.append(f"| `.appliance-grid` Columns Count | {res['gridColumnsCount']} | {com['gridColumnsCount']} | {ind['gridColumnsCount']} |")
        report.append(f"| `.appliance-grid` Computed Columns | `{res['gridComputedColumns']}` | `{com['gridComputedColumns']}` | `{ind['gridComputedColumns']}` |")
        
        with open("selenium_audit_results.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
    finally:
        driver.quit()

if __name__ == "__main__":
    run_audit()
