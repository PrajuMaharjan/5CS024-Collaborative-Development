// ============================================================
// KaamKhoji - Main JavaScript
// Handles: Dark Mode, Hamburger Menu, AJAX Job Search,
//          Form Validation, Flash Message auto-dismiss
// ============================================================

// ---- Run everything after DOM is loaded ----
document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initHamburger();
    initFlashDismiss();
    initFormValidation();
    initJobSearch();
    initSaveJob();
    initApplyJob();
});

// ============================================================
// DARK MODE / LIGHT MODE TOGGLE
// Saves preference to localStorage so it persists on reload.
// ============================================================
function initTheme() {
    const toggleBtn = document.getElementById('themeToggle');
    const html = document.documentElement;

    // Load saved theme from localStorage (default: dark)
    const savedTheme = localStorage.getItem('kaamkhoji_theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', function () {
        const current = html.getAttribute('data-theme');
        const next = current === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', next);
        localStorage.setItem('kaamkhoji_theme', next);
        updateThemeIcon(next);
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('.theme-icon');
    if (icon) icon.textContent = theme === 'dark' ? '☀️' : '🌙';
}

// ============================================================
// HAMBURGER MENU (Mobile)
// ============================================================
function initHamburger() {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    if (!hamburger || !navLinks) return;

    hamburger.addEventListener('click', function () {
        navLinks.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', navLinks.classList.contains('open'));
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (e) {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('open');
        }
    });
}

// ============================================================
// FLASH MESSAGE AUTO-DISMISS
// Auto-removes flash messages after 4 seconds
// ============================================================
function initFlashDismiss() {
    const flash = document.getElementById('flashMsg');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transition = 'opacity 0.4s ease';
            setTimeout(() => flash.remove(), 400);
        }, 4000);
    }
}

// ============================================================
// AJAX JOB SEARCH
// Sends search request without page reload.
// Used on: pages/jobs.php
// ============================================================
function initJobSearch() {
    const searchForm = document.getElementById('jobSearchForm');
    const resultsDiv = document.getElementById('jobResults');
    const loadingDiv = document.getElementById('searchLoading');

    if (!searchForm || !resultsDiv) return;

    // Search on input (with debounce so we don't spam requests)
    let debounceTimer;
    const inputs = searchForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doSearch, 400); // wait 400ms after typing
        });
    });

    // Also search on form submit
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent page reload
        clearTimeout(debounceTimer);
        doSearch();
    });

    function doSearch() {
        const keyword  = document.getElementById('searchKeyword')?.value  || '';
        const location = document.getElementById('searchLocation')?.value || '';
        const type     = document.getElementById('searchType')?.value     || '';

        // Show loading spinner
        if (loadingDiv) loadingDiv.classList.remove('hidden');
        resultsDiv.style.opacity = '0.5';

        // Build query string
        const params = new URLSearchParams({ keyword, location, type });

        // AJAX request using Fetch API
        fetch(BASE_URL + '/api/search-jobs.php?' + params.toString())
            .then(res => res.json())             // Parse JSON response
            .then(data => {
                renderJobs(data);                // Show results
                if (loadingDiv) loadingDiv.classList.add('hidden');
                resultsDiv.style.opacity = '1';
            })
            .catch(err => {
                console.error('Search error:', err);
                resultsDiv.innerHTML = '<p class="text-muted text-center mt-3">Search failed. Please try again.</p>';
                if (loadingDiv) loadingDiv.classList.add('hidden');
                resultsDiv.style.opacity = '1';
            });
    }

    // Initial load - show all jobs
    doSearch();
}

