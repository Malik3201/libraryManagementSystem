<?php
/*
 * Purpose: Global header with responsive navigation
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

start_secure_session();
$u = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../assets/css/style.css' : 'assets/css/style.css'); ?>">
<script defer src="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../assets/js/app.js' : 'assets/js/app.js'); ?>"></script>
<title><?php echo h('Library System'); ?></title>
</head>
<?php
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$page_class = 'page-' . $current_page;
?>
<body class="<?php echo h($page_class); ?>">
<header class="site-header">
	<div class="container nav">
		<div class="brand">
			<span class="brand-badge">LS</span>
			<span>Library System</span>
		</div>
		<nav>
			<button class="nav-toggle" aria-label="Toggle menu" data-nav-toggle>☰</button>
			<div class="nav-links" data-nav-links>
				<!-- Public navigation - always visible -->
				<a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'index.php')!==false?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php' : 'index.php'); ?>"><?php echo h('Home'); ?></a>
				<a class="<?php echo (isset($_GET['section']) && $_GET['section']==='faqs')?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php?section=faqs' : 'index.php?section=faqs'); ?>"><?php echo h('FAQs'); ?></a>
				<a class="<?php echo (isset($_GET['section']) && $_GET['section']==='about')?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php?section=about' : 'index.php?section=about'); ?>"><?php echo h('About'); ?></a>
				
                <!-- Authentication links -->
                <?php if ($u): ?>
                    <!-- Logged in user navigation -->
                    <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'student_dashboard.php')!==false || strpos($_SERVER['SCRIPT_NAME'], 'faculty_dashboard.php')!==false || strpos($_SERVER['SCRIPT_NAME'], 'admin/dashboard.php')!==false?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' . ($u['role'] === 'admin' ? 'admin/dashboard.php' : ($u['role'] === 'faculty' ? 'faculty_dashboard.php' : 'student_dashboard.php')) : ($u['role'] === 'admin' ? 'admin/dashboard.php' : ($u['role'] === 'faculty' ? 'faculty_dashboard.php' : 'student_dashboard.php'))); ?>"><?php echo h('Dashboard'); ?></a>
                <?php else: ?>
					<!-- Guest navigation -->
					<a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'login.php')!==false?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../login.php' : 'login.php'); ?>"><?php echo h('Login'); ?></a>
					<a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'register.php')!==false?'active':''; ?>" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../register.php' : 'register.php'); ?>"><?php echo h('Register'); ?></a>
				<?php endif; ?>
			</div>
		</nav>
	</div>
</header>


