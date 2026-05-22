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
  <link rel="apple-touch-icon" type="image/x-icon"
    href="https://www.ctechoman.com/public/apple-touch-icon-57x57-precomposed.png">
  <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72"
    href="https://www.ctechoman.com/public/apple-touch-icon-72x72-precomposed.png">
  <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114"
    href="https://www.ctechoman.com/public/apple-touch-icon-114x114-precomposed.png">
  <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144"
    href="https://www.ctechoman.com/public/apple-touch-icon-144x144-precomposed.png">

  <!-- Preconnect and Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&family=Tajawal:wght@300;400;500;700;900&family=Noto+Sans+Arabic:wght@300;400;500;700;900&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="style.css?v=3.0">
  <link rel="stylesheet" href="chatbot.css?v=3.0">
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
      <a href="#" class="logo">
        <img src="https://www.ctechoman.com/public/logo.webp" alt="Concept Technologies LLC Logo" width="160" height="48">
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
        <p><?= $lang['hero_desc'] ?></p>

        <div class="hero-actions">
          <a href="#contact" class="btn btn-hero-primary"><?= $lang['hero_btn'] ?></a>

        </div>

        <div class="hero-slider-nav">
          <div class="slider-item active">
            <span class="slider-num">1.</span>
            <div class="slider-line"></div>
          </div>
          <div class="slider-item">
            <span class="slider-num">2.</span>
            <div class="slider-line"></div>
          </div>
          <div class="slider-item">
            <span class="slider-num">3.</span>
            <div class="slider-line"></div>
          </div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="lightning-bg"></div>
        <img src="lightbulb.webp" alt="Renewable Energy" class="lightbulb-img" width="500" height="500" fetchpriority="high">
      </div>
    </div>
  </section>

  <!-- Solar Calculator -->
  <section id="calculator" class="calculator-section container reveal">
    <div class="calculator-wrapper">
      <div class="calc-info">
        <h2><?= $lang['calc_title'] ?></h2>
        <p class="text-muted"><?= $lang['calc_desc'] ?></p>

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

      <div class="calc-form">
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

        <div class="form-row">
          <select id="property-type" aria-label="<?= $lang['calc_prop_residential'] ?>">
            <option value="residential"><?= $lang['calc_prop_residential'] ?></option>
            <option value="commercial"><?= $lang['calc_prop_commercial'] ?></option>
            <option value="industrial"><?= $lang['calc_prop_industrial'] ?></option>
          </select>

          <select id="location" aria-label="<?= $lang['calc_loc_muscat'] ?>">
            <option value="muscat"><?= $lang['calc_loc_muscat'] ?></option>
            <option value="dhofar"><?= $lang['calc_loc_dhofar'] ?></option>
            <option value="batinah"><?= $lang['calc_loc_batinah'] ?></option>
            <option value="other"><?= $lang['calc_loc_other'] ?></option>
          </select>
        </div>
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
        <img src="img2.webp" alt="Solar Installation" width="480" height="360">
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
      <a href="#projects" class="btn btn-dark-green"><?= $lang['gal_btn'] ?></a>
    </div>

    <div class="gallery-grid reveal delay-100">
      <div class="gallery-col">
        <div class="gallery-item tall">
          <img
            src="https://images.unsplash.com/photo-1509391366360-2e959784a276?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=70&fm=webp"
            alt="Solar Project 1" width="600" height="800">
        </div>
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1592833159155-c62df1b65634?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=70&fm=webp"
            alt="Solar Project 2" width="600" height="400">
        </div>
      </div>
      <div class="gallery-col">
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=70&fm=webp"
            alt="Solar Project 3" width="600" height="400">
        </div>
        <div class="gallery-item tall">
          <img
            src="https://plus.unsplash.com/premium_photo-1679917152396-4b18accacb9d?q=70&w=600&fm=webp&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="Solar Project 4" width="600" height="800">
        </div>
      </div>
      <div class="gallery-col">
        <div class="gallery-item tall">
          <img
            src="https://plus.unsplash.com/premium_photo-1682148205811-e8a8ce759f4b?q=70&w=600&fm=webp&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="Solar Project 5" width="600" height="800">
        </div>
        <div class="gallery-item short">
          <img
            src="https://images.unsplash.com/photo-1613665813446-82a78c468a1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=70&fm=webp"
            alt="Solar Project 6" width="600" height="400">
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
        <img src="solar_panel.webp" alt="Solar Panel" width="300" height="338"
          style="width: 100%; max-width: 300px; position: relative; z-index: 2;">

        <img src="cloud1.webp" class="cloud-anim cloud-1" alt="Cloud" width="120" height="60">
        <img src="cloud2.webp" class="cloud-anim cloud-2" alt="Cloud" width="120" height="60">
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

    <div class="datasheet-grid">
      <!-- Huawei Card -->
      <div class="datasheet-card">
        <div class="datasheet-card-accent"></div>
        <div class="datasheet-icon-wrapper">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="datasheet-icon">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </div>
        <div class="datasheet-content">
          <h3><?= $lang['ds_1_title'] ?></h3>
          <p><?= $lang['ds_1_desc'] ?></p>
          <div class="datasheet-specs">
            <span class="ds-spec-badge"><?= $lang['ds_1_spec_1'] ?></span>
            <span class="ds-spec-badge"><?= $lang['ds_1_spec_2'] ?></span>
          </div>
        </div>
        <a href="download.php?product=huawei_sun2000" class="btn-ds-download" target="_blank" rel="noopener">
          <span><?= $lang['datasheet_download_btn'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </div>

      <!-- Canadian Solar Card -->
      <div class="datasheet-card">
        <div class="datasheet-card-accent"></div>
        <div class="datasheet-icon-wrapper">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="datasheet-icon">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </div>
        <div class="datasheet-content">
          <h3><?= $lang['ds_2_title'] ?></h3>
          <p><?= $lang['ds_2_desc'] ?></p>
          <div class="datasheet-specs">
            <span class="ds-spec-badge"><?= $lang['ds_2_spec_1'] ?></span>
            <span class="ds-spec-badge"><?= $lang['ds_2_spec_2'] ?></span>
          </div>
        </div>
        <a href="download.php?product=canadian_solar" class="btn-ds-download" target="_blank" rel="noopener">
          <span><?= $lang['datasheet_download_btn'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </div>

      <!-- Deye Card -->
      <div class="datasheet-card">
        <div class="datasheet-card-accent"></div>
        <div class="datasheet-icon-wrapper">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="datasheet-icon">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </div>
        <div class="datasheet-content">
          <h3><?= $lang['ds_3_title'] ?></h3>
          <p><?= $lang['ds_3_desc'] ?></p>
          <div class="datasheet-specs">
            <span class="ds-spec-badge"><?= $lang['ds_3_spec_1'] ?></span>
            <span class="ds-spec-badge"><?= $lang['ds_3_spec_2'] ?></span>
          </div>
        </div>
        <a href="download.php?product=deye_hybrid" class="btn-ds-download" target="_blank" rel="noopener">
          <span><?= $lang['datasheet_download_btn'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </div>

      <!-- Concept CC Card -->
      <div class="datasheet-card">
        <div class="datasheet-card-accent"></div>
        <div class="datasheet-icon-wrapper">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="datasheet-icon">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </div>
        <div class="datasheet-content">
          <h3><?= $lang['ds_4_title'] ?></h3>
          <p><?= $lang['ds_4_desc'] ?></p>
          <div class="datasheet-specs">
            <span class="ds-spec-badge"><?= $lang['ds_4_spec_1'] ?></span>
            <span class="ds-spec-badge"><?= $lang['ds_4_spec_2'] ?></span>
          </div>
        </div>
        <a href="download.php?product=concept_mppt" class="btn-ds-download" target="_blank" rel="noopener">
          <span><?= $lang['datasheet_download_btn'] ?></span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </div>
    </div>
  </section>

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



  <script src="calculator-engine.js?v=3.0" defer></script>
  <script src="analytics.js?v=3.0" defer></script>
  <script src="script.js?v=3.0" defer></script>
  <script src="chatbot.js?v=3.0" defer></script>
</body>

</html>

