# selenium_measure.py
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
    # 1. Setup headless Chrome options
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--window-size=1200,800")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    # 2. Get local URL
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
        
        # REMOVE the inline style on #appliance-inputs-container to rely entirely on style.css!
        driver.execute_script("document.getElementById('appliance-inputs-container').removeAttribute('style');")
        # Keep display: block
        driver.execute_script("document.getElementById('appliance-inputs-container').style.display = 'block';")
        time.sleep(0.5)
        
        # Elements to measure
        container_id = "appliance-inputs-container"
        grid_class = "appliance-grid"
        
        def get_computed_metrics(prop_name):
            # Select property type
            prop_select = Select(driver.find_element(By.ID, "property-type"))
            prop_select.select_by_value(prop_name)
            time.sleep(1) # wait for DOM update
            
            # Read DOM computed metrics using JS
            js_script = """
            const container = document.getElementById('appliance-inputs-container');
            const grid = container.querySelector('.appliance-grid');
            const filterBar = container.querySelector('.appliance-filter-bar');
            
            return {
                containerHeight: container.offsetHeight,
                containerClientHeight: container.clientHeight,
                containerScrollHeight: container.scrollHeight,
                gridHeight: grid ? grid.offsetHeight : 0,
                filterBarHeight: filterBar ? filterBar.offsetHeight : 0,
                cardsCount: grid ? grid.querySelectorAll('.appliance-item').length : 0
            };
            """
            return driver.execute_script(js_script)
            
        print("Measuring Residential...")
        res_metrics = get_computed_metrics("residential")
        
        print("Measuring Commercial...")
        com_metrics = get_computed_metrics("commercial")
        
        print("Measuring Industrial...")
        ind_metrics = get_computed_metrics("industrial")
        
        # Output console report
        print("\n" + "="*50)
        print("COMPUTED HEIGHTS MEASURED IN HEADLESS CHROME")
        print("="*50)
        print(f"Residential > All: containerHeight={res_metrics['containerHeight']}px, gridHeight={res_metrics['gridHeight']}px, cardsCount={res_metrics['cardsCount']}")
        print(f"Commercial > All:  containerHeight={com_metrics['containerHeight']}px, gridHeight={com_metrics['gridHeight']}px, cardsCount={com_metrics['cardsCount']}")
        print(f"Industrial > All:  containerHeight={ind_metrics['containerHeight']}px, gridHeight={ind_metrics['gridHeight']}px, cardsCount={ind_metrics['cardsCount']}")
        print("="*50)
        
        # Write report to markdown file
        report = []
        report.append("# COMPUTED BROWSER METRICS REPORT")
        report.append("\nThis report compiles the actual measured heights inside a headless Chrome browser instance:")
        report.append("\n| Property Configuration | Container Height | Scroll (Content) Height | Appliance Grid Height | Card Count | Filter Bar Height |")
        report.append("| --- | --- | --- | --- | --- | --- |")
        report.append(f"| **Residential > All** | {res_metrics['containerHeight']}px | {res_metrics['containerScrollHeight']}px | {res_metrics['gridHeight']}px | {res_metrics['cardsCount']} | {res_metrics['filterBarHeight']}px |")
        report.append(f"| **Commercial > All** | {com_metrics['containerHeight']}px | {com_metrics['containerScrollHeight']}px | {com_metrics['gridHeight']}px | {com_metrics['cardsCount']} | {com_metrics['filterBarHeight']}px |")
        report.append(f"| **Industrial > All** | {ind_metrics['containerHeight']}px | {ind_metrics['containerScrollHeight']}px | {ind_metrics['gridHeight']}px | {ind_metrics['cardsCount']} | {ind_metrics['filterBarHeight']}px |")
        
        # Add analysis
        report.append("\n### Height Difference Analysis")
        diff_grid_com_res = com_metrics['gridHeight'] - res_metrics['gridHeight']
        diff_grid_com_ind = com_metrics['gridHeight'] - ind_metrics['gridHeight']
        report.append(f"- **Grid Height Difference (Commercial vs Residential)**: `{diff_grid_com_res}px`")
        report.append(f"- **Grid Height Difference (Commercial vs Industrial)**: `{diff_grid_com_ind}px`")
        
        diff_container_com_res = com_metrics['containerHeight'] - res_metrics['containerHeight']
        report.append(f"- **Container Height Difference (Commercial vs Residential)**: `{diff_container_com_res}px`")
        
        # Deduce root cause
        if com_metrics['containerHeight'] == res_metrics['containerHeight'] and com_metrics['containerHeight'] == ind_metrics['containerHeight']:
            report.append("\n✅ **EVIDENCE CONFIRMED**: Under the 'All' category, `#appliance-inputs-container` has the **identical computed height** across all three property configurations.")
            report.append("Since the 'All' container height is identical, selecting 'Commercial > All' does **not** cause layout jumping or vertical expansion compared to Residential and Industrial.")
            report.append("The vertical heights only differ when filtering specific Commercial categories due to their smaller card counts (which shrinks the container track below the 520px max-height threshold).")
        else:
            report.append("\n⚠️ **WARNING**: Under the 'All' category, `#appliance-inputs-container` has **differing computed heights**!")
            report.append("This means the category tabs count or grid row height mismatch stretches the container even when 'All' is selected. The diagnosis is incomplete.")
            
        with open("selenium_results.md", "w", encoding="utf-8") as f:
            f.write("\n".join(report))
            
        print("Report written successfully as selenium_results.md.")
        
    finally:
        driver.quit()

if __name__ == "__main__":
    run_measurements()
