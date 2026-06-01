document.addEventListener('DOMContentLoaded', () => {
    "use strict";

    // --- 1. Mobile Menu Drawer ---
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenuBtn.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        document.querySelectorAll('.nav-link, .nav-links .btn').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuBtn.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    }

    // --- 2. Navbar Scroll Style Trigger ---
    const navbar = document.getElementById('navbar');
    if (navbar) {
        let lastScrollY = 0;
        let ticking = false;
        
        window.addEventListener('scroll', () => {
            lastScrollY = window.scrollY;
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    if (lastScrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    // --- 3. Scroll Reveal Animations (Intersection Observer) ---
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    const revealOptions = {
        threshold: 0.05,            // Trigger as soon as 5% of the element is visible
        rootMargin: "0px 0px -10px 0px" // Trigger almost immediately upon entering viewport
    };

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);

    revealElements.forEach(el => {
        revealObserver.observe(el);
    });

    // --- 4. FAQ Accordion ---
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            
            // Close all other open accordions
            document.querySelectorAll('.accordion-header').forEach(otherHeader => {
                if (otherHeader !== header) {
                    otherHeader.classList.remove('active');
                    otherHeader.nextElementSibling.style.maxHeight = null;
                }
            });
            
            // Toggle current accordion
            header.classList.toggle('active');
            if (header.classList.contains('active')) {
                content.style.maxHeight = content.scrollHeight + "px";
            } else {
                content.style.maxHeight = null;
            }
        });
    });

    // --- 5. Dual-Mode Solar Sizer & Calculation Bindings ---
    const tabBill = document.getElementById('tab-bill');
    const tabAppliances = document.getElementById('tab-appliances');
    const billInputs = document.getElementById('bill-inputs-container');
    const applianceInputs = document.getElementById('appliance-inputs-container');
    const loadRecs = document.getElementById('load-recommendations');

    const billSlider = document.getElementById('bill-slider');
    const billDisplay = document.getElementById('bill-display');
    const propType = document.getElementById('property-type');
    const loc = document.getElementById('location');

    const resSize = document.getElementById('res-size');
    const resPanels = document.getElementById('res-panels');
    const resCost = document.getElementById('res-cost');
    const resSavings = document.getElementById('res-savings');

    const resInverter = document.getElementById('res-inverter');
    const resBattery = document.getElementById('res-battery');
    const resConnectedLoad = document.getElementById('res-connected-load');
    const resDailyConsumption = document.getElementById('res-daily-consumption');

    let activeSizerMode = 'bill'; // 'bill' | 'appliances'
    const applianceQuantities = {};

    // Get active language context
    const isArabic = document.documentElement.lang === 'ar';

    // Categories mapping for translation
    const categoryLabels = {
        Kitchen: isArabic ? "المطبخ" : "Kitchen Appliances",
        HVAC: isArabic ? "تكييف وتدفئة" : "HVAC & Heavy Loads",
        General: isArabic ? "أجهزة عامة ومعيشة" : "General & Living",
        Luxury: isArabic ? "رفاهية ومسابح" : "Villa & Luxury Load",
        Security: isArabic ? "أنظمة الأمان والذكية" : "Security & Smart Home",
        IT: isArabic ? "تكنولوجيا المعلومات" : "IT & Servers",
        Lighting: isArabic ? "الإضاءة" : "Lighting System",
        Office: isArabic ? "المكتب" : "Office Equipment",
        Cooling: isArabic ? "تبريد" : "Cooling & Chillers",
        Machinery: isArabic ? "آلات ومعدات" : "Heavy Machinery",
        Ventilation: isArabic ? "تهوية" : "Ventilation System"
    };

    // Render Appliance Registry dynamically
    function initApplianceSizer() {
        if (!applianceInputs || !window.SolarCalculatorEngine) return;

        const appliances = window.SolarCalculatorEngine.APPLIANCES;
        const selectedPropType = propType.value || 'residential';
        
        // Filter appliances by selected property type (Residential, Commercial, Industrial)
        const filteredAppliances = appliances.filter(app => app.property_type === selectedPropType);

        // Setup initial default quantities for this group if not set
        filteredAppliances.forEach(app => {
            if (applianceQuantities[app.id] === undefined) {
                applianceQuantities[app.id] = app.default_qty || 0;
            }
        });

        // Banner to indicate active category and inform the user
        const typeLabels = {
            residential: isArabic ? 'سكني' : 'Residential',
            commercial: isArabic ? 'تجاري' : 'Commercial',
            industrial: isArabic ? 'صناعي' : 'Industrial'
        };
        const infoMessage = isArabic 
            ? `💡 خيارات الأجهزة تتغير تلقائياً بناءً على نوع العقار المحدد: <strong>${typeLabels[selectedPropType]}</strong>. يمكنك تغيير نوع العقار من القائمة بالأسفل.`
            : `💡 Equipment options automatically change based on selected property type: <strong>${typeLabels[selectedPropType].toUpperCase()}</strong>. You can change it using the dropdown below.`;

        // Generate Category Filter Bar dynamically based on categories in the filtered list
        const uniqueCategories = [...new Set(filteredAppliances.map(app => app.category))];
        
        let html = `
            <div class="appliance-info-banner" style="margin-bottom: 1.25rem; padding: 0.8rem 1rem; background: rgba(58, 141, 204, 0.08); border-left: 4px solid var(--color-primary); border-radius: 8px; font-size: 0.85rem; color: #1e4b6e; display: flex; align-items: center; gap: 8px;">
                <span>${infoMessage}</span>
            </div>
            <div class="appliance-filter-bar">
                <button type="button" class="filter-tab active" data-category="all">
                    <span>${isArabic ? 'الكل' : 'All'}</span>
                </button>
        `;

        uniqueCategories.forEach(cat => {
            const label = categoryLabels[cat] || cat;
            html += `
                <button type="button" class="filter-tab" data-category="${cat}">
                    <span>${label}</span>
                </button>
            `;
        });

        html += `
            </div>
            <div class="appliance-grid">
        `;

        filteredAppliances.forEach(app => {
            const name = isArabic ? app.name_ar : app.name_en;
            const currentQty = applianceQuantities[app.id];
            const activeClass = currentQty > 0 ? ' active-card' : '';
            const disabledAttr = currentQty === 0 ? 'disabled' : '';
            
            // Format power value: show kW if 1000W or higher, and support ranges
            let powerText = "";
            if (app.min_w >= 1000) {
                powerText = `${app.min_w / 1000}kW`;
                if (app.min_w !== app.max_w) {
                    powerText += ` - ${app.max_w / 1000}kW`;
                }
            } else {
                powerText = `${app.min_w}W`;
                if (app.min_w !== app.max_w) {
                    powerText += ` - ${app.max_w}W`;
                }
            }

            // Calculate dynamic load contribution
            const kwhDaily = ((app.min_w * app.hours * currentQty) / 1000).toFixed(1);
            const loadText = currentQty > 0 
                ? (isArabic ? `الاستهلاك: ${kwhDaily} كيلوواط/يوم` : `Load: ${kwhDaily} kWh/d`)
                : '';

            html += `
                <div class="appliance-item${activeClass}" data-id="${app.id}" data-category="${app.category}">
                    <div class="active-badge" id="badge-${app.id}" style="${currentQty > 0 ? 'display: flex;' : 'display: none;'}">${currentQty}</div>
                    <div class="appliance-header-row">
                        <div class="appliance-icon-wrapper">
                            ${getApplianceSVG(app.id)}
                        </div>
                    </div>
                    <div class="appliance-body">
                        <h4>${name}</h4>
                        <div class="appliance-specs-badges">
                            <span class="spec-badge power">${powerText}</span>
                            <span class="spec-badge hours">${app.hours}h/d</span>
                        </div>
                        <div class="appliance-live-load" id="load-val-${app.id}">${loadText}</div>
                    </div>
                    <div class="qty-selector-pill">
                        <button type="button" class="qty-btn minus" data-id="${app.id}" ${disabledAttr} aria-label="Decrease">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                        <span class="qty-val" id="qty-${app.id}">${currentQty}</span>
                        <button type="button" class="qty-btn plus" data-id="${app.id}" aria-label="Increase">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
        applianceInputs.innerHTML = html;

        // Hook up category filtering click handler
        const filterTabs = applianceInputs.querySelectorAll('.filter-tab');
        const cards = applianceInputs.querySelectorAll('.appliance-item');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                filterTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const selectedCategory = tab.dataset.category;
                cards.forEach(card => {
                    if (selectedCategory === 'all' || card.dataset.category === selectedCategory) {
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
            });
        });

        // Bind interactive Qty controls
        applianceInputs.addEventListener('click', (e) => {
            const btn = e.target.closest('.qty-btn');
            if (!btn) return;

            const appId = btn.dataset.id;
            const appSpec = appliances.find(a => a.id === appId);
            if (!appSpec) return;

            let qty = applianceQuantities[appId] || 0;

            if (btn.classList.contains('plus')) {
                qty = Math.min(99, qty + 1);
            } else if (btn.classList.contains('minus')) {
                qty = Math.max(0, qty - 1);
            }

            applianceQuantities[appId] = qty;
            
            // Live DOM updates
            const qtyText = document.getElementById(`qty-${appId}`);
            if (qtyText) qtyText.textContent = qty;

            const badge = document.getElementById(`badge-${appId}`);
            if (badge) {
                if (qty > 0) {
                    badge.textContent = qty;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            const kwhDaily = ((appSpec.min_w * appSpec.hours * qty) / 1000).toFixed(1);
            const loadValText = document.getElementById(`load-val-${appId}`);
            if (loadValText) {
                loadValText.textContent = qty > 0 
                    ? (isArabic ? `الاستهلاك: ${kwhDaily} ك.و/يوم` : `Load: ${kwhDaily} kWh/d`)
                    : '';
            }

            // Live visual feedback: toggle active-card and disable/enable minus button
            const card = btn.closest('.appliance-item');
            if (card) {
                const minusBtn = card.querySelector('.qty-btn.minus');
                if (qty > 0) {
                    card.classList.add('active-card');
                    if (minusBtn) minusBtn.removeAttribute('disabled');
                } else {
                    card.classList.remove('active-card');
                    if (minusBtn) minusBtn.setAttribute('disabled', 'true');
                }
            }

            calculateSolar();
        });
    }

    function getApplianceSVG(id) {
        const svgStyle = 'width: 24px; height: 24px; transition: stroke 0.3s ease;';
        const SVGs = {
            // Residential
            ac_1ton: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="5" width="20" height="9" rx="2"></rect>
                    <path d="M2 9h20M6 14v1M18 14v1M12 9v5"></path>
                    <path d="M8 18c1 1.5 2 2 4 2s3-.5 4-2" stroke-dasharray="2 2"></path>
                </svg>
            `,
            ac_2ton: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="4" width="20" height="10" rx="2"></rect>
                    <path d="M2 9h20M6 14v2m12-2v2"></path>
                    <circle cx="8" cy="6.5" r="1"></circle>
                    <circle cx="16" cy="6.5" r="1"></circle>
                    <path d="M7 18c1.5 1.5 3 2 5 2s3.5-.5 5-2" stroke-dasharray="2 2"></path>
                </svg>
            `,
            water_heater: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="6" y="2" width="12" height="17" rx="3"></rect>
                    <path d="M9 19v3M15 19v3M12 6v6M10 8h4M9 15c.5.5 1 .8 1.5.8s1-.3 1.5-.8"></path>
                </svg>
            `,
            refrigerator: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="5" y="2" width="14" height="20" rx="2"></rect>
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <path d="M10 7v3M14 15v3"></path>
                </svg>
            `,
            freezer: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                    <line x1="4" y1="9" x2="20" y2="9"></line>
                    <path d="M9 6h6M12 13v4M10 15h4"></path>
                </svg>
            `,
            washing_machine: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="4" y="3" width="16" height="18" rx="2"></rect>
                    <circle cx="12" cy="13" r="5"></circle>
                    <path d="M12 10a3 3 0 0 1 3 3"></path>
                    <circle cx="8" cy="6" r="0.5"></circle>
                    <circle cx="12" cy="6" r="0.5"></circle>
                    <line x1="15" y1="6" x2="17" y2="6"></line>
                </svg>
            `,
            microwave: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                    <rect x="5" y="7" width="10" height="10" rx="1"></rect>
                    <circle cx="18" cy="8" r="1"></circle>
                    <circle cx="18" cy="11" r="1"></circle>
                    <line x1="17" y1="14" x2="19" y2="14"></line>
                    <line x1="17" y1="16" x2="19" y2="16"></line>
                </svg>
            `,
            tv: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="4" width="20" height="13" rx="2"></rect>
                    <path d="M12 17v4M8 21h8"></path>
                </svg>
            `,
            led_lights: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .3 2.2 1.5 3.5.7.8 1.2 1.5 1.5 2.5"></path>
                    <path d="M9 18h6M10 21h4"></path>
                    <line x1="12" y1="2" x2="12" y2="3"></line>
                    <line x1="20" y1="8" x2="22" y2="8"></line>
                    <line x1="2" y1="8" x2="4" y2="8"></line>
                </svg>
            `,
            water_pump: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <circle cx="12" cy="12" r="7"></circle>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9l-2.1 2.1M7 17l-2.1 2.1"></path>
                </svg>
            `,
            ev_charger: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="3" y="3" width="10" height="15" rx="2"></rect>
                    <circle cx="8" cy="7.5" r="1.5"></circle>
                    <path d="M13 8h4a2 2 0 0 1 2 2v5a2 2 0 0 1-4 0v-2a1 1 0 0 0-1-1h-1"></path>
                    <path d="M17 14h2"></path>
                </svg>
            `,
            // Commercial
            com_ducted_ac: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="4" width="20" height="10" rx="2"></rect>
                    <path d="M2 9h20M6 14v2m12-2v2"></path>
                    <circle cx="8" cy="6.5" r="1"></circle>
                    <circle cx="16" cy="6.5" r="1"></circle>
                    <path d="M7 18c1.5 1.5 3 2 5 2s3.5-.5 5-2" stroke-dasharray="2 2"></path>
                </svg>
            `,
            com_server_rack: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="2" width="20" height="8" rx="2"></rect>
                    <rect x="2" y="14" width="20" height="8" rx="2"></rect>
                    <line x1="6" y1="6" x2="6.01" y2="6"></line>
                    <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    <line x1="20" y1="6" x2="16" y2="6"></line>
                    <line x1="20" y1="18" x2="16" y2="18"></line>
                </svg>
            `,
            com_led_lighting: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .3 2.2 1.5 3.5.7.8 1.2 1.5 1.5 2.5"></path>
                    <path d="M9 18h6M10 21h4"></path>
                    <line x1="12" y1="2" x2="12" y2="3"></line>
                    <line x1="20" y1="8" x2="22" y2="8"></line>
                    <line x1="2" y1="8" x2="4" y2="8"></line>
                </svg>
            `,
            com_copier: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8" rx="1"></rect>
                    <path d="M16 9V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v5"></path>
                </svg>
            `,
            com_display_fridge: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="5" y="2" width="14" height="20" rx="2"></rect>
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <path d="M10 7v3M14 15v3"></path>
                </svg>
            `,
            com_cctv: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                    <circle cx="12" cy="13" r="4"></circle>
                </svg>
            `,
            com_workstation: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="3" width="20" height="12" rx="2"></rect>
                    <line x1="12" y1="15" x2="12" y2="21"></line>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                </svg>
            `,
            com_water_dispenser: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="6" y="2" width="12" height="17" rx="3"></rect>
                    <path d="M9 19v3M15 19v3M12 6v6M10 8h4M9 15c.5.5 1 .8 1.5.8s1-.3 1.5-.8"></path>
                </svg>
            `,
            com_sliding_door: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="3" width="20" height="18" rx="2"></rect>
                    <line x1="12" y1="3" x2="12" y2="21"></line>
                    <path d="M7 8h2M15 8h2M7 12h2M15 12h2"></path>
                </svg>
            `,
            com_adv_signage: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <path d="M12 2v4M12 18v4M2 12h4M18 12h4"></path>
                </svg>
            `,
            // Industrial
            ind_compressor: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <circle cx="12" cy="12" r="9"></circle>
                    <line x1="12" y1="2" x2="12" y2="22"></line>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                </svg>
            `,
            ind_chiller: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M12 2v20M17 5H7M17 19H7M21 12H3"></path>
                </svg>
            `,
            ind_water_pump: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <circle cx="12" cy="12" r="7"></circle>
                    <circle cx="12" cy="12" r="2.5"></circle>
                    <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9l-2.1 2.1M7 17l-2.1 2.1"></path>
                </svg>
            `,
            ind_molding_mach: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <line x1="15" y1="3" x2="15" y2="21"></line>
                </svg>
            `,
            ind_gantry_crane: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M20 21V4H4v17M12 4v8M12 12a3 3 0 0 1-3 3"></path>
                </svg>
            `,
            ind_exhaust_fan: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a10 10 0 0 0 0 20M2 12a10 10 0 0 0 20 0"></path>
                </svg>
            `,
            ind_welding_mach: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                </svg>
            `,
            ind_conveyor: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="2" y="6" width="20" height="12" rx="6"></rect>
                    <circle cx="8" cy="12" r="2"></circle>
                    <circle cx="16" cy="12" r="2"></circle>
                </svg>
            `,
            ind_cnc_machine: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <rect x="3" y="3" width="18" height="12" rx="2"></rect>
                    <line x1="6" y1="15" x2="6" y2="21"></line>
                    <line x1="18" y1="15" x2="18" y2="21"></line>
                </svg>
            `,
            ind_induction_furnace: `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"></path>
                </svg>
            `
        };
        return SVGs[id] || `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="${svgStyle}">
                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
            </svg>
        `;
    }

    // Number Incrementor Easing
    function animateValue(obj, start, end, duration, prefix = "", suffix = "") {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const easeProgress = 1 - Math.pow(1 - progress, 4); // easeOutQuart
            
            let currentVal = (progress === 1) ? end : start + (end - start) * easeProgress;
            
            if (typeof end === 'string' && end.includes('-')) {
                obj.textContent = end;
                return;
            }
            
            if (Number.isInteger(end)) {
                obj.textContent = `${prefix}${Math.floor(currentVal).toLocaleString()}${suffix}`;
            } else {
                obj.textContent = `${prefix}${currentVal.toFixed(1)}${suffix}`;
            }
            
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    function calculateSolar() {
        if (!window.SolarCalculatorEngine) return;

        const selectedPropType = propType.value || 'residential';
        const selectedLocation = loc.value || 'muscat';
        let result = {};

        if (activeSizerMode === 'bill') {
            const monthlyBill = parseFloat(billSlider.value);
            billDisplay.textContent = monthlyBill;
            result = window.SolarCalculatorEngine.calculate(monthlyBill, selectedPropType, selectedLocation);
            if (loadRecs) loadRecs.style.display = 'none';
        } else {
            result = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities, selectedPropType, selectedLocation);
            if (loadRecs) loadRecs.style.display = 'block';

            // Animate Inverter and Battery Specs
            if (resInverter && resBattery) {
                animateValue(resInverter, 0, result.loadSizing.inverterRecommendationKw, 1000, "", " kW");
                animateValue(resBattery, 0, result.loadSizing.batteryRecommendationKwh, 1000, "", " kWh");
            }
            // Animate Connected Load and Daily Consumption Specs
            if (resConnectedLoad && resDailyConsumption) {
                const peakLoad = result.loadSizing.peakLoadWatts;
                if (peakLoad >= 1000) {
                    animateValue(resConnectedLoad, 0, parseFloat((peakLoad / 1000).toFixed(2)), 1000, "", " kW");
                } else {
                    animateValue(resConnectedLoad, 0, peakLoad, 1000, "", " W");
                }
                animateValue(resDailyConsumption, 0, result.loadSizing.avgDailyKwh, 1000, "", " kWh/day");
            }
        }

        // Animate primary results boxes
        if (resSize && resPanels && resCost && resSavings) {
            if (resSize.textContent === '0 kW') {
                animateValue(resSize, 0, result.systemSizeKw, 1000, "", " kW");
                animateValue(resPanels, 0, result.panelCount, 1000);
                resCost.textContent = result.costRange.formatted;
                animateValue(resSavings, 0, result.yearlySavingsOmr, 1000, "", " OMR");
            } else {
                resSize.textContent = result.systemSizeKw.toFixed(1) + ' kW';
                resPanels.textContent = result.panelCount;
                resCost.textContent = result.costRange.formatted;
                resSavings.textContent = `${result.yearlySavingsOmr.toLocaleString()} OMR`;
            }
        }

        // Send telemetry events
        if (window.SolarAnalytics) {
            window.SolarAnalytics.markCalculatorTouched();
            window.SolarAnalytics.track("calculator_change", {
                sizer_mode: activeSizerMode,
                monthly_bill: activeSizerMode === 'bill' ? parseFloat(billSlider.value) : result.inputs.monthlyBill,
                property_type: selectedPropType,
                location: selectedLocation,
                system_size_kw: result.systemSizeKw,
                panel_count: result.panelCount,
                yearly_savings_omr: result.yearlySavingsOmr
            });
        }
    }

    // Toggle Sizer Tabs
    if (tabBill && tabAppliances) {
        tabBill.addEventListener('click', () => {
            activeSizerMode = 'bill';
            tabBill.classList.add('active');
            tabAppliances.classList.remove('active');

            billInputs.style.display = 'block';
            applianceInputs.style.display = 'none';
            calculateSolar();
        });

        tabAppliances.addEventListener('click', () => {
            activeSizerMode = 'appliances';
            tabAppliances.classList.add('active');
            tabBill.classList.remove('active');

            billInputs.style.display = 'none';
            applianceInputs.style.display = 'block';
            
            initApplianceSizer(); // Re-render in case property type was changed
            calculateSolar();
        });
    }

    // Bind Core Inputs
    if (billSlider) billSlider.addEventListener('input', calculateSolar);
    if (propType) {
        propType.addEventListener('change', () => {
            if (activeSizerMode === 'appliances') {
                initApplianceSizer();
            }
            calculateSolar();
        });
    }
    if (loc) loc.addEventListener('change', calculateSolar);

    // Lazy-init calculator when it scrolls into view (eliminates main-thread blocking on load)
    let calculatorInitialized = false;

    function initCalculatorWhenReady() {
        if (calculatorInitialized) return;
        calculatorInitialized = true;
        initApplianceSizer();
        calculateSolar();
    }

    const calcSection = document.getElementById('calculator');
    if (calcSection && 'IntersectionObserver' in window) {
        const calcObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    initCalculatorWhenReady();
                    calcObserver.disconnect();
                }
            });
        }, { rootMargin: '200px 0px' }); // Pre-init 200px before entering view
        calcObserver.observe(calcSection);
    } else {
        // Fallback: init on idle or after short delay
        if (typeof requestIdleCallback === 'function') {
            requestIdleCallback(initCalculatorWhenReady, { timeout: 2000 });
        } else {
            setTimeout(initCalculatorWhenReady, 500);
        }
    }

    // Hook up AI advisor dynamic explanation trigger
    const explainBtn = document.getElementById('calc-explain-btn');
    if (explainBtn) {
        explainBtn.addEventListener('click', () => {
            if (window.SolarChatbot && window.SolarCalculatorEngine) {
                const selectedPropType = propType.value || 'residential';
                const selectedLocation = loc.value || 'muscat';
                let result = {};

                if (activeSizerMode === 'bill') {
                    result = window.SolarCalculatorEngine.calculate(parseFloat(billSlider.value), selectedPropType, selectedLocation);
                } else {
                    result = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities, selectedPropType, selectedLocation);
                }

                // Push calculation metrics directly into Chatbot advisor context
                window.SolarChatbot.explainCalculatorResult({
                    systemSize: result.systemSizeKw.toFixed(1),
                    panels: result.panelCount,
                    cost: result.costRange.formatted.replace(" OMR", ""),
                    monthlySavings: result.monthlySavingsOmr.toLocaleString(),
                    yearlySavings: result.yearlySavingsOmr.toLocaleString(),
                    payback: result.paybackYears.toFixed(1),
                    sizerMode: activeSizerMode
                });

                if (window.SolarAnalytics) {
                    window.SolarAnalytics.track("calculator_explain_click", {
                        sizer_mode: activeSizerMode,
                        system_size_kw: result.systemSizeKw,
                        location: selectedLocation
                    });
                }
            }
        });
    }

    // --- 6. Native Lead Form AJAX Submission Flow ---
    const nativeForm = document.getElementById('native-lead-form');
    const formFeedback = document.getElementById('form-feedback');

    if (nativeForm) {
        nativeForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Honeypot spam interceptor
            const honeypot = nativeForm.querySelector('input[name="honeypot"]').value;
            if (honeypot) {
                console.warn("[SolarLead] Spam submission detected via honeypot.");
                return;
            }

            // Validate Phone Number
            const phoneField = document.getElementById('lead-phone');
            const phoneValue = phoneField.value.trim().replace(/\D/g, "");
            if (phoneValue.length < 8) {
                showFormFeedback(
                    isArabic ? "يرجى إدخال رقم هاتف عماني صالح يتكون من 8 أرقام على الأقل." : "Please enter a valid phone number with at least 8 digits.", 
                    "error"
                );
                return;
            }

            // Show interactive loading spinners
            const submitBtn = nativeForm.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('span:first-child');
            const spinner = submitBtn.querySelector('.spinner');

            if (spinner) spinner.style.display = 'inline-block';
            submitBtn.disabled = true;

            const formData = new FormData(nativeForm);
            
            // Append current calculator state context to enrich Odoo CRM leads!
            let currentSizingMetrics = {};
            if (window.SolarCalculatorEngine) {
                if (activeSizerMode === 'bill') {
                    currentSizingMetrics = window.SolarCalculatorEngine.calculate(parseFloat(billSlider.value), propType.value, loc.value);
                } else {
                    currentSizingMetrics = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities, propType.value, loc.value);
                }
                formData.append('estimated_kw', currentSizingMetrics.systemSizeKw);
                formData.append('estimated_cost', currentSizingMetrics.costRange.formatted);
                formData.append('estimated_savings', currentSizingMetrics.yearlySavingsOmr);
                formData.append('sizer_mode', activeSizerMode);
            }

            // Submit securely via local PHP proxy endpoint
            fetch('chatbot.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                submitBtn.disabled = false;

                if (data.status === 'success') {
                    showFormFeedback(
                        isArabic 
                          ? "شكراً لك! تم حجز استشارة الطاقة الشمسية الخاصة بك بنجاح. سيتصل بك خبراؤنا قريباً." 
                          : "Thank you! Your solar consultation has been booked successfully. Our experts will call you shortly.", 
                        "success"
                    );
                    nativeForm.reset();

                    // Trigger telemetry conversion conversions
                    if (window.SolarAnalytics) {
                        window.SolarAnalytics.markFormSubmitted();
                        window.SolarAnalytics.track("lead_submitted", {
                            name: formData.get('name'),
                            phone: formData.get('phone'),
                            governorate: formData.get('governorate'),
                            property_type: formData.get('property_type'),
                            system_size_kw: currentSizingMetrics.systemSizeKw || 0,
                            enquiry_source: "native_contact_form"
                        });
                    }
                } else {
                    showFormFeedback(
                        data.message || (isArabic ? "فشل إرسال الطلب. يرجى إعادة المحاولة." : "Failed to submit. Please try again."),
                        "error"
                    );
                }
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
                submitBtn.disabled = false;
                console.error("[SolarLead] AJAX error:", err);
                showFormFeedback(
                    isArabic ? "فشل اتصال الشبكة. يرجى التحقق من اتصالك وإعادة المحاولة." : "Network connection failed. Please check your network and try again.",
                    "error"
                );
            });
        });
    }

    function showFormFeedback(msg, type) {
        if (!formFeedback) return;
        formFeedback.textContent = msg;
        formFeedback.style.display = 'block';
        
        if (type === 'success') {
            formFeedback.style.background = 'rgba(62, 182, 73, 0.15)';
            formFeedback.style.color = '#3eb649';
            formFeedback.style.border = '1px solid #3eb649';
        } else {
            formFeedback.style.background = 'rgba(239, 68, 68, 0.15)';
            formFeedback.style.color = '#ef4444';
            formFeedback.style.border = '1px solid #ef4444';
        }

        // Auto-dismiss alert after 8 seconds
        setTimeout(() => {
            formFeedback.style.display = 'none';
        }, 8000);
    }

    // --- 7. Product Datasheet Center dynamic category filters & Accordion ---
    const dsFilterTabs = document.querySelectorAll('.ds-filter-tab');
    const dsCards = document.querySelectorAll('.datasheet-card');
    const brandToggleBtn = document.getElementById('brand-toggle-btn');
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

    // --- 8. Brand-Centric Datasheet Modal Interactions ---

    // Official manufacturer datasheet URLs — replaces generated download.php PDFs entirely.
    // Each key maps to the most direct official source available for that product.

    const modal = document.getElementById('brand-datasheet-modal');
    const modalCloseBtn = document.getElementById('ds-modal-close-btn');
    const modalLogoContainer = document.getElementById('modal-logo-container');
    const modalBrandTitle = document.getElementById('modal-brand-title');
    const modalBrandTagline = document.getElementById('modal-brand-tagline');
    const modalProductsGrid = document.getElementById('modal-products-grid');
    const brandCards = document.querySelectorAll('.brand-card');
 
    if (modal && brandCards.length > 0) {
        // Open Modal
        brandCards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Prevent duplicate trigger if clicking direct child anchors
                if (e.target.closest('a')) return;
 
                const brandKey = card.dataset.brand;
                const brandData = window.BrandProductsData ? window.BrandProductsData[brandKey] : null;
                if (!brandData) return;
 
                // Set Header details
                modalBrandTitle.textContent = brandData.name;
                modalBrandTagline.textContent = brandData.tagline;
 
                // Render Brand Logo
                if (brandData.logo) {
                    modalLogoContainer.innerHTML = `<img src="${brandData.logo}" alt="${brandData.name}" class="modal-brand-logo">`;
                } else {
                    // Fallback to stylized SVG icon for Concept brand
                    modalLogoContainer.innerHTML = `
                        <div class="brand-logo-icon" style="height: 38px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 32px; height: 32px; color: var(--color-primary);">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                            </svg>
                        </div>`;
                }
 
                // Render Products List Grouped by Category
                const downloadText = isArabic ? "تحميل ورقة البيانات" : "Download Datasheet";
                
                const grouped = {
                    inverter: [],
                    panel: [],
                    battery: [],
                    controller: []
                };

                brandData.products.forEach(prod => {
                    const cat = prod.category || 'inverter';
                    if (grouped[cat]) {
                        grouped[cat].push(prod);
                    }
                });

                const modalCategoryHeadings = {
                    inverter: isArabic ? "العواكس" : "Inverters",
                    panel: isArabic ? "الألواح الشمسية" : "Solar Panels",
                    battery: isArabic ? "البطاريات" : "Batteries",
                    controller: isArabic ? "منظمات الشحن" : "Controllers"
                };

                let productsHTML = '';
                const categoryOrder = ['inverter', 'panel', 'battery', 'controller'];

                categoryOrder.forEach(cat => {
                    const prods = grouped[cat];
                    if (prods && prods.length > 0) {
                        const headingText = modalCategoryHeadings[cat];
                        productsHTML += `
                            <div class="modal-category-group" data-category="${cat}">
                                <h4 class="modal-category-title">${headingText}</h4>
                                <div class="modal-category-products">
                        `;
                        prods.forEach(prod => {
                            const specBadges = prod.specs.map(spec => `<span class="ds-spec-badge">${spec}</span>`).join('');
                            const localPdf = prod.localPdf || `datasheets/${prod.key}.pdf`;
                            const downloadText = isArabic ? "تنزيل ورقة البيانات" : "View Datasheet";

                            let btnHTML = `
                                <a href="#" class="btn-ds-download modal-download-btn pdf-viewer-trigger" data-pdf-url="${localPdf}" data-product="${prod.title}" data-brand="${brandData.name}">
                                    <span>${downloadText}</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                </a>
                            `;

                            productsHTML += `
                                <div class="modal-product-card">
                                    <div class="modal-product-info">
                                        <h3>${prod.title}</h3>
                                        <p>${prod.desc}</p>
                                        <div class="datasheet-specs">
                                            ${specBadges}
                                        </div>
                                    </div>
                                    ${btnHTML}
                                </div>
                            `;
                        });
                        productsHTML += `
                                </div>
                            </div>
                        `;
                    }
                });

                modalProductsGrid.innerHTML = productsHTML;

                // Bind click listener on PDF Viewer triggers
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
                            console.log(`[Datasheet Tracking] View - ${prodTitle} (${brandName})`);
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
                                        ? `مرحباً، أود طلب أسعار لمنتج: ${prodTitle} (${brandName}).`
                                        : `Hi, I would like to request pricing and availability for: ${prodTitle} (${brandName}).`;
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
                });

                // Show Modal with body scroll lock
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                
                // Trigger reveal animation active state
                setTimeout(() => {
                    modal.classList.add('active');
                }, 50);
            });
        });

        // Close Modal Helper
        const closeModal = () => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }, 300);
        };

        // Close Event bindings
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);

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

        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });
    }

    // ==========================================
    // Hero Slider Implementation
    // ==========================================
    const heroTrack = document.getElementById('hero-slider-track');
    const heroNavItems = document.querySelectorAll('.hero-slider-nav .slider-item');
    
    if (heroTrack && heroNavItems.length > 0) {
        let currentSlide = 0;
        const totalSlides = heroNavItems.length;
        let slideInterval;

        const updateSlider = (index) => {
            // Ensure index is within bounds
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            currentSlide = index;
            // Calculate translation percentage based on number of slides. 
            const isRTL = document.documentElement.getAttribute('dir') === 'rtl' || document.body.getAttribute('dir') === 'rtl';
            const percent = currentSlide * (100 / totalSlides);
            const translateValue = isRTL ? percent : -percent;
            heroTrack.style.transform = `translate3d(${translateValue}%, 0, 0)`;
            
            // Update active state on pagination indicators
            heroNavItems.forEach(item => item.classList.remove('active'));
            const activeIndicator = document.querySelector(`.hero-slider-nav .slider-item[data-slide-index="${currentSlide}"]`);
            if (activeIndicator) {
                activeIndicator.classList.add('active');
            }
        };

        const nextSlide = () => {
            updateSlider(currentSlide + 1);
        };

        const startSlider = () => {
            stopSlider(); // Ensure no duplicates
            slideInterval = setInterval(nextSlide, 5000);
        };

        const stopSlider = () => {
            if (slideInterval) clearInterval(slideInterval);
        };

        // Attach click listeners to pagination dots
        heroNavItems.forEach(item => {
            item.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.getAttribute('data-slide-index'), 10);
                if (!isNaN(index)) {
                    updateSlider(index);
                    startSlider(); // Reset the timer on manual navigation
                }
            });
        });

        // Pause on hover
        const heroVisual = document.getElementById('hero-slider-visual');
        if (heroVisual) {
            heroVisual.addEventListener('mouseenter', stopSlider);
            heroVisual.addEventListener('mouseleave', startSlider);
            
            // Touch swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            
            heroVisual.addEventListener('touchstart', e => {
                touchStartX = e.changedTouches[0].screenX;
                stopSlider();
            }, {passive: true});
            
            heroVisual.addEventListener('touchend', e => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
                startSlider();
            }, {passive: true});
            
            const handleSwipe = () => {
                const swipeThreshold = 50;
                if (touchEndX < touchStartX - swipeThreshold) {
                    // Swipe left -> next slide
                    nextSlide();
                }
                if (touchEndX > touchStartX + swipeThreshold) {
                    // Swipe right -> prev slide
                    updateSlider(currentSlide - 1);
                }
            }
        }

        // Initialize slider
        startSlider();
    }

});
