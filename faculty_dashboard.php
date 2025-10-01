<?php
/*
 * Purpose: Faculty dashboard with quick links
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

start_secure_session();
$user = current_user();
if (!$user) { redirect('login.php'); }

$success = get_flash('success');
$error = get_flash('error');
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
	<?php include __DIR__ . '/includes/sidebar.php'; ?>
	
	<main class="dashboard-main">
	<div class="dashboard-hero">
		<div class="container">
			<h1><?php echo h('Welcome back, ' . ($user['name'] ?? 'Faculty')); ?></h1>
			<p><?php echo h('Access resources for your courses and research projects.'); ?></p>
		</div>
	</div>
	
	<div class="container">
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<div class="dashboard-actions">
			<div class="dashboard-card">
				<div class="dashboard-card-icon">
					<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
				</div>
				<h3><?php echo h('Browse Catalog'); ?></h3>
				<p><?php echo h('Explore our academic collection to find materials for your courses and research.'); ?></p>
				<a class="btn btn-accent" href="catalog.php">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path>
						<path d="M4 4v15"></path>
						<path d="M8 4v15"></path>
						<path d="M12 4v15"></path>
					</svg>
					<?php echo h('Explore Books'); ?>
				</a>
			</div>
			
			<div class="dashboard-card">
				<div class="dashboard-card-icon">
					<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M3 3h18v4H3z"></path>
						<path d="M8 7v14"></path>
						<path d="M16 7v14"></path>
					</svg>
				</div>
				<h3><?php echo h('My History'); ?></h3>
				<p><?php echo h('Manage your borrowed materials and track your research resources.'); ?></p>
				<a class="btn btn-outline" href="history.php">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M3 3h18v4H3z"></path>
						<path d="M8 7v14"></path>
						<path d="M16 7v14"></path>
					</svg>
					<?php echo h('View History'); ?>
				</a>
			</div>
		</div>
		</div>
	</main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


