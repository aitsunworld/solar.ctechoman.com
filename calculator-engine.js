/**
 * calculator-engine.js
 * Concept Technologies LLC - Core Solar Sizing & Tariff Calculation Engine
 *
 * Centralized Single Source of Truth (SSOT) for all solar mathematics.
 * Used identically by the Website UI, Chatbot Advisor, CRM lead payloads, and Analytics.
 * Modular and migration-friendly (pure JavaScript).
 */

(function () {
  "use strict";

  const PANEL_WATTAGE = 550;

  const BASE_TARIFFS = {
    residential: 0.020,
    commercial: 0.024,
    industrial: 0.028
  };

  const REGIONAL_YIELDS = {
    muscat: 1700,
    batinah: 1700,
    dhofar: 1445,
    dakhiliyah: 1750,
    other: 1650
  };

  const EPC_COSTS_PER_KW = {
    residential: 380,
    commercial: 320,
    industrial: 285
  };

  const APPLIANCES = [
    { id: "window_ac", property_type: "residential", category: "HVAC", name_en: "Window AC (Standard Room AC)", name_ar: "\u0645\u0643\u064A\u0641 \u0646\u0627\u0641\u0630\u0629 (\u0645\u0643\u064A\u0641 \u063A\u0631\u0641\u0629 \u0642\u064A\u0627\u0633\u064A)", min_w: 1500, max_w: 2000, hours: 8.0, default_qty: 0 },
    { id: "ac_1ton", property_type: "residential", category: "HVAC", name_en: "Split AC 1 Ton (Small Bedroom AC)", name_ar: "\u0645\u0643\u064A\u0641 \u0633\u0628\u0644\u064A\u062A 1 \u0637\u0646 (\u0645\u0643\u064A\u0641 \u063A\u0631\u0641\u0629 \u0646\u0648\u0645 \u0635\u063A\u064A\u0631\u0629)", min_w: 1200, max_w: 1500, hours: 8.0, default_qty: 1 },
    { id: "ac_1_5ton", property_type: "residential", category: "HVAC", name_en: "Split AC 1.5 Ton (Medium Bedroom AC)", name_ar: "\u0645\u0643\u064A\u0641 \u0633\u0628\u0644\u064A\u062A 1.5 \u0637\u0646 (\u0645\u0643\u064A\u0641 \u063A\u0631\u0641\u0629 \u0646\u0648\u0645 \u0645\u062A\u0648\u0633\u0637\u0629)", min_w: 1800, max_w: 2200, hours: 8.0, default_qty: 0 },
    { id: "ac_2ton", property_type: "residential", category: "HVAC", name_en: "Split AC 2 Ton (Large Room / Hall AC)", name_ar: "\u0645\u0643\u064A\u0641 \u0633\u0628\u0644\u064A\u062A 2 \u0637\u0646 (\u0645\u0643\u064A\u0641 \u063A\u0631\u0641\u0629 \u0643\u0628\u064A\u0631\u0629 / \u0635\u0627\u0644\u0629)", min_w: 2400, max_w: 3000, hours: 8.0, default_qty: 1 },
    { id: "refrigerator", property_type: "residential", category: "Kitchen", name_en: "Refrigerator", name_ar: "\u062B\u0644\u0627\u062C\u0629", min_w: 150, max_w: 300, hours: 24.0, default_qty: 1 },
    { id: "freezer", property_type: "residential", category: "Kitchen", name_en: "Freezer", name_ar: "\u0641\u0631\u063A\u064A\u0632\u0631", min_w: 200, max_w: 400, hours: 24.0, default_qty: 0 },
    { id: "washing_machine", property_type: "residential", category: "Kitchen", name_en: "Washing Machine", name_ar: "\u063A\u0633\u0627\u0644\u0629 \u0645\u0644\u0627\u0628\u0633", min_w: 500, max_w: 1000, hours: 1.0, default_qty: 0 },
    { id: "water_heater", property_type: "residential", category: "HVAC", name_en: "Water Heater", name_ar: "\u0633\u062E\u0627\u0646 \u0645\u064A\u0627\u0621", min_w: 1500, max_w: 2000, hours: 3.0, default_qty: 0 },
    { id: "tv", property_type: "residential", category: "General", name_en: "TV", name_ar: "\u062A\u0644\u0641\u0632", min_w: 80, max_w: 150, hours: 6.0, default_qty: 1 },
    { id: "led_lights", property_type: "residential", category: "General", name_en: "LED Lights (Set of 10)", name_ar: "\u0623\u0636\u0627\u0648\u0627\u062A LED (\u0637\u0642\u0645 10)", min_w: 50, max_w: 100, hours: 6.0, default_qty: 1 },
    { id: "ceiling_fan", property_type: "residential", category: "HVAC", name_en: "Ceiling Fan", name_ar: "\u0645\u0631\u0648\u062D\u0629 \u0633\u0642\u0641", min_w: 50, max_w: 80, hours: 12.0, default_qty: 0 },
    { id: "microwave", property_type: "residential", category: "Kitchen", name_en: "Microwave", name_ar: "\u0645\u0627\u064A\u0643\u0631\u0648\u064A\u0641", min_w: 1000, max_w: 1500, hours: 0.5, default_qty: 0 },
    { id: "electric_oven", property_type: "residential", category: "Kitchen", name_en: "Electric Oven", name_ar: "\u0641\u0631\u0646 \u0643\u0647\u0631\u0628\u0627\u0626\u064A", min_w: 2000, max_w: 3000, hours: 1.0, default_qty: 0 },
    { id: "water_pump", property_type: "residential", category: "Luxury", name_en: "Water Pump", name_ar: "\u0645\u0636\u062E\u0629 \u0645\u064A\u0627\u0647", min_w: 750, max_w: 1500, hours: 2.0, default_qty: 0 },
    { id: "dishwasher", property_type: "residential", category: "Kitchen", name_en: "Dishwasher", name_ar: "\u063A\u0633\u0627\u0644\u0629 \u0635\u062D\u0648\u0646", min_w: 1200, max_w: 1800, hours: 1.0, default_qty: 0 },
    { id: "desktop_pc", property_type: "residential", category: "General", name_en: "Desktop PC", name_ar: "\u0643\u0645\u0628\u064A\u0648\u062A\u0631 \u0645\u0643\u062A\u0628\u064A", min_w: 150, max_w: 300, hours: 4.0, default_qty: 0 },
    { id: "laptop", property_type: "residential", category: "General", name_en: "Laptop", name_ar: "\u0643\u0645\u0628\u064A\u0648\u062A\u0631 \u0645\u062D\u0645\u0648\u0644", min_w: 50, max_w: 90, hours: 6.0, default_qty: 0 },
    { id: "ev_charger", property_type: "residential", category: "Luxury", name_en: "EV Charger", name_ar: "\u0634\u0627\u0639\u0646 \u0633\u064A\u0627\u0631\u0629 \u0643\u0647\u0631\u0628\u0627\u0626\u064A\u0629", min_w: 3600, max_w: 7200, hours: 4.0, default_qty: 0 },
    { id: "com_ducted_ac", property_type: "commercial", category: "HVAC", name_en: "Commercial Ducted/Standing AC", name_ar: "\u0645\u0643\u0627\u062A\u0628\u064A \u0645\u0627\u0631\u0643\u0632/\u0639\u0645\u0648\u062F\u064A \u062A\u062C\u0627\u0631\u064A", min_w: 4000, max_w: 6000, hours: 11.0, default_qty: 1 },
    { id: "com_server_rack", property_type: "commercial", category: "IT", name_en: "Network Rack Server", name_ar: "\u062E\u0627\u062F\u0645 \u0627\u0644\u0634\u0628\u0643\u0629\u064A", min_w: 500, max_w: 1200, hours: 24.0, default_qty: 0 },
    { id: "com_led_lighting", property_type: "commercial", category: "Lighting", name_en: "Commercial LED Lighting Panels (100 pcs)", name_ar: "\u0627\u0644\u0627\u0644\u0648\u0627\u062D \u0627\u0644\u0634\u0645\u0633\u064A\u0629 LED (\u0639\u062F\u062F 100 \u0644\u0648\u062D\u0629)", min_w: 1500, max_w: 1500, hours: 11.0, default_qty: 1 },
    { id: "com_copier", property_type: "commercial", category: "Office", name_en: "Heavy-Duty Office Copier/Printer", name_ar: "\u0627\u0644\u0622\u0644\u0627\u0629 \u0627\u0644\u0643\u0627\u0631\u064A\u0629 \u0627\u0644\u0639\u0627\u0644\u064A\u0629", min_w: 750, max_w: 750, hours: 3.0, default_qty: 0 },
    { id: "com_display_fridge", property_type: "commercial", category: "Cooling", name_en: "Commercial Display Refrigerator", name_ar: "\u0627\u0644\u062B\u0644\u0627\u062C\u0629\u0629 \u0627\u0644\u062A\u062C\u0627\u0631\u064A\u0629", min_w: 800, max_w: 1200, hours: 24.0, default_qty: 1 },
    { id: "com_cctv", property_type: "commercial", category: "Security", name_en: "CCTV Security System & NVR", name_ar: "\u0646\u0638\u0627\u0645 \u0627\u0644\u0623\u0645\u0627\u0646 \u0648\u0643\u0627\u0645\u064A\u0631\u0627\u062A \u0627\u0644\u0645\u0631\u0627\u0642\u0628\u0629", min_w: 200, max_w: 400, hours: 24.0, default_qty: 1 },
    { id: "com_workstation", property_type: "commercial", category: "Office", name_en: "Desktop Workstations (PCs)", name_ar: "\u0627\u0644\u0623\u0633\u0627\u0633\u064A\u062A (\u0627\u0644\u0639\u0646\u064A\u064A\u0646)", min_w: 200, max_w: 200, hours: 8.0, default_qty: 2 },
    { id: "com_water_dispenser", property_type: "commercial", category: "Kitchen", name_en: "Commercial Water Dispenser", name_ar: "\u0628\u0631\u062F \u0627\u0644\u0645\u064A\u0627\u0629 \u0627\u0644\u062A\u062C\u0627\u0631\u064A", min_w: 500, max_w: 500, hours: 10.0, default_qty: 0 },
    { id: "com_sliding_door", property_type: "commercial", category: "General", name_en: "Automatic Sliding Glass Doors", name_ar: "\u0627\u0644\u0623\u0636\u0627\u0628\u0627\u062A \u0627\u0644\u0632\u062C\u0627\u064A\u0644\u064A\u0629", min_w: 150, max_w: 150, hours: 11.0, default_qty: 0 },
    { id: "com_adv_signage", property_type: "commercial", category: "Lighting", name_en: "LED Advertising Signage", name_ar: "\u0627\u0644\u0644\u0627\u062D\u0642\u0629 \u0627\u0644\u0625\u0639\u0644\u0627\u0646\u064A\u0629 LED \u0645\u0636\u064A\u0641\u0629", min_w: 300, max_w: 800, hours: 11.0, default_qty: 0 },
    { id: "ind_compressor", property_type: "industrial", category: "Machinery", name_en: "Industrial Air Compressor", name_ar: "\u0636\u0627\u063A\u0639 \u0647\u0648\u0627\u0621 \u0627\u0644\u062D\u0628\u0627\u0633\u0629", min_w: 15000, max_w: 37000, hours: 12.0, default_qty: 1 },
    { id: "ind_chiller", property_type: "industrial", category: "Cooling", name_en: "Industrial Water-Cooled Chiller", name_ar: "\u0645\u0628\u0631\u062F \u0627\u0644\u0645\u064A\u0627\u0629 \u0627\u0644\u0635\u0646\u0627\u0627\u064A", min_w: 50000, max_w: 150000, hours: 20.0, default_qty: 0 },
    { id: "ind_water_pump", property_type: "industrial", category: "Machinery", name_en: "Three-Phase Water/Fluid Pump (20 HP)", name_ar: "\u062A\u0634\u0627\u0639 \u0627\u0644\u0645\u064A\u0627\u0629 \u0627\u0644\u062B\u0644\u0627\u062E\u0629\u064A\u0629 (20 \u062D\u0635\u0627\u0646\u0629)", min_w: 15000, max_w: 15000, hours: 8.0, default_qty: 1 },
    { id: "ind_molding_mach", property_type: "industrial", category: "Machinery", name_en: "Plastic Injection Molding Machine", name_ar: "\u0627\u0644\u0642\u064A\u0639 \u0627\u0644\u0643\u0631\u064A\u0633\u064A\u0629 \u0644\u0642\u0637\u0639\u0627\u062A \u0627\u0644\u0628\u0644\u0627\u0633\u062A\u064A\u0643", min_w: 30000, max_w: 75000, hours: 20.0, default_qty: 0 },
    { id: "ind_gantry_crane", property_type: "industrial", category: "Machinery", name_en: "Industrial Overhead Gantry Crane", name_ar: "\u0627\u0644\u0631\u062E\u064A\u0639\u0629 \u0627\u0644\u062A\u0644\u0627\u064A\u0641\u0627\u062A\u064A\u0629", min_w: 5000, max_w: 15000, hours: 3.0, default_qty: 0 },
    { id: "ind_exhaust_fan", property_type: "industrial", category: "Ventilation", name_en: "Industrial Exhaust/Ventilation Fan", name_ar: "\u0645\u0631\u0648\u062D \u0627\u0644\u062A\u0646\u0647\u064A\u0629 \u0627\u0644\u0635\u0646\u0627\u0627\u064A", min_w: 3000, max_w: 7500, hours: 18.0, default_qty: 2 },
    { id: "ind_welding_mach", property_type: "industrial", category: "Machinery", name_en: "Industrial Rectifier Welding Machine", name_ar: "\u0627\u0644\u0642\u064A\u0639 \u0627\u0644\u0644\u062D\u0627\u0645", min_w: 8000, max_w: 12000, hours: 5.0, default_qty: 0 },
    { id: "ind_conveyor", property_type: "industrial", category: "Machinery", name_en: "Assembly Line Conveyor System", name_ar: "\u0633\u064A\u0631 \u0627\u0644\u0645\u0627\u0632\u064A\u0646", min_w: 4000, max_w: 11000, hours: 12.0, default_qty: 1 },
    { id: "ind_cnc_machine", property_type: "industrial", category: "Machinery", name_en: "Heavy-Duty CNC Milling/Lathe Machine", name_ar: "\u0627\u0644\u0642\u064A\u0639 CNC \u0627\u0644\u0643\u0627\u0646\u0648\u0646", min_w: 11000, max_w: 22000, hours: 10.0, default_qty: 0 },
    { id: "ind_induction_furnace", property_type: "industrial", category: "Machinery", name_en: "Small Industrial Induction Furnace", name_ar: "\u0641\u0631\u0646 \u0627\u0644\u062D\u0628\u0627\u0633\u062F", min_w: 50000, max_w: 100000, hours: 8.0, default_qty: 0 }
  ];

  function calculateSolarResults(monthlyBill, propertyType, location, availableSpace, overrideSystemSize) {
    if (overrideSystemSize === void 0) { overrideSystemSize = null; }
    
    var bill = Math.max(10, parseFloat(monthlyBill) || 50);
    var prop = (propertyType || "residential").toLowerCase();
    var loc = (location || "muscat").toLowerCase();
    var space = parseFloat(availableSpace) || null;

    var tariff = BASE_TARIFFS[prop] || BASE_TARIFFS.residential;
    var yieldKwh = REGIONAL_YIELDS[loc] || REGIONAL_YIELDS.other;
    var costPerKw = EPC_COSTS_PER_KW[prop] || EPC_COSTS_PER_KW.residential;

    var monthlyConsumptionKwh = bill / tariff;
    var yearlyConsumptionKwh = monthlyConsumptionKwh * 12;

    var targetSystemSizeKw = overrideSystemSize !== null ? overrideSystemSize : (yearlyConsumptionKwh / yieldKwh);
    if (targetSystemSizeKw < 1.0) targetSystemSizeKw = 1.0;

    var totalWatts = targetSystemSizeKw * 1000;
    var numPanels = Math.ceil(totalWatts / PANEL_WATTAGE);
    var exactSystemSizeKw = (numPanels * PANEL_WATTAGE) / 1000;

    var estimatedSpaceRequiredSqm = Math.ceil(exactSystemSizeKw * 6);

    var baseEpcCost = exactSystemSizeKw * costPerKw;
    var minCost = Math.floor(baseEpcCost * 0.9);
    var maxCost = Math.ceil(baseEpcCost * 1.1);

    var averageOffsetFactor = prop === "residential" ? 0.85 : 0.95;
    var yearlySavingsOmr = bill * 12 * averageOffsetFactor;
    var paybackPeriodYears = baseEpcCost / yearlySavingsOmr;

    var annualCo2OffsetTons = (exactSystemSizeKw * yieldKwh * 0.82) / 1000;

    var warnings = [];

    if (prop === "residential" && exactSystemSizeKw > 30) {
      warnings.push("EXCEEDS_RESIDENTIAL_GRID_CAP");
    }

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
        formatted: minCost.toLocaleString() + " - " + maxCost.toLocaleString() + " OMR"
      },
      yearlySavingsOmr: Math.round(yearlySavingsOmr),
      monthlySavingsOmr: Math.round(yearlySavingsOmr / 12),
      paybackYears: parseFloat(paybackPeriodYears.toFixed(1)),
      co2OffsetTons: parseFloat(annualCo2OffsetTons.toFixed(1)),
      warnings: warnings
    };
  }

  function calculateByLoad(applianceSelections, propertyType, location, availableSpace) {
    var prop = (propertyType || "residential").toLowerCase();
    var loc = (location || "muscat").toLowerCase();
    var tariff = BASE_TARIFFS[prop] || BASE_TARIFFS.residential;

    var totalMinWatts = 0;
    var totalMaxWatts = 0;
    var totalMinKwhDaily = 0;
    var totalMaxKwhDaily = 0;

    APPLIANCES.forEach(function(spec) {
      if (spec.property_type !== prop) return;
      var qty = parseInt(applianceSelections[spec.id]) || 0;
      if (qty > 0) {
        totalMinWatts += spec.min_w * qty;
        totalMaxWatts += spec.max_w * qty;
        totalMinKwhDaily += (spec.min_w * qty * spec.hours) / 1000;
        totalMaxKwhDaily += (spec.max_w * qty * spec.hours) / 1000;
      }
    });

    if (totalMaxKwhDaily <= 0) {
      return Object.assign({}, calculateSolarResults(50, prop, loc, availableSpace), {
        loadSizing: {
          minDailyKwh: 0,
          maxDailyKwh: 0,
          avgDailyKwh: 0,
          peakLoadWatts: 0,
          inverterRecommendationKw: 0,
          batteryRecommendationKwh: 0
        }
      });
    }

    var avgDailyKwh = (totalMinKwhDaily + totalMaxKwhDaily) / 2;
    var monthlyConsumptionKwh = avgDailyKwh * 30;
    var simulatedBill = monthlyConsumptionKwh * tariff;
    var overrideSystemSize = (avgDailyKwh * 1.2) / 5.5;
    var results = calculateSolarResults(simulatedBill, prop, loc, availableSpace, overrideSystemSize);

    var inverterSizeKw = parseFloat(((totalMaxWatts * 1.25) / 1000).toFixed(1));
    var batteryBackupKwh = parseFloat((avgDailyKwh * 1.0 / 0.80).toFixed(1));

    return Object.assign({}, results, {
      loadSizing: {
        minDailyKwh: parseFloat(totalMinKwhDaily.toFixed(1)),
        maxDailyKwh: parseFloat(totalMaxKwhDaily.toFixed(1)),
        avgDailyKwh: parseFloat(avgDailyKwh.toFixed(1)),
        peakLoadWatts: totalMaxWatts,
        inverterRecommendationKw: Math.max(1.5, inverterSizeKw),
        batteryRecommendationKwh: Math.max(2.4, batteryBackupKwh)
      }
    });
  }

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