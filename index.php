<?php
/*
 * Purpose: Public home page with role-based redirect when logged in
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

start_secure_session();
$user = current_user();
if ($user) {
	$role = (string)($user['role'] ?? 'student');
	if ($role === 'admin') { redirect('admin/dashboard.php'); }
	if ($role === 'faculty') { redirect('faculty_dashboard.php'); }
	redirect('student_dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Welcome to Library Management System'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 800px; margin: 0 auto; text-align: center; }
a { display: inline-block; margin: .5rem 1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Welcome to Library Management System'); ?></h1>
	<div>
		<a href="login.php"><?php echo h('Login'); ?></a>
		<a href="register.php"><?php echo h('Register'); ?></a>
	</div>
</div>
</body>
</html>


