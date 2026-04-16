// ============================================================
// form-validation.js — Client-side form validation
// Used by: login.php, signup.php, pages/post-job.php, pages/profile.php
// Requires: utils.js (showAlert, isValidEmail)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form[data-validate]');
    if (!forms.length) return;

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
});
