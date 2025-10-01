<?php
/**
 * footer.php
 * Global footer template with navigation links and copyright.
 * Provides quick access to key pages and displays current year.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';
?>

<!-- Site Footer -->
<footer class="site-footer">
    <div class="container">
        <!-- Footer navigation links -->
        <div class="footer-links">
            <a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../about.php' : 'about.php'); ?>">
                <?php echo h('About Us'); ?>
            </a>
            <a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../faqs.php' : 'faqs.php'); ?>">
                <?php echo h('FAQs'); ?>
            </a>
            <a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../catalog.php' : 'catalog.php'); ?>">
                <?php echo h('Our Catalog'); ?>
            </a>
            <a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../register.php' : 'register.php'); ?>">
                <?php echo h('Become Our Member'); ?>
            </a>
            <a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../catalog.php' : 'catalog.php'); ?>">
                <?php echo h('Borrow a Book'); ?>
            </a>
        </div>
        
        <!-- Copyright notice -->
        <div class="footer-meta muted">
            <?php echo h('© ' . date('Y') . ' Library System. All rights reserved.'); ?>
        </div>
    </div>
</footer>

</body>
</html>


