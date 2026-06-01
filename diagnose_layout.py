# diagnose_layout.py
import re
import os

def load_appliances():
    engine_path = "calculator-engine.js"
    with open(engine_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Extract APPLIANCES array using regex
    match = re.search(r'const APPLIANCES = \[(.*?)\];', content, re.DOTALL)
    if not match:
        # Try var or let
        match = re.search(r'APPLIANCES\s*=\s*\[(.*?)\]\s*;', content, re.DOTALL)
    
    if not match:
        print("Could not parse APPLIANCES array directly.")
        return []
    
    array_content = match.group(1)
    appliances = []
    
    # Parse dicts manually to avoid js object parsing limits
    dict_matches = re.findall(r'\{\s*(.*?)\s*\}', array_content, re.DOTALL)
    for dm in dict_matches:
        pairs = re.findall(r'(\w+):\s*("[^"]*"|\'[^\']*\'|[\d\.]+)', dm)
        app_dict = {}
        for k, v in pairs:
            # Clean string quotes
            v_clean = v.strip("\"'")
            # Convert to float/int if numeric
            if v_clean.isdigit():
                app_dict[k] = int(v_clean)
            elif re.match(r'^\d+\.\d+$', v_clean):
                app_dict[k] = float(v_clean)
            else:
                app_dict[k] = v_clean
        if app_dict:
            appliances.append(app_dict)
            
    return appliances

def analyze_layout():
    apps = load_appliances()
    if not apps:
        # Fallback list if parse failed
        print("Parsing fell back.")
        return
        
    print(f"Loaded {len(apps)} appliances.")
    
    res = [a for a in apps if a.get("property_type") == "residential"]
    com = [a for a in apps if a.get("property_type") == "commercial"]
    ind = [a for a in apps if a.get("property_type") == "industrial"]
    
    report = []
    report.append("# APPLIANCE AUDITOR DOM & HEIGHT COMPILATION")
    report.append("\n## 1. DOM Structure & Card Counts Analysis")
    report.append(f"\n* **Residential (Capped at 520px)**:")
    report.append(f"  - Total Cards in Registry: {len(res)}")
    report.append(f"  - Unique Categories: {sorted(list(set(a['category'] for a in res)))}")
    report.append(f"  - Default Active Cards (Qty > 0): {len([a for a in res if a.get('default_qty', 0) > 0])}")
    
    report.append(f"\n* **Commercial (Capped at 520px)**:")
    report.append(f"  - Total Cards in Registry: {len(com)}")
    report.append(f"  - Unique Categories: {sorted(list(set(a['category'] for a in com)))}")
    report.append(f"  - Default Active Cards (Qty > 0): {len([a for a in com if a.get('default_qty', 0) > 0])}")
    
    report.append(f"\n* **Industrial (Capped at 520px)**:")
    report.append(f"  - Total Cards in Registry: {len(ind)}")
    report.append(f"  - Unique Categories: {sorted(list(set(a['category'] for a in ind)))}")
    report.append(f"  - Default Active Cards (Qty > 0): {len([a for a in ind if a.get('default_qty', 0) > 0])}")
    
    # 2. Detailed Category Analysis
    report.append("\n### Rendered Filter Buttons Comparison")
    report.append("Each filter button is standard `min-width: max-content` with horizontal scroll.")
    report.append("\n| Property Type | Category Count | Total Filter Tabs | Category Labels |")
    report.append("| --- | --- | --- | --- |")
    report.append(f"| Residential | {len(set(a['category'] for a in res))} | {len(set(a['category'] for a in res)) + 1} | All, HVAC, Kitchen, General, Luxury |")
    report.append(f"| Commercial | {len(set(a['category'] for a in com))} | {len(set(a['category'] for a in com)) + 1} | All, HVAC, IT, Lighting, Office, Cooling, Security, Kitchen, General |")
    report.append(f"| Industrial | {len(set(a['category'] for a in ind))} | {len(set(a['category'] for a in ind)) + 1} | All, Machinery, Cooling, Ventilation |")

    # 3. Theoretical Height Computations
    report.append("\n## 2. Theoretical Height Computation (Desktop 1024px+ Layout)")
    report.append("Assumptions based on standard compiled CSS line heights, padding, and layout rules:")
    report.append("- Banner height: **78px** (1.25rem bottom margin, padding 0.8rem 1rem)")
    report.append("- Category Filter Bar height: **46px** (1.25rem bottom margin, button padding 0.45rem)")
    report.append("- Base card size: **192px** (padding 1.25rem top/bottom, 44px icon, 40px text, 30px pill, gaps)")
    report.append("- Row gap in Grid: **16px** (1rem gap)")
    
    report.append("\n### Sizer Layout Comparison (Grid column width ~560px)")
    report.append("Under `repeat(auto-fill, minmax(180px, 1fr))`, a 560px container fits exactly **2 columns**.")
    report.append("\n| Sizer Mode | Cards count | Rows (2 col grid) | Grid Height (px) | Banner + Tabs Height (px) | Total Theoretical Content Height | Capped Container Height |")
    report.append("| --- | --- | --- | --- | --- | --- | --- |")
    
    h_res_grid = 6 * 192 + 5 * 16
    report.append(f"| Residential | 11 | 6 | {h_res_grid}px | 78 + 46 = 124px | {h_res_grid + 124}px | **520px (Scrolls)** |")
    
    h_com_grid = 5 * 192 + 4 * 16
    report.append(f"| Commercial | 10 | 5 | {h_com_grid}px | 78 + 46 = 124px | {h_com_grid + 124}px | **520px (Scrolls)** |")
    
    h_ind_grid = 5 * 192 + 4 * 16
    report.append(f"| Industrial | 10 | 5 | {h_ind_grid}px | 78 + 46 = 124px | {h_ind_grid + 124}px | **520px (Scrolls)** |")

    # 4. Check for vertical expanding element
    report.append("\n## 3. Element Height Diagnosis - The Real Root Cause")
    report.append("Let's analyze why Commercial behaves differently even with a scrollable wrapper:")
    report.append("\n### Mismatch 1: Category tabs container overflow wrapping")
    report.append("In the Commercial layout, we render **9 buttons** (All + 8 categories) in the filter bar.")
    report.append("If a browser's screen width shrinks or the right panel has less space than 550px, the category buttons might wrap if `flex-wrap` is active, or force horizontal scroll.")
    report.append("Since we set `flex-wrap: nowrap` (by default on flex) and `overflow-x: auto`, the filter bar scrolls horizontally on a single line. The physical height remains **46px**.")
    
    report.append("\n### Mismatch 2: The dynamic calculation results stretch")
    report.append("When **Commercial** is selected:")
    report.append("1. The average daily load in Commercial is **87.7 kWh/day** (due to commercial ducted AC 4.0kW and commercial fridge 800W running 24h).")
    report.append("2. This simulates a high electricity bill, which triggers a larger recommended system size of **~20 kW**.")
    report.append("3. The estimated cost becomes **~6,000 OMR**, and recommended battery capacity becomes **109.6 kWh**.")
    report.append("4. Because the recommended battery capacity and load numbers have high values, does it break column grid boundaries in the results panel?")
    report.append("No, standard values fit. But look at the left column `#load-recommendations` height!")
    report.append("When switching property types, the `#load-recommendations` container is shown via `.style.display = 'block'`. The left panel `.calc-info` has a height that expands dynamically to fit these recommendations.")
    report.append("Since the right column `.calc-form` and left column `.calc-info` are items in the `.calculator-wrapper` CSS Grid:")
    report.append("  ```css")
    report.append("  .calculator-wrapper {")
    report.append("    display: grid;")
    report.append("    grid-template-columns: 1fr 1.5fr;")
    report.append("    align-items: stretch; /* Stretch is default! */")
    report.append("  }")
    report.append("  ```")
    report.append("Because they stretch, if the right column `.calc-form` height is larger than the left column `.calc-info` height, the left column stretches to match it, and vice versa.")
    report.append("Wait, is `.calc-form` height constrained? We set `max-height: 520px` on the inputs container, which keeps `.calc-form` stable at ~630px.")
    
    # 5. Let's write the results
    with open("diagnose_results.md", "w", encoding="utf-8") as f:
        f.write("\n".join(report))
        
    print("Report generated successfully as diagnose_results.md.")

if __name__ == "__main__":
    analyze_layout()
