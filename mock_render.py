# mock_render.py
import re

# Load appliances
with open("calculator-engine.js", "r", encoding="utf-8") as f:
    engine_content = f.read()

match = re.search(r'const APPLIANCES = \[(.*?)\];', engine_content, re.DOTALL)
if not match:
    match = re.search(r'APPLIANCES\s*=\s*\[(.*?)\]\s*;', engine_content, re.DOTALL)

array_content = match.group(1)
apps = []
dict_matches = re.findall(r'\{\s*(.*?)\s*\}', array_content, re.DOTALL)
for dm in dict_matches:
    pairs = re.findall(r'(\w+):\s*("[^"]*"|\'[^\']*\'|[\d\.]+)', dm)
    app_dict = {}
    for k, v in pairs:
        v_clean = v.strip("\"'")
        if v_clean.isdigit():
            app_dict[k] = int(v_clean)
        elif re.match(r'^\d+\.\d+$', v_clean):
            app_dict[k] = float(v_clean)
        else:
            app_dict[k] = v_clean
    if app_dict:
        apps.append(app_dict)

# Load SVGs from script.js
with open("script.js", "r", encoding="utf-8") as f:
    script_content = f.read()

svgs = {}
svg_blocks = re.findall(r'(\w+):\s*`\s*<svg(.*?)</svg>\s*`', script_content, re.DOTALL)
for k, v in svg_blocks:
    svgs[k] = f"<svg{v}</svg>"

print(f"Loaded {len(svgs)} custom SVGs from script.js.")

# Simulate initApplianceSizer HTML for each type
for prop_type in ["residential", "commercial", "industrial"]:
    filtered = [a for a in apps if a.get("property_type") == prop_type]
    print(f"\n--- PROPERTY TYPE: {prop_type} ({len(filtered)} items) ---")
    
    # Check for unclosed divs or tags in each item's simulated HTML
    for app in filtered:
        app_id = app["id"]
        svg = svgs.get(app_id, "")
        if not svg:
            print(f"  [WARNING] No SVG found for: {app_id}")
            continue
            
        # Count tag pairs in SVG
        open_svg_tags = len(re.findall(r'<[a-zA-Z]+', svg))
        close_svg_tags = len(re.findall(r'</[a-zA-Z]+', svg))
        self_closing = len(re.findall(r'/>', svg))
        # Self-closing doesn't have a close tag, so open_svg_tags should equal close_svg_tags + self_closing
        if open_svg_tags != (close_svg_tags + self_closing):
            print(f"  [ERROR] Malformed SVG tag structure for {app_id}: open={open_svg_tags}, close={close_svg_tags}, self_closing={self_closing}")
            
        # Check standard template
        # Div template structure:
        # <div class="appliance-item...">
        #     <div class="active-badge"...></div>
        #     <div class="appliance-header-row">
        #         <div class="appliance-icon-wrapper">
        #             SVG
        #         </div>
        #     </div>
        #     <div class="appliance-body">
        #         <h4>...</h4>
        #         <div class="appliance-specs-badges">
        #             <span class="spec-badge power">...</span>
        #             <span class="spec-badge hours">...</span>
        #         </div>
        #         <div class="appliance-live-load" ...></div>
        #     </div>
        #     <div class="qty-selector-pill">
        #         <button ...>SVG</button>
        #         <span ...></span>
        #         <button ...>SVG</button>
        #     </div>
        # </div>
        
        # Parse individual tags inside the card
        # Let's count open vs close div tags inside the template (excluding the dynamic SVG)
        open_divs = 5 # base item, active-badge (Wait, active-badge is just a single div with class and text, closed), appliance-header-row, appliance-icon-wrapper, appliance-body, qty-selector-pill (Wait! qty-selector-pill is a div too!)
        # Let's write the exact template string and count
        card_html = f"""
        <div class="appliance-item" data-id="{app_id}" data-category="{app['category']}">
            <div class="active-badge" id="badge-{app_id}">0</div>
            <div class="appliance-header-row">
                <div class="appliance-icon-wrapper">
                    {svg}
                </div>
            </div>
            <div class="appliance-body">
                <h4>{app.get('name_en')}</h4>
                <div class="appliance-specs-badges">
                    <span class="spec-badge power">100W</span>
                    <span class="spec-badge hours">5h/d</span>
                </div>
                <div class="appliance-live-load" id="load-val-{app_id}"></div>
            </div>
            <div class="qty-selector-pill">
                <button type="button" class="qty-btn minus" data-id="{app_id}" disabled aria-label="Decrease">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                <span class="qty-val" id="qty-{app_id}">0</span>
                <button type="button" class="qty-btn plus" data-id="{app_id}" aria-label="Increase">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>
        </div>
        """
        
        # Count divs in the simulated card HTML
        c_open_divs = len(re.findall(r'<div', card_html))
        c_close_divs = len(re.findall(r'</div', card_html))
        if c_open_divs != c_close_divs:
            print(f"  [ERROR] Mismatch of <div> tags in simulated template for {app_id}: open={c_open_divs}, close={c_close_divs}")
        else:
            print(f"  Card {app_id}: Well-formed HTML (div open/close count matches: {c_open_divs})")
