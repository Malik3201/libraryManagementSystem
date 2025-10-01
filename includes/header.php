<?php
/**
 * header.php
 * Global header template with responsive navigation and toast notifications.
 * Handles authentication state, active page detection, and flash message display.
 */

declare(strict_types=1);

// Include required dependencies
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Initialize session and get current user
start_secure_session();
$u = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../assets/css/style.css' : 'assets/css/style.css'); ?>">
    <script defer src="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../assets/js/app.js' : 'assets/js/app.js'); ?>"></script>
    <title><?php echo h('Library System'); ?></title>
</head>
<?php
// Determine current page for CSS class and active navigation
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$page_class = 'page-' . $current_page;

// Collect flash messages to display as toast notifications
$__flashPayload = [];
if ($msg = get_flash('success')) { 
    $__flashPayload[] = ['type'=>'success','message'=>$msg]; 
}
if ($msg = get_flash('error')) { 
    $__flashPayload[] = ['type'=>'error','message'=>$msg]; 
}
?>
<body class="<?php echo h($page_class); ?>">
    <!-- Pass flash messages to JavaScript for toast display -->
    <script>window.__FLASH__ = <?php echo json_encode($__flashPayload); ?>;</script>
    
    <!-- Toast notification container -->
    <div class="toast-container" id="toast-container"></div>
    
    <!-- Main Site Header -->
    <header class="site-header">
        <div class="container nav">
            <!-- Brand/Logo -->
		<div class="brand">
			<a href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php' : 'index.php'); ?>" aria-label="<?php echo h('Home'); ?>">
				<img class="brand-logo" src="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../assets/Canberra LBBS.png' : 'assets/Canberra LBBS.png'); ?>" alt="<?php echo h('Library System'); ?>">
			</a>
		</div>
            
            <!-- Navigation -->
            <nav>
                <!-- Mobile menu toggle button -->
                <button class="nav-toggle" aria-label="Toggle menu" data-nav-toggle>☰</button>
                
                <!-- Navigation links -->
                <div class="nav-links" data-nav-links>
                    <!-- Public navigation - always visible -->
                    <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'index.php')!==false?'active':''; ?>" 
                       href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../index.php' : 'index.php'); ?>">
                        <?php echo h('Home'); ?>
                    </a>
                    
                    <!-- Show catalog link only for guests -->
                    <?php if (!$u): ?>
                    <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'catalog.php')!==false?'active':''; ?>" 
                       href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../catalog.php' : 'catalog.php'); ?>">
                        <?php echo h('Catalog'); ?>
                    </a>
                    <?php endif; ?>
                    
                    <a class="<?php echo (strpos($_SERVER['SCRIPT_NAME'], 'faqs.php')!==false)?'active':''; ?>" 
                       href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../faqs.php' : 'faqs.php'); ?>">
                        <?php echo h('FAQs'); ?>
                    </a>
                    
                    <a class="<?php echo (strpos($_SERVER['SCRIPT_NAME'], 'about.php')!==false)?'active':''; ?>" 
                       href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../about.php' : 'about.php'); ?>">
                        <?php echo h('About'); ?>
                    </a>
                    
                    <!-- Authentication-based navigation -->
                    <?php if ($u): ?>
                        <!-- Logged in user navigation -->
                        <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'student_dashboard.php')!==false || strpos($_SERVER['SCRIPT_NAME'], 'faculty_dashboard.php')!==false || strpos($_SERVER['SCRIPT_NAME'], 'admin/dashboard.php')!==false?'active':''; ?>" 
                           href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../' . ($u['role'] === 'admin' ? 'admin/dashboard.php' : ($u['role'] === 'faculty' ? 'faculty_dashboard.php' : 'student_dashboard.php')) : ($u['role'] === 'admin' ? 'admin/dashboard.php' : ($u['role'] === 'faculty' ? 'faculty_dashboard.php' : 'student_dashboard.php'))); ?>">
                            <?php echo h('Dashboard'); ?>
                        </a>
                    <?php else: ?>
                        <!-- Guest navigation -->
                        <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'login.php')!==false?'active':''; ?>" 
                           href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../login.php' : 'login.php'); ?>">
                            <?php echo h('Login'); ?>
                        </a>
                        
                        <a class="<?php echo strpos($_SERVER['SCRIPT_NAME'], 'register.php')!==false?'active':''; ?>" 
                           href="<?php echo h((strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '../register.php' : 'register.php'); ?>">
                            <?php echo h('Register'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>


