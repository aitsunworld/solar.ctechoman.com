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
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
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
    function initApplianceSizer() {
        if (!applianceInputs || !window.SolarCalculatorEngine) return;

        const appliances = window.SolarCalculatorEngine.APPLIANCES;
        
        // Group by category
        const categories = {};
        appliances.forEach(app => {
            if (!categories[app.category]) {
                categories[app.category] = [];
            }
            categories[app.category].push(app);
            // Setup initial default quantities
            applianceQuantities[app.id] = app.default_qty || 0;
        });

        let html = '';
        for (const [catName, list] of Object.entries(categories)) {
            html += `<h4 style="margin: 1.5rem 0 0.75rem 0; color: var(--color-primary); font-size: 1rem; text-transform: uppercase; border-bottom: 1px solid var(--color-border); padding-bottom: 0.25rem;">${categoryLabels[catName] || catName}</h4>`;
            
            list.forEach(app => {
                const name = isArabic ? app.name_ar : app.name_en;
                const powerSpec = `${app.min_w}-${app.max_w} W`;
                const hrsSpec = isArabic ? `${app.hours} ساعة/يوم` : `${app.hours} hrs/day`;
                const currentQty = applianceQuantities[app.id];

                html += `
                    <div class="appliance-item" data-id="${app.id}">
                        <div class="appliance-info">
                            <div class="appliance-icon">${getApplianceIcon(app.id)}</div>
                            <div class="appliance-details">
                                <h4>${name}</h4>
                                <span>${powerSpec} • ${hrsSpec}</span>
                            </div>
                        </div>
                        <div class="qty-selector">
                            <button type="button" class="qty-btn minus" data-id="${app.id}">-</button>
                            <span class="qty-val" id="qty-${app.id}">${currentQty}</span>
                            <button type="button" class="qty-btn plus" data-id="${app.id}">+</button>
                        </div>
                    </div>
                `;
            });
        }
        applianceInputs.innerHTML = html;

        // Bind interactive Qty controls
        applianceInputs.addEventListener('click', (e) => {
            const btn = e.target.closest('.qty-btn');
            if (!btn) return;

            const appId = btn.dataset.id;
            let qty = applianceQuantities[appId] || 0;

            if (btn.classList.contains('plus')) {
                qty = Math.min(99, qty + 1);
            } else if (btn.classList.contains('minus')) {
                qty = Math.max(0, qty - 1);
            }

            applianceQuantities[appId] = qty;
            const qtyText = document.getElementById(`qty-${appId}`);
            if (qtyText) qtyText.textContent = qty;

            calculateSolar();
        });
    }

    function getApplianceIcon(id) {
        const icons = {
            fridge: "❄️",
            washer: "🧺",
            dishwasher: "🍽️",
            microwave: "🍲",
            stove: "🍳",
            kettle: "☕",
            split_ac: "❄️",
            water_heater: "♨️",
            cooler: "🌬️",
            tv: "📺",
            lighting: "💡",
            iron: "🔌",
            vacuum: "🧹",
            pc: "💻",
            pool_pump: "🏊",
            pool_heater: "🌡️",
            jacuzzi: "🛀",
            irrigation: "⛲",
            cctv: "📹"
        };
        return icons[id] || "⚡";
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
