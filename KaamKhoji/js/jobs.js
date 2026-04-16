// ============================================================
// jobs.js — AJAX job search and save job
// Used by: pages/jobs.php
// Requires: utils.js (escHtml, formatDate, showAlert)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    initJobSearch();
});

function initJobSearch() {
    const searchForm = document.getElementById('jobSearchForm');
    const resultsDiv = document.getElementById('jobResults');
    const loadingDiv = document.getElementById('searchLoading');
    if (!searchForm || !resultsDiv) return;

    let debounceTimer;
    searchForm.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doSearch, 400);
        });
    });

    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        doSearch();
    });

    function doSearch() {
        const keyword  = document.getElementById('searchKeyword')?.value  || '';
        const location = document.getElementById('searchLocation')?.value || '';
        const type     = document.getElementById('searchType')?.value     || '';

        if (loadingDiv) loadingDiv.classList.remove('hidden');
        resultsDiv.style.opacity = '0.5';

        const params = new URLSearchParams({ keyword, location, type });

        fetch(BASE_URL + '/api/search-jobs.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                renderJobs(data);
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

    // Initial load — show all jobs
    doSearch();
}

function renderJobs(jobs) {
    const container = document.getElementById('jobResults');
    if (!container) return;

    if (!jobs || jobs.length === 0) {
        container.innerHTML = `
            <div class="empty-state fade-in">
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

    const starSvgEmpty  = `<svg class="star-empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;
    const starSvgFilled = `<svg class="star-filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`;

    container.innerHTML = jobs.map(job => `
        <div class="card job-card fade-in">
            <div class="job-card-header">
                <div>
                    <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="job-title">${escHtml(job.title)}</a>
                    <div class="job-company">${escHtml(job.company)}</div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;">
                    <span class="job-badge badge-${escHtml(job.type)}">${escHtml(typeLabels[job.type] || job.type)}</span>
                    <button class="star-btn ${job.is_saved ? 'starred' : ''}"
                            data-job-id="${escHtml(job.id)}"
                            title="${job.is_saved ? 'Unsave job' : 'Save job'}">
                        ${starSvgEmpty}${starSvgFilled}
                    </button>
                </div>
            </div>
            <div class="job-meta">
                <span>${escHtml(job.location)}</span>
                ${job.salary ? `<span>${escHtml(job.salary)}</span>` : ''}
                <span>${formatDate(job.created_at)}</span>
            </div>
            <p class="text-muted text-sm">${escHtml(job.description).substring(0, 120)}...</p>
            <div class="job-actions">
                <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="btn btn-primary btn-sm">View Details</a>
            </div>
        </div>
    `).join('');

    // Attach star toggle listeners
    container.querySelectorAll('.star-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleStarJob(btn);
        });
    });
}

function toggleStarJob(btn) {
    const jobId   = btn.dataset.jobId;
    const starred = btn.classList.contains('starred');

    if (starred) {
        fetch(BASE_URL + '/api/unsave-job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + encodeURIComponent(jobId)
        }).then(r => r.json()).then(d => {
            if (d.success) {
                btn.classList.remove('starred');
                btn.title = 'Save job';
            } else if (d.error) {
                showAlert(d.error, 'error');
            }
        }).catch(() => {});
    } else {
        fetch(BASE_URL + '/api/save-job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + encodeURIComponent(jobId)
        }).then(r => r.json()).then(d => {
            if (d.success) {
                btn.classList.add('starred');
                btn.title = 'Unsave job';
            } else if (d.error) {
                showAlert(d.error, 'error');
            }
        }).catch(() => {});
    }
}
