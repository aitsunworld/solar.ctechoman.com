with open('style.css', 'r', encoding='utf-8') as f:
    css = f.read()

# Change base margin-top to 1.25rem
css = css.replace('margin-top: 1.5rem;\n    max-height: 2500px; /* High enough to contain the remaining 6 brand cards */', 'margin-top: 1.25rem;\n    max-height: 2500px; /* High enough to contain the remaining 6 brand cards */')

# Add margin-top: 1.5rem to the 768px media query block
target = '.expandable-brand-grid.expanded {\n      display: grid;\n      grid-template-columns: repeat(4, 1fr);\n      gap: 1.5rem;\n      align-items: stretch;\n    }'
replacement = target.replace('gap: 1.5rem;', 'gap: 1.5rem;\n      margin-top: 1.5rem;')
css = css.replace(target, replacement)

with open('style.css', 'w', encoding='utf-8') as f:
    f.write(css)
