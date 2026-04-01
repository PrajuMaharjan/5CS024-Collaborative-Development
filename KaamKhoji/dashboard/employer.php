<?php
// ============================================================
// dashboard/employer.php - Employer Dashboard
// Shows: jobs posted, total applicants, recent activity
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo = getPDO();
$userId = getUserId();

// Stats
$totalJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$totalJobs->execute([$userId]);
$totalJobs = $totalJobs->fetchColumn();

$activeJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND status = 'active'");
$activeJobs->execute([$userId]);
$activeJobs = $activeJobs->fetchColumn();

$totalApplicants = $pdo->prepare("
    SELECT COUNT(*) FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    WHERE jobs.employer_id = ?
");
$totalApplicants->execute([$userId]);
$totalApplicants = $totalApplicants->fetchColumn();

// Recent jobs with applicant count
$stmt = $pdo->prepare("
    SELECT jobs.*,
           COUNT(applications.id) AS applicant_count
    FROM jobs
    LEFT JOIN applications ON jobs.id = applications.job_id
    WHERE jobs.employer_id = ?
    GROUP BY jobs.id
    ORDER BY jobs.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentJobs = $stmt->fetchAll();

$pageTitle = 'Employer Dashboard';
require_once '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1>🏢 Employer Dashboard</h1>
        <p>Manage your job postings and review applicants.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- Stats -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon">💼</div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalJobs ?></div>
                <div class="stat-label">Total Jobs Posted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <div class="stat-value"><?= $activeJobs ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplicants ?></div>
                <div class="stat-label">Total Applicants</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-3">
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">Quick Actions</h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary">➕ Post a Job</a>
            <a href="<?= BASE_URL ?>/pages/applicants.php" class="btn btn-outline">👥 View Applicants</a>
            <a href="<?= BASE_URL ?>/pages/analytics.php" class="btn btn-outline">📊 Analytics</a>
            <a href="<?= BASE_URL ?>/pages/profile.php" class="btn btn-outline">👤 Edit Profile</a>
        </div>
    </div>

    <!-- Recent Job Postings -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h3 style="font-size:1rem; font-weight:600;">Your Job Postings</h3>
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary btn-sm">+ Post New</a>
        </div>

        <?php if (empty($recentJobs)): ?>
            <div class="empty-state" style="padding:2rem;">
                <div class="empty-icon">📭</div>
                <h3>No jobs posted yet</h3>
                <p>Start attracting candidates by posting your first job.</p>
                <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary">Post a Job</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Type</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentJobs as $job): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                <td><?= ucfirst(str_replace('-', ' ', $job['type'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/applicants.php?job_id=<?= $job['id'] ?>" class="text-primary">
                                        <?= $job['applicant_count'] ?> applicants
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $job['status'] ?>">
                                        <?= ucfirst($job['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-outline btn-sm">View</a>
                                    <a href="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                                       onclick="return confirm('Delete this job?')"
                                       class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
