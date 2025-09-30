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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Borrow Logs'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 1100px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
form.filters { margin-bottom: 1rem; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: .5rem; text-align: left; }
.pagination { margin-top: 1rem; }
.pagination a, .pagination span { margin-right: .5rem; }
a { display:inline-block; margin-bottom:1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Borrow Logs'); ?></h1>
	<a href="dashboard.php"><?php echo h('Back to Dashboard'); ?></a>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<form method="get" class="filters">
		<label><?php echo h('Status'); ?>
			<select name="status">
				<option value="all" <?php echo $status==='all'?'selected':''; ?>><?php echo h('All'); ?></option>
				<option value="borrowed" <?php echo $status==='borrowed'?'selected':''; ?>><?php echo h('Borrowed'); ?></option>
				<option value="returned" <?php echo $status==='returned'?'selected':''; ?>><?php echo h('Returned'); ?></option>
				<option value="overdue" <?php echo $status==='overdue'?'selected':''; ?>><?php echo h('Overdue'); ?></option>
			</select>
		</label>
		<button type="submit"><?php echo h('Filter'); ?></button>
	</form>

	<table>
		<thead>
			<tr>
				<th><?php echo h('User'); ?></th>
				<th><?php echo h('Email'); ?></th>
				<th><?php echo h('Book'); ?></th>
				<th><?php echo h('Borrow date'); ?></th>
				<th><?php echo h('Return date'); ?></th>
				<th><?php echo h('Status'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($rows)): ?>
			<tr><td colspan="6"><?php echo h('No records found'); ?></td></tr>
		<?php else: ?>
			<?php foreach ($rows as $r): ?>
			<tr>
				<td><?php echo h($r['user_name']); ?></td>
				<td><?php echo h($r['email']); ?></td>
				<td><?php echo h($r['book_title']); ?></td>
				<td><?php echo h($r['borrow_date']); ?></td>
				<td><?php echo h($r['return_date'] ?? ''); ?></td>
				<td><?php echo h($r['status']); ?></td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<div class="pagination">
		<?php if ($page > 1): ?>
			<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page-1)); ?>"><?php echo h('Prev'); ?></a>
		<?php else: ?><span><?php echo h('Prev'); ?></span><?php endif; ?>
		<span><?php echo h("Page {$page} of {$totalPages}"); ?></span>
		<?php if ($page < $totalPages): ?>
			<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page+1)); ?>"><?php echo h('Next'); ?></a>
		<?php else: ?><span><?php echo h('Next'); ?></span><?php endif; ?>
	</div>

</div>
</body>
</html>


