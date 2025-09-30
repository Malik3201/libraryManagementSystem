<?php
/*
 * Purpose: Handle return action (POST only)
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
	flash('error', 'Please login to return books.');
	redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	flash('error', 'Invalid request method.');
	redirect('history.php');
}

$token = $_POST['csrf_token'] ?? '';
if (!csrf_verify($token)) {
	flash('error', 'Invalid request.');
	redirect('history.php');
}

$recordId = isset($_POST['record_id']) ? (int)$_POST['record_id'] : 0;
if ($recordId <= 0) {
	flash('error', 'Invalid record.');
	redirect('history.php');
}

$pdo = db();

try {
	$pdo->beginTransaction();
	// Lock and verify ownership or admin
	$stmt = $pdo->prepare('SELECT br.book_id, br.user_id, br.status FROM borrow_records br WHERE br.record_id = ? FOR UPDATE');
	$stmt->execute([$recordId]);
	$rec = $stmt->fetch();
	if (!$rec) {
		$pdo->rollBack();
		flash('error', 'Record not found.');
		redirect('history.php');
	}
	$isAdmin = isset($user['role']) && $user['role'] === 'admin';
	if (!$isAdmin && (int)$rec['user_id'] !== (int)$user['user_id']) {
		$pdo->rollBack();
		flash('error', 'Not authorized to return this record.');
		redirect('history.php');
	}
	if ($rec['status'] !== 'borrowed') {
		$pdo->rollBack();
		flash('error', 'Record already returned.');
		redirect('history.php');
	}
	// Update record and availability
	$stmt = $pdo->prepare('UPDATE borrow_records SET return_date = CURDATE(), status = "returned" WHERE record_id = ?');
	$stmt->execute([$recordId]);
	$stmt = $pdo->prepare('UPDATE books SET availability = 1 WHERE book_id = ?');
	$stmt->execute([(int)$rec['book_id']]);
	$pdo->commit();
	flash('success', 'Book returned');
} catch (Throwable $e) {
	if ($pdo->inTransaction()) { $pdo->rollBack(); }
	flash('error', 'Failed to return book.');
}

redirect('history.php');


