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
include __DIR__ . '/includes/header.php';
?>

<main class="auth-layout">
	<div class="auth-image">
		<img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66">
	</div>
	<div class="auth-content">
		<div class="auth-card animate-right">
			<div class="auth-header">
				<h1><?php echo h('Welcome Back'); ?></h1>
				<p><?php echo h('Sign in to access your library account'); ?></p>
			</div>
			
			<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
			<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

			<form method="post" action="" class="form">
				<?php echo csrf_field(); ?>
				<div class="col-12">
					<label for="email"><?php echo h('Email Address'); ?></label>
					<input type="email" id="email" name="email" required value="<?php echo h($email_prefill); ?>" placeholder="Enter your email">
				</div>
			<div class="col-12">
				<label for="password"><?php echo h('Password'); ?></label>
				<input type="password" id="password" name="password" required placeholder="Enter your password">
			</div>
			<div class="col-12">
				<button class="btn btn-accent" type="submit"><?php echo h('Sign In'); ?></button>
			</div>
			</form>

			<div class="auth-footer">
				<?php echo h('Don\'t have an account?'); ?> <a href="register.php"><?php echo h('Create one'); ?></a>
			</div>
		</div>
	</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


