import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Find the grid section boundaries
start_idx = html.find('<div class="datasheet-grid">')
end_idx = html.find('<!-- Centered Glassy Accordion Toggle Button -->')

if start_idx == -1 or end_idx == -1:
    print('Could not find grid boundaries')
    exit()

# Wait, there's a </div> before the toggle button
# Let's include that in the slice
grid_section = html[start_idx:end_idx]

# A card starts with <!-- [Name] Brand --> and ends with the </div> that closes the card.
# A regex to match a card: <!--.*?Brand -->\s*<div class="datasheet-card.*?</button>\s*</div>
card_pattern = re.compile(r'<!--.*?Brand -->\s*<div class="datasheet-card.*?</button>\s*</div>', re.DOTALL)

cards = card_pattern.findall(grid_section)
print(f'Found {len(cards)} total cards in the section.')

seen_brands = set()
unique_cards = []

for card in cards:
    # Extract brand id to deduplicate
    m = re.search(r'data-brand="([^"]+)"', card)
    if m:
        brand = m.group(1)
        if brand not in seen_brands:
            seen_brands.add(brand)
            unique_cards.append(card)

print(f'Found {len(unique_cards)} unique cards.')

# Ensure they are in the correct order:
order = ['huawei', 'sungrow', 'solis', 'canadian', 'deye', 'powersun', 'trina', 'longi', 'jinko', 'ja', 'jebel']
ordered_cards = []
for o in order:
    for c in unique_cards:
        if f'data-brand="{o}"' in c:
            ordered_cards.append(c)
            break

# Reconstruct the grid section
new_grid_section = '<div class="datasheet-grid">\n\n  ' + '\n\n  '.join(ordered_cards) + '\n\n      </div>\n\n      '

html = html[:start_idx] + new_grid_section + html[end_idx:]

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
