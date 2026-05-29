/**
 * calculator-engine.js
 * Concept Technologies LLC — Core Solar Sizing & Tariff Calculation Engine
 * 
 * Centralized Single Source of Truth (SSOT) for all solar mathematics.
 * Used identically by the Website UI, Chatbot Advisor, CRM lead payloads, and Analytics.
 * Modular and migration-friendly (pure JavaScript).
 */

(function () {
  "use strict";

  const PANEL_WATTAGE = 550; // Watts per panel

  // ─── REGULATORY & ECONOMIC CONSTANTS (OMAN SPECIFIC) ──────────────────────

  // APSR-compliant basic tiered retail tariffs (OMR per kWh)
  const BASE_TARIFFS = {
    residential: 0.020, // 20 Baiza standard tier
    commercial: 0.024,  // 24 Baiza retail commercial
    industrial: 0.028   // 28 Baiza large industrial / peak tariff
  };

  // Governorate specific annual solar yields (kWh per kWp per year)
  // Incorporates weather patterns, tilt optimizations, and cloud cover indexes
  const REGIONAL_YIELDS = {
    muscat: 1700,
    batinah: 1700,
    dhofar: 1445,       // 15% solar discount for Dhofar monsoon (Khareef) cloud cover
    dakhiliyah: 1750,   // High desert irradiance premium
    other: 1650
  };

  // Average localized turn-key EPC pricing (OMR per kW installed)
  // Factoring in systemic economies of scale
  const EPC_COSTS_PER_KW = {
    residential: 380, // High custom residential mounting systems
    commercial: 320,  // Medium commercial engineering scale
    industrial: 285   // Optimised utility ground mounts
  };

  // ─── APPLIANCE DEFINITION REGISTRY (BOTTOM-UP ELECTRICAL LOAD SCHEDULE) ─────
  const APPLIANCES = [
    // Residential Category
    { id: "ac_1ton", property_type: "residential", category: "HVAC", name_en: "AC 1 ton", name_ar: "مكيف 1 طن", min_w: 1200, max_w: 1500, hours: 8.0, default_qty: 1 },
    { id: "ac_2ton", property_type: "residential", category: "HVAC", name_en: "AC 2 ton", name_ar: "مكيف 2 طن", min_w: 2400, max_w: 3000, hours: 8.0, default_qty: 1 },
    { id: "water_heater", property_type: "residential", category: "HVAC", name_en: "Water Heater", name_ar: "سخان مياه", min_w: 1500, max_w: 2000, hours: 3.0, default_qty: 0 },
    { id: "refrigerator", property_type: "residential", category: "Kitchen", name_en: "Refrigerator", name_ar: "ثلاجة", min_w: 150, max_w: 300, hours: 24.0, default_qty: 1 },
    { id: "freezer", property_type: "residential", category: "Kitchen", name_en: "Freezer", name_ar: "فريزر", min_w: 200, max_w: 400, hours: 24.0, default_qty: 0 },
    { id: "washing_machine", property_type: "residential", category: "Kitchen", name_en: "Washing Machine", name_ar: "غسالة ملابس", min_w: 500, max_w: 1000, hours: 1.0, default_qty: 0 },
    { id: "microwave", property_type: "residential", category: "Kitchen", name_en: "Microwave", name_ar: "مايكرويف", min_w: 1000, max_w: 1500, hours: 0.5, default_qty: 0 },
    { id: "tv", property_type: "residential", category: "General", name_en: "TV", name_ar: "تلفاز", min_w: 80, max_w: 150, hours: 6.0, default_qty: 1 },
    { id: "led_lights", property_type: "residential", category: "General", name_en: "LED Lights (Set of 10)", name_ar: "أضواء LED (طقم 10)", min_w: 50, max_w: 100, hours: 6.0, default_qty: 1 },
    { id: "water_pump", property_type: "residential", category: "Luxury", name_en: "Water Pump", name_ar: "مضخة مياه", min_w: 750, max_w: 1500, hours: 2.0, default_qty: 0 },
    { id: "ev_charger", property_type: "residential", category: "Luxury", name_en: "EV Charger", name_ar: "شاحن سيارة كهربائية", min_w: 3600, max_w: 7200, hours: 4.0, default_qty: 0 },

    // Commercial Category
    { id: "com_ducted_ac", property_type: "commercial", category: "HVAC", name_en: "Commercial Ducted/Standing AC", name_ar: "مكيف مركزي/عمودي تجاري", min_w: 4000, max_w: 6000, hours: 11.0, default_qty: 1 },
    { id: "com_server_rack", property_type: "commercial", category: "IT", name_en: "Network Rack Server", name_ar: "خادم شبكة (سيرفر)", min_w: 500, max_w: 1200, hours: 24.0, default_qty: 0 },
    { id: "com_led_lighting", property_type: "commercial", category: "Lighting", name_en: "Commercial LED Lighting Panels (100 pcs)", name_ar: "ألواح إضاءة LED تجارية (100 لوح)", min_w: 1500, max_w: 1500, hours: 11.0, default_qty: 1 },
    { id: "com_copier", property_type: "commercial", category: "Office", name_en: "Heavy-Duty Office Copier/Printer", name_ar: "آلة تصوير/طابعة مكتبية شديدة التحمل", min_w: 750, max_w: 750, hours: 3.0, default_qty: 0 },
    { id: "com_display_fridge", property_type: "commercial", category: "Cooling", name_en: "Commercial Display Refrigerator", name_ar: "ثلاجة عرض تجارية", min_w: 800, max_w: 1200, hours: 24.0, default_qty: 1 },
    { id: "com_cctv", property_type: "commercial", category: "Security", name_en: "CCTV Security System & NVR", name_ar: "نظام أمني وكاميرات مراقبة", min_w: 200, max_w: 400, hours: 24.0, default_qty: 1 },
    { id: "com_workstation", property_type: "commercial", category: "Office", name_en: "Desktop Workstations (PCs)", name_ar: "أجهزة كمبيوتر مكتبية (PC)", min_w: 200, max_w: 200, hours: 8.0, default_qty: 2 },
    { id: "com_water_dispenser", property_type: "commercial", category: "Kitchen", name_en: "Commercial Water Dispenser", name_ar: "براد مياه تجاري", min_w: 500, max_w: 500, hours: 10.0, default_qty: 0 },
    { id: "com_sliding_door", property_type: "commercial", category: "General", name_en: "Automatic Sliding Glass Doors", name_ar: "أبواب زجاجية منزلقة أوتوماتيكية", min_w: 150, max_w: 150, hours: 11.0, default_qty: 0 },
    { id: "com_adv_signage", property_type: "commercial", category: "Lighting", name_en: "LED Advertising Signage", name_ar: "لوحة إعلانات LED مضيئة", min_w: 300, max_w: 800, hours: 11.0, default_qty: 0 },

    // Industrial Category
    { id: "ind_compressor", property_type: "industrial", category: "Machinery", name_en: "Industrial Air Compressor", name_ar: "ضاغط هواء صناعي", min_w: 15000, max_w: 37000, hours: 12.0, default_qty: 1 },
    { id: "ind_chiller", property_type: "industrial", category: "Cooling", name_en: "Industrial Water-Cooled Chiller", name_ar: "مبرد مياه صناعي مبرد بالماء", min_w: 50000, max_w: 150000, hours: 20.0, default_qty: 0 },
    { id: "ind_water_pump", property_type: "industrial", category: "Machinery", name_en: "Three-Phase Water/Fluid Pump (20 HP)", name_ar: "مضخة مياه ثلاثية الطور (20 حصان)", min_w: 15000, max_w: 15000, hours: 8.0, default_qty: 1 },
    { id: "ind_molding_mach", property_type: "industrial", category: "Machinery", name_en: "Plastic Injection Molding Machine", name_ar: "آلة تشكيل البلاستيك بالحقن", min_w: 30000, max_w: 75000, hours: 20.0, default_qty: 0 },
    { id: "ind_gantry_crane", property_type: "industrial", category: "Machinery", name_en: "Industrial Overhead Gantry Crane", name_ar: "رافعة علوية صناعية", min_w: 5000, max_w: 15000, hours: 3.0, default_qty: 0 },
    { id: "ind_exhaust_fan", property_type: "industrial", category: "Ventilation", name_en: "Industrial Exhaust/Ventilation Fan", name_ar: "مروحة تهوية وعادم صناعية", min_w: 3000, max_w: 7500, hours: 18.0, default_qty: 2 },
    { id: "ind_welding_mach", property_type: "industrial", category: "Machinery", name_en: "Industrial Rectifier Welding Machine", name_ar: "آلة لحام صناعية", min_w: 8000, max_w: 12000, hours: 5.0, default_qty: 0 },
    { id: "ind_conveyor", property_type: "industrial", category: "Machinery", name_en: "Assembly Line Conveyor System", name_ar: "نظام سير ناقل لخط التجميع", min_w: 4000, max_w: 11000, hours: 12.0, default_qty: 1 },
    { id: "ind_cnc_machine", property_type: "industrial", category: "Machinery", name_en: "Heavy-Duty CNC Milling/Lathe Machine", name_ar: "آلة خرط وتفريز CNC ثقيلة", min_w: 11000, max_w: 22000, hours: 10.0, default_qty: 0 },
    { id: "ind_induction_furnace", property_type: "industrial", category: "Machinery", name_en: "Small Industrial Induction Furnace", name_ar: "فرن حث صناعي صغير", min_w: 50000, max_w: 100000, hours: 8.0, default_qty: 0 }
  ];

  // ─── SOLAR ESTIMATION ENGINE ────────────────────────────────────────────────

  /**
   * Performs real-world energy sizing, savings, and financial computations.
   * 
   * @param {number} monthlyBill - Bill amount in OMR
   * @param {string} propertyType - residential | commercial | industrial
   * @param {string} location - muscat | batinah | dhofar | dakhiliyah | other
   * @param {number} [availableSpace] - Available roof area in sqm (optional)
   * @returns {Object} Calculated metrics and constraint warnings
   */
  function calculateSolarResults(monthlyBill, propertyType, location, availableSpace, overrideSystemSize = null) {
    // Sanitise inputs
    const bill = Math.max(10, parseFloat(monthlyBill) || 50);
    const prop = (propertyType || "residential").toLowerCase();
    const loc = (location || "muscat").toLowerCase();
    const space = parseFloat(availableSpace) || null;

    // Load dynamic parameters
    const tariff = BASE_TARIFFS[prop] || BASE_TARIFFS.residential;
    const yieldKwh = REGIONAL_YIELDS[loc] || REGIONAL_YIELDS.other;
    const costPerKw = EPC_COSTS_PER_KW[prop] || EPC_COSTS_PER_KW.residential;

    // Calculate consumption metrics
    const monthlyConsumptionKwh = bill / tariff;
    const yearlyConsumptionKwh = monthlyConsumptionKwh * 12;

    // Sizing computation: Offset 100% of daytime consumption
    // Assuming Oman net-metering grid injection parity
    let targetSystemSizeKw = overrideSystemSize !== null ? overrideSystemSize : (yearlyConsumptionKwh / yieldKwh);
    if (targetSystemSizeKw < 1.0) targetSystemSizeKw = 1.0; // Min system scale threshold

    // Determine panel metrics
    const totalWatts = targetSystemSizeKw * 1000;
    const numPanels = Math.ceil(totalWatts / PANEL_WATTAGE);
    const exactSystemSizeKw = (numPanels * PANEL_WATTAGE) / 1000;

    // Space constraints: 1 kWp flat-roof tilt spacing occupies approx 6 sqm
    const estimatedSpaceRequiredSqm = Math.ceil(exactSystemSizeKw * 6);

    // Cost ranges factoring standard component deviation (inverters, cabling, structures)
    const baseEpcCost = exactSystemSizeKw * costPerKw;
    const minCost = Math.floor(baseEpcCost * 0.9);
    const maxCost = Math.ceil(baseEpcCost * 1.1);

    // Financial indicators
    // Assuming average Omani self-consumption rate (70% direct offset, 30% export pool price)
    const averageOffsetFactor = prop === "residential" ? 0.85 : 0.95;
    const yearlySavingsOmr = bill * 12 * averageOffsetFactor;
    const paybackPeriodYears = baseEpcCost / yearlySavingsOmr;

    // Environmental impacts
    // 0.82 kg CO2 offset per solar kWh generated in the GCC region
    const annualCo2OffsetTons = (exactSystemSizeKw * yieldKwh * 0.82) / 1000;

    // Compile warnings/alerts
    const warnings = [];

    // Check regulatory caps in Oman (30 kW for residential grid connection limits)
    if (prop === "residential" && exactSystemSizeKw > 30) {
      warnings.push("EXCEEDS_RESIDENTIAL_GRID_CAP");
    }

    // Check customer-provided roof size constraints
    if (space !== null && estimatedSpaceRequiredSqm > space) {
      warnings.push("ROOF_SPACE_INSUFFICIENT");
    }

    return {
      inputs: {
        monthlyBill: bill,
        propertyType: prop,
        location: loc,
        availableSpace: space
      },
      systemSizeKw: parseFloat(exactSystemSizeKw.toFixed(2)),
      panelCount: numPanels,
      spaceRequiredSqm: estimatedSpaceRequiredSqm,
      costRange: {
        min: minCost,
        max: maxCost,
        formatted: `${minCost.toLocaleString()} - ${maxCost.toLocaleString()} OMR`
      },
      yearlySavingsOmr: Math.round(yearlySavingsOmr),
      monthlySavingsOmr: Math.round(yearlySavingsOmr / 12),
      paybackYears: parseFloat(paybackPeriodYears.toFixed(1)),
      co2OffsetTons: parseFloat(annualCo2OffsetTons.toFixed(1)),
      warnings: warnings
    };
  }

  /**
   * Sizing calculations based on a bottom-up list of appliances.
   * Leverages the exact logic inside "Electrical Load Schedule.xlsx".
   * 
   * @param {Object} applianceSelections - Key-value pair of { [applianceId]: quantity }
   * @param {string} propertyType - residential | commercial | industrial
   * @param {string} location - muscat | batinah | dhofar | dakhiliyah | other
   * @param {number} [availableSpace] - Available roof area in sqm
   * @returns {Object} Detailed sizing + battery/inverter specs
   */
  function calculateByLoad(applianceSelections, propertyType, location, availableSpace) {
    const prop = (propertyType || "residential").toLowerCase();
    const loc = (location || "muscat").toLowerCase();
    const tariff = BASE_TARIFFS[prop] || BASE_TARIFFS.residential;

    let totalMinWatts = 0;
    let totalMaxWatts = 0;
    let totalMinKwhDaily = 0;
    let totalMaxKwhDaily = 0;

    APPLIANCES.forEach(spec => {
      if (spec.property_type !== prop) return;
      const qty = parseInt(applianceSelections[spec.id]) || 0;
      if (qty > 0) {
        const applianceMinWatts = spec.min_w * qty;
        const applianceMaxWatts = spec.max_w * qty;

        totalMinWatts += applianceMinWatts;
        totalMaxWatts += applianceMaxWatts;

        totalMinKwhDaily += (applianceMinWatts * spec.hours) / 1000;
        totalMaxKwhDaily += (applianceMaxWatts * spec.hours) / 1000;
      }
    });

    // Fallbacks if no appliances selected
    if (totalMaxKwhDaily <= 0) {
      return {
        ...calculateSolarResults(50, prop, loc, availableSpace),
        loadSizing: {
          minDailyKwh: 0,
          maxDailyKwh: 0,
          avgDailyKwh: 0,
          peakLoadWatts: 0,
          inverterRecommendationKw: 0,
          batteryRecommendationKwh: 0
        }
      };
    }

    const avgDailyKwh = (totalMinKwhDaily + totalMaxKwhDaily) / 2;
    const monthlyConsumptionKwh = avgDailyKwh * 30;

    // Convert equivalent bottom-up consumption back to OMR bill (SSOT parity!)
    const simulatedBill = monthlyConsumptionKwh * tariff;

    // Specific calculation formula for Omani sun hours (5.5) and 1.2 safety factor
    const overrideSystemSize = (avgDailyKwh * 1.2) / 5.5;

    // Run core sizer using simulated equivalent bill and the exact overridden system size
    const results = calculateSolarResults(simulatedBill, prop, loc, availableSpace, overrideSystemSize);

    // Calculate Inverter size (Max watts * 1.25 standard safety headroom limit)
    const inverterSizeKw = parseFloat(((totalMaxWatts * 1.25) / 1000).toFixed(1));

    // Calculate Hybrid Battery size (1.0 day autonomy backup at 80% Depth-of-Discharge DoD)
    const batteryBackupKwh = parseFloat((avgDailyKwh * 1.0 / 0.80).toFixed(1));

    return {
      ...results,
      loadSizing: {
        minDailyKwh: parseFloat(totalMinKwhDaily.toFixed(1)),
        maxDailyKwh: parseFloat(totalMaxKwhDaily.toFixed(1)),
        avgDailyKwh: parseFloat(avgDailyKwh.toFixed(1)),
        peakLoadWatts: totalMaxWatts,
        inverterRecommendationKw: Math.max(1.5, inverterSizeKw),
        batteryRecommendationKwh: Math.max(2.4, batteryBackupKwh)
      }

  // Expose to window globally
  window.SolarCalculatorEngine = {
    calculate: calculateSolarResults,
    calculateByLoad: calculateByLoad,
    APPLIANCES: APPLIANCES,
    PANEL_WATTAGE: PANEL_WATTAGE,
    TARIFFS: BASE_TARIFFS,
    YIELDS: REGIONAL_YIELDS,
    COSTS: EPC_COSTS_PER_KW
  };

})();