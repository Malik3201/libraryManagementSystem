<?php
/**
 * login.php
 * User login page with secure authentication and CSRF protection.
 * Handles user login form display and authentication processing.
 */

declare(strict_types=1);

// Include required dependencies
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Initialize secure session
start_secure_session();


// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for security
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_verify($token)) {
        flash('error', 'Invalid request. Please try again.');
        redirect('login.php');
    }

    // Get and sanitize form input
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // Validate input data
    $valid = true;
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valid = false;
    }
    if ($password === '') {
        $valid = false;
    }

    // Attempt to authenticate user
    if ($valid && login($email, $password)) {
        flash('success', 'Welcome back');
        
        // Get user data and redirect to appropriate dashboard
        $u = current_user();
        if ($u) {
            $role = (string)($u['role'] ?? 'student');
            if ($role === 'admin') { 
                redirect('admin/dashboard.php'); 
            }
            if ($role === 'faculty') { 
                redirect('faculty_dashboard.php'); 
            }
            redirect('student_dashboard.php');
        }
        // Fallback if user not found after login
        redirect('index.php');
    } else {
        flash('error', 'Invalid credentials');
        redirect('login.php');
    }
}

// Get flash messages and prepare form data
$error = get_flash('error');
$success = get_flash('success');
$email_prefill = isset($_POST['email']) ? (string)$_POST['email'] : '';

// Include header template
include __DIR__ . '/includes/header.php';
?>

<!-- Login Page Layout -->
<main class="auth-layout">
    <!-- Left Side - Library Image -->
    <div class="auth-image">
        <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66" alt="Library interior">
    </div>
    
    <!-- Right Side - Login Form -->
    <div class="auth-content">
        <div class="auth-card animate-right">
            <!-- Page Header -->
            <div class="auth-header">
                <h1><?php echo h('Welcome Back'); ?></h1>
                <p><?php echo h('Sign in to access your library account'); ?></p>
            </div>
            
            <!-- Flash Messages -->
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo h($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo h($success); ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post" action="" class="form">
                <?php echo csrf_field(); ?>
                
                <!-- Email Field -->
                <div class="col-12">
                    <label for="email"><?php echo h('Email Address'); ?></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo h($email_prefill); ?>" 
                           placeholder="Enter your email">
                </div>
                
                <!-- Password Field -->
                <div class="col-12">
                    <label for="password"><?php echo h('Password'); ?></label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <!-- Submit Button -->
                <div class="col-12">
                    <button class="btn btn-accent" type="submit">
                        <?php echo h('Sign In'); ?>
                    </button>
                </div>
            </form>

            <!-- Registration Link -->
            <div class="auth-footer">
                <?php echo h('Don\'t have an account?'); ?> 
                <a href="register.php"><?php echo h('Create one'); ?></a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


