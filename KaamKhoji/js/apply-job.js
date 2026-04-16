// apply-job.js — AJAX job application form + cover letter word count
// Used by: pages/job-detail.php
// Requires: utils.js (showAlert)

const WORD_MIN = 200;
const WORD_MAX = 250;

function countWords(str) {
    return str.trim() === '' ? 0 : str.trim().split(/\s+/).length;
}

document.addEventListener('DOMContentLoaded', function () {
    // ---- Cover letter word count ----
    const coverLetter  = document.getElementById('cover_letter');
    const wordCountNum = document.getElementById('wordCountNum');
    const wordCountMsg = document.getElementById('wordCountMsg');

    if (coverLetter && wordCountNum) {
        coverLetter.addEventListener('input', function () {
            let text  = coverLetter.value;
            let words = countWords(text);

            // Block beyond 250 words
            if (words > WORD_MAX) {
                const trimmed = text.trim().split(/\s+/).slice(0, WORD_MAX).join(' ');
                coverLetter.value = trimmed + ' ';
                words = WORD_MAX;
            }

            wordCountNum.textContent = words + ' / ' + WORD_MAX + ' words';

            if (wordCountMsg) {
                if (words > WORD_MAX - 15 && words <= WORD_MAX) {
                    wordCountMsg.textContent = 'Approaching limit';
                    wordCountMsg.style.color = '#f59e0b';
                    wordCountNum.style.color = '#f59e0b';
                } else if (words >= WORD_MIN) {
                    wordCountMsg.textContent = 'Good length';
                    wordCountMsg.style.color = '#10b981';
                    wordCountNum.style.color = '#10b981';
                } else {
                    wordCountMsg.textContent = '';
                    wordCountNum.style.color = 'var(--text-muted)';
                }
            }
        });
    }

    // ---- Apply form submit ----
    const applyForm = document.getElementById('applyForm');
    if (!applyForm) return;

    applyForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = applyForm.querySelector('button[type="submit"]');
        const formData  = new FormData(applyForm);

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Submitting...';

        fetch(BASE_URL + '/api/apply-job.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Application submitted successfully!', 'success');
                applyForm.reset();
                if (wordCountNum) { wordCountNum.textContent = '0 / 250 words'; wordCountNum.style.color = 'var(--text-muted)'; }
                if (wordCountMsg) { wordCountMsg.textContent = ''; }
                submitBtn.textContent = 'Applied!';
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Submit Application';
            }
        })
        .catch(() => {
            showAlert('Network error. Try again.', 'error');
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Submit Application';
        });
    });
});
