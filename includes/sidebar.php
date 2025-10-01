<?php
/*
 * Purpose: Dashboard sidebar navigation
 */

declare(strict_types=1);

$user = current_user();
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$in_admin = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$is_admin = ($user['role'] ?? '') === 'admin';
$is_faculty = ($user['role'] ?? '') === 'faculty';
$is_student = ($user['role'] ?? '') === 'student';
?>

<button class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>

<aside class="dashboard-sidebar">
	<div class="sidebar-header">
		<div class="user-info">
			<div class="avatar">
				<?php if (isset($user['avatar']) && $user['avatar']): ?>
					<img src="<?php echo h($user['avatar']); ?>" alt="User avatar">
				<?php else: ?>
					<div class="avatar-placeholder">
						<?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="user-details">
				<h3><?php echo h($user['name'] ?? 'User'); ?></h3>
				<span class="user-role"><?php echo h(ucfirst($user['role'] ?? 'user')); ?></span>
			</div>
		</div>
	</div>
	
	<nav class="sidebar-nav">
		<ul class="nav-menu">
			<!-- Dashboard Home -->
            <li class="nav-item">
                <a href="<?php echo h($is_admin ? ($in_admin ? 'dashboard.php' : 'admin/dashboard.php') : ($is_faculty ? 'faculty_dashboard.php' : 'student_dashboard.php')); ?>" 
				   class="nav-link <?php echo ($current_page === 'dashboard' || $current_page === 'student_dashboard' || $current_page === 'faculty_dashboard') ? 'active' : ''; ?>">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
							<polyline points="9,22 9,12 15,12 15,22"></polyline>
						</svg>
					</span>
					<span class="nav-text">Dashboard</span>
				</a>
			</li>
			
			<!-- Catalog -->
            <?php if (!$is_admin): ?>
            <li class="nav-item">
                <a href="<?php echo h($in_admin ? '../catalog.php' : 'catalog.php'); ?>" class="nav-link <?php echo $current_page === 'catalog' ? 'active' : ''; ?>">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
							<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
						</svg>
					</span>
					<span class="nav-text">Browse Books</span>
				</a>
            </li>
            <?php endif; ?>
			
			<?php if (!$is_admin): ?>
			<!-- Borrow/Return - Only for students and faculty -->
			<li class="nav-item">
				<a href="return.php" class="nav-link <?php echo $current_page === 'return' ? 'active' : ''; ?>">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H5a2 2 0 0 0-2-2z"></path>
							<path d="M8 21v-4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"></path>
							<path d="M12 3v18"></path>
						</svg>
					</span>
					<span class="nav-text">Return Book</span>
				</a>
			</li>
			
			<!-- History -->
			<li class="nav-item">
				<a href="history.php" class="nav-link <?php echo $current_page === 'history' ? 'active' : ''; ?>">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M3 3v18h18"></path>
							<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
						</svg>
					</span>
					<span class="nav-text">Borrow History</span>
				</a>
			</li>
			<?php endif; ?>
			
			<?php if ($is_admin): ?>
				<!-- Admin Section -->
				<li class="nav-section">
					<span class="section-title">Admin Panel</span>
				</li>
				
                <li class="nav-item">
                    <a href="<?php echo h($in_admin ? 'manage_books.php' : 'admin/manage_books.php'); ?>" class="nav-link <?php echo $current_page === 'manage_books' ? 'active' : ''; ?>">
						<span class="nav-icon">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
							</svg>
						</span>
						<span class="nav-text">Manage Books</span>
					</a>
				</li>
				
                <li class="nav-item">
                    <a href="<?php echo h($in_admin ? 'manage_users.php' : 'admin/manage_users.php'); ?>" class="nav-link <?php echo $current_page === 'manage_users' ? 'active' : ''; ?>">
						<span class="nav-icon">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
								<circle cx="9" cy="7" r="4"></circle>
								<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
								<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
							</svg>
						</span>
						<span class="nav-text">Manage Users</span>
					</a>
				</li>
				
                <li class="nav-item">
                    <a href="<?php echo h($in_admin ? 'borrow_logs.php' : 'admin/borrow_logs.php'); ?>" class="nav-link <?php echo $current_page === 'borrow_logs' ? 'active' : ''; ?>">
						<span class="nav-icon">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M3 3v18h18"></path>
								<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
							</svg>
						</span>
						<span class="nav-text">Borrow Logs</span>
					</a>
				</li>
			<?php endif; ?>
			
			<!-- Settings -->
			<li class="nav-section">
				<span class="section-title">Account</span>
			</li>
			
			<li class="nav-item">
				<a href="<?php echo h($is_admin ? '../profile.php' : 'profile.php'); ?>" class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
							<circle cx="12" cy="7" r="4"></circle>
						</svg>
					</span>
					<span class="nav-text">Profile</span>
				</a>
			</li>
			
			<li class="nav-item">
				<a href="<?php echo h($is_admin ? '../logout.php' : 'logout.php'); ?>" class="nav-link logout">
					<span class="nav-icon">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
							<polyline points="16,17 21,12 16,7"></polyline>
							<line x1="21" y1="12" x2="9" y2="12"></line>
						</svg>
					</span>
					<span class="nav-text">Logout</span>
				</a>
			</li>
		</ul>
	</nav>
</aside>
