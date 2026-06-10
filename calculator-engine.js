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
    { id: "window_ac", property_type: "residential", category: "HVAC", name_en: "Window Air Conditioner", name_ar: "مكيف هواء نافذة", subtitle_en: "Classic room cooling (1.5 Ton)", subtitle_ar: "وحدة تبريد الغرف الكلاسيكية (1.5 طن)", min_w: 1500, max_w: 2000, hours: 8.0, default_qty: 0, desc_en: "Standard window-mounted room cooler", desc_ar: "وحدة تبريد الغرفة القياسية المثبتة على النافذة", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "ac_1ton", property_type: "residential", category: "HVAC", name_en: "Small Split AC", name_ar: "مكيف سبليت صغير", subtitle_en: "Suitable for rooms up to 12m²", subtitle_ar: "مثالي لغرف الدراسة والنوم الصغيرة (1 طن)", min_w: 1200, max_w: 1500, hours: 8.0, default_qty: 1, desc_en: "Energy-efficient cooling for study rooms & small bedrooms", desc_ar: "تبريد موفر للطاقة للمساحات حتى 12 متر مربع", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "ac_1_5ton", property_type: "residential", category: "HVAC", name_en: "Medium Split AC", name_ar: "مكيف سبليت متوسط", subtitle_en: "Perfect for bedrooms up to 18m²", subtitle_ar: "مثالي لغرف النوم والمساحات المتوسطة (1.5 طن)", min_w: 1800, max_w: 2200, hours: 8.0, default_qty: 0, desc_en: "Optimal cooling for master bedrooms & medium rooms", desc_ar: "تبريد مثالي للمساحات التي تصل إلى 18 متر مربع", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "ac_2ton", property_type: "residential", category: "HVAC", name_en: "Large Split AC", name_ar: "مكيف سبليت كبير", subtitle_en: "Best for spacious majlis & halls", subtitle_ar: "للصالات والمجالس المفتوحة الكبيرة (2 طن)", min_w: 2400, max_w: 3000, hours: 8.0, default_qty: 1, desc_en: "High-capacity cooling for rooms up to 25m²", desc_ar: "تبريد عالي القدرة للمساحات التي تصل إلى 25 متر مربع", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "refrigerator", property_type: "residential", category: "Kitchen", name_en: "Family Refrigerator", name_ar: "ثلاجة عائلية", subtitle_en: "Runs 24/7 keeping food fresh", subtitle_ar: "تعمل على مدار الساعة لحفظ الأطعمة", min_w: 150, max_w: 300, hours: 24.0, default_qty: 1, desc_en: "Always-on kitchen food refrigeration", desc_ar: "ثلاجة المطبخ لحفظ الأطعمة على مدار الساعة", cat_en: "Kitchen", cat_ar: "المطبخ" },
    { id: "freezer", property_type: "residential", category: "Kitchen", name_en: "Deep Freezer", name_ar: "فريزر عميق", subtitle_en: "Dedicated long-term preservation", subtitle_ar: "تخزين الأغذية وحفظها لفترات طويلة", min_w: 200, max_w: 400, hours: 24.0, default_qty: 0, desc_en: "Dedicated food freezing unit", desc_ar: "وحدة تجميد الأطعمة المخصصة", cat_en: "Kitchen", cat_ar: "المطبخ" },
    { id: "washing_machine", property_type: "residential", category: "Kitchen", name_en: "Automatic Washing Machine", name_ar: "غسالة ملابس أوتوماتيكية", subtitle_en: "Daily laundry care & cleaning", subtitle_ar: "للعناية اليومية بالملابس وغسلها", min_w: 500, max_w: 1000, hours: 1.0, default_qty: 0, desc_en: "Automatic clothes washing appliance", desc_ar: "غسالة الملابس الأوتوماتيكية القياسية", cat_en: "Laundry", cat_ar: "الغسيل" },
    { id: "water_heater", property_type: "residential", category: "HVAC", name_en: "Electric Water Heater", name_ar: "سخان مياه كهربائي", subtitle_en: "Hot water for kitchen & bath", subtitle_ar: "لتوفير المياه الساخنة الأساسية في المنزل", min_w: 1500, max_w: 2000, hours: 3.0, default_qty: 0, desc_en: "Bathroom & kitchen hot water system", desc_ar: "سخان المياه الساخنة للحمام والمطبخ", cat_en: "Utility", cat_ar: "المرافق" },
    { id: "tv", property_type: "residential", category: "General", name_en: "Smart TV & Media", name_ar: "شاشة تلفاز ذكية وترفيه", subtitle_en: "Family lounge screen & consoles", subtitle_ar: "شاشات صالة العائلة وأجهزة الألعاب", min_w: 80, max_w: 150, hours: 6.0, default_qty: 1, desc_en: "Living room media display screen", desc_ar: "شاشة عرض التلفاز لغرفة المعيشة", cat_en: "Entertainment", cat_ar: "الترفيه" },
    { id: "led_lights", property_type: "residential", category: "General", name_en: "Smart LED Home Lighting", name_ar: "إضاءة LED منزلية ذكية", subtitle_en: "Set of 10 energy-efficient lights", subtitle_ar: "مجموعة من 10 مصابيح موفرة للطاقة", min_w: 50, max_w: 100, hours: 6.0, default_qty: 1, desc_en: "Energy-efficient home light bulbs", desc_ar: "مصابيح إضاءة منزلية موفرة للطاقة", cat_en: "Lighting", cat_ar: "الإضاءة" },
    { id: "ceiling_fan", property_type: "residential", category: "HVAC", name_en: "Standard Ceiling Fan", name_ar: "مروحة سقف قياسية", subtitle_en: "Circulates air efficiently", subtitle_ar: "تحسين دوران الهواء وتلطيف الغرفة بكفاءة", min_w: 50, max_w: 80, hours: 12.0, default_qty: 0, desc_en: "Standard overhead air circulator", desc_ar: "مروحة سقف دائرية قياسية لتحريك الهواء", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "microwave", property_type: "residential", category: "Kitchen", name_en: "Kitchen Microwave Oven", name_ar: "فرن مايكروويف للمطبخ", subtitle_en: "Fast heating & defrosting", subtitle_ar: "للتسخين السريع وإذابة الأطعمة", min_w: 1000, max_w: 1500, hours: 0.5, default_qty: 0, desc_en: "Kitchen countertop reheating appliance", desc_ar: "جهاز تسخين الوجبات السريع للمطبخ", cat_en: "Kitchen", cat_ar: "المطبخ" },
    { id: "electric_oven", property_type: "residential", category: "Kitchen", name_en: "Electric Oven & Grill", name_ar: "فرن وشواية كهربائية", subtitle_en: "High-power cooking & baking", subtitle_ar: "للطهي والخبز عالي الحرارة والقدرة", min_w: 2000, max_w: 3000, hours: 1.0, default_qty: 0, desc_en: "Baking and heavy cooking oven", desc_ar: "فرن طهي وخبز كهربائي عالي القدرة", cat_en: "Kitchen", cat_ar: "المطبخ" },
    { id: "water_pump", property_type: "residential", category: "Luxury", name_en: "Home Water Pump", name_ar: "مضخة مياه منزلية", subtitle_en: "Maintains strong water pressure", subtitle_ar: "للحفاظ على ضغط مياه قوي ومستقر", min_w: 750, max_w: 1500, hours: 2.0, default_qty: 0, desc_en: "Rooftop water pressurization system", desc_ar: "مضخة ضغط مياه الخزان العلوي", cat_en: "Utility", cat_ar: "المرافق" },
    { id: "dishwasher", property_type: "residential", category: "Kitchen", name_en: "Automatic Dishwasher", name_ar: "غسالة صحون تلقائية", subtitle_en: "Saves water & handles cleanup", subtitle_ar: "توفير المياه وتسهيل تنظيف الأواني", min_w: 1200, max_w: 1800, hours: 1.0, default_qty: 0, desc_en: "Automatic kitchen dish washing unit", desc_ar: "غسالة صحون أوتوماتيكية للمطبخ", cat_en: "Kitchen", cat_ar: "المطبخ" },
    { id: "desktop_pc", property_type: "residential", category: "General", name_en: "Desktop PC Workstation", name_ar: "جهاز كمبيوتر مكتبي متكامل", subtitle_en: "Powering home offices & learning", subtitle_ar: "لإدارة الأعمال المنزلية والدراسة", min_w: 150, max_w: 300, hours: 4.0, default_qty: 0, desc_en: "Standard desktop computer station", desc_ar: "كمبيوتر مكتبي مع شاشته وملحقاته", cat_en: "General", cat_ar: "عام" },
    { id: "laptop", property_type: "residential", category: "General", name_en: "Personal Laptop", name_ar: "كمبيوتر محمول شخصي", subtitle_en: "Low power everyday device", subtitle_ar: "جهاز يومي ذو استهلاك طاقة منخفض", min_w: 50, max_w: 90, hours: 6.0, default_qty: 0, desc_en: "Personal computer and device charger", desc_ar: "كمبيوتر محمول شخصي لشحن الأجهزة والعمل", cat_en: "General", cat_ar: "عام" },
    { id: "ev_charger", property_type: "residential", category: "Luxury", name_en: "Fast EV Home Charger", name_ar: "شاحن منزلي سريع للسيارات الكهربائية", subtitle_en: "Charges electric vehicles overnight", subtitle_ar: "لشحن سيارتك الكهربائية بالكامل خلال الليل", min_w: 3600, max_w: 7200, hours: 4.0, default_qty: 0, desc_en: "Residential electric vehicle charging", desc_ar: "شاحن منزلي مخصص للسيارات الكهربائية", cat_en: "Utility", cat_ar: "المرافق" },
    { id: "com_ducted_ac", property_type: "commercial", category: "HVAC", name_en: "Commercial Ducted AC", name_ar: "مكيف تجاري مركزي", min_w: 4000, max_w: 6000, hours: 11.0, default_qty: 1, desc_en: "Ducted cooling for business spaces", desc_ar: "مكيف مركزي تجاري للمحلات والمكاتب", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "com_server_rack", property_type: "commercial", category: "IT", name_en: "Network Rack Server", name_ar: "خادم شبكة", min_w: 500, max_w: 1200, hours: 24.0, default_qty: 0, desc_en: "Active business servers running 24/7", desc_ar: "خوادم شبكية تعمل باستمرار لقطاع الأعمال", cat_en: "IT", cat_ar: "تقنية المعلومات" },
    { id: "com_led_lighting", property_type: "commercial", category: "Lighting", name_en: "Commercial LED Panel Set", name_ar: "إضاءة LED تجارية", min_w: 1500, max_w: 1500, hours: 11.0, default_qty: 1, desc_en: "Office/retail high efficiency lighting", desc_ar: "مصابيح إضاءة تجارية عالية الكفاءة للمكتب", cat_en: "Lighting", cat_ar: "الإضاءة" },
    { id: "com_copier", property_type: "commercial", category: "Office", name_en: "Office Copier & Printer", name_ar: "طابعة ومصورة مكتبية", min_w: 750, max_w: 750, hours: 3.0, default_qty: 0, desc_en: "Multifunction document processing", desc_ar: "طابعة ومصورة وثائق مكتبية متعددة الوظائف", cat_en: "Office", cat_ar: "المكتب" },
    { id: "com_display_fridge", property_type: "commercial", category: "Cooling", name_en: "Commercial Display Fridge", name_ar: "ثلاجة عرض تجارية", min_w: 800, max_w: 1200, hours: 24.0, default_qty: 1, desc_en: "Glass showcase beverage cooler", desc_ar: "ثلاجة عرض زجاجية للأطعمة والمشروبات", cat_en: "Cooling", cat_ar: "التبريد" },
    { id: "com_cctv", property_type: "commercial", category: "Security", name_en: "CCTV Security System", name_ar: "نظام مراقبة CCTV", min_w: 200, max_w: 400, hours: 24.0, default_qty: 1, desc_en: "Continuous camera monitoring & NVR", desc_ar: "نظام مراقبة أمني بالكاميرات مع جهاز تسجيل", cat_en: "Security", cat_ar: "الأمن" },
    { id: "com_workstation", property_type: "commercial", category: "Office", name_en: "Desktop Workstation", name_ar: "محطة عمل مكتبية", min_w: 200, max_w: 200, hours: 8.0, default_qty: 2, desc_en: "Desktop computer for office staff", desc_ar: "جهاز كمبيوتر مكتبي لموظفي الشركة", cat_en: "Office", cat_ar: "المكتب" },
    { id: "com_water_dispenser", property_type: "commercial", category: "Kitchen", name_en: "Commercial Water Dispenser", name_ar: "موزع مياه تجاري", min_w: 500, max_w: 500, hours: 10.0, default_qty: 0, desc_en: "Office hot/cold water supply", desc_ar: "موزع مياه باردة وساخنة للموظفين والعملاء", cat_en: "Office", cat_ar: "المكتب" },
    { id: "com_sliding_door", property_type: "commercial", category: "General", name_en: "Auto Sliding Doors", name_ar: "أبواب منزلقة تلقائية", min_w: 150, max_w: 150, hours: 11.0, default_qty: 0, desc_en: "Electronic motion-sensing entrance", desc_ar: "مدخل أبواب زجاجية منزلقة تعمل بالحساسات", cat_en: "General", cat_ar: "عام" },
    { id: "com_adv_signage", property_type: "commercial", category: "Lighting", name_en: "LED Advertising Signage", name_ar: "لوحة إعلانية LED", min_w: 300, max_w: 800, hours: 11.0, default_qty: 0, desc_en: "Illuminated storefront brand display", desc_ar: "لوحة إعلانية مضيئة للعلامة التجارية بالخارج", cat_en: "Lighting", cat_ar: "الإضاءة" },
    { id: "ind_compressor", property_type: "industrial", category: "Machinery", name_en: "Industrial Air Compressor", name_ar: "ضاغط هواء صناعي", min_w: 15000, max_w: 37000, hours: 12.0, default_qty: 1, desc_en: "Heavy power plant pneumatic air supply", desc_ar: "ضاغط هواء صناعي عالي الطاقة للعمليات", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_chiller", property_type: "industrial", category: "Cooling", name_en: "Industrial Water Chiller", name_ar: "مبرد مياه صناعي", min_w: 50000, max_w: 150000, hours: 20.0, default_qty: 0, desc_en: "Water-cooled fluid process chiller", desc_ar: "مبرد سوائل صناعي ضخم لتبريد الآلات", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_water_pump", property_type: "industrial", category: "Machinery", name_en: "Three-Phase Water Pump", name_ar: "مضخة مياه ثلاثية الطور", min_w: 15000, max_w: 15000, hours: 8.0, default_qty: 1, desc_en: "High-volume process pump (20 HP)", desc_ar: "مضخة مياه وتصريف للمنشآت بقوة 20 حصان", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_molding_mach", property_type: "industrial", category: "Machinery", name_en: "Injection Molding Machine", name_ar: "آلة حقن وتشكيل البلاستيك", min_w: 30000, max_w: 75000, hours: 20.0, default_qty: 0, desc_en: "Automated custom parts manufacturing", desc_ar: "آلة حقن أوتوماتيكية لتصنيع قطع البلاستيك", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_gantry_crane", property_type: "industrial", category: "Machinery", name_en: "Overhead Gantry Crane", name_ar: "رافعة علوية صناعية", min_w: 5000, max_w: 15000, hours: 3.0, default_qty: 0, desc_en: "Heavy equipment plant room hoisting", desc_ar: "رافعة جسرية علوية لنقل الأوزان الثقيلة", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_exhaust_fan", property_type: "industrial", category: "Ventilation", name_en: "Industrial Exhaust Fan", name_ar: "مروحة سحب صناعية", min_w: 3000, max_w: 7500, hours: 18.0, default_qty: 2, desc_en: "High volume warehouse air extraction", desc_ar: "مروحة سحب وتفريغ الهواء للتهوية في المصنع", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_welding_mach", property_type: "industrial", category: "Machinery", name_en: "Industrial Welder Station", name_ar: "محطة لحام صناعية", min_w: 8000, max_w: 12000, hours: 5.0, default_qty: 0, desc_en: "Heavy-duty manufacturing metal welder", desc_ar: "آلة لحام صناعية ثقيلة للأعمال المعدنية", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_conveyor", property_type: "industrial", category: "Machinery", name_en: "Assembly Line Conveyor", name_ar: "سير ناقل صناعي", min_w: 4000, max_w: 11000, hours: 12.0, default_qty: 1, desc_en: "Automated factory assembly transport", desc_ar: "نظام سير ناقل لخطوط التجميع والتغليف", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_cnc_machine", property_type: "industrial", category: "Machinery", name_en: "CNC Milling Machine", name_ar: "آلة تشكيل بالتحكم الرقمي", min_w: 11000, max_w: 22000, hours: 10.0, default_qty: 0, desc_en: "Precision automated metal lathe", desc_ar: "آلة تشكيل وخراطة دقيقة تعمل بالكمبيوتر", cat_en: "Factory", cat_ar: "المصنع" },
    { id: "ind_induction_furnace", property_type: "industrial", category: "Machinery", name_en: "Induction Furnace", name_ar: "فرن حثي صهر المعادن", min_w: 50000, max_w: 100000, hours: 8.0, default_qty: 0, desc_en: "Metal smelting induction furnace", desc_ar: "فرن حثي حراري مخصص لصهر المعادن", cat_en: "Factory", cat_ar: "المصنع" }
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