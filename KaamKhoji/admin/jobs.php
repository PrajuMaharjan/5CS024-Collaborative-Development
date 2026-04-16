<?php
// ============================================================
// admin/jobs.php - Admin: Manage All Jobs
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

$pdo = getPDO();

// Filter by status
$filterStatus = $_GET['status'] ?? '';

if (in_array($filterStatus, ['active', 'closed'])) {
    $stmt = $pdo->prepare("
        SELECT jobs.*, users.name AS employer_name,
               COUNT(applications.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN applications ON jobs.id = applications.job_id
        WHERE jobs.status = ?
        GROUP BY jobs.id
        ORDER BY jobs.created_at DESC
    ");
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query("
        SELECT jobs.*, users.name AS employer_name,
               COUNT(applications.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN applications ON jobs.id = applications.job_id
        GROUP BY jobs.id
        ORDER BY jobs.created_at DESC
    ");
}
$jobs = $stmt->fetchAll();

$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

$pageTitle = 'Manage Jobs';
$pageCss = 'admin-pages';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Manage Jobs</h1>
        <p>View and delete all job listings</p>
    </div>
</div>

<div class="container section">

    <!-- Filter -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>
            <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn <?= !$filterStatus ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=active" class="btn <?= $filterStatus==='active' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Active</a>
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=closed" class="btn <?= $filterStatus==='closed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Closed</a>
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($jobs) ?> job(s)</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Employer</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Apps</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr><td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">No jobs found.</td></tr>
                <?php endif; ?>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="text-muted"><?= $job['id'] ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="text-primary">
                                <strong><?= htmlspecialchars($job['title']) ?></strong>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <td><?= htmlspecialchars($job['employer_name']) ?></td>
                        <td>
                            <span class="job-badge badge-<?= $job['type'] ?>" style="font-size:0.72rem;">
                                <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($job['location']) ?></td>
                        <td><?= $job['app_count'] ?></td>
                        <td>
                            <span class="status-badge status-<?= $job['status'] ?>">
                                <?= ucfirst($job['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                               onclick="return confirm('Delete this job and all its applications?')"
                               class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