// Render job cards from API response
function renderJobs(jobs) {
    const container = document.getElementById('jobResults');
    if (!container) return;

    if (!jobs || jobs.length === 0) {
        container.innerHTML = `
            <div class="empty-state fade-in">
                <div class="empty-icon">🔍</div>
                <h3>No jobs found</h3>
                <p>Try different keywords or filters.</p>
                <a href="${BASE_URL}/pages/jobs.php" class="btn btn-outline">Clear Search</a>
            </div>`;
        return;
    }

    const typeLabels = {
        'full-time':  'Full Time',
        'part-time':  'Part Time',
        'remote':     'Remote',
        'contract':   'Contract',
        'internship': 'Internship',
    };

    container.innerHTML = jobs.map(job => `
        <div class="card job-card fade-in">
            <div class="job-card-header">
                <div>
                    <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="job-title">${escHtml(job.title)}</a>
                    <div class="job-company">${escHtml(job.company)}</div>
                </div>
                <span class="job-badge badge-${escHtml(job.type)}">${escHtml(typeLabels[job.type] || job.type)}</span>
            </div>
            <div class="job-meta">
                <span>📍 ${escHtml(job.location)}</span>
                ${job.salary ? `<span>💰 ${escHtml(job.salary)}</span>` : ''}
                <span>📅 ${formatDate(job.created_at)}</span>
            </div>
            <p class="text-muted text-sm">${escHtml(job.description).substring(0, 120)}...</p>
            <div class="job-actions">
                <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="btn btn-primary btn-sm">View Details</a>
                <button onclick="saveJob(${escHtml(job.id)}, this)" class="btn btn-outline btn-sm">🔖 Save</button>
            </div>
        </div>
    `).join('');
}

// ============================================================
// SAVE JOB (AJAX)
// ============================================================
function initSaveJob() {
    // saveJob() is called inline from renderJobs, handled globally
}

function saveJob(jobId, btn) {
    btn.disabled = true;
    btn.textContent = 'Saving...';

    fetch(BASE_URL + '/api/save-job.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'job_id=' + encodeURIComponent(jobId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✅ Saved';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline');
        } else {
            btn.textContent = data.error || '⚠️ Login to save';
            btn.disabled = false;
        }
    })
    .catch(() => {
        btn.textContent = '⚠️ Error';
        btn.disabled = false;
    });
}

// ============================================================
// APPLY FOR JOB (AJAX)
// ============================================================
function initApplyJob() {
    const applyForm = document.getElementById('applyForm');
    if (!applyForm) return;

    applyForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = applyForm.querySelector('button[type="submit"]');
        const formData = new FormData(applyForm);

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch(BASE_URL + '/api/apply-job.php', {
            method: 'POST',
            body: new URLSearchParams(formData) // Convert FormData to URL params
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Application submitted successfully! 🎉', 'success');
                applyForm.reset();
                submitBtn.textContent = '✅ Applied!';
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Application';
            }
        })
        .catch(() => {
            showAlert('Network error. Try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Application';
        });
    });
}

// ============================================================
// FORM VALIDATION
// Basic client-side validation before submitting forms
// ============================================================
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            const required = form.querySelectorAll('[required]');
            required.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('invalid');
                    valid = false;
                } else {
                    field.classList.remove('invalid');
                }
            });

            // Email validation
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value && !isValidEmail(emailField.value)) {
                emailField.classList.add('invalid');
                valid = false;
            }

            // Password match validation
            const pass1 = form.querySelector('#password');
            const pass2 = form.querySelector('#confirm_password');
            if (pass1 && pass2 && pass1.value !== pass2.value) {
                pass2.classList.add('invalid');
                showAlert('Passwords do not match.', 'error');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });

        // Remove invalid class on input
        form.querySelectorAll('.form-control').forEach(field => {
            field.addEventListener('input', () => field.classList.remove('invalid'));
        });
    });
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

// Escape HTML to prevent XSS
function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Format date string
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Email validation
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Show a temporary alert message on any page
function showAlert(message, type = 'info') {
    // Remove existing alerts
    document.querySelectorAll('.js-alert').forEach(a => a.remove());

    const alert = document.createElement('div');
    alert.className = `flash flash-${type} js-alert`;
    alert.innerHTML = `${message} <button onclick="this.parentElement.remove()" class="flash-close">✕</button>`;

    // Insert after navbar
    const nav = document.querySelector('.navbar');
    if (nav) nav.insertAdjacentElement('afterend', alert);
    else document.body.prepend(alert);

    // Auto dismiss
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.4s ease';
        setTimeout(() => alert.remove(), 400);
    }, 4000);
}

// Confirm delete action
function confirmDelete(url, msg = 'Are you sure you want to delete this?') {
    if (confirm(msg)) {
        window.location.href = url;
    }
}
