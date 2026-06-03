/**
 * analytics.js
 * Concept Technologies LLC — Solar Acquisition Analytics Module
 * 
 * Tracks conversion telemetry, scroll milestones, calculator interactions,
 * chatbot engagement, and forms. Integrates natively with GTM, GA4, and Meta Pixel.
 */

(function () {
  "use strict";

  const SESSION_START = Date.now();
  let trackedScrollMilestones = { 25: false, 50: false, 75: false, 100: false };
  let calculatorTouched = false;
  let formFocused = false;
  let formSubmitted = false;

  // ─── INITIALIZATION ──────────────────────────────────────────────────────────

  function init() {
    bindGlobalListeners();
    track("session_start", { referrer: document.referrer || "direct" });
  }

  // ─── CORE TELEMETRY TRACKER ──────────────────────────────────────────────────

  /**
   * Pushes a structured event payload to all active tracking channels.
   * 
   * @param {string} eventName - Name of the event (snake_case)
   * @param {Object} [properties] - Metadata related to the event
   */
  function track(eventName, properties = {}) {
    const payload = {
      event: eventName,
      timestamp: new Date().toISOString(),
      url: window.location.href,
      path: window.location.pathname,
      language: document.documentElement.getAttribute("lang") || "en",
      properties: properties
    };

    // 1. Google Tag Manager / GA4 Data Layer Integration
    if (window.dataLayer && typeof window.dataLayer.push === "function") {
      window.dataLayer.push(payload);
    }

    // 2. Meta Pixel Integration (Mapped Events)
    if (window.fbq && typeof window.fbq === "function") {
      if (eventName === "lead_submitted") {
        window.fbq("track", "Lead", {
          content_name: "Solar Enquiry",
          value: properties.system_size_kw || 0,
          currency: "OMR"
        });
      } else if (eventName === "calculator_complete") {
        window.fbq("track", "CustomizeProduct", {
          content_category: "Solar Calculator",
          content_name: properties.property_type || "residential"
        });
      } else {
        window.fbq("trackCustom", eventName, properties);
      }
    }

    // 3. Beacon: only for high-value events (not every micro-interaction)
    const beaconEvents = ["lead_submitted", "calculator_change", "session_end", "form_abandoned"];
    if (navigator.sendBeacon && beaconEvents.includes(eventName) && window.location.protocol.startsWith('http')) {
      const blob = new Blob([JSON.stringify({ action: "analytics_log", data: payload })], {
        type: "application/json"
      });
      navigator.sendBeacon("chatbot.php", blob);
    }
  }

  // ─── TRACKING LISTENERS ──────────────────────────────────────────────────────

  function bindGlobalListeners() {
    // 1. Scroll Depth Milestones (Throttled for high mobile performance)
    let scrollTimeout;
    window.addEventListener("scroll", function () {
      if (scrollTimeout) return;
      scrollTimeout = setTimeout(function () {
        scrollTimeout = null;
        checkScrollDepth();
      }, 250);
    });

    // 2. Outbound Link & CTA Tracking (WhatsApp, Phone)
    document.addEventListener("click", function (e) {
      const target = e.target.closest("a");
      if (!target) return;

      const href = target.getAttribute("href") || "";
      
      // WhatsApp Click Track
      if (href.includes("wa.me") || href.includes("api.whatsapp.com") || target.classList.contains("wa-trigger")) {
        track("whatsapp_click", {
          destination: href,
          text: target.textContent.trim()
        });
      }
      
      // Hotline Call Track
      if (href.startsWith("tel:")) {
        track("phone_call_click", {
          number: href.replace("tel:", "")
        });
      }
    });

    // 3. Form Abandonment & Focus Triggers
    document.addEventListener("focusin", function (e) {
      if (e.target.closest("#sb-form-panel") || e.target.closest(".calc-form") || e.target.closest(".contact-form")) {
        if (!formFocused) {
          formFocused = true;
          track("form_engagement_start", {
            form_type: e.target.closest("#sb-form-panel") ? "chatbot_lead" : "calculator_lead"
          });
        }
      }
    });

    // 4. Session Exit Intent (Desktop Only)
    document.addEventListener("mouseleave", function (e) {
      if (e.clientY < 0) {
        track("exit_intent_detected");
      }
    });

    // 5. Unload Beacon (Calculates precise session durations)
    window.addEventListener("beforeunload", function () {
      const durationSec = Math.round((Date.now() - SESSION_START) / 1000);
      
      // Detect form abandonment (focused but left without submitting)
      if (formFocused && !formSubmitted) {
        track("form_abandoned", { session_duration_sec: durationSec });
      }

      track("session_end", {
        session_duration_sec: durationSec,
        calculator_touched: calculatorTouched
      });
    });
  }

  // Scroll math computations
  function checkScrollDepth() {
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (docHeight <= 0) return;

    const scrollPct = Math.round((window.scrollY / docHeight) * 100);

    [25, 50, 75, 100].forEach(function (milestone) {
      if (scrollPct >= milestone && !trackedScrollMilestones[milestone]) {
        trackedScrollMilestones[milestone] = true;
        track("scroll_depth", { milestone_percent: milestone });
      }
    });
  }

  // ─── API EXPOSURE ────────────────────────────────────────────────────────────

  window.SolarAnalytics = {
    track: track,
    markCalculatorTouched: function () { calculatorTouched = true; },
    markFormSubmitted: function () { formSubmitted = true; }
  };

  // Run after idle to avoid blocking TBT
  if (typeof requestIdleCallback === "function") {
    requestIdleCallback(init, { timeout: 3000 });
  } else {
    // Fallback for Safari: 2.5s delay to let critical render complete
    setTimeout(init, 2500);
  }

})();
