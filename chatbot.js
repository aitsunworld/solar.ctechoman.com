/**
 * chatbot.js
 * Concept Technologies LLC — Solar Sales Chatbot (Vanilla JS version)
 * Complete, standalone, zero-dependency widget.
 * Self-renders into the DOM, manages guided FAQs, leads, smart triggers, and AI proxies.
 */

(function () {
  "use strict";

  // ─── TRUSTED TYPES SECURITY COMPLIANCE LAYER ─────────────────────────────────
  let ttPolicy = null;
  if (typeof window !== "undefined" && window.trustedTypes && window.trustedTypes.createPolicy) {
    try {
      ttPolicy = window.trustedTypes.createPolicy("sbChatbotPolicy", {
        createHTML: (string) => string
      });
    } catch (e) {
      console.warn("[SolarBot] Trusted Types policy registration failed or blocked:", e);
    }
  }

  function setSafeHTML(element, htmlString) {
    if (element) {
      if (ttPolicy) {
        element.innerHTML = ttPolicy.createHTML(htmlString);
      } else {
        element.innerHTML = htmlString;
      }
    }
  }

  // ─── CONSTANTS & LOCALIZATION ────────────────────────────────────────────────

  const TRANSLATIONS = {
    en: {
      greeting: "👋 Hi! I'm SolarBot, your solar energy guide for Concept Technologies.",
      greetingCta: "I can help you understand solar savings, answer questions, or get you a free quote!",
      placeholder: "Type your message...",
      send: "Send",
      quickReplies: {
        calculator: "🧮 Use Solar Calculator",
        savings: "💰 How much will I save?",
        cost: "💵 What does it cost?",
        quote: "📋 Get a Free Quote",
        faq: "❓ Common Questions",
      },
      leadCapture: {
        name: "What's your name?",
        phone: "What's your phone number?",
        email: "What's your email address?",
        location: "Which city/area are you located in?",
        submitted: "✅ Thank you! Our solar expert will contact you within 24 hours.",
      },
      typing: "SolarBot is typing...",
      minimize: "Minimize chat",
      close: "Close chat",
      open: "Chat with SolarBot",
      language: "العربية",
    },
    ar: {
      greeting: "👋 مرحباً! أنا SolarBot، مرشدك للطاقة الشمسية من كونسبت تكنولوجيز.",
      greetingCta: "يمكنني مساعدتك في فهم توفير الطاقة الشمسية، الإجابة على أسئلتك، أو الحصول على عرض سعر مجاني!",
      placeholder: "اكتب رسالتك...",
      send: "إرسال",
      quickReplies: {
        calculator: "🧮 استخدم حاسبة الطاقة الشمسية",
        savings: "💰 كم سأوفر؟",
        cost: "💵 ما هي التكلفة؟",
        quote: "📋 احصل على عرض مجاني",
        faq: "❓ أسئلة شائعة",
      },
      leadCapture: {
        name: "ما اسمك؟",
        phone: "ما رقم هاتفك؟",
        email: "ما عنوان بريدك الإلكتروني؟",
        location: "في أي مدينة/منطقة تقع؟",
        submitted: "✅ شكراً! سيتواصل معك خبير الطاقة الشمسية لدينا خلال 24 ساعة.",
      },
      typing: "SolarBot يكتب...",
      minimize: "تصغير المحادثة",
      close: "إغلاق المحادثة",
      open: "تحدث مع SolarBot",
      language: "English",
    },
  };

  const FAQ_DB = {
    cost: {
      keywords: ["cost", "price", "how much", "expensive", "afford", "كم", "سعر", "تكلفة", "غالي"],
      en: `☀️ **Solar System Costs in Oman:**\n\nA typical residential system costs between **OMR 1,500 – OMR 4,500** depending on size.\n\n• 3kW system (small home): ~OMR 1,500\n• 5kW system (average home): ~OMR 2,500\n• 10kW system (large home): ~OMR 4,500\n\nThese are net costs after any available incentives. Would you like a personalized quote?`,
      ar: `☀️ **تكاليف أنظمة الطاقة الشمسية في عُمان:**\n\nتتراوح تكلفة النظام السكني النموذجي بين **1,500 – 4,500 ريال عُماني** حسب الحجم.\n\n• نظام 3 كيلوواط (منزل صغير): ~1,500 ريال\n• نظام 5 كيلوواط (منزل متوسط): ~2,500 ريال\n• نظام 10 كيلوواط (منزل كبير): ~4,500 ريال\n\nهل تريد عرض سعر مخصص؟`,
    },
    savings: {
      keywords: ["save", "saving", "roi", "return", "payback", "benefit", "profit", "وفر", "توفير", "عائد", "ربح"],
      en: `💰 **Your Solar Savings Potential:**\n\nMost Omani homes save **OMR 40–120/month** on electricity bills after going solar.\n\n• Average payback period: **4–6 years**\n• 25-year system lifespan → **15–21 years of free electricity**\n• ROI typically **200–400%** over system life\n\nUse our calculator to see YOUR exact savings based on your bill!`,
      ar: `💰 **إمكانات توفير الطاقة الشمسية لديك:**\n\nمعظم المنازل العُمانية توفر **40-120 ريال/شهر** على فواتير الكهرباء بعد تركيب الطاقة الشمسية.\n\n• متوسط فترة الاسترداد: **4-6 سنوات**\n• عمر النظام 25 سنة → **15-21 سنة من الكهرباء المجانية**\n• عائد الاستثمار عادةً **200-400%** على مدى عمر النظام\n\nاستخدم حاسبتنا لمعرفة توفيرك الدقيق!`,
    },
    installation: {
      keywords: ["install", "installation", "how long", "process", "steps", "setup", "تركيب", "كيف", "خطوات", "مدة"],
      en: `🔧 **Installation Process:**\n\n1. **Site Survey** – Our engineer visits your home (1–2 hours)\n2. **Design** – Custom system design (2–3 days)\n3. **Permits** – We handle DCREC approvals (5–10 days)\n4. **Installation** – Panels installed (1–2 days)\n5. **Grid Connection** – connection & testing (3–5 days)\n\n**Total: Typically 2–4 weeks from signing to power!**`,
      ar: `🔧 **عملية التركيب:**\n\n1. **مسح الموقع** – يزور مهندسنا منزلك (1-2 ساعة)\n2. **التصميم** – تصميم نظام مخصص (2-3 أيام)\n3. **التصاريح** – نتولى موافقات DCREC (5-10 أيام)\n4. **التركيب** – تركيب الألواح (1-2 يوم)\n5. **توصيل الشبكة** – توصيل MEDC والاختبار (3-5 أيام)\n\n**المجموع: عادةً 2-4 أسابيع من التوقيع حتى التشغيل!**`,
    },
    maintenance: {
      keywords: ["maintenance", "maintain", "clean", "repair", "warranty", "broken", "صيانة", "ضمان", "تنظيف", "إصلاح"],
      en: `🛠️ **Solar Maintenance:**\n\n**Very minimal!** Solar panels are almost maintenance-free.\n\n• **Cleaning:** 2–4 times/year (we offer service plans)\n• **Monitoring:** 24/7 via our app\n• **Warranty:** 25-year panel performance warranty\n• **Inverter:** 10-year warranty, typically lasts 15+ years\n• **Annual check:** We recommend yearly professional inspection\n\nConcept Technologies offers maintenance packages starting at OMR 50/year.`,
      ar: `🛠️ **صيانة الطاقة الشمسية:**\n\n**صيانة بسيطة جداً!** الألواح الشمسية لا تحتاج تقريباً إلى صيانة.\n\n• **التنظيف:** 2-4 مرات/سنة (نقدم خطط خدمة)\n• **المراقبة:** 24/7 عبر تطبيقنا\n• **الضمان:** ضمان أداء الألواح 25 سنة\n• **العاكس:** ضمان 10 سنوات، يدوم عادةً 15+ سنة\n• **فحص سنوي:** نوصي بالفحص المهني السنوي\n\nكونسبت تكنولوجيز تقدم باقات صيانة تبدأ من 50 ريال/سنة.`,
    },
    regulations: {
      keywords: ["regulation", "legal", "permit", "law", "oman", "authority", "dcrec", "medc", "allowed", "قانون", "تصريح", "سماح", "عُمان", "ديكريك"],
      en: `📋 **Oman Solar Regulations:**\n\n✅ **Solar is fully legal and encouraged in Oman!**\n\n• **DCREC** (Dhofar) or **MEDC/OETC** (Muscat) approval required\n• Net metering available — sell excess electricity back to grid\n• **REDS scheme** – Authority of Electricity Regulation incentives\n• VAT exemption may apply to solar equipment\n• Concept Technologies handles ALL permits for you\n\nWe're fully certified and have completed 200+ legal installations.`,
      ar: `📋 **لوائح الطاقة الشمسية في عُمان:**\n\n✅ **الطاقة الشمسية قانونية ومشجعة تماماً في عُمان!**\n\n• مطلوب موافقة **DCREC** (ظفار) أو **MEDC/OETC** (مسقط)\n• قياس صافي متاح — بيع الكهرباء الزائدة للشبكة\n• **مخطط REDS** – حوافز هيئة تنظيم الكهرباء\n• قد تنطبق إعفاء ضريبة القيمة المضافة على معدات الطاقة الشمسية\n• كونسبت تكنولوجيز تتولى جميع التصاريح نيابةً عنك\n\nنحن معتمدون بالكامل وأكملنا أكثر من 200 تركيب قانوني.`,
    },
    calculator: {
      keywords: ["calculator", "calculate", "estimate", "bill", "kwh", "system size", "panels", "حاسبة", "احسب", "تقدير", "فاتورة", "ألواح"],
      en: `🧮 **Using the Solar Calculator:**\n\nOur calculator is on this page! Here's how to use it:\n\n1. **Enter your monthly electricity bill** in OMR\n2. The calculator automatically shows:\n   • Recommended system size (kW)\n   • Number of panels needed\n   • Estimated installation cost\n   • Monthly & yearly savings\n   • Payback period\n\nScroll up to find the calculator, or I can explain any result!`,
      ar: `🧮 **استخدام حاسبة الطاقة الشمسية:**\n\nحاسبتنا موجودة في هذه الصفحة! إليك كيفية استخدامها:\n\n1. **أدخل فاتورة الكهرباء الشهرية** بالريال العُماني\n2. تعرض الحاسبة تلقائياً:\n   • حجم النظام الموصى به (كيلوواط)\n   • عدد الألواح المطلوبة\n   • التكلفة التقديرية للتركيب\n   • التوفير الشهري والسنوي\n   • فترة الاسترداد\n\nانتقل للأعلى للعثور على الحاسبة، أو يمكنني شرح أي نتيجة!`,
    },
  };

  const FLOW = {
    WELCOME: "WELCOME",
    MAIN_MENU: "MAIN_MENU",
    FAQ_MENU: "FAQ_MENU",
    CALCULATOR_GUIDE: "CALCULATOR_GUIDE",
    LEAD_CAPTURE: "LEAD_CAPTURE",
    LEAD_DONE: "LEAD_DONE",
  };

  // ─── STATE MANAGEMENT ────────────────────────────────────────────────────────

  let state = {
    isOpen: false,
    isMinimized: false,
    lang: "en",
    messages: [],
    flow: FLOW.WELCOME,
    leadData: {},
    notificationCount: 0,
    hasGreeted: false,
    exitIntentFired: false,
    pulseTimer: null,
    calculatorContext: null,
    isTyping: false
  };

  // DOM Cache
  let el = {};

  // ─── INIT FUNCTIONS ──────────────────────────────────────────────────────────

  function init() {
    // Detect starting language (matches html tag or session variable)
    const docLang = document.documentElement.getAttribute("lang") || "en";
    state.lang = docLang.startsWith("ar") ? "ar" : "en";

    renderDOM();
    bindEvents();
    startSmartTriggers();
    retryQueuedLeads();

    // Expose global window hooks for the page calculator to reach the widget
    window.SolarChatbot = {
      explainCalculatorResult: explainCalculatorResult,
      open: openChat,
      close: closeChat
    };
  }

  // ─── DYNAMIC DOM CREATION ────────────────────────────────────────────────────

  function renderDOM() {
    // 1. Create Floating Button Widget
    const btn = document.createElement("button");
    btn.className = "sb-widget-btn pulse";
    btn.setAttribute("aria-label", TRANSLATIONS[state.lang].open);
    setSafeHTML(btn, "☀️");
    if (state.lang === "ar") btn.classList.add("sb-left");
    document.body.appendChild(btn);
    el.widgetBtn = btn;

    // 2. Create Chat Window Wrapper
    const win = document.createElement("div");
    win.className = "sb-chat-window";
    win.style.display = "none";
    if (state.lang === "ar") win.classList.add("sb-left");
    win.setAttribute("dir", state.lang === "ar" ? "rtl" : "ltr");

    // Populate inner HTML structure
    setSafeHTML(win, `
      <div class="sb-header">
        <div class="sb-header-avatar">☀️</div>
        <div class="sb-header-info">
          <div class="sb-header-title">SolarBot</div>
          <div class="sb-header-status">
            <span class="sb-status-dot"></span>
            <span id="sb-status-text">${state.lang === "ar" ? "متصل الآن" : "Online now"}</span>
          </div>
        </div>
        <button class="sb-lang-toggle" id="sb-lang-btn">${TRANSLATIONS[state.lang].language}</button>
        <button class="sb-control-btn" id="sb-minimize-btn" aria-label="Minimize">▼</button>
        <button class="sb-control-btn" id="sb-close-btn" aria-label="Close">×</button>
      </div>
      <div class="sb-messages" id="sb-msg-list"></div>
      <div class="sb-quick-replies" id="sb-replies-list" style="display: none;"></div>
      <div class="sb-lead-form" id="sb-form-panel" style="display: none;"></div>
      <div class="sb-input-panel" id="sb-input-panel">
        <input type="text" class="sb-text-input" id="sb-chat-input" placeholder="${TRANSLATIONS[state.lang].placeholder}" />
        <button class="sb-send-btn" id="sb-send-btn">${state.lang === "ar" ? "←" : "→"}</button>
      </div>
      <div class="sb-footer">
        Powered by <strong>Concept Technologies LLC</strong>
      </div>
    `);

    document.body.appendChild(win);
    el.chatWindow = win;

    // Cache elements internally
    el.msgList = document.getElementById("sb-msg-list");
    el.repliesList = document.getElementById("sb-replies-list");
    el.formPanel = document.getElementById("sb-form-panel");
    el.chatInput = document.getElementById("sb-chat-input");
    el.sendBtn = document.getElementById("sb-send-btn");
    el.langBtn = document.getElementById("sb-lang-btn");
    el.minimizeBtn = document.getElementById("sb-minimize-btn");
    el.closeBtn = document.getElementById("sb-close-btn");
    el.statusText = document.getElementById("sb-status-text");
  }

  // ─── EVENTS BINDING ──────────────────────────────────────────────────────────

  function bindEvents() {
    el.widgetBtn.addEventListener("click", openChat);
    el.closeBtn.addEventListener("click", closeChat);
    el.minimizeBtn.addEventListener("click", toggleMinimize);
    el.langBtn.addEventListener("click", toggleLanguage);

    // Input handlers
    el.chatInput.addEventListener("input", function () {
      if (el.chatInput.value.trim().length > 0) {
        el.sendBtn.classList.add("active");
      } else {
        el.sendBtn.classList.remove("active");
      }
    });

    el.chatInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    });

    el.sendBtn.addEventListener("click", handleSend);
  }

  // ─── STATE ACTIONS ───────────────────────────────────────────────────────────

  function openChat() {
    state.isOpen = true;
    state.isMinimized = false;
    el.chatWindow.classList.remove("minimized");
    el.chatWindow.style.display = "flex";
    el.widgetBtn.style.display = "none";
    state.notificationCount = 0;
    removeBadge();

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("chatbot_open");
    }

    if (state.messages.length === 0) {
      triggerWelcome();
    }
    setTimeout(() => el.chatInput.focus(), 300);
  }

  function closeChat() {
    state.isOpen = false;
    el.chatWindow.style.display = "none";
    el.widgetBtn.style.display = "flex";

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("chatbot_close");
    }
  }

  function toggleMinimize() {
    state.isMinimized = !state.isMinimized;
    if (state.isMinimized) {
      el.chatWindow.classList.add("minimized");
      el.minimizeBtn.textContent = "▲";
      if (window.SolarAnalytics) {
        window.SolarAnalytics.track("chatbot_minimize");
      }
    } else {
      el.chatWindow.classList.remove("minimized");
      el.minimizeBtn.textContent = "▼";
      if (window.SolarAnalytics) {
        window.SolarAnalytics.track("chatbot_maximize");
      }
      setTimeout(() => el.chatInput.focus(), 100);
    }
  }

  function toggleLanguage() {
    state.lang = state.lang === "en" ? "ar" : "en";
    
    // Update layout configurations
    const isRtl = state.lang === "ar";
    el.chatWindow.setAttribute("dir", isRtl ? "rtl" : "ltr");
    
    if (isRtl) {
      el.chatWindow.classList.add("sb-left");
      el.widgetBtn.classList.add("sb-left");
      el.sendBtn.textContent = "←";
    } else {
      el.chatWindow.classList.remove("sb-left");
      el.widgetBtn.classList.remove("sb-left");
      el.sendBtn.textContent = "→";
    }

    // Refresh translations
    el.chatInput.setAttribute("placeholder", TRANSLATIONS[state.lang].placeholder);
    el.langBtn.textContent = TRANSLATIONS[state.lang].language;
    el.statusText.textContent = isRtl ? "متصل الآن" : "Online now";

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("language_switch", { language: state.lang });
    }

    // Clear messages and trigger clean re-entry greeting
    state.messages = [];
    el.msgList.innerHTML = "";
    el.formPanel.style.display = "none";
    el.formPanel.innerHTML = "";
    el.repliesList.style.display = "none";
    triggerWelcome();
  }

  // ─── CHAT CONVERSATION LOGIC ──────────────────────────────────────────────────

  function triggerWelcome() {
    state.flow = FLOW.WELCOME;
    addBotMessage(TRANSLATIONS[state.lang].greeting);
    
    setTimeout(function () {
      addBotMessage(TRANSLATIONS[state.lang].greetingCta, getMainMenuReplies());
      state.flow = FLOW.MAIN_MENU;
    }, 1200);
  }

  function getMainMenuReplies() {
    const t = TRANSLATIONS[state.lang].quickReplies;
    return [
      { label: t.calculator, value: "calculator" },
      { label: t.savings, value: "savings" },
      { label: t.cost, value: "cost" },
      { label: t.quote, value: "quote" },
      { label: t.faq, value: "faq" },
    ];
  }

  // Message Addition
  function addMessage(sender, text) {
    const id = Date.now();
    state.messages.push({ sender, text, id });

    const msgRow = document.createElement("div");
    msgRow.className = `sb-msg-row ${sender}`;

    const avatar = document.createElement("div");
    avatar.className = `sb-avatar ${sender}`;
    avatar.textContent = sender === "bot" ? "☀" : "U";

    const bubble = document.createElement("div");
    bubble.className = "sb-bubble";
    setSafeHTML(bubble, formatMarkdown(text));

    msgRow.appendChild(avatar);
    msgRow.appendChild(bubble);
    el.msgList.appendChild(msgRow);
    scrollBottom();
  }

  function addBotMessage(text, replies = []) {
    showTyping();
    const delay = Math.min(600 + text.length * 10, 2000);

    setTimeout(function () {
      hideTyping();
      addMessage("bot", text);
      
      if (replies.length > 0) {
        renderQuickReplies(replies);
      } else {
        el.repliesList.style.display = "none";
      }
    }, delay);
  }

  function addUserMessage(text) {
    addMessage("user", text);
    el.repliesList.style.display = "none";
  }

  // Typing state
  function showTyping() {
    if (state.isTyping) return;
    state.isTyping = true;
    
    const row = document.createElement("div");
    row.className = "sb-msg-row bot";
    row.id = "sb-typing-row";

    setSafeHTML(row, `
      <div class="sb-avatar bot">☀</div>
      <div class="sb-typing-indicator">
        <span class="sb-typing-dot"></span>
        <span class="sb-typing-dot"></span>
        <span class="sb-typing-dot"></span>
      </div>
    `);
    el.msgList.appendChild(row);
    scrollBottom();
  }

  function hideTyping() {
    state.isTyping = false;
    const row = document.getElementById("sb-typing-row");
    if (row) row.remove();
  }

  // Quick Replies Rendering
  function renderQuickReplies(replies) {
    setSafeHTML(el.repliesList, "");
    el.repliesList.style.display = "flex";

    replies.forEach(function (opt) {
      const chip = document.createElement("button");
      chip.className = "sb-reply-chip";
      chip.textContent = opt.label;
      chip.addEventListener("click", function () {
        handleQuickReply(opt);
      });
      el.repliesList.appendChild(chip);
    });
    scrollBottom();
  }

  // Quick Reply Routing
  function handleQuickReply(opt) {
    addUserMessage(opt.label);
    const l = state.lang;

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("chatbot_reply_click", { option: opt.label, value: opt.value });
    }

    switch (opt.value) {
      case "calculator":
        addBotMessage(FAQ_DB.calculator[l] || FAQ_DB.calculator.en, [
          { label: l === "ar" ? "📋 احصل على عرض سعر" : "📋 Get a Quote", value: "quote" },
          { label: l === "ar" ? "🔙 القائمة الرئيسية" : "🔙 Main Menu", value: "menu" },
        ]);
        state.flow = FLOW.CALCULATOR_GUIDE;
        break;

      case "savings":
        addBotMessage(FAQ_DB.savings[l] || FAQ_DB.savings.en, [
          { label: l === "ar" ? "🧮 جرب الحاسبة" : "🧮 Try Calculator", value: "calculator" },
          { label: l === "ar" ? "📋 احصل على عرض" : "📋 Get a Quote", value: "quote" },
        ]);
        break;

      case "cost":
        addBotMessage(FAQ_DB.cost[l] || FAQ_DB.cost.en, [
          { label: l === "ar" ? "📋 عرض سعر مجاني" : "📋 Free Quote", value: "quote" },
          { label: l === "ar" ? "🔙 رجوع" : "🔙 Back", value: "menu" },
        ]);
        break;

      case "quote":
        addBotMessage(
          l === "ar"
            ? "رائع! سنساعدك في الحصول على عرض سعر مجاني. أحتاج بعض المعلومات منك."
            : "Great! Let's get you a free solar quote. I just need a few details."
        );
        setTimeout(function () {
          renderLeadForm();
        }, 1500);
        break;

      case "faq":
        addBotMessage(
          l === "ar" ? "إليك أكثر الأسئلة شيوعاً حول الطاقة الشمسية:" : "Here are the most common solar questions:",
          [
            { label: l === "ar" ? "💰 التكلفة" : "💰 Cost", value: "cost_faq" },
            { label: l === "ar" ? "📈 العائد" : "📈 ROI", value: "savings_faq" },
            { label: l === "ar" ? "🔧 التركيب" : "🔧 Installation", value: "installation_faq" },
            { label: l === "ar" ? "🛠 الصيانة" : "🛠 Maintenance", value: "maintenance_faq" },
            { label: l === "ar" ? "📋 اللوائح" : "📋 Regulations", value: "regulations_faq" },
          ]
        );
        state.flow = FLOW.FAQ_MENU;
        break;

      // Internal FAQ redirect triggers
      case "cost_faq": handleQuickReply({ label: opt.label, value: "cost" }); break;
      case "savings_faq": handleQuickReply({ label: opt.label, value: "savings" }); break;
      case "installation_faq":
        addBotMessage(FAQ_DB.installation[l] || FAQ_DB.installation.en, [
          { label: l === "ar" ? "📋 احصل على عرض" : "📋 Get a Quote", value: "quote" },
          { label: l === "ar" ? "🔙 المزيد من الأسئلة" : "🔙 More FAQs", value: "faq" },
        ]);
        break;
      case "maintenance_faq":
        addBotMessage(FAQ_DB.maintenance[l] || FAQ_DB.maintenance.en, [
          { label: l === "ar" ? "📋 تواصل معنا" : "📋 Contact Us", value: "quote" },
          { label: l === "ar" ? "🔙 المزيد" : "🔙 More", value: "faq" },
        ]);
        break;
      case "regulations_faq":
        addBotMessage(FAQ_DB.regulations[l] || FAQ_DB.regulations.en, [
          { label: l === "ar" ? "📋 احصل على عرض" : "📋 Get a Quote", value: "quote" },
          { label: l === "ar" ? "🔙 رجوع" : "🔙 Back", value: "menu" },
        ]);
        break;

      case "menu":
        addBotMessage(l === "ar" ? "كيف يمكنني مساعدتك؟" : "How can I help you?", getMainMenuReplies());
        state.flow = FLOW.MAIN_MENU;
        break;

      default:
        handleFreeText(opt.value);
    }
  }

  // Free text query logic
  function handleSend() {
    const text = el.chatInput.value.trim();
    if (!text) return;
    el.chatInput.value = "";
    el.sendBtn.classList.remove("active");

    addUserMessage(text);
    handleFreeText(text);
  }

  function handleFreeText(text) {
    const isAr = detectArabic(text);
    if (isAr !== (state.lang === "ar")) {
      // Auto-adapt language state to input style
      state.lang = isAr ? "ar" : "en";
      toggleLanguage();
    }
    const l = state.lang;

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("chatbot_message_sent", { text_length: text.length });
    }

    // --- AGREEMENT & QUOTE INTENT DETECTION ---
    const normalized = text.toLowerCase().trim().replace(/[.,\/#!$%\^&\*;:{}=\-_`~()?]/g,"");
    const agreementPatterns = [
      /\b(sure|yes|go on|okay|ok|yeah|yup|yep|agree|confirm|details)\b/i,
      /\b(quote|quotation|survey|site survey|price|estimation|quot)\b/i,
      /\b(نعم|أجل|بالتأكيد|بالتاكيد|موافق|معاينة|عرض سعر|اقتباس|سعر|طبعا|اوكي|حاضر)\b/
    ];
    const isGreeting = /^(hy|hi|hello|hey|hola|مرحبا|سلام|اهلا|أهلاً|مرحباً)$/i.test(normalized);

    if (!isGreeting && agreementPatterns.some(pattern => pattern.test(normalized))) {
      if (window.SolarAnalytics) {
        window.SolarAnalytics.track("chatbot_quote_intent_match", { text: text });
      }
      addBotMessage(
        l === "ar"
          ? "رائع! سنساعدك في الحصول على عرض سعر مجاني. أحتاج بعض المعلومات منك."
          : "Great! Let's get you a free solar quote. I just need a few details."
      );
      setTimeout(function () {
        renderLeadForm();
      }, 1200);
      return;
    }

    // 1. Check local keyword DB match
    const matchedFaq = matchKeywordFAQ(text, l);
    if (matchedFaq) {
      if (window.SolarAnalytics) {
        window.SolarAnalytics.track("chatbot_faq_match", { category: matchedFaq.key });
      }
      addBotMessage(matchedFaq.content, [
        { label: l === "ar" ? "📋 احصل على عرض" : "📋 Get a Quote", value: "quote" },
        { label: l === "ar" ? "🔙 القائمة الرئيسية" : "🔙 Main Menu", value: "menu" },
      ]);
      return;
    }

    // 2. Claude AI Proxy Route fallback
    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("chatbot_ai_query");
    }
    callAIProxy(text, l);
  }

  // AI HTTP Proxy Handlers
  function callAIProxy(prompt, lang) {
    showTyping();
    
    fetch("chatbot.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "ai_chat",
        prompt: prompt,
        lang: lang,
        calc_context: state.calculatorContext
      })
    })
      .then(res => res.json())
      .then(data => {
        hideTyping();
        const reply = data.reply || (lang === "ar"
          ? "آسف، لم أفهم سؤالك تماماً. هل تريد معرفة التكاليف أو حساب توفيرك الكهربائي؟"
          : "Sorry, I couldn't process that query. Would you like to check system costs or calculate electric offsets?");
        
        addMessage("bot", reply);
        renderQuickReplies([
          { label: lang === "ar" ? "📋 احصل على عرض" : "📋 Get a Quote", value: "quote" },
          { label: lang === "ar" ? "🔙 القائمة الرئيسية" : "🔙 Main Menu", value: "menu" },
        ]);
      })
      .catch(err => {
        hideTyping();
        console.error("[SolarBot] AI request failed: ", err);
        addBotMessage(
          lang === "ar"
            ? "أواجه حالياً صعوبة في الاتصال. كيف يمكنني إرشادك بخصوص التكلفة أو التوفير؟"
            : "I am having connection issues. How can I help you regarding costs, savings, or quotes?",
          getMainMenuReplies()
        );
      });
  }

  // ─── LEAD CAPTURING FORM ─────────────────────────────────────────────────────

  function renderLeadForm() {
    state.flow = FLOW.LEAD_CAPTURE;
    const t = TRANSLATIONS[state.lang].leadCapture;

    if (window.SolarAnalytics) {
      window.SolarAnalytics.track("lead_capture_start");
    }

    el.repliesList.style.display = "none";
    setSafeHTML(el.formPanel, `
      <input type="text" id="sb-lead-name" class="sb-input" placeholder="${t.name}" required />
      <input type="tel" id="sb-lead-phone" class="sb-input" placeholder="${t.phone}" required />
      <input type="email" id="sb-lead-email" class="sb-input" placeholder="${t.email} (${state.lang === "ar" ? "اختياري" : "Optional"})" />
      <input type="text" id="sb-lead-loc" class="sb-input" placeholder="${t.location}" required />
      <button class="sb-submit-btn" id="sb-form-submit">${state.lang === "ar" ? "إرسال ←" : "Submit →"}</button>
    `);
    el.formPanel.style.display = "block";
    
    // Focus first element
    document.getElementById("sb-lead-name").focus();
    scrollBottom();

    document.getElementById("sb-form-submit").addEventListener("click", submitLeadForm);
  }

  function submitLeadForm() {
    const nameVal = document.getElementById("sb-lead-name").value.trim();
    const phoneVal = document.getElementById("sb-lead-phone").value.trim();
    const emailVal = document.getElementById("sb-lead-email").value.trim();
    const locVal = document.getElementById("sb-lead-loc").value.trim();

    if (!nameVal || !phoneVal || !locVal) {
      alert(state.lang === "ar" ? "يرجى ملء جميع الحقول المطلوبة!" : "Please fill in all required fields!");
      return;
    }

    el.formPanel.style.display = "none";
    setSafeHTML(el.formPanel, "");

    const userLog = `${nameVal} | ${phoneVal} ${emailVal ? "| " + emailVal : ""} | ${locVal}`;
    addUserMessage(userLog);

    const leadObj = {
      name: nameVal,
      phone: phoneVal,
      email: emailVal,
      location: locVal,
      lang: state.lang,
      timestamp: new Date().toISOString(),
      calculator: state.calculatorContext
    };

    state.leadData = leadObj;
    addBotMessage(TRANSLATIONS[state.lang].leadCapture.submitted, [
      { label: state.lang === "ar" ? "🔙 القائمة الرئيسية" : "🔙 Main Menu", value: "menu" }
    ]);

    state.flow = FLOW.LEAD_DONE;

    if (window.SolarAnalytics) {
      window.SolarAnalytics.markFormSubmitted();
      window.SolarAnalytics.track("lead_submitted", {
        name: nameVal,
        phone: phoneVal,
        email: emailVal,
        location: locVal,
        system_size_kw: state.calculatorContext ? state.calculatorContext.systemSize : 0
      });
    }

    // Send HTTP lead trigger
    postLeadData(leadObj);
  }

  function postLeadData(lead) {
    fetch("chatbot.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        action: "submit_lead",
        data: lead
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === "ok") {
          console.log("[SolarBot] CRM lead capture sync completed ✓");
        } else {
          throw new Error("Failed proxy payload relay.");
        }
      })
      .catch(err => {
        // Safe localStorage queue recovery fallback
        console.warn("[SolarBot] Target offline — Lead queued locally: ", err);
        const queue = JSON.parse(localStorage.getItem("sb_lead_queue") || "[]");
        queue.push(lead);
        localStorage.setItem("sb_lead_queue", JSON.stringify(queue));
      });
  }

  function retryQueuedLeads() {
    const queue = JSON.parse(localStorage.getItem("sb_lead_queue") || "[]");
    if (queue.length === 0) return;

    console.log(`[SolarBot] Retrying ${queue.length} offline queued lead captures...`);
    localStorage.removeItem("sb_lead_queue");

    queue.forEach(function (queuedLead) {
      postLeadData(queuedLead);
    });
  }

  // ─── CALCULATOR INTERACTION API ──────────────────────────────────────────────

  function explainCalculatorResult(result) {
    state.calculatorContext = result;
    openChat();

    const isAr = state.lang === "ar";
    const msg = isAr
      ? `🧮 **نتائج حاسبة الطاقة الشمسية الخاصة بك:**\n\n• حجم النظام الموصى به: **${result.systemSize} كيلوواط**\n• عدد الألواح المطلوبة: **${result.panels} لوح**\n• التكلفة التقديرية للتركيب: **${result.cost} ريال**\n• التوفير الشهري المتوقع: **${result.monthlySavings} ريال**\n• التوفير السنوي المتوقع: **${result.yearlySavings} ريال**\n• فترة استرداد رأس المال: **${result.payback} سنوات**\n\nهل تريد الحصول على عرض سعر وتصميم مخصص لمنزلك مجاناً؟`
      : `🧮 **Your Solar Calculator Results:**\n\n• System Size: **${result.systemSize} kW**\n• Panels Needed: **${result.panels} panels**\n• Estimated Cost: **OMR ${result.cost}**\n• Monthly Savings: **OMR ${result.monthlySavings}**\n• Yearly Savings: **OMR ${result.yearlySavings}**\n• Payback Period: **${result.payback} years**\n\nWould you like a detailed custom quote and layout design?`;

    addBotMessage(msg, [
      { label: isAr ? "📋 احصل على عرض مخصص" : "📋 Get Custom Quote", value: "quote" },
      { label: isAr ? "❓ ما معنى هذا؟" : "❓ What does this mean?", value: "savings" },
    ]);
  }

  // ─── SMART TRIGGERS ──────────────────────────────────────────────────────────

  function startSmartTriggers() {
    // 1. Auto Greet Delay
    setTimeout(function () {
      if (!state.isOpen && !state.hasGreeted) {
        greetVisitor();
      }
    }, 5000);

    // 2. Exit Intent Trigger
    document.addEventListener("mouseleave", function (e) {
      if (e.clientY < 0 && !state.isOpen && !state.exitIntentFired) {
        state.exitIntentFired = true;
        greetVisitor();
      }
    });

    // 3. User Inactivity Trigger
    let inactiveTimer;
    const resetTimer = function () {
      clearTimeout(inactiveTimer);
      if (!state.isOpen) {
        inactiveTimer = setTimeout(greetVisitor, 45000);
      }
    };
    ["mousemove", "keydown", "scroll", "click"].forEach(function (e) {
      window.addEventListener(e, resetTimer);
    });
    resetTimer();
  }

  function greetVisitor() {
    state.hasGreeted = true;
    state.notificationCount++;
    setBadge(state.notificationCount);

    // Pulse button animation
    el.widgetBtn.classList.add("pulse");
    setTimeout(function () {
      el.widgetBtn.classList.remove("pulse");
    }, 4000);
  }

  function setBadge(count) {
    let badge = el.widgetBtn.querySelector(".sb-badge");
    if (!badge) {
      badge = document.createElement("span");
      badge.className = "sb-badge";
      el.widgetBtn.appendChild(badge);
    }
    badge.textContent = count;
  }

  function removeBadge() {
    const badge = el.widgetBtn.querySelector(".sb-badge");
    if (badge) badge.remove();
  }

  // ─── UTILITIES ────────────────────────────────────────────────────────────────

  function detectArabic(text) {
    const pattern = /[\u0600-\u06FF]/;
    return pattern.test(text);
  }

  function matchKeywordFAQ(text, lang) {
    const query = text.toLowerCase();
    for (const key in FAQ_DB) {
      const keywords = FAQ_DB[key].keywords;
      const matched = keywords.some(function (kw) {
        return query.includes(kw);
      });
      if (matched) {
        return {
          key: key,
          content: FAQ_DB[key][lang] || FAQ_DB[key].en
        };
      }
    }
    return null;
  }

  function formatMarkdown(text) {
    // Markdown replacements: Bold (**text** -> <strong>) & line breaks (\n -> <br>)
    return text
      .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
      .replace(/\n/g, "<br/>");
  }

  function scrollBottom() {
    setTimeout(function () {
      el.msgList.scrollTop = el.msgList.scrollHeight;
    }, 50);
  }

  // Trigger init on DOMContentLoaded or immediate load if page is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

})();
