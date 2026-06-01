<?php
/**
 * download.php
 * Concept Technologies LLC — Product Datasheet Redirect Layer
 *
 * All "Download Datasheet" clicks now redirect directly to the official
 * manufacturer's documentation portal or PDF. The custom on-the-fly
 * PDF generator has been removed.
 *
 * Mapping maintained here as a server-side fallback; the primary mapping
 * lives in script.js (window.OFFICIAL_DATASHEET_URLS) so the link is
 * resolved client-side before this file is ever hit.
 */

// Official manufacturer datasheet URLs per product key
$official_urls = [
    // Huawei — direct PDF
    'huawei_sun2000'        => 'https://solar.huawei.com/admin/asset/v1/pro/view/2d71dbdf468d4a1688883119fcdf576b.pdf',

    // Sungrow — support search for SG110CX
    'sungrow_sg110cx'       => 'https://support.sungrowpower.com/search#q=SG110CX&t=All',

    // Solis — download centre
    'solis_s5'              => 'https://www.solisinverters.com/service/',
    'solis_flexi'           => 'https://www.solisinverters.com/service/',

    // Canadian Solar — specific direct PDF (panel) and portals (inverter, battery)
    'canadian_solar'        => 'https://static.csisolar.com/wp-content/uploads/2020/10/07090707/CS-Datasheet-BiHiKu7_CS7N-MB-AG_v2.51_EN-33mm-frame-594-pcs-package.pdf',
    'canadian_solar_inv'    => 'https://www.csisolar.com/downloads/',
    'canadian_solar_battery'=> 'https://epcube.com/en-US/support/document',

    // Deye — download portals
    'deye_hybrid'           => 'https://www.deyeinverter.com/download/',
    'deye_bos_g'            => 'https://deyeess.com/downloads/',

    // Power & Sun — brand website
    'power_sun_inv'         => 'https://powernsun.com/',
    'power_sun_ess'         => 'https://powernsun.com/',

    // Trina Solar — specific direct PDF
    'trina_vertex'          => 'https://static.trinasolar.com/sites/default/files/Datasheet_Vertex%20S%2B_NEG9R.28_EN_2024_C_web.pdf',

    // LONGi — document centre
    'longi_himo6'           => 'https://www.longi.com/en/download/',
    'longi_battery'         => 'https://www.longi.com/en/download/',

    // Jinko Solar — specific product page
    'jinko_tiger'           => 'https://www.jinkosolar.eu/en/product/tiger-neo-n-type-72hl4-bdv/',

    // JA Solar — product downloads
    'ja_solar_blue'         => 'https://www.jasolar.com/html/en/serve/download/',

    // Jebel — product page with download buttons
    'jebel_battery'         => 'https://jebel.ae/solar-battery/'
];

$product_key = trim($_GET['product'] ?? '');

// Redirect to official URL or fall back to the main solar site
$destination = $official_urls[$product_key] ?? 'https://solar.ctechoman.com/';

header('Location: ' . $destination, true, 302);
exit;
