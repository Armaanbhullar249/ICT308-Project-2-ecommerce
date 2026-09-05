(function () {
  if (!window.Warners || !window.WarnersBackend) return;
  const B = window.WarnersBackend;
  const W = window.Warners;

  function req(method, url, data) { return B.request(method, url, data); }
  function refresh() { return B.refresh(); }
  function setList(key, list) { localStorage.setItem(key, JSON.stringify(list || [])); }
  function currentUser() { try { return JSON.parse(localStorage.getItem("warners_user") || "null"); } catch { return null; } }
  function resultError(r, fallback) { return { ok: false, error: r?.error || fallback }; }

  W.registerCustomer = function (data) {
    const r = req("POST", "api/auth.php", { action: "register", ...data });
    return r.ok ? { ok: true } : resultError(r, "Could not create account.");
  };

  W.login = function (username, password) {
    let guestCart = [];
    try { guestCart = JSON.parse(localStorage.getItem("warners_cart") || "[]"); } catch {}
    const r = req("POST", "api/auth.php", { action: "login", username, password, guestCart });
    if (!r.ok || !r.user) return null;
    refresh();
    return { role: r.user.role, label: r.user.name, initial: r.user.initial };
  };

  W.logout = function () {
    req("POST", "api/auth.php", { action: "logout" });
    ["warners_auth","warners_role","warners_user","warners_cart","warners_viewed","warners_purchased","warners_orders","warners_customers","warners_current_profile"].forEach(k => localStorage.removeItem(k));
    location.href = "home.html";
  };

  W.addToCart = function (productId, qty) {
    if (!W.isLoggedIn()) {
      const cart = W.getCart(); const p = W.productById(productId); const stock = Number(p?.stock || 0);
      const found = cart.find(i => String(i.id) === String(productId)); const cur = found ? Number(found.qty) : 0;
      if (cur >= stock) { W.toast(`${p?.name || "Item"} is out of stock`); return; }
      const add = Math.min(Number(qty || 1), Math.max(0, stock-cur));
      if (found) found.qty += add; else cart.push({ id: String(productId), qty: add });
      W.saveCart(cart); W.trackView(productId); W.toast("Added to cart"); return;
    }
    const r = req("POST", "api/cart.php", { action: "add", productId, qty: qty || 1 });
    if (!r.ok) { W.toast(r.error || "Could not add item"); return; }
    setList("warners_cart", r.cart); W.trackView(productId); W.toast("Added to cart");
  };

  W.setQty = function (productId, qty) {
    if (!W.isLoggedIn()) {
      const cart=W.getCart(); const item=cart.find(i=>String(i.id)===String(productId)); if(!item)return;
      if(Number(qty)<=0)return W.removeFromCart(productId); const p=W.productById(productId); item.qty=Math.min(Math.max(1,Number(qty)||1),Number(p?.stock||1));W.saveCart(cart);return;
    }
    const r=req("POST","api/cart.php",{action:"set",productId,qty});if(r.ok)setList("warners_cart",r.cart);else W.toast(r.error||"Could not update cart");
  };
  W.removeFromCart = function (productId) {
    if (!W.isLoggedIn()) { W.saveCart(W.getCart().filter(i=>String(i.id)!==String(productId))); return; }
    const r=req("POST","api/cart.php",{action:"remove",productId});if(r.ok)setList("warners_cart",r.cart);else W.toast(r.error||"Could not remove item");
  };
  W.bindAddButtons = function (root) {
    (root || document).addEventListener("click", function (e) { const id=e.target.getAttribute("data-add"); if(id) W.addToCart(id); });
  };

  W.trackView = function (productId) {
    let viewed=[];try{viewed=JSON.parse(localStorage.getItem("warners_viewed")||"[]");}catch{}
    viewed=[String(productId),...viewed.filter(id=>String(id)!==String(productId))].slice(0,12);setList("warners_viewed",viewed);
    if(W.isLoggedIn()) req("POST","api/activity.php",{action:"view",productId});
  };
  W.recordSearch = function (query) {
    const q=String(query||"").trim();if(q.length<2)return;const user=currentUser();const key=user?.username?`warners_search_history_${user.username}`:"warners_search_history";
    let list=[];try{list=JSON.parse(localStorage.getItem(key)||"[]");}catch{} list=[q,...list.filter(x=>String(x).toLowerCase()!==q.toLowerCase())].slice(0,12);setList(key,list);
    if(W.isLoggedIn())req("POST","api/activity.php",{action:"search",query:q});
  };
  W.clearSearchHistory = function () { const user=currentUser();const key=user?.username?`warners_search_history_${user.username}`:"warners_search_history";localStorage.removeItem(key);if(W.isLoggedIn())req("POST","api/activity.php",{action:"clearSearch"}); };

  W.placeOrder = function (checkout) {
    const r=req("POST","api/orders.php",{action:"create",...checkout});
    if(!r.ok)return resultError(r,"Could not place order.");
    setList("warners_orders",r.orders||[]);setList("warners_cart",r.cart||[]);setList("warners_products",r.products||[]);
    const purchased=(r.order?.items||[]).map(i=>String(i.id)).filter(Boolean);setList("warners_purchased",[...purchased,...W.getPurchasedIds().filter(id=>!purchased.includes(String(id)))].slice(0,24));
    return {ok:true,order:r.order};
  };

  W.updateCustomerProfile = function (data, options) {
    const r=req("POST","api/profile.php",data);if(!r.ok)return resultError(r,"Could not update profile.");
    const list=W.getCustomers();const idx=list.findIndex(c=>c.username===r.profile.username);if(idx>=0)list[idx]={...list[idx],...r.profile};else list.push(r.profile);setList("warners_customers",list);
    if(r.user){localStorage.setItem("warners_user",JSON.stringify(r.user));localStorage.setItem("warners_current_profile",JSON.stringify(r.profile));}
    if(!options?.silent)W.toast("Profile updated");return {ok:true,profile:r.profile};
  };
  W.saveCustomerSettings = function (partial, options) {
    const r=req("POST","api/settings.php",partial);if(!r.ok)return resultError(r,"Could not save settings.");
    const list=W.getCustomers();const user=currentUser();const idx=list.findIndex(c=>c.username===user?.username);if(idx>=0){list[idx].settings=r.settings;setList("warners_customers",list);}
    if(!options?.silent)W.toast("Settings saved");return {ok:true,settings:r.settings};
  };
  W.updateCustomerPassword = function (currentPassword,newPassword) { const r=req("POST","api/auth.php",{action:"password",currentPassword,newPassword});if(!r.ok)return resultError(r,"Could not update password.");W.toast("Password updated");return {ok:true}; };
  W.updateStaffPassword = W.updateCustomerPassword;
  W.getStaffProfile = function () { try { const p=JSON.parse(localStorage.getItem("warners_current_profile")||"null"); if(p){return {username:p.username,role:p.role,name:p.name||p.username,email:p.email||"",phone:p.phone||"",label:p.role==="owner"?"Store owner":"Catalogue manager"};} }catch{} const u=currentUser()||{};return {username:u.username||"",role:u.role||"",name:u.name||u.username||"Staff",email:"",phone:"",label:u.role==="owner"?"Store owner":"Catalogue manager"}; };
  W.saveStaffProfile = function (data, options) { const r=req("POST","api/profile.php",data);if(!r.ok)return resultError(r,"Could not update profile.");localStorage.setItem("warners_current_profile",JSON.stringify(r.profile));if(r.user)localStorage.setItem("warners_user",JSON.stringify(r.user));if(!options?.silent)W.toast("Profile updated");return {ok:true,profile:W.getStaffProfile()}; };

  W.addProduct = function (data) { const r=req("POST","api/products.php",{action:"create",...data});if(!r.ok){W.toast(r.error||"Could not add product");return null;}setList("warners_products",r.products);W.toast("Product added");return W.productById(r.productId); };
  W.updateProduct = function (id, patch) { const r=req("POST","api/products.php",{action:"update",id,...patch});if(!r.ok){W.toast(r.error||"Could not update product");return;}setList("warners_products",r.products);W.toast("Product updated"); };
  W.deleteProduct = function (id) { const r=req("POST","api/products.php",{action:"delete",id});if(!r.ok){W.toast(r.error||"Could not delete product");return;}setList("warners_products",r.products);W.toast("Product deleted"); };
  W.addCategory = function (name) { const r=req("POST","api/categories.php",{action:"add",name});if(!r.ok){W.toast(r.error||"Could not add category");return;}setList("warners_categories",r.categories);W.toast("Category added"); };
  W.deleteCategory = function (name) { const r=req("POST","api/categories.php",{action:"delete",name});if(!r.ok){W.toast(r.error||"Could not remove category");return;}setList("warners_categories",r.categories);W.toast("Category removed"); };
  W.addRule = function (rule) { const r=req("POST","api/rules.php",{action:"add",...rule});if(!r.ok){W.toast(r.error||"Could not add rule");return;}setList("warners_rules",r.rules);W.toast("Rule added"); };
  W.deleteRule = function (index) { const rules=W.getRules();const id=rules[index]?.id;if(!id)return;const r=req("POST","api/rules.php",{action:"delete",id});if(!r.ok){W.toast(r.error||"Could not delete rule");return;}setList("warners_rules",r.rules);W.toast("Rule deleted"); };
  W.clearOrders = function () { const r=req("POST","api/orders.php",{action:"clear"});if(!r.ok){W.toast(r.error||"Could not clear orders");return false;}setList("warners_orders",[]);W.toast("Sales history cleared");return true; };
  W.clearCatalogue = function () { const r=req("POST","api/products.php",{action:"clear"});if(!r.ok){W.toast(r.error||"Could not clear catalogue");return false;}setList("warners_products",[]);setList("warners_categories",[]);setList("warners_rules",[]);W.toast("Catalogue cleared");return true; };
  W.saveCustomers = function (list) { const previous=W.getCustomers(); for(const c of list){const old=previous.find(x=>x.username===c.username);if(old && !!old.disabled!==!!c.disabled)req("POST","api/customers.php",{action:"status",username:c.username,disabled:!!c.disabled});}setList("warners_customers",list); };
  W.promoteOwner = function (username) { const r=req("POST","api/customers.php",{action:"promoteOwner",username}); if(!r.ok)return resultError(r,"Could not promote account."); refresh(); return {ok:true}; };
  W.sendSupport = function (data) { const r=req("POST","api/support.php",data);return r.ok?{ok:true}:resultError(r,"Could not send message."); };

  document.addEventListener("change", function(e){ if(!e.target.matches('input[name="appearance"]') || W.getRole()!=="customer") return; setTimeout(function(){ W.saveCustomerSettings({appearance:e.target.value},{silent:true}); },0); }, true);
})();
