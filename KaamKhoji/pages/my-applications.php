<?php
// ============================================================
// pages/my-applications.php - Job Seeker: View Applications
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT applications.*, jobs.title AS job_title, jobs.company,
           jobs.location, jobs.type, jobs.status AS job_status
    FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    WHERE applications.seeker_id = ?
    ORDER BY applications.applied_at DESC
");
$stmt->execute([getUserId()]);
$applications = $stmt->fetchAll();

$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

$pageTitle = 'My Applications';
$pageCss = 'my-applications';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>My Applications</h1>
        <p>Track the status of all your job applications</p>
    </div>
</div>

<div class="container section">

    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <h3>No applications yet</h3>
            <p>Start applying for jobs and track your progress here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>
        <!-- Summary -->
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <?php
            $counts = array_count_values(array_column($applications, 'status'));
            $labels = ['pending'=>'Pending','reviewed'=>'Reviewed','accepted'=>'Accepted','rejected'=>'Rejected'];
            foreach ($labels as $key => $label):
                if (($counts[$key] ?? 0) > 0):
            ?>
                <div class="stat-card" style="flex:1; min-width:140px;">
                    <div class="stat-info">
                        <div class="stat-value"><?= $counts[$key] ?? 0 ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                </div>
            <?php endif; endforeach; ?>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $app['job_id'] ?>" class="text-primary">
                                    <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($app['company']) ?></td>
                            <td>
                                <span class="job-badge badge-<?= $app['type'] ?>" style="font-size:0.72rem;">
                                    <?= $typeLabels[$app['type']] ?? $app['type'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($app['location']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $app['job_id'] ?>" class="btn btn-outline btn-sm">View Job</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
