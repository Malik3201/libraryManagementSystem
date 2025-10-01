<?php
/*
 * Purpose: Display user's borrowing history with return action for borrowed items
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

start_secure_session();
$user = current_user();
if (!$user) {
	flash('error', 'Please login to view history.');
	redirect('login.php');
}

csrf_token();

$pdo = db();

// Show only returned records in history
$isAdmin = isset($user['role']) && $user['role'] === 'admin';
if ($isAdmin) {
    $sql = 'SELECT br.record_id, br.user_id, br.book_id, br.borrow_date, br.return_date, br.status, b.title
        FROM borrow_records br JOIN books b ON b.book_id = br.book_id
        WHERE br.status = "returned"
        ORDER BY br.return_date DESC, br.record_id DESC';
    $stmt = $pdo->query($sql);
} else {
    $sql = 'SELECT br.record_id, br.user_id, br.book_id, br.borrow_date, br.return_date, br.status, b.title
        FROM borrow_records br JOIN books b ON b.book_id = br.book_id
        WHERE br.user_id = ? AND br.status = "returned"
        ORDER BY br.return_date DESC, br.record_id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$user['user_id']]);
}

$rows = $isAdmin ? $stmt->fetchAll() : $stmt->fetchAll();

$error = get_flash('error');
$success = get_flash('success');
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="dashboard-main section">
    <div class="container">
		<div class="history-header">
			<div>
                <h1 class="history-title"><?php echo h($isAdmin ? 'Returned Books' : 'My Returned Books'); ?></h1>
                <p class="history-subtitle"><?php echo h($isAdmin ? 'All returned records in the system' : 'All books you have returned'); ?></p>
			</div>
			<div class="history-actions">
				<a class="btn btn-outline" href="catalog.php">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path>
						<path d="M4 4v15"></path>
						<path d="M8 4v15"></path>
						<path d="M12 4v15"></path>
					</svg>
					<?php echo h('Browse Books'); ?>
				</a>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<?php if (empty($rows)): ?>
			<div class="empty-card">
				<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; color: var(--color-text-muted);">
					<path d="M3 3h18v4H3z"></path>
					<path d="M8 7v14"></path>
					<path d="M16 7v14"></path>
				</svg>
				<h3><?php echo h('No borrowing records'); ?></h3>
				<p><?php echo h($isAdmin ? 'No books have been borrowed yet.' : 'You haven\'t borrowed any books yet. Start exploring the catalog!'); ?></p>
			</div>
		<?php else: ?>
            <div class="table-responsive">
            <table class="admin-table striped hover center" style="width:100%">
				<thead>
					<tr>
                        <th><?php echo h('Book Title'); ?></th>
                        <th><?php echo h('Borrow Date'); ?></th>
                        <th><?php echo h('Return Date'); ?></th>
                        <th><?php echo h('Status'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($rows as $r): ?>
					<tr>
						<td>
							<div style="font-weight: 600;"><?php echo h($r['title']); ?></div>
						</td>
						<td><?php echo h(date('M j, Y', strtotime($r['borrow_date']))); ?></td>
						<td><?php echo h($r['return_date'] ? date('M j, Y', strtotime($r['return_date'])) : '—'); ?></td>
                        <td><span class="badge success"><?php echo h('Returned'); ?></span></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
            </table>
            </div>
		<?php endif; ?>
    </div>
    </main>
</div>



