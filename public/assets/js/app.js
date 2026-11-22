// Butaca · progressive enhancement. Every page also works without JavaScript.
(() => {
  "use strict";

  const money = (value) => "$ " + Math.round(value).toLocaleString("es-CO");

  // Mobile navigation toggles (site header and admin sidebar).
  document.querySelectorAll("[data-nav-toggle]").forEach((button) => {
    const target = document.getElementById(button.getAttribute("aria-controls"));
    if (!target) return;
    button.addEventListener("click", () => {
      const open = target.classList.toggle("is-open");
      button.setAttribute("aria-expanded", String(open));
    });
  });

  // Confirmation before destructive actions.
  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });

  // Seat picker: live summary, total and maximum seats per purchase.
  const picker = document.querySelector("[data-seat-picker]");
  if (picker) {
    const max = Number(picker.dataset.max || 10);
    const price = Number(picker.dataset.price || 0);
    const list = picker.querySelector("[data-selected-list]");
    const total = picker.querySelector("[data-total]");
    const buy = picker.querySelector("[data-buy]");
    const inputs = Array.from(picker.querySelectorAll('input[name="seats[]"]'));

    const update = () => {
      const selected = inputs.filter((input) => input.checked);
      list.textContent = selected.length ? selected.map((i) => i.dataset.label).join(", ") : "Ninguna";
      total.textContent = money(selected.length * price);
      if (buy) buy.disabled = selected.length === 0;
      inputs.forEach((input) => {
        if (!input.checked && !input.closest(".seat--sold")) {
          input.disabled = selected.length >= max;
        }
      });
    };

    inputs.forEach((input) => input.addEventListener("change", update));
    update();
  }
})();
