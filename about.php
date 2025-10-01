<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

start_secure_session();

include __DIR__ . '/includes/header.php';
?>

<main class="section">
	<div class="container">
		<h1 class="section-heading"><?php echo h('About Our Library System'); ?></h1>
		<p class="section-sub muted"><?php echo h('A modern, responsive platform built to make library access fast, simple, and secure.'); ?></p>

		<!-- At a glance stats -->
		<div class="stats-row" style="margin-bottom: 28px;">
			<div class="card center"><div class="stat"><?php echo h('10k+'); ?></div><div class="stat-label"><?php echo h('Books Indexed'); ?></div></div>
			<div class="card center"><div class="stat"><?php echo h('5k+'); ?></div><div class="stat-label"><?php echo h('Active Members'); ?></div></div>
			<div class="card center"><div class="stat"><?php echo h('24/7'); ?></div><div class="stat-label"><?php echo h('Access'); ?></div></div>
			<div class="card center"><div class="stat"><?php echo h('99.9%'); ?></div><div class="stat-label"><?php echo h('Uptime'); ?></div></div>
		</div>

		<div class="grid cols-3" style="margin-bottom: 24px;">
			<div class="card">
				<h3><?php echo h('Mission'); ?></h3>
				<p class="muted"><?php echo h('Provide students, faculty, and administrators an easy way to search, borrow, and manage books from anywhere.'); ?></p>
			</div>
			<div class="card">
				<h3><?php echo h('What We Offer'); ?></h3>
				<p class="muted"><?php echo h('Role-based dashboards, real-time availability, smooth borrowing and returns, responsive UI, and secure sessions.'); ?></p>
			</div>
			<div class="card">
				<h3><?php echo h('Technology'); ?></h3>
				<p class="muted"><?php echo h('Built with PHP, MySQL, and a modern front-end. Security includes CSRF protection and hashed passwords.'); ?></p>
			</div>
		</div>

		<div class="split">
			<div class="card">
				<h3><?php echo h('For Students & Faculty'); ?></h3>
				<ul class="muted" style="margin-left:16px;">
					<li><?php echo h('Browse the catalog and search across title, author, and category.'); ?></li>
					<li><?php echo h('Borrow books with a chosen return date and track progress.'); ?></li>
					<li><?php echo h('Return books quickly from the dashboard.'); ?></li>
					<li><?php echo h('View borrow history for your records.'); ?></li>
				</ul>
			</div>
			<div class="card">
				<h3><?php echo h('For Administrators'); ?></h3>
				<ul class="muted" style="margin-left:16px;">
					<li><?php echo h('Manage books and users from a dedicated admin panel.'); ?></li>
					<li><?php echo h('Review borrowing logs and track activity.'); ?></li>
					<li><?php echo h('Role-based navigation and permissions.'); ?></li>
				</ul>
			</div>
		</div>

		<!-- How it works timeline -->
		<div class="dashboard-section" style="margin-top:28px;">
			<h2 class="section-title"><?php echo h('How It Works'); ?></h2>
			<div class="timeline">
				<div class="step">
					<div class="num">1</div>
					<div>
						<h4><?php echo h('Create your account'); ?></h4>
						<p class="muted"><?php echo h('Register as a Student or Faculty, then sign in to your dashboard.'); ?></p>
					</div>
				</div>
				<div class="step">
					<div class="num">2</div>
					<div>
						<h4><?php echo h('Browse the catalog'); ?></h4>
						<p class="muted"><?php echo h('Search by title, author, or category and open a book card to borrow.'); ?></p>
					</div>
				</div>
				<div class="step">
					<div class="num">3</div>
					<div>
						<h4><?php echo h('Borrow & track'); ?></h4>
						<p class="muted"><?php echo h('Pick a return date, borrow the book, and track it under Return Book.'); ?></p>
					</div>
				</div>
				<div class="step">
					<div class="num">4</div>
					<div>
						<h4><?php echo h('Return & review'); ?></h4>
						<p class="muted"><?php echo h('Return with one click when finished. Review past activity in Borrow History.'); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Values -->
		<div class="grid cols-3" style="margin-top:28px;">
			<div class="card">
				<h3><?php echo h('Accessibility'); ?></h3>
				<p class="muted"><?php echo h('Mobile-first design and inclusive UI ensure everyone can access resources.'); ?></p>
			</div>
			<div class="card">
				<h3><?php echo h('Performance'); ?></h3>
				<p class="muted"><?php echo h('Optimized queries and caching for fast browsing and reliable operations.'); ?></p>
			</div>
			<div class="card">
				<h3><?php echo h('Security'); ?></h3>
				<p class="muted"><?php echo h('Secure sessions, CSRF protection, hashed passwords, and role-based permissions.'); ?></p>
			</div>
		</div>

		<!-- Call to action -->
		<div class="cta-band" style="margin-top:28px;">
			<h3 style="margin:0 0 6px 0;"><?php echo h('Start exploring the catalog'); ?></h3>
			<p class="muted" style="margin:0 0 12px 0;"><?php echo h('Discover new titles and manage your reading journey.'); ?></p>
			<a class="btn btn-outline" href="catalog.php"><?php echo h('Browse Catalog'); ?></a>
		</div>
	</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


