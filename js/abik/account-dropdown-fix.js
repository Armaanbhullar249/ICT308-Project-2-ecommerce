/* ICT308 feedback fix — Abik: easier click-based account dropdown */
(function () {
  "use strict";
  if (!window.Warners) return;
  const W = window.Warners;
  // ------------------------------------------------------------
  // ABIK — CLICK-BASED ACCOUNT DROPDOWN
  // ------------------------------------------------------------
  function enhanceAccountDropdowns(root) {
    const scope = root || document;
    scope.querySelectorAll(".header-account").forEach((account) => {
      if (account.dataset.easyDropdown === "1") return;
      account.dataset.easyDropdown = "1";

      const trigger = account.querySelector(".header-icon-link");
      const menu = account.querySelector(".header-account-menu");
      if (!trigger || !menu) return;

      // Inline styling: bigger target, no hover gap, stronger stacking.
      trigger.style.minWidth = "44px";
      trigger.style.minHeight = "44px";
      trigger.style.display = "inline-flex";
      trigger.style.alignItems = "center";
      trigger.style.justifyContent = "center";
      trigger.style.cursor = "pointer";
      trigger.setAttribute("aria-haspopup", "menu");
      trigger.setAttribute("aria-expanded", "false");

      menu.style.top = "100%";
      menu.style.minWidth = "190px";
      menu.style.padding = "8px 0";
      menu.style.zIndex = "9999";
      menu.style.borderRadius = "8px";

      menu.querySelectorAll("a, button").forEach((item) => {
        item.style.minHeight = "44px";
        item.style.padding = "12px 16px";
        item.style.display = "flex";
        item.style.alignItems = "center";
        item.style.cursor = "pointer";
      });

      function openMenu() {
        menu.style.display = "grid";
        trigger.setAttribute("aria-expanded", "true");
      }

      function closeMenu() {
        menu.style.display = "none";
        trigger.setAttribute("aria-expanded", "false");
      }

      function isOpen() {
        return trigger.getAttribute("aria-expanded") === "true";
      }

      // A click on the account icon now opens the menu instead of requiring precise hover movement.
      trigger.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        isOpen() ? closeMenu() : openMenu();
      });

      account.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          closeMenu();
          trigger.focus();
        }
      });

      menu.addEventListener("click", (event) => event.stopPropagation());
      document.addEventListener("click", closeMenu);

      // Keep keyboard accessibility.
      trigger.addEventListener("keydown", (event) => {
        if (event.key === "ArrowDown" || event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          openMenu();
          const first = menu.querySelector("a, button");
          if (first) first.focus();
        }
      });
    });
  }

  if (typeof W.renderHeader === "function") {
    const originalRenderHeader = W.renderHeader;
    W.renderHeader = function () {
      const result = originalRenderHeader.apply(this, arguments);
      enhanceAccountDropdowns(document);
      return result;
    };
  }

  setTimeout(() => enhanceAccountDropdowns(document), 0);


})();
