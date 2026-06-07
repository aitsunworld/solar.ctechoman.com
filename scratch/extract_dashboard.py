import re
import sys

if sys.version_info >= (3, 7):
    sys.stdout.reconfigure(encoding='utf-8')

with open('preview.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's find the div with class "insights-row"
insights_match = re.search(r'<div class="[^"]*?insights-row[^"]*?">(.*?)</div>\s*<!--\s*/insights-row\s*-->', html, re.DOTALL)
if not insights_match:
    # Fallback search for insights-row
    insights_match = re.search(r'<div\s+[^>]*?class="[^"]*?insights-row[^"]*?"[^>]*?>(.*?)</div>', html, re.DOTALL)

if insights_match:
    content = insights_match.group(1)
    print("=== Found insights-row content ===")
    # Find separate columns by comments
    cols = re.split(r'<!--\s*Col \d+:\s*', content)
    for idx, col in enumerate(cols):
        if col.strip():
            print(f"\n--- Column {idx} ---")
            print(col.strip()[:3000])
else:
    # If insights-row not found, search for dashboard-section col-insights
    print("insights-row not found. Searching for col-insights...")
    matches = re.findall(r'<div class="[^"]*?col-insights[^"]*?">.*?</div>', html, re.DOTALL)
    for idx, match in enumerate(matches):
        print(f"\n--- Col {idx} ---")
        print(match[:2000])
