with open('index.php', 'r', encoding='utf-8') as f:
    html = f.read()

target = '''      <div class="brand-toggle-wrapper" style="text-align: center; margin-top: 2rem;">
        <button type="button" id="brand-toggle-btn" class="btn btn-secondary">
          <span><?= ['brand_toggle_show'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem; transition: transform 0.3s ease;">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
      </div>'''

replacement = '''      <div class="brand-toggle-wrapper" style="text-align: center; margin-top: 2rem;">
        <button type="button" id="brand-toggle-btn" class="btn btn-secondary">
          <span><?=  === 'ar' ? '??? ???? ???????? ???????? &darr;' : 'View All Brands &darr;' ?></span>
        </button>
      </div>'''

html = html.replace(target, replacement)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write(html)
print('Updated index.php toggle button successfully!')
