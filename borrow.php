<?php
/*
 * Purpose: Handle borrow action (POST only)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

start_secure_session();
$user = current_user();
if (!$user) {
	flash('error', 'Please login to borrow books.');
	redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	flash('error', 'Invalid request method.');
	redirect('catalog.php');
}

$token = $_POST['csrf_token'] ?? '';
if (!csrf_verify($token)) {
	flash('error', 'Invalid request.');
	redirect('catalog.php');
}

$bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
if ($bookId <= 0) {
	flash('error', 'Invalid book.');
	redirect('catalog.php');
}

$pdo = db();

try {
	$pdo->beginTransaction();
	// Check availability
	$stmt = $pdo->prepare('SELECT availability FROM books WHERE book_id = ? FOR UPDATE');
	$stmt->execute([$bookId]);
	$row = $stmt->fetch();
	if (!$row || (int)$row['availability'] !== 1) {
		$pdo->rollBack();
		flash('error', 'Book is not available.');
		redirect('catalog.php');
	}
	// Insert record
	$stmt = $pdo->prepare('INSERT INTO borrow_records (user_id, book_id, borrow_date, status) VALUES (?, ?, CURDATE(), "borrowed")');
	$stmt->execute([(int)$user['user_id'], $bookId]);
	// Update availability
	$stmt = $pdo->prepare('UPDATE books SET availability = 0 WHERE book_id = ?');
	$stmt->execute([$bookId]);
	$pdo->commit();
	flash('success', 'Book borrowed');
} catch (Throwable $e) {
	if ($pdo->inTransaction()) { $pdo->rollBack(); }
	flash('error', 'Failed to borrow book.');
}

redirect('catalog.php');


