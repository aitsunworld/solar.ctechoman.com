<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
  mb_internal_encoding('UTF-8');
}

session_start();

// Handle language toggle
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
  $_SESSION['lang'] = $_GET['lang'];
}

$active_lang = $_SESSION['lang'] ?? 'en';
$dir = ($active_lang === 'ar') ? 'rtl' : 'ltr';

// Load constants
$constants = require_once "constants.php";

// Load language dictionary
$lang = require_once "lang/{$active_lang}.php";
?>
<!DOCTYPE html>
<html lang="<?= $active_lang ?>" dir="<?= $dir ?>">

<head>
  <!-- JS class signal FIRST for no-FOUC -->
  <script>document.documentElement.classList.add('js-enabled');</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $lang['title'] ?></title>
  <meta name="description" content="<?= $lang['desc'] ?>">
  <meta property="og:title" content="<?= $lang['title'] ?>">
  <meta property="og:description" content="<?= $lang['og_desc'] ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://solar.ctechoman.com">
  <meta property="og:image" content="https://www.ctechoman.com/public/logo.webp">

  <!-- Favicons -->
  <link rel="shortcut icon" href="https://www.ctechoman.com/public/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" sizes="180x180" href="https://www.ctechoman.com/public/apple-touch-icon-114x114-precomposed.png">

  <!-- DNS Prefetch for external domains -->
  <link rel="dns-prefetch" href="https://fonts.googleapis.com">
  <link rel="dns-prefetch" href="https://fonts.gstatic.com">
  <link rel="dns-prefetch" href="https://images.unsplash.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Preload LCP hero image and nav logo -->
  <link rel="preload" as="image" href="lightbulb.png" fetchpriority="high">
  <link rel="preload" as="image" href="https://www.ctechoman.com/public/logo.webp">

