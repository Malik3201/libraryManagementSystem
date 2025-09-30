<?php
/*
 * Purpose: User login page with CSRF protection and server-side validation
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
		redirect('login.php');
	}

	$email = trim((string)($_POST['email'] ?? ''));
	$password = (string)($_POST['password'] ?? '');

	$valid = true;
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$valid = false;
	}
	if ($password === '') {
		$valid = false;
	}

	if ($valid && login($email, $password)) {
		flash('success', 'Welcome back');
		redirect('index.php');
	} else {
		flash('error', 'Invalid credentials');
		redirect('login.php');
	}
}

$error = get_flash('error');
$success = get_flash('success');
$email_prefill = isset($_POST['email']) ? (string)$_POST['email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Login'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 400px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
label { display: block; margin-top: .75rem; }
input[type="email"], input[type="password"] { width: 100%; padding: .5rem; }
button { margin-top: 1rem; padding: .5rem .75rem; }
a { display: inline-block; margin-top: .75rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Login'); ?></h1>
	<?php if ($error): ?>
		<div class="flash error"><?php echo h($error); ?></div>
	<?php endif; ?>
	<?php if ($success): ?>
		<div class="flash success"><?php echo h($success); ?></div>
	<?php endif; ?>

	<form method="post" action="">
		<?php echo csrf_field(); ?>
		<label for="email"><?php echo h('Email'); ?></label>
		<input type="email" id="email" name="email" required value="<?php echo h($email_prefill); ?>">

		<label for="password"><?php echo h('Password'); ?></label>
		<input type="password" id="password" name="password" required>

		<button type="submit"><?php echo h('Login'); ?></button>
	</form>

	<a href="register.php"><?php echo h('Create an account'); ?></a>
</div>
</body>
</html>


