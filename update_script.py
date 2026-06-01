with open('script.js', 'r', encoding='utf-8') as f:
    js = f.read()

target = '''                if (selectedCategory === 'all') {
                    // Reset to default flagship brand view + toggle controls
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'block';
                    
                    // Collapse or keep accordion in its native state
                    const isExpanded = expandableGrid && expandableGrid.classList.contains('expanded');
                    
                    dsCards.forEach(card => {
                        // Check if card is inside expandable grid
                        const inExpandable = card.closest('.expandable-brand-grid');
                        if (inExpandable) {
                            // If accordion is expanded, show it; otherwise hide it
                            card.style.display = 'flex';
                            card.style.opacity = isExpanded ? '1' : '0';
                            card.style.transform = 'scale(1)';
                        } else {
                            card.style.display = 'flex';
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }
                    });
                } else {
                    // Filtering: show matches cross-catalog, hide toggle button, force grid visible
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'none';
                    
                    dsCards.forEach(card => {'''

replacement = '''                if (selectedCategory === 'all') {
                    // Reset to default flagship brand view + toggle controls
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'block';
                    
                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = '';
                        expandableGrid.style.overflow = '';
                        expandableGrid.style.opacity = '';
                    }

                    // Collapse or keep accordion in its native state
                    const isExpanded = expandableGrid && expandableGrid.classList.contains('expanded');
                    
                    dsCards.forEach(card => {
                        // Check if card is inside expandable grid
                        const inExpandable = card.closest('.expandable-brand-grid');
                        if (inExpandable) {
                            // If accordion is expanded, show it; otherwise hide it
                            card.style.display = 'flex';
                            card.style.opacity = isExpanded ? '1' : '0';
                            card.style.transform = 'scale(1)';
                        } else {
                            card.style.display = 'flex';
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }
                    });
                } else {
                    // Filtering: show matches cross-catalog, hide toggle button, force grid visible
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'none';
                    
                    if (expandableGrid) {
                        expandableGrid.style.maxHeight = 'none';
                        expandableGrid.style.overflow = 'visible';
                        expandableGrid.style.opacity = '1';
                    }

                    dsCards.forEach(card => {'''

js = js.replace(target, replacement)

with open('script.js', 'w', encoding='utf-8') as f:
    f.write(js)
print('Updated script.js successfully!')
