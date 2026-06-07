import re
import os

with open('preview.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Find insights-row
match = re.search(r'<div class="[^"]*?insights-row[^"]*?">(.*?)</div>\s*<!--\s*/insights-row\s*-->', html, re.DOTALL)
if not match:
    match = re.search(r'<div\s+[^>]*?class="[^"]*?insights-row[^"]*?"[^>]*?>(.*?)</div>', html, re.DOTALL)

if match:
    content = match.group(1)
    os.makedirs('scratch', exist_ok=True)
    
    # Split by Column comments
    cols = re.split(r'<!--\s*Col \d+:\s*', content)
    col_num = 1
    for col in cols:
        if 'col-insights' in col:
            with open(f'scratch/col{col_num}.html', 'w', encoding='utf-8') as f_out:
                f_out.write(col.strip())
            print(f"Saved scratch/col{col_num}.html")
            col_num += 1
else:
    print("Could not find insights-row.")
