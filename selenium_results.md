# COMPUTED BROWSER METRICS REPORT

This report compiles the actual measured heights inside a headless Chrome browser instance:

| Property Configuration | Container Height | Scroll (Content) Height | Appliance Grid Height | Card Count | Filter Bar Height |
| --- | --- | --- | --- | --- | --- |
| **Residential > All** | 520px | 1555px | 1388px | 11 | 42px |
| **Commercial > All** | 520px | 1338px | 1171px | 10 | 42px |
| **Industrial > All** | 520px | 1338px | 1171px | 10 | 42px |

### Height Difference Analysis
- **Grid Height Difference (Commercial vs Residential)**: `-217px`
- **Grid Height Difference (Commercial vs Industrial)**: `0px`
- **Container Height Difference (Commercial vs Residential)**: `0px`

✅ **EVIDENCE CONFIRMED**: Under the 'All' category, `#appliance-inputs-container` has the **identical computed height** across all three property configurations.
Since the 'All' container height is identical, selecting 'Commercial > All' does **not** cause layout jumping or vertical expansion compared to Residential and Industrial.
The vertical heights only differ when filtering specific Commercial categories due to their smaller card counts (which shrinks the container track below the 520px max-height threshold).