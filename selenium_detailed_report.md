# DETAILED LAYOUT AUDIT & COMPARISON REPORT

## 1. Generated HTML of `.appliance-grid` (Structure & IDs)

### Residential > All Grid HTML Structure
```html
<div class="appliance-grid">
        
                <div class="appliance-item active-card" data-id="ac_1ton" data-category="HVAC">
                    <div class="active-badge" id="badge-ac_1ton" style="display: flex;">1</div>
                    <div class="appliance-header-row">
                        <div class="appliance-icon-wrapper">
                            
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; transition: stroke 0.3s ease;">
                    <rect x="2" y="5" width="20" height="9" rx="2"></rect>
                    <path d="M2 9h20M6 14v1M18 14v1M12 9v5"></path>
                    <path d="M8 18c1 1.5 2 2 4 2s3-.5 4-2" stroke-dasharray="2 2"></path>
                </svg>
            
                        </div>
                    </div>
                    <div class="appliance-body">
                        <h4>AC 1 ton</h4>
                        <div class="appliance-specs-badges">
                            <span class="spec-badge power">1.2kW - 1.5kW</span>
                            <span class="spec-badge hours">8h/d</span>
... [truncated for readability]
```

### Commercial > All Grid HTML Structure
```html
<div class="appliance-grid">
        
                <div class="appliance-item active-card" data-id="com_ducted_ac" data-category="HVAC">
                    <div class="active-badge" id="badge-com_ducted_ac" style="display: flex;">1</div>
                    <div class="appliance-header-row">
                        <div class="appliance-icon-wrapper">
                            
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; transition: stroke 0.3s ease;">
                    <rect x="2" y="4" width="20" height="10" rx="2"></rect>
                    <path d="M2 9h20M6 14v2m12-2v2"></path>
                    <circle cx="8" cy="6.5" r="1"></circle>
                    <circle cx="16" cy="6.5" r="1"></circle>
                    <path d="M7 18c1.5 1.5 3 2 5 2s3.5-.5 5-2" stroke-dasharray="2 2"></path>
                </svg>
            
                        </div>
                    </div>
                    <div class="appliance-body">
                        <h4>Commercial Ducted/Standing AC</h4>
                        <div class="appliance-specs-badges">
... [truncated for readability]
```

### Industrial > All Grid HTML Structure
```html
<div class="appliance-grid">
        
                <div class="appliance-item active-card" data-id="ind_compressor" data-category="Machinery">
                    <div class="active-badge" id="badge-ind_compressor" style="display: flex;">1</div>
                    <div class="appliance-header-row">
                        <div class="appliance-icon-wrapper">
                            
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px; transition: stroke 0.3s ease;">
                    <circle cx="12" cy="12" r="9"></circle>
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                </svg>
            
                        </div>
                    </div>
                    <div class="appliance-body">
                        <h4>Industrial Air Compressor</h4>
                        <div class="appliance-specs-badges">
                            <span class="spec-badge power">15kW - 37kW</span>
                            <span class="spec-badge hours">12h/d</span>
... [truncated for readability]
```

## 2. Computed grid-template-columns Value
- **Residential**: `251.5px 251.5px`
- **Commercial**: `251.5px 251.5px`
- **Industrial**: `251.5px 251.5px`

## 3. Computed Width of Every `appliance-item` Card

### Residential Cards:
- Card `ac_1ton` (AC 1 ton): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `ac_2ton` (AC 2 ton): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `water_heater` (Water Heater): width = `252px`, class = `appliance-item`, style = ``
- Card `refrigerator` (Refrigerator): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `freezer` (Freezer): width = `252px`, class = `appliance-item`, style = ``
- Card `washing_machine` (Washing Machine): width = `252px`, class = `appliance-item`, style = ``
- Card `microwave` (Microwave): width = `252px`, class = `appliance-item`, style = ``
- Card `tv` (TV): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `led_lights` (LED Lights (Set of 10)): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `water_pump` (Water Pump): width = `252px`, class = `appliance-item`, style = ``
- Card `ev_charger` (EV Charger): width = `252px`, class = `appliance-item`, style = ``

