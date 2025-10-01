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
include __DIR__ . '/includes/header.php';
?>

<main class="auth-layout">
	<div class="auth-image">
		<img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66">
	</div>
	<div class="auth-content">
		<div class="auth-card animate-left">
			<div class="auth-header">
				<h1><?php echo h('Join Our Library'); ?></h1>
				<p><?php echo h('Create your account to start borrowing books'); ?></p>
			</div>
			
			<?php if ($error): ?><div class="alert alert-error" style="white-space: pre-line;"><?php echo h($error); ?></div><?php endif; ?>
			<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

			<form method="post" action="" class="form">
				<?php echo csrf_field(); ?>
				<div class="col-12">
					<label for="name"><?php echo h('Full Name'); ?></label>
					<input type="text" id="name" name="name" required value="<?php echo h($prefill['name']); ?>" placeholder="Enter your full name">
				</div>
				<div class="col-12">
					<label for="email"><?php echo h('Email Address'); ?></label>
					<input type="email" id="email" name="email" required value="<?php echo h($prefill['email']); ?>" placeholder="Enter your email">
				</div>
				<div class="col-12">
					<label for="password"><?php echo h('Password'); ?></label>
					<input type="password" id="password" name="password" required minlength="8" placeholder="Create a password (min 8 characters)">
				</div>
			<div class="col-12">
				<label for="role"><?php echo h('I am a'); ?></label>
				<select id="role" name="role" required>
					<option value="student" <?php echo $prefill['role']==='student'?'selected':''; ?>><?php echo h('Student'); ?></option>
					<option value="faculty" <?php echo $prefill['role']==='faculty'?'selected':''; ?>><?php echo h('Faculty Member'); ?></option>
				</select>
			</div>
			<div class="col-12">
				<button class="btn btn-accent" type="submit"><?php echo h('Create Account'); ?></button>
			</div>
			</form>

			<div class="auth-footer">
				<?php echo h('Already have an account?'); ?> <a href="login.php"><?php echo h('Sign in'); ?></a>
			</div>
		</div>
	</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


