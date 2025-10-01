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
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
	<?php include __DIR__ . '/../includes/sidebar.php'; ?>
	
	<main class="dashboard-main section">
	<div class="container">
		<div class="admin-header">
			<div>
				<h1 class="admin-title"><?php echo h('Manage Users'); ?></h1>
				<p class="admin-subtitle"><?php echo h('View and manage user roles and permissions'); ?></p>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<?php if (empty($users)): ?>
			<div class="empty-card">
				<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; color: var(--color-text-muted);">
					<path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
					<path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
					<circle cx="12" cy="7" r="4"></circle>
				</svg>
				<h3><?php echo h('No users found'); ?></h3>
				<p><?php echo h('No users have been registered yet.'); ?></p>
			</div>
		<?php else: ?>
            <div class="table-responsive">
            <table class="admin-table striped hover" style="width:100%">
				<thead>
					<tr>
						<th><?php echo h('Name'); ?></th>
						<th><?php echo h('Email'); ?></th>
						<th><?php echo h('Role'); ?></th>
						<th><?php echo h('Joined'); ?></th>
						<th><?php echo h('Actions'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($users as $u): ?>
					<tr>
						<td>
							<div style="font-weight: 600;"><?php echo h($u['name']); ?></div>
							<?php if ((int)$u['user_id'] === (int)$me['user_id']): ?>
								<span style="color: var(--color-accent); font-size: 0.8rem;">(You)</span>
							<?php endif; ?>
						</td>
						<td><?php echo h($u['email']); ?></td>
						<td>
							<?php
							$roleClass = 'accent';
							if ($u['role'] === 'admin') $roleClass = 'warn';
							elseif ($u['role'] === 'faculty') $roleClass = 'success';
							?>
							<span class="badge <?php echo $roleClass; ?>"><?php echo h(ucfirst($u['role'])); ?></span>
						</td>
						<td><?php echo h(date('M j, Y', strtotime($u['created_at']))); ?></td>
						<td>
							<form method="post" action="" class="actions-form">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="user_id" value="<?php echo h((string)$u['user_id']); ?>">
								<select name="role" class="role-select" style="padding: 6px 8px; border-radius: 6px; border: 1px solid #e5e7eb; font-size: 0.9rem;">
									<option value="student" <?php echo $u['role']==='student'?'selected':''; ?>><?php echo h('Student'); ?></option>
									<option value="faculty" <?php echo $u['role']==='faculty'?'selected':''; ?>><?php echo h('Faculty'); ?></option>
									<option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>><?php echo h('Admin'); ?></option>
								</select>
							<button class="btn btn-edit-table" type="submit">
									<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M20 6L9 17l-5-5"></path>
									</svg>
									Update
								</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
		</div>
	</main>
</div>


