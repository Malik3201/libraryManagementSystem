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
// Do NOT redirect logged-in users away from the homepage.
// Redirection to dashboards should only happen after a successful login action.
include __DIR__ . '/includes/header.php';
?>

<main>
	<section class="hero section" id="home">
        <div class="container two-col">
            <div>
                <span class="kicker"><?php echo h('Modern Academic Library'); ?></span>
                <h1 class="title" style="margin-top:8px;">
                    <?php echo h('Search, Borrow, and Learn — All in One Place'); ?>
                </h1>
                <p class="subtitle"><?php echo h('A fast, secure, and delightful system for students, faculty, and administrators.'); ?></p>
                <div class="actions">
                    <?php if ($u): ?>
                        <!-- Logged in user actions -->
                        <a class="btn btn-accent" href="<?php echo h($u['role'] === 'admin' ? 'admin/dashboard.php' : ($u['role'] === 'faculty' ? 'faculty_dashboard.php' : 'student_dashboard.php')); ?>"><?php echo h('Go to Dashboard'); ?></a>
                        <a class="btn btn-outline" href="catalog.php"><?php echo h('Browse Catalog'); ?></a>
                    <?php else: ?>
                        <!-- Guest actions -->
                        <a class="btn btn-accent" href="login.php"><?php echo h('Get Started'); ?></a>
                        <a class="btn btn-outline" href="catalog.php"><?php echo h('Browse Catalog'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="illus">
                <img src="<?php echo h('https://www.skoolbeep.com/blog/wp-content/uploads/2020/12/BEST-LIBRARY-AUTOMATION-SOFTWARE-min.png'); ?>" alt="<?php echo h('Library illustration'); ?>">
            </div>
        </div>
	</section>

    <section class="section" id="glance">
		<div class="container">
			<h2 class="section-heading"><?php echo h('At a Glance'); ?></h2>
			<p class="section-sub muted"><?php echo h('Fast access, secure accounts, clear borrowing.'); ?></p>
			<div class="stats-row">
				<div class="card center"><div class="stat"><?php echo h('10k+'); ?></div><div class="stat-label"><?php echo h('Books'); ?></div></div>
				<div class="card center"><div class="stat"><?php echo h('5k+'); ?></div><div class="stat-label"><?php echo h('Members'); ?></div></div>
				<div class="card center"><div class="stat"><?php echo h('24/7'); ?></div><div class="stat-label"><?php echo h('Access'); ?></div></div>
				<div class="card center"><div class="stat"><?php echo h('99.9%'); ?></div><div class="stat-label"><?php echo h('Uptime'); ?></div></div>
			</div>
		</div>
	</section>

	<section class="section" id="why">
		<div class="container">
			<h2 class="section-heading"><?php echo h('Why Choose Us'); ?></h2>
			<p class="section-sub muted"><?php echo h('Built for performance, security, and simplicity.'); ?></p>
			<div class="grid cols-3">
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.24 7.24a6 6 0 0 1-8.49 8.49L3 21l5.27-8.76a6 6 0 1 1 11.97-5z"></path></svg></div><h3><?php echo h('Secure by Design'); ?></h3><p class="muted"><?php echo h('CSRF protection, hashed passwords, and prepared statements everywhere.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 19V5l-7 7h14"></path></svg></div><h3><?php echo h('Fast & Reliable'); ?></h3><p class="muted"><?php echo h('Optimized queries and responsive UI for quick browsing.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M14.31 8l5.74 9.94"></path><path d="M9.69 8h11.48"></path><path d="M7.38 12l5.74-9.94"></path><path d="M9.69 16L3.95 6.06"></path><path d="M14.31 16H2.83"></path><path d="M16.62 12l-5.74 9.94"></path></svg></div><h3><?php echo h('Purpose-Built'); ?></h3><p class="muted"><?php echo h('Designed for students, faculty, and admins with the right tools.'); ?></p></div>
			</div>
		</div>
	</section>

    <section class="section" id="features">
		<div class="container">
			<h2 class="section-heading"><?php echo h('Features'); ?></h2>
			<p class="section-sub muted"><?php echo h('Everything you need to manage your library journey.'); ?></p>
			<div class="grid cols-4">
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div><h3><?php echo h('Powerful Search'); ?></h3><p class="muted"><?php echo h('Find books by title, author, or category.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="14" x2="20" y2="14"></line><line x1="4" y1="10" x2="14" y2="10"></line><circle cx="18" cy="10" r="2"></circle></svg></div><h3><?php echo h('Filter & Sort'); ?></h3><p class="muted"><?php echo h('Narrow results to what matters most.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path><path d="M4 4v15"></path><path d="M8 4v15"></path><path d="M12 4v15"></path></svg></div><h3><?php echo h('Borrow & Return'); ?></h3><p class="muted"><?php echo h('Track your loans securely.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v4H3z"></path><path d="M8 7v14"></path><path d="M16 7v14"></path></svg></div><h3><?php echo h('History Tracking'); ?></h3><p class="muted"><?php echo h('See all your past activity.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-3-3.87"></path><path d="M4 21v-2a4 4 0 0 1 3-3.87"></path><circle cx="12" cy="7" r="4"></circle></svg></div><h3><?php echo h('Role-Based Access'); ?></h3><p class="muted"><?php echo h('Students, faculty, and admins get tailored experiences.'); ?></p></div>
				<div class="card"><div class="icon-lg"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div><h3><?php echo h('Secure Sessions'); ?></h3><p class="muted"><?php echo h('Modern protections for your account.'); ?></p></div>
			</div>
		</div>
	</section>

	<section class="section" id="how-it-works">
		<div class="container">
			<h2 class="section-heading"><?php echo h('How It Works'); ?></h2>
			<div class="timeline">
				<div class="step"><span class="num">1</span><div><h3><?php echo h('Browse'); ?></h3><p class="muted"><?php echo h('Search the catalog by title, author, or category.'); ?></p></div></div>
				<div class="step"><span class="num">2</span><div><h3><?php echo h('Borrow'); ?></h3><p class="muted"><?php echo h('Borrow available books instantly with one click.'); ?></p></div></div>
				<div class="step"><span class="num">3</span><div><h3><?php echo h('Return'); ?></h3><p class="muted"><?php echo h('Return on time and keep your history organized.'); ?></p></div></div>
			</div>
		</div>
	</section>

	<section class="section" id="callouts">
  <div class="container">
    <div class="split">
      <div class="card">
        <h3>For Students & Faculty</h3>
        <p class="muted">Quickly access resources for courses and research.</p>
        <a class="btn btn-accent" href="catalog.php">Explore Catalog</a>
      </div>
      <div class="card">
        <h3>For Admins</h3>
        <p class="muted">Manage users, books, and borrowing logs.</p>
        <a class="btn btn-outline" href="admin/dashboard.php">Admin Panel</a>
      </div>
    </div>
  </div>
</section>


	<section class="section" id="testimonials">
		<div class="container">
			<h2 class="section-heading"><?php echo h('Meet the Team'); ?></h2>
			<p class="section-sub muted"><?php echo h('The people behind the platform.'); ?></p>
			<div class="grid cols-3" style="margin-bottom:20px;">
				<div class="card center"><span class="avatar-lg" style="margin:0 auto 8px;"></span><h3><?php echo h('Muhammad Haider Ali'); ?></h3><p class="muted"><?php echo h('Backend Developer'); ?></p></div>
				<div class="card center"><span class="avatar-lg" style="margin:0 auto 8px;"></span><h3><?php echo h('Ruplal Sah'); ?></h3><p class="muted"><?php echo h('Frontend Developer'); ?></p></div>
				<div class="card center"><span class="avatar-lg" style="margin:0 auto 8px;"></span><h3><?php echo h('Arash Mathur'); ?></h3><p class="muted"><?php echo h('Testing & Documentation'); ?></p></div>
			</div>
			<h2 class="section-heading"><?php echo h('What Users Say'); ?></h2>
<div class="grid cols-3">
  <div class="card">
    <div class="user-info">
      <span class="avatar">
        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="A. Student">
      </span>
      <strong><?php echo h('A. Student'); ?></strong>
    </div>
    <p>“<?php echo h('The catalog is fast and borrowing is effortless.'); ?>”</p>
  </div>

  <div class="card">
    <div class="user-info">
      <span class="avatar">
        <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="F. Faculty">
      </span>
      <strong><?php echo h('F. Faculty'); ?></strong>
    </div>
    <p>“<?php echo h('Great for managing class readings.'); ?>”</p>
  </div>

  <div class="card">
    <div class="user-info">
      <span class="avatar">
        <img src="https://randomuser.me/api/portraits/men/14.jpg" alt="L. Admin">
      </span>
      <strong><?php echo h('L. Admin'); ?></strong>
    </div>
    <p>“<?php echo h('Admin tools are simple yet powerful.'); ?>”</p>
  </div>
</div>

		</div>
	</section>

	<!-- Contact section intentionally removed per requirements -->

	<section class="section" id="faqs">
		<div class="container">
			<h2 class="section-heading"><?php echo h('Frequently Asked Questions'); ?></h2>
			<div class="accordion">
				<?php
				$faqs = [
					['q' => 'How do I create an account?', 'a' => 'Use the Register link and fill the form.'],
					['q' => 'How to borrow a book?', 'a' => 'Open the catalog and click Borrow on an available book.'],
					['q' => 'How do I return a book?', 'a' => 'Visit your History page and click Return on an active record.'],
					['q' => 'Who can access admin features?', 'a' => 'Only users with the admin role.'],
					['q' => 'Is my data secure?', 'a' => 'We use secure sessions, CSRF protection, and prepared statements.'],
					['q' => 'Can faculty do more?', 'a' => 'Faculty have the same borrowing features as students.'],
				];
				foreach ($faqs as $i => $f): ?>
					<div class="accordion-item">
						<button class="accordion-header" aria-expanded="false">
							<span><?php echo h($f['q']); ?></span>
							<span>+</span>
						</button>
						<div class="accordion-content">
							<p><?php echo h($f['a']); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section" id="cta">
		<div class="container">
			<div class="cta-band">
				<h2 class="section-heading"><?php echo h('Ready to get started?'); ?></h2>
				<p class="section-sub" style="color:rgba(255,255,255,0.9)"><?php echo h('Join now and borrow your first book today.'); ?></p>
				<a class="btn" href="register.php"><?php echo h('Create Account'); ?></a>
			</div>
		</div>
	</section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

