<?php
// ============================================================
// pages/job-detail.php - Single Job Detail Page
// Shows full job info + apply form for seekers
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

$pdo = getPDO();

// Get job ID from URL
$jobId = (int)($_GET['id'] ?? 0);
if (!$jobId) {
    header('Location: ' . BASE_URL . '/pages/jobs.php');
    exit;
}

// Fetch job details
$stmt = $pdo->prepare("
    SELECT jobs.*, users.name AS employer_name, users.location AS employer_location
    FROM jobs
    JOIN users ON jobs.employer_id = users.id
    WHERE jobs.id = ? AND jobs.status = 'active'
");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/pages/jobs.php');
    exit;
}

// Check if user already applied
$alreadyApplied = false;
$alreadySaved   = false;
if (isLoggedIn() && getRole() === 'seeker') {
    $checkApply = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
    $checkApply->execute([$jobId, getUserId()]);
    $alreadyApplied = (bool)$checkApply->fetch();

    $checkSave = $pdo->prepare("SELECT id FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
    $checkSave->execute([$jobId, getUserId()]);
    $alreadySaved = (bool)$checkSave->fetch();
}

$typeLabels = [
    'full-time'  => 'Full Time',
    'part-time'  => 'Part Time',
    'remote'     => 'Remote',
    'contract'   => 'Contract',
    'internship' => 'Internship',
];

$pageTitle = $job['title'];
require_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb container">
    <a href="<?= BASE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= BASE_URL ?>/pages/jobs.php">Jobs</a>
    <span class="breadcrumb-sep">/</span>
    <span><?= htmlspecialchars($job['title']) ?></span>
</div>

<!-- Job Detail Layout: main content + sidebar -->
<div class="job-detail-layout">

    <!-- ---- LEFT: Job Details ---- -->
    <div>
        <div class="job-detail-card">
            <!-- Title & Badge -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1rem;">
                <div>
                    <h1 class="job-detail-title"><?= htmlspecialchars($job['title']) ?></h1>
                    <p style="color:var(--text-muted);">
                        🏢 <?= htmlspecialchars($job['company']) ?> &nbsp;·&nbsp;
                        📍 <?= htmlspecialchars($job['location']) ?>
                    </p>
                </div>
                <span class="job-badge badge-<?= $job['type'] ?>">
                    <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                </span>
            </div>

            <!-- Meta Info -->
            <div class="job-meta" style="margin-bottom:1rem;">
                <?php if ($job['salary']): ?>
                    <span>💰 <?= htmlspecialchars($job['salary']) ?></span>
                <?php endif; ?>
                <span>📅 Posted <?= date('M d, Y', strtotime($job['created_at'])) ?></span>
            </div>

            <!-- Description -->
            <div class="job-detail-section">
                <h3>Job Description</h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            </div>

            <!-- Requirements -->
            <?php if ($job['requirements']): ?>
            <div class="job-detail-section">
                <h3>Requirements</h3>
                <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- ---- APPLY FORM (only for logged-in seekers) ---- -->
        <?php if (isLoggedIn() && getRole() === 'seeker'): ?>
            <div class="apply-section">
                <h3>Apply for this Job</h3>

                <?php if ($alreadyApplied): ?>
                    <div class="flash flash-success" style="border-radius:8px;">
                        ✅ You have already applied for this job.
                    </div>
                <?php else: ?>
                    <!-- Apply form — submitted via AJAX (see js/main.js initApplyJob) -->
                    <form id="applyForm">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                        <div class="form-group">
                            <label class="form-label" for="cover_letter">Cover Letter <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            <textarea id="cover_letter" name="cover_letter"
                                      class="form-control"
                                      rows="5"
                                      placeholder="Tell the employer why you're a great fit..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            🚀 Submit Application
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        <?php elseif (!isLoggedIn()): ?>
            <div class="apply-section text-center">
                <p class="text-muted mb-2">You need to be logged in to apply for this job.</p>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg">Login to Apply</a>
                &nbsp;
                <a href="<?= BASE_URL ?>/signup.php?role=seeker" class="btn btn-outline btn-lg">Create Account</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ---- RIGHT: Sidebar ---- -->
    <div class="job-sidebar">

        <!-- Job Info Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">Job Overview</h4>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Job Type</span>
                <span class="sidebar-info-value"><?= $typeLabels[$job['type']] ?? $job['type'] ?></span>
            </div>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Location</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['location']) ?></span>
            </div>
            <?php if ($job['salary']): ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Salary</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['salary']) ?></span>
            </div>
            <?php endif; ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Posted</span>
                <span class="sidebar-info-value"><?= date('M d, Y', strtotime($job['created_at'])) ?></span>
            </div>
        </div>

        <!-- Company Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">About the Company</h4>
            <p style="font-weight:600;"><?= htmlspecialchars($job['company']) ?></p>
            <p class="text-muted text-sm mt-1">Posted by: <?= htmlspecialchars($job['employer_name']) ?></p>
        </div>

        <!-- Save Job Button -->
        <?php if (isLoggedIn() && getRole() === 'seeker'): ?>
        <div class="sidebar-card" style="text-align:center;">
            <?php if ($alreadySaved): ?>
                <button class="btn btn-success btn-block" disabled>✅ Job Saved</button>
            <?php else: ?>
                <button onclick="saveJob(<?= $job['id'] ?>, this)" class="btn btn-outline btn-block">
                    🔖 Save this Job
                </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-outline btn-block" style="text-align:center;">
            ← Back to Jobs
        </a>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
