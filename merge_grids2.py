import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

pattern = r'</div>\s*<!-- Close datasheet-grid for flagship brands -->\s*<!-- Expandable brand grid for remaining brands -->\s*<div class="expandable-brand-grid" id="expandable-brands">'
html, count = re.subn(pattern, '', html)
print(f'Merged grids using regex: {count} matches')

brands_to_hide = ['deye', 'powersun', 'trina', 'longi', 'jinko', 'ja', 'jebel']
for brand in brands_to_hide:
    def replacer(match):
        full_tag = match.group(0)
        if 'extra-brand' not in full_tag:
            full_tag = full_tag.replace('brand-card"', 'brand-card extra-brand" style="display: none;"')
        return full_tag

    html, n = re.subn(f'<div class="datasheet-card brand-card"[^>]*data-brand="{brand}"', replacer, html)
    print(f'Updated {brand}: {n} matches')

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
