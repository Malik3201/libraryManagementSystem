<?php
/*
 * Purpose: Admin book management (list, add, edit, delete)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST['csrf_token'] ?? '';
	if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
		flash('error', 'Invalid request.');
		redirect('manage_books.php');
	}

	$action = $_POST['action'] ?? '';
	if ($action === 'add') {
		$title = trim((string)($_POST['title'] ?? ''));
		$author = trim((string)($_POST['author'] ?? ''));
		$category = trim((string)($_POST['category'] ?? ''));
		$coverUrl = trim((string)($_POST['cover_url'] ?? ''));
		if ($title === '' || $author === '' || $category === '') {
			flash('error', 'All fields are required.');
			redirect('manage_books.php');
		}
		$stmt = $pdo->prepare('INSERT INTO books (title, author, category, cover_url, availability) VALUES (?, ?, ?, ?, 1)');
		$stmt->execute([$title, $author, $category, $coverUrl !== '' ? $coverUrl : null]);
		flash('success', 'Book added.');
		redirect('manage_books.php');
	}

	if ($action === 'edit') {
		$bookId = (int)($_POST['book_id'] ?? 0);
		$title = trim((string)($_POST['title'] ?? ''));
		$author = trim((string)($_POST['author'] ?? ''));
		$category = trim((string)($_POST['category'] ?? ''));
		$coverUrl = trim((string)($_POST['cover_url'] ?? ''));
		$availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 1;
		if ($bookId <= 0 || $title === '' || $author === '' || $category === '') {
			flash('error', 'Invalid input.');
			redirect('manage_books.php');
		}
		$stmt = $pdo->prepare('UPDATE books SET title=?, author=?, category=?, cover_url=?, availability=? WHERE book_id=?');
		$stmt->execute([$title, $author, $category, ($coverUrl !== '' ? $coverUrl : null), $availability, $bookId]);
		flash('success', 'Book updated.');
		redirect('manage_books.php');
	}

	if ($action === 'delete') {
		$bookId = (int)($_POST['book_id'] ?? 0);
		if ($bookId <= 0) {
			flash('error', 'Invalid book.');
			redirect('manage_books.php');
		}
		// prevent delete if currently borrowed
		$stmt = $pdo->prepare('SELECT availability FROM books WHERE book_id = ?');
		$stmt->execute([$bookId]);
		$book = $stmt->fetch();
		if (!$book) {
			flash('error', 'Book not found.');
			redirect('manage_books.php');
		}
		if ((int)$book['availability'] === 0) {
			flash('error', 'Cannot delete a borrowed book.');
			redirect('manage_books.php');
		}
		$stmt = $pdo->prepare('DELETE FROM books WHERE book_id = ?');
		$stmt->execute([$bookId]);
		flash('success', 'Book deleted.');
		redirect('manage_books.php');
	}
}

// Load books for list and potential editing
$books = $pdo->query('SELECT book_id, title, author, category, cover_url, availability, created_at FROM books ORDER BY created_at DESC, title ASC')->fetchAll();

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
				<h1 class="admin-title"><?php echo h('Manage Books'); ?></h1>
				<p class="admin-subtitle"><?php echo h('Add, edit, and manage your book collection'); ?></p>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<div class="admin-form-section">
			<h2><?php echo h('Add New Book'); ?></h2>
			<form method="post" action="" class="admin-form">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="action" value="add">
				<div class="form-row">
					<div>
						<label><?php echo h('Title'); ?></label>
						<input type="text" name="title" required placeholder="Enter book title">
					</div>
					<div>
						<label><?php echo h('Author'); ?></label>
						<input type="text" name="author" required placeholder="Enter author name">
					</div>
					<div>
						<label><?php echo h('Category'); ?></label>
						<input type="text" name="category" required placeholder="Enter category">
					</div>
					<div>
						<label><?php echo h('Cover URL'); ?></label>
						<input type="text" name="cover_url" placeholder="Optional cover image URL">
					</div>
				</div>
				<div class="form-actions">
					<button class="btn btn-edit add-book-btn" type="submit">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<line x1="12" y1="5" x2="12" y2="19"></line>
							<line x1="5" y1="12" x2="19" y2="12"></line>
						</svg>
						<?php echo h('Add Book'); ?>
					</button>
				</div>
			</form>
		</div>

		<div class="admin-form-section">
			<h2><?php echo h('All Books'); ?></h2>
			<?php if (empty($books)): ?>
				<div class="empty-card">
					<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; color: var(--color-text-muted);">
						<path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path>
						<path d="M4 4v15"></path>
						<path d="M8 4v15"></path>
						<path d="M12 4v15"></path>
					</svg>
					<h3><?php echo h('No books found'); ?></h3>
					<p><?php echo h('Start by adding your first book to the collection.'); ?></p>
				</div>
			<?php else: ?>
				<div class="table-responsive">
				<table class="admin-table striped hover">
					<thead>
						<tr>
							<th><?php echo h('Cover'); ?></th>
							<th><?php echo h('Title'); ?></th>
							<th><?php echo h('Author'); ?></th>
							<th><?php echo h('Category'); ?></th>
							<th><?php echo h('Availability'); ?></th>
							<th><?php echo h('Actions'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($books as $b): ?>
						<tr>
							<td>
								<?php if (!empty($b['cover_url'])): ?>
									<img class="book-cover" src="<?php echo h($b['cover_url']); ?>" alt="<?php echo h($b['title']); ?>">
								<?php else: ?>
									<div class="book-cover" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 0.8rem;">No Cover</div>
								<?php endif; ?>
							</td>
							<td>
								<div style="font-weight: 600;"><?php echo h($b['title']); ?></div>
							</td>
							<td><?php echo h($b['author']); ?></td>
							<td>
								<span class="category"><?php echo h($b['category']); ?></span>
							</td>
							<td>
								<?php echo (int)$b['availability'] === 1 ? '<span class="badge success">'.h('Available').'</span>' : '<span class="badge warn">'.h('Borrowed').'</span>'; ?>
							</td>
							<td>
								<div class="action-buttons">
							<button class="btn btn-edit-table" onclick="editBook(<?php echo h((string)$b['book_id']); ?>, '<?php echo h($b['title']); ?>', '<?php echo h($b['author']); ?>', '<?php echo h($b['category']); ?>', '<?php echo h((string)$b['cover_url']); ?>', <?php echo (int)$b['availability']; ?>)">
										<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
											<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
										</svg>
												Edit
									</button>
									<form method="post" style="display: inline;" onsubmit="return confirm('Delete this book?');">
										<?php echo csrf_field(); ?>
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="book_id" value="<?php echo h((string)$b['book_id']); ?>">
										<button class="btn btn-danger" type="submit" <?php echo (int)$b['availability']===0?'disabled':''; ?>>
											<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
												<polyline points="3,6 5,6 21,6"></polyline>
												<path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
											</svg>
											Delete
										</button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
	</main>
</div>

<!-- Edit Modal (hidden by default) -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
	<div style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
		<h3 style="margin-bottom: 16px;">Edit Book</h3>
		<form method="post" action="">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="action" value="edit">
			<input type="hidden" name="book_id" id="edit_book_id">
			<div style="display: grid; gap: 16px;">
				<div>
					<label>Title</label>
					<input type="text" name="title" id="edit_title" required>
				</div>
				<div>
					<label>Author</label>
					<input type="text" name="author" id="edit_author" required>
				</div>
				<div>
					<label>Category</label>
					<input type="text" name="category" id="edit_category" required>
				</div>
				<div>
					<label>Cover URL</label>
					<input type="text" name="cover_url" id="edit_cover_url">
				</div>
				<div>
					<label>Availability</label>
					<select name="availability" id="edit_availability">
						<option value="1">Available</option>
						<option value="0">Borrowed</option>
					</select>
				</div>
			</div>
			<div style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
				<button type="button" class="btn btn-muted" onclick="closeEditModal()">Cancel</button>
				<button type="submit" class="btn btn-accent">Save Changes</button>
			</div>
		</form>
	</div>
</div>

<script>
function editBook(id, title, author, category, coverUrl, availability) {
	document.getElementById('edit_book_id').value = id;
	document.getElementById('edit_title').value = title;
	document.getElementById('edit_author').value = author;
	document.getElementById('edit_category').value = category;
	document.getElementById('edit_cover_url').value = coverUrl;
	document.getElementById('edit_availability').value = availability;
	document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
	document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
	if (e.target === this) {
		closeEditModal();
	}
});
</script>


