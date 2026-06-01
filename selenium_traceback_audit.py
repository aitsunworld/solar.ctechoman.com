# selenium_traceback_audit.py
import sys
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def run_traceback_audit():
    prod_url = "https://solar.ctechoman.com"
    print(f"Connecting to production: {prod_url}")
    
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    driver = webdriver.Chrome(options=options)
    
    try:
        driver.get(prod_url)
        time.sleep(2)
        
        # Open Appliance Auditor
        tab_appliances = driver.find_element(By.ID, "tab-appliances")
        tab_appliances.click()
        time.sleep(2)
        
        def capture_metrics(prop_name):
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(2)
            
            js_script = """
            const filterBar = document.querySelector('.appliance-filter-bar');
            const container = document.getElementById('appliance-inputs-container');
            const form = document.querySelector('.calc-form');
            const wrapper = document.querySelector('.calculator-wrapper');
            
            function getMetrics(el) {
                if (!el) return { clientWidth: 0, scrollWidth: 0, offsetWidth: 0 };
                return {
                    clientWidth: el.clientWidth,
                    scrollWidth: el.scrollWidth,
                    offsetWidth: el.offsetWidth
                };
            }
            
            return {
                filterBar: getMetrics(filterBar),
                container: getMetrics(container),
                form: getMetrics(form),
                wrapper: getMetrics(wrapper)
            };
            """
            return driver.execute_script(js_script)
            
        print("Tracing Residential...")
        res = capture_metrics("residential")
        
        print("Tracing Commercial...")
        com = capture_metrics("commercial")
        
        print("Tracing Industrial...")
        ind = capture_metrics("industrial")
        
        # Print Traceback Report
        print("\n" + "="*80)
        print("PRODUCTION LAYOUT WIDTH TRACEBACK REPORT")
        print("="*80)
        
        def print_elem(name, r_el, c_el, i_el):
            print(f"\n[{name}]")
            print(f"  - Residential: client={r_el['clientWidth']}px, scroll={r_el['scrollWidth']}px, offset={r_el['offsetWidth']}px")
            print(f"  - Commercial:  client={c_el['clientWidth']}px, scroll={c_el['scrollWidth']}px, offset={c_el['offsetWidth']}px")
            print(f"  - Industrial:  client={i_el['clientWidth']}px, scroll={i_el['scrollWidth']}px, offset={i_el['offsetWidth']}px")
            
        print_elem(".appliance-filter-bar (Child)", res['filterBar'], com['filterBar'], ind['filterBar'])
        print_elem("#appliance-inputs-container", res['container'], com['container'], ind['container'])
        print_elem(".calc-form", res['form'], com['form'], ind['form'])
        print_elem(".calculator-wrapper (Parent)", res['wrapper'], com['wrapper'], ind['wrapper'])
        
        print("\n" + "="*80)
        
        # Write report
        report = []
        report.append("# PRODUCTION LAYOUT WIDTH TRACEBACK REPORT")
        report.append(f"\nLive URL Audited: `{prod_url}`")
        
        def append_elem_md(name, r_el, c_el, i_el):
            report.append(f"\n### `{name}`")
            report.append("| Mode | clientWidth | scrollWidth | offsetWidth |")
            report.append("| --- | --- | --- | --- |")
            report.append(f"| **Residential** | {r_el['clientWidth']}px | {r_el['scrollWidth']}px | {r_el['offsetWidth']}px |")
            report.append(f"| **Commercial** | {c_el['clientWidth']}px | {c_el['scrollWidth']}px | {c_el['offsetWidth']}px |")
            report.append(f"| **Industrial** | {i_el['clientWidth']}px | {i_el['scrollWidth']}px | {i_el['offsetWidth']}px |")
            
        append_elem_md(".appliance-filter-bar (Child)", res['filterBar'], com['filterBar'], ind['filterBar'])
        append_elem_md("#appliance-inputs-container", res['container'], com['container'], ind['container'])
        append_elem_md(".calc-form", res['form'], com['form'], ind['form'])
        append_elem_md(".calculator-wrapper (Parent)", res['wrapper'], com['wrapper'], ind['wrapper'])
        
        with open("selenium_traceback_report.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
        print("Traceback report written successfully as selenium_traceback_report.md.")
        
    finally:
        driver.quit()

if __name__ == "__main__":
    run_traceback_audit()
