import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Remove the closing and opening tags that separate the two grids.
target = '''    </div> <!-- Close datasheet-grid for flagship brands -->
  
      <!-- Expandable brand grid for remaining brands -->
      <div class="expandable-brand-grid" id="expandable-brands">'''
      
if target in html:
    html = html.replace(target, '')
    print('Merged grids successfully.')
else:
    # Try an alternative matching just in case whitespace is slightly different
    pattern = r'</div>\s*<!-- Close datasheet-grid for flagship brands -->\s*<!-- Expandable brand grid for remaining brands -->\s*<div class="expandable-brand-grid" id="expandable-brands">'
    html, count = re.subn(pattern, '', html)
    print(f'Merged grids using regex: {count} matches')

# 2. Add extra-brand and display:none to the specific brands
brands_to_hide = ['deye', 'powersun', 'trina', 'longi', 'jinko', 'ja', 'jebel']
for brand in brands_to_hide:
    # Match the card div for this brand
    # It looks like: <div class="datasheet-card brand-card" data-category="..." data-brand="brand">
    # Note that Trina Solar had extra-brand added previously but lost it when I reverted!
    # Let's ensure we only add it if it's not already there.
    
    def replacer(match):
        full_tag = match.group(0)
        if 'extra-brand' not in full_tag:
            full_tag = full_tag.replace('brand-card"', 'brand-card extra-brand" style="display: none;"')
        return full_tag

    html, n = re.sub(f'<div class="datasheet-card brand-card"[^>]*data-brand="{brand}"', replacer, html)
    print(f'Updated {brand}: {n} matches')

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
