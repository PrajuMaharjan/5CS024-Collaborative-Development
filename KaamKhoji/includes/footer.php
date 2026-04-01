<!-- ====== END OF MAIN CONTENT ====== -->
</main>

<!-- ====== FOOTER ====== -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
                <img src="<?= BASE_URL ?>/assets/logo.svg" alt="KaamKhoji" class="logo-img logo-sm">
                <span>Kaam<span class="logo-accent">Khoji</span></span>
            </a>
            <p>Nepal's simple job portal. Find jobs, hire talent.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>/pages/jobs.php">Browse Jobs</a></li>
                <li><a href="<?= BASE_URL ?>/signup.php">Register</a></li>
                <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
            </ul>
        </div>
        <div class="footer-links">
            <h4>For Employers</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/pages/post-job.php">Post a Job</a></li>
                <li><a href="<?= BASE_URL ?>/pages/applicants.php">View Applicants</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> KaamKhoji. Made with ❤️ for Nepal.</p>
    </div>
</footer>

<!-- Main JavaScript File -->
<script src="<?= BASE_URL ?>/js/main.js"></script>

</body>
</html>
