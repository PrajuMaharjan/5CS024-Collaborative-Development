<?php
// ============================================================
// pages/jobs.php - Job Listings Page
// Features AJAX-powered live search (no page reload)
// ============================================================
require_once '../includes/auth.php';

$pageTitle = 'Browse Jobs';
$pageCss = 'jobs';
require_once '../includes/header.php';

// Get initial filter values from URL (for non-JS fallback / hero form)
$keyword  = htmlspecialchars($_GET['keyword']  ?? '');
$location = htmlspecialchars($_GET['location'] ?? '');
$type     = htmlspecialchars($_GET['type']     ?? '');
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Browse Jobs</h1>
        <p>Find your perfect opportunity — search and filter below</p>
    </div>
</div>

<div class="container section">

    <!-- SEARCH & FILTER BAR -->
    <!-- This form triggers AJAX search (see js/main.js initJobSearch) -->
    <form id="jobSearchForm" class="search-bar">

        <!-- Keyword Search -->
        <div class="search-input-wrap" style="flex:2;">
            <input type="text"
                   id="searchKeyword"
                   name="keyword"
                   placeholder="Job title, skill, or company..."
                   value="<?= $keyword ?>"
                   style="padding-left:1rem;">
        </div>

        <!-- Location Filter -->
        <div class="search-input-wrap">
            <input type="text"
                   id="searchLocation"
                   name="location"
                   placeholder="Location..."
                   value="<?= $location ?>"
                   style="padding-left:1rem;">
        </div>

        <!-- Job Type Filter -->
        <select id="searchType" name="type" class="form-control" style="max-width:160px;">
            <option value="">All Types</option>
            <option value="full-time"  <?= $type === 'full-time'  ? 'selected' : '' ?>>Full Time</option>
            <option value="part-time"  <?= $type === 'part-time'  ? 'selected' : '' ?>>Part Time</option>
            <option value="remote"     <?= $type === 'remote'     ? 'selected' : '' ?>>Remote</option>
            <option value="contract"   <?= $type === 'contract'   ? 'selected' : '' ?>>Contract</option>
            <option value="internship" <?= $type === 'internship' ? 'selected' : '' ?>>Internship</option>
        </select>

        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- LOADING SPINNER (shown while AJAX is running) -->
    <div id="searchLoading" class="hidden">
        <div class="spinner"></div>
        <p class="loading-text">Searching jobs...</p>
    </div>

    <!-- JOB RESULTS CONTAINER -->
    <!-- JavaScript (main.js) fills this div with rendered job cards -->
    <div id="jobResults" class="grid-3" style="transition: opacity 0.3s ease; min-height: 200px;">
        <!-- Jobs loaded here via AJAX -->
        <div class="spinner"></div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
