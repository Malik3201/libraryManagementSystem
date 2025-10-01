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

// Render a Return UI on GET so the menu item can show a page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT br.record_id, br.book_id, br.borrow_date, br.due_date, b.title FROM borrow_records br JOIN books b ON b.book_id = br.book_id WHERE br.user_id = ? AND br.status = "borrowed" ORDER BY br.borrow_date DESC, br.record_id DESC');
    $stmt->execute([(int)$user['user_id']]);
    $rows = $stmt->fetchAll();
    $error = get_flash('error');
    $success = get_flash('success');
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="dashboard-layout">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <main class="dashboard-main section">
    <div class="container">
        <div class="admin-header"><div><h1 class="admin-title"><?php echo h('Return Book'); ?></h1><p class="admin-subtitle"><?php echo h('Return an active borrowing'); ?></p></div></div>
        <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>
        <?php if (empty($rows)): ?>
            <div class="empty-card"><h3><?php echo h('No borrowed books to return'); ?></h3></div>
        <?php else: ?>
            <div class="table-responsive">
            <table class="admin-table striped hover" style="width:100%"><thead><tr><th><?php echo h('Book Title'); ?></th><th><?php echo h('Borrow Date'); ?></th><th><?php echo h('Return Date'); ?></th><th><?php echo h('Action'); ?></th></tr></thead><tbody>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?php echo h($r['title']); ?></strong></td>
                <td><?php echo h(date('M j, Y', strtotime($r['borrow_date']))); ?></td>
                <td><?php echo h(isset($r['due_date']) && $r['due_date'] ? date('M j, Y', strtotime($r['due_date'])) : '—'); ?></td>
                <td>
                    <form method="post" action="return.php" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="record_id" value="<?php echo h((string)$r['record_id']); ?>">
                        <button class="btn btn-accent" type="submit"><?php echo h('Return'); ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
            </div>
        <?php endif; ?>
    </div>
    </main>
    </div>
    <?php
    exit;
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
	// Increment availability by 1 when a copy is returned
	$stmt = $pdo->prepare('UPDATE books SET availability = availability + 1 WHERE book_id = ?');
	$stmt->execute([(int)$rec['book_id']]);
	$pdo->commit();
	flash('success', 'Book returned');
} catch (Throwable $e) {
	if ($pdo->inTransaction()) { $pdo->rollBack(); }
	flash('error', 'Failed to return book.');
}

redirect('history.php');






