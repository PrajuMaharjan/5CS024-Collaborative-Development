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
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>🔖 Saved Jobs</h1>
        <p>Jobs you bookmarked for later</p>
    </div>
</div>

<div class="container section">

    <?php if (empty($savedJobs)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔖</div>
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
                        <span>📍 <?= htmlspecialchars($job['location']) ?></span>
                        <?php if ($job['salary']): ?>
                            <span>💰 <?= htmlspecialchars($job['salary']) ?></span>
                        <?php endif; ?>
                        <span>🔖 Saved <?= date('M d', strtotime($job['saved_at'])) ?></span>
                    </div>
                    <div class="job-actions">
                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View & Apply</a>
                        <a href="<?= BASE_URL ?>/api/unsave-job.php?job_id=<?= $job['id'] ?>"
                           onclick="return confirm('Remove from saved jobs?')"
                           class="btn btn-danger btn-sm">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
