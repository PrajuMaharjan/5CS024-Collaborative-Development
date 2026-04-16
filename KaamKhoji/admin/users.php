<?php
// ============================================================
// admin/users.php - Admin: Manage All Users
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

$pdo = getPDO();

// Filter by role
$filterRole = $_GET['role'] ?? '';
$validRoles = ['seeker', 'employer', 'admin'];

if (in_array($filterRole, $validRoles)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
    $stmt->execute([$filterRole]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
$pageCss = 'admin-pages';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Manage Users</h1>
        <p>View and delete user accounts</p>
    </div>
</div>

<div class="container section">

    <!-- Filter -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn <?= !$filterRole ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=seeker" class="btn <?= $filterRole === 'seeker'   ? 'btn-primary' : 'btn-outline' ?> btn-sm">Job Seekers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=employer" class="btn <?= $filterRole === 'employer' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Employers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=admin" class="btn <?= $filterRole === 'admin'    ? 'btn-primary' : 'btn-outline' ?> btn-sm">Admins</a>
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($users) ?> user(s)</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="text-muted"><?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="status-badge <?= $u['role'] === 'admin' ? 'status-reviewed' : ($u['role'] === 'employer' ? 'status-accepted' : 'status-pending') ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['location'] ?? '—') ?></td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] !== getUserId()): ?>
                                <a href="<?= BASE_URL ?>/api/delete.php?type=user&id=<?= $u['id'] ?>"
                                    onclick="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>? This will also delete their jobs and applications.')"
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

<?php require_once '../includes/footer.php'; ?>