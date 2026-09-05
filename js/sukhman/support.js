/* Sukhman — customer support (public) */
Warners.renderHeader("support");

const formPanel = document.getElementById("support-form-panel");
const hoursPanel = document.getElementById("support-hours-panel");

document.getElementById("open-support-form")?.addEventListener("click", () => {
  formPanel.hidden = !formPanel.hidden;
  hoursPanel.hidden = true;
  if (!formPanel.hidden) {
    formPanel.scrollIntoView({ behavior: "smooth", block: "start" });
    document.getElementById("support-name")?.focus();
  }
});

document.getElementById("show-hours")?.addEventListener("click", () => {
  hoursPanel.hidden = !hoursPanel.hidden;
  formPanel.hidden = true;
  if (!hoursPanel.hidden) {
    hoursPanel.scrollIntoView({ behavior: "smooth", block: "start" });
  }
});

document.getElementById("support-form")?.addEventListener("submit", (e) => {
  e.preventDefault();
  const result = Warners.sendSupport({
    name: document.getElementById("support-name")?.value.trim() || "",
    email: document.getElementById("support-email")?.value.trim() || "",
    subject: document.getElementById("support-topic")?.value || "Customer support",
    message: document.getElementById("support-message")?.value.trim() || "",
  });
  if (!result.ok) {
    Warners.toast(result.error || "Could not send message");
    return;
  }
  Warners.toast("Message sent — our team will reply soon");
  e.target.reset();
  formPanel.hidden = true;
});

document.getElementById("support-chat")?.addEventListener("click", () => {
  formPanel.hidden = false;
  hoursPanel.hidden = true;
  formPanel.scrollIntoView({ behavior: "smooth", block: "start" });
  Warners.toast("Live chat opens the support form in this demo");
});
