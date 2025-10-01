<?php
/*
 * Purpose: Global footer
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';
?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-links">
			<a href="#"><?php echo h('Privacy'); ?></a>
			<a href="#"><?php echo h('Terms'); ?></a>
			<a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php?section=faqs' : 'index.php?section=faqs'); ?>"><?php echo h('FAQs'); ?></a>
		</div>
		<div class="footer-meta muted">
			<?php echo h('© ' . date('Y') . ' Library System. All rights reserved.'); ?>
		</div>
	</div>
</footer>

</body>
</html>


