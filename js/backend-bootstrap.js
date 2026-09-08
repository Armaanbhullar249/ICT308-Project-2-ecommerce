(function () {
  function request(method, url, data) {
    try {
      const xhr = new XMLHttpRequest();
      xhr.open(method, url, false);
      xhr.setRequestHeader("Accept", "application/json");
      if (data !== undefined) xhr.setRequestHeader("Content-Type", "application/json");
      xhr.send(data !== undefined ? JSON.stringify(data) : null);
      const body = JSON.parse(xhr.responseText || "{}");
      if (xhr.status >= 200 && xhr.status < 300 && body.ok !== false) return body;
      return { ok: false, error: body.error || `HTTP ${xhr.status}` };
    } catch (e) {
      return { ok: false, error: e.message || "Backend unavailable" };
    }
  }

  function apply(data) {
    if (!data || !data.ok) return false;
    localStorage.setItem("warners_products", JSON.stringify(data.products || []));
    localStorage.setItem("warners_categories", JSON.stringify(data.categories || []));
    localStorage.setItem("warners_rules", JSON.stringify(data.rules || []));
    localStorage.setItem("warners_customers", JSON.stringify(data.customers || []));
    localStorage.setItem("warners_orders", JSON.stringify(data.orders || []));

    if (data.user) {
      localStorage.setItem("warners_auth", "1");
      localStorage.setItem("warners_role", data.user.role);
      localStorage.setItem("warners_user", JSON.stringify(data.user));
      localStorage.setItem("warners_cart", JSON.stringify(data.cart || []));
      localStorage.setItem("warners_viewed", JSON.stringify(data.viewed || []));
      localStorage.setItem("warners_purchased", JSON.stringify(data.purchased || []));
      localStorage.setItem(`warners_search_history_${data.user.username}`, JSON.stringify(data.searchHistory || []));
      const current = data.currentProfile || (data.customers || []).find((c) => c.username === data.user.username) || data.user;
      localStorage.setItem("warners_current_profile", JSON.stringify(current));
    } else {
      localStorage.removeItem("warners_auth");
      localStorage.removeItem("warners_role");
      localStorage.removeItem("warners_user");
      localStorage.removeItem("warners_current_profile");
    }
    return true;
  }

  function seedFromFiles() {
    const products = window.WARNERS_PRODUCTS || [];
    if (!products.length) return;
    localStorage.setItem("warners_products", JSON.stringify(products));
    localStorage.setItem("warners_categories", JSON.stringify(window.WARNERS_CATEGORIES || []));
    localStorage.setItem("warners_rules", JSON.stringify(window.WARNERS_RULES || []));
  }

  window.WarnersBackend = { request, apply, refresh: function () { const r = request("GET", "api/bootstrap.php"); if (r.ok) apply(r); return r; } };
  const result = window.WarnersBackend.refresh();
  window.WARNERS_BACKEND_READY = !!result.ok;
  window.WARNERS_BACKEND_ERROR = result.ok ? "" : (result.error || "Backend unavailable");
  if (!result.ok) seedFromFiles();
})();
