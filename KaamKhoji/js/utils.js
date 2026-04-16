// ============================================================
// utils.js — Shared utility functions
// Used by: all pages
// ============================================================

// Escape HTML to prevent XSS
function escHtml(str) {
  if (str === null || str === undefined) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

// Format date string
function formatDate(dateStr) {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

// Email validation
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Show a temporary alert message on any page
function showAlert(message, type = "info") {
  document.querySelectorAll(".js-alert").forEach((a) => a.remove());

  const alert = document.createElement("div");
  alert.className = `flash flash-${type} js-alert`;
  alert.innerHTML = `${message} <button onclick="this.parentElement.remove()" class="flash-close">✕</button>`;

  const nav = document.querySelector(".navbar");
  if (nav) nav.insertAdjacentElement("afterend", alert);
  else document.body.prepend(alert);

  setTimeout(() => {
    alert.style.opacity = "0";
    alert.style.transition = "opacity 0.4s ease";
    setTimeout(() => alert.remove(), 400);
  }, 4000);
}

// Confirm delete action
function confirmDelete(url, msg = "Are you sure you want to delete this?") {
  if (confirm(msg)) {
    window.location.href = url;
  }
}
