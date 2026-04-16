// ============================================================
// flash.js — Flash message auto-dismiss
// Used by: all pages via footer.php
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const flash = document.getElementById('flashMsg');
    if (!flash) return;

    setTimeout(() => {
        flash.style.opacity = '0';
        flash.style.transition = 'opacity 0.4s ease';
        setTimeout(() => flash.remove(), 400);
    }, 4000);
});
