<?php
/*
 * Purpose: Admin user management (list users and update roles)
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
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST['csrf_token'] ?? '';
	if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
		flash('error', 'Invalid request.');
		redirect('manage_users.php');
	}
	$userId = (int)($_POST['user_id'] ?? 0);
	$newRole = (string)($_POST['role'] ?? 'student');
	if (!in_array($newRole, ['student','faculty','admin'], true)) {
		flash('error', 'Invalid role.');
		redirect('manage_users.php');
	}
	if ($userId === (int)$me['user_id'] && $newRole !== 'admin') {
		flash('error', 'You cannot demote yourself.');
		redirect('manage_users.php');
	}
	$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?');
	$stmt->execute([$newRole, $userId]);
	flash('success', 'Role updated.');
	redirect('manage_users.php');
}

$users = $pdo->query('SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$error = get_flash('error');
$success = get_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Manage Users'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 1100px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: .5rem; text-align: left; }
form.inline { display: inline; }
a { display:inline-block; margin-bottom:1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Manage Users'); ?></h1>
	<a href="dashboard.php"><?php echo h('Back to Dashboard'); ?></a>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<table>
		<thead>
			<tr>
				<th><?php echo h('Name'); ?></th>
				<th><?php echo h('Email'); ?></th>
				<th><?php echo h('Role'); ?></th>
				<th><?php echo h('Action'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($users as $u): ?>
			<tr>
				<td><?php echo h($u['name']); ?></td>
				<td><?php echo h($u['email']); ?></td>
				<td><?php echo h($u['role']); ?></td>
				<td>
					<form method="post" class="inline" action="">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="user_id" value="<?php echo h((string)$u['user_id']); ?>">
						<select name="role">
							<option value="student" <?php echo $u['role']==='student'?'selected':''; ?>><?php echo h('student'); ?></option>
							<option value="faculty" <?php echo $u['role']==='faculty'?'selected':''; ?>><?php echo h('faculty'); ?></option>
							<option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>><?php echo h('admin'); ?></option>
						</select>
						<button type="submit"><?php echo h('Update'); ?></button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

</div>
</body>
</html>


