<?php
/*
 * Purpose: Admin dashboard showing high-level stats
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

csrf_token();

$pdo = db();

$totalUsers = (int)$pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
$totalBooks = (int)$pdo->query('SELECT COUNT(*) AS c FROM books')->fetch()['c'];
$activeBorrowed = (int)$pdo->query("SELECT COUNT(*) AS c FROM borrow_records WHERE status='borrowed'")->fetch()['c'];
$returned = (int)$pdo->query("SELECT COUNT(*) AS c FROM borrow_records WHERE status='returned'")->fetch()['c'];

$error = get_flash('error');
$success = get_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Admin Dashboard'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 1000px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
.cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.card { border: 1px solid #ddd; border-radius: 6px; padding: 1rem; background: #fafafa; }
a { display: inline-block; margin-bottom: 1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Admin Dashboard'); ?></h1>
	<a href="../catalog.php"><?php echo h('Back to Catalog'); ?></a>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<div class="cards">
		<div class="card"><h3><?php echo h('Total Users'); ?></h3><p><?php echo h((string)$totalUsers); ?></p></div>
		<div class="card"><h3><?php echo h('Total Books'); ?></h3><p><?php echo h((string)$totalBooks); ?></p></div>
		<div class="card"><h3><?php echo h('Active Borrowed'); ?></h3><p><?php echo h((string)$activeBorrowed); ?></p></div>
		<div class="card"><h3><?php echo h('Returned'); ?></h3><p><?php echo h((string)$returned); ?></p></div>
	</div>

	<div style="margin-top:1rem;">
		<a href="manage_books.php"><?php echo h('Manage Books'); ?></a>
		|
		<a href="manage_users.php"><?php echo h('Manage Users'); ?></a>
		|
		<a href="borrow_logs.php"><?php echo h('Borrow Logs'); ?></a>
		|
		<a href="../logout.php"><?php echo h('Logout'); ?></a>
	</div>
</div>
</body>
</html>


