<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * chatbot.php
 * Concept Technologies LLC — Solar Backend Lead Proxy & AI Advisor
 * 
 * Securely sanitises, validates, and forwards leads to n8n (Odoo CRM) and routes
 * Omani solar consultation requests. Isolates API credentials from the front-end.
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
$credentials = file_exists(__DIR__ . '/credentials.php') ? require __DIR__ . '/credentials.php' : [];

define("N8N_WEBHOOK_URL", "https://n8n.aitsun.space/webhook/solar-lead");
define("GROQ_API_KEY", getenv("GROQ_API_KEY") ?: ($credentials['groq_api_key'] ?? 'YOUR_REAL_GROQ_API_KEY_HERE'));

// Parse Input Payload: Support both standard Form POST and Raw JSON bodies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $payload = $_POST;
    if (isset($payload['action']) && $payload['action'] === 'submit_lead') {
        $payload['data'] = $_POST;
    }
} else {
    $inputRaw = file_get_contents("php://input");
    $payload = json_decode($inputRaw, true) ?: [];
}

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

// ─── ACTION 1: SECURE & SANITISED LEAD SUBMISSION TO n8n (CRM) ───────────────
function handleLeadSubmit($data) {
    if (empty($data)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing lead data"]);
        exit;
    }

    // Anti-Spam Honeypot check
    if (!empty($data['honeypot'])) {
        http_response_code(200); // Fail silently for spam bots
        echo json_encode(["status" => "success", "message" => "Verification completed"]);
        exit;
    }

    // Server-Side Sanitisation & Strict Validation
    $name = isset($data['name']) ? strip_tags(trim($data['name'])) : '';
    $phone = isset($data['phone']) ? preg_replace('/\D/', '', $data['phone']) : '';
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $governorate = isset($data['governorate']) ? strip_tags(trim($data['governorate'])) : 'muscat';
    $property_type = isset($data['property_type']) ? strip_tags(trim($data['property_type'])) : 'residential';
    $monthly_bill = isset($data['monthly_bill']) ? floatval($data['monthly_bill']) : 50.0;
    $consultation_type = isset($data['consultation_type']) ? strip_tags(trim($data['consultation_type'])) : 'site_survey';
    $message = isset($data['message']) ? strip_tags(trim($data['message'])) : '';
    $lang = isset($data['lang']) ? strip_tags(trim($data['lang'])) : 'en';

    // Estimated Calculator Metrics if routed from custom sizer
    $estimated_kw = isset($data['estimated_kw']) ? floatval($data['estimated_kw']) : 0.0;
    $estimated_cost = isset($data['estimated_cost']) ? strip_tags(trim($data['estimated_cost'])) : '';
    $estimated_savings = isset($data['estimated_savings']) ? floatval($data['estimated_savings']) : 0.0;
    $sizer_mode = isset($data['sizer_mode']) ? strip_tags(trim($data['sizer_mode'])) : 'bill';

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Name is required"]);
        exit;
    }

    if (strlen($phone) < 8) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Phone number must be at least 8 digits"]);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid email address format"]);
        exit;
    }

    // Get user IP safely
    $userIp = $_SERVER['HTTP_CLIENT_IP'] 
        ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
        ?? $_SERVER['REMOTE_ADDR'] 
        ?? '';

    if (strpos($userIp, ',') !== false) {
        $userIp = trim(explode(',', $userIp)[0]);
    }

    $sessionId = session_id() ?: '';

    // Standardized Payload structure for n8n to write securely to Odoo
    $n8nPayload = json_encode([
        "source" => "solar.ctechoman.com",
        "lead_type" => "solar_custom_enquiry",
        "session_id" => $sessionId,
        "user_ip" => $userIp,
        "language" => $lang,
        "payload" => [
            "name" => $name,
            "phone" => $phone,
            "email" => $email,
            "governorate" => $governorate,
            "property_type" => $property_type,
            "monthly_bill" => $monthly_bill,
            "consultation_type" => $consultation_type,
            "message" => $message,
            "sizer_mode" => $sizer_mode,
            "estimated_kw" => $estimated_kw,
            "estimated_cost" => $estimated_cost,
            "estimated_savings" => $estimated_savings
        ]
    ]);

    // Send payload via secure cURL POST to the n8n webhook
    $ch = curl_init(N8N_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $n8nPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($n8nPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("[SolarLead Proxy] n8n hook error: " . $curlError);
        http_response_code(502);
        echo json_encode(["status" => "error", "message" => "CRM webhook target unreachable"]);
        exit;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode(["status" => "success", "message" => "Lead successfully forwarded to CRM"]);
    } else {
        error_log("[SolarLead Proxy] n8n returned error code: " . $httpCode);
        http_response_code($httpCode);
        echo json_encode(["status" => "error", "message" => "CRM server responded with error"]);
    }
}

