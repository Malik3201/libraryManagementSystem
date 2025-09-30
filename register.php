<?php
/*
 * Purpose: User registration page with validation and CSRF token
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST['csrf_token'] ?? '';
	if (!csrf_verify($token)) {
		flash('error', 'Invalid request. Please try again.');
		redirect('register.php');
	}

	$name = trim((string)($_POST['name'] ?? ''));
	$email = trim((string)($_POST['email'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	$role = (string)($_POST['role'] ?? 'student');

	$errors = [];
	if ($name === '') { $errors[] = 'Name is required'; }
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required'; }
	if (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters'; }
	if (!in_array($role, ['student', 'faculty'], true)) { $role = 'student'; }

	if (empty($errors)) {
		$ok = register_user($name, $email, $password, $role);
		if ($ok) {
			flash('success', 'Account created');
			redirect('login.php');
		} else {
			flash('error', 'Registration failed. Email may already be in use.');
			redirect('register.php');
		}
	} else {
		flash('error', implode('\n', $errors));
		redirect('register.php');
	}
}

$error = get_flash('error');
$success = get_flash('success');

$prefill = [
	'name' => isset($_POST['name']) ? (string)$_POST['name'] : '',
	'email' => isset($_POST['email']) ? (string)$_POST['email'] : '',
	'role' => isset($_POST['role']) && in_array((string)$_POST['role'], ['student','faculty'], true) ? (string)$_POST['role'] : 'student',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Register'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 480px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; white-space: pre-line; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
label { display: block; margin-top: .75rem; }
input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: .5rem; }
button { margin-top: 1rem; padding: .5rem .75rem; }
a { display: inline-block; margin-top: .75rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Create Account'); ?></h1>
	<?php if ($error): ?>
		<div class="flash error"><?php echo h($error); ?></div>
	<?php endif; ?>
	<?php if ($success): ?>
		<div class="flash success"><?php echo h($success); ?></div>
	<?php endif; ?>

	<form method="post" action="">
		<?php echo csrf_field(); ?>
		<label for="name"><?php echo h('Full Name'); ?></label>
		<input type="text" id="name" name="name" required value="<?php echo h($prefill['name']); ?>">

		<label for="email"><?php echo h('Email'); ?></label>
		<input type="email" id="email" name="email" required value="<?php echo h($prefill['email']); ?>">

		<label for="password"><?php echo h('Password'); ?></label>
		<input type="password" id="password" name="password" required minlength="8">

		<label for="role"><?php echo h('Role'); ?></label>
		<select id="role" name="role" required>
			<option value="student" <?php echo $prefill['role']==='student'?'selected':''; ?>><?php echo h('Student'); ?></option>
			<option value="faculty" <?php echo $prefill['role']==='faculty'?'selected':''; ?>><?php echo h('Faculty'); ?></option>
		</select>

		<button type="submit"><?php echo h('Register'); ?></button>
	</form>

	<a href="login.php"><?php echo h('Have an account? Login'); ?></a>
</div>
</body>
</html>


