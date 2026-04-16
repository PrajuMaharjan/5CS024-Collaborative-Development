<?php
// ============================================================
// pages/edit-job.php - Employer: Edit an Existing Job
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();
$jobId  = (int)($_GET['id'] ?? 0);
$error  = '';

if (!$jobId) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php');
    exit;
}

// Load the job — verify it belongs to this employer
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title']        ?? '');
    $company      = trim($_POST['company']      ?? '');
    $location     = trim($_POST['location']     ?? '');
    $type         = $_POST['type']              ?? 'full-time';
    $salary       = trim($_POST['salary']       ?? '');
    $description  = trim($_POST['description']  ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $status       = $_POST['status']            ?? 'active';

    $validTypes   = ['full-time','part-time','remote','contract','internship'];
    $validStatuses= ['active','closed'];
    if (!in_array($type, $validTypes))     $type   = 'full-time';
    if (!in_array($status, $validStatuses)) $status = 'active';

    if (empty($title) || empty($company) || empty($location) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE jobs
            SET title=?, company=?, location=?, type=?, salary=?,
                description=?, requirements=?, status=?
            WHERE id=? AND employer_id=?
        ");
        $stmt->execute([
            $title, $company, $location, $type, $salary,
            $description, $requirements, $status,
            $jobId, $userId
        ]);

        header('Location: ' . BASE_URL . '/dashboard/employer.php?msg=job_posted');
        exit;
    }

    // Keep edited values on error
    $job = array_merge($job, compact('title','company','location','type','salary','description','requirements','status'));
}

$pageTitle = 'Edit Job';
$pageCss = 'post-job';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Edit Job</h1>
        <p>Update the details for: <strong><?= htmlspecialchars($job['title']) ?></strong></p>
    </div>
</div>

<div class="container section">
    <div style="max-width:720px; margin:0 auto;">
        <div class="card">

            <?php if ($error): ?>
                <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/pages/edit-job.php?id=<?= $jobId ?>" data-validate>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="title">Job Title *</label>
                        <input type="text" id="title" name="title"
                               class="form-control"
                               value="<?= htmlspecialchars($job['title']) ?>"
                               required>
                        <span class="form-error">Job title is required.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company">Company Name *</label>
                        <input type="text" id="company" name="company"
                               class="form-control"
                               value="<?= htmlspecialchars($job['company']) ?>"
                               required>
                        <span class="form-error">Company name is required.</span>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="location">Location *</label>
                        <input type="text" id="location" name="location"
                               class="form-control"
                               value="<?= htmlspecialchars($job['location']) ?>"
                               required>
                        <span class="form-error">Location is required.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="type">Job Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <?php
                            $types = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];
                            foreach ($types as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $job['type'] === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="salary">Salary Range <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <input type="text" id="salary" name="salary"
                               class="form-control"
                               value="<?= htmlspecialchars($job['salary'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active"  <?= $job['status'] === 'active'  ? 'selected' : '' ?>>Active</option>
                            <option value="closed"  <?= $job['status'] === 'closed'  ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Job Description *</label>
                    <textarea id="description" name="description"
                              class="form-control" rows="6"
                              required><?= htmlspecialchars($job['description']) ?></textarea>
                    <span class="form-error">Description is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="requirements" name="requirements"
                              class="form-control" rows="4"><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
                </div>

                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                    <a href="<?= BASE_URL ?>/dashboard/employer.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
