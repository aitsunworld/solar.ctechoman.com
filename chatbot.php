<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
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

function json_utf8($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function json_response($data, $statusCode = null) {
    if ($statusCode !== null) {
        http_response_code($statusCode);
    }
    echo json_utf8($data);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(["status" => "error", "message" => "Method not allowed"], 405);
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
    json_response(["status" => "error", "message" => "Invalid payload or missing action"], 400);
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
        json_response(["status" => "error", "message" => "Unknown action: " . $action], 400);
        break;
}

// ─── ACTION 1: SECURE & SANITISED LEAD SUBMISSION TO n8n (CRM) ───────────────
function handleLeadSubmit($data) {
    if (empty($data)) {
        json_response(["status" => "error", "message" => "Missing lead data"], 400);
        exit;
    }

    // Anti-Spam Honeypot check
    if (!empty($data['honeypot'])) {
        json_response(["status" => "success", "message" => "Verification completed"], 200); // Fail silently for spam bots
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
        json_response(["status" => "error", "message" => "Name is required"], 400);
        exit;
    }

    if (strlen($phone) < 8) {
        json_response(["status" => "error", "message" => "Phone number must be at least 8 digits"], 400);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(["status" => "error", "message" => "Invalid email address format"], 400);
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
    $n8nPayload = json_utf8([
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
        json_response(["status" => "error", "message" => "CRM webhook target unreachable"], 502);
        exit;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        json_response(["status" => "success", "message" => "Lead successfully forwarded to CRM"]);
    } else {
        error_log("[SolarLead Proxy] n8n returned error code: " . $httpCode);
        json_response(["status" => "error", "message" => "CRM server responded with error"], $httpCode);
    }
}

// Smart localized safety fallback for Omani solar market when API key is missing or offline
function getSmartFallbackReply($prompt, $lang) {
    $promptLower = mb_strtolower($prompt, 'UTF-8');
    $isAr = ($lang === 'ar');

    $costKeys = ['cost', 'price', 'how much', 'expensive', 'afford', 'omr', 'pricing', 'سعر', 'تكلفة', 'كم', 'ريال', 'غالي'];
    $saveKeys = ['save', 'saving', 'roi', 'return', 'payback', 'benefit', 'electricity bill', 'وفر', 'توفير', 'عائد', 'ربح', 'فاتورة'];
    $instKeys = ['install', 'installation', 'how long', 'process', 'steps', 'setup', 'time', 'تركيب', 'كيف', 'خطوات', 'مدة', 'وقت'];

    $matchedCost = false;
    foreach ($costKeys as $key) {
        if (strpos($promptLower, $key) !== false) {
            $matchedCost = true;
            break;
        }
    }

    $matchedSave = false;
    foreach ($saveKeys as $key) {
        if (strpos($promptLower, $key) !== false) {
            $matchedSave = true;
            break;
        }
    }

    $matchedInst = false;
    foreach ($instKeys as $key) {
        if (strpos($promptLower, $key) !== false) {
            $matchedInst = true;
            break;
        }
    }

    if ($matchedCost) {
        return $isAr 
            ? "☀️ **تكاليف أنظمة الطاقة الشمسية في سلطنة عُمان:**\n\nتتراوح تكلفة التركيب السكني النموذجي بين **1,500 إلى 4,500 ريال عُماني** حسب حجم الفيلا واستهلاك الكهرباء.\n\n• نظام 3 كيلوواط (مناسب للمنازل الصغيرة): ~1,500 ريال عُماني\n• نظام 5 كيلوواط (متوسط): ~2,500 ريال عُماني\n• نظام 10 كيلوواط (فلل كبيرة): ~4,500 ريال عُماني\n\nتتضمن التكلفة الألواح عالية الكفاءة والعاكس الذكي مع ضمان لمدة 25 سنة. هل ترغب في جدولة مسح ميداني مجاني لمنزلك لتحديد التكلفة الدقيقة؟"
            : "☀️ **Solar System Costs in Oman:**\n\nA typical residential solar installation in Oman ranges from **OMR 1,500 to OMR 4,500** depending on system capacity:\n\n• 3kW System (small villa): ~OMR 1,500\n• 5kW System (average villa): ~OMR 2,500\n• 10kW System (large villa): ~OMR 4,500\n\nThese estimates include premium Tier-1 solar panels, smart inverter, structural framing, and grid integration. Would you like to schedule a free site survey to get an exact quote?";
    }

    if ($matchedSave) {
        return $isAr 
            ? "💰 **التوفير وعائد الاستثمار في عُمان:**\n\nتتيح لك أنظمة الطاقة الشمسية توفير ما بين **40 إلى 120 ريال عُماني شهرياً** على فاتورة الكهرباء:\n\n• **فترة استرداد رأس المال:** بين 4 إلى 6 سنوات فقط.\n• **عمر النظام التشغيلي:** أكثر من 25 عاماً (مما يعني 20 عاماً من الكهرباء المجانية!).\n• **عائد الاستثمار:** يتراوح بين 200% إلى 400% على المدى الطويل.\n\nيمكننا تحويل منزلك بالكامل إلى الطاقة النظيفة. هل ترغب في التحدث إلى مهندس مختص لحساب نسبة التوفير الدقيقة لك؟"
            : "💰 **Solar Savings & Payback in Oman:**\n\nMost homeowners in Oman save between **OMR 40 to OMR 120 per month** on their electricity bills after going solar:\n\n• **Payback Period:** Typically 4 to 6 years.\n• **System Lifespan:** 25+ years (meaning 20 years of 100% free electricity!).\n• **Total Return on Investment (ROI):** 200% to 400% over the system life.\n\nWould you like to schedule a quick call with one of our energy consultants to run a detailed savings forecast for your property?";
    }

    if ($matchedInst) {
        return $isAr 
            ? "⚡ **خطوات تركيب النظام والجدول الزمني:**\n\nنقوم بإدارة عملية التركيب بالكامل في 3 خطوات بسيطة وسريعة:\n\n1. **المسح الميداني والتصميم (يومان):** نقوم بزيارة موقعك وتصميم أفضل تخطيط للألواح مجاناً.\n2. **الموافقات الرسمية (1-2 أسبوع):** نقوم بتأمين كافة الموافقات من هيئة تنظيم الخدمات العامة (APSR) والبلدية.\n3. **التركيب والتشغيل (3-5 أيام):** نقوم بتركيب النظام وربطه بالشبكة الحكومية للبدء في توفير فاتورتك!\n\nهل ترغب في جدولة الخطوة الأولى (المسح الميداني المجاني) هذا الأسبوع؟"
            : "⚡ **Installation Process & Timeline in Oman:**\n\nWe manage the entire solar journey for you in 3 clear steps:\n\n1. **Site Survey & Design (2 days):** We conduct a free technical site analysis and create a custom layout design.\n2. **Permits & Approvals (1-2 weeks):** We secure all necessary grid-connection approvals from APSR and local authorities.\n3. **Installation & Commissioning (3-5 days):** Our certified engineers install the panels, configure the inverter, and activate net-metering!\n\nWould you like to schedule your free site survey this week to get started?";
    }

    return $isAr 
        ? "مرحباً! أنا طارق، مستشار الطاقة الشمسية في كونسبت تكنولوجيز. يسعدني جداً الإجابة على أي استفسار لديك حول أنظمة الطاقة الشمسية، التكاليف في عُمان، أو كيفية خفض فاتورة الكهرباء الخاصة بك.\n\nأنصحك باستخدام حاسبتنا المتقدمة أعلى الصفحة أو جدولة زيارة ميدانية مجانية لمنزلك للحصول على تقرير دقيق ومخصص. كيف يمكنني مساعدتك اليوم؟"
        : "Hello! I am Tariq, senior solar consultant at Concept Technologies LLC. I am here to help you with any questions regarding solar energy systems, OMR pricing in Oman, or reducing your electricity bill.\n\nI highly recommend using our advanced solar calculator at the top of this page or booking a free home site survey to get a precise solar layout report. How can I help you today?";
}

// ─── ACTION 2: DYNAMIC AI SALES ADVISOR (GROQ COMPATIBLE LLAMA-3.3) ─────────
function handleAIChat($prompt, $lang, $calcContext) {
    if (empty($prompt)) {
        json_response(["status" => "error", "message" => "Prompt is required"], 400);
        exit;
    }

    // Safety fallback if key is placeholder or invalid
    $isPlaceholder = empty(GROQ_API_KEY) || 
                     strpos(GROQ_API_KEY, "YOUR_") !== false || 
                     strpos(GROQ_API_KEY, "gsk_5F6E8") !== false ||
                     strlen(GROQ_API_KEY) < 20;

    if ($isPlaceholder) {
        $reply = getSmartFallbackReply($prompt, $lang);
        json_response(["status" => "ok", "reply" => $reply]);
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
    $groqPayload = json_utf8([
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

    if ($curlError || $httpCode !== 200) {
        if ($curlError) {
            error_log("[SolarChatbot Proxy] Groq API error: " . $curlError);
        } else {
            error_log("[SolarChatbot Proxy] Groq API returned error. HTTP Code: " . $httpCode . ", Payload: " . $response);
        }
        $reply = getSmartFallbackReply($prompt, $lang);
        json_response(["status" => "ok", "reply" => $reply]);
        exit;
    }

    $resData = json_decode($response, true);

    if (isset($resData['choices'][0]['message']['content'])) {
        $aiReply = $resData['choices'][0]['message']['content'];
        
        // Save the assistant's reply to the conversation memory
        $_SESSION['chatbot_history'][] = ["role" => "assistant", "content" => $aiReply];
        
        json_response(["status" => "ok", "reply" => $aiReply]);
    } else {
        $reply = getSmartFallbackReply($prompt, $lang);
        json_response(["status" => "ok", "reply" => $reply]);
    }
}

// ─── ACTION 3: SERVER-SIDE TELEMETRY LOGGER ──────────────────────────────────
function handleAnalyticsLog($data) {
    if (empty($data)) {
        json_response(["status" => "error", "message" => "Missing log data"], 400);
        exit;
    }
    $logFile = __DIR__ . "/analytics.log";
    
    // Mask customer IP ranges to enforce privacy guidelines
    $data['ip_masked'] = preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', '$1.$2.xxx.xxx', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logEntry = json_utf8($data) . "\n";

    // Securely write using concurrent block flags
    if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
        json_response(["status" => "ok", "message" => "Event logged successfully"]);
    } else {
        json_response(["status" => "error", "message" => "Failed to write server log"], 500);
    }
}
