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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Manage Books'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 1100px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
th, td { border: 1px solid #ddd; padding: .5rem; text-align: left; }
form.inline { display: inline; }
label { display:block; margin-top:.4rem; }
input[type="text"], select { width: 100%; padding: .4rem; }
.rowform { display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:.5rem; margin-top:1rem; }
button { padding:.4rem .7rem; }
a { display:inline-block; margin-bottom:1rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Manage Books'); ?></h1>
	<a href="dashboard.php"><?php echo h('Back to Dashboard'); ?></a>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<h2><?php echo h('Add New Book'); ?></h2>
	<form method="post" action="" class="rowform">
		<?php echo csrf_field(); ?>
		<input type="hidden" name="action" value="add">
		<input type="text" name="title" placeholder="<?php echo h('Title'); ?>" required>
		<input type="text" name="author" placeholder="<?php echo h('Author'); ?>" required>
		<input type="text" name="category" placeholder="<?php echo h('Category'); ?>" required>
		<input type="text" name="cover_url" placeholder="<?php echo h('Cover URL'); ?>">
		<button type="submit"><?php echo h('Add'); ?></button>
	</form>

	<h2 style="margin-top:1.5rem;">\
	<?php echo h('All Books'); ?></h2>
	<table>
		<thead>
			<tr>
				<th><?php echo h('Title'); ?></th>
				<th><?php echo h('Author'); ?></th>
				<th><?php echo h('Category'); ?></th>
				<th><?php echo h('Cover'); ?></th>
				<th><?php echo h('Availability'); ?></th>
				<th><?php echo h('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($books as $b): ?>
			<tr>
				<td><?php echo h($b['title']); ?></td>
				<td><?php echo h($b['author']); ?></td>
				<td><?php echo h($b['category']); ?></td>
				<td>
					<?php if (!empty($b['cover_url'])): ?>
						<img src="<?php echo h($b['cover_url']); ?>" alt="<?php echo h($b['title']); ?>" style="width:80px;height:auto;">
					<?php else: ?>
						<?php echo h('No image'); ?>
					<?php endif; ?>
				</td>
				<td><?php echo (int)$b['availability'] === 1 ? h('Available') : h('Borrowed'); ?></td>
				<td>
					<form method="post" class="inline" action="">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="action" value="edit">
						<input type="hidden" name="book_id" value="<?php echo h((string)$b['book_id']); ?>">
						<input type="text" name="title" value="<?php echo h($b['title']); ?>" required>
						<input type="text" name="author" value="<?php echo h($b['author']); ?>" required>
						<input type="text" name="category" value="<?php echo h($b['category']); ?>" required>
						<input type="text" name="cover_url" value="<?php echo h((string)$b['cover_url']); ?>" placeholder="<?php echo h('Cover URL'); ?>">
						<select name="availability">
							<option value="1" <?php echo (int)$b['availability']===1?'selected':''; ?>><?php echo h('Available'); ?></option>
							<option value="0" <?php echo (int)$b['availability']===0?'selected':''; ?>><?php echo h('Borrowed'); ?></option>
						</select>
						<button type="submit"><?php echo h('Save'); ?></button>
					</form>
					<form method="post" class="inline" action="" onsubmit="return confirm('Delete this book?');">
						<?php echo csrf_field(); ?>
						<input type="hidden" name="action" value="delete">
						<input type="hidden" name="book_id" value="<?php echo h((string)$b['book_id']); ?>">
						<button type="submit" <?php echo (int)$b['availability']===0?'disabled':''; ?>><?php echo h('Delete'); ?></button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

</div>
</body>
</html>


