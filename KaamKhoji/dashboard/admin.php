<?php
// ============================================================
// dashboard/admin.php - Admin Dashboard
// Full overview: users, jobs, applications
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

$pdo = getPDO();

// Site-wide stats
$totalUsers       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSeekers     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='seeker'")->fetchColumn();
$totalEmployers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn();
$totalJobs        = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$activeJobs       = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='active'")->fetchColumn();
$totalApplications= $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Recent users
$recentUsers = $pdo->query("
    SELECT * FROM users ORDER BY created_at DESC LIMIT 5
")->fetchAll();

// Recent jobs
$recentJobs = $pdo->query("
    SELECT jobs.*, users.name AS employer_name
    FROM jobs
    JOIN users ON jobs.employer_id = users.id
    ORDER BY jobs.created_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Admin Dashboard';
$pageCss = 'admin-dashboard';
require_once '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Overview of all platform activity.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- Stats Grid -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalJobs ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalSeekers ?></div>
                <div class="stat-label">Job Seekers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalEmployers ?></div>
                <div class="stat-label">Employers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $activeJobs ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-3">
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">Admin Actions</h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-primary">Manage Users</a>
            <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn btn-outline">Manage Jobs</a>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-outline">View All Jobs</a>
        </div>
    </div>

    <div class="grid-2">
        <!-- Recent Users -->
        <div class="card">
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size:1rem; font-weight:600;">Recent Users</h3>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Name</th><th>Role</th><th>Joined</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $user['role'] === 'admin' ? 'reviewed' : ($user['role'] === 'employer' ? 'accepted' : 'pending') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] !== getUserId()): ?>
                                        <a href="<?= BASE_URL ?>/api/delete.php?type=user&id=<?= $user['id'] ?>"
                                           onclick="return confirm('Delete user <?= htmlspecialchars(addslashes($user['name'])) ?>?')"
                                           class="btn btn-danger btn-sm">Delete</a>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card">
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size:1rem; font-weight:600;">Recent Jobs</h3>
                <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Title</th><th>Employer</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentJobs as $job): ?>
                            <tr>
                                <td><?= htmlspecialchars($job['title']) ?></td>
                                <td><?= htmlspecialchars($job['employer_name']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $job['status'] ?>">
                                        <?= ucfirst($job['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                                       onclick="return confirm('Delete this job?')"
                                       class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
