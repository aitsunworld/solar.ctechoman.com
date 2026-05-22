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
