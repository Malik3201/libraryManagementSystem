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
// Dynamic data (same as student dashboard)
$pdo = db();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND status = "borrowed"');
$stmt->execute([(int)$user['user_id']]);
$borrowedCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND status = "returned"');
$stmt->execute([(int)$user['user_id']]);
$returnedCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE user_id = ?');
$stmt->execute([(int)$user['user_id']]);
$totalHistoryCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT br.record_id, br.borrow_date, br.due_date, b.title, b.author, b.cover_url
    FROM borrow_records br JOIN books b ON b.book_id = br.book_id
    WHERE br.user_id = ? AND br.status = "borrowed"
    ORDER BY br.borrow_date DESC, br.record_id DESC LIMIT 1');
$stmt->execute([(int)$user['user_id']]);
$currentReading = $stmt->fetch();

$stmt = $pdo->prepare('SELECT br.record_id, br.borrow_date, br.due_date, b.title, b.cover_url
    FROM borrow_records br JOIN books b ON b.book_id = br.book_id
    WHERE br.user_id = ? AND br.status = "borrowed"
    ORDER BY br.borrow_date DESC, br.record_id DESC LIMIT 3');
$stmt->execute([(int)$user['user_id']]);
$recentBorrowed = $stmt->fetchAll();
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

        <!-- Stats -->
        <div class="dashboard-stats">
            <div class="stat-card"><div class="stat-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg></div><div class="stat-content"><div class="stat-number"><?php echo h((string)$borrowedCount); ?></div><div class="stat-label"><?php echo h('Books Borrowed'); ?></div></div></div>
            <div class="stat-card"><div class="stat-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg></div><div class="stat-content"><div class="stat-number"><?php echo h((string)$returnedCount); ?></div><div class="stat-label"><?php echo h('Books Returned'); ?></div></div></div>
            <div class="stat-card"><div class="stat-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg></div><div class="stat-content"><div class="stat-number"><?php echo h((string)$totalHistoryCount); ?></div><div class="stat-label"><?php echo h('Total History'); ?></div></div></div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-content">
            <div class="dashboard-section">
                <h2 class="section-title"><?php echo h('Quick Actions'); ?></h2>
                <div class="quick-actions">
                    <a href="catalog.php" class="quick-action-card"><div class="quick-action-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div><h3><?php echo h('Browse Catalog'); ?></h3><p><?php echo h('Explore our academic collection to find materials for your courses and research.'); ?></p></a>
                    <a href="return.php" class="quick-action-card"><div class="quick-action-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg></div><h3><?php echo h('Return Book'); ?></h3><p><?php echo h('Return a borrowed book.'); ?></p></a>
                    <a href="history.php" class="quick-action-card"><div class="quick-action-icon"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg></div><h3><?php echo h('Borrow History'); ?></h3><p><?php echo h('See your returned books.'); ?></p></a>
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
		</div>
	</div>
</main>
</div>


