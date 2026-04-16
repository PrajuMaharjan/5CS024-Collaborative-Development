<!-- ====== END OF MAIN CONTENT ====== -->
</main>

<!-- ====== FOOTER ====== -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/kaamkhoji.png" alt="KaamKhoji" style="height:70px;width:auto;display:block;margin:-20px -16px;">
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
        <?php if (!isLoggedIn() || getRole() === 'employer'): ?>
        <div class="footer-links">
            <h4>For Employers</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/pages/post-job.php">Post a Job</a></li>
                <li><a href="<?= BASE_URL ?>/pages/applicants.php">View Applicants</a></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> KaamKhoji. Made for Nepal.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?= BASE_URL ?>/js/utils.js"></script>
<script src="<?= BASE_URL ?>/js/nav.js"></script>
<script src="<?= BASE_URL ?>/js/flash.js"></script>
<script src="<?= BASE_URL ?>/js/form-validation.js"></script>
<script src="<?= BASE_URL ?>/js/jobs.js"></script>
<script src="<?= BASE_URL ?>/js/apply-job.js"></script>
<?php if (!empty($pageJs)): ?>
<script src="<?= BASE_URL ?>/js/<?= htmlspecialchars($pageJs) ?>.js"></script>
<?php endif; ?>

</body>
</html>