### Commercial Cards:
- Card `com_ducted_ac` (Commercial Ducted/Standing AC): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `com_server_rack` (Network Rack Server): width = `252px`, class = `appliance-item`, style = ``
- Card `com_led_lighting` (Commercial LED Lighting Panels (100 pcs)): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `com_copier` (Heavy-Duty Office Copier/Printer): width = `252px`, class = `appliance-item`, style = ``
- Card `com_display_fridge` (Commercial Display Refrigerator): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `com_cctv` (CCTV Security System & NVR): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `com_workstation` (Desktop Workstations (PCs)): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `com_water_dispenser` (Commercial Water Dispenser): width = `252px`, class = `appliance-item`, style = ``
- Card `com_sliding_door` (Automatic Sliding Glass Doors): width = `252px`, class = `appliance-item`, style = ``
- Card `com_adv_signage` (LED Advertising Signage): width = `252px`, class = `appliance-item`, style = ``

### Industrial Cards:
- Card `ind_compressor` (Industrial Air Compressor): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `ind_chiller` (Industrial Water-Cooled Chiller): width = `252px`, class = `appliance-item`, style = ``
- Card `ind_water_pump` (Three-Phase Water/Fluid Pump (20 HP)): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `ind_molding_mach` (Plastic Injection Molding Machine): width = `252px`, class = `appliance-item`, style = ``
- Card `ind_gantry_crane` (Industrial Overhead Gantry Crane): width = `252px`, class = `appliance-item`, style = ``
- Card `ind_exhaust_fan` (Industrial Exhaust/Ventilation Fan): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `ind_welding_mach` (Industrial Rectifier Welding Machine): width = `252px`, class = `appliance-item`, style = ``
- Card `ind_conveyor` (Assembly Line Conveyor System): width = `252px`, class = `appliance-item active-card`, style = ``
- Card `ind_cnc_machine` (Heavy-Duty CNC Milling/Lathe Machine): width = `252px`, class = `appliance-item`, style = ``
- Card `ind_induction_furnace` (Small Industrial Induction Furnace): width = `252px`, class = `appliance-item`, style = ``

## 4. Computed Width of `.appliance-grid`
- **Residential**: `519px`
- **Commercial**: `519px`
- **Industrial**: `519px`

## 5. Computed Width of `.appliance-filter-bar`
- **Residential**: `519px` (scrollWidth = `605px`)
- **Commercial**: `519px` (scrollWidth = `1109px`)
- **Industrial**: `519px` (scrollWidth = `517px`)

## 6. Any Class Added ONLY in Commercial Mode
- `#appliance-inputs-container` unique classes: `None`
- `.appliance-filter-bar` unique classes: `None`
- `.appliance-item` unique classes: `None`

## 7. Any Inline Style Added ONLY in Commercial Mode
- `#appliance-inputs-container` inline style: `display: block; max-height: 480px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem; text-align: start;` (Residential: `display: block; max-height: 480px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem; text-align: start;`)
- `.appliance-filter-bar` inline style: `` (Residential: ``)

## 8. TEMPORARY TEST RULE RESULTS (`repeat(3, 1fr) !important`)
- **Commercial Grid Width**: `519px` (Baseline: `519px`)
- **Commercial Grid Computed Columns**: `162.641px 162.172px 162.188px` (Baseline: `251.5px 251.5px`)

### Commercial Card Widths under Temporary Test:
- Card `com_ducted_ac` (Commercial Ducted/Standing AC): width = `163px` (Baseline: `252px`)
- Card `com_server_rack` (Network Rack Server): width = `162px` (Baseline: `252px`)
- Card `com_led_lighting` (Commercial LED Lighting Panels (100 pcs)): width = `162px` (Baseline: `252px`)
- Card `com_copier` (Heavy-Duty Office Copier/Printer): width = `163px` (Baseline: `252px`)
- Card `com_display_fridge` (Commercial Display Refrigerator): width = `162px` (Baseline: `252px`)
- Card `com_cctv` (CCTV Security System & NVR): width = `162px` (Baseline: `252px`)
- Card `com_workstation` (Desktop Workstations (PCs)): width = `163px` (Baseline: `252px`)
- Card `com_water_dispenser` (Commercial Water Dispenser): width = `162px` (Baseline: `252px`)
- Card `com_sliding_door` (Automatic Sliding Glass Doors): width = `162px` (Baseline: `252px`)
- Card `com_adv_signage` (LED Advertising Signage): width = `163px` (Baseline: `252px`)