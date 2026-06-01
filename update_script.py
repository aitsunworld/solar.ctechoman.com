import re

with open('script.js', 'r', encoding='utf-8') as f:
    js = f.read()

# Remove window.OFFICIAL_DATASHEET_URLS entirely
js = re.sub(r'\s*window\.OFFICIAL_DATASHEET_URLS\s*=\s*\{.*?\};\n', '\n', js, flags=re.DOTALL)

# Replace the btnHTML logic
old_logic = r'''                            const officialUrl = window.OFFICIAL_DATASHEET_URLS\[prod\.key\] \|\| '#';
                            const isUnavailable = !officialUrl \|\| officialUrl === '#' \|\| officialUrl\.endsWith\('powernsun\.com/'\) \|\| officialUrl\.includes\('ctechoman\.com'\);
                            
                            let btnHTML = '';
                            if \(isUnavailable\) \{.*?\} else \{.*?\}'''

new_logic = r'''                            const localPdf = prod.localPdf || datasheets/.pdf;
                            const downloadText = isArabic ? "????? ???? ????????" : "View Datasheet";

                            let btnHTML = 
                                <a href="#" class="btn-ds-download modal-download-btn pdf-viewer-trigger" data-pdf-url="" data-product="" data-brand="">
                                    <span></span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>
                            ;'''

js = re.sub(old_logic, new_logic, js, flags=re.DOTALL)

# Find where modalProductsGrid.querySelectorAll('.btn-ds-contact') is bound, and replace it
old_binding = r'''                // Bind click listener on all newly rendered Contact fallback buttons
                modalProductsGrid\.querySelectorAll\('\.btn-ds-contact'\)\.forEach\(btn => \{.*?\n                \}\);'''

new_binding = r'''                // Bind click listener on PDF Viewer triggers
                modalProductsGrid.querySelectorAll('.pdf-viewer-trigger').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const pdfUrl = btn.dataset.pdfUrl;
                        const prodTitle = btn.dataset.product;
                        const brandName = btn.dataset.brand;
                        
                        // Analytics Tracking
                        if (window.SolarAnalytics) {
                            window.SolarAnalytics.track('datasheet_view', {
                                product: prodTitle,
                                brand: brandName,
                                timestamp: new Date().toISOString()
                            });
                        } else {
                            console.log([Datasheet Tracking] View -  ());
                        }
                        
                        // Populate modal details
                        document.getElementById('pdf-modal-title').textContent = prodTitle;
                        document.getElementById('pdf-modal-brand').textContent = brandName;
                        document.getElementById('pdf-viewer-frame').src = pdfUrl;
                        
                        // Configure Pricing Button inside the PDF modal
                        const pricingBtn = document.getElementById('pdf-request-pricing-btn');
                        if (pricingBtn) {
                            // remove old listeners
                            const newPricingBtn = pricingBtn.cloneNode(true);
                            pricingBtn.parentNode.replaceChild(newPricingBtn, pricingBtn);
                            
                            newPricingBtn.addEventListener('click', () => {
                                document.getElementById('pdf-viewer-modal').style.display = 'none';
                                document.body.style.overflow = '';
                                
                                const contactSec = document.getElementById('contact');
                                if (contactSec) contactSec.scrollIntoView({ behavior: 'smooth' });
                                
                                const notesField = document.getElementById('lead-notes');
                                if (notesField) {
                                    notesField.value = isArabic
                                        ? ??????? ??? ??? ????? ?????:  ().
                                        : Hi, I would like to request pricing and availability for:  ().;
                                }
                                
                                setTimeout(() => {
                                    const nameField = document.getElementById('lead-name');
                                    if (nameField) nameField.focus();
                                }, 800);
                            });
                        }
                        
                        // Close underlying brand modal and open PDF modal
                        document.getElementById('brand-datasheet-modal').classList.remove('active');
                        setTimeout(() => {
                            document.getElementById('brand-datasheet-modal').style.display = 'none';
                            
                            const pdfModal = document.getElementById('pdf-viewer-modal');
                            pdfModal.style.display = 'flex';
                            setTimeout(() => pdfModal.classList.add('active'), 50);
                        }, 300);
                    });
                });'''

js = re.sub(old_binding, new_binding, js, flags=re.DOTALL)

# Add PDF Modal Close Button Listener globally
pdf_close_logic = '''
        const pdfModalCloseBtn = document.getElementById('pdf-modal-close-btn');
        if (pdfModalCloseBtn) {
            pdfModalCloseBtn.addEventListener('click', () => {
                const pdfModal = document.getElementById('pdf-viewer-modal');
                if (pdfModal) {
                    pdfModal.classList.remove('active');
                    setTimeout(() => {
                        pdfModal.style.display = 'none';
                        document.getElementById('pdf-viewer-frame').src = '';
                        document.body.style.overflow = '';
                    }, 300);
                }
            });
        }
'''

js = js.replace("        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);", 
                "        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);\n" + pdf_close_logic)

with open('script.js', 'w', encoding='utf-8') as f:
    f.write(js)
print('Updated script.js successfully!')
