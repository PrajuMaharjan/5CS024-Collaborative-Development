<?php
// ============================================================
// pages/post-job.php - Employer: Post a New Job
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer'); // Only employers can post jobs

$pdo   = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $title       = trim($_POST['title']       ?? '');
    $company     = trim($_POST['company']     ?? '');
    $location    = trim($_POST['location']    ?? '');
    $type        = $_POST['type']             ?? 'full-time';
    $salary      = trim($_POST['salary']      ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements= trim($_POST['requirements']?? '');

    $validTypes = ['full-time','part-time','remote','contract','internship'];
    if (!in_array($type, $validTypes)) $type = 'full-time';

    if (empty($title) || empty($company) || empty($location) || empty($description)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert job using PDO prepared statement
        $stmt = $pdo->prepare("
            INSERT INTO jobs (employer_id, title, company, location, type, salary, description, requirements)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            getUserId(), $title, $company, $location,
            $type, $salary, $description, $requirements
        ]);

        header('Location: ' . BASE_URL . '/dashboard/employer.php?msg=job_posted');
        exit;
    }
}

$pageTitle = 'Post a Job';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>➕ Post a New Job</h1>
        <p>Fill in the details below to attract the right candidates</p>
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

            <form method="POST" action="<?= BASE_URL ?>/pages/post-job.php" data-validate>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="title">Job Title *</label>
                        <input type="text" id="title" name="title"
                               class="form-control"
                               placeholder="e.g. PHP Developer"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                               required>
                        <span class="form-error">Job title is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company Name *</label>
                        <input type="text" id="company" name="company"
                               class="form-control"
                               placeholder="e.g. Tech Corp"
                               value="<?= htmlspecialchars($_POST['company'] ?? '') ?>"
                               required>
                        <span class="form-error">Company name is required.</span>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="location">Location *</label>
                        <input type="text" id="location" name="location"
                               class="form-control"
                               placeholder="e.g. Kathmandu / Remote"
                               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                               required>
                        <span class="form-error">Location is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Job Type *</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="full-time"  <?= (($_POST['type']??'')  === 'full-time')  ? 'selected' : '' ?>>Full Time</option>
                            <option value="part-time"  <?= (($_POST['type']??'')  === 'part-time')  ? 'selected' : '' ?>>Part Time</option>
                            <option value="remote"     <?= (($_POST['type']??'')  === 'remote')     ? 'selected' : '' ?>>Remote</option>
                            <option value="contract"   <?= (($_POST['type']??'')  === 'contract')   ? 'selected' : '' ?>>Contract</option>
                            <option value="internship" <?= (($_POST['type']??'')  === 'internship') ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="salary">Salary Range <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <input type="text" id="salary" name="salary"
                           class="form-control"
                           placeholder="e.g. Rs. 40,000 - 60,000 / month"
                           value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Job Description *</label>
                    <textarea id="description" name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Describe the role, responsibilities, and what makes it exciting..."
                              required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <span class="form-error">Description is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="requirements" name="requirements"
                              class="form-control"
                              rows="4"
                              placeholder="Skills, experience, education requirements..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                </div>

                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">🚀 Post Job</button>
                    <a href="<?= BASE_URL ?>/dashboard/employer.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
