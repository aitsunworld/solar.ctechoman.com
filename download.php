<?php
session_start();
$active_lang = $_SESSION['lang'] ?? 'en';

// Load constants and translation file for metadata
$constants = require_once "constants.php";
$lang = require_once "lang/{$active_lang}.php";

// Get requested product
$product_key = $_GET['product'] ?? '';

// Technical parameters database for dynamic PDF compiling
$products = [
    'huawei_sun2000' => [
        'title' => $lang['ds_1_title'] ?? 'Huawei SUN2000 Inverter',
        'desc' => $lang['ds_1_desc'] ?? 'High-efficiency smart string inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Max. Efficiency', '98.6%'],
            ['European Efficiency', '98.3%'],
            ['Max. Input Voltage', '1100 V'],
            ['MPPT Operating Range', '160 V - 950 V'],
            ['Rated Output Power', '50 kW / 100 kW'],
            ['Grid Standards', 'DCRP Oman Approved'],
            ['Cooling Method', 'Smart Forced Air Cooling'],
            ['Protection Rating', 'IP66 Waterproof'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    'canadian_solar' => [
        'title' => $lang['ds_2_title'] ?? 'Canadian Solar BiHiKu7',
        'desc' => $lang['ds_2_desc'] ?? 'Ultra-high power bifacial monocrystalline modules.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Nominal Max. Power', '650W - 670W'],
            ['Opt. Operating Voltage', '38.5 V'],
            ['Opt. Operating Current', '17.02 A'],
            ['Module Efficiency', '21.2%'],
            ['Operating Temp.', '-40C to +85C'],
            ['Max. System Voltage', '1500 V (IEC/UL)'],
            ['Bifaciality Rate', '70% (+- 5%)'],
            ['Wind / Snow Load', '2400 Pa / 5400 Pa'],
            ['Performance Warranty', '25 Years Linear'],
        ]
    ],
    'deye_hybrid' => [
        'title' => $lang['ds_3_title'] ?? 'Deye Hybrid Smart Inverter',
        'desc' => $lang['ds_3_desc'] ?? 'Battery-ready hybrid inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Max. DC Input Power', '15600 W'],
            ['DC Input Voltage', '370 V (100V - 500V)'],
            ['Max. Charge/Discharge', '220 A / 220 A'],
            ['Charging Curve', '3-Stage / Equalization'],
            ['Compatible Battery', 'Lithium-Ion / Lead-Acid'],
            ['Output Frequency', '50 / 60 Hz (Oman Grid)'],
            ['Max. Efficiency', '97.6%'],
            ['Transfer Time', '< 4 ms (UPS Mode)'],
            ['Standard Warranty', '5 Years'],
        ]
    ],
    'concept_mppt' => [
        'title' => $lang['ds_4_title'] ?? 'Concept Custom Smart MPPT Controller',
        'desc' => $lang['ds_4_desc'] ?? 'Heavy-duty off-grid solar charge controller.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Tracking Efficiency', '>= 99.5%'],
            ['Max. PV Input Power', '4800 W'],
            ['Max. PV Voltage', '150 V (Voc)'],
            ['System Battery Voltage', '12V/24V/36V/48V Auto'],
            ['Rated Charge Current', '80 A / 100 A'],
            ['Conversion Efficiency', 'Up to 98%'],
            ['Protection Features', 'Over-temp, Short-circuit, Reverse'],
            ['Communication Interface', 'RS485 Modbus Telemetry'],
            ['Warranty', '5 Years Certified'],
        ]
    ],
    // Sungrow Inverter
    'sungrow_sg110cx' => [
        'title' => $lang['ds_sungrow_inv_title'] ?? 'Sungrow SG110CX Inverter',
        'desc' => $lang['ds_sungrow_inv_desc'] ?? 'Multi-MPPT grid-tied string inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Max. Input Voltage', '1100 V'],
            ['MPPT Number', '9'],
            ['Rated AC Output Power', '110 kW'],
            ['Max. Efficiency', '98.7%'],
            ['European Efficiency', '98.5%'],
            ['Grid Standards', 'DCRP Approved'],
            ['Cooling Method', 'Smart Forced Air Cooling'],
            ['Protection Rating', 'IP66 Waterproof'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Solis Inverter
    'solis_s5' => [
        'title' => $lang['ds_solis_inv_title'] ?? 'Solis S5-GR3P20K Inverter',
        'desc' => $lang['ds_solis_inv_desc'] ?? 'High frequency three-phase inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Max. Input Voltage', '1100 V'],
            ['Rated AC Output Power', '20 kW'],
            ['MPPT Number', '2'],
            ['Max. Efficiency', '98.5%'],
            ['European Efficiency', '98.0%'],
            ['Grid Standards', 'DCRP Oman Compliant'],
            ['Cooling Method', 'Natural Convection'],
            ['Protection Rating', 'IP66 Waterproof'],
            ['Standard Warranty', '5 Years'],
        ]
    ],
    // Canadian Inverter
    'canadian_solar_inv' => [
        'title' => $lang['ds_canadian_inv_title'] ?? 'Canadian Solar CSI-50K Inverter',
        'desc' => $lang['ds_canadian_inv_desc'] ?? 'Commercial string inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Max. Input Voltage', '1000 V'],
            ['Rated AC Output Power', '50 kW'],
            ['MPPT Number', '4'],
            ['Max. Efficiency', '98.8%'],
            ['European Efficiency', '98.3%'],
            ['Grid Standards', 'APSR / DCRP Oman Approved'],
            ['Cooling Method', 'Intelligent Fan Cooling'],
            ['Protection Rating', 'IP65 Waterproof'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Power & Sun Inverter
    'power_sun_inv' => [
        'title' => $lang['ds_powersun_inv_title'] ?? 'Power & Sun PS-INV-10K Inverter',
        'desc' => $lang['ds_powersun_inv_desc'] ?? 'Robust off-grid solar inverter.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Rated Output Power', '10 kW'],
            ['System Battery Voltage', '48 VDC'],
            ['Built-in MPPT Controller', '120 A / 150 VDC'],
            ['Conversion Efficiency', '93%'],
            ['Output Waveform', 'Pure Sine Wave'],
            ['Output Voltage', '220VAC / 230VAC'],
            ['Protection Features', 'Over-temp, Short-circuit, Overload'],
            ['Standard Warranty', '5 Years'],
        ]
    ],
    // Trina Solar Panel
    'trina_vertex' => [
        'title' => $lang['ds_trina_panel_title'] ?? 'Trina Solar Vertex S+ (440W+)',
        'desc' => $lang['ds_trina_panel_desc'] ?? 'Bifacial dual-glass monocrystalline solar module.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Nominal Max. Power', '440W - 450W'],
            ['Module Efficiency', '22.0%'],
            ['Opt. Operating Voltage', '44.0 V'],
            ['Opt. Operating Current', '10.01 A'],
            ['Operating Temp.', '-40C to +85C'],
            ['Max. System Voltage', '1500 V (IEC)'],
            ['Glass thickness', '1.6mm + 1.6mm Dual Glass'],
            ['Wind / Snow Load', '2400 Pa / 5400 Pa'],
            ['Performance Warranty', '25 Years Linear'],
        ]
    ],
    // LONGi Panel
    'longi_himo6' => [
        'title' => $lang['ds_longi_panel_title'] ?? 'LONGi Hi-MO 6 Explorer (580W+)',
        'desc' => $lang['ds_longi_panel_desc'] ?? 'Next-generation HPBC cell technology panel.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Nominal Max. Power', '580W - 590W'],
            ['Module Efficiency', '22.5%'],
            ['Opt. Operating Voltage', '44.5 V'],
            ['Opt. Operating Current', '13.04 A'],
            ['Operating Temp.', '-40C to +85C'],
            ['Max. System Voltage', '1500 V (IEC/UL)'],
            ['Cell Type', 'HPBC Monocrystalline'],
            ['Temp. Coefficient', '-0.29% / C'],
            ['Performance Warranty', '25 Years Linear'],
        ]
    ],
    // Jinko Panel
    'jinko_tiger' => [
        'title' => $lang['ds_jinko_panel_title'] ?? 'Jinko Solar Tiger Neo N-type (600W+)',
        'desc' => $lang['ds_jinko_panel_desc'] ?? 'High efficiency N-type cell technology panel.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Nominal Max. Power', '600W - 610W'],
            ['Module Efficiency', '22.2%'],
            ['Opt. Operating Voltage', '42.2 V'],
            ['Opt. Operating Current', '14.22 A'],
            ['Operating Temp.', '-40C to +85C'],
            ['Max. System Voltage', '1500 V (IEC)'],
            ['Bifaciality Rate', '80% (+- 5%)'],
            ['Temp. Coefficient', '-0.30% / C'],
            ['Performance Warranty', '25 Years Linear'],
        ]
    ],
    // JA Solar Panel
    'ja_solar_blue' => [
        'title' => $lang['ds_ja_panel_title'] ?? 'JA Solar DeepBlue 3.0 (550W+)',
        'desc' => $lang['ds_ja_panel_desc'] ?? 'Monocrystalline multi-busbar PERC panel.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Nominal Max. Power', '550W - 560W'],
            ['Module Efficiency', '21.3%'],
            ['Opt. Operating Voltage', '41.9 V'],
            ['Opt. Operating Current', '13.11 A'],
            ['Operating Temp.', '-40C to +85C'],
            ['Max. System Voltage', '1500 V (IEC)'],
            ['Cell Arrangement', '144 (6 x 24)'],
            ['Wind / Snow Load', '2400 Pa / 5400 Pa'],
            ['Performance Warranty', '25 Years Linear'],
        ]
    ],
    // Canadian Solar Battery
    'canadian_solar_battery' => [
        'title' => $lang['ds_canadian_bat_title'] ?? 'Canadian Solar EP Cube ESS',
        'desc' => $lang['ds_canadian_bat_desc'] ?? 'Flexible LFP storage system.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Usable Capacity', '9.9 kWh - 19.9 kWh'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Nominal Output Power', '7.6 kW (Grid-tied)'],
            ['Max. Output Power', '10.2 kW (Off-grid peak)'],
            ['Round Trip Efficiency', '>= 95%'],
            ['Cooling Method', 'Natural Convection'],
            ['Protection Rating', 'IP65 Waterproof (Outdoor)'],
            ['Dimensions', '600 x 950 x 220 mm'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Solis Battery
    'solis_flexi' => [
        'title' => $lang['ds_solis_bat_title'] ?? 'Solis Flexi-One Battery (5.12kWh)',
        'desc' => $lang['ds_solis_bat_desc'] ?? 'Lithium iron phosphate battery module.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Battery Capacity', '5.12 kWh'],
            ['Nominal Voltage', '51.2 VDC'],
            ['Usable Capacity', '4.6 kWh (90% DoD)'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Max. Charge Current', '50 A'],
            ['Max. Discharge Current', '80 A'],
            ['Active Protection', 'Smart BMS System'],
            ['Cooling Method', 'Natural Convection'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Deye Battery
    'deye_bos_g' => [
        'title' => $lang['ds_deye_bat_title'] ?? 'Deye BOS-G High Voltage Battery',
        'desc' => $lang['ds_deye_bat_desc'] ?? 'High voltage LFP storage battery system.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Module Capacity', '5.12 kWh'],
            ['System Voltage Range', '120V - 750V (Modular)'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Recommended DoD', '90%'],
            ['Max. Charge/Discharge', '100 A'],
            ['BMS Communication', 'CAN2.0 / RS485'],
            ['Thermal Regulation', 'Self-heating & Air cooling'],
            ['Cycle Life', '>= 6000 cycles (25C)'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Jebel Battery
    'jebel_battery' => [
        'title' => $lang['ds_jebel_bat_title'] ?? 'Jebel J-LIPO Battery (5kWh)',
        'desc' => $lang['ds_jebel_bat_desc'] ?? 'Wall-mounted heavy-duty LiFePO4 battery pack.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Battery Capacity', '5.12 kWh'],
            ['Nominal Voltage', '51.2 VDC'],
            ['Usable Capacity', '4.6 kWh (90% DoD)'],
            ['Max. Continuous Charge', '100 A'],
            ['Max. Continuous Discharge', '100 A'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Operating Temp.', '-10C to +60C (Extreme Heat spec)'],
            ['Cycle Life', '>= 5500 cycles (25C)'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // LONGi Battery
    'longi_battery' => [
        'title' => $lang['ds_longi_bat_title'] ?? 'LONGi Solar-Home Battery (15kWh)',
        'desc' => $lang['ds_longi_bat_desc'] ?? 'High-density LFP home backup battery pack.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Battery Capacity', '15.0 kWh'],
            ['Nominal Voltage', '51.2 V'],
            ['Usable Capacity', '13.5 kWh'],
            ['Max. Continuous Output', '7.5 kW'],
            ['Peak Output Power', '10.0 kW (10s)'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Operating Temp.', '-20C to +55C'],
            ['Protection Rating', 'IP65 Water/Dust proof'],
            ['Standard Warranty', '10 Years'],
        ]
    ],
    // Power & Sun Battery
    'power_sun_ess' => [
        'title' => $lang['ds_powersun_bat_title'] ?? 'Power & Sun ESS Rack Battery (5.12kWh)',
        'desc' => $lang['ds_powersun_bat_desc'] ?? 'Industrial LFP storage rack module.',
        'specs' => [
            ['Parameter', 'Value'],
            ['Battery Capacity', '5.12 kWh'],
            ['Nominal Voltage', '51.2 VDC'],
            ['Usable Capacity', '4.1 kWh (80% DoD)'],
            ['Battery Chemistry', 'LiFePO4 (LFP)'],
            ['Max. Continuous Charge', '50 A'],
            ['Max. Continuous Discharge', '50 A'],
            ['Form Factor', 'Standard 19-inch 3U Rackmount'],
            ['Cooling Method', 'Passive Cooling'],
            ['Standard Warranty', '5 Years'],
        ]
    ],
];

// Fallback if product not found
if (!isset($products[$product_key])) {
    $product_key = 'huawei_sun2000';
}

$product = $products[$product_key];

// Define PDF structure and build dynamic stream
class SimplePDF {
    private $objects = [];
    private $offsets = [];
    private $buffer = '';

    public function __construct() {
        $this->buffer = "%PDF-1.4\r\n";
    }

    private function addObj($content) {
        $id = count($this->objects) + 1;
        $this->offsets[$id] = strlen($this->buffer);
        $this->objects[$id] = $content;
        $this->buffer .= "{$id} 0 obj\r\n{$content}\r\nendobj\r\n";
        return $id;
    }

    private function escapePDFText($text) {
        // Strip out non-ASCII or convert to readable representation
        $text = str_replace(array('(', ')', '\\'), array('\\(', '\\)', '\\\\'), $text);
        return preg_replace('/[^\x20-\x7E]/', '', $text); // Keep printable ASCII
    }

    public function generate($product, $lang, $active_lang) {
        // Fonts definitions
        $fBold = $this->addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");
        $fRegular = $this->addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");

        // Document Metadata and translation clean layout
        $title = $this->escapePDFText($product['title']);
        $desc = $this->escapePDFText($product['desc']);
        
        $brandText = "CONCEPT TECHNOLOGIES LLC  Oman Solar Solutions";
        $contactText = "Phone: 968 24701234 | Email: info@ctechoman.com | Web: solar.ctechoman.com";
        $specHeader = "TECHNICAL SPECIFICATION SHEET";
        $certText = "DCRP Approved  ISO 9001:2015 Certified System  Premium Renewable Equipment";
        $footerText = "Page 1 of 1  Official Resource (c) 2026 Concept Technologies LLC. All rights reserved.";

        // Compile page visual content stream
        $stream = "";
        
        // Header Banner Background (Navy Navy/Blue)
        $stream .= "0.1 0.3 0.6 rg\r\n"; // Fill Navy
        $stream .= "30 750 535 55 re\r\n"; // Rect at top
        $stream .= "f\r\n";
        
        // Brand Title inside Header
        $stream .= "BT\r\n";
        $stream .= "/F" . $fBold . " 12 Tf\r\n";
        $stream .= "1.0 1.0 1.0 rg\r\n"; // White text
        $stream .= "45 782 Td\r\n";
        $stream .= "(" . $this->escapePDFText($brandText) . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Subtitle inside Header
        $stream .= "BT\r\n";
        $stream .= "/F" . $fRegular . " 9 Tf\r\n";
        $stream .= "0.8 0.9 1.0 rg\r\n"; // Light blue text
        $stream .= "45 765 Td\r\n";
        $stream .= "(" . $this->escapePDFText($contactText) . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Spec Title
        $stream .= "BT\r\n";
        $stream .= "/F" . $fBold . " 16 Tf\r\n";
        $stream .= "0.1 0.1 0.1 rg\r\n"; // Dark Gray
        $stream .= "30 710 Td\r\n";
        $stream .= "(" . $title . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Subheader Category
        $stream .= "BT\r\n";
        $stream .= "/F" . $fBold . " 9 Tf\r\n";
        $stream .= "0.4 0.4 0.4 rg\r\n"; // Medium Gray
        $stream .= "30 690 Td\r\n";
        $stream .= "(" . $specHeader . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Description Paragraph
        $stream .= "BT\r\n";
        $stream .= "/F" . $fRegular . " 10 Tf\r\n";
        $stream .= "0.2 0.2 0.2 rg\r\n";
        $stream .= "30 670 Td\r\n";
        $stream .= "(" . $desc . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Dynamic Specification Table Grid Setup
        $y = 635;
        $rowHeight = 22;
        $col1Width = 230;
        $col2Width = 305;
        $tableWidth = 535;

        // Table Header fill (Light gray banner)
        $stream .= "0.92 0.94 0.96 rg\r\n";
        $stream .= "30 " . ($y - 2) . " " . $tableWidth . " " . $rowHeight . " re\r\n";
        $stream .= "f\r\n";

        // Grid lines drawing
        $stream .= "0.8 0.8 0.8 RG\r\n"; // Gray border stroke
        $stream .= "0.75 w\r\n";

        // Horizontal Grid Rows
        $numRows = count($product['specs']);
        for ($i = 0; $i <= $numRows; $i++) {
            $currentY = $y - ($i * $rowHeight);
            $stream .= "30 " . $currentY . " m " . (30 + $tableWidth) . " " . $currentY . " l s\r\n";
        }
        
        // Vertical grid lines
        $stream .= "30 " . $y . " m 30 " . ($y - ($numRows * $rowHeight)) . " l s\r\n";
        $stream .= "260 " . $y . " m 260 " . ($y - ($numRows * $rowHeight)) . " l s\r\n";
        $stream .= "565 " . $y . " m 565 " . ($y - ($numRows * $rowHeight)) . " l s\r\n";

        // Render Table Row Contents
        foreach ($product['specs'] as $index => $row) {
            $isHeader = ($index === 0);
            $rowY = $y - ($index * $rowHeight) - 15;
            $colVal1 = $this->escapePDFText($row[0]);
            $colVal2 = $this->escapePDFText($row[1]);

            // Set Font based on row type
            $fontObj = $isHeader ? $fBold : $fRegular;
            $color = $isHeader ? "0.1 0.1 0.1 rg\r\n" : "0.2 0.2 0.2 rg\r\n";

            // Col 1 Text
            $stream .= "BT\r\n";
            $stream .= "/F" . $fontObj . " 9.5 Tf\r\n";
            $stream .= $color;
            $stream .= "40 " . $rowY . " Td\r\n";
            $stream .= "(" . $colVal1 . ") Tj\r\n";
            $stream .= "ET\r\n";

            // Col 2 Text
            $stream .= "BT\r\n";
            $stream .= "/F" . $fontObj . " 9.5 Tf\r\n";
            $stream .= $color;
            $stream .= "270 " . $rowY . " Td\r\n";
            $stream .= "(" . $colVal2 . ") Tj\r\n";
            $stream .= "ET\r\n";
        }

        // Certification text block at bottom
        $certY = $y - ($numRows * $rowHeight) - 30;
        $stream .= "BT\r\n";
        $stream .= "/F" . $fBold . " 9 Tf\r\n";
        $stream .= "0.1 0.6 0.3 rg\r\n"; // Dynamic Green color
        $stream .= "30 " . $certY . " Td\r\n";
        $stream .= "(" . $this->escapePDFText($certText) . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Footer block line separator
        $stream .= "0.9 0.9 0.9 RG\r\n";
        $stream .= "30 60 m 565 60 l s\r\n";

        // Footer Text
        $stream .= "BT\r\n";
        $stream .= "/F" . $fRegular . " 8 Tf\r\n";
        $stream .= "0.5 0.5 0.5 rg\r\n";
        $stream .= "30 45 Td\r\n";
        $stream .= "(" . $this->escapePDFText($footerText) . ") Tj\r\n";
        $stream .= "ET\r\n";

        // Add compiled contents stream object
        $cStream = $this->addObj("<< /Length " . strlen($stream) . " >>\r\nstream\r\n" . $stream . "endstream");

        // Resources Catalog
        $res = $this->addObj("<< /Font << /F" . $fBold . " " . $fBold . " 0 R /F" . $fRegular . " " . $fRegular . " 0 R >> >>");

        // Main Page Catalog structure
        $page = $this->addObj("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources " . $res . " 0 R /Contents " . $cStream . " 0 R >>");

        // Document Catalog Root and Pages references
        $pages = $this->addObj("<< /Type /Pages /Kids [" . $page . " 0 R] /Count 1 >>");
        $catalog = $this->addObj("<< /Type /Catalog /Pages " . $pages . " 0 R >>");

        // Finalize cross-reference table index
        $startxref = strlen($this->buffer);
        $this->buffer .= "xref\r\n0 " . (count($this->objects) + 1) . "\r\n";
        $this->buffer .= "0000000000 65535 f\r\n";

        foreach ($this->offsets as $id => $offset) {
            $this->buffer .= sprintf("%010d 00000 n\r\n", $offset);
        }

        $this->buffer .= "trailer\r\n<< /Size " . (count($this->objects) + 1) . " /Root " . $catalog . " 0 R >>\r\n";
        $this->buffer .= "startxref\r\n" . $startxref . "\r\n";
        $this->buffer .= "%%EOF\r\n";

        return $this->buffer;
    }
}

// Generate the PDF stream
$pdfEngine = new SimplePDF();
$pdfBinary = $pdfEngine->generate($product, $lang, $active_lang);

// Send clean headers to stream the PDF
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfBinary));
header('Content-Disposition: attachment; filename="' . $product_key . '_datasheet_' . $active_lang . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $pdfBinary;
exit;
