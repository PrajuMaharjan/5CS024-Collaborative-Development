// ============================================================
// nav.js — Hamburger menu + Avatar dropdown
// Used by: all pages via header.php
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    // ---- Hamburger ----
    const hamburger = document.getElementById('hamburger');
    const navLinks  = document.getElementById('navLinks');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', navLinks.classList.contains('open'));
        });
    }

    // ---- Avatar dropdown ----
    const navAvatar   = document.getElementById('navAvatar');
    const navDropdown = document.getElementById('navDropdown');
    if (navAvatar && navDropdown) {
        navAvatar.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = navDropdown.classList.toggle('open');
            navAvatar.setAttribute('aria-expanded', isOpen);
        });
    }

    // Close both on outside click
    document.addEventListener('click', function (e) {
        if (hamburger && navLinks && !hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('open');
        }
        if (navAvatar && navDropdown && !navAvatar.contains(e.target) && !navDropdown.contains(e.target)) {
            navDropdown.classList.remove('open');
            navAvatar.setAttribute('aria-expanded', 'false');
        }
    });
});
