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
$dueDate = isset($_POST['due_date']) ? trim((string)$_POST['due_date']) : '';
if ($bookId <= 0) {
	flash('error', 'Invalid book.');
	redirect('catalog.php');
}
// Validate due date (YYYY-MM-DD and today or later)
if ($dueDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
    flash('error', 'Please select a valid return date.');
    redirect('catalog.php');
}
if (strtotime($dueDate) === false || strtotime($dueDate) < strtotime(date('Y-m-d'))) {
    flash('error', 'Return date cannot be in the past.');
    redirect('catalog.php');
}

$pdo = db();
// Ensure schema has due_date column (MySQL 8+)
try { $pdo->exec('ALTER TABLE borrow_records ADD COLUMN IF NOT EXISTS due_date DATE NULL AFTER borrow_date'); } catch (Throwable $e) { /* ignore if not supported */ }

try {
	$pdo->beginTransaction();
    // Prevent same user from borrowing the same book twice concurrently
    $check = $pdo->prepare('SELECT 1 FROM borrow_records WHERE user_id = ? AND book_id = ? AND status = "borrowed" LIMIT 1');
    $check->execute([(int)$user['user_id'], $bookId]);
    if ($check->fetch()) {
        $pdo->rollBack();
        flash('error', 'You have already borrowed this book.');
        redirect('catalog.php');
    }

    // Check availability (copies remaining)
    $stmt = $pdo->prepare('SELECT availability FROM books WHERE book_id = ? FOR UPDATE');
	$stmt->execute([$bookId]);
	$row = $stmt->fetch();
    if (!$row || (int)$row['availability'] <= 0) {
		$pdo->rollBack();
        flash('error', 'Book is not available.');
		redirect('catalog.php');
	}
    // Insert record with due date
    $stmt = $pdo->prepare('INSERT INTO borrow_records (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, CURDATE(), ?, "borrowed")');
    $stmt->execute([(int)$user['user_id'], $bookId, $dueDate]);
    // Decrement availability by 1
    $stmt = $pdo->prepare('UPDATE books SET availability = availability - 1 WHERE book_id = ?');
    $stmt->execute([$bookId]);
	$pdo->commit();
	flash('success', 'Book borrowed');
} catch (Throwable $e) {
	if ($pdo->inTransaction()) { $pdo->rollBack(); }
	flash('error', 'Failed to borrow book.');
}

redirect('catalog.php');


