/* ICT308 feedback fix — Kushal: forgot-password UI for local demo */
(function () {
  "use strict";
  if (!window.Warners) return;
  const W = window.Warners;
  // ------------------------------------------------------------
  // KUSHAL — FORGOT PASSWORD UI (LOCAL ACADEMIC DEMO)
  // ------------------------------------------------------------
  function request(method, url, data) {
    if (window.WarnersBackend?.request) return window.WarnersBackend.request(method, url, data);
    return { ok: false, error: "Backend unavailable." };
  }

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

  function injectForgotPassword() {
    if (!/login\.html(?:$|[?#])/.test(location.pathname + location.search) && !location.pathname.endsWith("/login.html")) return;
    const form = document.getElementById("login-form");
    if (!form || document.getElementById("forgot-password-fix")) return;

    const box = createElement("section", {
      id: "forgot-password-fix",
      hidden: "hidden",
      style:
        "margin-top:14px;padding:16px;border:1px solid #dbe3ee;border-radius:10px;background:#f8fafc;box-shadow:0 8px 20px rgba(15,23,42,.06);",
    });

    const title = createElement("h3", { style: "margin:0 0 6px;font-size:1rem;" }, "Reset your password");
    const note = createElement(
      "p",
      { style: "margin:0 0 12px;font-size:.88rem;line-height:1.45;color:#475569;" },
      "Enter the email registered with your Warner account. For the local project demo, a secure one-time token is generated automatically."
    );

    const email = createElement("input", {
      id: "forgot-email",
      type: "email",
      required: "required",
      autocomplete: "email",
      placeholder: "Registered email address",
      style:
        "width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:10px;font:inherit;",
    });

    const requestBtn = createElement(
      "button",
      {
        type: "button",
        id: "forgot-request-btn",
        style:
          "width:100%;padding:11px 14px;border:0;border-radius:8px;background:#0f172a;color:#fff;font-weight:700;cursor:pointer;",
      },
      "Generate reset link"
    );

    const resetStep = createElement("div", {
      id: "forgot-reset-step",
      hidden: "hidden",
      style: "margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0;",
    });

    const newPass = createElement("input", {
      id: "forgot-new-password",
      type: "password",
      autocomplete: "new-password",
      placeholder: "New password",
      style:
        "width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px;font:inherit;",
    });

    const confirmPass = createElement("input", {
      id: "forgot-confirm-password",
      type: "password",
      autocomplete: "new-password",
      placeholder: "Confirm new password",
      style:
        "width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:10px;font:inherit;",
    });

    const resetBtn = createElement(
      "button",
      {
        type: "button",
        id: "forgot-reset-btn",
        style:
          "width:100%;padding:11px 14px;border:0;border-radius:8px;background:#0f766e;color:#fff;font-weight:700;cursor:pointer;",
      },
      "Set new password"
    );

    const message = createElement("p", {
      id: "forgot-message",
      role: "status",
      style: "margin:10px 0 0;font-size:.86rem;line-height:1.4;color:#334155;",
    });

    resetStep.append(newPass, confirmPass, resetBtn);
    box.append(title, note, email, requestBtn, resetStep, message);

    const open = createElement(
      "button",
      {
        id: "forgot-password-open",
        type: "button",
        style:
          "border:0;background:transparent;padding:4px 0;color:#0369a1;font:inherit;font-size:.88rem;font-weight:700;cursor:pointer;text-decoration:underline;text-underline-offset:3px;",
      },
      "Forgot password?"
    );

    const submit = form.querySelector('button[type="submit"]');
    if (submit) form.insertBefore(open, submit);
    else form.appendChild(open);
    //form.appendChild(box);
    form.after(box);

    let token = "";

    open.addEventListener("click", () => {
      box.hidden = !box.hidden;
      if (!box.hidden) email.focus();
    });

    requestBtn.addEventListener("click", () => {
      message.textContent = "";
      const value = email.value.trim().toLowerCase();
      if (!value || !email.checkValidity()) {
        message.textContent = "Enter a valid registered email address.";
        return;
      }

      const result = request("POST", "api/password-reset.php", {
        action: "forgot",
        email: value,
      });

      if (!result.ok) {
        message.textContent = result.error || "Could not start password reset.";
        return;
      }

      message.textContent = result.message || "Reset request created.";
      token = result.demoToken || "";
      if (token) {
        resetStep.hidden = false;
        newPass.focus();
      }
    });

    resetBtn.addEventListener("click", () => {
      const password = newPass.value;
      const confirm = confirmPass.value;
      const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{4,}$/;

      if (!token) {
        message.textContent = "Generate a reset link first.";
        return;
      }
      if (!passwordPattern.test(password)) {
        message.textContent = "Password needs at least 1 uppercase letter, 1 lowercase letter, and 1 symbol.";
        return;
      }
      if (password !== confirm) {
        message.textContent = "Passwords do not match.";
        return;
      }

      const result = request("POST", "api/password-reset.php", {
        action: "reset",
        token,
        newPassword: password,
      });

      if (!result.ok) {
        message.textContent = result.error || "Password reset failed.";
        return;
      }

      message.textContent = "Password updated. You can now sign in with the new password.";
      token = "";
      resetStep.hidden = true;
      newPass.value = "";
      confirmPass.value = "";
    });
  }


  injectForgotPassword();
})();
