with open('script.js', 'r', encoding='utf-8') as f:
    js = f.read()

target = '''                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = 'none';
                        expandableGrid.style.overflow = 'visible';
                        expandableGrid.style.opacity = '1';
                    }'''

replacement = '''                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = 'none';
                        expandableGrid.style.overflow = 'visible';
                        expandableGrid.style.opacity = '1';
                        expandableGrid.style.marginTop = window.innerWidth >= 768 ? '1.5rem' : '1.25rem';
                    }'''

js = js.replace(target, replacement)

target2 = '''                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = '';
                        expandableGrid.style.overflow = '';
                        expandableGrid.style.opacity = '';
                    }'''

replacement2 = '''                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = '';
                        expandableGrid.style.overflow = '';
                        expandableGrid.style.opacity = '';
                        expandableGrid.style.marginTop = '';
                    }'''

js = js.replace(target2, replacement2)

with open('script.js', 'w', encoding='utf-8') as f:
    f.write(js)
