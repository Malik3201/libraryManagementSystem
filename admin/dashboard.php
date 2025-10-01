<?php
/*
 * Purpose: Admin dashboard showing high-level stats
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

$totalUsers = (int)$pdo->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
$totalBooks = (int)$pdo->query('SELECT COUNT(*) AS c FROM books')->fetch()['c'];
$activeBorrowed = (int)$pdo->query("SELECT COUNT(*) AS c FROM borrow_records WHERE status='borrowed'")->fetch()['c'];
$returned = (int)$pdo->query("SELECT COUNT(*) AS c FROM borrow_records WHERE status='returned'")->fetch()['c'];

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
				<h1 class="admin-title"><?php echo h('Admin Dashboard'); ?></h1>
				<p class="admin-subtitle"><?php echo h('Manage your library system'); ?></p>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<div class="admin-stats">
			<div class="admin-stat-card">
				<div class="admin-stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M20 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M4 21v-2a4 4 0 0 1 3-3.87"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</div>
				<div class="admin-stat-number"><?php echo h((string)$totalUsers); ?></div>
				<div class="admin-stat-label"><?php echo h('Total Users'); ?></div>
			</div>
			<div class="admin-stat-card">
				<div class="admin-stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path>
						<path d="M4 4v15"></path>
						<path d="M8 4v15"></path>
						<path d="M12 4v15"></path>
					</svg>
				</div>
				<div class="admin-stat-number"><?php echo h((string)$totalBooks); ?></div>
				<div class="admin-stat-label"><?php echo h('Total Books'); ?></div>
			</div>
			<div class="admin-stat-card">
				<div class="admin-stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M3 3h18v4H3z"></path>
						<path d="M8 7v14"></path>
						<path d="M16 7v14"></path>
					</svg>
				</div>
				<div class="admin-stat-number"><?php echo h((string)$activeBorrowed); ?></div>
				<div class="admin-stat-label"><?php echo h('Currently Borrowed'); ?></div>
			</div>
			<div class="admin-stat-card">
				<div class="admin-stat-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M9 12l2 2 4-4"></path>
						<circle cx="12" cy="12" r="10"></circle>
					</svg>
				</div>
				<div class="admin-stat-number"><?php echo h((string)$returned); ?></div>
				<div class="admin-stat-label"><?php echo h('Books Returned'); ?></div>
			</div>
		</div>

		<!-- Enhanced Dashboard Content -->
		<div class="dashboard-content">
			<!-- Charts Section -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Library Analytics'); ?></h2>
				<div class="charts-grid">
					<div class="chart-card">
						<h3><?php echo h('Borrowing Trends'); ?></h3>
						<div class="chart-container">
							<canvas id="borrowingChart" width="400" height="200"></canvas>
						</div>
					</div>
					<div class="chart-card">
						<h3><?php echo h('User Distribution'); ?></h3>
						<div class="chart-container">
							<canvas id="userChart" width="300" height="300"></canvas>
						</div>
					</div>
				</div>
			</div>

			<!-- Recent Activity -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Recent Activity'); ?></h2>
				<div class="activity-feed">
					<div class="activity-item">
						<div class="activity-icon">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
							</svg>
						</div>
						<div class="activity-content">
							<p><strong>New book added:</strong> "Introduction to Machine Learning" by Dr. Smith</p>
							<span class="activity-time">2 hours ago</span>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
								<circle cx="9" cy="7" r="4"></circle>
								<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
								<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
							</svg>
						</div>
						<div class="activity-content">
							<p><strong>New user registered:</strong> John Doe (Student)</p>
							<span class="activity-time">4 hours ago</span>
						</div>
					</div>
					<div class="activity-item">
						<div class="activity-icon">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M9 12l2 2 4-4"></path>
								<circle cx="12" cy="12" r="10"></circle>
							</svg>
						</div>
						<div class="activity-content">
							<p><strong>Book returned:</strong> "Data Structures" by Jane Wilson</p>
							<span class="activity-time">6 hours ago</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('Quick Actions'); ?></h2>
				<div class="quick-actions">
					<a href="manage_books.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
				</svg>
						</div>
						<h3><?php echo h('Manage Books'); ?></h3>
						<p><?php echo h('Add, edit, or remove books from the library'); ?></p>
					</a>
					<a href="manage_users.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
								<circle cx="9" cy="7" r="4"></circle>
								<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
								<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
				</svg>
						</div>
						<h3><?php echo h('Manage Users'); ?></h3>
						<p><?php echo h('View and manage user accounts and roles'); ?></p>
					</a>
					<a href="borrow_logs.php" class="quick-action-card">
						<div class="quick-action-icon">
							<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M3 3v18h18"></path>
								<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
				</svg>
						</div>
						<h3><?php echo h('View Reports'); ?></h3>
						<p><?php echo h('Check borrowing logs and generate reports'); ?></p>
					</a>
				</div>
			</div>

			<!-- System Status -->
			<div class="dashboard-section">
				<h2 class="section-title"><?php echo h('System Status'); ?></h2>
				<div class="status-grid">
					<div class="status-card">
						<div class="status-indicator online"></div>
						<div class="status-content">
							<h4><?php echo h('Database'); ?></h4>
							<p><?php echo h('All systems operational'); ?></p>
						</div>
					</div>
					<div class="status-card">
						<div class="status-indicator online"></div>
						<div class="status-content">
							<h4><?php echo h('Authentication'); ?></h4>
							<p><?php echo h('Secure sessions active'); ?></p>
						</div>
					</div>
					<div class="status-card">
						<div class="status-indicator online"></div>
						<div class="status-content">
							<h4><?php echo h('File Storage'); ?></h4>
							<p><?php echo h('Storage space: 85% available'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
	function renderFallbackCharts() {
		var trendContainer = document.getElementById('borrowingChart')?.parentElement;
		var userContainer = document.getElementById('userChart')?.parentElement;
		if (trendContainer) {
			trendContainer.innerHTML = '<div class="bar-chart">'
				+ '<div class="bar" style="height:70%"></div>'
				+ '<div class="bar" style="height:40%"></div>'
				+ '<div class="bar" style="height:85%"></div>'
				+ '<div class="bar" style="height:55%"></div>'
				+ '<div class="bar" style="height:30%"></div>'
				+ '<div class="bar" style="height:60%"></div>'
				+ '</div>';
		}
		if (userContainer) {
			userContainer.innerHTML = '<div class="donut-chart">'
				+ '<svg viewBox="0 0 42 42" class="donut"><circle class="donut-ring" cx="21" cy="21" r="15.915" stroke-width="6"></circle>'
				+ '<circle class="donut-segment seg1" cx="21" cy="21" r="15.915" stroke-width="6" stroke-dasharray="60 40" stroke-dashoffset="25"></circle>'
				+ '<circle class="donut-segment seg2" cx="21" cy="21" r="15.915" stroke-width="6" stroke-dasharray="25 75" stroke-dashoffset="-35"></circle>'
				+ '<circle class="donut-segment seg3" cx="21" cy="21" r="15.915" stroke-width="6" stroke-dasharray="15 85" stroke-dashoffset="-60"></circle>'
				+ '</svg></div>';
		}
	}

	function initCharts() {
		try {
			if (typeof Chart === 'undefined') {
				renderFallbackCharts();
				return;
			}
			var borrowingEl = document.getElementById('borrowingChart');
			var userEl = document.getElementById('userChart');
			if (!borrowingEl || !userEl) { return; }
			var borrowingCtx = borrowingEl.getContext('2d');
			new Chart(borrowingCtx, {
				type: 'line',
				data: {
					labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
					datasets: [{
						label: 'Books Borrowed',
						data: [12, 19, 3, 5, 2, 3],
						borderColor: '#E67E22',
						backgroundColor: 'rgba(230, 126, 34, 0.1)',
						tension: 0.4,
						fill: true
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { display: false } },
					scales: {
						y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#9ca3af' } },
						x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#9ca3af' } }
					}
				}
			});

			var userCtx = userEl.getContext('2d');
			new Chart(userCtx, {
				type: 'doughnut',
				data: {
					labels: ['Students', 'Faculty', 'Admins'],
					datasets: [{
						data: [65, 25, 10],
						backgroundColor: ['#E67E22', '#3498db', '#e74c3c'],
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af', padding: 20 } } }
				}
			});
		} catch (e) {
			renderFallbackCharts();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCharts);
	} else {
		initCharts();
	}
})();
</script>


