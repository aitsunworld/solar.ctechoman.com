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
    const applianceQuantities = {
        residential: {},
        commercial: {},
        industrial: {}
    };

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
            if (applianceQuantities[selectedPropType][app.id] === undefined) {
                applianceQuantities[selectedPropType][app.id] = app.default_qty || 0;
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
            <div class="appliance-info-banner">
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
            const currentQty = applianceQuantities[selectedPropType][app.id];
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
            result = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities[selectedPropType], selectedPropType, selectedLocation);
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

        // Sync context to chatbot in real time
        if (window.SolarChatbot) {
            window.SolarChatbot.updateContext({
                systemSize: result.systemSizeKw.toFixed(1),
                panels: result.panelCount,
                cost: result.costRange.formatted.replace(" OMR", ""),
                monthlySavings: result.monthlySavingsOmr.toLocaleString(),
                yearlySavings: result.yearlySavingsOmr.toLocaleString(),
                payback: result.paybackYears.toFixed(1),
                sizerMode: activeSizerMode
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
            toggleSizerLayout();
        });
    }
    if (loc) {
        loc.addEventListener('change', () => {
            const selectedPropType = propType ? propType.value : 'residential';
            if (selectedPropType === 'residential') {
                goToDiscoveryStep(currentDiscoveryStep);
            } else {
                calculateSolar();
            }
        });
    }

    // Bind interactive Qty controls (once globally to prevent event listener duplicate binding / leakage)
    if (applianceInputs) {
        applianceInputs.addEventListener('click', (e) => {
            const btn = e.target.closest('.qty-btn');
            if (!btn) return;

            const appId = btn.dataset.id;
            const appliances = window.SolarCalculatorEngine ? window.SolarCalculatorEngine.APPLIANCES : [];
            const appSpec = appliances.find(a => a.id === appId);
            if (!appSpec) return;

            const selectedPropType = propType.value || 'residential';
            let qty = applianceQuantities[selectedPropType][appId] || 0;

            if (btn.classList.contains('plus')) {
                qty = Math.min(99, qty + 1);
            } else if (btn.classList.contains('minus')) {
                qty = Math.max(0, qty - 1);
            }

            applianceQuantities[selectedPropType][appId] = qty;
            
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

    // Lazy-init calculator when it scrolls into view (eliminates main-thread blocking on load)
    let calculatorInitialized = false;

    function initCalculatorWhenReady() {
        if (calculatorInitialized) return;
        calculatorInitialized = true;
        initApplianceSizer();
        toggleSizerLayout();
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
                    result = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities[selectedPropType], selectedPropType, selectedLocation);
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
                    currentSizingMetrics = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities[propType.value], propType.value, loc.value);
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
            const labelShow = isArabic ? "عرض جميع العلامات التجارية ▾" : "View All Brands ▾";
            const labelHide = isArabic ? "عرض أقل ▴" : "Show Less ▴";
            
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

    // ==========================================
    // Residential Solar Discovery Experience (Wizard)
    // ==========================================
    let currentDiscoveryStep = 1;
    let selectedAppliances = new Set();
    let calibratedBillValue = null;

    function toggleSizerLayout() {
        const selectedPropType = propType ? propType.value : 'residential';
        const standardInputs = document.getElementById('standard-calc-inputs');
        const standardResults = document.getElementById('standard-calc-results');
        const discoveryJourney = document.getElementById('residential-discovery-journey');
        const discoveryResults = document.getElementById('residential-discovery-results');
        const calcWrapper = document.querySelector('.calculator-wrapper');
        const calcInfo = document.querySelector('.calc-info');

        // ALWAYS hide the legacy standard/old calculator elements
        if (standardInputs) standardInputs.style.display = 'none';
        if (standardResults) standardResults.style.display = 'none';

        // ALWAYS show discovery journey (wizard)
        if (discoveryJourney) discoveryJourney.style.display = 'block';

        if (selectedPropType === 'residential') {
            if (calcWrapper) {
                calcWrapper.classList.add('residential-mode');
                calcWrapper.classList.remove('commercial-mode');
            }
            if (calcInfo) calcInfo.style.display = 'none'; // hide left panel title
            if (billSlider) { billSlider.value = 50; if (billDisplay) billDisplay.textContent = 50; }
        } else {
            // Commercial or Industrial: uses the same block layout, but can track active class
            if (calcWrapper) {
                calcWrapper.classList.remove('residential-mode');
                calcWrapper.classList.add('commercial-mode');
            }
            if (calcInfo) calcInfo.style.display = 'none'; // hide left panel title
            
            const defaultBills = { commercial: 200, industrial: 500 };
            const defaultBill = defaultBills[selectedPropType] || 200;
            if (billSlider) {
                billSlider.value = defaultBill;
                if (billDisplay) billDisplay.textContent = defaultBill;
            }
        }
        resetDiscoveryJourney();
    }

    function goToDiscoveryStep(step) {
        const selectedPropType = propType ? propType.value : 'residential';
        currentDiscoveryStep = step;

        // Update progress indicators (wrapper + inner dot)
        const indicators = document.querySelectorAll('.discovery-steps-progress .step-indicator');
        indicators.forEach(indicator => {
            const indicatorStep = parseInt(indicator.dataset.step);
            const dot = indicator.querySelector('.step-dot');
            if (indicatorStep < step) {
                indicator.className = 'step-indicator completed';
                if (dot) dot.className = 'step-dot completed';
            } else if (indicatorStep === step) {
                indicator.className = 'step-indicator active';
                if (dot) dot.className = 'step-dot active';
            } else {
                indicator.className = 'step-indicator';
                if (dot) dot.className = 'step-dot';
            }
        });

        // Scroll active step into view on mobile
        const activeIndicator = document.querySelector(`.discovery-steps-progress .step-indicator[data-step="${step}"]`);
        if (activeIndicator && window.innerWidth < 768) {
            activeIndicator.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        // Update the filled progress line via CSS custom property (0 to 1 ratio)
        const progressBar = document.querySelector('.discovery-steps-progress');
        if (progressBar) {
            const progressRatio = Math.max(0, (step - 1) / 6);
            progressBar.style.setProperty('--step-progress-ratio', progressRatio);
            progressBar.style.setProperty('--step-progress', (progressRatio * 100) + '%');
        }

        // Hide all panels
        const panels = document.querySelectorAll('.discovery-step-panel');
        panels.forEach(p => p.style.display = 'none');

        // Show active panel
        const activePanel = document.getElementById(`discovery-panel-${step}`);
        if (activePanel) {
            activePanel.style.display = 'block';
            activePanel.style.opacity = '0';
            activePanel.style.transform = 'translateY(10px)';
            setTimeout(() => {
                activePanel.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                activePanel.style.opacity = '1';
                activePanel.style.transform = 'translateY(0)';
            }, 30);
        }

        // Handle panel-specific initializations
        if (step === 1) {
            renderDiscoveryApplianceSelectionGrid();
            const discoveryResults = document.getElementById('residential-discovery-results');
            if (discoveryResults) discoveryResults.style.display = 'none';
        } else if (step === 2) {
            renderDiscoveryApplianceQtyGrid();
        } else if (step === 3) {
            const result = window.SolarCalculatorEngine.calculateByLoad(
                applianceQuantities[selectedPropType],
                selectedPropType,
                loc.value
            );
            const monthlyConsumption = result.loadSizing.avgDailyKwh * 30;
            const revealCons = document.getElementById('reveal-consumption-value');
            if (revealCons) {
                animateValue(revealCons, 0, monthlyConsumption, 1000, "", " kWh");
            }
        } else if (step === 4) {
            const result = window.SolarCalculatorEngine.calculateByLoad(
                applianceQuantities[selectedPropType],
                selectedPropType,
                loc.value
            );
            const revealSolarKw = document.getElementById('reveal-solar-kw');
            const revealSolarPanels = document.getElementById('reveal-solar-panels');
            const revealSolarCost = document.getElementById('reveal-solar-cost');

            if (revealSolarKw) animateValue(revealSolarKw, 0, result.systemSizeKw, 1000, "", " kW");
            if (revealSolarPanels) animateValue(revealSolarPanels, 0, result.panelCount, 1000, "", isArabic ? " لوح" : " Panels");
            if (revealSolarCost) revealSolarCost.textContent = result.costRange.formatted;
        } else if (step === 5) {
            const slider = document.getElementById('discovery-bill-slider');
            const display = document.getElementById('discovery-bill-display');
            if (slider && display) {
                const result = window.SolarCalculatorEngine.calculateByLoad(
                    applianceQuantities[selectedPropType],
                    selectedPropType,
                    loc.value
                );
                const estimatedBill = Math.round(result.inputs.monthlyBill);
                slider.value = Math.max(10, Math.min(1000, estimatedBill));
                display.textContent = slider.value;
            }
        } else if (step === 6) {
            const statusMsg = document.getElementById('calibration-status-message');
            const warningBox = document.getElementById('calibration-warning-box');
            const successBox = document.getElementById('calibration-success-box');
            const btnGotoStep7 = document.getElementById('btn-goto-step7');
            
            const progressFill = document.getElementById('calibration-progress-fill');
            const confidencePct = document.getElementById('calibration-confidence-pct');
            const calScore = document.getElementById('cal-val-score');
            const calBillMatch = document.getElementById('cal-val-bill-match');
            const calAccuracy = document.getElementById('cal-val-accuracy');
            const calConfLevel = document.getElementById('cal-val-confidence-level');

            const insightCard = document.getElementById('cal-insight-card');
            const actionCard = document.getElementById('cal-action-card');
            const insightDesc = document.getElementById('cal-insight-desc');
            const actionDesc = document.getElementById('cal-action-desc');

            if (btnGotoStep7) btnGotoStep7.style.display = 'none';
            if (warningBox) warningBox.style.display = 'none';
            if (successBox) successBox.style.display = 'none';
            if (insightCard) insightCard.style.display = 'none';
            if (actionCard) actionCard.style.display = 'none';

            const strokeCircumference = 534.07;
            if (progressFill) progressFill.style.strokeDashoffset = strokeCircumference;

            if (calibratedBillValue !== null) {
                if (statusMsg) statusMsg.textContent = isArabic ? "جاري مقارنة استهلاك الأجهزة مع فاتورة الكهرباء..." : "Comparing appliance usage with bill history...";
                
                if (confidencePct) confidencePct.textContent = isArabic ? "جاري الحساب..." : "Calculating...";
                if (calScore) calScore.textContent = isArabic ? "جاري التحليل..." : "Analyzing...";
                if (calBillMatch) calBillMatch.textContent = isArabic ? "جاري التحليل..." : "Analyzing...";
                if (calAccuracy) calAccuracy.textContent = isArabic ? "جاري التحليل..." : "Analyzing...";
                if (calConfLevel) calConfLevel.textContent = isArabic ? "جاري التحليل..." : "Analyzing...";

                setTimeout(() => {
                    let confidence = 92;
                    let billMatchPct = 95;
                    let energyAccuracy = 98;
                    let scoreText = isArabic ? "تطابق ممتاز" : "Excellent Match";
                    let confidenceLevelText = isArabic ? "مرتفع جداً" : "Very High";
                    let insightText = "";
                    let actionText = "";

                    const result = window.SolarCalculatorEngine.calculateByLoad(
                        applianceQuantities[selectedPropType],
                        selectedPropType,
                        loc.value
                    );
                    const loadConsumption = result.loadSizing.avgDailyKwh * 30;
                    const tariff = window.SolarCalculatorEngine.TARIFFS[selectedPropType] || 0.020;
                    const billConsumption = calibratedBillValue / tariff;
                    const variance = Math.abs(billConsumption - loadConsumption) / loadConsumption;
                    const pctDiff = Math.round(variance * 100);

                    billMatchPct = Math.max(10, Math.round((1 - Math.min(1, variance)) * 100));
                    energyAccuracy = Math.max(10, Math.round((1 - Math.min(1, variance * 0.8)) * 100));
                    confidence = Math.max(10, Math.round(billMatchPct * 0.9 + 5));

                    if (variance > 0.15) {
                        scoreText = isArabic ? "تطابق متفاوت" : "Diverging Match";
                        confidenceLevelText = isArabic ? "متوسط" : "Moderate";
                        if (warningBox) warningBox.style.display = 'block';
                        
                        if (isArabic) {
                            insightText = `فاتورتك الفعلية تشير إلى استهلاك ${billConsumption > loadConsumption ? 'أعلى' : 'أقل'} بنسبة ${pctDiff}% من تقدير الأجهزة. يشير هذا إلى وجود أجهزة إضافية غير مدرجة أو استخدام مكثف للتكييف.`;
                            actionText = "لقد قمنا بتعديل حجم النظام الشمسي الموصى به ليتناسب مع نمط استهلاكك الفعلي لضمان تغطية كاملة وتجنب أي عجز في الطاقة.";
                        } else {
                            insightText = `Your actual electricity bill indicates energy consumption is ${pctDiff}% ${billConsumption > loadConsumption ? 'higher' : 'lower'} than our appliance estimate. This suggests additional background loads or high air conditioning runtime.`;
                            actionText = "We have dynamically scaled your recommended solar system size to match your actual consumption, ensuring optimal coverage and maximum grid independence.";
                        }
                    } else {
                        scoreText = isArabic ? "تطابق ممتاز" : "Excellent Match";
                        confidenceLevelText = isArabic ? "مرتفع جداً" : "Very High";
                        if (successBox) successBox.style.display = 'block';
                        
                        if (isArabic) {
                            insightText = `تطابق رائع! استهلاك الأجهزة المحددة يتطابق بشكل وثيق جداً (في حدود ${pctDiff}%) مع فاتورتك الكهربائية الفعلية.`;
                            actionText = "نوصي بالاستمرار مع حجم النظام القياسي الموصى به. إن ملف استهلاكك مثالي لتثبيت نظام طاقة شمسية عالي الكفاءة.";
                        } else {
                            insightText = `Excellent calibration! Your selected appliance profile matches your actual monthly electricity bill very closely (within ${pctDiff}%).`;
                            actionText = "We recommend proceeding with our standard solar package. Your energy usage profile is optimized for maximum ROI and immediate utility bill reduction.";
                        }
                    }

                    const offset = strokeCircumference - (confidence / 100) * strokeCircumference;
                    if (progressFill) progressFill.style.strokeDashoffset = offset;

                    if (confidencePct) animateValue(confidencePct, 0, confidence, 800, "", "%");
                    if (calScore) calScore.textContent = scoreText;
                    if (calBillMatch) animateValue(calBillMatch, 0, billMatchPct, 800, "", "%");
                    if (calAccuracy) animateValue(calAccuracy, 0, energyAccuracy, 800, "", "%");
                    if (calConfLevel) calConfLevel.textContent = confidenceLevelText;

                    if (insightDesc) insightDesc.textContent = insightText;
                    if (actionDesc) actionDesc.textContent = actionText;
                    if (insightCard) insightCard.style.display = 'block';
                    if (actionCard) actionCard.style.display = 'block';

                    if (statusMsg) statusMsg.textContent = isArabic ? "اكتملت المعايرة!" : "Calibration completed!";
                    if (btnGotoStep7) btnGotoStep7.style.display = 'inline-block';
                }, 1000);
            } else {
                if (statusMsg) statusMsg.textContent = isArabic ? "اكتملت المعايرة!" : "Calibration completed!";
                if (successBox) {
                    successBox.textContent = isArabic ? "تم التجاوز بنجاح. سيتم استخدام تقديرات استهلاك الأجهزة." : "Skipped successfully. Using appliance-derived estimates.";
                    successBox.style.display = 'block';
                }

                const confidence = 85;
                const offset = strokeCircumference - (confidence / 100) * strokeCircumference;
                if (progressFill) progressFill.style.strokeDashoffset = offset;

                if (confidencePct) confidencePct.textContent = isArabic ? "تقديري" : "Estimated";
                if (calScore) calScore.textContent = isArabic ? "استخدام ملف الأجهزة" : "Using Appliance Profile";
                if (calBillMatch) calBillMatch.textContent = isArabic ? "غير متوفر" : "Not Available";
                if (calAccuracy) calAccuracy.textContent = isArabic ? "تقديري" : "Estimated";
                if (calConfLevel) calConfLevel.textContent = isArabic ? "تقديري" : "Estimated";

                let insightText = "";
                let actionText = "";
                if (isArabic) {
                    const typeLabel = selectedPropType === 'residential' ? 'السكنية' : (selectedPropType === 'commercial' ? 'التجارية' : 'الصناعية');
                    insightText = `يتم استخدام تقدير الاستهلاك القياسي المستند إلى الأجهزة نظراً لتجاوز إدخال الفاتورة. نسبة الدقة ممتازة لتقديرات المباني ${typeLabel} المماثلة.`;
                    actionText = "تابع لمراجعة لوحة معلومات التوفير. ننصحك بالتحقق من فاتورتك لاحقاً مع مستشارينا لتأكيد القياسات الدقيقة قبل التثبيت.";
                } else {
                    insightText = `Using standard appliance-based load profile as bill input was bypassed. Sizing accuracy remains high for typical ${selectedPropType} properties in your area.`;
                    actionText = "Proceed to view your savings dashboard. We suggest verifying with a physical electricity bill during our consultant's follow-up call to finalize system engineering.";
                }

                if (insightDesc) insightDesc.textContent = insightText;
                if (actionDesc) actionDesc.textContent = actionText;
                if (insightCard) insightCard.style.display = 'block';
                if (actionCard) actionCard.style.display = 'block';

                if (btnGotoStep7) btnGotoStep7.style.display = 'inline-block';
            }
        } else if (step === 7) {
            const discoveryResults = document.getElementById('residential-discovery-results');
            if (discoveryResults) discoveryResults.style.display = 'block';

            let finalResult;
            if (calibratedBillValue !== null) {
                finalResult = window.SolarCalculatorEngine.calculate(calibratedBillValue, selectedPropType, loc.value);
            } else {
                finalResult = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities[selectedPropType], selectedPropType, loc.value);
            }

            updateDiscoveryDashboard(finalResult);

            const leadLoc = document.getElementById('disc-lead-loc');
            const leadBill = document.getElementById('disc-lead-bill');
            if (leadLoc) leadLoc.value = loc.value;
            if (leadBill) leadBill.value = (calibratedBillValue !== null) ? calibratedBillValue : Math.round(finalResult.inputs.monthlyBill);

            if (window.SolarChatbot) {
                const yieldKwh = window.SolarCalculatorEngine.YIELDS[loc.value] || 1700;
                window.SolarChatbot.updateContext({
                    systemSize: finalResult.systemSizeKw.toFixed(1),
                    panels: finalResult.panelCount,
                    cost: finalResult.costRange.formatted.replace(" OMR", ""),
                    monthlySavings: finalResult.monthlySavingsOmr.toLocaleString(),
                    yearlySavings: finalResult.yearlySavingsOmr.toLocaleString(),
                    payback: finalResult.paybackYears.toFixed(1),
                    sizerMode: 'discovery'
                });
            }
        }
    }

    function renderDiscoveryApplianceSelectionGrid() {
        const grid = document.getElementById('discovery-appliance-selection-grid');
        if (!grid || !window.SolarCalculatorEngine) return;

        const selectedPropType = propType ? propType.value : 'residential';
        const appliances = window.SolarCalculatorEngine.APPLIANCES.filter(app => app.property_type === selectedPropType);
        let html = '';

        appliances.forEach(app => {
            const name = isArabic ? app.name_ar : app.name_en;
            const isSelected = selectedAppliances.has(app.id);
            const selectedClass = isSelected ? ' selected' : '';

            let powerText = "";
            if (app.min_w >= 1000) {
                powerText = `${app.min_w / 1000}kW`;
            } else {
                powerText = `${app.min_w}W`;
            }

            html += `
                <div class="discovery-appliance-card${selectedClass}" data-id="${app.id}">
                    <div class="card-icon-wrapper">
                        ${getApplianceSVG(app.id)}
                    </div>
                    <h4>${name}</h4>
                    <span class="card-spec">${powerText} • ${app.hours}h/d</span>
                    <div class="card-checkbox-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;

        grid.querySelectorAll('.discovery-appliance-card').forEach(card => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                if (selectedAppliances.has(id)) {
                    selectedAppliances.delete(id);
                    card.classList.remove('selected');
                    applianceQuantities[selectedPropType][id] = 0;
                } else {
                    selectedAppliances.add(id);
                    card.classList.add('selected');
                    const app = appliances.find(a => a.id === id);
                    applianceQuantities[selectedPropType][id] = (app.default_qty || 1);
                }
            });
        });
    }

    function renderDiscoveryApplianceQtyGrid() {
        const grid = document.getElementById('discovery-appliance-qty-grid');
        if (!grid || !window.SolarCalculatorEngine) return;

        const selectedPropType = propType ? propType.value : 'residential';
        const appliances = window.SolarCalculatorEngine.APPLIANCES.filter(app => app.property_type === selectedPropType);
        let html = '';
        let hasSelected = false;

        appliances.forEach(app => {
            if (!selectedAppliances.has(app.id)) return;
            hasSelected = true;

            const name = isArabic ? app.name_ar : app.name_en;
            const qty = applianceQuantities[selectedPropType][app.id] || 1;

            html += `
                <div class="discovery-qty-row" data-id="${app.id}">
                    <div class="qty-row-info">
                        <div class="qty-row-icon">
                            ${getApplianceSVG(app.id)}
                        </div>
                        <div class="qty-row-text">
                            <h4>${name}</h4>
                            <small>${app.hours}h/d</small>
                        </div>
                    </div>
                    <div class="qty-selector-pill">
                        <button type="button" class="disc-qty-btn minus" data-id="${app.id}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                        <span class="disc-qty-val" id="disc-qty-val-${app.id}">${qty}</span>
                        <button type="button" class="disc-qty-btn plus" data-id="${app.id}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </button>
                    </div>
                </div>
            `;
        });

        if (!hasSelected) {
            html = `<div class="text-center py-3 text-muted">${isArabic ? 'لم يتم تحديد أي أجهزة. يرجى العودة للخطوة السابقة.' : 'No appliances selected. Please go back.'}</div>`;
        }

        grid.innerHTML = html;

        grid.querySelectorAll('.disc-qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                let qty = applianceQuantities[selectedPropType][id] || 1;
                if (btn.classList.contains('plus')) {
                    qty = Math.min(99, qty + 1);
                } else if (btn.classList.contains('minus')) {
                    qty = Math.max(1, qty - 1);
                }
                applianceQuantities[selectedPropType][id] = qty;
                const qtyText = document.getElementById(`disc-qty-val-${id}`);
                if (qtyText) qtyText.textContent = qty;
            });
        });
    }

    function resetDiscoveryJourney() {
        selectedAppliances.clear();
        calibratedBillValue = null;

        const selectedPropType = propType ? propType.value : 'residential';
        const appliances = window.SolarCalculatorEngine ? window.SolarCalculatorEngine.APPLIANCES : [];
        appliances.forEach(app => {
            if (app.property_type === selectedPropType) {
                applianceQuantities[selectedPropType][app.id] = app.default_qty || 0;
                if (app.default_qty > 0) {
                    selectedAppliances.add(app.id);
                }
            }
        });

        const leadForm = document.getElementById('discovery-lead-form');
        if (leadForm) leadForm.reset();

        const feedback = document.getElementById('discovery-form-feedback');
        if (feedback) feedback.style.display = 'none';

        goToDiscoveryStep(1);
    }

    function updateDiscoveryDashboard(result) {
        const selectedPropType = propType ? propType.value : 'residential';
        const tariff = window.SolarCalculatorEngine.TARIFFS[selectedPropType] || 0.020;
        const yieldKwh = window.SolarCalculatorEngine.YIELDS[loc.value] || 1700;
        const monthlyConsumption = result.inputs.monthlyBill / tariff;
        const dailyConsumption = monthlyConsumption / 30;
        const monthlyProduction = (result.systemSizeKw * yieldKwh) / 12;
        const lifetimeSavings = result.yearlySavingsOmr * 25;

        // Calculate Cost Reduction %
        const reductionPct = Math.round((result.monthlySavingsOmr / (result.inputs.monthlyBill || 50)) * 100);
        
        // Calculate ROI %
        const avgCost = (result.costRange.min + result.costRange.max) / 2;
        const roiPct = avgCost > 0 ? ((result.yearlySavingsOmr / avgCost) * 100).toFixed(1) : 0;
        const paybackRoiText = `${result.paybackYears} ${isArabic ? 'سنوات' : 'Years'} / ${roiPct}% ${isArabic ? 'عائد' : 'ROI'}`;

        // Calculate Technical specs
        const inverterSize = result.loadSizing ? result.loadSizing.inverterRecommendationKw : Math.max(1.5, parseFloat((result.systemSizeKw * 0.95).toFixed(1)));
        const batterySize = result.loadSizing ? result.loadSizing.batteryRecommendationKwh : Math.max(2.4, parseFloat((result.systemSizeKw * 1.5).toFixed(1)));

        const dbDaily = document.getElementById('db-val-daily-cons');
        const dbMonthly = document.getElementById('db-val-monthly-cons');
        const dbRecSize = document.getElementById('db-val-rec-size');
        const dbMonthlySav = document.getElementById('db-val-monthly-sav');
        const dbYearlySav = document.getElementById('db-val-yearly-sav');
        const dbLifetime = document.getElementById('db-val-lifetime-sav');
        
        // New elements
        const dbReduction = document.getElementById('db-val-reduction-pct');
        const dbPaybackRoi = document.getElementById('db-val-payback-roi');
        const dbInstallCost = document.getElementById('db-val-install-cost');
        const dbPanelCount = document.getElementById('db-val-panel-count');
        const dbInverterSize = document.getElementById('db-val-inverter-size');
        const dbBatterySize = document.getElementById('db-val-battery-size');

        if (dbDaily) animateValue(dbDaily, 0, dailyConsumption, 1000, "", " kWh/day");
        if (dbMonthly) animateValue(dbMonthly, 0, monthlyConsumption, 1000, "", " kWh/month");
        if (dbRecSize) animateValue(dbRecSize, 0, result.systemSizeKw, 1000, "", " kW");
        if (dbMonthlySav) animateValue(dbMonthlySav, 0, result.monthlySavingsOmr, 1000, "", " OMR");
        if (dbYearlySav) animateValue(dbYearlySav, 0, result.yearlySavingsOmr, 1000, "", " OMR");
        if (dbLifetime) animateValue(dbLifetime, 0, lifetimeSavings, 1000, "", " OMR");
        
        if (dbReduction) animateValue(dbReduction, 0, reductionPct, 1000, "", "%");
        if (dbPaybackRoi) dbPaybackRoi.textContent = paybackRoiText;
        if (dbInstallCost) dbInstallCost.textContent = result.costRange.formatted;
        if (dbPanelCount) animateValue(dbPanelCount, 0, result.panelCount, 1000, "", isArabic ? " لوح" : " Panels");
        if (dbInverterSize) animateValue(dbInverterSize, 0, inverterSize, 1000, "", " kW");
        if (dbBatterySize) animateValue(dbBatterySize, 0, batterySize, 1000, "", " kWh");

        // Energy Independence
        const indScore = Math.min(100, Math.round((monthlyProduction / monthlyConsumption) * 100));
        const scoreLabel = document.getElementById('score-energy-label');
        const statusLabel = document.getElementById('db-val-energy-status');
        
        if (scoreLabel) animateValue(scoreLabel, 0, indScore, 1000, "", "%");

        if (statusLabel) {
            let labelText = "";
            let statusClass = "";
            if (indScore >= 91) {
                labelText = isArabic ? "استقلالية ممتازة للطاقة" : "Excellent Energy Independence";
                statusClass = "status-excellent";
            } else if (indScore >= 71) {
                labelText = isArabic ? "استقلالية جيدة للطاقة" : "Good Energy Independence";
                statusClass = "status-good";
            } else if (indScore >= 41) {
                labelText = isArabic ? "استقلالية متوسطة للطاقة" : "Moderate Energy Independence";
                statusClass = "status-average";
            } else {
                labelText = isArabic ? "استقلالية منخفضة للطاقة" : "Low Energy Independence";
                statusClass = "status-poor";
            }
            statusLabel.textContent = labelText;
            statusLabel.className = `proposal-status-badge ${statusClass}`;
        }

        // Solar Suitability
        let yieldPoints = 2;
        if (['muscat', 'batinah', 'dakhiliyah'].includes(loc.value)) yieldPoints = 3;
        else if (loc.value === 'dhofar') yieldPoints = 1;

        let spacePoints = 1;
        if (result.spaceRequiredSqm <= 80) spacePoints = 3;
        else if (result.spaceRequiredSqm <= 150) spacePoints = 2;

        let consPoints = 2;
        if (monthlyConsumption >= 500 && monthlyConsumption <= 3000) consPoints = 3;

        const totalPoints = yieldPoints + spacePoints + consPoints;
        let grade = "B";
        if (totalPoints >= 9) grade = "A+";
        else if (totalPoints >= 7) grade = "A";
        else if (totalPoints >= 5) grade = "B";
        else grade = "C";

        const suitLabel = document.getElementById('score-suitability-label');
        if (suitLabel) suitLabel.textContent = grade;

        const suitBadge = document.getElementById('score-suitability-badge');
        const suitDesc = document.getElementById('score-suitability-desc');
        if (suitBadge) {
            suitBadge.className = `suitability-badge-new grade-${grade.toLowerCase().replace('+', 'plus')}`;
        }
        if (suitDesc) {
            let descText = "";
            if (grade === "A+") {
                descText = isArabic ? "مرشح استثنائي. ملاءمة مثالية للموقع لتركيب الطاقة الشمسية." : "Outstanding Candidate. Ideal site suitability for solar deployment.";
            } else if (grade === "A") {
                descText = isArabic ? "مرشح ممتاز. إمكانات توليد عالية وملاءمة ممتازة للطاقة الشمسية." : "Excellent Candidate. High solar generation potential and suitability.";
            } else if (grade === "B") {
                descText = isArabic ? "مناسب لتركيب الطاقة الشمسية مع وجود فرص تحسين متوسطة." : "Suitable for solar deployment with moderate optimization opportunities.";
            } else {
                descText = isArabic ? "مرشح ممكن. يتطلب تصميماً أو هندسة مخصصة." : "Feasible Candidate. Custom design or engineering required.";
            }
            suitDesc.textContent = descText;
        }

        // Green Impact & Environmental Rating
        const co2Val = document.getElementById('score-co2-val');
        const treesVal = document.getElementById('score-trees-val');
        const greenRating = document.getElementById('score-green-rating');
        
        const trees = Math.round(result.co2OffsetTons * 16.5);
        if (co2Val) animateValue(co2Val, 0, result.co2OffsetTons, 1000, "", " Tons");
        if (treesVal) animateValue(treesVal, 0, trees, 1000);

        if (greenRating) {
            let ratingText = '';
            let ratingClass = '';
            if (result.co2OffsetTons >= 25) {
                ratingText = isArabic ? 'ممتاز' : 'Excellent';
                ratingClass = 'text-green';
            } else if (result.co2OffsetTons >= 10) {
                ratingText = isArabic ? 'جيد' : 'Good';
                ratingClass = 'text-green';
            } else if (result.co2OffsetTons >= 5) {
                ratingText = isArabic ? 'متوسط' : 'Moderate';
                ratingClass = 'text-orange';
            } else {
                ratingText = isArabic ? 'مقبول' : 'Standard';
                ratingClass = 'text-muted';
            }
            greenRating.textContent = ratingText;
            greenRating.className = `proposal-value ${ratingClass}`;
        }
    }

    // Step Actions Bindings
    const btnGotoStep2 = document.getElementById('btn-goto-step2');
    if (btnGotoStep2) {
        btnGotoStep2.addEventListener('click', () => {
            if (selectedAppliances.size === 0) {
                alert(isArabic ? "يرجى تحديد جهاز واحد على الأقل للمتابعة." : "Please select at least one appliance to proceed.");
                return;
            }
            goToDiscoveryStep(2);
        });
    }

    const btnGotoStep3 = document.getElementById('btn-goto-step3');
    if (btnGotoStep3) btnGotoStep3.addEventListener('click', () => goToDiscoveryStep(3));

    const btnGotoStep4 = document.getElementById('btn-goto-step4');
    if (btnGotoStep4) btnGotoStep4.addEventListener('click', () => goToDiscoveryStep(4));

    const btnGotoStep5 = document.getElementById('btn-goto-step5');
    if (btnGotoStep5) btnGotoStep5.addEventListener('click', () => goToDiscoveryStep(5));

    const btnBackToStep1 = document.getElementById('btn-back-to-step1');
    if (btnBackToStep1) btnBackToStep1.addEventListener('click', () => goToDiscoveryStep(1));

    const btnBackToStep2 = document.getElementById('btn-back-to-step2');
    if (btnBackToStep2) btnBackToStep2.addEventListener('click', () => goToDiscoveryStep(2));

    const btnBackToStep3 = document.getElementById('btn-back-to-step3');
    if (btnBackToStep3) btnBackToStep3.addEventListener('click', () => goToDiscoveryStep(3));

    const btnBackToStep4 = document.getElementById('btn-back-to-step4');
    if (btnBackToStep4) btnBackToStep4.addEventListener('click', () => goToDiscoveryStep(4));

    const btnBackToStep5 = document.getElementById('btn-back-to-step5');
    if (btnBackToStep5) btnBackToStep5.addEventListener('click', () => goToDiscoveryStep(5));

    const btnCalibrateBill = document.getElementById('btn-calibrate-bill');
    if (btnCalibrateBill) {
        btnCalibrateBill.addEventListener('click', () => {
            const slider = document.getElementById('discovery-bill-slider');
            calibratedBillValue = slider ? parseFloat(slider.value) : 50;
            goToDiscoveryStep(6);
        });
    }

    const btnSkipCalibration = document.getElementById('btn-skip-calibration');
    if (btnSkipCalibration) {
        btnSkipCalibration.addEventListener('click', () => {
            calibratedBillValue = null;
            goToDiscoveryStep(6);
        });
    }

    const btnGotoStep7 = document.getElementById('btn-goto-step7');
    if (btnGotoStep7) btnGotoStep7.addEventListener('click', () => goToDiscoveryStep(7));

    const btnResetDiscovery = document.getElementById('btn-reset-discovery');
    if (btnResetDiscovery) btnResetDiscovery.addEventListener('click', resetDiscoveryJourney);

    const discBillSlider = document.getElementById('discovery-bill-slider');
    const discBillDisplay = document.getElementById('discovery-bill-display');
    if (discBillSlider && discBillDisplay) {
        discBillSlider.addEventListener('input', () => {
            discBillDisplay.textContent = discBillSlider.value;
        });
    }


    // Manual Bill Entry — Show/Hide Panel
    const btnShowManualEntry = document.getElementById('btn-show-manual-entry');
    const manualBillPanel = document.getElementById('manual-bill-panel');
    const step5MainActions = document.getElementById('step5-main-actions');
    const step5SliderSection = document.getElementById('step5-slider-section');

    if (btnShowManualEntry && manualBillPanel) {
        btnShowManualEntry.addEventListener('click', () => {
            if (step5MainActions) step5MainActions.style.display = 'none';
            if (step5SliderSection) step5SliderSection.style.display = 'none';
            manualBillPanel.style.display = 'block';
            // Pre-fill with slider value
            const manualAmountInput = document.getElementById('manual-bill-amount');
            if (manualAmountInput && discBillSlider) {
                manualAmountInput.value = discBillSlider.value;
            }
        });
    }

    const btnCancelManual = document.getElementById('btn-cancel-manual');
    if (btnCancelManual && manualBillPanel) {
        btnCancelManual.addEventListener('click', () => {
            manualBillPanel.style.display = 'none';
            if (step5MainActions) step5MainActions.style.display = 'flex';
            if (step5SliderSection) step5SliderSection.style.display = 'block';
        });
    }

    const btnManualBillSubmit = document.getElementById('btn-manual-bill-submit');
    if (btnManualBillSubmit) {
        btnManualBillSubmit.addEventListener('click', () => {
            const manualAmountInput = document.getElementById('manual-bill-amount');
            const val = manualAmountInput ? parseFloat(manualAmountInput.value) : null;
            if (!val || isNaN(val) || val < 5) {
                alert(isArabic ? 'يرجى إدخال مبلغ صحيح.' : 'Please enter a valid bill amount.');
                return;
            }
            calibratedBillValue = val;
            // Sync slider display
            if (discBillSlider) discBillSlider.value = Math.min(1000, Math.max(10, val));
            if (discBillDisplay) discBillDisplay.textContent = Math.round(val);
            goToDiscoveryStep(6);
        });
    }


    // Lead Form submission handler
    const discoveryForm = document.getElementById('discovery-lead-form');
    const discoveryFeedback = document.getElementById('discovery-form-feedback');

    if (discoveryForm) {
        discoveryForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Honeypot spam interceptor
            const honeypot = discoveryForm.querySelector('input[name="honeypot"]').value;
            if (honeypot) {
                console.warn("[SolarLead] Spam submission detected via honeypot.");
                return;
            }

            // Validate Phone Number
            const phoneField = document.getElementById('disc-lead-phone');
            const phoneValue = phoneField.value.trim().replace(/\D/g, "");
            if (phoneValue.length < 8) {
                showDiscoveryFormFeedback(
                    isArabic ? "يرجى إدخال رقم هاتف عماني صالح يتكون من 8 أرقام على الأقل." : "Please enter a valid phone number with at least 8 digits.", 
                    "error"
                );
                return;
            }

            const submitBtn = document.getElementById('btn-submit-discovery');
            const spinner = submitBtn ? submitBtn.querySelector('.spinner') : null;

            if (spinner) spinner.style.display = 'inline-block';
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(discoveryForm);
            
            const selectedPropType = propType ? propType.value : 'residential';
            let finalResult;
            if (calibratedBillValue !== null) {
                finalResult = window.SolarCalculatorEngine.calculate(calibratedBillValue, selectedPropType, loc.value);
            } else {
                finalResult = window.SolarCalculatorEngine.calculateByLoad(applianceQuantities[selectedPropType], selectedPropType, loc.value);
            }
            formData.append('estimated_kw', finalResult.systemSizeKw);
            formData.append('estimated_cost', finalResult.costRange.formatted);
            formData.append('estimated_savings', finalResult.yearlySavingsOmr);
            formData.append('sizer_mode', 'discovery');

            fetch('chatbot.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (spinner) spinner.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;

                if (data.status === 'success') {
                    showDiscoveryFormFeedback(
                        isArabic 
                          ? "شكراً لك! تم حجز تقييم الطاقة الشمسية الخاص بك بنجاح. سيتصل بك مهندسونا قريباً." 
                          : "Thank you! Your solar assessment has been booked successfully. Our engineers will contact you shortly.", 
                        "success"
                    );
                    discoveryForm.reset();

                    if (window.SolarAnalytics) {
                        window.SolarAnalytics.markFormSubmitted();
                        window.SolarAnalytics.track("lead_submitted", {
                            name: formData.get('name'),
                            phone: formData.get('phone'),
                            location: loc.value,
                            property_type: selectedPropType,
                            system_size_kw: finalResult.systemSizeKw,
                            enquiry_source: "discovery_assessment_form"
                        });
                    }
                } else {
                    showDiscoveryFormFeedback(
                        data.message || (isArabic ? "فشل إرسال الطلب. يرجى إعادة المحاولة." : "Failed to submit. Please try again."),
                        "error"
                    );
                }
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;
                console.error("[SolarLead] Discovery AJAX error:", err);
                showDiscoveryFormFeedback(
                    isArabic ? "فشل اتصال الشبكة. يرجى التحقق من اتصالك وإعادة المحاولة." : "Network connection failed. Please check your network and try again.",
                    "error"
                );
            });
        });
    }

    function showDiscoveryFormFeedback(msg, type) {
        if (!discoveryFeedback) return;
        discoveryFeedback.textContent = msg;
        discoveryFeedback.style.display = 'block';
        
        if (type === 'success') {
            discoveryFeedback.style.background = 'rgba(62, 182, 73, 0.15)';
            discoveryFeedback.style.color = '#3eb649';
            discoveryFeedback.style.border = '1px solid #3eb649';
        } else {
            discoveryFeedback.style.background = 'rgba(239, 68, 68, 0.15)';
            discoveryFeedback.style.color = '#ef4444';
            discoveryFeedback.style.border = '1px solid #ef4444';
        }

        setTimeout(() => {
            discoveryFeedback.style.display = 'none';
        }, 8000);
    }

});
