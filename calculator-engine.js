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
    // HVAC
    { id: "ac_1ton", category: "HVAC", name_en: "AC 1 ton", name_ar: "مكيف 1 طن", min_w: 1200, max_w: 1500, hours: 8.0, default_qty: 1 },
    { id: "ac_2ton", category: "HVAC", name_en: "AC 2 ton", name_ar: "مكيف 2 طن", min_w: 2400, max_w: 3000, hours: 8.0, default_qty: 1 },
    { id: "water_heater", category: "HVAC", name_en: "Water Heater", name_ar: "سخان مياه", min_w: 1500, max_w: 2000, hours: 3.0, default_qty: 0 },

    // Kitchen
    { id: "refrigerator", category: "Kitchen", name_en: "Refrigerator", name_ar: "ثلاجة", min_w: 150, max_w: 300, hours: 24.0, default_qty: 1 },
    { id: "freezer", category: "Kitchen", name_en: "Freezer", name_ar: "فريزر", min_w: 200, max_w: 400, hours: 24.0, default_qty: 0 },
    { id: "washing_machine", category: "Kitchen", name_en: "Washing Machine", name_ar: "غسالة ملابس", min_w: 500, max_w: 1000, hours: 1.0, default_qty: 0 },
    { id: "microwave", category: "Kitchen", name_en: "Microwave", name_ar: "مايكرويف", min_w: 1000, max_w: 1500, hours: 0.5, default_qty: 0 },

    // General
    { id: "tv", category: "General", name_en: "TV", name_ar: "تلفاز", min_w: 80, max_w: 150, hours: 6.0, default_qty: 1 },
    { id: "led_lights", category: "General", name_en: "LED Lights (Set of 10)", name_ar: "أضواء LED (طقم 10)", min_w: 50, max_w: 100, hours: 6.0, default_qty: 1 },

    // Luxury
    { id: "water_pump", category: "Luxury", name_en: "Water Pump", name_ar: "مضخة مياه", min_w: 750, max_w: 1500, hours: 2.0, default_qty: 0 },
    { id: "ev_charger", category: "Luxury", name_en: "EV Charger", name_ar: "شاحن سيارة كهربائية", min_w: 3600, max_w: 7200, hours: 4.0, default_qty: 0 }
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
    };
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
