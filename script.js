document.addEventListener('DOMContentLoaded', () => {
    

    // --- 2. Mobile Menu ---
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const navLinks = document.querySelector('.nav-links');
    
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

    // --- 3. Navbar Scroll Effect ---
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // --- 4. Scroll Reveal Animations (Intersection Observer) ---
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

    // --- 5. FAQ Accordion ---
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            
            // Close all others
            document.querySelectorAll('.accordion-header').forEach(otherHeader => {
                if (otherHeader !== header) {
                    otherHeader.classList.remove('active');
                    otherHeader.nextElementSibling.style.maxHeight = null;
                }
            });
            
            // Toggle current
            header.classList.toggle('active');
            if (header.classList.contains('active')) {
                content.style.maxHeight = content.scrollHeight + "px";
            } else {
                content.style.maxHeight = null;
            }
        });
    });

    // --- 6. Solar Calculator Logic ---
    const billSlider = document.getElementById('bill-slider');
    const billDisplay = document.getElementById('bill-display');
    const propType = document.getElementById('property-type');
    const loc = document.getElementById('location');
    
    const resSize = document.getElementById('res-size');
    const resPanels = document.getElementById('res-panels');
    const resCost = document.getElementById('res-cost');
    const resSavings = document.getElementById('res-savings');

    // Function to animate numbers
    function animateValue(obj, start, end, duration, prefix = "", suffix = "") {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            
            // easeOutQuart
            const easeProgress = 1 - Math.pow(1 - progress, 4);
            
            let currentVal = (progress === 1) ? end : start + (end - start) * easeProgress;
            
            // Format numbers nicely
            if (typeof end === 'string' && end.includes('-')) {
                // Do not animate string ranges
                obj.textContent = end;
                return;
            } else if (Number.isInteger(end)) {
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
        const monthlyBill = parseFloat(billSlider.value);
        billDisplay.textContent = monthlyBill;

        // Fetch user dropdown selections
        const selectedPropType = propType.value || 'residential';
        const selectedLocation = loc.value || 'muscat';

        // Calculate using Centralized Engine (Single Source of Truth)
        let result = { systemSizeKw: 1, panelCount: 2, costRange: { formatted: "0 OMR" }, yearlySavingsOmr: 0, paybackYears: 4 };
        if (window.SolarCalculatorEngine) {
            result = window.SolarCalculatorEngine.calculate(monthlyBill, selectedPropType, selectedLocation);
        }

        // Animate Results (if elements exist)
        if (resSize.textContent === '0 kW') {
            animateValue(resSize, 0, result.systemSizeKw, 1000, "", " kW");
            animateValue(resPanels, 0, result.panelCount, 1000);
            resCost.textContent = result.costRange.formatted; // Range is hard to animate, set directly
            animateValue(resSavings, 0, result.yearlySavingsOmr, 1000, "", " OMR");
        } else {
            // Instantly update if not first load
            resSize.textContent = result.systemSizeKw.toFixed(1) + ' kW';
            resPanels.textContent = result.panelCount;
            resCost.textContent = result.costRange.formatted;
            resSavings.textContent = `${result.yearlySavingsOmr.toLocaleString()} OMR`;
        }

        // Analytics Tracking
        if (window.SolarAnalytics) {
            window.SolarAnalytics.markCalculatorTouched();
            // Fire throttled tracking event
            window.SolarAnalytics.track("calculator_change", {
                monthly_bill: monthlyBill,
                property_type: selectedPropType,
                location: selectedLocation,
                system_size_kw: result.systemSizeKw,
                panel_count: result.panelCount,
                yearly_savings_omr: result.yearlySavingsOmr
            });
        }
    }

    billSlider.addEventListener('input', calculateSolar);
    propType.addEventListener('change', calculateSolar);
    loc.addEventListener('change', calculateSolar);

    calculateSolar();

    // Hook up explain with AI advisor button
    const explainBtn = document.getElementById('calc-explain-btn');
    if (explainBtn) {
        explainBtn.addEventListener('click', function() {
            if (window.SolarChatbot && window.SolarCalculatorEngine) {
                const monthlyBill = parseFloat(billSlider.value);
                const selectedPropType = propType.value || 'residential';
                const selectedLocation = loc.value || 'muscat';

                const result = window.SolarCalculatorEngine.calculate(monthlyBill, selectedPropType, selectedLocation);

                // Send to Chatbot Context & Open Window
                window.SolarChatbot.explainCalculatorResult({
                    systemSize: result.systemSizeKw.toFixed(1),
                    panels: result.panelCount,
                    cost: result.costRange.formatted.replace(" OMR", ""),
                    monthlySavings: result.monthlySavingsOmr.toLocaleString(),
                    yearlySavings: result.yearlySavingsOmr.toLocaleString(),
                    payback: result.paybackYears.toFixed(1)
                });

                // Track CTA Conversions
                if (window.SolarAnalytics) {
                    window.SolarAnalytics.track("calculator_explain_click", {
                        monthly_bill: monthlyBill,
                        property_type: selectedPropType,
                        location: selectedLocation,
                        system_size_kw: result.systemSizeKw
                    });
                }
            }
        });
    }

});
