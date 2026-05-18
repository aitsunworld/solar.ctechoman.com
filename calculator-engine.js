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
  function calculateSolarResults(monthlyBill, propertyType, location, availableSpace) {
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
    let targetSystemSizeKw = yearlyConsumptionKwh / yieldKwh;
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

  // Expose to window globally
  window.SolarCalculatorEngine = {
    calculate: calculateSolarResults,
    PANEL_WATTAGE: PANEL_WATTAGE,
    TARIFFS: BASE_TARIFFS,
    YIELDS: REGIONAL_YIELDS,
    COSTS: EPC_COSTS_PER_KW
  };

})();
