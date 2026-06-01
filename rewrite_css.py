import re

with open('style.css', 'r', encoding='utf-8') as f:
    css = f.read()

# Completely remove .expandable-brand-grid classes
css = re.sub(r'\.expandable-brand-grid[^\{]*\{[^}]*\}', '', css)

# Update datasheet-grid responsive rules
# Base is 2 columns (mobile)
# Tablet is 3 columns (min-width: 768px)
# Desktop is 4 columns (min-width: 1024px)

css = re.sub(
    r'\.datasheet-grid \{\s*display: grid;\s*grid-template-columns: repeat\(2, 1fr\);\s*gap: 1\.25rem;\s*width: 100%;\s*\}',
    '.datasheet-grid {\n  display: grid;\n  grid-template-columns: repeat(2, 1fr);\n  gap: 1.25rem;\n  width: 100%;\n}',
    css
)

css = re.sub(
    r'\.datasheet-grid \{\s*grid-template-columns: repeat\(4, 1fr\);\s*display: grid;\s*gap: 1\.5rem;\s*align-items: stretch;\s*\}',
    '.datasheet-grid {\n    grid-template-columns: repeat(3, 1fr);\n    gap: 1.5rem;\n    align-items: stretch;\n  }',
    css
)

css = re.sub(
    r'@media \(min-width: 1024px\) \{.*?\/\* Toggle button styling \*\/',
    '@media (min-width: 1024px) {\n  .datasheet-grid {\n    grid-template-columns: repeat(4, 1fr);\n  }\n}\n\n/* Toggle button styling */',
    css,
    flags=re.DOTALL
)

# Wait, the regexes above might be fragile. Let's just append the media queries cleanly, or parse it properly.
# The previous state of style.css had:
# @media (min-width: 768px) {
#   .datasheet-grid, .expandable-brand-grid.expanded {
#       grid-template-columns: repeat(4, 1fr);

# Let's just do a string replacement for the media query sections.
