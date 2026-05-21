<?php
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
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="style.css?v=2.4">
  <link rel="stylesheet" href="chatbot.css">
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
          <a href="?lang=ar" class="lang-btn" style="text-decoration:none;">Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©</a>
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
            <strong class="result-value text-eco" id="res-size" dir="ltr" style="display: inline-block;">0 kW</strong>
          </div>
          <div class="result-box">
            <span class="result-label"><?= $lang['calc_est_panels'] ?></span>
            <strong class="result-value" id="res-panels" dir="ltr" style="display: inline-block;">0</strong>
          </div>
          <div class="result-box">
            <span class="result-label"><?= $lang['calc_est_cost'] ?></span>
            <strong class="result-value" id="res-cost" dir="ltr" style="display: inline-block;">0 OMR</strong>
          </div>
          <div class="result-box highlight">
            <span class="result-label"><?= $lang['calc_yearly_savings'] ?></span>
            <strong class="result-value" id="res-savings" dir="ltr" style="display: inline-block;">0 OMR</strong>
          </div>
        </div>
        <button id="calc-explain-btn" class="btn btn-secondary mt-3" style="width: 100%; border: 1.5px solid var(--color-accent); background: transparent; color: var(--color-accent); font-weight: 600; padding: 0.8rem; border-radius: var(--radius-pill); cursor: pointer; transition: all 0.2s;">
          âœ¨ <?= $active_lang === 'ar' ? 'Ø§Ø´Ø±Ø­ Ù„ÙŠ Ø§Ù„Ù†ØªØ§Ø¦Ø¬ Ø¨Ù…Ø³ØªØ´Ø§Ø± Ø§Ù„Ø°ÙƒØ§Ø¡ Ø§Ù„Ø§ØµØ·Ù†Ø§Ø¹ÙŠ' : 'Explain results with AI Advisor' ?>
        </button>
        <p class="mt-3 text-muted" style="font-size: 0.8rem;"><?= $lang['calc_note'] ?></p>

        <div id="load-recommendations" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--color-border); width: 100%;">
          <h4 style="margin-bottom: 1rem; color: var(--color-primary); font-size: 1.1rem; text-align: start;"><?= $active_lang === 'ar' ? 'ØªÙˆØµÙŠØ§Øª Ø§Ù„Ù†Ø¸Ø§Ù… Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©' : 'Advanced System Recommendations' ?></h4>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="result-box" style="background: rgba(58, 141, 204, 0.05);">
              <span class="result-label"><?= $active_lang === 'ar' ? 'Ø­Ø¬Ù… Ø§Ù„Ø¹Ø§ÙƒØ³ Ø§Ù„Ù…Ù‚ØªØ±Ø­' : 'Recommended Inverter' ?></span>
              <strong class="result-value" id="res-inverter" style="color: var(--color-primary);">0 kW</strong>
            </div>
            <div class="result-box" style="background: rgba(62, 182, 73, 0.05);">
              <span class="result-label"><?= $active_lang === 'ar' ? 'Ø³Ø¹Ø© Ø§Ù„Ø¨Ø·Ø§Ø±ÙŠØ© Ø§Ù„Ù…Ù‚ØªØ±Ø­Ø©' : 'Battery Storage' ?></span>
              <strong class="result-value text-eco" id="res-battery">0 kWh</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="calc-form">
        <div class="calc-tabs" style="display: flex; gap: 1rem; margin-bottom: 2rem;">
          <button id="tab-bill" class="calc-tab active" style="flex: 1; padding: 0.75rem; border: none; border-radius: var(--radius-pill); background: var(--color-primary); color: #fff; font-weight: 600; cursor: pointer; transition: all 0.2s;">
            <?= $active_lang === 'ar' ? 'ØªÙ‚Ø¯ÙŠØ± Ø§Ù„ÙØ§ØªÙˆØ±Ø©' : 'Bill Estimator' ?>
          </button>
          <button id="tab-appliances" class="calc-tab" style="flex: 1; padding: 0.75rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: transparent; color: var(--color-text); font-weight: 600; cursor: pointer; transition: all 0.2s;">
            <?= $active_lang === 'ar' ? 'Ù…Ø¯Ù‚Ù‚ Ø§Ù„Ø£Ø¬Ù‡Ø²Ø©' : 'Appliance Auditor' ?>
          </button>
        </div>

        <div id="bill-inputs-container">
          <h3 class="mb-3"><?= $lang['calc_monthly_bill'] ?></h3>
          <div class="slider-container">
            <input type="range" id="bill-slider" min="10" max="1000" value="50" step="5" aria-label="<?= $lang['calc_monthly_bill'] ?>">
            <div class="slider-value"><span id="bill-display">50</span></div>
          </div>
        </div>

        <div id="appliance-inputs-container" style="display: none; max-height: 480px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem; text-align: start;">
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
      <p class="text-muted mt-2" style="font-size: 1.25rem;"><?= $lang['ben_desc'] ?></p>
    </div>

    <div class="staggered-grid">
      <div class="benefit-card reveal">
        <div class="icon-blob">ðŸ’°</div>
        <h3><?= $lang['ben_1_title'] ?></h3>
        <p class="text-muted"><?= $lang['ben_1_desc'] ?></p>
      </div>

      <div class="benefit-card reveal delay-100">
        <div class="icon-blob" style="background: #E0F2FE; color: #3a8dcc;">ðŸŒ</div>
        <h3><?= $lang['ben_2_title'] ?></h3>
        <p class="text-muted"><?= $lang['ben_2_desc'] ?></p>
      </div>

      <div class="benefit-card reveal delay-200">
        <div class="icon-blob" style="background: #F1F5F9; color: #444444;">ðŸ“ˆ</div>
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
        <span class="gallery-pre"><span class="pre-icon">â–</span> <?= $lang['gal_pre'] ?> <span
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
  <section id="why-choose-us" class="container section-padding text-center" style="text-align: center;">
    <div class="reveal">
      <span
        style="font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; color: var(--color-primary); text-transform: uppercase;"><?= $lang['why_pre'] ?></span>
      <h2 style="font-size: 3rem; margin-top: 0.5rem; margin-bottom: 4rem;"><?= $lang['why_title'] ?></h2>
    </div>

    <div class="choose-us-grid">
      <div class="choose-col left-col">
        <div class="choose-item reveal delay-100">
          <div class="choose-text">
            <h3><?= $lang['why_1_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_1_desc'] ?></p>
          </div>
          <div class="choose-icon">ðŸ…</div>
        </div>
        <div class="choose-item reveal delay-200">
          <div class="choose-text">
            <h3><?= $lang['why_2_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_2_desc'] ?></p>
          </div>
          <div class="choose-icon">ðŸ†</div>
        </div>
        <div class="choose-item reveal delay-300">
          <div class="choose-text">
            <h3><?= $lang['why_3_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_3_desc'] ?></p>
          </div>
          <div class="choose-icon">âš¡</div>
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
          <div class="choose-icon">â˜€ï¸</div>
          <div class="choose-text">
            <h3><?= $lang['why_4_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_4_desc'] ?></p>
          </div>
        </div>
        <div class="choose-item reveal delay-200">
          <div class="choose-icon">ðŸ‘¨â€ðŸ”¬</div>
          <div class="choose-text">
            <h3><?= $lang['why_5_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_5_desc'] ?></p>
          </div>
        </div>
        <div class="choose-item reveal delay-300">
          <div class="choose-icon">ðŸ‡´ðŸ‡²</div>
          <div class="choose-text">
            <h3><?= $lang['why_6_title'] ?></h3>
            <p class="text-muted"><?= $lang['why_6_desc'] ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Trusted Brands & Products -->
  <section id="partners" class="partners-section text-center reveal" style="text-align: center; padding: 5rem 0;">
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
  <section id="testimonials" class="container section-padding"
    style="background-color: var(--color-surface); border-radius: var(--radius-card); margin-top: 4rem; box-shadow: var(--shadow-soft);">
    <div class="section-title text-center reveal" style="margin: 0 auto 4rem auto;">
      <h2><?= $lang['test_title'] ?></h2>
      <p class="text-muted mt-2"><?= $lang['test_desc'] ?></p>
    </div>

    <div class="testimonials-grid">
      <div class="benefit-card reveal"
        style="padding: 2.5rem; display: flex; flex-direction: column; justify-content: space-between; background: var(--color-bg);">
        <div>
          <div style="color: var(--color-primary); font-size: 1.5rem; margin-bottom: 1rem;">â˜…â˜…â˜…â˜…â˜…</div>
          <p class="text-muted" style="font-style: italic; margin-bottom: 2rem;"><?= $lang['test_1_quote'] ?></p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div
            style="width: 50px; height: 50px; background: var(--color-primary); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
            SA</div>
          <div>
            <h3 style="margin: 0; font-size: 1rem; color: var(--color-text-dark);"><?= $lang['test_1_name'] ?></h3>
            <span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= $lang['test_1_role'] ?></span>
          </div>
        </div>
      </div>

      <div class="benefit-card reveal delay-100"
        style="padding: 2.5rem; display: flex; flex-direction: column; justify-content: space-between; background: var(--color-bg);">
        <div>
          <div style="color: var(--color-primary); font-size: 1.5rem; margin-bottom: 1rem;">â˜…â˜…â˜…â˜…â˜…</div>
          <p class="text-muted" style="font-style: italic; margin-bottom: 2rem;"><?= $lang['test_2_quote'] ?></p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div
            style="width: 50px; height: 50px; background: var(--color-accent); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
            MA</div>
          <div>
            <h3 style="margin: 0; font-size: 1rem; color: var(--color-text-dark);"><?= $lang['test_2_name'] ?></h3>
            <span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= $lang['test_2_role'] ?></span>
          </div>
        </div>
      </div>

      <div class="benefit-card reveal delay-200"
        style="padding: 2.5rem; display: flex; flex-direction: column; justify-content: space-between; background: var(--color-bg);">
        <div>
          <div style="color: var(--color-primary); font-size: 1.5rem; margin-bottom: 1rem;">â˜…â˜…â˜…â˜…â˜…</div>
          <p class="text-muted" style="font-style: italic; margin-bottom: 2rem;"><?= $lang['test_3_quote'] ?></p>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div
            style="width: 50px; height: 50px; background: var(--color-text-dark); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
            FA</div>
          <div>
            <h3 style="margin: 0; font-size: 1rem; color: var(--color-text-dark);"><?= $lang['test_3_name'] ?></h3>
            <span style="font-size: 0.8rem; color: var(--color-text-muted);"><?= $lang['test_3_role'] ?></span>
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

      <div class="contact-form reveal delay-200" style="background: var(--color-surface); padding: 3rem; border-radius: var(--radius-card); box-shadow: var(--shadow-soft); border: 1px solid var(--color-border); text-align: start;">
        <h3 class="mb-4" style="font-size: 1.5rem; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ø·Ù„Ø¨ Ø§Ø³ØªØ´Ø§Ø±Ø© Ù…Ø¬Ø§Ù†ÙŠØ©' : 'Request Free Solar Consultation' ?></h3>
        
        <form id="native-lead-form" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
          <input type="hidden" name="action" value="submit_lead">
          <input type="hidden" name="lang" value="<?= $active_lang ?>">
          
          <div class="form-group">
            <label for="lead-name" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ø§Ù„Ø§Ø³Ù… Ø§Ù„ÙƒØ§Ù…Ù„' : 'Full Name' ?> *</label>
            <input type="text" id="lead-name" name="name" required placeholder="<?= $active_lang === 'ar' ? 'Ø£Ø¯Ø®Ù„ Ø§Ø³Ù…Ùƒ Ø§Ù„ÙƒØ§Ù…Ù„' : 'Enter your full name' ?>" style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Full Name">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="lead-phone" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ø±Ù‚Ù… Ø§Ù„Ù‡Ø§ØªÙ (ÙˆØ§ØªØ³Ø§Ø¨)' : 'Phone Number (WhatsApp)' ?> *</label>
              <input type="tel" id="lead-phone" name="phone" required placeholder="968 XXXXXXXX" style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Phone Number">
            </div>
            <div class="form-group">
              <label for="lead-email" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ø§Ù„Ø¨Ø±ÙŠØ¯ Ø§Ù„Ø¥Ù„ÙƒØªØ±ÙˆÙ†ÙŠ' : 'Email Address' ?> *</label>
              <input type="email" id="lead-email" name="email" required placeholder="example@domain.com" style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Email Address">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="lead-gov" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ø§Ù„Ù…Ø­Ø§ÙØ¸Ø©' : 'Governorate' ?> *</label>
              <select id="lead-gov" name="governorate" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Governorate">
                <option value="muscat"><?= $lang['calc_loc_muscat'] ?></option>
                <option value="dhofar"><?= $lang['calc_loc_dhofar'] ?></option>
                <option value="batinah"><?= $lang['calc_loc_batinah'] ?></option>
                <option value="dakhiliyah"><?= $active_lang === 'ar' ? 'Ø§Ù„Ø¯Ø§Ø®Ù„ÙŠØ©' : 'Dakhiliyah' ?></option>
                <option value="other"><?= $lang['calc_loc_other'] ?></option>
              </select>
            </div>
            <div class="form-group">
              <label for="lead-prop" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ù†ÙˆØ¹ Ø§Ù„Ø¹Ù‚Ø§Ø±' : 'Property Type' ?> *</label>
              <select id="lead-prop" name="property_type" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Property Type">
                <option value="residential"><?= $lang['calc_prop_residential'] ?></option>
                <option value="commercial"><?= $lang['calc_prop_commercial'] ?></option>
                <option value="industrial"><?= $lang['calc_prop_industrial'] ?></option>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label for="lead-bill" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ù…ØªÙˆØ³Ø· Ø§Ù„ÙØ§ØªÙˆØ±Ø© Ø§Ù„ÙƒÙ‡Ø±Ø¨Ø§Ø¦ÙŠØ© (Ø±ÙŠØ§Ù„)' : 'Average Electricity Bill (OMR)' ?> *</label>
              <input type="number" id="lead-bill" name="monthly_bill" required min="10" max="5000" value="50" style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Electricity Bill">
            </div>
            <div class="form-group">
              <label for="lead-consult" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ù†ÙˆØ¹ Ø§Ù„Ø§Ø³ØªØ´Ø§Ø±Ø©' : 'Consultation Type' ?> *</label>
              <select id="lead-consult" name="consultation_type" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: var(--radius-pill); background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; transition: border-color 0.2s;" aria-label="Consultation Type">
                <option value="site_survey"><?= $active_lang === 'ar' ? 'Ù…Ø¹Ø§ÙŠÙ†Ø© Ù…ÙˆÙ‚Ø¹ Ù…Ø¬Ø§Ù†ÙŠØ©' : 'Free Site Survey' ?></option>
                <option value="video_call"><?= $active_lang === 'ar' ? 'Ø§Ø³ØªØ´Ø§Ø±Ø© Ø¨Ø§Ù„ÙÙŠØ¯ÙŠÙˆ Ø¹Ù† Ø¨Ø¹Ø¯' : 'Online Video Call' ?></option>
                <option value="office"><?= $active_lang === 'ar' ? 'Ø²ÙŠØ§Ø±Ø© Ù…ÙƒØªØ¨ ÙƒÙˆÙ†Ø³ÙŠØ¨Øª' : 'Office Consultation' ?></option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="lead-notes" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--color-text-dark);"><?= $active_lang === 'ar' ? 'Ù…Ù„Ø§Ø­Ø¸Ø§Øª Ø¥Ø¶Ø§ÙÙŠØ© (Ø§Ø®ØªÙŠØ§Ø±ÙŠ)' : 'Additional Notes (Optional)' ?></label>
            <textarea id="lead-notes" name="message" rows="3" placeholder="<?= $active_lang === 'ar' ? 'ØªØ­Ø¯Ø« Ø¥Ù„ÙŠÙ†Ø§ Ø¹Ù† Ø§Ø­ØªÙŠØ§Ø¬Ø§ØªÙƒ Ø§Ù„Ù…Ø­Ø¯Ø¯Ø©...' : 'Tell us about your specific solar needs...' ?>" style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--color-border); border-radius: 1rem; background: var(--color-bg); color: var(--color-text); font-size: 0.95rem; font-family: inherit; resize: vertical; transition: border-color 0.2s;" aria-label="Additional Notes"></textarea>
          </div>

          <!-- Anti-Spam HoneyPot -->
          <div style="display: none;">
            <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 600; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <span><?= $active_lang === 'ar' ? 'ØªØ£ÙƒÙŠØ¯ ÙˆØ­Ø¬Ø² Ø§Ù„Ø§Ø³ØªØ´Ø§Ø±Ø© ðŸš€' : 'Confirm & Book Consultation ðŸš€' ?></span>
            <span class="spinner" style="display: none; width: 20px; height: 20px; border: 3px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite;"></span>
          </button>

          <div id="form-feedback" class="mt-3 text-center" style="display: none; padding: 0.85rem 1rem; border-radius: var(--radius-pill); font-weight: 600; font-size: 0.95rem;"></div>
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



  <script src="calculator-engine.js?v=2.4" defer></script>
  <script src="analytics.js?v=2.4" defer></script>
  <script src="script.js?v=2.4" defer></script>
  <script src="chatbot.js?v=2.4" defer></script>
</body>

</html>
