<?php
/**
 * Common footer for DoodleSense AI
 */
?>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2><?php echo APP_NAME; ?></h2>
                    <p>Draw, Create, Recognize</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#features">Features</a></li>
                        <li><a href="index.php#gallery">Gallery</a></li>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact</h3>
                    <p>School of Computer Science Engineering and Information Systems</p>
                    <p>Web Development - UBCA204L</p>
                    <p>SLOT: F1 +TF1</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Common JavaScript -->
    <script src="js/script.js"></script>
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
