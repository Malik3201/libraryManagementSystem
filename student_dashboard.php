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
// Load dynamic data
$pdo = db();
// Counts
$stmtTmp = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND status = "borrowed"');
$stmtTmp->execute([(int)$user['user_id']]);
$borrowedCount = (int)$stmtTmp->fetchColumn();

$stmtTmp = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND status = "returned"');
$stmtTmp->execute([(int)$user['user_id']]);
$returnedCount = (int)$stmtTmp->fetchColumn();

$stmtTmp = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ?');
$stmtTmp->execute([(int)$user['user_id']]);
$totalHistoryCount = (int)$stmtTmp->fetchColumn();

// Currently reading: latest borrowed (not yet returned)
$stmtTmp = $pdo->prepare('SELECT br.record_id, br.borrow_date, br.due_date, b.title, b.author, b.cover_url
    FROM borrow_records br JOIN books b ON b.book_id = br.book_id
    WHERE br.user_id = ? AND br.status = "borrowed"
    ORDER BY br.borrow_date DESC, br.record_id DESC LIMIT 1');
$stmtTmp->execute([(int)$user['user_id']]);
$currentReading = $stmtTmp->fetch();

// Recently borrowed (active) - last 5
$stmtTmp = $pdo->prepare('SELECT br.record_id, br.borrow_date, br.due_date, b.title, b.cover_url
    FROM borrow_records br JOIN books b ON b.book_id = br.book_id
    WHERE br.user_id = ? AND br.status = "borrowed"
    ORDER BY br.borrow_date DESC, br.record_id DESC LIMIT 3');
$stmtTmp->execute([(int)$user['user_id']]);
$recentBorrowed = $stmtTmp->fetchAll();
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
                    <div class="stat-number"><?php echo h((string)$borrowedCount); ?></div>
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
                    <div class="stat-number"><?php echo h((string)$returnedCount); ?></div>
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
                    <div class="stat-number"><?php echo h((string)$totalHistoryCount); ?></div>
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
                        <?php if ($currentReading): ?>
                        <div class="book-item">
                            <div class="book-cover">
                                <?php if (!empty($currentReading['cover_url'])): ?>
                                    <img src="<?php echo h($currentReading['cover_url']); ?>" alt="<?php echo h($currentReading['title']); ?>" style="width:60px;height:80px;object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                    <div class="book-placeholder">📚</div>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h4><?php echo h($currentReading['title']); ?></h4>
                                <?php if (!empty($currentReading['author'])): ?><p><?php echo h('by ' . $currentReading['author']); ?></p><?php endif; ?>
                                <span class="progress-text"><?php echo h('Borrowed: ' . date('M j, Y', strtotime($currentReading['borrow_date'])) . ($currentReading['due_date'] ? ' • Due: ' . date('M j, Y', strtotime($currentReading['due_date'])) : '')); ?></span>
                            </div>
                        </div>
                        <?php else: ?>
                            <p class="muted"><?php echo h('No active reading right now.'); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="progress-card">
                        <h3><?php echo h('Recently Borrowed'); ?></h3>
                        <div class="recent-books">
                            <?php if (empty($recentBorrowed)): ?>
                                <p class="muted"><?php echo h('No recent borrowings.'); ?></p>
                            <?php else: foreach ($recentBorrowed as $rb): ?>
                            <div class="recent-book">
                                <div class="book-cover small">
                                    <?php if (!empty($rb['cover_url'])): ?>
                                        <img src="<?php echo h($rb['cover_url']); ?>" alt="<?php echo h($rb['title']); ?>" style="width:40px;height:50px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <div class="book-placeholder">📖</div>
                                    <?php endif; ?>
                                </div>
                                <div class="book-details">
                                    <h5><?php echo h($rb['title']); ?></h5>
                                    <p><?php echo h('Borrowed: ' . date('M j, Y', strtotime($rb['borrow_date'])) . ($rb['due_date'] ? ' • Due: ' . date('M j, Y', strtotime($rb['due_date'])) : '')); ?></p>
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
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


