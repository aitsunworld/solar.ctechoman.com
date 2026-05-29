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
    'huawei_sun2000'        => 'https://solar.huawei.com/en/download?p=/~/media/Solar/attachment/pdf/ee/datasheet/SUN2000-50KTL-M3.pdf',

    // Sungrow — support search for SG110CX
    'sungrow_sg110cx'       => 'https://support.sungrowpower.com/search#q=SG110CX&t=All',

    // Solis — download centre
    'solis_s5'              => 'https://www.solisinverters.com/download.html',
    'solis_flexi'           => 'https://www.solisinverters.com/download.html',

    // Canadian Solar — download centre
    'canadian_solar'        => 'https://www.csisolar.com/download-center/',
    'canadian_solar_inv'    => 'https://www.csisolar.com/download-center/',
    'canadian_solar_battery'=> 'https://www.epcube.com/support/',

    // Deye — download portals
    'deye_hybrid'           => 'https://www.deyeinverter.com/download/',
    'deye_bos_g'            => 'https://deyeess.com/downloads/',

    // Power & Sun — brand website
    'power_sun_inv'         => 'https://powernsun.com/',
    'power_sun_ess'         => 'https://powernsun.com/',

    // Trina Solar — downloads
    'trina_vertex'          => 'https://www.trinasolar.com/en-glb/resources/downloads',

    // LONGi — document centre
    'longi_himo6'           => 'https://www.longi.com/en/document/',
    'longi_battery'         => 'https://www.longi.com/en/document/',

    // Jinko Solar — download centre
    'jinko_tiger'           => 'https://www.jinkosolar.com/en/site/download',

    // JA Solar — product downloads
    'ja_solar_blue'         => 'https://www.jasolar.com/html/en/serve/download/',

    // Jebel — product page with download buttons
    'jebel_battery'         => 'https://jebel.ae/solar-battery/',

    // Concept — internal product; link to main corporate site
    'concept_mppt'          => 'https://www.ctechoman.com/',
];

$product_key = trim($_GET['product'] ?? '');

// Redirect to official URL or fall back to the main solar site
$destination = $official_urls[$product_key] ?? 'https://solar.ctechoman.com/';

header('Location: ' . $destination, true, 302);
exit;
