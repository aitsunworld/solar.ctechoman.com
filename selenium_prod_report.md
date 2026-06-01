# PRODUCTION LAYOUT AUDIT REPORT

Live URL Audited: `https://solar.ctechoman.com`

## 1. Loaded Stylesheets on Production
- `https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap`
- `https://solar.ctechoman.com/style.css?v=4.0`
- `https://solar.ctechoman.com/chatbot.css?v=3.1`

## 2. Production CSS Server HTTP Headers

### `css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap`
- **Last-Modified**: `Not Provided`
- **Cache-Control**: `private, max-age=86400`
- **Server-Date**: `Mon, 01 Jun 2026 10:31:34 GMT`

### `style.css?v=4.0`
- **Last-Modified**: `Mon, 01 Jun 2026 04:59:13 GMT`
- **Cache-Control**: `public, max-age=604800`
- **Server-Date**: `Mon, 01 Jun 2026 10:31:34 GMT`

### `chatbot.css?v=3.1`
- **Last-Modified**: `Fri, 22 May 2026 04:20:17 GMT`
- **Cache-Control**: `public, max-age=604800`
- **Server-Date**: `Mon, 01 Jun 2026 10:31:34 GMT`

## 3. Production Computed Widths (at 1200px viewport)
| Mode | `.calculator-wrapper` | `.calc-form` (Right) | `.appliance-filter-bar` (Visible / Scroll) | `.appliance-grid` (Visible) | Grid Columns count / template |
| --- | --- | --- | --- | --- | --- |
| **Residential** | 1103px | 624px | 607px / 605px | 607px | 3 cols (`191.609px 191.609px 191.609px`) |
| **Commercial** | 1103px | 1128px | 1111px / 1109px | 1111px | 5 cols (`209.453px 209.453px 209.453px 209.453px 209.469px`) |
| **Industrial** | 1103px | 537px | 520px / 518px | 520px | 2 cols (`252px 252px`) |

## 4. Comparison with Local Workspace Measurements

Since our local headless baseline measured:
- Commercial `.calculator-wrapper`: `1103px`
- Commercial `.calc-form`: `537px`
- Commercial `.appliance-grid`: `519px` (2 columns)

Production vs Local Baseline difference:
- **Wrapper Width Difference**: `0px`
- **Form Width Difference**: `591px`
- **Grid Width Difference**: `592px`

🚨 **CONFIRMED DIFFERENCE**: Production exhibits horizontal stretching (**1128px** right panel width, **5** columns) that does not occur in local headless tests!
This points to a styling mismatch (either production has CSS caching or is serving a different stylesheet).