<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

start_secure_session();

include __DIR__ . '/includes/header.php';
?>

<main class="section">
	<div class="container">
		<h1 class="section-heading"><?php echo h('Frequently Asked Questions'); ?></h1>
		<p class="section-sub muted"><?php echo h('Answers to common questions about using the Library Management System.'); ?></p>

		<div class="accordion">
			<?php
			$faqs = [
				['q' => 'How do I create an account?', 'a' => 'Use the Register link in the header to open the sign-up form. Choose your role (Student or Faculty), then complete the required information.'],
				['q' => 'How can I borrow a book?', 'a' => 'Open the Catalog page, search or browse for a book, and click Borrow Book. You will be asked to choose a return date.'],
				['q' => 'Where do I see my borrowed books?', 'a' => 'Open your dashboard and click Return Book to see currently borrowed titles, or Borrow History to view returned items.'],
				['q' => 'How do I return a book?', 'a' => 'Go to Return Book from the sidebar, then click Return next to the book. The due date is also shown there.'],
				['q' => 'Can I change my name, email, or password?', 'a' => 'Yes. Use the Profile page from the sidebar to update your information securely.'],
				['q' => 'What happens if I miss the due date?', 'a' => 'Books may be marked as overdue. Please return them as soon as possible. (Overdue policy can vary by institution.)'],
				['q' => 'Who can manage books and users?', 'a' => 'Only Administrators can access Manage Books, Manage Users, and Borrow Logs from the admin dashboard.'],
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
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


