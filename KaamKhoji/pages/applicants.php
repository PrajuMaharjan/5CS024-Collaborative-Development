<?php
// ============================================================
// pages/applicants.php - Employer: View Applicants
// Shows all applications for employer's jobs
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();

// Optional: filter by specific job
$filterJobId = (int)($_GET['job_id'] ?? 0);

// Get all employer's jobs (for filter dropdown)
$jobsStmt = $pdo->prepare("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$jobsStmt->execute([$userId]);
$myJobs = $jobsStmt->fetchAll();

// Get applicants with job info
if ($filterJobId) {
    // Verify this job belongs to the employer
    $verifyStmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
    $verifyStmt->execute([$filterJobId, $userId]);
    if (!$verifyStmt->fetch()) $filterJobId = 0; // Reset if not authorized

    $stmt = $pdo->prepare("
        SELECT applications.*, users.name AS applicant_name, users.email AS applicant_email,
               users.location AS applicant_location, jobs.title AS job_title
        FROM applications
        JOIN users ON applications.seeker_id = users.id
        JOIN jobs ON applications.job_id = jobs.id
        WHERE jobs.employer_id = ? AND applications.job_id = ?
        ORDER BY applications.applied_at DESC
    ");
    $stmt->execute([$userId, $filterJobId]);
} else {
    $stmt = $pdo->prepare("
        SELECT applications.*, users.name AS applicant_name, users.email AS applicant_email,
               users.location AS applicant_location, jobs.title AS job_title
        FROM applications
        JOIN users ON applications.seeker_id = users.id
        JOIN jobs ON applications.job_id = jobs.id
        WHERE jobs.employer_id = ?
        ORDER BY applications.applied_at DESC
    ");
    $stmt->execute([$userId]);
}
$applicants = $stmt->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appId     = (int)$_POST['application_id'];
    $newStatus = $_POST['status'] ?? 'pending';
    $validStatuses = ['pending', 'reviewed', 'accepted', 'rejected'];
    if (!in_array($newStatus, $validStatuses)) $newStatus = 'pending';

    // Verify this application belongs to employer's job
    $stmt = $pdo->prepare("
        UPDATE applications SET status = ?
        WHERE id = ? AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)
    ");
    $stmt->execute([$newStatus, $appId, $userId]);

    header('Location: ' . BASE_URL . '/pages/applicants.php');
    exit;
}

$pageTitle = 'Applicants';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>👥 Applicants</h1>
        <p>Review candidates who applied for your jobs</p>
    </div>
</div>

<div class="container section">

    <!-- Filter by job -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:600; font-size:.9rem;">Filter by Job:</label>
            <select name="job_id" class="form-control" style="max-width:300px;" onchange="this.form.submit()">
                <option value="">All Jobs</option>
                <?php foreach ($myJobs as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= $filterJobId == $j['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($j['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="text-muted text-sm"><?= count($applicants) ?> applicant(s)</span>
        </form>
    </div>

    <?php if (empty($applicants)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>No applicants yet</h3>
            <p>Applicants will appear here once people apply for your jobs.</p>
        </div>

    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Job</th>
                        <th>Cover Letter</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($app['applicant_name']) ?></strong>
                                <?php if ($app['applicant_location']): ?>
                                    <div class="text-muted text-sm">📍 <?= htmlspecialchars($app['applicant_location']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($app['applicant_email']) ?></td>
                            <td><?= htmlspecialchars($app['job_title']) ?></td>
                            <td>
                                <?php if ($app['cover_letter']): ?>
                                    <span title="<?= htmlspecialchars($app['cover_letter']) ?>" style="cursor:help;">
                                        📄 <?= htmlspecialchars(substr($app['cover_letter'], 0, 40)) ?>...
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td>
                                <!-- Update status form -->
                                <form method="POST" style="display:flex; gap:0.4rem;">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <select name="status" class="form-control" style="font-size:0.8rem; padding:0.3rem 0.5rem;">
                                        <option value="pending"  <?= $app['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                                        <option value="reviewed" <?= $app['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                        <option value="accepted" <?= $app['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                        <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">✓</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
