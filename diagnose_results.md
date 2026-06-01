# APPLIANCE AUDITOR DOM & HEIGHT COMPILATION

## 1. DOM Structure & Card Counts Analysis

* **Residential (Capped at 520px)**:
  - Total Cards in Registry: 11
  - Unique Categories: ['General', 'HVAC', 'Kitchen', 'Luxury']
  - Default Active Cards (Qty > 0): 5

* **Commercial (Capped at 520px)**:
  - Total Cards in Registry: 10
  - Unique Categories: ['Cooling', 'General', 'HVAC', 'IT', 'Kitchen', 'Lighting', 'Office', 'Security']
  - Default Active Cards (Qty > 0): 5

* **Industrial (Capped at 520px)**:
  - Total Cards in Registry: 10
  - Unique Categories: ['Cooling', 'Machinery', 'Ventilation']
  - Default Active Cards (Qty > 0): 4

### Rendered Filter Buttons Comparison
Each filter button is standard `min-width: max-content` with horizontal scroll.

| Property Type | Category Count | Total Filter Tabs | Category Labels |
| --- | --- | --- | --- |
| Residential | 4 | 5 | All, HVAC, Kitchen, General, Luxury |
| Commercial | 8 | 9 | All, HVAC, IT, Lighting, Office, Cooling, Security, Kitchen, General |
| Industrial | 3 | 4 | All, Machinery, Cooling, Ventilation |

## 2. Theoretical Height Computation (Desktop 1024px+ Layout)
Assumptions based on standard compiled CSS line heights, padding, and layout rules:
- Banner height: **78px** (1.25rem bottom margin, padding 0.8rem 1rem)
- Category Filter Bar height: **46px** (1.25rem bottom margin, button padding 0.45rem)
- Base card size: **192px** (padding 1.25rem top/bottom, 44px icon, 40px text, 30px pill, gaps)
- Row gap in Grid: **16px** (1rem gap)

### Sizer Layout Comparison (Grid column width ~560px)
Under `repeat(auto-fill, minmax(180px, 1fr))`, a 560px container fits exactly **2 columns**.

| Sizer Mode | Cards count | Rows (2 col grid) | Grid Height (px) | Banner + Tabs Height (px) | Total Theoretical Content Height | Capped Container Height |
| --- | --- | --- | --- | --- | --- | --- |
| Residential | 11 | 6 | 1232px | 78 + 46 = 124px | 1356px | **520px (Scrolls)** |
| Commercial | 10 | 5 | 1024px | 78 + 46 = 124px | 1148px | **520px (Scrolls)** |
| Industrial | 10 | 5 | 1024px | 78 + 46 = 124px | 1148px | **520px (Scrolls)** |

## 3. Element Height Diagnosis - The Real Root Cause
Let's analyze why Commercial behaves differently even with a scrollable wrapper:

### Mismatch 1: Category tabs container overflow wrapping
In the Commercial layout, we render **9 buttons** (All + 8 categories) in the filter bar.
If a browser's screen width shrinks or the right panel has less space than 550px, the category buttons might wrap if `flex-wrap` is active, or force horizontal scroll.
Since we set `flex-wrap: nowrap` (by default on flex) and `overflow-x: auto`, the filter bar scrolls horizontally on a single line. The physical height remains **46px**.

### Mismatch 2: The dynamic calculation results stretch
When **Commercial** is selected:
1. The average daily load in Commercial is **87.7 kWh/day** (due to commercial ducted AC 4.0kW and commercial fridge 800W running 24h).
2. This simulates a high electricity bill, which triggers a larger recommended system size of **~20 kW**.
3. The estimated cost becomes **~6,000 OMR**, and recommended battery capacity becomes **109.6 kWh**.
4. Because the recommended battery capacity and load numbers have high values, does it break column grid boundaries in the results panel?
No, standard values fit. But look at the left column `#load-recommendations` height!
When switching property types, the `#load-recommendations` container is shown via `.style.display = 'block'`. The left panel `.calc-info` has a height that expands dynamically to fit these recommendations.
Since the right column `.calc-form` and left column `.calc-info` are items in the `.calculator-wrapper` CSS Grid:
  ```css
  .calculator-wrapper {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    align-items: stretch; /* Stretch is default! */
  }
  ```
Because they stretch, if the right column `.calc-form` height is larger than the left column `.calc-info` height, the left column stretches to match it, and vice versa.
Wait, is `.calc-form` height constrained? We set `max-height: 520px` on the inputs container, which keeps `.calc-form` stable at ~630px.