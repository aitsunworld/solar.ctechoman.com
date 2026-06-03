# selenium_detailed_audit.py
import sys
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select

if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def run_detailed_audit():
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    url = "http://localhost:8000/preview.html"
    
    driver = webdriver.Chrome(options=options)
    
    try:
        driver.get(url)
        time.sleep(1)
        
        # Click the tab to open Appliance Auditor
        tab_appliances = driver.find_element(By.ID, "tab-appliances")
        tab_appliances.click()
        time.sleep(1)
        
        def capture_metrics(prop_name):
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(1) # wait for DOM update
            
            js_script = """
            const container = document.getElementById('appliance-inputs-container');
            const grid = document.querySelector('.appliance-grid');
            const filterBar = document.querySelector('.appliance-filter-bar');
            
            // Collect card widths
            const cards = Array.from(document.querySelectorAll('.appliance-item'));
            const cardMetrics = cards.map(c => {
                return {
                    id: c.getAttribute('data-id'),
                    name: c.querySelector('h4') ? c.querySelector('h4').innerText : "Unknown",
                    width: c.offsetWidth,
                    classes: c.className,
                    style: c.getAttribute('style') || ""
                };
            });
            
            return {
                gridHTML: grid ? grid.outerHTML : "",
                gridWidth: grid ? grid.offsetWidth : 0,
                gridComputedColumns: grid ? window.getComputedStyle(grid).gridTemplateColumns : "",
                filterBarWidth: filterBar ? filterBar.offsetWidth : 0,
                filterBarScrollWidth: filterBar ? filterBar.scrollWidth : 0,
                filterBarClasses: filterBar ? filterBar.className : "",
                filterBarStyle: filterBar ? filterBar.getAttribute('style') || "" : "",
                containerClasses: container ? container.className : "",
                containerStyle: container ? container.getAttribute('style') || "" : "",
                cards: cardMetrics
            };
            """
            return driver.execute_script(js_script)
            
        print("Capturing baseline Residential...")
        res = capture_metrics("residential")
        
        print("Capturing baseline Commercial...")
        com = capture_metrics("commercial")
        
        print("Capturing baseline Industrial...")
        ind = capture_metrics("industrial")
        
        # Apply the temporary test rule
        print("\nApplying temporary test rule: .appliance-grid { grid-template-columns: repeat(3, 1fr) !important; }")
        driver.execute_script("""
            const style = document.createElement('style');
            style.id = 'temp-test-style';
            style.innerHTML = '.appliance-grid { grid-template-columns: repeat(3, 1fr) !important; }';
            document.head.appendChild(style);
        """)
        time.sleep(1)
        
        print("Capturing Commercial under temporary test rule...")
        com_test = capture_metrics("commercial")
        
        # Write report
        report = []
        report.append("# DETAILED LAYOUT AUDIT & COMPARISON REPORT")
        
        report.append("\n## 1. Generated HTML of `.appliance-grid` (Structure & IDs)")
        
        # Residential grid summary
        report.append("\n### Residential > All Grid HTML Structure")
        report.append("```html")
        res_grid_cleaned = "\n".join([line for line in res['gridHTML'].split("\n")[:20]]) # show first 20 lines
        report.append(res_grid_cleaned + "\n... [truncated for readability]")
        report.append("```")
        
        # Commercial grid summary
        report.append("\n### Commercial > All Grid HTML Structure")
        report.append("```html")
        com_grid_cleaned = "\n".join([line for line in com['gridHTML'].split("\n")[:20]]) # show first 20 lines
        report.append(com_grid_cleaned + "\n... [truncated for readability]")
        report.append("```")
        
        # Industrial grid summary
        report.append("\n### Industrial > All Grid HTML Structure")
        report.append("```html")
        ind_grid_cleaned = "\n".join([line for line in ind['gridHTML'].split("\n")[:20]]) # show first 20 lines
        report.append(ind_grid_cleaned + "\n... [truncated for readability]")
        report.append("```")
        
        report.append("\n## 2. Computed grid-template-columns Value")
        report.append(f"- **Residential**: `{res['gridComputedColumns']}`")
        report.append(f"- **Commercial**: `{com['gridComputedColumns']}`")
        report.append(f"- **Industrial**: `{ind['gridComputedColumns']}`")
        
        report.append("\n## 3. Computed Width of Every `appliance-item` Card")
        report.append("\n### Residential Cards:")
        for c in res['cards']:
            report.append(f"- Card `{c['id']}` ({c['name']}): width = `{c['width']}px`, class = `{c['classes']}`, style = `{c['style']}`")
            
        report.append("\n### Commercial Cards:")
        for c in com['cards']:
            report.append(f"- Card `{c['id']}` ({c['name']}): width = `{c['width']}px`, class = `{c['classes']}`, style = `{c['style']}`")
            
        report.append("\n### Industrial Cards:")
        for c in ind['cards']:
            report.append(f"- Card `{c['id']}` ({c['name']}): width = `{c['width']}px`, class = `{c['classes']}`, style = `{c['style']}`")
            
        report.append("\n## 4. Computed Width of `.appliance-grid`")
        report.append(f"- **Residential**: `{res['gridWidth']}px`")
        report.append(f"- **Commercial**: `{com['gridWidth']}px`")
        report.append(f"- **Industrial**: `{ind['gridWidth']}px`")
        
        report.append("\n## 5. Computed Width of `.appliance-filter-bar`")
        report.append(f"- **Residential**: `{res['filterBarWidth']}px` (scrollWidth = `{res['filterBarScrollWidth']}px`)")
        report.append(f"- **Commercial**: `{com['filterBarWidth']}px` (scrollWidth = `{com['filterBarScrollWidth']}px`)")
        report.append(f"- **Industrial**: `{ind['filterBarWidth']}px` (scrollWidth = `{ind['filterBarScrollWidth']}px`)")
        
        report.append("\n## 6. Any Class Added ONLY in Commercial Mode")
        # Compare classes
        container_diff_class = com['containerClasses'] if com['containerClasses'] != res['containerClasses'] else "None"
        filter_diff_class = com['filterBarClasses'] if com['filterBarClasses'] != res['filterBarClasses'] else "None"
        report.append(f"- `#appliance-inputs-container` unique classes: `{container_diff_class}`")
        report.append(f"- `.appliance-filter-bar` unique classes: `{filter_diff_class}`")
        
        # Check cards classes
        com_card_classes = set(c['classes'] for c in com['cards'])
        res_card_classes = set(c['classes'] for c in res['cards'])
        card_diff_classes = com_card_classes - res_card_classes
        report.append(f"- `.appliance-item` unique classes: `{card_diff_classes if card_diff_classes else 'None'}`")
        
        report.append("\n## 7. Any Inline Style Added ONLY in Commercial Mode")
        report.append(f"- `#appliance-inputs-container` inline style: `{com['containerStyle']}` (Residential: `{res['containerStyle']}`)")
        report.append(f"- `.appliance-filter-bar` inline style: `{com['filterBarStyle']}` (Residential: `{res['filterBarStyle']}`)")
        
        report.append("\n## 8. TEMPORARY TEST RULE RESULTS (`repeat(3, 1fr) !important`)")
        report.append(f"- **Commercial Grid Width**: `{com_test['gridWidth']}px` (Baseline: `{com['gridWidth']}px`)")
        report.append(f"- **Commercial Grid Computed Columns**: `{com_test['gridComputedColumns']}` (Baseline: `{com['gridComputedColumns']}`)")
        report.append("\n### Commercial Card Widths under Temporary Test:")
        for c in com_test['cards']:
            report.append(f"- Card `{c['id']}` ({c['name']}): width = `{c['width']}px` (Baseline: `{next(bc['width'] for bc in com['cards'] if bc['id'] == c['id'])}px`)")
            
        with open("selenium_detailed_report.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
        print("Detailed audit report written successfully as selenium_detailed_report.md.")
        
    finally:
        driver.quit()

if __name__ == "__main__":
    run_detailed_audit()
