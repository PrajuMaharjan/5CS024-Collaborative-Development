<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();

// Get jobs with stats
$stmt = $pdo->prepare("
    SELECT jobs.id, jobs.title, jobs.type, jobs.status,
           COUNT(applications.id) AS total_apps,
           SUM(applications.status = 'accepted') AS accepted,
           SUM(applications.status = 'rejected') AS rejected,
           SUM(applications.status = 'pending')  AS pending
    FROM jobs
    LEFT JOIN applications ON jobs.id = applications.job_id
    WHERE jobs.employer_id = ?
    GROUP BY jobs.id
    ORDER BY total_apps DESC
");
$stmt->execute([$userId]);
$jobStats = $stmt->fetchAll();

$totalApplications = array_sum(array_column($jobStats, 'total_apps'));
$maxApps = max(1, max(array_column($jobStats, 'total_apps') ?: [1]));

$pageTitle = 'Analytics';
$pageCss = 'analytics';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Analytics</h1>
        <p>See how your job postings are performing</p>
    </div>
</div>

<div class="container section">

    <!-- Overview Stats -->
    <div class="grid-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= count($jobStats) ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= array_sum(array_column($jobStats, 'accepted')) ?></div>
                <div class="stat-label">Accepted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= array_sum(array_column($jobStats, 'pending')) ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
    </div>

    <!-- Per-Job Analytics -->
    <?php if (empty($jobStats)): ?>
        <div class="empty-state">
            <h3>No data yet</h3>
            <p>Post jobs to see analytics here.</p>
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary">Post a Job</a>
        </div>
    <?php else: ?>
        <div class="card">
            <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">Applications per Job</h3>

            <?php foreach ($jobStats as $stat): ?>
                <div style="margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);">
                    <div class="d-flex justify-between align-center mb-1">
                        <div>
                            <strong><?= htmlspecialchars($stat['title']) ?></strong>
                            <span class="status-badge status-<?= $stat['status'] ?>" style="margin-left:0.5rem;">
                                <?= ucfirst($stat['status']) ?>
                            </span>
                        </div>
                        <span class="text-primary" style="font-weight:700; font-size:1.1rem;">
                            <?= $stat['total_apps'] ?> apps
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div class="analytics-bar-wrap">
                        <div class="analytics-bar-bg">
                            <div class="analytics-bar-fill"
                                 style="width:<?= $maxApps > 0 ? round(($stat['total_apps']/$maxApps)*100) : 0 ?>%">
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown -->
                    <div style="display:flex; gap:1rem; margin-top:0.5rem; font-size:0.82rem; flex-wrap:wrap;">
                        <span style="color:var(--warning);">Pending: <?= $stat['pending'] ?></span>
                        <span style="color:var(--success);">Accepted: <?= $stat['accepted'] ?></span>
                        <span style="color:var(--error);">Rejected: <?= $stat['rejected'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
