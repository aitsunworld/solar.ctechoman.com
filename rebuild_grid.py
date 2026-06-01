import re

brands = [
    ('Huawei', 'inverter', 'huawei', 'huawei.svg', 'Huawei', 'brand_huawei_tag'),
    ('Sungrow', 'inverter', 'sungrow', 'sungrow.png', 'Sungrow', 'brand_sungrow_tag'),
    ('Solis', 'inverter battery', 'solis', 'solis.png', 'Solis', 'brand_solis_tag'),
    ('Canadian Solar', 'inverter panel battery', 'canadian', 'canadian_solar.png', 'Canadian Solar', 'brand_canadian_tag'),
    ('Deye', 'inverter battery', 'deye', 'deye.png', 'Deye', 'brand_deye_tag'),
    ('Power & Sun', 'inverter battery', 'powersun', 'power_sun.png', 'Power & Sun', 'brand_powersun_tag'),
    ('Trina Solar', 'panel', 'trina', 'trina_solar.svg', 'Trina Solar', 'brand_trina_tag'),
    ('LONGi', 'panel battery', 'longi', 'longi.svg', 'LONGi', 'brand_longi_tag'),
    ('Jinko Solar', 'panel', 'jinko', 'jinko_solar.png', 'Jinko Solar', 'brand_jinko_tag'),
    ('JA Solar', 'panel', 'ja', 'ja_solar.svg', 'JA Solar', 'brand_ja_tag'),
    ('Jebel', 'battery', 'jebel', 'jebel.png', 'Jebel', 'brand_jebel_tag')
]

cards_html = []
for i, b in enumerate(brands):
    comment, category, brand_id, img, title, lang_tag = b
    extra = ' extra-brand" style="display: none;"' if i >= 4 else '"'
    
    card = f'''        <!-- {comment} Brand -->
        <div class="datasheet-card brand-card{extra} data-category="{category}" data-brand="{brand_id}">
          <div class="brand-card-accent"></div>
          <div class="brand-logo-wrapper">
            <img src="brands/{img}" alt="{comment}" class="brand-logo-img" loading="lazy">
          </div>
          <div class="brand-content">
            <h3>{title}</h3>
            <p><?= ['{lang_tag}'] ?></p>
          </div>
          <button type="button" class="btn-brand-explore">
            <span><?= ['brand_btn_explore'] ?></span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
        </div>'''
    cards_html.append(card)

grid_content = '\n\n'.join(cards_html)

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# The grid section starts after <div class="datasheet-grid">
# and ends before <!-- Centered Glassy Accordion Toggle Button -->
pattern = re.compile(r'(<div class="datasheet-grid">).*?(?=<!-- Centered Glassy Accordion Toggle Button -->)', re.DOTALL)

# Ensure the replacement includes the closing </div> for datasheet-grid
replacement = r'\1\n' + grid_content.replace('\\', '\\\\') + '\n      </div>\n\n      '

html, count = pattern.subn(replacement, html)
print(f'Replaced {count} instances of grid.')

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
