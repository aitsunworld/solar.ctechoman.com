import re

with open('style.css', 'r', encoding='utf-8') as f:
    css = f.read()

# Fix default datasheet-grid and expandable-brand-grid
css = re.sub(
    r'\.datasheet-grid \{\s*display: flex;\s*flex-direction: column;\s*gap: 1\.25rem;\s*width: 100%;\s*\}',
    '.datasheet-grid {\n  display: grid;\n  grid-template-columns: repeat(2, 1fr);\n  gap: 1.25rem;\n  width: 100%;\n}',
    css
)

css = re.sub(
    r'\.expandable-brand-grid \{\s*max-height: 0;\s*opacity: 0;\s*overflow: hidden;\s*transition:.*?\s*width: 100%;\s*display: flex;\s*flex-direction: column;\s*gap: 1\.25rem;\s*\}',
    '.expandable-brand-grid {\n  max-height: 0;\n  opacity: 0;\n  overflow: hidden;\n  transition: max-height 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease, margin-top 0.4s ease;\n  width: 100%;\n  display: grid;\n  grid-template-columns: repeat(2, 1fr);\n  gap: 1.25rem;\n}',
    css,
    flags=re.DOTALL
)

# In media queries, we need to update it to 4 columns.
# Let's find .datasheet-grid,\n  .expandable-brand-grid.expanded { in the media queries and replace it.
# The user's CSS has repeat(auto-fit, minmax(280px, 1fr)). We will change it to repeat(4, 1fr).
css = re.sub(
    r'\.datasheet-grid,\s*\.expandable-brand-grid\.expanded \{\s*grid-template-columns: repeat\(auto-fit, minmax\(280px, 1fr\)\);\s*display: grid;\s*gap: 1\.5rem;\s*align-items: stretch;\s*\}',
    '.datasheet-grid, .expandable-brand-grid.expanded {\n    grid-template-columns: repeat(4, 1fr);\n    display: grid;\n    gap: 1.5rem;\n    align-items: stretch;\n  }',
    css
)

css = re.sub(
    r'\.datasheet-grid,\s*\.expandable-brand-grid\.expanded \{\s*grid-template-columns: repeat\(auto-fit, minmax\(280px, 1fr\)\);\s*align-items: stretch;\s*\}',
    '.datasheet-grid, .expandable-brand-grid.expanded {\n    grid-template-columns: repeat(4, 1fr);\n    align-items: stretch;\n  }',
    css
)

# And standalone .expandable-brand-grid.expanded in media queries
css = re.sub(
    r'\.expandable-brand-grid\.expanded \{\s*display: grid;\s*grid-template-columns: repeat\(auto-fit, minmax\(280px, 1fr\)\);\s*gap: 1\.5rem;\s*align-items: stretch;\s*\}',
    '.expandable-brand-grid.expanded {\n    display: grid;\n    grid-template-columns: repeat(4, 1fr);\n    gap: 1.5rem;\n    align-items: stretch;\n  }',
    css
)

with open('style.css', 'w', encoding='utf-8') as f:
    f.write(css)
print('Updated style.css successfully!')
