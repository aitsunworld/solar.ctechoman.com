<?php
/**
 * chatbot.php
 * Concept Technologies LLC — Solar Chatbot Backend Proxy
 * Securely forwards leads to n8n and calls Claude API for AI fallbacks.
 * Keeps API keys hidden from client side.
 */

// --- SECURITY & CORS HEADERS ---
header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Simple CORS: restrict to same origin
$allowed_origin = "https://" . $_SERVER['HTTP_HOST'];
header("Access-Control-Allow-Origin: " . $allowed_origin);
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

// --- CONFIGURATION ---
// In production, load these from environment variables (.env) or your server config!
define("N8N_WEBHOOK_URL", "https://n8n.aitsun.space/webhook/solar-lead");
define("ANTHROPIC_API_KEY", getenv("ANTHROPIC_API_KEY") ?: "YOUR_CLAUDE_API_KEY_HERE");

// Parse JSON Input Payload
$inputRaw = file_get_contents("php://input");
$payload = json_decode($inputRaw, true);

if (!$payload || !isset($payload['action'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid payload or missing action"]);
    exit;
}

$action = $payload['action'];

// --- ACTION ROUTER ---
switch ($action) {
    case "submit_lead":
        handleLeadSubmit($payload['data'] ?? []);
        break;

    case "ai_chat":
        handleAIChat($payload['prompt'] ?? "", $payload['lang'] ?? "en", $payload['calc_context'] ?? null);
        break;

    case "analytics_log":
        handleAnalyticsLog($payload['data'] ?? []);
        break;

    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Unknown action: " . $action]);
        break;
}

// ─── ACTION 1: SUBMIT LEAD TO n8n ───────────────────────────────────────────
function handleLeadSubmit($data) {
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing lead data"]);
        exit;
    }

    // Prepare payload
    $n8nPayload = json_encode([
        "workflow" => "solar_lead_capture",
        "data" => $data
    ]);

    // Send curl request to n8n webhook
    $ch = curl_init(N8N_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $n8nPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($n8nPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        // Log locally if n8n is unreachable
        error_log("[SolarChatbot Proxy] n8n curl error: " . $curlError);
        http_response_code(502);
        echo json_encode(["status" => "error", "message" => "CRM webhook target unreachable"]);
        exit;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(["status" => "ok", "message" => "Lead successfully forwarded to CRM"]);
    } else {
        error_log("[SolarChatbot Proxy] n8n returned HTTP code: " . $httpCode);
        http_response_code($httpCode);
        echo json_encode(["status" => "error", "message" => "CRM server responded with error"]);
    }
}

// ─── ACTION 2: CLAUDE AI FALLBACK ROUTE ─────────────────────────────────────
function handleAIChat($prompt, $lang, $calcContext) {
    if (empty($prompt)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Prompt is required"]);
        exit;
    }

    // Safety fallback if key is not configured
    if (ANTHROPIC_API_KEY === "YOUR_CLAUDE_API_KEY_HERE" || empty(ANTHROPIC_API_KEY)) {
        $fallback = [
            "ar" => "مرحباً! يبدو أنني أواجه مشكلة في الاتصال بالذكاء الاصطناعي حالياً. هل ترغب في معرفة المزيد عن تكاليف أنظمتنا أو حساب توفيرك الكهربائي؟",
            "en" => "Hi! It seems my AI engine is currently offline. Would you like to check our system costs, compute your solar savings, or speak with an expert?"
        ];
        echo json_encode(["status" => "ok", "reply" => $fallback[$lang] ?? $fallback["en"]]);
        exit;
    }

    // Build the dynamic, localized, sales-focused System Prompt
    $sysPrompt = "You are Tariq, a helpful and senior Omani Solar Energy Consultant representing Concept Technologies LLC in Oman.\n";
    $sysPrompt .= "Your ONLY objectives are:\n";
    $sysPrompt .= "1. Answer the user's solar questions clearly, and detail regional solar cost benefits or grid net-metering regulations in Oman.\n";
    $sysPrompt .= "2. Constantly guide the user toward scheduling a free site survey or energy audit.\n";
    $sysPrompt .= "3. Keep your answers short, warm, extremely professional, and sales-focused. Answer in under 100 words.\n";
    $sysPrompt .= "4. Respond strictly in the language they type in (" . ($lang === "ar" ? "Arabic" : "English") . ").\n";
    $sysPrompt .= "5. NEVER discuss topics outside of solar energy or Concept Technologies. Politely redirect unrelated queries to solar.\n";

    if (!empty($calcContext)) {
        $sysPrompt .= "\nCURRENT CONTEXT:\nThe user is currently interacting with the solar calculator on the webpage and generated these calculations:\n";
        $sysPrompt .= "- System Capacity: " . ($calcContext['systemSize'] ?? 'unknown') . " kW\n";
        $sysPrompt .= "- Panel Count Required: " . ($calcContext['panels'] ?? 'unknown') . " panels\n";
        $sysPrompt .= "- Estimated Installation Cost: " . ($calcContext['cost'] ?? 'unknown') . " OMR\n";
        $sysPrompt .= "- Estimated Monthly Savings: " . ($calcContext['monthlySavings'] ?? 'unknown') . " OMR\n";
        $sysPrompt .= "- Estimated Yearly Savings: " . ($calcContext['yearlySavings'] ?? 'unknown') . " OMR\n";
        $sysPrompt .= "- Payback Period: " . ($calcContext['payback'] ?? 'unknown') . " years\n";
        $sysPrompt .= "Reference these numbers if they ask what they mean, explaining the high return on investment in Oman.";
    }

    // Build payload for Claude Messages API (Claude 3.5 Sonnet)
    $claudePayload = json_encode([
        "model" => "claude-3-5-sonnet-20241022",
        "max_tokens" => 300,
        "temperature" => 0.3,
        "system" => $sysPrompt,
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ]);

    // Send curl request to Anthropic API
    $ch = curl_init("https://api.anthropic.com/v1/messages");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $claudePayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: " . ANTHROPIC_API_KEY,
        "anthropic-version: 2023-06-01",
        "Content-Type: application/json",
        "Content-Length: " . strlen($claudePayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("[SolarChatbot Proxy] Claude API error: " . $curlError);
        http_response_code(502);
        echo json_encode(["status" => "error", "message" => "AI engine unreachable"]);
        exit;
    }

    $resData = json_decode($response, true);

    if ($httpCode === 200 && isset($resData['content'][0]['text'])) {
        $aiReply = $resData['content'][0]['text'];
        echo json_encode(["status" => "ok", "reply" => $aiReply]);
    } else {
        error_log("[SolarChatbot Proxy] Claude API returned error. HTTP Code: " . $httpCode . ", Payload: " . $response);
        http_response_code($httpCode ?: 500);
        echo json_encode([
            "status" => "error", 
            "message" => "AI engine returned error code: " . $httpCode
        ]);
    }
}

// ─── ACTION 3: SERVER-SIDE TELEMETRY LOGGER ──────────────────────────────────
function handleAnalyticsLog($data) {
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing log data"]);
        exit;
    }

    $logFile = __DIR__ . "/analytics.log";
    
    // Mask customer IP ranges to enforce GCC privacy guidelines
    $data['ip_masked'] = preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', '$1.$2.xxx.xxx', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logEntry = json_encode($data) . "\n";

    // Securely write using concurrent block flags
    if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        echo json_encode(["status" => "ok", "message" => "Event logged successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to write server log"]);
    }
}
