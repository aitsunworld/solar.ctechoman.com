import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# Replace the closing of datasheet-grid and opening of expandable-brand-grid
target = '''    </div> <!-- Close datasheet-grid for flagship brands -->
  
      <!-- Expandable brand grid for remaining brands -->
      <div class="expandable-brand-grid" id="expandable-brands">'''
replacement = ''
html = html.replace(target, replacement)

# Add extra-brand class to Deye, Power & Sun, Trina, LONGi, Jinko, JA, Jebel
brands_to_hide = ['deye', 'power-and-sun', 'trina', 'longi', 'jinko', 'ja', 'jebel']
for brand in brands_to_hide:
    html = re.sub(
        f'<div class="datasheet-card brand-card" data-category="[^"]*" data-brand="{brand}">',
        lambda m: m.group(0).replace('brand-card"', 'brand-card extra-brand" style="display: none;"'),
        html
    )

# Fix power&sun which has data-brand="power-and-sun" -- wait, let's check its data-brand
html = re.sub(
    r'<div class="datasheet-card brand-card" data-category="[^"]*" data-brand="(deye|power-and-sun|trina|longi|jinko|ja|jebel)">',
    lambda m: m.group(0).replace('brand-card"', 'brand-card extra-brand" style="display: none;"'),
    html
)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
