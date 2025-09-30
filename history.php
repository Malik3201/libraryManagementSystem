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

$isAdmin = isset($user['role']) && $user['role'] === 'admin';
if ($isAdmin) {
	$sql = 'SELECT br.record_id, br.user_id, br.book_id, br.borrow_date, br.return_date, br.status, b.title
		FROM borrow_records br JOIN books b ON b.book_id = br.book_id
		ORDER BY br.borrow_date DESC, br.record_id DESC';
	$stmt = $pdo->query($sql);
} else {
	$sql = 'SELECT br.record_id, br.user_id, br.book_id, br.borrow_date, br.return_date, br.status, b.title
		FROM borrow_records br JOIN books b ON b.book_id = br.book_id
		WHERE br.user_id = ?
		ORDER BY br.borrow_date DESC, br.record_id DESC';
	$stmt = $pdo->prepare($sql);
	$stmt->execute([(int)$user['user_id']]);
}

$rows = $isAdmin ? $stmt->fetchAll() : $stmt->fetchAll();

$error = get_flash('error');
$success = get_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Borrowing History'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 960px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: .5rem; text-align: left; }
button { padding: .35rem .6rem; }
a { display: inline-block; margin-bottom: 1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Borrowing History'); ?></h1>
	<a href="catalog.php"><?php echo h('Back to Catalog'); ?></a>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<table>
		<thead>
			<tr>
				<th><?php echo h('Title'); ?></th>
				<th><?php echo h('Borrow date'); ?></th>
				<th><?php echo h('Return date'); ?></th>
				<th><?php echo h('Status'); ?></th>
				<th><?php echo h('Action'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($rows)): ?>
			<tr><td colspan="5"><?php echo h('No records found'); ?></td></tr>
		<?php else: ?>
			<?php foreach ($rows as $r): ?>
			<tr>
				<td><?php echo h($r['title']); ?></td>
				<td><?php echo h($r['borrow_date']); ?></td>
				<td><?php echo h($r['return_date'] ?? '—'); ?></td>
				<td><?php echo h($r['status']); ?></td>
				<td>
					<?php if ($r['status'] === 'borrowed'): ?>
						<form method="post" action="return.php" style="display:inline">
							<?php echo csrf_field(); ?>
							<input type="hidden" name="record_id" value="<?php echo h((string)$r['record_id']); ?>">
							<button type="submit"><?php echo h('Return'); ?></button>
						</form>
					<?php else: ?>
						<span><?php echo h('—'); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

</div>
</body>
</html>


