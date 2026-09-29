/* ICT308 feedback fix — Vedic: personalised recommendation engine */
(function () {
  "use strict";
  if (!window.Warners) return;
  const W = window.Warners;
  // ------------------------------------------------------------
  // VEDIC — PERSONALIZED RECOMMENDATION ENGINE
  // ------------------------------------------------------------
  const RELATED_CATEGORIES = {
    Laptops: ["Accessories", "Audio", "Tablets", "Monitors"],
    Smartphones: ["Audio", "Accessories", "Wearables"],
    Audio: ["Accessories", "Smartphones", "Gaming"],
    Accessories: ["Laptops", "Smartphones", "Tablets", "Audio", "Gaming"],
    Smart Home : ["Accessories", "Audio"],
    Tablets: ["Accessories", "Laptops", "Audio"],
    Gaming: ["Accessories", "Audio", "Monitors", "Laptops", "Televisions"],
    Cameras: ["Accessories", "Storage"],
    Televisions: ["Audio", "Smart Home", "Accessories", "Gaming"],
    Wearables: ["Smartphones", "Accessories", "Audio"],
  };

  function str(value) {
    return String(value == null ? "" : value);
  }

  function normalizeWords(value) {
    return str(value)
      .toLowerCase()
      .split(/[^a-z0-9]+/)
      .map((x) => x.trim())
      .filter((x) => x.length >= 3);
  }

  function productText(product) {
    return [
      product?.name,
      product?.category,
      product?.brand,
      product?.subtitle,
      ...(Array.isArray(product?.tags) ? product.tags : []),
    ]
      .filter(Boolean)
      .join(" ")
      .toLowerCase();
  }

  function getUserLabel() {
    try {
      const u = JSON.parse(localStorage.getItem("warners_user") || "null");
      return u?.username || u?.name || "guest";
    } catch {
      return "guest";
    }
  }

  function getSignals(seedProductId, products) {
    const byId = new Map(products.map((p) => [str(p.id), p]));
    const signals = [];
    const seen = new Set();

    function add(id, type, strength, index) {
      const key = str(id);
      if (!key || !byId.has(key)) return;
      const uniqueKey = type + ":" + key;
      if (seen.has(uniqueKey)) return;
      seen.add(uniqueKey);
      signals.push({ product: byId.get(key), type, strength, index: index || 0 });
    }

    if (seedProductId) add(seedProductId, "seed", 1.35, 0);

    (W.getCart?.() || []).forEach((item, i) => {
      add(item.id, "cart", Math.max(0.85, 1.25 - i * 0.05), i);
    });

    (W.getViewedIds?.() || []).forEach((id, i) => {
      add(id, "view", Math.max(0.45, 1.05 - i * 0.06), i);
    });

    (W.getPurchasedIds?.() || []).forEach((id, i) => {
      add(id, "purchase", Math.max(0.40, 0.90 - i * 0.04), i);
    });

    return signals;
  }

  function getSearchTerms() {
    const history = W.getSearchHistory?.() || [];
    const words = [];
    history.slice(0, 8).forEach((query, index) => {
      normalizeWords(query).forEach((word) => words.push({ word, index }));
    });
    return words;
  }

  function signalBase(type) {
    if (type === "seed") return 14;
    if (type === "cart") return 12;
    if (type === "view") return 10;
    if (type === "purchase") return 8;
    return 1;
  }

  function categoryRelated(from, to) {
    const list = RELATED_CATEGORIES[from] || [];
    return list.includes(to);
  }

  function sharedTagCount(a, b) {
    const aa = new Set((a?.tags || []).map((x) => str(x).toLowerCase()));
    return (b?.tags || []).reduce(
      (count, x) => count + (aa.has(str(x).toLowerCase()) ? 1 : 0),
      0
    );
  }

  function addReason(record, reason, points) {
    record.score += points;
    if (points > 0.25 && reason) record.reasons.push({ reason, points });
  }

  function ruleApplies(rule, signal) {
    const action = str(rule?.triggerAction || "").toLowerCase();
    if (action && action !== signal.type && !(action === "view" && signal.type === "seed")) {
      return false;
    }
    return str(rule?.whenId) === str(signal.product?.id);
  }

  function diverseTop(records, maxItems) {
    const remaining = records.slice();
    const chosen = [];
    const categoryCounts = {};

    while (remaining.length && chosen.length < maxItems) {
      let bestIndex = 0;
      let bestAdjusted = -Infinity;

      remaining.forEach((record, index) => {
        const cat = record.product?.category || "Other";
        const penalty = (categoryCounts[cat] || 0) * 4.5;
        const adjusted = record.score - penalty;
        if (adjusted > bestAdjusted) {
          bestAdjusted = adjusted;
          bestIndex = index;
        }
      });

      const [picked] = remaining.splice(bestIndex, 1);
      chosen.push(picked);
      const cat = picked.product?.category || "Other";
      categoryCounts[cat] = (categoryCounts[cat] || 0) + 1;
    }

    return chosen;
  }

  W.getRecommendations = function improvedRecommendations(seedProductId) {
    const products = (W.getProducts?.() || []).filter((p) => Number(p.stock ?? 1) > 0);
    const signals = getSignals(seedProductId, products);
    const searchTerms = getSearchTerms();
    const rules = W.getRules?.() || [];

    const excluded = new Set();
    signals.forEach((s) => excluded.add(str(s.product.id)));

    const records = new Map();
    products.forEach((product) => {
      if (excluded.has(str(product.id))) return;
      records.set(str(product.id), {
        product,
        score: Math.max(0, Number(product.weight || 0)) * 0.15,
        reasons: [],
      });
    });

    // Activity-based scoring. Recent cart/view activity has the strongest effect.
    signals.forEach((signal) => {
      const seed = signal.product;
      const base = signalBase(signal.type) * signal.strength;

      records.forEach((record) => {
        const candidate = record.product;

        if (candidate.category && candidate.category === seed.category) {
          addReason(record, `same ${seed.category} category`, base * 0.62);
        } else if (categoryRelated(seed.category, candidate.category)) {
          addReason(record, `related to ${seed.category}`, base * 0.44);
        }

        if (candidate.brand && seed.brand && candidate.brand === seed.brand) {
          addReason(record, `same ${seed.brand} brand`, base * 0.30);
        }

        const shared = sharedTagCount(seed, candidate);
        if (shared) {
          addReason(record, `${shared} shared product tag${shared > 1 ? "s" : ""}`, base * 0.20 * Math.min(shared, 3));
        }

        const a = Number(candidate.price || 0);
        const b = Number(seed.price || 0);
        if (a > 0 && b > 0) {
          const similarity = Math.min(a, b) / Math.max(a, b);
          if (similarity >= 0.55) {
            addReason(record, "similar price range", base * 0.12 * similarity);
          }
        }
      });

      // Admin rules receive a clear boost and respect the configured trigger action.
      rules.forEach((rule) => {
        if (!ruleApplies(rule, signal)) return;
        const target = records.get(str(rule.thenId));
        if (target) addReason(target, `admin rule: ${rule.name || "recommended pairing"}`, base * 1.35 + 12);
      });
    });

    // Recent search history gives a smaller supporting signal.
    searchTerms.forEach(({ word, index }) => {
      const searchStrength = Math.max(0.5, 1 - index * 0.08);
      records.forEach((record) => {
        if (productText(record.product).includes(word)) {
          addReason(record, `matches recent search “${word}”`, 3.2 * searchStrength);
        }
      });
    });

    let rankedRecords = [...records.values()]
      .filter((record) => record.score > 0)
      .sort((a, b) => b.score - a.score || str(a.product.name).localeCompare(str(b.product.name)));

    const hasPersonalActivity = signals.length > 0 || searchTerms.length > 0;

    // New-user fallback: category-balanced popular products rather than one fixed block.
    if (!hasPersonalActivity || !rankedRecords.length) {
      rankedRecords = [...records.values()]
        .sort((a, b) => Number(b.product.weight || 0) - Number(a.product.weight || 0))
        .map((record) => {
          record.reasons.push({ reason: "popular new-user fallback", points: 0 });
          return record;
        });
    }

    const selected = diverseTop(rankedRecords, 8);

    window.WarnersRecommendationDebug = {
      user: getUserLabel(),
      generatedAt: new Date().toISOString(),
      personalized: hasPersonalActivity,
      signals: signals.map((s) => ({
        id: str(s.product.id),
        name: s.product.name,
        type: s.type,
        strength: Number(s.strength.toFixed(2)),
      })),
      searches: searchTerms.map((x) => x.word),
      ranked: selected.map((record) => ({
        id: str(record.product.id),
        name: record.product.name,
        score: Number(record.score.toFixed(2)),
        reasons: record.reasons
          .sort((a, b) => b.points - a.points)
          .slice(0, 3)
          .map((r) => r.reason),
      })),
    };

    updateRecommendationExplanation();
    return selected.map((record) => record.product);
  };

  W.getRecommendationDebug = function () {
    return window.WarnersRecommendationDebug || null;
  };


  function createElement(tag, attrs, text) {
    const el = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([key, value]) => {
      if (key === "style") el.style.cssText = value;
      else if (key === "class") el.className = value;
      else el.setAttribute(key, value);
    });
    if (text != null) el.textContent = text;
    return el;
  }

  // ------------------------------------------------------------
  // RECOMMENDATION EXPLANATION PANEL FOR THE SHOWCASE
  // ------------------------------------------------------------
  function ensureRecommendationPanel() {
    if (!location.pathname.endsWith("recommendations.html")) return;
    if (document.getElementById("recommendation-engine-explainer")) return;

    const recs = document.getElementById("recs");
    if (!recs) return;

    const panel = createElement("section", {
      id: "recommendation-engine-explainer",
      style:
        "margin:14px 0 22px;padding:16px 18px;border:1px solid #bfdbfe;border-left:5px solid #2563eb;border-radius:10px;background:#eff6ff;box-shadow:0 8px 22px rgba(37,99,235,.08);",
    });

    panel.innerHTML = `
      <div style="display:flex;gap:12px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;">
        <div style="min-width:220px;flex:1;">
          <strong style="display:block;font-size:1rem;margin-bottom:5px;">How the recommendation engine works</strong>
          <span style="font-size:.88rem;line-height:1.5;color:#334155;">Views, cart items, purchases, recent searches, product category/brand/tags and Admin rules are converted into weighted scores. Products are ranked by score, then diversified so the list changes with each user's behaviour.</span>
        </div>
        <span id="recommendation-engine-status" style="font-size:.78rem;font-weight:800;padding:6px 9px;border-radius:999px;background:#dbeafe;color:#1d4ed8;">Waiting for activity</span>
      </div>
      <div id="recommendation-engine-details" style="margin-top:10px;font-size:.82rem;color:#475569;"></div>`;

    recs.parentNode.insertBefore(panel, recs);
  }

  function updateRecommendationExplanation() {
    const status = document.getElementById("recommendation-engine-status");
    const details = document.getElementById("recommendation-engine-details");
    const debug = window.WarnersRecommendationDebug;
    if (!status || !details || !debug) return;

    const counts = debug.signals.reduce((acc, s) => {
      acc[s.type] = (acc[s.type] || 0) + 1;
      return acc;
    }, {});

    status.textContent = debug.personalized ? "Live personalised ranking" : "New-user fallback";
    const signalText = [
      counts.view ? `${counts.view} view${counts.view > 1 ? "s" : ""}` : "",
      counts.cart ? `${counts.cart} cart item${counts.cart > 1 ? "s" : ""}` : "",
      counts.purchase ? `${counts.purchase} purchase${counts.purchase > 1 ? "s" : ""}` : "",
      debug.searches.length ? `${debug.searches.length} search signal${debug.searches.length > 1 ? "s" : ""}` : "",
    ].filter(Boolean);

    const top = debug.ranked.slice(0, 3).map((r) => {
      const why = r.reasons?.length ? ` — ${r.reasons.join(", ")}` : "";
      return `${r.name} (${r.score})${why}`;
    });

    details.textContent = signalText.length
      ? `Signals used: ${signalText.join(" · ")}. Top ranking: ${top.join(" | ")}`
      : "No personal activity yet. The engine is showing a balanced popular-product fallback until the customer browses, searches, adds to cart, or purchases.";
  }

  ensureRecommendationPanel();

})();
