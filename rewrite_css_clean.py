import re

with open('style.css', 'r', encoding='utf-8') as f:
    css = f.read()

# 1. Remove ALL references to .expandable-brand-grid
css = re.sub(r'\.expandable-brand-grid[^{]*\{[^}]*\}', '', css)
css = css.replace(', .expandable-brand-grid.expanded', '')

# 2. Fix the 768px media query block for datasheet-grid
# It currently has grid-template-columns: repeat(4, 1fr);
# We want it to be 3
target_768 = '''.datasheet-grid {
      grid-template-columns: repeat(4, 1fr);
      display: grid;
      gap: 1.5rem;
      align-items: stretch;
    }'''
replacement_768 = '''.datasheet-grid {
      grid-template-columns: repeat(3, 1fr);
      display: grid;
      gap: 1.5rem;
      align-items: stretch;
    }'''
css = css.replace(target_768, replacement_768)

target_768_alt = '''.datasheet-grid {
      grid-template-columns: repeat(4, 1fr);
      align-items: stretch;
    }'''
replacement_768_alt = '''.datasheet-grid {
      grid-template-columns: repeat(3, 1fr);
      align-items: stretch;
    }'''
css = css.replace(target_768_alt, replacement_768_alt)

# 3. Add the 1024px media query for datasheet-grid
# Find @media (min-width: 1024px) {
css = re.sub(
    r'(@media \(min-width: 1024px\) \{)',
    r'\1\n  .datasheet-grid {\n    grid-template-columns: repeat(4, 1fr);\n  }',
    css
)

with open('style.css', 'w', encoding='utf-8') as f:
    f.write(css)
print('Updated style.css completely.')
