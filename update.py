import re

with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

# 1. Move Trina and LONGi to datasheet-grid
# First extract Trina block
trina_pattern = re.compile(r'(\s*<!-- Trina Solar Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_trina = trina_pattern.search(html)
if m_trina:
    trina_block = m_trina.group(1)
    html = html.replace(trina_block, '')
else:
    trina_block = ''

# Extract LONGi block
longi_pattern = re.compile(r'(\s*<!-- LONGi Solar Brand -->.*?</div>\s*</div>)', re.DOTALL)
m_longi = longi_pattern.search(html)
if m_longi:
    longi_block = m_longi.group(1)
    html = html.replace(longi_block, '')
else:
    longi_block = ''

# Insert them before '    </div> <!-- Close datasheet-grid for flagship brands -->'
insert_point = '    </div> <!-- Close datasheet-grid for flagship brands -->'
html = html.replace(insert_point, trina_block + longi_block + '\n' + insert_point)

# 2. Add PDF Viewer Modal right after Brand Explorer Modal
pdf_modal_html = '''
    <!-- Embedded PDF Viewer Modal -->
    <div id="pdf-viewer-modal" class="ds-modal-overlay" aria-hidden="true" style="display: none;">
      <div class="ds-modal-content" style="max-width: 900px; height: 90vh; display: flex; flex-direction: column;">
        <button type="button" class="ds-modal-close" id="pdf-modal-close-btn" aria-label="Close">&times;</button>
        <div class="ds-modal-header" style="padding-bottom: 1rem;">
          <h3 id="pdf-modal-title">Product Datasheet</h3>
          <p class="text-muted" id="pdf-modal-brand"></p>
        </div>
        <div class="ds-modal-body" style="flex: 1; padding: 0; position: relative;">
          <iframe id="pdf-viewer-frame" style="width: 100%; height: 100%; border: none; background: #fff;"></iframe>
        </div>
        <div class="pdf-modal-footer" style="padding: 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
          <p style="margin-bottom: 1rem; font-weight: 600; color: #1e293b;">Would you like our team to send pricing and availability?</p>
          <button type="button" id="pdf-request-pricing-btn" class="btn btn-primary" style="padding: 0.75rem 2rem;">Yes, Request Pricing</button>
        </div>
      </div>
    </div>
'''

html = html.replace('<!-- Process -->', pdf_modal_html + '\n    <!-- Process -->')

# 3. Add localPdf to window.BrandProductsData
def repl_product(match):
    prod_str = match.group(0)
    m_key = re.search(r"key:\s*'([^']+)'", prod_str)
    if m_key:
        key = m_key.group(1)
        if 'pdfLink:' in prod_str:
            prod_str = re.sub(r"pdfLink:\s*'[^']+'", f"localPdf: 'datasheets/{key}.pdf'", prod_str)
        else:
            prod_str = prod_str.replace('}', f", localPdf: 'datasheets/{key}.pdf' }} ")
    return prod_str

html = re.sub(r'\{\s*key:\s*\'[^\']+\'.*?\}', repl_product, html)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
print('Updated index.php successfully!')
