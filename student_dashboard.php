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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Student Dashboard'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 800px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
a { display: inline-block; margin-right: 1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Hello, ' . ($user['name'] ?? 'Student')); ?></h1>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>
	<div>
		<a href="catalog.php"><?php echo h('View Catalog'); ?></a>
		<a href="history.php"><?php echo h('My Borrow History'); ?></a>
		<a href="logout.php"><?php echo h('Logout'); ?></a>
	</div>
</div>
</body>
</html>


