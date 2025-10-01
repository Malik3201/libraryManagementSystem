<?php
/*
 * Purpose: Admin borrow logs with filters and pagination
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

start_secure_session();

function require_admin(): void {
	$user = current_user();
	if (!$user || ($user['role'] ?? '') !== 'admin') {
		flash('error', 'Admin access required.');
		redirect('../login.php');
	}
}

require_admin();

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = db();

$status = isset($_GET['status']) ? (string)$_GET['status'] : 'all';
$validStatuses = ['all','borrowed','returned','overdue'];
if (!in_array($status, $validStatuses, true)) { $status = 'all'; }

$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($status !== 'all') {
	$where = 'WHERE br.status = ?';
	$params[] = $status;
}

// Count
$sqlCount = 'SELECT COUNT(*) AS cnt FROM borrow_records br JOIN users u ON u.user_id=br.user_id JOIN books b ON b.book_id=br.book_id ' . $where;
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = (int)$stmt->fetch()['cnt'];
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

// Fetch
$sql = 'SELECT br.record_id, u.name AS user_name, u.email, b.title AS book_title, br.borrow_date, br.return_date, br.status
	FROM borrow_records br
	JOIN users u ON u.user_id = br.user_id
	JOIN books b ON b.book_id = br.book_id
	' . $where . '
	ORDER BY br.borrow_date DESC, br.record_id DESC
	LIMIT ? OFFSET ?';
$stmt = $pdo->prepare($sql);
$idx = 1;
foreach ($params as $p) { $stmt->bindValue($idx++, $p, PDO::PARAM_STR); }
$stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$qs = $_GET; unset($qs['page']); $baseQuery = http_build_query($qs);

$error = get_flash('error');
$success = get_flash('success');
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
	<?php include __DIR__ . '/../includes/sidebar.php'; ?>
	
	<main class="dashboard-main section">
	<div class="container">
		<div class="admin-header">
			<div>
				<h1 class="admin-title"><?php echo h('Borrow Logs'); ?></h1>
				<p class="admin-subtitle"><?php echo h('Track all borrowing activity and manage returns'); ?></p>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<div class="admin-form-section">
			<h2><?php echo h('Filter Logs'); ?></h2>
			<form method="get" class="admin-form">
				<div class="form-row">
					<div>
						<label><?php echo h('Status Filter'); ?></label>
						<select name="status">
							<option value="all" <?php echo $status==='all'?'selected':''; ?>><?php echo h('All Records'); ?></option>
							<option value="borrowed" <?php echo $status==='borrowed'?'selected':''; ?>><?php echo h('Currently Borrowed'); ?></option>
							<option value="returned" <?php echo $status==='returned'?'selected':''; ?>><?php echo h('Returned'); ?></option>
							<option value="overdue" <?php echo $status==='overdue'?'selected':''; ?>><?php echo h('Overdue'); ?></option>
						</select>
					</div>
				</div>
				<div>
					<button class="btn btn-accent" type="submit">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
						<?php echo h('Apply Filter'); ?>
					</button>
				</div>
			</form>
		</div>

		<div class="admin-form-section">
			<h2><?php echo h('Borrowing Records'); ?></h2>
			<?php if (empty($rows)): ?>
				<div class="empty-card">
					<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; color: var(--color-text-muted);">
						<path d="M3 3h18v4H3z"></path>
						<path d="M8 7v14"></path>
						<path d="M16 7v14"></path>
					</svg>
					<h3><?php echo h('No borrowing records'); ?></h3>
					<p><?php echo h('No books have been borrowed yet, or no records match your filter.'); ?></p>
				</div>
			<?php else: ?>
				<table class="admin-table striped hover">
					<thead>
						<tr>
							<th><?php echo h('User'); ?></th>
							<th><?php echo h('Email'); ?></th>
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
								<div style="font-weight: 600;"><?php echo h($r['user_name']); ?></div>
							</td>
							<td><?php echo h($r['email']); ?></td>
							<td>
								<div style="font-weight: 500;"><?php echo h($r['book_title']); ?></div>
							</td>
							<td><?php echo h(date('M j, Y', strtotime($r['borrow_date']))); ?></td>
							<td><?php echo h($r['return_date'] ? date('M j, Y', strtotime($r['return_date'])) : '—'); ?></td>
							<td>
								<?php if ($r['status'] === 'borrowed'): ?>
									<span class="badge accent"><?php echo h('Currently Borrowed'); ?></span>
								<?php elseif ($r['status'] === 'returned'): ?>
									<span class="badge success"><?php echo h('Returned'); ?></span>
								<?php else: ?>
									<span class="badge warn"><?php echo h('Overdue'); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<?php if ($totalPages > 1): ?>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page-1)); ?>" class="btn btn-outline">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="15,18 9,12 15,6"></polyline>
						</svg>
						<?php echo h('Previous'); ?>
					</a>
				<?php endif; ?>
				
				<span class="pagination-info"><?php echo h("Page {$page} of {$totalPages}"); ?></span>
				
				<?php if ($page < $totalPages): ?>
					<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page+1)); ?>" class="btn btn-outline">
						<?php echo h('Next'); ?>
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="9,18 15,12 9,6"></polyline>
						</svg>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		</div>
	</main>
</div>


