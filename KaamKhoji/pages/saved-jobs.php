<?php
// ============================================================
// pages/saved-jobs.php - Job Seeker: Saved/Bookmarked Jobs
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT jobs.*, saved_jobs.saved_at
    FROM saved_jobs
    JOIN jobs ON saved_jobs.job_id = jobs.id
    WHERE saved_jobs.seeker_id = ?
    ORDER BY saved_jobs.saved_at DESC
");
$stmt->execute([getUserId()]);
$savedJobs = $stmt->fetchAll();

$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

$pageTitle = 'Saved Jobs';
$pageCss = 'saved-jobs';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Saved Jobs</h1>
        <p>Jobs you bookmarked for later</p>
    </div>
</div>

<div class="container section">

    <?php if (empty($savedJobs)): ?>
        <div class="empty-state">
            <h3>No saved jobs yet</h3>
            <p>Click the "Save" button on any job listing to bookmark it here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>
        <div class="grid-3">
            <?php foreach ($savedJobs as $job): ?>
                <div class="card job-card">
                    <div class="job-card-header">
                        <div>
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="job-title">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                            <div class="job-company"><?= htmlspecialchars($job['company']) ?></div>
                        </div>
                        <span class="job-badge badge-<?= $job['type'] ?>">
                            <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                        </span>
                    </div>
                    <div class="job-meta">
                        <span><?= htmlspecialchars($job['location']) ?></span>
                        <?php if ($job['salary']): ?>
                            <span><?= htmlspecialchars($job['salary']) ?></span>
                        <?php endif; ?>
                        <span>Saved <?= date('M d', strtotime($job['saved_at'])) ?></span>
                    </div>
                    <div class="job-actions" style="align-items:center;">
                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View & Apply</a>
                        <button class="star-btn starred"
                                data-job-id="<?= $job['id'] ?>"
                                title="Unsave job">
                            <svg class="star-empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg class="star-filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
document.querySelectorAll('.star-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const jobId = btn.dataset.jobId;
        const card  = btn.closest('.card');
        fetch(BASE_URL + '/api/unsave-job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + encodeURIComponent(jobId)
        }).then(r => r.json()).then(d => {
            if (d.success && card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }
        }).catch(() => {});
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
