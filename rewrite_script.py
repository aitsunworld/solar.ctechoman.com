import re

with open('script.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Replace the entire filter and toggle section from:
# const expandableGrid = document.getElementById('expandable-brands');
# down to the end of the toggle logic (just before Brand-Centric Datasheet Modal Interactions)

target_pattern = re.compile(r"const expandableGrid = document\.getElementById\('expandable-brands'\);.*?// --- 8\. Brand-Centric Datasheet Modal Interactions ---", re.DOTALL)

new_logic = '''const brandToggleBtn = document.getElementById('brand-toggle-btn');
    const brandToggleWrapper = document.querySelector('.brand-toggle-wrapper');
    const datasheetGrid = document.querySelector('.datasheet-grid');
    const extraCards = document.querySelectorAll('.extra-brand');
    let isBrandsExpanded = false;
 
    if (dsFilterTabs.length > 0 && dsCards.length > 0) {
        dsFilterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                dsFilterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
 
                const selectedCategory = tab.dataset.category;
                
                if (selectedCategory === 'all') {
                    // Reset to default flagship brand view + toggle controls
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'block';
                    
                    dsCards.forEach(card => {
                        const isExtra = card.classList.contains('extra-brand');
                        if (isExtra && !isBrandsExpanded) {
                            card.style.display = 'none';
                            card.style.opacity = '0';
                        } else {
                            card.style.display = 'flex';
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }
                    });
                } else {
                    // Filtering: show matches cross-catalog, hide toggle button
                    if (brandToggleWrapper) brandToggleWrapper.style.display = 'none';
                    
                    dsCards.forEach(card => {
                        const categories = (card.dataset.category || '').split(' ');
                        if (categories.includes(selectedCategory)) {
                            card.style.display = 'flex';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                                card.style.opacity = '1';
                                card.style.transform = 'scale(1)';
                            }, 50);
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
            });
        });
    }

    // Toggle button handler
    if (brandToggleBtn && datasheetGrid && extraCards.length > 0) {
        brandToggleBtn.addEventListener('click', () => {
            isBrandsExpanded = !isBrandsExpanded;
            brandToggleBtn.classList.toggle('expanded', isBrandsExpanded);
            
            // Translations
            const labelShow = isArabic ? "??? ???? ???????? ???????? ?" : "View All Brands ?";
            const labelHide = isArabic ? "????? ??? ?" : "Show Less ?";
            
            const btnSpan = brandToggleBtn.querySelector('span');
            if (btnSpan) btnSpan.textContent = isBrandsExpanded ? labelHide : labelShow;

            if (isBrandsExpanded) {
                // Expand
                const collapsedHeight = datasheetGrid.offsetHeight;
                
                extraCards.forEach(card => {
                    card.style.display = 'flex';
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(10px)';
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                });

                const expandedHeight = datasheetGrid.scrollHeight;

                datasheetGrid.style.overflow = 'hidden';
                datasheetGrid.style.maxHeight = collapsedHeight + 'px';
                datasheetGrid.style.transition = 'max-height 0.4s ease-out';

                void datasheetGrid.offsetHeight; // Force reflow

                datasheetGrid.style.maxHeight = expandedHeight + 'px';

                setTimeout(() => {
                    extraCards.forEach(card => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    });
                }, 50);

                setTimeout(() => {
                    datasheetGrid.style.maxHeight = 'none';
                    datasheetGrid.style.overflow = 'visible';
                }, 400);

            } else {
                // Collapse
                const currentHeight = datasheetGrid.offsetHeight;
                
                datasheetGrid.style.overflow = 'hidden';
                datasheetGrid.style.maxHeight = currentHeight + 'px';
                datasheetGrid.style.transition = 'max-height 0.4s ease-out';
                
                extraCards.forEach(card => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(10px)';
                });

                void datasheetGrid.offsetHeight;

                extraCards.forEach(card => card.style.display = 'none');
                const collapsedHeight = datasheetGrid.scrollHeight;
                extraCards.forEach(card => card.style.display = 'flex');

                datasheetGrid.style.maxHeight = collapsedHeight + 'px';

                setTimeout(() => {
                    extraCards.forEach(card => {
                        card.style.display = 'none';
                        card.style.transform = '';
                        card.style.opacity = '';
                    });
                    datasheetGrid.style.maxHeight = 'none';
                    datasheetGrid.style.overflow = 'visible';
                }, 400);
            }
        });
    }

    // --- 8. Brand-Centric Datasheet Modal Interactions ---'''

js = target_pattern.sub(new_logic, js)

with open('script.js', 'w', encoding='utf-8') as f:
    f.write(js)
print('Updated script.js successfully!')
