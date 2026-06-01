import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# Extract Deye
deye_pattern = re.compile(r'(\s*<!-- Deye Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_deye = deye_pattern.search(html)
deye_block = m_deye.group(1) if m_deye else ''

# Extract PowerSun
power_pattern = re.compile(r'(\s*<!-- Power & Sun Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_power = power_pattern.search(html)
power_block = m_power.group(1) if m_power else ''

# Extract Trina
trina_pattern = re.compile(r'(\s*<!-- Trina Solar Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_trina = trina_pattern.search(html)
trina_block = m_trina.group(1) if m_trina else ''

# Extract LONGi
longi_pattern = re.compile(r'(\s*<!-- LONGi Solar Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_longi = longi_pattern.search(html)
longi_block = m_longi.group(1) if m_longi else ''

# Remove them from html
html = html.replace(deye_block, '')
html = html.replace(power_block, '')
html = html.replace(trina_block, '')
html = html.replace(longi_block, '')

# Add them to expandable-brand-grid
insert_point = '    <div class="expandable-brand-grid" id="expandable-brands">'
replacement = insert_point + '\n' + deye_block + power_block + trina_block + longi_block
html = html.replace(insert_point, replacement)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
print('Updated index.php layout successfully!')
