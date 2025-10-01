<?php
/*
 * Purpose: User profile page for all roles with forms to update name, email, password
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

start_secure_session();
$user = current_user();
if (!$user) { redirect('login.php'); }

$pdo = db();

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST['csrf_token'] ?? '';
	if (!csrf_verify($token)) {
		flash('error', 'Invalid request.');
		redirect('profile.php');
	}

	$action = $_POST['action'] ?? '';
    try {
        if ($action === 'update_name') {
			$name = trim((string)($_POST['name'] ?? ''));
			if ($name === '' || mb_strlen($name) < 2) { throw new Exception('Please enter a valid name.'); }
			$stmt = $pdo->prepare('UPDATE users SET name = ? WHERE user_id = ?');
			$stmt->execute([$name, (int)$user['user_id']]);
			flash('success', 'Name updated successfully.');
			redirect('profile.php');
		}
        if ($action === 'update_email') {
			$email = trim((string)($_POST['email'] ?? ''));
			if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new Exception('Please enter a valid email.'); }
			// Ensure email unique
			$stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ?');
			$stmt->execute([$email, (int)$user['user_id']]);
			if ($stmt->fetch()) { throw new Exception('Email already in use.'); }
			$stmt = $pdo->prepare('UPDATE users SET email = ? WHERE user_id = ?');
			$stmt->execute([$email, (int)$user['user_id']]);
			flash('success', 'Email updated successfully.');
			redirect('profile.php');
		}
        if ($action === 'update_password') {
			$current = (string)($_POST['current_password'] ?? '');
			$new = (string)($_POST['new_password'] ?? '');
			$confirm = (string)($_POST['confirm_password'] ?? '');
			if ($new === '' || mb_strlen($new) < 8) { throw new Exception('New password must be at least 8 characters.'); }
			if ($new !== $confirm) { throw new Exception('New password and confirm do not match.'); }
			// Verify current password
			$stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
			$stmt->execute([(int)$user['user_id']]);
			$hash = (string)$stmt->fetchColumn();
			if (!password_verify($current, $hash)) { throw new Exception('Current password is incorrect.'); }
			$newHash = password_hash($new, PASSWORD_DEFAULT);
			$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
			$stmt->execute([$newHash, (int)$user['user_id']]);
			flash('success', 'Password updated successfully.');
			redirect('profile.php');
        }
        if ($action === 'update_all') {
            $name = trim((string)($_POST['name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $new = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if ($name === '' || mb_strlen($name) < 2) { throw new Exception('Please enter a valid name.'); }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new Exception('Please enter a valid email.'); }

            // check unique email excluding current user
            $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? AND user_id <> ?');
            $stmt->execute([$email, (int)$user['user_id']]);
            if ($stmt->fetch()) { throw new Exception('Email already in use.'); }

            // update name and email
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE user_id = ?');
            $stmt->execute([$name, $email, (int)$user['user_id']]);

            // optional password update
            if ($new !== '' || $confirm !== '') {
                if (mb_strlen($new) < 8) { throw new Exception('New password must be at least 8 characters.'); }
                if ($new !== $confirm) { throw new Exception('New password and confirm do not match.'); }
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
                $stmt->execute([$newHash, (int)$user['user_id']]);
            }

            flash('success', 'Profile updated successfully.');
            redirect('profile.php');
        }
	} catch (Exception $e) {
		flash('error', $e->getMessage());
		redirect('profile.php');
	}
}

$error = get_flash('error');
$success = get_flash('success');
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
	<?php include __DIR__ . '/includes/sidebar.php'; ?>

	<main class="dashboard-main section">
    <div class="container">
        <div class="admin-header">
            <div>
                <h1 class="admin-title"><?php echo h($user['name'] ?? 'My Profile'); ?></h1>
                <p class="admin-subtitle"><?php echo h('Manage your account details'); ?></p>
            </div>
        </div>

		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

        <div class="admin-form-section">
            <h2><?php echo h('Account Details'); ?></h2>
            <form method="post" action="" class="admin-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_all">
                <div class="form">
                    <div>
                        <label><?php echo h('Full Name'); ?></label>
                        <input type="text" name="name" value="<?php echo h($user['name'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label><?php echo h('Email Address'); ?></label>
                        <input type="email" name="email" value="<?php echo h($user['email'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label><?php echo h('Change Password'); ?> <span class="muted"><?php echo h('(optional)'); ?></span></label>
                        <input type="password" name="new_password" minlength="8" placeholder="Leave blank to keep current">
                    </div>
                    <div>
                        <label><?php echo h('Confirm New Password'); ?></label>
                        <input type="password" name="confirm_password" minlength="8" placeholder="Leave blank to keep current">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-edit" type="submit"><?php echo h('Save Changes'); ?></button>
                </div>
            </form>
        </div>
	</div>
	</main>
</div>




