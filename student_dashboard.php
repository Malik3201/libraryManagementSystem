<?php
/*
 * Purpose: Student dashboard with quick links
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
			<h1><?php echo h('Welcome back, ' . ($user['name'] ?? 'Student')); ?></h1>
			<p><?php echo h('Ready to discover your next great read?'); ?></p>
		</div>
	</div>
	
	<div class="container">
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<!-- Student Stats -->
		<div class="dashboard-stats">
			<div class="stat-card">
				<div class="stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
						<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
					</svg>
				</div>
				<div class="stat-content">
					<div class="stat-number">3</div>
					<div class="stat-label">Books Borrowed</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 12l2 2 4-4"></path>
						<circle cx="12" cy="12" r="10"></circle>
					</svg>
				</div>
				<div class="stat-content">
					<div class="stat-number">12</div>
					<div class="stat-label">Books Returned</div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M3 3v18h18"></path>
						<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
					</svg>
				</div>
				<div class="stat-content">
					<div class="stat-number">15</div>
					<div class="stat-label">Total History</div>
				</div>
			</div>
		</div>

		<!-- Enhanced Dashboard Content -->
		<div class="dashboard-content">
			<!-- Quick Actions -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Quick Actions'); ?></h2>
				<div class="quick-actions">
					<a href="catalog.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
						</div>
						<h3><?php echo h('Browse Catalog'); ?></h3>
						<p><?php echo h('Search through our extensive collection of books and discover your next favorite read.'); ?></p>
					</a>
					<a href="borrow.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
							</svg>
						</div>
						<h3><?php echo h('Borrow Book'); ?></h3>
						<p><?php echo h('Find a book you want to read and borrow it from our library collection.'); ?></p>
					</a>
					<a href="history.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M3 3v18h18"></path>
								<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
							</svg>
						</div>
						<h3><?php echo h('Borrow History'); ?></h3>
						<p><?php echo h('View all your past and current book borrowings and manage your loans.'); ?></p>
					</a>
				</div>
			</div>

			<!-- Reading Progress -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Reading Progress'); ?></h2>
				<div class="progress-grid">
					<div class="progress-card">
						<h3><?php echo h('Currently Reading'); ?></h3>
						<div class="book-item">
							<div class="book-cover">
								<div class="book-placeholder">📚</div>
							</div>
							<div class="book-info">
								<h4>Introduction to Algorithms</h4>
								<p>by Thomas H. Cormen</p>
								<div class="progress-bar">
									<div class="progress-fill" style="width: 65%"></div>
								</div>
								<span class="progress-text">65% Complete</span>
							</div>
						</div>
					</div>
					<div class="progress-card">
						<h3><?php echo h('Recently Borrowed'); ?></h3>
						<div class="recent-books">
							<div class="recent-book">
								<div class="book-cover small">
									<div class="book-placeholder">📖</div>
								</div>
								<div class="book-details">
									<h5>Data Structures</h5>
									<p>Due: 2024-01-15</p>
								</div>
							</div>
							<div class="recent-book">
								<div class="book-cover small">
									<div class="book-placeholder">🔬</div>
								</div>
								<div class="book-details">
									<h5>Machine Learning</h5>
									<p>Due: 2024-01-20</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Study Tips -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Study Tips'); ?></h2>
				<div class="tips-grid">
					<div class="tip-card">
						<div class="tip-icon">💡</div>
						<h4><?php echo h('Effective Reading'); ?></h4>
						<p><?php echo h('Take notes while reading to improve comprehension and retention.'); ?></p>
					</div>
					<div class="tip-card">
						<div class="tip-icon">⏰</div>
						<h4><?php echo h('Time Management'); ?></h4>
						<p><?php echo h('Set aside dedicated reading time each day for consistent progress.'); ?></p>
					</div>
					<div class="tip-card">
						<div class="tip-icon">📝</div>
						<h4><?php echo h('Note Taking'); ?></h4>
						<p><?php echo h('Use the library\'s study spaces for focused learning sessions.'); ?></p>
					</div>
				</div>
			</div>
		</div>
		</div>
	</main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