// ─── ACTION 2: DYNAMIC AI SALES ADVISOR (GROQ COMPATIBLE LLAMA-3.3) ─────────
function handleAIChat($prompt, $lang, $calcContext) {
    if (empty($prompt)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Prompt is required"]);
        exit;
    }

    // Safety fallback if key is not configured
    if (GROQ_API_KEY === "YOUR_GROQ_API_KEY_HERE" || empty(GROQ_API_KEY)) {
        $fallback = [
            "ar" => "مرحباً! يبدو أنني أواجه مشكلة في الاتصال بالذكاء الاصطناعي حالياً. هل ترغب في معرفة المزيد عن تكاليف أنظمتنا أو حساب توفيرك الكهربائي؟",
            "en" => "Hi! It seems my AI engine is currently offline. Would you like to check our system costs, compute your solar savings, or speak with an expert?"
        ];
        echo json_encode(["status" => "ok", "reply" => $fallback[$lang] ?? $fallback["en"]]);
        exit;
    }

    // Initialize/Reset session history on language switch or first message
    if (!isset($_SESSION['chatbot_lang']) || $_SESSION['chatbot_lang'] !== $lang) {
        $_SESSION['chatbot_lang'] = $lang;
        $_SESSION['chatbot_history'] = [];
    }

    if (!isset($_SESSION['chatbot_history'])) {
        $_SESSION['chatbot_history'] = [];
    }

    // Build highly optimized system prompt for the GCC / Oman market
    $sysPrompt = "You are Tariq, a warm and senior Omani Solar Energy Consultant representing Concept Technologies LLC in Oman.\n";
    $sysPrompt .= "Your ONLY objectives are:\n";
    $sysPrompt .= "1. Answer the user's solar questions clearly, referencing regional Omani costs (in OMR) and APSR net-metering grid regulations.\n";
    $sysPrompt .= "2. Constantly guide the user toward scheduling a free site survey or energy audit.\n";
    $sysPrompt .= "3. Keep your replies short (under 90 words), extremely professional, warm, and conversion-focused.\n";
    $sysPrompt .= "4. Respond strictly in the language they type in (" . ($lang === "ar" ? "Arabic" : "English") . ").\n";
    $sysPrompt .= "5. NEVER discuss topics outside of solar energy or Concept Technologies. Politely redirect unrelated queries to solar.\n";
    $sysPrompt .= "6. Do NOT repeat your introductory welcome greeting if the user is already talking to you in a continuous conversation.\n";
    $sysPrompt .= "7. LEAD DATA EXTRACTION: If the user types any contact details (such as their name, phone number, location, or email) in raw text, you must extract them. At the very end of your response, you MUST append a hidden metadata tag in this exact format:\n";
    $sysPrompt .= "[LEAD_DATA: {\"name\": \"EXTRACTED_NAME\", \"phone\": \"EXTRACTED_PHONE\", \"location\": \"EXTRACTED_LOCATION\", \"email\": \"EXTRACTED_EMAIL\"}]\n";
    $sysPrompt .= "Ensure the JSON is perfectly valid. Do not explain this tag to the user.";

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

    // Append the new user message to the conversation memory
    $_SESSION['chatbot_history'][] = ["role" => "user", "content" => $prompt];

    // Cap history size to prevent payload bloat (keep last 12 messages = 6 turns)
    if (count($_SESSION['chatbot_history']) > 12) {
        $_SESSION['chatbot_history'] = array_slice($_SESSION['chatbot_history'], -12);
    }

    // Build payload for Groq Chat Completions API
    $groqPayload = json_encode([
        "model" => "llama-3.3-70b-versatile",
        "messages" => array_merge(
            [["role" => "system", "content" => $sysPrompt]],
            $_SESSION['chatbot_history']
        ),
        "temperature" => 0.3,
        "max_tokens" => 300
    ]);

    // Send curl request to Groq API
    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $groqPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . GROQ_API_KEY,
        "Content-Type: application/json",
        "Content-Length: " . strlen($groqPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("[SolarChatbot Proxy] Groq API error: " . $curlError);
        http_response_code(502);
        echo json_encode(["status" => "error", "message" => "AI engine unreachable"]);
        exit;
    }

    $resData = json_decode($response, true);

    if ($httpCode === 200 && isset($resData['choices'][0]['message']['content'])) {
        $aiReply = $resData['choices'][0]['message']['content'];
        
        // Save the assistant's reply to the conversation memory
        $_SESSION['chatbot_history'][] = ["role" => "assistant", "content" => $aiReply];
        
        echo json_encode(["status" => "ok", "reply" => $aiReply]);
    } else {
        error_log("[SolarChatbot Proxy] Groq API returned error. HTTP Code: " . $httpCode . ", Payload: " . $response);
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
    
    // Mask customer IP ranges to enforce privacy guidelines
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
