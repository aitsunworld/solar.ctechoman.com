import re
import bs4

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

soup = bs4.BeautifulSoup(html, 'html.parser')

datasheet_section = soup.find('section', id='datasheets')

all_cards = soup.find_all('div', class_='brand-card')

order = ['huawei', 'sungrow', 'solis', 'canadian', 'deye', 'powersun', 'trina', 'longi', 'jinko', 'ja', 'jebel']
ordered_cards = []

for brand in order:
    for card in all_cards:
        if card.get('data-brand') == brand:
            if 'style' in card.attrs:
                del card['style']
            classes = card.get('class', [])
            if 'extra-brand' in classes:
                classes.remove('extra-brand')
            
            card['class'] = ['datasheet-card', 'brand-card']
            
            if order.index(brand) >= 4:
                card['class'].append('extra-brand')
                card['style'] = 'display: none;'
                
            ordered_cards.append(card)
            break

filter_tabs = datasheet_section.find('div', class_='datasheet-filter-bar')

new_grid = soup.new_tag('div', **{'class': 'datasheet-grid'})
for card in ordered_cards:
    new_grid.append(card)

for grid in datasheet_section.find_all('div', class_='datasheet-grid'):
    grid.extract()
for grid in datasheet_section.find_all('div', class_='expandable-brand-grid'):
    grid.extract()
    
for card in all_cards:
    if card.parent and card.parent.name != 'div' or ('class' in card.parent.attrs and 'datasheet-grid' not in card.parent['class']):
         card.extract()

filter_tabs.insert_after(new_grid)

toggle_wrapper = datasheet_section.find('div', class_='brand-toggle-wrapper')
if toggle_wrapper:
    toggle_wrapper.extract()
    new_grid.insert_after(toggle_wrapper)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(str(soup))
print('Successfully rebuilt grid.')
