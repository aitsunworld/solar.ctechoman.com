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
  <link rel="preload" as="image" href="lightbulb.webp" fetchpriority="high">
  <link rel="preload" as="image" href="https://www.ctechoman.com/public/logo.webp">

<!-- CRITICAL INLINE CSS: Above-fold render + core layout primitives -->
   <style>
     *{margin:0;padding:0;box-sizing:border-box;overflow-wrap:break-word}
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
     h1{font-size:clamp(1.6rem,5vw,2.1rem);font-weight:900;line-height:1.2;color:#0d1014}
     h2{font-size:clamp(1.25rem,4vw,1.45rem);font-weight:900;line-height:1.2;color:#0d1014}
     h3{font-size:clamp(1.05rem,3vw,1.2rem);font-weight:900;line-height:1.2;color:#0d1014}
     h4{font-size:clamp(.95rem,2.5vw,1.05rem);font-weight:900;line-height:1.2;color:#0d1014}
     .hero-text p{font-size:1rem;color:#64748B;margin-bottom:1.5rem;font-weight:500}
     .hero-actions{display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;align-items:center;width:100%}
     .hero-visual{width:100%;height:180px;display:flex;justify-content:flex-start;align-items:center;position:relative;overflow:hidden;opacity:1}
     .lightbulb-img{max-height:160px;width:auto}
     .text-muted{color:#64748B}
     .text-center{text-align:center}
     .section-padding{padding-top:2rem;padding-bottom:2rem}
     .section-title{text-align:center;margin-bottom:2rem}
     .bg-blobs{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
     .blob-1,.blob-2{position:absolute;width:200px;height:200px;background:#F1F5F9;border-radius:40% 60% 70% 30%/40% 50% 60% 50%;opacity:.2}
     .blob-1{top:-10%;left:-10%}.blob-2{bottom:10%;right:-5%}
     /* Calculator section — prevent FOUC on below-hero content */
     .calculator-section{position:relative;z-index:10;margin-top:1rem}
     .calculator-wrapper{background:#fff;border-radius:1.5rem;padding:1.25rem 1rem;box-shadow:0 12px 30px -10px rgba(15,23,42,.05);display:flex;flex-direction:column;gap:1.5rem}
     .calc-form{min-width:0;width:100%;max-width:100%}
     .discovery-step-panel{width:100%}
     /* Prevent FOUC: keep hero visible */
     .js-enabled .hero-text,.js-enabled .hero-visual{opacity:1}
     /* Reveal system — hidden until JS activates */
     .js-enabled .reveal{opacity:0;transform:translate3d(0,15px,0);transition:opacity .6s cubic-bezier(.16,1,.3,1),transform .6s cubic-bezier(.16,1,.3,1)}
     .js-enabled .reveal.active{opacity:1;transform:translate3d(0,0,0)}
     /* Spacing utilities */
     .mt-2{margin-top:.5rem}.mt-3{margin-top:1rem}.mt-4{margin-top:1.5rem}
     .mb-3{margin-bottom:1rem}.mb-4{margin-bottom:1.5rem}
   </style>

  <!-- Non-blocking Google Fonts (avoids render-blocking) -->
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&family=Tajawal:wght@400;700;900&display=swap"></noscript>

  <!-- Main stylesheet: RENDER-BLOCKING to prevent FOUC -->
  <link rel="stylesheet" href="style.css?v=9.1">
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
        <a href="#calculator" class="btn btn-primary"><?= $lang['nav_quote'] ?></a>
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
    <div class="hero-slider-container" id="hero-slider-visual">
      <div class="hero-slider-track" id="hero-slider-track">
        <?php 
        $slide_images = [
          'lightbulb.webp',
          'solar_panel.webp',
          'hero-commercial.webp?v=3.11',
          'hero-villa.webp',
          'lightbulb.webp',
          'solar_panel.webp'
        ];
        foreach ($lang['slides'] as $index => $slide): 
          $img = $slide_images[$index];
          $lazy = ($index === 0) ? 'eager' : 'lazy';
          $priority = ($index === 0) ? 'fetchpriority="high"' : '';
        ?>
          <div class="hero-slide">
            <div class="slide-card-container">
              <div class="slide-card-text">
                <span class="slide-badge"><?= $slide['badge'] ?></span>
                <h2><?= $slide['title'] ?></h2>
                <p><?= $slide['desc'] ?></p>
                <?php if (isset($slide['metric_value']) && isset($slide['metric_label'])): ?>
                  <div class="slide-trust-metric">
                    <span class="metric-value"><?= $slide['metric_value'] ?></span>
                    <span class="metric-label"><?= $slide['metric_label'] ?></span>
                  </div>
                <?php endif; ?>
                <div class="slide-actions">
                  <a href="#calculator" class="btn btn-hero-primary"><?= $slide['btn'] ?></a>
                </div>
              </div>
              <div class="slide-card-visual">
                <img src="<?= $img ?>" alt="<?= $slide['title'] ?>" class="lightbulb-img" width="500" height="500"
                  loading="<?= $lazy ?>" <?= $priority ?> style="mix-blend-mode: darken;">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <!-- Slider Navigation Controls inside the Hero Section -->
      <div class="hero-slider-nav">
        <?php foreach ($lang['slides'] as $index => $slide): ?>
          <div class="slider-item <?= $index === 0 ? 'active' : '' ?>" data-slide-index="<?= $index ?>">
            <span class="slider-num"><?= sprintf("%02d", $index + 1) ?></span>
            <div class="slider-line"></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Solar Calculator -->
  <section id="calculator" class="calculator-section container reveal">
    <div class="calculator-wrapper">
      <div class="calc-info" style="display:none">
        <h2><?= $lang['calc_title'] ?></h2>
        <?php if (isset($lang['calc_subtitle'])): ?>
          <p class="section-subtitle text-muted mb-3" id="calc-subtitle" style="font-weight: 500; font-size: 1.1rem; line-height: 1.5;"><?= $lang['calc_subtitle'] ?></p>
        <?php endif; ?>
        <p class="text-muted" id="calc-description"><?= $lang['calc_desc'] ?></p>

        <div id="standard-calc-results" style="display:none">
          <div class="results-grid mt-4">
            <div class="result-box">
              <span class="result-label"><?= $lang['calc_sys_size'] ?></span>
              <strong class="result-value text-eco" id="res-size" dir="ltr">0 kW</strong>
            </div>
            <div class="result-box">
              <span class="result-label"><?= $lang['calc_est_panels'] ?></span>
              <strong class="result-value" id="res-panels" dir="ltr">0</strong>
            </div>
            <div class="result-box">
              <span class="result-label"><?= $lang['calc_est_cost'] ?></span>
              <strong class="result-value" id="res-cost" dir="ltr">0 OMR</strong>
            </div>
            <div class="result-box highlight">
              <span class="result-label"><?= $lang['calc_yearly_savings'] ?></span>
              <strong class="result-value" id="res-savings" dir="ltr">0 OMR</strong>
            </div>
          </div>
          <button id="calc-explain-btn" class="btn btn-secondary mt-3">
            ✨ <?= $active_lang === 'ar' ? 'اشرح لي النتائج بمستشار الذكاء الاصطناعي' : 'Explain results with AI Advisor' ?>
          </button>
          <p class="mt-3 text-muted" style="font-size: 0.8rem;"><?= $lang['calc_note'] ?></p>

          <div id="load-recommendations" style="display: none;">
            <h4 class="recs-title"><?= $active_lang === 'ar' ? 'توصيات النظام المتقدمة' : 'Advanced System Recommendations' ?></h4>
            <div class="recs-grid">
              <div class="result-box load-box">
                <span class="result-label"><?= $active_lang === 'ar' ? 'إجمالي الحمل المتصل' : 'Total Connected Load' ?></span>
                <strong class="result-value" id="res-connected-load">0 W</strong>
              </div>
              <div class="result-box consumption-box">
                <span class="result-label"><?= $active_lang === 'ar' ? 'الاستهلاك اليومي المقدر' : 'Estimated Daily Consumption' ?></span>
                <strong class="result-value" id="res-daily-consumption">0 kWh/day</strong>
              </div>
              <div class="result-box inverter-box">
                <span class="result-label"><?= $active_lang === 'ar' ? 'حجم العاكس المقترح' : 'Recommended Inverter' ?></span>
                <strong class="result-value" id="res-inverter">0 kW</strong>
              </div>
              <div class="result-box battery-box">
                <span class="result-label"><?= $active_lang === 'ar' ? 'سعة البطارية المقترحة' : 'Battery Storage' ?></span>
                <strong class="result-value text-eco" id="res-battery">0 kWh</strong>
              </div>
            </div>
          </div>
        </div>


      </div>

      <div class="calc-form">
        <div id="standard-calc-inputs" style="display:none">
          <div class="calc-tabs">
            <button id="tab-bill" class="calc-tab active">
              <?= $active_lang === 'ar' ? 'تقدير الفاتورة' : 'Bill Estimator' ?>
            </button>
            <button id="tab-appliances" class="calc-tab">
              <?= $active_lang === 'ar' ? 'مدقق الأجهزة' : 'Appliance Auditor' ?>
            </button>
          </div>

          <div id="bill-inputs-container">
            <h3 class="mb-3"><?= $lang['calc_monthly_bill'] ?></h3>
            <div class="slider-container">
              <input type="range" id="bill-slider" min="10" max="1000" value="50" step="5" aria-label="<?= $lang['calc_monthly_bill'] ?>">
              <div class="slider-value"><span id="bill-display">50</span></div>
            </div>
          </div>

          <div id="appliance-inputs-container" style="display: none;">
            <!-- Appliances rendered dynamically via Javascript for multi-language SSOT support -->
          </div>
        </div>

        <div id="residential-discovery-journey" style="display: none;">
          <!-- Wizard Navigation & Steps -->
          <div class="discovery-steps-progress mb-4">
            <div class="step-indicator active" data-step="1">
              <div class="step-dot" title="<?= $lang['step_title_1'] ?>"><span class="step-number">1</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'الأجهزة' : 'Appliances' ?></span>
            </div>
            <div class="step-indicator" data-step="2">
              <div class="step-dot" title="<?= $lang['step_title_2'] ?>"><span class="step-number">2</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'الكميات' : 'Quantities' ?></span>
            </div>
            <div class="step-indicator" data-step="3">
              <div class="step-dot" title="<?= $lang['step_title_3'] ?>"><span class="step-number">3</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'الاستهلاك' : 'Consumption' ?></span>
            </div>
            <div class="step-indicator" data-step="4">
              <div class="step-dot" title="<?= $lang['step_title_4'] ?>"><span class="step-number">4</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'حجم الطاقة' : 'Solar Size' ?></span>
            </div>
            <div class="step-indicator" data-step="5">
              <div class="step-dot" title="<?= $lang['step_title_5'] ?>"><span class="step-number">5</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'الفاتورة' : 'Bill' ?></span>
            </div>
            <div class="step-indicator" data-step="6">
              <div class="step-dot" title="<?= $lang['step_title_6'] ?>"><span class="step-number">6</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'المعايرة' : 'Calibration' ?></span>
            </div>
            <div class="step-indicator" data-step="7">
              <div class="step-dot" title="<?= $lang['step_title_7'] ?>"><span class="step-number">7</span></div>
              <span class="step-label"><?= $active_lang === 'ar' ? 'النتائج' : 'Results' ?></span>
            </div>
          </div>


          <!-- Panel 1: Select Appliances (Step 1) -->
          <div class="discovery-step-panel" id="discovery-panel-1">
            <h3 class="mb-2"><?= $lang['step_title_1'] ?></h3>
            <p class="text-muted mb-3" style="font-size: 0.88rem;">
              <?= $active_lang === 'ar' ? 'اختر الأجهزة المستخدمة في منزلك من خلال النقر عليها لتحديدها.' : 'Click on the appliances you run at home to select them.' ?>
            </p>
            <div id="discovery-appliance-selection-grid" class="appliance-selection-grid">
              <!-- Rendered dynamically in script.js (toggle cards) -->
            </div>
            <div class="step-actions mt-3">
              <button type="button" class="btn btn-primary w-100" id="btn-goto-step2">
                <?= $active_lang === 'ar' ? 'التالي: تحديد الكميات ➔' : 'Next: Enter Quantities ➔' ?>
              </button>
            </div>
          </div>

          <!-- Panel 2: Enter Quantity (Step 2) -->
          <div class="discovery-step-panel" id="discovery-panel-2" style="display: none;">
            <h3 class="mb-2"><?= $lang['step_title_2'] ?></h3>
            <p class="text-muted mb-3" style="font-size: 0.88rem;">
              <?= $active_lang === 'ar' ? 'اضبط الكمية المناسبة لكل جهاز من الأجهزة المحددة.' : 'Specify the count/quantity for each of your selected appliances.' ?>
            </p>
            <div id="discovery-appliance-qty-grid" class="appliance-qty-grid">
              <!-- Rendered dynamically in script.js (counters) -->
            </div>
            <div class="step-actions mt-3" style="display: flex; gap: 0.75rem;">
              <button type="button" class="btn btn-secondary" id="btn-back-to-step1" style="flex: 1; min-width: 80px;">
                <?= $active_lang === 'ar' ? '⬅ الأجهزة' : '⬅ Appliances' ?>
              </button>
              <button type="button" class="btn btn-primary" id="btn-goto-step3" style="flex: 2;">
                <?= $active_lang === 'ar' ? 'التالي: الاستهلاك المقدر ➔' : 'Next: See Consumption ➔' ?>
              </button>
            </div>
          </div>

          <!-- Panel 3: Show Estimated Monthly Consumption (Step 3) -->
          <div class="discovery-step-panel" id="discovery-panel-3" style="display: none;">
            <h3 class="mb-2"><?= $lang['step_title_3'] ?></h3>
            <p class="text-muted mb-4"><?= $active_lang === 'ar' ? 'تحليل استهلاك الطاقة بناءً على قائمة أجهزتك المنزلية المحددة.' : 'Energy consumption profile computed from your selected household appliances.' ?></p>
            
            <div class="side-by-side-layout">
              <div class="consumption-reveal-zone text-center py-4">
                <div class="animated-gauge-wrapper">
                  <div class="gauge-value" id="reveal-consumption-value">0 kWh</div>
                  <div class="gauge-label"><?= $active_lang === 'ar' ? 'شهرياً' : 'per month' ?></div>
                </div>
                <p class="text-muted mt-3" style="font-size: 0.9rem;">
                  <?= $active_lang === 'ar' ? 'تقدير استهلاكك الشهري التقريبي للكهرباء.' : 'Your approximate estimated monthly electricity consumption.' ?>
                </p>
              </div>
              <div class="contributors-card-wrapper">
                <div class="contributors-header">
                  <strong><?= $active_lang === 'ar' ? 'أعلى الأجهزة استهلاكاً للطاقة' : 'Top Energy Contributors' ?></strong>
                </div>
                <div id="consumption-contributors-list" class="contributors-list">
                  <!-- Rendered dynamically in script.js -->
                </div>
              </div>
            </div>

            <div class="step-actions mt-3" style="display: flex; gap: 0.75rem;">
              <button type="button" class="btn btn-secondary" id="btn-back-to-step2" style="flex: 1; min-width: 80px;">
                <?= $active_lang === 'ar' ? '⬅ الكميات' : '⬅ Quantities' ?>
              </button>
              <button type="button" class="btn btn-primary" id="btn-goto-step4" style="flex: 2;">
                <?= $active_lang === 'ar' ? 'التالي: حجم نظام الطاقة الشمسية ➔' : 'Next: Recommended Solar ➔' ?>
              </button>
            </div>
          </div>

          <!-- Panel 4: Show Recommended Solar Size (Step 4) -->
          <div class="discovery-step-panel" id="discovery-panel-4" style="display: none;">
            <h3 class="mb-2"><?= $lang['step_title_4'] ?></h3>
            <p class="text-muted mb-4"><?= $active_lang === 'ar' ? 'تم حساب سعة النظام بناءً على نمط استهلاكك الفعلي.' : 'System capacity calculated based on your unique load profile.' ?></p>

            <div class="side-by-side-layout">
              <div class="solar-reveal-zone text-center py-4">
                <div class="solar-value-wrapper">
                  <div class="solar-kw-value" id="reveal-solar-kw">0.0 kW</div>
                  <div class="solar-panels-count" id="reveal-solar-panels">0 Panels</div>
                </div>
                <p class="text-muted mt-3" style="font-size: 0.9rem;">
                  <?= $active_lang === 'ar' ? 'حجم النظام المقترح لتلبية وتغطية احتياجاتك من الطاقة.' : 'This recommended solar system size is configured to generate enough electricity for your home.' ?>
                </p>
              </div>
              <div class="education-info-card">
                <div class="edu-card-header">
                  <strong><?= $active_lang === 'ar' ? 'كيف نقوم بحساب حجم النظام؟' : 'How We Sized Your System' ?></strong>
                </div>
                <div class="edu-card-body">
                  <p class="edu-math-desc">
                    <?= $active_lang === 'ar'
                      ? 'نقوم بتحليل الحمل الكهربائي المتوقع لكل جهاز من أجهزتك المختارة على مدار 365 يومًا، ثم نقارنه بمعدل الإشعاع الشمسي السنوي في سلطنة عُمان (حوالي 1600-1800 كيلوواط ساعة لكل كيلوواط ذروة سنوياً) لتقدير القدرة الشمسية المطلوبة بدقة.'
                      : 'We model the expected hourly runtimes of your selected appliances across a full calendar year. We cross-reference this against Oman\'s high annual solar yield (average 1,650 kWh/kWp per year) to calculate the precise solar capacity required to offset your consumption.'
                    ?>
                  </p>
                  <ul class="edu-specs-list">
                    <li>💡 <strong><?= $active_lang === 'ar' ? 'إجمالي الحمل المتصل:' : 'Total Connected Load:' ?></strong> <span id="reveal-total-load-kw">0 W</span></li>
                    <li>🌞 <strong><?= $active_lang === 'ar' ? 'معامل الإنتاج الشمسي:' : 'Solar Yield Factor:' ?></strong> <span>~1,700 kWh/kW/yr</span></li>
                    <li>📐 <strong><?= $active_lang === 'ar' ? 'المساحة التقريبية للسطح:' : 'Est. Roof Area Required:' ?></strong> <span id="reveal-roof-area-sqm">0 sqm</span></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="step-actions mt-3" style="display: flex; gap: 0.75rem;">
              <button type="button" class="btn btn-secondary" id="btn-back-to-step3" style="flex: 1; min-width: 80px;">
                <?= $active_lang === 'ar' ? '⬅ الاستهلاك' : '⬅ Consumption' ?>
              </button>
              <button type="button" class="btn btn-primary" id="btn-goto-step5" style="flex: 2;">
                <?= $active_lang === 'ar' ? 'التالي: معايرة الفاتورة ➔' : 'Next: Calibrate Bill ➔' ?>
              </button>
            </div>
          </div>

          <!-- Panel 5: Ask for average electricity bill (Step 5) -->
          <div class="discovery-step-panel" id="discovery-panel-5" style="display: none;">
            <h3 class="mb-2"><?= $lang['step_title_5'] ?></h3>
            <p class="text-muted mb-4" style="font-size: 0.88rem;">
              <?= $active_lang === 'ar' ? 'معايرة النظام بناءً على نمط استهلاكك الفعلي تزيد من دقة حساب العائد المالي.' : 'Calibrating with your actual billing history ensures absolute ROI accuracy.' ?>
            </p>

            <div class="side-by-side-layout">
              <!-- Left Column: Slider Calibration -->
              <div class="step5-left-col">
                <div class="step5-slider-card" id="step5-slider-section">
                  <div class="step5-bill-badge-row">
                    <span class="step5-bill-label"><?= $active_lang === 'ar' ? 'متوسط الفاتورة الشهرية' : 'Average Monthly Bill' ?></span>
                    <div class="step5-bill-badge">
                      <span class="bill-badge-amount" id="discovery-bill-display">50</span>
                      <span class="bill-badge-currency">OMR</span>
                    </div>
                  </div>
                  <div class="step5-slider-wrapper">
                    <input type="range" id="discovery-bill-slider" min="10" max="1000" value="50" step="5" aria-label="Monthly Bill Calibration Slider" class="step5-range">
                    <div class="step5-slider-labels">
                      <span>10 OMR</span>
                      <span>1000 OMR</span>
                    </div>
                  </div>
                </div>

                <div class="step5-actions-section" id="step5-main-actions">
                  <button type="button" class="btn-step5-primary" id="btn-calibrate-bill">
                    <?= $active_lang === 'ar' ? '✓ المتابعة بهذه الفاتورة' : '✓ Continue With This Bill' ?>
                  </button>
                  <button type="button" class="btn-step5-skip" id="btn-skip-calibration">
                    <?= $active_lang === 'ar' ? 'تخطي في الوقت الحالي' : 'Skip For Now' ?>
                  </button>
                </div>
              </div>

              <!-- Right Column: Advanced Options (Tabs for Manual/Upload) -->
              <div class="step5-right-col">
                <div class="calibration-options-card">
                  <div class="calibration-options-tabs">
                    <button type="button" class="cal-opt-tab active" id="tab-upload-bill">
                      <?= $active_lang === 'ar' ? '📎 رفع فاتورة' : '📎 Upload Bill' ?>
                    </button>
                    <button type="button" class="cal-opt-tab" id="tab-manual-entry">
                      <?= $active_lang === 'ar' ? '✏️ إدخال تفاصيل يدوية' : '✏️ Manual Details' ?>
                    </button>
                  </div>

                  <!-- Upload Dropzone (active by default) -->
                  <div class="cal-opt-panel" id="panel-upload-bill">
                    <label class="upload-bill-dropzone" id="upload-bill-dropzone" for="db-bill-file">
                      <div class="upload-dropzone-icon">☁️</div>
                      <div class="upload-dropzone-text">
                        <?= $active_lang === 'ar' ? 'اسحب ملف الفاتورة هنا، أو <span>تصفح</span>' : 'Drag &amp; drop your bill here, or <span>browse</span>' ?>
                      </div>
                      <input type="file" id="db-bill-file" name="bill_file" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" aria-label="Upload electricity bill">
                    </label>
                    <div class="upload-bill-success" id="upload-bill-success" style="display: none;">
                      <div class="upload-success-icon">✅</div>
                      <p class="upload-success-msg"><?= $active_lang === 'ar' ? 'تم رفع الفاتورة بنجاح. سيقوم المهندس بمراجعتها.' : 'Bill uploaded successfully. A solar consultant will review the bill during assessment.' ?></p>
                    </div>
                  </div>

                  <!-- Manual Entry Panel (hidden by default) -->
                  <div class="cal-opt-panel" id="panel-manual-entry" style="display: none;">
                    <div class="manual-bill-fields">
                      <div class="manual-bill-input-group">
                        <label class="manual-bill-field-label"><?= $active_lang === 'ar' ? 'الفاتورة الشهرية (ريال عماني)' : 'Monthly Bill (OMR)' ?></label>
                        <input type="number" id="manual-bill-amount" min="5" max="5000" step="1" placeholder="Enter amount..." class="manual-bill-input" aria-label="Monthly Bill Amount">
                      </div>
                      <div class="manual-bill-input-group">
                        <label class="manual-bill-field-label"><?= $active_lang === 'ar' ? 'ملاحظات إضافية (اختياري)' : 'Optional Notes' ?></label>
                        <input type="text" id="manual-bill-notes" placeholder="e.g. Summer bill, AC heavy..." class="manual-bill-input" aria-label="Optional Notes">
                      </div>
                    </div>
                    <button type="button" class="btn-manual-submit mt-3" id="btn-manual-bill-submit">
                      <?= $active_lang === 'ar' ? 'تأكيد ومعايرة' : 'Continue Calibration' ?>
                    </button>
                  </div>

                </div>
              </div>
            </div>

            <div class="step-actions mt-4" style="justify-content: flex-start;">
              <button type="button" class="btn btn-secondary" id="btn-back-to-step4" style="flex: 0 0 auto; width: auto;">
                <?= $active_lang === 'ar' ? '⬅ الحجم الشمسي' : '⬅ Back to Solar Size' ?>
              </button>
            </div>
          </div>

          <!-- Panel 6: Calibrate results (Step 6) -->
          <div class="discovery-step-panel" id="discovery-panel-6" style="display: none;">
            <h3 class="mb-2"><?= $lang['step_title_6'] ?></h3>
            <p class="text-muted mb-4" id="calibration-status-message">
              <?= $active_lang === 'ar' ? 'اكتملت المعايرة!' : 'Calibration completed!' ?>
            </p>

            <div class="side-by-side-layout">
              <!-- Left Column: Ring progress -->
              <div class="calibration-ring-section py-4">
                <div class="progress-ring-wrapper">
                  <svg class="progress-ring-svg" width="200" height="200" viewBox="0 0 200 200">
                    <circle class="progress-ring-bg" cx="100" cy="100" r="85" stroke-width="10" fill="none" />
                    <circle class="progress-ring-fill" id="calibration-progress-fill" cx="100" cy="100" r="85" stroke-width="10" fill="none" />
                  </svg>
                  <div class="progress-ring-text">
                    <span class="progress-ring-number" id="calibration-confidence-pct"><?= $active_lang === 'ar' ? 'تقديري' : '—' ?></span>
                    <span class="progress-ring-sublabel" id="calibration-match-label"><?= $active_lang === 'ar' ? 'درجة الثقة' : 'Excellent Match' ?></span>
                    <span class="progress-ring-label"><?= $active_lang === 'ar' ? 'الثقة' : 'Confidence' ?></span>
                  </div>
                </div>
              </div>

              <!-- Right Column: Metrics & Feedback -->
              <div class="calibration-details-col">
                <!-- 2x2 Metrics Grid -->
                <div class="calibration-metrics-grid mb-3">
                  <div class="cal-metric-card">
                    <span class="cal-metric-label"><?= $active_lang === 'ar' ? 'حالة التطابق' : 'Match Status' ?></span>
                    <strong class="cal-metric-val" id="cal-val-score"><?= $active_lang === 'ar' ? 'استخدام ملف الأجهزة' : 'Using Appliance Profile' ?></strong>
                  </div>
                  <div class="cal-metric-card">
                    <span class="cal-metric-label"><?= $active_lang === 'ar' ? 'نسبة تطابق الفاتورة' : 'Bill Match' ?></span>
                    <strong class="cal-metric-val" id="cal-val-bill-match"><?= $active_lang === 'ar' ? 'غير متوفر' : 'Not Available' ?></strong>
                  </div>
                  <div class="cal-metric-card">
                    <span class="cal-metric-label"><?= $active_lang === 'ar' ? 'دقة تقدير الطاقة' : 'Energy Accuracy' ?></span>
                    <strong class="cal-metric-val text-eco" id="cal-val-accuracy"><?= $active_lang === 'ar' ? 'تقديري' : 'Estimated' ?></strong>
                  </div>
                  <div class="cal-metric-card">
                    <span class="cal-metric-label"><?= $active_lang === 'ar' ? 'مستوى الثقة' : 'Confidence Level' ?></span>
                    <strong class="cal-metric-val" id="cal-val-confidence-level"><?= $active_lang === 'ar' ? 'تقديري' : 'Estimated' ?></strong>
                  </div>
                </div>

                <!-- Hidden feedback boxes -->
                <div class="calibration-feedback alert alert-warning mb-3" id="calibration-warning-box" style="display: none;">
                  ⚠️ <?= $lang['calibration_warning'] ?>
                </div>
                <div class="calibration-feedback alert alert-success mb-3" id="calibration-success-box" style="display: none;">
                  ✅ <?= $active_lang === 'ar' ? 'تطابق استهلاك الأجهزة مع الفاتورة ممتاز! التقديرات معايرة بدقة.' : 'Excellent match between appliance usage and bill history! Estimates calibrated.' ?>
                </div>

                <!-- Insight Summary Card -->
                <div class="calibration-insight-card mb-3" id="cal-insight-card" style="display: none;">
                  <div class="cal-insight-header">
                    <span>💡</span>
                    <strong><?= $active_lang === 'ar' ? 'تحليل مقارنة الاستهلاك' : 'Calibrated Energy Insight' ?></strong>
                  </div>
                  <p id="cal-insight-desc" class="mb-0 mt-1"></p>
                </div>

                <!-- Action Plan Card -->
                <div class="calibration-action-card mb-3" id="cal-action-card" style="display: none;">
                  <div class="cal-action-header">
                    <span>🚀</span>
                    <strong><?= $active_lang === 'ar' ? 'الإجراء الموصى به من قبل النظام' : 'Recommended Action Plan' ?></strong>
                  </div>
                  <p id="cal-action-desc" class="mb-0 mt-1"></p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="calibration-step-actions mt-4">
              <button type="button" class="btn btn-secondary" id="btn-back-to-step5">
                <?= $active_lang === 'ar' ? '⬅ تعديل الفاتورة' : '⬅ Edit Bill' ?>
              </button>
              <button type="button" class="btn btn-primary" id="btn-goto-step7">
                <?= $active_lang === 'ar' ? 'عرض لوحة التحكم ➔' : 'Reveal Insights Dashboard ➔' ?>
              </button>
            </div>
          </div>
          <!-- /Panel 6 -->

          <!-- Panel 7: Reveal personalized dashboard (Step 7) -->
          <div class="discovery-step-panel" id="discovery-panel-7" style="display: none;">
            <div id="residential-discovery-results">
              
              <h3 class="dashboard-main-title"><?= $lang['step_title_7'] ?></h3>
              
              <p class="dashboard-summary-intro text-center text-muted mb-4" style="max-width: 800px; margin: 0 auto 1.5rem auto; font-size: 0.95rem; line-height: 1.6;">
                <?= $active_lang === 'ar' 
                  ? 'بناءً على الأجهزة المحددة، وسلوك الاستهلاك المقدر، ومعايرة الفاتورة الشهرية، قمنا بإعداد تحليل استثماري وبيئي مخصص لعقارك في سلطنة عُمان.'
                  : 'Based on your selected appliances, estimated load profile, and monthly bill calibration, we have generated a personalized technical, investment, and environmental yield analysis for your property in Oman.'
                ?>
              </p>

              <!-- Dynamic Assessment Summary Box -->
              <div class="assessment-summary-box mb-4" id="db-assessment-summary-box">
                <div class="summary-box-icon">📋</div>
                <div class="summary-box-content">
                  <h4 class="summary-box-title"><?= $active_lang === 'ar' ? 'ملخص التقييم الفني' : 'Technical Assessment Summary' ?></h4>
                  <p id="db-val-assessment-summary-text" class="mb-0">
                    <?= $active_lang === 'ar' 
                      ? 'جاري تحميل ملخص التقييم...'
                      : 'Generating your custom solar assessment summary...'
                    ?>
                  </p>
                </div>
              </div>
              
              <div class="discovery-dashboard">
                
                <!-- 4-Up Premium Score Cards -->
                <div class="score-cards-row mb-4">
                  <!-- Score Card 1: Solar Readiness -->
                  <div class="score-card-item">
                    <span class="score-card-label"><?= $active_lang === 'ar' ? 'جاهزية الطاقة الشمسية' : 'Solar Readiness' ?></span>
                    <strong class="score-card-value text-primary" id="score-card-readiness">A+</strong>
                    <div class="score-card-bar-wrapper">
                      <div class="score-card-bar bg-primary" id="score-card-readiness-bar" style="width: 95%;"></div>
                    </div>
                  </div>
                  <!-- Score Card 2: Energy Independence -->
                  <div class="score-card-item">
                    <span class="score-card-label"><?= $active_lang === 'ar' ? 'الاستقلال عن الشبكة' : 'Energy Independence' ?></span>
                    <strong class="score-card-value text-eco" id="score-card-independence">0%</strong>
                    <div class="score-card-bar-wrapper">
                      <div class="score-card-bar bg-eco" id="score-card-independence-bar" style="width: 0%;"></div>
                    </div>
                  </div>
                  <!-- Score Card 3: Financial Opportunity -->
                  <div class="score-card-item">
                    <span class="score-card-label"><?= $active_lang === 'ar' ? 'الفرصة المالية السنوية' : 'Financial Opportunity' ?></span>
                    <strong class="score-card-value text-gold" id="score-card-financial">0 OMR</strong>
                    <div class="score-card-bar-wrapper">
                      <div class="score-card-bar bg-gold" id="score-card-financial-bar" style="width: 85%;"></div>
                    </div>
                  </div>
                  <!-- Score Card 4: Environmental Impact -->
                  <div class="score-card-item">
                    <span class="score-card-label"><?= $active_lang === 'ar' ? 'الأثر البيئي السنوي' : 'Environmental Impact' ?></span>
                    <strong class="score-card-value text-green" id="score-card-environmental">0.0 T</strong>
                    <div class="score-card-bar-wrapper">
                      <div class="score-card-bar bg-green" id="score-card-environmental-bar" style="width: 75%;"></div>
                    </div>
                  </div>
                </div>

                <!-- Financial Impact Section Title -->
                <h4 class="dashboard-group-title mb-3">
                  <span>📊</span> <?= $active_lang === 'ar' ? 'ملخص الأثر المالي والاستثماري' : 'Financial Impact Summary' ?>
                </h4>

                <!-- ROW 1: Top Summary Row -->
                <div class="dashboard-row summary-row">
                  <!-- Monthly Savings -->
                  <div class="discovery-metric-card text-eco-card">
                    <span class="metric-label"><?= $lang['db_monthly_sav'] ?></span>
                    <strong class="metric-val text-eco" id="db-val-monthly-sav">0 OMR</strong>
                  </div>
                  <!-- Yearly Savings -->
                  <div class="discovery-metric-card text-eco-card">
                    <span class="metric-label"><?= $lang['db_yearly_sav'] ?></span>
                    <strong class="metric-val text-eco" id="db-val-yearly-sav">0 OMR</strong>
                  </div>
                  <!-- Electricity Cost Reduction % -->
                  <div class="discovery-metric-card text-eco-card">
                    <span class="metric-label"><?= $active_lang === 'ar' ? 'نسبة خفض الفاتورة' : 'Bill Reduction %' ?></span>
                    <strong class="metric-val text-eco" id="db-val-reduction-pct">0%</strong>
                  </div>
                  <!-- ROI / Payback -->
                  <div class="discovery-metric-card">
                    <span class="metric-label"><?= $lang['db_payback'] ?> / ROI</span>
                    <strong class="metric-val" id="db-val-payback-roi">—</strong>
                  </div>
                </div>

                <!-- ROW 2: Lifetime Savings & Environmental Impact Banners -->
                <div class="dashboard-row hero-row">
                  <!-- Lifetime Savings Card -->
                  <div class="discovery-metric-card highlight-lifetime">
                    <span class="metric-label"><?= $lang['db_lifetime_sav'] ?></span>
                    <strong class="metric-val text-eco" id="db-val-lifetime-sav">0 OMR</strong>
                    <p class="hero-card-desc">
                      <?= $active_lang === 'ar' ? 'الوفر المالي التراكمي المضمون على مدى العمر الافتراضي المتوقع للألواح (25 عاماً).' : 'Expected cumulative cash savings over the guaranteed 25-year operational lifecycle of the solar panels.' ?>
                    </p>
                  </div>
                  <!-- Lifetime Environmental Impact Card -->
                  <div class="discovery-metric-card highlight-environmental">
                    <span class="metric-label"><?= $active_lang === 'ar' ? 'ملخص الأثر البيئي' : 'Environmental Impact Summary' ?></span>
                    <div class="environmental-metrics-inline">
                      <div class="env-metric-item">
                        <span class="env-value text-green" id="score-co2-val">0.0 Tons</span>
                        <span class="env-label"><?= $active_lang === 'ar' ? 'تجنب ثاني أكسيد الكربون سنوياً' : 'Annual CO₂ Offset' ?></span>
                      </div>
                      <div class="env-metric-item">
                        <span class="env-value" id="score-trees-val">0</span>
                        <span class="env-label"><?= $active_lang === 'ar' ? 'شجرة مكافئة مغروسة' : 'Equivalent Trees' ?></span>
                      </div>
                      <div class="env-metric-item">
                        <span class="env-value text-green" id="score-green-rating">Excellent</span>
                        <span class="env-label"><?= $active_lang === 'ar' ? 'التقييم البيئي' : 'Ecological Rating' ?></span>
                      </div>
                    </div>
                    <p class="hero-card-desc">
                      <?= $active_lang === 'ar' ? 'يدعم هذا المشروع بشكل مباشر مبادرات الحياد الكربوني ورؤية عُمان 2040 للبيئة المستدامة.' : 'This project directly offsets grid-generated emissions, advancing Oman Vision 2040 sustainability targets.' ?>
                    </p>
                  </div>
                </div>

                <!-- ROW 3: Three Columns for Insights -->
                <div class="dashboard-row insights-row">
                  <!-- Col 1: Energy Consumption & Independence Score -->
                  <div class="dashboard-section col-insights">
                    <h4 class="section-title-discovery">
                      <span>⚡</span> <?= $active_lang === 'ar' ? 'مؤشر استقلال الطاقة' : 'Energy Independence Score' ?>
                    </h4>
                    <div class="proposal-card-content">
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'الاستهلاك اليومي' : 'Daily Usage' ?></span>
                        <strong class="proposal-value" id="db-val-daily-cons">0 kWh/day</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'الاستهلاك الشهري' : 'Monthly Usage' ?></span>
                        <strong class="proposal-value" id="db-val-monthly-cons">0 kWh/month</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'تغطية الطاقة الشمسية' : 'Solar Offset Potential' ?></span>
                        <strong class="proposal-value text-eco" id="score-energy-label">0%</strong>
                      </div>
                      <div class="proposal-status-section">
                        <span class="proposal-label-small"><?= $active_lang === 'ar' ? 'الحالة' : 'Status' ?></span>
                        <div class="proposal-status-badge status-excellent" id="db-val-energy-status">
                          <?= $active_lang === 'ar' ? 'استقلال ممتاز للطاقة' : 'Excellent Energy Independence' ?>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Col 2: Solar Performance Summary -->
                  <div class="dashboard-section col-insights">
                    <h4 class="section-title-discovery">
                      <span>☀️</span> <?= $active_lang === 'ar' ? 'ملخص أداء الطاقة الشمسية' : 'Solar Performance Summary' ?>
                    </h4>
                    <div class="proposal-card-content">
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'إنتاج الطاقة السنوي' : 'Annual Energy Production' ?></span>
                        <strong class="proposal-value text-eco" id="db-val-annual-prod">0 kWh/year</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'التوليد الشمسي الشهري' : 'Monthly Solar Generation' ?></span>
                        <strong class="proposal-value text-eco" id="db-val-monthly-gen">0 kWh/month</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'معدل الاستهلاك الذاتي' : 'Self Consumption Rate' ?></span>
                        <strong class="proposal-value" id="db-val-self-cons">0%</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'نسبة إزاحة الشبكة' : 'Grid Offset %' ?></span>
                        <strong class="proposal-value" id="db-val-grid-offset">0%</strong>
                      </div>
                      <div class="proposal-row">
                        <span class="proposal-label"><?= $active_lang === 'ar' ? 'فئة الأداء التقديرية' : 'Estimated Performance Grade' ?></span>
                        <strong class="proposal-value text-green" id="db-val-perf-grade">A+</strong>
                      </div>
                    </div>
                  </div>

                  <!-- Col 3: Technical Specifications & Solar Readiness Score -->
                  <div class="dashboard-section col-insights">
                    <h4 class="section-title-discovery">
                      <span>🔧</span> <?= $active_lang === 'ar' ? 'المواصفات الفنية والجاهزية' : 'Technical Specs & Solar Readiness' ?>
                    </h4>
                    <div class="proposal-card-content spec-card-content">
                      <!-- Section 1: Recommended System -->
                      <div class="spec-section">
                        <h5 class="spec-section-title"><?= $active_lang === 'ar' ? 'النظام الموصى به' : 'Recommended System' ?></h5>
                        <div class="proposal-row">
                          <span class="proposal-label"><?= $active_lang === 'ar' ? 'قدرة الطاقة الشمسية' : 'Solar Capacity' ?></span>
                          <strong class="proposal-value text-eco" id="db-val-rec-size">0 kW</strong>
                        </div>
                        <div class="proposal-row">
                          <span class="proposal-label"><?= $active_lang === 'ar' ? 'عدد الألواح' : 'Panel Count' ?></span>
                          <strong class="proposal-value" id="db-val-panel-count">0 Panels</strong>
                        </div>
                        <div class="proposal-row">
                          <span class="proposal-label"><?= $active_lang === 'ar' ? 'قدرة العاكس' : 'Inverter Capacity' ?></span>
                          <strong class="proposal-value" id="db-val-inverter-size">0 kW</strong>
                        </div>
                        <div class="proposal-row">
                          <span class="proposal-label"><?= $active_lang === 'ar' ? 'سعة تخزين البطارية' : 'Battery Storage' ?></span>
                          <strong class="proposal-value text-eco" id="db-val-battery-size">0 kWh</strong>
                        </div>
                      </div>

                      <!-- Section 2: Estimated Investment -->
                      <div class="spec-section border-top-divider">
                        <h5 class="spec-section-title"><?= $active_lang === 'ar' ? 'الاستثمار التقديري' : 'Estimated Investment' ?></h5>
                        <div class="investment-value-box" id="db-val-install-cost">
                          0 OMR
                        </div>
                      </div>

                      <!-- Section 3: Solar Readiness Score -->
                      <div class="spec-section border-top-divider">
                        <h5 class="spec-section-title"><?= $active_lang === 'ar' ? 'مؤشر الجاهزية للطاقة الشمسية' : 'Solar Readiness Score' ?></h5>
                        <div class="suitability-result-box">
                          <div class="suitability-badge-new grade-b" id="score-suitability-badge">
                            <span id="score-suitability-label">B</span>
                          </div>
                          <p class="suitability-desc" id="score-suitability-desc">
                            <?= $active_lang === 'ar' ? 'عقار مناسب جداً مع إمكانية شمسية عالية وحمل متوافق.' : 'High suitability ready for deployment with optimum orientation.' ?>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ROW 4: Integrated Lead Assessment Form (Recommended Next Step) -->
                <div class="dashboard-row form-row-row">
                  <div class="cta-lead-card">
                    <div class="cta-lead-header text-center">
                      <span class="next-step-badge mb-2 d-inline-block"><?= $active_lang === 'ar' ? 'الخطوة التالية الموصى بها' : 'Recommended Next Step' ?></span>
                      <h4><?= $active_lang === 'ar' ? 'طلب التحقق الفني المجاني لتصميم النظام' : 'Request Free Engineering Verification & Sizing Audit' ?></h4>
                      <p class="mt-2" style="max-width: 800px; margin: 0 auto; font-size: 0.9rem; line-height: 1.5; color: var(--color-text-muted);">
                        <?= $active_lang === 'ar' 
                          ? 'سيقوم مهندسونا المعتمدون من مجلس مراجعة قواعد توزيع الكهرباء (DCRP) بمراجعة ملف استهلاك أجهزتك وإجراء محاكاة تفصيلية للموقع للتحقق من الجاهزية والوفر النهائي دون أي التزام.' 
                          : 'Our DCRP-certified engineering team will review your load profile and run site-specific shadow modeling to verify your system readiness and final cash yield, completely free of obligation.' 
                        ?>
                      </p>
                    </div>
                    <form id="discovery-lead-form" class="cta-lead-form">
                      <input type="hidden" name="action" value="submit_lead">
                      <input type="hidden" name="lang" value="<?= $active_lang ?>">
                      <input type="hidden" name="property_type" value="residential">
                      <input type="hidden" name="location" id="disc-lead-loc">
                      <input type="hidden" name="monthly_bill" id="disc-lead-bill">
                      <input type="hidden" name="honeypot" tabindex="-1" autocomplete="off">

                      <div class="cta-form-fields">
                        <div class="form-group-field">
                          <input type="text" id="disc-lead-name" name="name" required placeholder="<?= $active_lang === 'ar' ? 'الاسم الكامل *' : 'Full Name *' ?>" class="form-control-discovery" aria-label="Full Name">
                        </div>
                        <div class="form-group-field phone-input-wrapper">
                          <input type="tel" id="disc-lead-phone" name="phone" required placeholder="<?= $active_lang === 'ar' ? 'رقم واتساب *' : 'WhatsApp Number *' ?>" class="form-control-discovery" aria-label="Phone Number">
                          <span class="whatsapp-input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg" style="width: 18px; height: 18px; color: #25D366; fill: #25D366;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                          </span>
                        </div>
                        <div class="form-group-field">
                          <input type="email" id="disc-lead-email" name="email" required placeholder="<?= $active_lang === 'ar' ? 'البريد الإلكتروني *' : 'Email Address *' ?>" class="form-control-discovery" aria-label="Email Address">
                        </div>
                      </div>
                      
                      <div class="form-group-field mt-3">
                        <textarea id="disc-lead-notes" name="message" rows="3" placeholder="<?= $active_lang === 'ar' ? 'ملاحظات إضافية (اختياري)...' : 'Additional notes (optional)...' ?>" class="form-control-discovery textarea-discovery" aria-label="Additional Notes"></textarea>
                      </div>

                      <button type="submit" class="btn-cta-submit mt-3" id="btn-submit-discovery">
                        <span><?= $active_lang === 'ar' ? 'تأكيد الحجز ومتابعة التدقيق الهندي ➔' : 'Confirm Sizing & Request Engineering Audit ➔' ?></span>
                        <span class="spinner" style="display: none; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; margin-left: 8px;"></span>
                      </button>
                    </form>
                    <div id="discovery-form-feedback" class="cta-form-feedback mt-3 text-center" style="display: none; padding: 0.85rem 1rem; border-radius: 30px; font-weight: 600;"></div>
                  </div>
                </div>

              </div> <!-- /discovery-dashboard -->

              <div class="wizard-restart-row mt-4 text-center">
                <button type="button" class="btn-skip-link" id="btn-reset-discovery">
                  🔄 <?= $active_lang === 'ar' ? 'إعادة الحساب من البداية' : 'Start Over' ?>
                </button>
              </div>

            </div> <!-- /residential-discovery-results -->
          </div>
        </div>

        <!-- Footer Controls Container -->
        <div class="wizard-footer-controls">
          <div class="form-group-field">
            <select id="property-type" aria-label="<?= $lang['calc_prop_residential'] ?>">
              <option value="residential"><?= $lang['calc_prop_residential'] ?></option>
              <option value="commercial"><?= $lang['calc_prop_commercial'] ?></option>
              <option value="industrial"><?= $lang['calc_prop_industrial'] ?></option>
            </select>
          </div>

          <div class="form-group-field">
            <select id="location" aria-label="<?= $lang['calc_loc_muscat'] ?>">
              <option value="muscat"><?= $lang['calc_loc_muscat'] ?></option>
              <option value="dhofar"><?= $lang['calc_loc_dhofar'] ?></option>
              <option value="batinah"><?= $lang['calc_loc_batinah'] ?></option>
              <option value="other"><?= $lang['calc_loc_other'] ?></option>
            </select>
          </div>
        </div>
        <!-- END Footer Controls -->
      </div>
    </div>
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
        <div class="pdf-modal-footer" style="padding: 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
          <p style="margin-bottom: 1rem; font-weight: 600; color: #1e293b;">Would you like our team to send pricing and availability?</p>
          <button type="button" id="pdf-request-pricing-btn" class="btn btn-primary" style="padding: 0.75rem 2rem;">Yes, Request Pricing</button>
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
  <script src="script.js?v=5.5" defer></script>

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






