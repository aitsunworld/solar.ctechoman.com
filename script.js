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
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
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
        Security: isArabic ? "أنظمة الأمان والذكية" : "Security & Smart Home"
    };

    // Render Appliance Registry dynamically
    // Render Appliance Registry dynamically
    function initApplianceSizer() {
        if (!applianceInputs || !window.SolarCalculatorEngine) return;

        const appliances = window.SolarCalculatorEngine.APPLIANCES;
        
        // Setup initial default quantities
        appliances.forEach(app => {
            if (applianceQuantities[app.id] === undefined) {
                applianceQuantities[app.id] = app.default_qty || 0;
            }
        });

        // Generate Category Filter Bar HTML
        let html = `
            <div class="appliance-filter-bar">
                <button type="button" class="filter-tab active" data-category="all">
                    <span>${isArabic ? 'الكل' : 'All'}</span>
                </button>
                <button type="button" class="filter-tab" data-category="HVAC">
                    <span>${isArabic ? 'تكييف وتدفئة' : 'HVAC'}</span>
                </button>
                <button type="button" class="filter-tab" data-category="Kitchen">
                    <span>${isArabic ? 'المطبخ' : 'Kitchen'}</span>
                </button>
                <button type="button" class="filter-tab" data-category="General">
                    <span>${isArabic ? 'المعيشة' : 'Living'}</span>
                </button>
                <button type="button" class="filter-tab" data-category="Luxury">
                    <span>${isArabic ? 'الرفاهية' : 'Luxury'}</span>
                </button>
            </div>
            <div class="appliance-grid">
        `;

        appliances.forEach(app => {
            const name = isArabic ? app.name_ar : app.name_en;
            const currentQty = applianceQuantities[app.id];
            const activeClass = currentQty > 0 ? ' active-card' : '';
            const disabledAttr = currentQty === 0 ? 'disabled' : '';
            
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
                            <span class="spec-badge power">${app.min_w}W</span>
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
                        // Add smooth fade-in and scale animation
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
            tabBill.style.background = 'var(--color-primary)';
            tabBill.style.color = '#fff';
            tabBill.style.border = 'none';

            tabAppliances.classList.remove('active');
            tabAppliances.style.background = 'transparent';
            tabAppliances.style.color = 'var(--color-text)';
            tabAppliances.style.border = '1.5px solid var(--color-border)';

            billInputs.style.display = 'block';
            applianceInputs.style.display = 'none';
            calculateSolar();
        });

        tabAppliances.addEventListener('click', () => {
            activeSizerMode = 'appliances';
            tabAppliances.classList.add('active');
            tabAppliances.style.background = 'var(--color-primary)';
            tabAppliances.style.color = '#fff';
            tabAppliances.style.border = 'none';

            tabBill.classList.remove('active');
            tabBill.style.background = 'transparent';
            tabBill.style.color = 'var(--color-text)';
            tabBill.style.border = '1.5px solid var(--color-border)';

            billInputs.style.display = 'none';
            applianceInputs.style.display = 'block';
            calculateSolar();
        });
    }

    // Bind Core Inputs
    if (billSlider) billSlider.addEventListener('input', calculateSolar);
    if (propType) propType.addEventListener('change', calculateSolar);
    if (loc) loc.addEventListener('change', calculateSolar);

    // Dynamic Sizer Initializer
    initApplianceSizer();
    calculateSolar();

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

});
