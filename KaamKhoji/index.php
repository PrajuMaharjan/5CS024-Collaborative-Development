<?php
// index.php - Landing Page
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

require_once 'includes/db.php';
$pdo = getPDO();

$totalJobs  = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status='active'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='seeker'")->fetchColumn();
$totalEmps  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetchColumn();

$stmt = $pdo->query("
    SELECT jobs.*, users.name AS employer_name
    FROM jobs
    JOIN users ON jobs.employer_id = users.id
    WHERE jobs.status = 'active'
    ORDER BY jobs.created_at DESC
    LIMIT 6
");
$latestJobs = $stmt->fetchAll();

$pageTitle = 'Home';
$pageCss = 'landing';
require_once 'includes/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="hero">
    <div class="hero-content">

        <div class="hero-badge">
            <span class="badge-dots">
                <span></span><span></span><span></span><span></span>
            </span>
            Nepal's #1 Free Job Portal
        </div>

        <h1>
            Find your dream
            <span class="gradient-text-gold">job in Nepal.</span>
        </h1>

        <p>Affordable, fast, human-powered job search.<br>
            Real employers, no AI filters, always on time.</p>

        <form class="hero-search" action="<?= BASE_URL ?>/pages/jobs.php" method="GET">
            <input type="text" name="keyword" placeholder="Job title, skills, or company...">
            <button type="submit">Search Jobs</button>
        </form>

        <div class="hero-ctas">
            <a href="<?= BASE_URL ?>/signup.php?role=seeker" class="btn btn-primary btn-lg">Find a Job</a>
            <a href="<?= BASE_URL ?>/signup.php?role=employer" class="btn btn-outline btn-lg">Post a Job</a>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <strong><?= number_format($totalJobs) ?>+</strong>
                <span>Active Jobs</span>
            </div>
            <div class="hero-stat">
                <strong><?= number_format($totalUsers) ?>+</strong>
                <span>Job Seekers</span>
            </div>
            <div class="hero-stat">
                <strong><?= number_format($totalEmps) ?>+</strong>
                <span>Employers</span>
            </div>
            <div class="hero-stat">
                <strong>100%</strong>
                <span>Free to Use</span>
            </div>
        </div>

    </div>
</section>

<hr class="glow-divider">

<!-- ============================================================
     TESTIMONIALS — "Hear it directly from our users"
     ============================================================ -->
<section class="section section-dark">
    <div class="container">
        <p class="section-label">Success Stories</p>
        <h2 class="section-title">
            Hear it directly from
            <span class="gradient-text-gold">our users.</span>
        </h2>
        <p class="section-subtitle">
            Real people, real results. Here's what our community has to say.
        </p>

        <div class="grid-3">
            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "I wouldn't have landed my IT job at Leapfrog if it weren't for KaamKhoji.
                    The application process was seamless and the employers responded quickly."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">R</div>
                    <div>
                        <div class="author-name">Rajan Shrestha</div>
                        <div class="author-title">Software Developer, Lalitpur</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "We've hired 3 amazing team members through KaamKhoji in the last 6 months.
                    The quality of candidates is excellent and it's completely free."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">S</div>
                    <div>
                        <div class="author-name">Sunita Karki</div>
                        <div class="author-title">HR Manager, Kathmandu</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "I struggled with my job search for months until I found KaamKhoji.
                    Within two weeks I had three interviews and got my first full-time role."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">A</div>
                    <div>
                        <div class="author-name">Anisha Tamang</div>
                        <div class="author-title">Marketing Executive, Pokhara</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="glow-divider">

<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section class="section section-alt">
    <div class="container">
        <p class="section-label">How It Works</p>
        <h2 class="section-title">
            How can we help
            <span class="gradient-text">you?</span>
        </h2>
        <p class="section-subtitle">From browsing jobs to landing your offer — we've got you covered at every step.</p>

        <div class="grid-3">
            <div class="step-card">
                <div class="step-num">01</div>
                <h3>Create Your Profile</h3>
                <p>Sign up as a Job Seeker or Employer in under a minute. No complicated forms.</p>
            </div>
            <div class="step-card">
                <div class="step-num">02</div>
                <h3>Search or Post Jobs</h3>
                <p>Seekers browse thousands of active listings. Employers post openings instantly.</p>
            </div>
            <div class="step-card">
                <div class="step-num">03</div>
                <h3>Connect & Get Hired</h3>
                <p>Apply with one click, track your applications, and connect directly with employers.</p>
            </div>
        </div>
    </div>
</section>

<hr class="glow-divider">

<!-- ============================================================
     LATEST JOBS
     ============================================================ -->
<?php if (!empty($latestJobs)): ?>
    <section class="section section-dark">
        <div class="container">
            <p class="section-label">Latest Jobs</p>
            <h2 class="section-title">Fresh opportunities,<br><span class="gradient-text">posted just now.</span></h2>
            <p class="section-subtitle">New jobs added daily from top companies across Nepal.</p>

            <div class="grid-3">
                <?php
                $typeLabels = [
                    'full-time'  => 'Full Time',
                    'part-time'  => 'Part Time',
                    'remote'     => 'Remote',
                    'contract'   => 'Contract',
                    'internship' => 'Internship',
                ];
                foreach ($latestJobs as $job): ?>
                    <div class="card job-card">
                        <div class="job-card-header">
                            <div>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="job-title">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                                <div class="job-company"><?= htmlspecialchars($job['company']) ?></div>
                            </div>
                            <span class="job-badge badge-<?= $job['type'] ?>">
                                <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                            </span>
                        </div>
                        <div class="job-meta">
                            <span>Location <?= htmlspecialchars($job['location']) ?></span>
                            <?php if ($job['salary']): ?>
                                <span>Salary <?= htmlspecialchars($job['salary']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="job-actions">
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-3">
                <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-outline btn-lg">Browse All Jobs →</a>
            </div>
        </div>
    </section>

    <hr class="glow-divider">
<?php endif; ?>

<!-- ============================================================
     WHY KAAMKHOJI — COMPARISON
     ============================================================ -->
<section class="section section-dark">
    <div class="container">
        <p class="section-label">Comparison</p>
        <h2 class="section-title">
            But, why would you
            <span class="gradient-text-gold">work with us?</span>
        </h2>
        <p class="section-subtitle">We're not like the others. See the difference for yourself.</p>

        <div class="comparison-wrap">
            <!-- Other portals -->
            <div class="comparison-col">
                <div class="comparison-col-header">
                    <span class="col-label">Other Job Portals</span>
                </div>
                <div class="comparison-list">
                    <div class="comparison-item ci-no">
                        <div class="ci-icon">✕</div>
                        Paid listings with no guarantees
                    </div>
                    <div class="comparison-item ci-no">
                        <div class="ci-icon">✕</div>
                        Hidden fees to view contact details
                    </div>
                    <div class="comparison-item ci-no">
                        <div class="ci-icon">✕</div>
                        Outdated job listings never removed
                    </div>
                    <div class="comparison-item ci-no">
                        <div class="ci-icon">✕</div>
                        No direct employer communication
                    </div>
                    <div class="comparison-item ci-no">
                        <div class="ci-icon">✕</div>
                        Cluttered interface, hard to use
                    </div>
                </div>
            </div>

            <!-- KaamKhoji -->
            <div class="comparison-col is-us">
                <div class="comparison-col-header">
                    <span class="col-label">KaamKhoji</span>
                </div>
                <div class="comparison-list">
                    <div class="comparison-item ci-yes">
                        <div class="ci-icon">✓</div>
                        100% free for seekers and employers
                    </div>
                    <div class="comparison-item ci-yes">
                        <div class="ci-icon">✓</div>
                        Direct access to employer contact
                    </div>
                    <div class="comparison-item ci-yes">
                        <div class="ci-icon">✓</div>
                        Active jobs only — auto-expired listings
                    </div>
                    <div class="comparison-item ci-yes">
                        <div class="ci-icon">✓</div>
                        Real-time application tracking
                    </div>
                    <div class="comparison-item ci-yes">
                        <div class="ci-icon">✓</div>
                        Clean, simple, mobile-friendly UI
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="glow-divider">

<!-- ============================================================
     RAVING REVIEWS
     ============================================================ -->
<section class="section section-alt">
    <div class="container">
        <p class="section-label">Testimonials</p>
        <h2 class="section-title">
            There's a reason people are
            <span class="gradient-text-gold">raving about us.</span>
        </h2>
        <p class="section-subtitle">Thousands of Nepali professionals trust KaamKhoji for their career growth.</p>

        <div class="grid-3">
            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "KaamKhoji helped me transition from a BPO job to a software company.
                    The saved jobs feature is brilliant — I tracked everything in one place."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">P</div>
                    <div>
                        <div class="author-name">Prashant Adhikari</div>
                        <div class="author-title">QA Engineer, Kathmandu</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "As a small startup, we couldn't afford expensive HR platforms.
                    KaamKhoji gave us the same results for free. We hired 5 people in a month."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">M</div>
                    <div>
                        <div class="author-name">Maya Gurung</div>
                        <div class="author-title">Founder, Pokhara Startup</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "Fresh graduate, no connections — KaamKhoji connected me with an employer
                    who actually gave me a chance. Got my dream internship within 10 days."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">N</div>
                    <div>
                        <div class="author-name">Nirajan Bhandari</div>
                        <div class="author-title">Design Intern, Biratnagar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="glow-divider">

<!-- ============================================================
     CTA
     ============================================================ -->
<section class="cta-section">
    <div class="container">
        <p class="section-label">Get Started</p>
        <h2>Ready to find your next<br><span class="gradient-text">opportunity?</span></h2>
        <p>Join thousands of job seekers and employers building Nepal's future together.</p>
        <div class="cta-btns">
            <a href="<?= BASE_URL ?>/signup.php?role=seeker" class="btn btn-primary btn-xl">Find a Job</a>
            <a href="<?= BASE_URL ?>/signup.php?role=employer" class="btn btn-ghost btn-xl">Post a Job</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>