<!-- CRITICAL INLINE CSS: Only what's needed for above-fold render -->
   <style>
     *{margin:0;padding:0;box-sizing:border-box}
     html{scroll-behavior:smooth;overflow-x:hidden;width:100%}
     body{font-family:'Outfit','Tajawal','Noto Sans Arabic','Segoe UI',Arial,sans-serif;color:#0d1014;background-color:#FAFAFA;line-height:1.6;overflow-x:hidden;width:100%;font-size:.95rem}
     img{max-width:100%;height:auto;display:block}
     .navbar{position:fixed;top:0;left:0;width:100%;z-index:1000;padding:.75rem 0;background:rgba(255,255,255,.97);border-bottom:1px solid rgba(0,0,0,.05);box-shadow:0 1px 6px rgba(0,0,0,.06)}
     .nav-container{display:flex;justify-content:space-between;align-items:center}
     .container{width:100%;max-width:1300px;margin:0 auto;padding-left:1rem;padding-right:1rem}
     .logo img{height:32px;width:auto}
     .mobile-menu-btn{display:flex;flex-direction:column;justify-content:space-between;width:30px;height:20px;background:none;border:none;cursor:pointer;z-index:1001}
     .mobile-menu-btn span{display:block;width:100%;height:3px;background:#0d1014;border-radius:2px}
     .nav-links{position:fixed;top:60px;left:-100%;width:100%;height:calc(100vh - 60px);background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:3rem 2rem;gap:2rem;transition:left .3s ease-in-out;overflow-y:auto}
     .nav-links.active{left:0}
     [dir=rtl] .nav-links{left:auto;right:-100%;transition:right .3s ease-in-out}
     [dir=rtl] .nav-links.active{right:0}
     .nav-link{color:#0d1014;text-decoration:none;font-weight:700;font-size:1.1rem;width:100%;text-align:center;padding:.75rem 0;border-bottom:1px solid #E2E8F0}
     .lang-btn{background:#F1F5F9;border:none;color:#64748B;cursor:pointer;font-weight:700;font-size:1rem;padding:.5rem 1.5rem;border-radius:100px}
     .btn{display:inline-flex;align-items:center;justify-content:center;padding:.85rem 2rem;font-weight:700;border-radius:100px;text-decoration:none;cursor:pointer;border:none;font-size:.95rem;text-align:center;min-height:48px;width:100%}
     .btn-primary{background:#0d1014;color:#fff}
     .btn-hero-primary{background:#3a8dcc;color:#fff}
     .hero{min-height:auto;padding-top:80px;padding-bottom:2rem;display:flex;align-items:center}
     .hero-grid{display:flex;flex-direction:column;gap:1.5rem;width:100%}
     .hero-text{text-align:center;opacity:1}
     h1{font-size:1.85rem;font-weight:900;line-height:1.2;color:#0d1014}
     h2{font-size:1.45rem;font-weight:900;line-height:1.2;color:#0d1014}
     h3{font-size:1.2rem;font-weight:900;line-height:1.2;color:#0d1014}
     h4{font-size:1.05rem;font-weight:900;line-height:1.2;color:#0d1014}
     .hero-text p{font-size:1rem;color:#64748B;margin-bottom:1.5rem;font-weight:500}
.hero-actions{display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;align-items:center;width:100%}
      .hero-visual{width:100%;height:180px;display:flex;justify-content:flex-start;align-items:center;position:relative;overflow:hidden;opacity:1}
      .lightbulb-img{max-height:160px;width:auto;}
      .text-muted{color:#64748B}
      .bg-blobs{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
      .blob-1,.blob-2{position:absolute;width:200px;height:200px;background:#F1F5F9;border-radius:40% 60% 70% 30%/40% 50% 60% 50%;opacity:.2}
      .blob-1{top:-10%;left:-10%}.blob-2{bottom:10%;right:-5%}
      /* Prevent FOUC: keep hero visible until styles load */
      .js-enabled .hero-text,.js-enabled .hero-visual{opacity:1}
      /* Reveal elements - hidden until active */
      .js-enabled .reveal{opacity:0;transform:translate3d(0,15px,0)}
      .js-enabled .reveal.active{opacity:1;transform:translate3d(0,0,0)}
    </style>

  <!-- Non-blocking Google Fonts (avoids render-blocking) -->
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap"></noscript>

  <!-- Main stylesheet: load non-blocking, apply after fonts -->
  <link rel="preload" as="style" href="style.css?v=4.0" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="style.css?v=4.0"></noscript>
  <!-- chatbot.css injected lazily by JS below, not here -->
</head>

<body>

  <!-- Organic Background Blobs -->
  <div class="bg-blobs">
    <div class="blob-1"></div>
    <div class="blob-2"></div>
  </div>

  <!-- Navigation -->
  <nav class="navbar" id="navbar">
    <div class="container nav-container">
      <a href="index.php" class="logo">
        <img src="https://www.ctechoman.com/public/logo.webp" alt="Concept Technologies LLC Logo" width="160" height="48" fetchpriority="high" loading="eager" decoding="async">
      </a>
      <div class="nav-links">
        <a href="#calculator" class="nav-link"><?= $lang['nav_calculator'] ?></a>
        <a href="#benefits" class="nav-link"><?= $lang['nav_benefits'] ?></a>
        <a href="#process" class="nav-link"><?= $lang['nav_process'] ?></a>
        <?php if ($active_lang === 'en'): ?>
          <a href="?lang=ar" class="lang-btn" style="text-decoration:none;">العربية</a>
        <?php else: ?>
          <a href="?lang=en" class="lang-btn" style="text-decoration:none;">English</a>
        <?php endif; ?>
        <a href="#contact" class="btn btn-primary"><?= $lang['nav_quote'] ?></a>
      </div>

      <!-- Mobile Menu Toggle -->
      <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </nav>

  <main id="main-content">

  <!-- Hero Section -->
  <section class="hero container">
    <div class="hero-grid">
      <div class="hero-text">
        <h1><?= $lang['hero_title'] ?></h1>
        <p style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-top: 1rem; margin-bottom: 1rem; text-align: inherit;"><?= $lang['hero_desc'] ?></p>
        <p style="font-size: 1rem; color: var(--color-text-muted); margin-bottom: 2rem; text-align: inherit;"><?= $lang['hero_support'] ?></p>

        <div class="hero-actions">
          <a href="#calculator" class="btn btn-hero-primary"><?= $active_lang === 'ar' ? 'ابدأ بحساب استهلاكك 🚀' : 'Start Sizing Now 🚀' ?></a>
        </div>

        <div class="hero-slider-nav">
          <div class="slider-item active" data-slide-index="0">
            <span class="slider-num">1.</span>
            <div class="slider-line"></div>
          </div>
          <div class="slider-item" data-slide-index="1">
            <span class="slider-num">2.</span>
            <div class="slider-line"></div>
          </div>
          <div class="slider-item" data-slide-index="2">
            <span class="slider-num">3.</span>
            <div class="slider-line"></div>
          </div>
        </div>
      </div>

      <div class="hero-visual" id="hero-slider-visual">
        <div class="hero-slider-track" id="hero-slider-track">
          <div class="hero-slide">
            <img src="lightbulb.png" alt="Renewable Energy" class="lightbulb-img" width="500" height="500"
              fetchpriority="high" loading="eager" decoding="async" style="mix-blend-mode: multiply;">
          </div>
          <div class="hero-slide">
            <img src="hero-villa.png" alt="Residential Villa Solar Panels" class="lightbulb-img" width="500" height="500"
              loading="lazy" decoding="async" style="mix-blend-mode: multiply;">
          </div>
          <div class="hero-slide">
            <img src="hero-commercial.png" alt="Commercial Industrial Solar Panels" class="lightbulb-img" width="500" height="500"
              loading="lazy" decoding="async" style="mix-blend-mode: multiply;">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Solar Calculator -->
  <section id="calculator" class="calculator-section container reveal">
    <div class="wizard-container">
      
      <!-- Wizard Progress Header -->
      <div class="wizard-progress-bar-container">
        <!-- Desktop progress line and nodes -->
        <div class="wizard-steps-indicators">
          <div class="wizard-indicator active" data-step="1">
            <span class="step-num">1</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'العقار' : 'Type' ?></span>
          </div>
          <div class="wizard-indicator" data-step="2">
            <span class="step-num">2</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'الموقع' : 'Location' ?></span>
          </div>
          <div class="wizard-indicator" data-step="3">
            <span class="step-num">3</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'الأجهزة' : 'Appliances' ?></span>
          </div>
          <div class="wizard-indicator" data-step="4">
            <span class="step-num">4</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'الاستهلاك' : 'Load' ?></span>
          </div>
          <div class="wizard-indicator" data-step="5">
            <span class="step-num">5</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'الفاتورة' : 'Bill' ?></span>
          </div>
          <div class="wizard-indicator" data-step="6">
            <span class="step-num">6</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'التوصية' : 'System' ?></span>
          </div>
          <div class="wizard-indicator" data-step="7">
            <span class="step-num">7</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'لوحة التحكم' : 'Savings' ?></span>
          </div>
          <div class="wizard-indicator" data-step="8">
            <span class="step-num">8</span>
            <span class="step-label"><?= $active_lang === 'ar' ? 'التأكيد' : 'Contact' ?></span>
          </div>
        </div>
        
        <!-- Mobile active step status -->
        <div class="wizard-mobile-status">
          <span class="mobile-step-text"><?= $active_lang === 'ar' ? 'الخطوة 1 من 8: نوع العقار' : 'Step 1 of 8: Property Type' ?></span>
          <div class="mobile-progress-line"><div class="mobile-progress-fill" style="width: 12.5%;"></div></div>
        </div>
      </div>

      <!-- Wizard Steps Panels -->
      <div class="wizard-steps-panels">
        
        <!-- Step 1: Property Type -->
        <div class="wizard-step active" id="wizard-step-1">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'اختر نوع العقار الخاص بك' : 'Select Your Property Type' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'تتغير خيارات الأجهزة والحسابات بناءً على نوع العقار.' : 'Calculations and appliance sizers will customize for your property type.' ?></p>
          </div>
          <div class="property-grid">
            <div class="property-card active" data-value="residential">
              <div class="card-icon">🏡</div>
              <h4><?= $active_lang === 'ar' ? 'منزل سكني' : 'Residential Villa' ?></h4>
              <p><?= $active_lang === 'ar' ? 'فلل، شقق، ومنازل سكنية' : 'Villas, townhouses, and residential homes' ?></p>
            </div>
            <div class="property-card" data-value="commercial">
              <div class="card-icon">🏢</div>
              <h4><?= $active_lang === 'ar' ? 'مبنى تجاري' : 'Commercial Business' ?></h4>
              <p><?= $active_lang === 'ar' ? 'مكاتب، معارض، ومحلات تجارية' : 'Offices, retail spaces, and clinics' ?></p>
            </div>
            <div class="property-card" data-value="industrial">
              <div class="card-icon">🏭</div>
              <h4><?= $active_lang === 'ar' ? 'منشأة صناعية' : 'Industrial Facility' ?></h4>
              <p><?= $active_lang === 'ar' ? 'مصانع، مستودعات، وورش كبرى' : 'Factories, cold storage, and warehouses' ?></p>
            </div>
          </div>
          <!-- Hidden select to bind to legacy script -->
          <div style="display: none;">
            <select id="property-type">
              <option value="residential" selected>Residential</option>
              <option value="commercial">Commercial</option>
              <option value="industrial">Industrial</option>
            </select>
          </div>
        </div>

        <!-- Step 2: Location -->
        <div class="wizard-step" id="wizard-step-2">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'أين يقع عقارك في سلطنة عُمان؟' : 'Where is Your Property Located?' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'نحتاج لتحديد الموقع الجغرافي لحساب معدل سطوع الشمس بدقة.' : 'We use solar irradiation yield data specific to Omani governorates.' ?></p>
          </div>
          <div class="location-grid">
            <div class="location-card active" data-value="muscat">
              <div class="location-icon">📍</div>
              <h4><?= $lang['calc_loc_muscat'] ?></h4>
              <span class="yield-badge"><?= $active_lang === 'ar' ? 'إنتاجية 1700' : '1700 kWh/kW' ?></span>
            </div>
            <div class="location-card" data-value="batinah">
              <div class="location-icon">📍</div>
              <h4><?= $lang['calc_loc_batinah'] ?></h4>
              <span class="yield-badge"><?= $active_lang === 'ar' ? 'إنتاجية 1700' : '1700 kWh/kW' ?></span>
            </div>
            <div class="location-card" data-value="dhofar">
              <div class="location-icon">📍</div>
              <h4><?= $lang['calc_loc_dhofar'] ?></h4>
              <span class="yield-badge"><?= $active_lang === 'ar' ? 'إنتاجية 1445' : '1445 kWh/kW' ?></span>
            </div>
            <div class="location-card" data-value="dakhiliyah">
              <div class="location-icon">📍</div>
              <h4><?= $active_lang === 'ar' ? 'الداخلية' : 'Al Dakhiliyah' ?></h4>
              <span class="yield-badge"><?= $active_lang === 'ar' ? 'إنتاجية 1750' : '1750 kWh/kW' ?></span>
            </div>
            <div class="location-card" data-value="other">
              <div class="location-icon">📍</div>
              <h4><?= $lang['calc_loc_other'] ?></h4>
              <span class="yield-badge"><?= $active_lang === 'ar' ? 'إنتاجية 1650' : '1650 kWh/kW' ?></span>
            </div>
          </div>
          <!-- Hidden select to bind to legacy script -->
          <div style="display: none;">
            <select id="location">
              <option value="muscat" selected>Muscat</option>
              <option value="batinah">Al Batinah</option>
              <option value="dhofar">Dhofar</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <!-- Step 3: Appliance Selection -->
        <div class="wizard-step" id="wizard-step-3">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'اختر الأجهزة المستعملة ومعدل الاستخدام' : 'Select Appliances & Daily Usage' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'أضف الأجهزة التي تستخدمها بانتظام لتوليد تقدير دقيق للاستهلاك.' : 'Add quantity and customize usage for your core appliances to estimate energy load.' ?></p>
          </div>
          
          <div id="appliance-inputs-container">
            <!-- Dynamically populated by JS (initApplianceSizer) -->
          </div>
        </div>

        <!-- Step 4: Consumption Analysis -->
        <div class="wizard-step" id="wizard-step-4">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'تحليل استهلاك الطاقة والتحميل' : 'Energy Load & Consumption Analysis' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'إليك تفاصيل الاحتياجات الكهربائية لعقارك بناءً على الأجهزة المحددة.' : 'Here is the electrical demand profile calculated from your appliance choices.' ?></p>
          </div>
          
          <div class="analysis-grid">
            <div class="analysis-stats">
              <div class="stat-box">
                <span class="stat-label"><?= $active_lang === 'ar' ? 'إجمالي الحمل المتصل' : 'Total Connected Load' ?></span>
                <strong class="stat-value" id="res-connected-load">0 kW</strong>
                <span class="stat-sub"><?= $active_lang === 'ar' ? 'أقصى طاقة استيعابية متزامنة' : 'Peak power demand capacity' ?></span>
              </div>
              <div class="stat-box">
                <span class="stat-label"><?= $active_lang === 'ar' ? 'الاستهلاك اليومي المقدر' : 'Estimated Daily Consumption' ?></span>
                <strong class="stat-value" id="res-daily-consumption">0 kWh/day</strong>
                <span class="stat-sub"><?= $active_lang === 'ar' ? 'معدل استهلاك الطاقة اليومي' : 'Average daily energy usage' ?></span>
              </div>
              <div class="stat-box">
                <span class="stat-label"><?= $active_lang === 'ar' ? 'الاستهلاك الشهري المقدر' : 'Estimated Monthly Consumption' ?></span>
                <strong class="stat-value" id="res-monthly-consumption">0 kWh/month</strong>
                <span class="stat-sub"><?= $active_lang === 'ar' ? 'استهلاك الطاقة خلال 30 يوماً' : 'Estimated usage over 30 days' ?></span>
              </div>
            </div>
            
            <div class="category-breakdown-card">
              <h4><?= $active_lang === 'ar' ? 'توزيع الاستهلاك حسب الفئة' : 'Load Distribution by Category' ?></h4>
              <div id="category-chart-bars" class="chart-bars-container">
                <!-- Populated dynamically in JS -->
              </div>
            </div>
          </div>
        </div>

        <!-- Step 5: Bill Verification -->
        <div class="wizard-step" id="wizard-step-5">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'التحقق من الفاتورة الفعلية' : 'Verify with Actual Electricity Bill' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'مقارنة استهلاك الأجهزة مع فاتورتك الحقيقية لضمان دقة توصية النظام.' : 'Compare sizer consumption with your actual bill to optimize solar recommendations.' ?></p>
          </div>
          
          <div class="bill-verification-wrapper">
            <div class="bill-tabs">
              <button type="button" class="bill-tab active" data-mode="manual"><?= $active_lang === 'ar' ? 'إدخال يدوي للفاتورة' : 'Manual Bill Entry' ?></button>
              <button type="button" class="bill-tab" data-mode="upload"><?= $active_lang === 'ar' ? 'رفع ملف الفاتورة' : 'Upload Digital Bill' ?></button>
            </div>
            
            <!-- Manual Bill Container -->
            <div id="bill-verification-manual" class="bill-panel active">
              <h4><?= $active_lang === 'ar' ? 'كم تبلغ فاتورة الكهرباء الشهرية المعتادة؟' : 'What is your average monthly electricity bill?' ?></h4>
              <div class="slider-container mt-4">
                <input type="range" id="bill-slider" min="10" max="1000" value="50" step="5" aria-label="Monthly Bill Slider">
                <div class="slider-value-display">
                  <span id="bill-display">50</span> <span class="currency">OMR</span>
                </div>
              </div>
            </div>
            
            <!-- Upload Bill Container -->
            <div id="bill-verification-upload" class="bill-panel">
              <div class="bill-upload-zone" id="bill-upload-zone">
                <div class="upload-icon">📄</div>
                <p class="upload-text"><?= $active_lang === 'ar' ? 'قم بسحب وإفلات فاتورة الكهرباء هنا (PDF أو صورة)' : 'Drag and drop your electricity bill here (PDF/Image)' ?></p>
                <span class="upload-or"><?= $active_lang === 'ar' ? 'أو' : 'OR' ?></span>
                <button type="button" class="btn btn-secondary btn-upload-file"><?= $active_lang === 'ar' ? 'تصفح الملفات' : 'Browse Files' ?></button>
                <input type="file" id="bill-file-input" accept="image/*,application/pdf" style="display: none;">
              </div>
              <div id="upload-status" class="upload-status" style="display: none;">
                <div class="spinner-circle"></div>
                <p id="upload-status-text"><?= $active_lang === 'ar' ? 'جاري قراءة الفاتورة وتحليل الاستهلاك...' : 'Analyzing bill statement metrics...' ?></p>
              </div>
            </div>
            
            <!-- Variance Warning Banner -->
            <div id="bill-variance-warning" class="variance-alert-banner" style="display: none;">
              <span class="alert-icon">⚠️</span>
              <div class="alert-body">
                <h5 id="variance-title"><?= $active_lang === 'ar' ? 'تم اكتشاف تباين في الاستهلاك' : 'Consumption Variance Detected' ?></h5>
                <p id="variance-desc"></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 6: Solar Recommendation -->
        <div class="wizard-step" id="wizard-step-6">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'نظام الطاقة الشمسية المقترح لعقارك' : 'Your Recommended Solar System' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'تم حساب سعة النظام المثالية لتغطية استهلاكك بنسبة تصل إلى 100٪.' : 'Optimal system dimensioning to cover up to 100% of your energy footprint.' ?></p>
          </div>
          
          <div class="recommendations-grid">
            <div class="recs-card main-recs">
              <div class="recs-icon">☀️</div>
              <div class="recs-info-text">
                <span class="recs-label"><?= $lang['calc_sys_size'] ?></span>
                <strong class="recs-value text-eco" id="res-size">0 kW</strong>
                <span class="recs-desc"><?= $active_lang === 'ar' ? 'السعة الكلية المقترحة للألواح' : 'Recommended peak solar capacity' ?></span>
              </div>
            </div>
            
            <div class="recs-card">
              <div class="recs-icon">⚙️</div>
              <div class="recs-info-text">
                <span class="recs-label"><?= $lang['calc_est_panels'] ?></span>
                <strong class="recs-value" id="res-panels">0</strong>
                <span class="recs-desc"><?= $active_lang === 'ar' ? 'ألواح بقدرة 550 واط' : 'Premium 550W mono-PV panels' ?></span>
              </div>
            </div>

            <div class="recs-card">
              <div class="recs-icon">📐</div>
              <div class="recs-info-text">
                <span class="recs-label"><?= $active_lang === 'ar' ? 'مساحة السقف المطلوبة' : 'Roof Space Required' ?></span>
                <strong class="recs-value" id="res-space">0 sqm</strong>
                <span class="recs-desc"><?= $active_lang === 'ar' ? 'المساحة الصافية للألواح' : 'Unshaded roof area needed' ?></span>
              </div>
            </div>

            <div class="recs-card">
              <div class="recs-icon">🔌</div>
              <div class="recs-info-text">
                <span class="recs-label"><?= $active_lang === 'ar' ? 'حجم العاكس الذكي' : 'Recommended Inverter' ?></span>
                <strong class="recs-value" id="res-inverter">0 kW</strong>
                <span class="recs-desc"><?= $active_lang === 'ar' ? 'عاكس DCRP معتمد' : 'Oman DCRP approved string inverter' ?></span>
              </div>
            </div>

            <div class="recs-card">
              <div class="recs-icon">🔋</div>
              <div class="recs-info-text">
                <span class="recs-label"><?= $active_lang === 'ar' ? 'سعة تخزين البطارية' : 'Battery Storage' ?></span>
                <strong class="recs-value text-eco" id="res-battery">0 kWh</strong>
                <span class="recs-desc"><?= $active_lang === 'ar' ? 'لتخزين الكهرباء الاحتياطية' : 'Backup battery capacity (optional)' ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 7: Savings Dashboard & Gamification -->
        <div class="wizard-step" id="wizard-step-7">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'لوحة تحكم الأرباح والأثر البيئي' : 'Savings & Environmental Impact Dashboard' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'توفير مالي مضمون واستدامة بيئية حقيقية لعقارك في عمان.' : 'Financial freedom and sustainable footprint for your Oman property.' ?></p>
          </div>
          
          <div class="dashboard-grid">
            <!-- Left panel: Financial metrics -->
            <div class="dashboard-card financials">
              <h4><?= $active_lang === 'ar' ? 'الجدوى الاقتصادية للمشروع' : 'Financial Viability' ?></h4>
              <div class="financials-grid">
                <div class="fin-box highlight">
                  <span class="fin-label"><?= $lang['calc_yearly_savings'] ?></span>
                  <strong class="fin-value" id="res-savings">0 OMR</strong>
                </div>
                <div class="fin-box">
                  <span class="fin-label"><?= $lang['calc_est_cost'] ?></span>
                  <strong class="fin-value" id="res-cost">0 OMR</strong>
                </div>
                <div class="fin-box">
                  <span class="fin-label"><?= $active_lang === 'ar' ? 'فترة استرداد رأس المال' : 'Payback Period' ?></span>
                  <strong class="fin-value" id="res-payback">0 Years</strong>
                </div>
                <div class="fin-box">
                  <span class="fin-label"><?= $active_lang === 'ar' ? 'العائد على الاستثمار' : 'Project ROI' ?></span>
                  <strong class="fin-value" id="res-roi">0%</strong>
                </div>
              </div>
              
              <!-- 25 Years Cumulative Savings Meter -->
              <div class="savings-meter-wrapper mt-4">
                <div class="savings-meter-header">
                  <span><?= $active_lang === 'ar' ? 'التوفير التراكمي خلال 25 سنة' : '25-Year Cumulative Savings' ?></span>
                  <strong id="res-cumulative-savings">0 OMR</strong>
                </div>
                <div class="savings-meter-bar"><div class="savings-meter-fill" id="res-savings-fill" style="width: 100%;"></div></div>
                <span class="savings-meter-note"><?= $active_lang === 'ar' ? '💡 العمر الافتراضي للألواح يصل إلى 25 سنة كأصل مدر للأرباح.' : '💡 Solar panels operate as a cash-generating asset for 25+ years.' ?></span>
              </div>
            </div>
            
            <!-- Right panel: Gamification widgets -->
            <div class="dashboard-card environment">
              <h4><?= $active_lang === 'ar' ? 'الأثر البيئي والتقييم الذكي' : 'Environmental & Suitability Score' ?></h4>
              <div class="gamified-widgets">
                <div class="gamified-widget suitability">
                  <div class="widget-icon">⭐</div>
                  <div class="widget-body">
                    <span class="widget-label"><?= $active_lang === 'ar' ? 'معدل ملاءمة العقار للطاقة الشمسية' : 'Solar Suitability Score' ?></span>
                    <strong class="widget-value" id="res-suitability-score">0/100</strong>
                  </div>
                </div>
                <div class="gamified-widget independence">
                  <div class="widget-icon">⚡</div>
                  <div class="widget-body">
                    <span class="widget-label"><?= $active_lang === 'ar' ? 'معدل الاستقلال عن الشبكة' : 'Energy Independence Score' ?></span>
                    <strong class="widget-value" id="res-energy-independence">0%</strong>
                  </div>
                </div>
                <div class="gamified-widget co2">
                  <div class="widget-icon">🌱</div>
                  <div class="widget-body">
                    <span class="widget-label"><?= $active_lang === 'ar' ? 'خفض انبعاثات الكربون' : 'Carbon Reduction (CO₂)' ?></span>
                    <strong class="widget-value" id="res-co2-offset">0 Tons/yr</strong>
                  </div>
                </div>
                <div class="gamified-widget trees">
                  <div class="widget-icon">🌳</div>
                  <div class="widget-body">
                    <span class="widget-label"><?= $active_lang === 'ar' ? 'يعادل زراعة أشجار' : 'Equivalent Trees Planted' ?></span>
                    <strong class="widget-value" id="res-trees-offset">0 Trees</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="text-center mt-3">
            <button id="calc-explain-btn" class="btn btn-secondary">
              ✨ <?= $active_lang === 'ar' ? 'شرح تفصيلي للنتائج بالمستشار الذكي' : 'Explain results with AI Advisor' ?>
            </button>
          </div>
        </div>

        <!-- Step 8: Lead Capture Form -->
        <div class="wizard-step" id="wizard-step-8">
          <div class="step-header">
            <h3><?= $active_lang === 'ar' ? 'طلب استشارة وتأكيد العرض المالي' : 'Confirm & Book Free Survey' ?></h3>
            <p class="text-muted"><?= $active_lang === 'ar' ? 'احصل على مخطط هندسي مجاني لمنزلك وتأكيد التكلفة من مهندسينا.' : 'Request a free technical survey and custom engineering design from our team.' ?></p>
          </div>
          
          <div class="lead-form-wrapper">
            <form id="native-lead-form" method="POST">
              <input type="hidden" name="action" value="submit_lead">
              <input type="hidden" name="lang" value="<?= $active_lang ?>">
              
              <div class="form-group">
                <label for="lead-name"><?= $active_lang === 'ar' ? 'الاسم الكامل' : 'Full Name' ?> *</label>
                <input type="text" id="lead-name" name="name" required placeholder="<?= $active_lang === 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' ?>" aria-label="Full Name">
              </div>

              <div class="form-row-grid">
                <div class="form-group">
                  <label for="lead-phone"><?= $active_lang === 'ar' ? 'رقم الهاتف (واتساب)' : 'Phone Number (WhatsApp)' ?> *</label>
                  <input type="tel" id="lead-phone" name="phone" required placeholder="968 XXXXXXXX" aria-label="Phone Number">
                </div>
                <div class="form-group">
                  <label for="lead-email"><?= $active_lang === 'ar' ? 'البريد الإلكتروني' : 'Email Address' ?> *</label>
                  <input type="email" id="lead-email" name="email" required placeholder="example@domain.com" aria-label="Email Address">
                </div>
              </div>

              <div class="form-row-grid" style="display: none;">
                <!-- Sync selections automatically from step cards -->
                <div class="form-group">
                  <select id="lead-gov" name="governorate" aria-label="Governorate">
                    <option value="muscat">Muscat</option>
                    <option value="dhofar">Dhofar</option>
                    <option value="batinah">Al Batinah</option>
                    <option value="dakhiliyah">Dakhiliyah</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <select id="lead-prop" name="property_type" aria-label="Property Type">
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="industrial">Industrial</option>
                  </select>
                </div>
              </div>

              <div class="form-row-grid">
                <div class="form-group">
                  <label for="lead-bill"><?= $active_lang === 'ar' ? 'متوسط الفاتورة الكهربائية (ريال)' : 'Average Electricity Bill (OMR)' ?> *</label>
                  <input type="number" id="lead-bill" name="monthly_bill" required min="10" max="5000" value="50" aria-label="Electricity Bill" readonly>
                </div>
                <div class="form-group">
                  <label for="lead-consult"><?= $active_lang === 'ar' ? 'نوع الاستشارة' : 'Consultation Type' ?> *</label>
                  <select id="lead-consult" name="consultation_type" required aria-label="Consultation Type">
                    <option value="site_survey" selected><?= $active_lang === 'ar' ? 'معاينة موقع مجانية' : 'Free Site Survey' ?></option>
                    <option value="video_call"><?= $active_lang === 'ar' ? 'استشارة بالفيديو عن بعد' : 'Online Video Call' ?></option>
                    <option value="office"><?= $active_lang === 'ar' ? 'زيارة مكتب كونسيبت' : 'Office Consultation' ?></option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="lead-notes"><?= $active_lang === 'ar' ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (Optional)' ?></label>
                <textarea id="lead-notes" name="message" rows="3" placeholder="<?= $active_lang === 'ar' ? 'تحدث إلينا عن احتياجاتك المحددة...' : 'Tell us about your specific solar needs...' ?>" aria-label="Additional Notes"></textarea>
              </div>

              <!-- Anti-Spam HoneyPot -->
              <div style="display: none;">
                <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
              </div>

              <button type="submit" class="btn btn-primary submit-btn" style="min-height: 48px; border-radius: 100px; width: 100%;">
                <span><?= $active_lang === 'ar' ? 'تأكيد وحجز الاستشارة 🚀' : 'Confirm & Book Consultation 🚀' ?></span>
                <span class="spinner" style="display: none; width: 20px; height: 20px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite; vertical-align: middle; margin-left: 10px;"></span>
              </button>

              <div id="form-feedback" class="mt-3 text-center" style="display: none;"></div>
            </form>
          </div>
        </div>

      </div>

      <!-- Wizard Navigation Footer Controls -->
      <div class="wizard-navigation-controls" style="display: flex; justify-content: space-between; gap: 1rem; margin-top: 2rem;">
        <button type="button" id="wizard-back-btn" class="btn btn-secondary" style="display: none; flex: 1; max-width: 200px;">
          <?= $active_lang === 'ar' ? '← السابق' : '← Back' ?>
        </button>
        <button type="button" id="wizard-next-btn" class="btn btn-primary" style="flex: 1; max-width: 200px; margin-left: auto;">
          <?= $active_lang === 'ar' ? 'التالي 🚀' : 'Next 🚀' ?>
        </button>
      </div>

    </div>
  </section>
  </section>

  <!-- Benefits -->
  <section id="benefits" class="benefits container section-padding">
    <div class="section-title reveal">
      <h2><?= $lang['ben_title'] ?></h2>
      <p class="section-subtitle"><?= $lang['ben_desc'] ?></p>
    </div>

    <div class="staggered-grid">
      <div class="benefit-card reveal">
        <div class="icon-blob">💰</div>
        <h3><?= $lang['ben_1_title'] ?></h3>
        <p class="text-muted"><?= $lang['ben_1_desc'] ?></p>
      </div>

      <div class="benefit-card reveal delay-100">
        <div class="icon-blob" style="background: #E0F2FE; color: #3a8dcc;">🌍</div>
        <h3><?= $lang['ben_2_title'] ?></h3>
        <p class="text-muted"><?= $lang['ben_2_desc'] ?></p>
      </div>

      <div class="benefit-card reveal delay-200">
        <div class="icon-blob" style="background: #F1F5F9; color: #444444;">📈</div>
        <h3><?= $lang['ben_3_title'] ?></h3>
        <p class="text-muted"><?= $lang['ben_3_desc'] ?></p>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="container section-padding">
    <div class="cta-banner reveal">
      <div class="cta-content">
        <span class="cta-pre"><?= $lang['cta_pre'] ?></span>
        <h2><?= $lang['cta_title'] ?></h2>
        <div class="cta-actions">
          <a href="#contact" class="btn btn-hero-primary"><?= $lang['cta_btn'] ?></a>
          <a href="tel:<?= $constants['phone_2'] ?>" class="cta-hotline">
            <span class="hotline-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                </path>
              </svg>
            </span>
            <?= $constants['phone_2'] ?>
          </a>
        </div>
      </div>
      <div class="cta-visual">
        <img src="img2.webp" alt="Solar Installation" width="480" height="360" loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <!-- Gallery -->
  <section id="projects" class="container section-padding gallery-section">
    <div class="gallery-header reveal">
      <div class="gallery-title-area">
        <span class="gallery-pre"><span class="pre-icon">❖</span> <?= $lang['gal_pre'] ?> <span
            class="dot"></span></span>
        <h2><?= $lang['gal_title'] ?></h2>
        <p class="text-muted"><?= $lang['gal_desc'] ?></p>
      </div>
      <a href="#contact" class="btn btn-dark-green"><?= $lang['gal_btn'] ?></a>
    </div>

    <div class="gallery-grid reveal delay-100">
      <div class="gallery-col">
        <div class="gallery-item tall">
          <img
            src="https://images.unsplash.com/photo-1509391366360-2e959784a276?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=70&fm=webp"
            alt="Solar Project 1" width="400" height="534" loading="lazy" decoding="async">
        </div>
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1592833159155-c62df1b65634?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=70&fm=webp"
            alt="Solar Project 2" width="400" height="267" loading="lazy" decoding="async">
        </div>
      </div>
      <div class="gallery-col">
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=70&fm=webp"
            alt="Solar Project 3" width="400" height="267" loading="lazy" decoding="async">
        </div>
        <div class="gallery-item tall">
          <img
            src="https://plus.unsplash.com/premium_photo-1679917152396-4b18accacb9d?q=70&w=400&fm=webp&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="Solar Project 4" width="400" height="534" loading="lazy" decoding="async">
        </div>
      </div>
      <div class="gallery-col">
        <div class="gallery-item tall">
          <img
            src="https://plus.unsplash.com/premium_photo-1682148205811-e8a8ce759f4b?q=70&w=400&fm=webp&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="Solar Project 5" width="400" height="534" loading="lazy" decoding="async">
        </div>
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1613665813446-82a78c468a1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=70&fm=webp"
            alt="Solar Project 6" width="400" height="267" loading="lazy" decoding="async">
        </div>
      </div>
    </div>
  </section>

  <!-- Why Choose Us -->
  <section id="why-choose-us" class="container section-padding">
    <div class="section-title reveal">
      <span class="section-eyebrow"><?= $lang['why_pre'] ?></span>
      <h2><?= $lang['why_title'] ?></h2>
    </div>

    <div class="choose-us-grid">
      <div class="choose-col left-col">
        <div class="choose-item reveal delay-100">
          <div class="choose-text">
            <h3><?= $lang['why_1_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_1_desc'] ?></p>
          </div>
          <div class="choose-icon">🏅</div>
        </div>
        <div class="choose-item reveal delay-200">
          <div class="choose-text">
            <h3><?= $lang['why_2_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_2_desc'] ?></p>
          </div>
          <div class="choose-icon">🏆</div>
        </div>
        <div class="choose-item reveal delay-300">
          <div class="choose-text">
            <h3><?= $lang['why_3_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_3_desc'] ?></p>
          </div>
          <div class="choose-icon">⚡</div>
        </div>
      </div>

      <div class="choose-center reveal" style="position: relative;">
        <img src="solar_panel.webp" alt="Solar Panel" width="300" height="338" loading="lazy" decoding="async"
          style="width: 100%; max-width: 300px; position: relative; z-index: 2;">

        <img src="cloud1.webp" class="cloud-anim cloud-1" alt="" role="presentation" width="120" height="60" loading="lazy">
        <img src="cloud2.webp" class="cloud-anim cloud-2" alt="" role="presentation" width="120" height="60" loading="lazy">
      </div>

      <div class="choose-col right-col">
        <div class="choose-item reveal delay-100">
          <div class="choose-icon">☀️</div>
          <div class="choose-text">
            <h3><?= $lang['why_4_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_4_desc'] ?></p>
          </div>
        </div>
        <div class="choose-item reveal delay-200">
          <div class="choose-icon">👨‍🔬</div>
          <div class="choose-text">
            <h3><?= $lang['why_5_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_5_desc'] ?></p>
          </div>
        </div>
        <div class="choose-item reveal delay-300">
          <div class="choose-icon">🇴🇲</div>
          <div class="choose-text">
            <h3><?= $lang['why_6_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_6_desc'] ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Trusted Brands & Products -->
  <section id="partners" class="partners-section text-center reveal" style="text-align: center;">
    <div class="logo-wall-section">
      <div class="logo-wall-header">
        <span class="logo-wall-eyebrow"><?= $lang['why_pre'] ?></span>
        <h2 class="logo-wall-title"><?= $lang['brands_title'] ?></h2>
        <p class="logo-wall-subtitle"><?= $lang['brands_subtitle'] ?></p>
      </div>

      <!-- Row 1: scrolls LEFT -->
      <div class="logo-row-wrapper">
        <div class="logo-row logo-row--left">
          <?php for ($i = 0; $i < 2; $i++): ?>
            <div class="logo-item"><img src="brands/huawei.svg" alt="Huawei" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/sungrow.png" alt="Sungrow" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/solis.png" alt="Solis" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/deye.png" alt="Deye" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/canadian_solar.png" alt="Canadian Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/longi.svg" alt="LONGi" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jinko_solar.png" alt="Jinko Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/ja_solar.svg" alt="JA Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/power_sun.png" alt="Power &amp; Sun" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/trina_solar.svg" alt="Trina Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jebel.png" alt="Jebel" width="120" height="38" loading="lazy"></div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Row 2: scrolls RIGHT -->
      <div class="logo-row-wrapper">
        <div class="logo-row logo-row--right">
          <?php for ($i = 0; $i < 2; $i++): ?>
            <div class="logo-item"><img src="brands/trina_solar.svg" alt="Trina Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/ja_solar.svg" alt="JA Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jebel.png" alt="Jebel" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jinko_solar.png" alt="Jinko Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/longi.svg" alt="LONGi" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/huawei.svg" alt="Huawei" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/deye.png" alt="Deye" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/power_sun.png" alt="Power &amp; Sun" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/canadian_solar.png" alt="Canadian Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/sungrow.png" alt="Sungrow" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/solis.png" alt="Solis" width="120" height="38" loading="lazy"></div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Row 3: scrolls LEFT -->
      <div class="logo-row-wrapper">
        <div class="logo-row logo-row--left logo-row--slow">
          <?php for ($i = 0; $i < 2; $i++): ?>
            <div class="logo-item"><img src="brands/solis.png" alt="Solis" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/power_sun.png" alt="Power &amp; Sun" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/canadian_solar.png" alt="Canadian Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/huawei.svg" alt="Huawei" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/trina_solar.svg" alt="Trina Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jebel.png" alt="Jebel" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/sungrow.png" alt="Sungrow" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/deye.png" alt="Deye" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/longi.svg" alt="LONGi" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/ja_solar.svg" alt="JA Solar" width="120" height="38" loading="lazy"></div>
            <div class="logo-item"><img src="brands/jinko_solar.png" alt="Jinko Solar" width="120" height="38" loading="lazy"></div>
          <?php endfor; ?>
        </div>
      </div>

    </div>
  </section>

  <!-- Technical Resources / Datasheet Download Center -->
  <section id="datasheets" class="datasheet-section container reveal section-padding">
    <div class="section-title reveal">
      <span class="section-eyebrow"><?= $lang['datasheet_pre'] ?></span>
      <h2><?= $lang['datasheet_title'] ?></h2>
      <p class="section-subtitle"><?= $lang['datasheet_subtitle'] ?></p>
    </div>

    <!-- Product Category Switcher Tab Bar -->
    <div class="datasheet-filter-bar reveal">
      <button type="button" class="ds-filter-tab active" data-category="all"><?= $active_lang === 'ar' ? 'الكل' : 'All' ?></button>
      <button type="button" class="ds-filter-tab" data-category="inverter"><?= $active_lang === 'ar' ? 'العواكس' : 'Inverters' ?></button>
      <button type="button" class="ds-filter-tab" data-category="panel"><?= $active_lang === 'ar' ? 'الألواح الشمسية' : 'Solar Panels' ?></button>
      <button type="button" class="ds-filter-tab" data-category="battery"><?= $active_lang === 'ar' ? 'البطاريات' : 'Batteries' ?></button>
      <button type="button" class="ds-filter-tab" data-category="controller"><?= $active_lang === 'ar' ? 'منظمات الشحن' : 'Controllers' ?></button>
    </div>

    <div class="datasheet-grid">

  <!-- Huawei Brand -->
      <div class="datasheet-card brand-card" data-category="inverter" data-brand="huawei">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/huawei.svg" alt="Huawei" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Huawei</h3>
          <p><?= $lang['brand_huawei_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Sungrow Brand -->
      <div class="datasheet-card brand-card" data-category="inverter" data-brand="sungrow">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/sungrow.png" alt="Sungrow" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Sungrow</h3>
          <p><?= $lang['brand_sungrow_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Solis Brand -->
      <div class="datasheet-card brand-card" data-category="inverter battery" data-brand="solis">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/solis.png" alt="Solis" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Solis</h3>
          <p><?= $lang['brand_solis_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Canadian Solar Brand -->
      <div class="datasheet-card brand-card" data-category="inverter panel battery" data-brand="canadian">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/canadian_solar.png" alt="Canadian Solar" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Canadian Solar</h3>
          <p><?= $lang['brand_canadian_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Deye Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="inverter battery" data-brand="deye">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/deye.png" alt="Deye" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Deye</h3>
          <p><?= $lang['brand_deye_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Power & Sun Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="inverter battery" data-brand="powersun">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/power_sun.png" alt="Power & Sun" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Power & Sun</h3>
          <p><?= $lang['brand_powersun_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Trina Solar Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="panel" data-brand="trina">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/trina_solar.svg" alt="Trina Solar" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Trina Solar</h3>
          <p><?= $lang['brand_trina_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- LONGi Solar Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="panel battery" data-brand="longi">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/longi.svg" alt="LONGi" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>LONGi</h3>
          <p><?= $lang['brand_longi_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Jinko Solar Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="panel" data-brand="jinko">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/jinko_solar.png" alt="Jinko Solar" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Jinko Solar</h3>
          <p><?= $lang['brand_jinko_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- JA Solar Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="panel" data-brand="ja">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/ja_solar.svg" alt="JA Solar" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>JA Solar</h3>
          <p><?= $lang['brand_ja_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Jebel Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="battery" data-brand="jebel">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <img src="brands/jebel.png" alt="Jebel" class="brand-logo-img" loading="lazy">
        </div>
        <div class="brand-content">
          <h3>Jebel</h3>
          <p><?= $lang['brand_jebel_tag'] ?></p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

  <!-- Concept Brand -->
      <div class="datasheet-card brand-card extra-brand" style="display: none;" data-category="controller" data-brand="concept">
        <div class="brand-card-accent"></div>
        <div class="brand-logo-wrapper">
          <div class="brand-logo-icon" style="height: 38px; display: flex; align-items: center; justify-content: center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 32px; height: 32px; color: var(--color-primary);">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
            <span style="margin-left: 10px; font-weight: 700; font-size: 1.25rem; color: var(--color-text-dark);">Concept</span>
          </div>
        </div>
        <div class="brand-content">
          <h3>Concept</h3>
          <p>Concept Technologies LLC</p>
        </div>
        <button type="button" class="btn-brand-explore">
          <span><?= $lang['brand_btn_explore'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>

      </div>

      <!-- Centered Glassy Accordion Toggle Button -->
    <div class="brand-toggle-wrapper" style="text-align: center; margin-top: 2rem;">
      <button type="button" id="brand-toggle-btn" class="btn btn-secondary">
        <span><?= $lang['brand_toggle_show'] ?></span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem; transition: transform 0.3s ease;">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
    </div>

    <!-- Premium Brand Explorer Modal -->
    <div id="brand-datasheet-modal" class="ds-modal-overlay" aria-hidden="true" style="display: none;">
      <div class="ds-modal-content">
        <button type="button" class="ds-modal-close" id="ds-modal-close-btn" aria-label="<?= $lang['brand_modal_close'] ?>">&times;</button>
        <div class="ds-modal-header">
          <div class="ds-modal-brand-logo-wrapper" id="modal-logo-container">
            <!-- Brand Logo rendered dynamically -->
          </div>
          <h3 id="modal-brand-title">Brand Products</h3>
          <p id="modal-brand-tagline" class="text-muted"></p>
        </div>
        <div class="ds-modal-body">
          <h4 class="modal-body-title"><?= $lang['brand_modal_title'] ?></h4>
          <div id="modal-products-grid" class="modal-products-grid">
            <!-- Dynamically populated via Javascript -->
          </div>
        </div>
      </div>
    </div>
  </section>

  
    <!-- Embedded PDF Viewer Modal -->
    <div id="pdf-viewer-modal" class="ds-modal-overlay" aria-hidden="true" style="display: none;">
      <div class="ds-modal-content" style="max-width: 900px; height: 90vh; display: flex; flex-direction: column;">
        <button type="button" class="ds-modal-close" id="pdf-modal-close-btn" aria-label="Close">&times;</button>
        <div class="ds-modal-header" style="padding-bottom: 1rem;">
          <h3 id="pdf-modal-title">Product Datasheet</h3>
          <p class="text-muted" id="pdf-modal-brand"></p>
        </div>
        <div class="ds-modal-body" style="flex: 1; padding: 0; position: relative;">
          <iframe id="pdf-viewer-frame" style="width: 100%; height: 100%; border: none; background: #fff;"></iframe>
        </div>
        <div class="pdf-modal-footer" style="padding: 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 1rem;">
          <p style="margin-bottom: 0.5rem; font-weight: 600; color: #1e293b;"><?= $active_lang === 'ar' ? 'هل تود الحصول على أسعار وتفاصيل توفر هذا المنتج؟' : 'Would you like our team to send pricing and availability?' ?></p>
          <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; width: 100%;">
            <a href="#" id="pdf-download-official-btn" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" style="padding: 0.75rem 2rem; min-height: 48px; border: 1.5px solid var(--color-accent); background: transparent; color: var(--color-accent); font-weight: 700; border-radius: var(--radius-pill); text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><?= $active_lang === 'ar' ? 'رابط المواصفات الرسمي ↗' : 'Official Portal ↗' ?></a>
            <button type="button" id="pdf-request-pricing-btn" class="btn btn-primary" style="padding: 0.75rem 2rem;"><?= $active_lang === 'ar' ? 'نعم، اطلب الأسعار' : 'Yes, Request Pricing' ?></button>
          </div>
        </div>
      </div>
    </div>

    <!-- Process -->
  <section id="process" class="container section-padding">
    <div class="section-title text-center reveal" style="margin: 0 auto 4rem auto;">
      <h2><?= $lang['proc_title'] ?></h2>
    </div>

    <div class="process-flow">
      <div class="flow-step reveal">
        <div class="flow-number">1</div>
        <h3><?= $lang['proc_1_title'] ?></h3>
        <p class="text-muted"><?= $lang['proc_1_desc'] ?></p>
      </div>
      <div class="flow-step reveal delay-100">
        <div class="flow-number">2</div>
        <h3><?= $lang['proc_2_title'] ?></h3>
        <p class="text-muted"><?= $lang['proc_2_desc'] ?></p>
      </div>
      <div class="flow-step reveal delay-200">
        <div class="flow-number">3</div>
        <h3><?= $lang['proc_3_title'] ?></h3>
        <p class="text-muted"><?= $lang['proc_3_desc'] ?></p>
      </div>
      <div class="flow-step reveal delay-300">
        <div class="flow-number">4</div>
        <h3><?= $lang['proc_4_title'] ?></h3>
        <p class="text-muted"><?= $lang['proc_4_desc'] ?></p>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="container section-padding testimonials-section">
    <div class="section-title text-center reveal">
      <h2><?= $lang['test_title'] ?></h2>
      <p class="text-muted mt-2"><?= $lang['test_desc'] ?></p>
    </div>

    <div class="testimonials-grid">
      <div class="testimonial-card reveal">
        <div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="text-muted testimonial-quote"><?= $lang['test_1_quote'] ?></p>
        </div>
        <div class="testimonial-user">
          <div class="testimonial-avatar avatar-primary">SA</div>
          <div>
            <h3 class="testimonial-name"><?= $lang['test_1_name'] ?></h3>
            <span class="testimonial-role"><?= $lang['test_1_role'] ?></span>
          </div>
        </div>
      </div>

      <div class="testimonial-card reveal delay-100">
        <div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="text-muted testimonial-quote"><?= $lang['test_2_quote'] ?></p>
        </div>
        <div class="testimonial-user">
          <div class="testimonial-avatar avatar-accent">MA</div>
          <div>
            <h3 class="testimonial-name"><?= $lang['test_2_name'] ?></h3>
            <span class="testimonial-role"><?= $lang['test_2_role'] ?></span>
          </div>
        </div>
      </div>

      <div class="testimonial-card reveal delay-200">
        <div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="text-muted testimonial-quote"><?= $lang['test_3_quote'] ?></p>
        </div>
        <div class="testimonial-user">
          <div class="testimonial-avatar avatar-dark">FA</div>
          <div>
            <h3 class="testimonial-name"><?= $lang['test_3_name'] ?></h3>
            <span class="testimonial-role"><?= $lang['test_3_role'] ?></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ & Contact Split -->
  <section id="contact" class="container section-padding">
    <div class="split-section">
      <div class="faq-section reveal">
        <h2 class="mb-4"><?= $lang['faq_title'] ?></h2>
        <div class="accordion">
          <?php foreach ($lang['faqs'] as $faq): ?>
            <div class="accordion-item">
              <button class="accordion-header">
                <span><?= $faq['q'] ?></span>
                <span class="icon">+</span>
              </button>
              <div class="accordion-content">
                <div class="content-inner"><?= $faq['a'] ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="contact-form reveal delay-200">
        <h3 class="mb-4"><?= $active_lang === 'ar' ? 'طلب استشارة مجانية' : 'Request Free Solar Consultation' ?></h3>
        
        <form id="native-lead-form" method="POST">
          <input type="hidden" name="action" value="submit_lead">
          <input type="hidden" name="lang" value="<?= $active_lang ?>">
          
          <div class="form-group">
            <label for="lead-name"><?= $active_lang === 'ar' ? 'الاسم الكامل' : 'Full Name' ?> *</label>
            <input type="text" id="lead-name" name="name" required placeholder="<?= $active_lang === 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' ?>" aria-label="Full Name">
          </div>

          <div class="form-row-grid">
            <div class="form-group">
              <label for="lead-phone"><?= $active_lang === 'ar' ? 'رقم الهاتف (واتساب)' : 'Phone Number (WhatsApp)' ?> *</label>
              <input type="tel" id="lead-phone" name="phone" required placeholder="968 XXXXXXXX" aria-label="Phone Number">
            </div>
            <div class="form-group">
              <label for="lead-email"><?= $active_lang === 'ar' ? 'البريد الإلكتروني' : 'Email Address' ?> *</label>
              <input type="email" id="lead-email" name="email" required placeholder="example@domain.com" aria-label="Email Address">
            </div>
          </div>

          <div class="form-row-grid">
            <div class="form-group">
              <label for="lead-gov"><?= $active_lang === 'ar' ? 'المحافظة' : 'Governorate' ?> *</label>
              <select id="lead-gov" name="governorate" required aria-label="Governorate">
                <option value="muscat"><?= $lang['calc_loc_muscat'] ?></option>
                <option value="dhofar"><?= $lang['calc_loc_dhofar'] ?></option>
                <option value="batinah"><?= $lang['calc_loc_batinah'] ?></option>
                <option value="dakhiliyah"><?= $active_lang === 'ar' ? 'الداخلية' : 'Dakhiliyah' ?></option>
                <option value="other"><?= $lang['calc_loc_other'] ?></option>
              </select>
            </div>
            <div class="form-group">
              <label for="lead-prop"><?= $active_lang === 'ar' ? 'نوع العقار' : 'Property Type' ?> *</label>
              <select id="lead-prop" name="property_type" required aria-label="Property Type">
                <option value="residential"><?= $lang['calc_prop_residential'] ?></option>
                <option value="commercial"><?= $lang['calc_prop_commercial'] ?></option>
                <option value="industrial"><?= $lang['calc_prop_industrial'] ?></option>
              </select>
            </div>
          </div>

          <div class="form-row-grid">
            <div class="form-group">
              <label for="lead-bill"><?= $active_lang === 'ar' ? 'متوسط الفاتورة الكهربائية (ريال)' : 'Average Electricity Bill (OMR)' ?> *</label>
              <input type="number" id="lead-bill" name="monthly_bill" required min="10" max="5000" value="50" aria-label="Electricity Bill">
            </div>
            <div class="form-group">
              <label for="lead-consult"><?= $active_lang === 'ar' ? 'نوع الاستشارة' : 'Consultation Type' ?> *</label>
              <select id="lead-consult" name="consultation_type" required aria-label="Consultation Type">
                <option value="site_survey"><?= $active_lang === 'ar' ? 'معاينة موقع مجانية' : 'Free Site Survey' ?></option>
                <option value="video_call"><?= $active_lang === 'ar' ? 'استشارة بالفيديو عن بعد' : 'Online Video Call' ?></option>
                <option value="office"><?= $active_lang === 'ar' ? 'زيارة مكتب كونسيبت' : 'Office Consultation' ?></option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="lead-notes"><?= $active_lang === 'ar' ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (Optional)' ?></label>
            <textarea id="lead-notes" name="message" rows="3" placeholder="<?= $active_lang === 'ar' ? 'تحدث إلينا عن احتياجاتك المحددة...' : 'Tell us about your specific solar needs...' ?>" aria-label="Additional Notes"></textarea>
          </div>

          <!-- Anti-Spam HoneyPot -->
          <div style="display: none;">
            <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
          </div>

          <button type="submit" class="btn btn-primary submit-btn">
            <span><?= $active_lang === 'ar' ? 'تأكيد وحجز الاستشارة 🚀' : 'Confirm & Book Consultation 🚀' ?></span>
            <span class="spinner"></span>
          </button>

          <div id="form-feedback" class="mt-3 text-center" style="display: none;"></div>
        </form>
      </div>
    </div>
  </section>

  </main>

  <!-- Footer -->
  <footer>
    <div class="container footer-grid">
      <div class="footer-col" style="display: flex; flex-direction: column; justify-content: center;">
        <img src="https://www.ctechoman.com/public/logo.webp" alt="Concept Technologies LLC" width="160" height="50"
          loading="lazy" decoding="async"
          style="height: 50px; width: auto; max-width: 200px; margin-bottom: 1.5rem;">
        <p class="text-muted"><?= $lang['foot_copy'] ?></p>
      </div>
      <div class="footer-col">
        <h3><?= $lang['foot_loc_title'] ?></h3>
        <p class="text-muted"><?= $lang['foot_loc_desc'] ?></p>
      </div>
      <div class="footer-col">
        <h3><?= $lang['foot_mail_title'] ?></h3>
        <p class="text-muted"><?= $lang['foot_mail_desc'] ?></p>
      </div>
      <div class="footer-col">
        <h3><?= $lang['foot_call_title'] ?></h3>
        <p class="text-muted"><?= $lang['foot_call_desc'] ?></p>
      </div>
    </div>
  </footer>



  <script>
    window.BrandProductsData = {
      huawei: {
        name: 'Huawei',
        logo: 'brands/huawei.svg',
        tagline: `<?= addslashes($lang['brand_huawei_tag']) ?>`,
        products: [
          { key: 'huawei_sun2000', category: 'inverter', title: `<?= addslashes($lang['ds_1_title']) ?>`, desc: `<?= addslashes($lang['ds_1_desc']) ?>`, specs: [`<?= addslashes($lang['ds_1_spec_1']) ?>`, `<?= addslashes($lang['ds_1_spec_2']) ?>`] , localPdf: 'datasheets/huawei_sun2000.pdf' } 
        ]
      },
      sungrow: {
        name: 'Sungrow',
        logo: 'brands/sungrow.png',
        tagline: `<?= addslashes($lang['brand_sungrow_tag']) ?>`,
        products: [
          { key: 'sungrow_sg110cx', category: 'inverter', title: `<?= addslashes($lang['ds_sungrow_inv_title']) ?>`, desc: `<?= addslashes($lang['ds_sungrow_inv_desc']) ?>`, specs: [`<?= addslashes($lang['ds_sungrow_inv_spec_1']) ?>`, `<?= addslashes($lang['ds_sungrow_inv_spec_2']) ?>`] , localPdf: 'datasheets/sungrow_sg110cx.pdf' } 
        ]
      },
      solis: {
        name: 'Solis',
        logo: 'brands/solis.png',
        tagline: `<?= addslashes($lang['brand_solis_tag']) ?>`,
        products: [
          { key: 'solis_s5', category: 'inverter', title: `<?= addslashes($lang['ds_solis_inv_title']) ?>`, desc: `<?= addslashes($lang['ds_solis_inv_desc']) ?>`, specs: [`<?= addslashes($lang['ds_solis_inv_spec_1']) ?>`, `<?= addslashes($lang['ds_solis_inv_spec_2']) ?>`] , localPdf: 'datasheets/solis_s5.pdf' } ,
          { key: 'solis_flexi', category: 'battery', title: `<?= addslashes($lang['ds_solis_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_solis_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_solis_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_solis_bat_spec_2']) ?>`] , localPdf: 'datasheets/solis_flexi.pdf' } 
        ]
      },
      canadian: {
        name: 'Canadian Solar',
        logo: 'brands/canadian_solar.png',
        tagline: `<?= addslashes($lang['brand_canadian_tag']) ?>`,
        products: [
          { key: 'canadian_solar', category: 'panel', title: `<?= addslashes($lang['ds_2_title']) ?>`, desc: `<?= addslashes($lang['ds_2_desc']) ?>`, specs: [`<?= addslashes($lang['ds_2_spec_1']) ?>`, `<?= addslashes($lang['ds_2_spec_2']) ?>`] , localPdf: 'datasheets/canadian_solar.pdf' } ,
          { key: 'canadian_solar_inv', category: 'inverter', title: `<?= addslashes($lang['ds_canadian_inv_title']) ?>`, desc: `<?= addslashes($lang['ds_canadian_inv_desc']) ?>`, specs: [`<?= addslashes($lang['ds_canadian_inv_spec_1']) ?>`, `<?= addslashes($lang['ds_canadian_inv_spec_2']) ?>`] , localPdf: 'datasheets/canadian_solar_inv.pdf' } ,
          { key: 'canadian_solar_battery', category: 'battery', title: `<?= addslashes($lang['ds_canadian_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_canadian_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_canadian_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_canadian_bat_spec_2']) ?>`] , localPdf: 'datasheets/canadian_solar_battery.pdf' } 
        ]
      },
      deye: {
        name: 'Deye',
        logo: 'brands/deye.png',
        tagline: `<?= addslashes($lang['brand_deye_tag']) ?>`,
        products: [
          { key: 'deye_hybrid', category: 'inverter', title: `<?= addslashes($lang['ds_3_title']) ?>`, desc: `<?= addslashes($lang['ds_3_desc']) ?>`, specs: [`<?= addslashes($lang['ds_3_spec_1']) ?>`, `<?= addslashes($lang['ds_3_spec_2']) ?>`] , localPdf: 'datasheets/deye_hybrid.pdf' } ,
          { key: 'deye_bos_g', category: 'battery', title: `<?= addslashes($lang['ds_deye_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_deye_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_deye_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_deye_bat_spec_2']) ?>`] , localPdf: 'datasheets/deye_bos_g.pdf' } 
        ]
      },
      powersun: {
        name: 'Power & Sun',
        logo: 'brands/power_sun.png',
        tagline: `<?= addslashes($lang['brand_powersun_tag']) ?>`,
        products: [
          { key: 'power_sun_inv', category: 'inverter', title: `<?= addslashes($lang['ds_powersun_inv_title']) ?>`, desc: `<?= addslashes($lang['ds_powersun_inv_desc']) ?>`, specs: [`<?= addslashes($lang['ds_powersun_inv_spec_1']) ?>`, `<?= addslashes($lang['ds_powersun_inv_spec_2']) ?>`] , localPdf: 'datasheets/power_sun_inv.pdf' } ,
          { key: 'power_sun_ess', category: 'battery', title: `<?= addslashes($lang['ds_powersun_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_powersun_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_powersun_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_powersun_bat_spec_2']) ?>`] , localPdf: 'datasheets/power_sun_ess.pdf' } 
        ]
      },
      trina: {
        name: 'Trina Solar',
        logo: 'brands/trina_solar.svg',
        tagline: `<?= addslashes($lang['brand_trina_tag']) ?>`,
        products: [
          { key: 'trina_vertex', category: 'panel', title: `<?= addslashes($lang['ds_trina_panel_title']) ?>`, desc: `<?= addslashes($lang['ds_trina_panel_desc']) ?>`, specs: [`<?= addslashes($lang['ds_trina_panel_spec_1']) ?>`, `<?= addslashes($lang['ds_trina_panel_spec_2']) ?>`] , localPdf: 'datasheets/trina_vertex.pdf' } 
        ]
      },
      longi: {
        name: 'LONGi',
        logo: 'brands/longi.svg',
        tagline: `<?= addslashes($lang['brand_longi_tag']) ?>`,
        products: [
          { key: 'longi_himo6', category: 'panel', title: `<?= addslashes($lang['ds_longi_panel_title']) ?>`, desc: `<?= addslashes($lang['ds_longi_panel_desc']) ?>`, specs: [`<?= addslashes($lang['ds_longi_panel_spec_1']) ?>`, `<?= addslashes($lang['ds_longi_panel_spec_2']) ?>`] , localPdf: 'datasheets/longi_himo6.pdf' } ,
          { key: 'longi_battery', category: 'battery', title: `<?= addslashes($lang['ds_longi_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_longi_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_longi_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_longi_bat_spec_2']) ?>`] , localPdf: 'datasheets/longi_battery.pdf' } 
        ]
      },
      jinko: {
        name: 'Jinko Solar',
        logo: 'brands/jinko_solar.png',
        tagline: `<?= addslashes($lang['brand_jinko_tag']) ?>`,
        products: [
          { key: 'jinko_tiger', category: 'panel', title: `<?= addslashes($lang['ds_jinko_panel_title']) ?>`, desc: `<?= addslashes($lang['ds_jinko_panel_desc']) ?>`, specs: [`<?= addslashes($lang['ds_jinko_panel_spec_1']) ?>`, `<?= addslashes($lang['ds_jinko_panel_spec_2']) ?>`] , localPdf: 'datasheets/jinko_tiger.pdf' } 
        ]
      },
      ja: {
        name: 'JA Solar',
        logo: 'brands/ja_solar.svg',
        tagline: `<?= addslashes($lang['brand_ja_tag']) ?>`,
        products: [
          { key: 'ja_solar_blue', category: 'panel', title: `<?= addslashes($lang['ds_ja_panel_title']) ?>`, desc: `<?= addslashes($lang['ds_ja_panel_desc']) ?>`, specs: [`<?= addslashes($lang['ds_ja_panel_spec_1']) ?>`, `<?= addslashes($lang['ds_ja_panel_spec_2']) ?>`] , localPdf: 'datasheets/ja_solar_blue.pdf' } 
        ]
      },
      jebel: {
        name: 'Jebel',
        logo: 'brands/jebel.png',
        tagline: `<?= addslashes($lang['brand_jebel_tag']) ?>`,
        products: [
          { key: 'jebel_battery', category: 'battery', title: `<?= addslashes($lang['ds_jebel_bat_title']) ?>`, desc: `<?= addslashes($lang['ds_jebel_bat_desc']) ?>`, specs: [`<?= addslashes($lang['ds_jebel_bat_spec_1']) ?>`, `<?= addslashes($lang['ds_jebel_bat_spec_2']) ?>`] , localPdf: 'datasheets/jebel_battery.pdf' } 
        ]
      }
    };
  </script>

<script src="calculator-engine.js?v=3.7"></script>
  <script src="script.js?v=4.5" defer></script>

  <!-- Idle-load non-critical scripts: chatbot + analytics loaded after user interacts or browser is idle -->
  <script>
  (function() {
    'use strict';
    var chatbotLoaded = false;
    var analyticsLoaded = false;

    function loadChatbot() {
      if (chatbotLoaded) return;
      chatbotLoaded = true;
      // Inject chatbot CSS
      var css = document.createElement('link');
      css.rel = 'stylesheet';
      css.href = 'chatbot.css?v=3.1';
      document.head.appendChild(css);
      // Inject chatbot JS
      var js = document.createElement('script');
      js.src = 'chatbot.js?v=3.1';
      js.defer = true;
      document.body.appendChild(js);
    }

    function loadAnalytics() {
      if (analyticsLoaded) return;
      analyticsLoaded = true;
      var js = document.createElement('script');
      js.src = 'analytics.js?v=3.1';
      js.defer = true;
      document.body.appendChild(js);
    }

    // Strategy: load both after idle, but load chatbot immediately on any interaction
    var interactionEvents = ['scroll', 'touchstart', 'mousemove', 'keydown', 'click'];
    function onFirstInteraction() {
      interactionEvents.forEach(function(e) {
        window.removeEventListener(e, onFirstInteraction, { passive: true });
      });
      loadChatbot();
      loadAnalytics();
    }

    interactionEvents.forEach(function(e) {
      window.addEventListener(e, onFirstInteraction, { passive: true, once: true });
    });

    // Idle fallback: load everything after 4 seconds regardless
    if (typeof requestIdleCallback === 'function') {
      requestIdleCallback(function() {
        loadChatbot();
        loadAnalytics();
      }, { timeout: 4000 });
    } else {
      setTimeout(function() {
        loadChatbot();
        loadAnalytics();
      }, 4000);
    }
  })();
  </script>
</body>

</html>






