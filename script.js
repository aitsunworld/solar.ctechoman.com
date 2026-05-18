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

    const TARIFF = 0.020; 
    const ANNUAL_YIELD_PER_KW = 1700; 
    const PANEL_WATTAGE = 550; 
    const COST_PER_KW = 350; 

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
                obj.innerHTML = end;
                return;
            } else if (Number.isInteger(end)) {
                obj.innerHTML = `${prefix}${Math.floor(currentVal).toLocaleString()}${suffix}`;
            } else {
                obj.innerHTML = `${prefix}${currentVal.toFixed(1)}${suffix}`;
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

        const monthlyConsumption = monthlyBill / TARIFF;
        const yearlyConsumption = monthlyConsumption * 12;
        let systemSize = yearlyConsumption / ANNUAL_YIELD_PER_KW;
        if (systemSize < 1) systemSize = 1;
        
        const totalWatts = systemSize * 1000;
        const numPanels = Math.ceil(totalWatts / PANEL_WATTAGE);
        const exactSystemSize = (numPanels * PANEL_WATTAGE) / 1000;
        
        const baseCost = exactSystemSize * COST_PER_KW;
        const minCost = Math.floor(baseCost * 0.9);
        const maxCost = Math.ceil(baseCost * 1.1);
        const costString = `${minCost.toLocaleString()} - ${maxCost.toLocaleString()} OMR`;
        
        const yearlySavings = monthlyBill * 12;

        // Animate Results (if elements exist)
        if (resSize.textContent === '0 kW') {
            animateValue(resSize, 0, exactSystemSize, 1000, "", " kW");
            animateValue(resPanels, 0, numPanels, 1000);
            resCost.textContent = costString; // Range is hard to animate, set directly
            animateValue(resSavings, 0, yearlySavings, 1000, "", " OMR");
        } else {
            // Instantly update if not first load
            resSize.textContent = exactSystemSize.toFixed(1) + ' kW';
            resPanels.textContent = numPanels;
            resCost.textContent = costString;
            resSavings.textContent = `${yearlySavings.toLocaleString()} OMR`;
        }
    }

    billSlider.addEventListener('input', calculateSolar);
    propType.addEventListener('change', calculateSolar);
    loc.addEventListener('change', calculateSolar);

    calculateSolar();

});
