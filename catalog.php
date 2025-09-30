<?php
/*
 * Purpose: Catalog listing with search, filters, pagination, and borrow action
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
	flash('error', 'Please login to access the catalog.');
	redirect('login.php');
}

csrf_token();

$pdo = db();

// Fetch categories for dropdown
$categories = [];
$stmtCat = $pdo->query('SELECT DISTINCT category FROM books ORDER BY category');
while ($row = $stmtCat->fetch()) { $categories[] = (string)$row['category']; }

// Read search params
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$avail = isset($_GET['avail']) ? (string)$_GET['avail'] : 'all'; // all|available

// Pagination params
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Build WHERE clauses safely
$where = [];
$params = [];
if ($q !== '') {
	$where[] = '(title LIKE ? ESCAPE "\\" OR author LIKE ? ESCAPE "\\" OR category LIKE ? ESCAPE "\\")';
	$like = escape_like($q);
	$params[] = $like; $params[] = $like; $params[] = $like;
}
if ($category !== '' && in_array($category, $categories, true)) {
	$where[] = 'category = ?';
	$params[] = $category;
}
if ($avail === 'available') {
	$where[] = 'availability = 1';
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count total
$sqlCount = 'SELECT COUNT(*) AS cnt FROM books ' . $whereSql;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total = (int)$stmtCount->fetch()['cnt'];
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

// Fetch page
$sql = 'SELECT book_id, title, author, category, cover_url, availability FROM books ' . $whereSql . ' ORDER BY created_at DESC, title ASC LIMIT ? OFFSET ?';
$stmt = $pdo->prepare($sql);
$bindParams = $params;
$bindParams[] = $perPage;
$bindParams[] = $offset;

// Bind with types to avoid emulation issues
$i = 1;
foreach ($params as $p) { $stmt->bindValue($i++, $p, PDO::PARAM_STR); }
$stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($i++, $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();

$error = get_flash('error');
$success = get_flash('success');

// Build base query string without page
$qs = $_GET;
unset($qs['page']);
$baseQuery = http_build_query($qs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h('Catalog'); ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 2rem; }
.container { max-width: 960px; margin: 0 auto; }
.flash { padding: .75rem; margin-bottom: 1rem; border-radius: 4px; }
.flash.error { background: #ffe5e5; color: #8a1f1f; }
.flash.success { background: #e6ffed; color: #0f6b2b; }
form.search { display: grid; grid-template-columns: 1fr 200px 180px auto; gap: .5rem; margin-bottom: 1rem; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: .5rem; text-align: left; }
.pagination { margin-top: 1rem; }
.pagination a, .pagination span { margin-right: .5rem; }
button { padding: .4rem .6rem; }
</style>
</head>
<body>
<div class="container">
	<h1><?php echo h('Catalog'); ?></h1>
	<?php if ($error): ?><div class="flash error"><?php echo h($error); ?></div><?php endif; ?>
	<?php if ($success): ?><div class="flash success"><?php echo h($success); ?></div><?php endif; ?>

	<form class="search" method="get" action="">
		<input type="text" name="q" placeholder="<?php echo h('Keyword'); ?>" value="<?php echo h($q); ?>">
		<select name="category">
			<option value=""><?php echo h('All categories'); ?></option>
			<?php foreach ($categories as $cat): ?>
				<option value="<?php echo h($cat); ?>" <?php echo $category===$cat?'selected':''; ?>><?php echo h($cat); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="avail">
			<option value="all" <?php echo $avail==='all'?'selected':''; ?>><?php echo h('All'); ?></option>
			<option value="available" <?php echo $avail==='available'?'selected':''; ?>><?php echo h('Available only'); ?></option>
		</select>
		<button type="submit"><?php echo h('Search'); ?></button>
	</form>

	<table>
		<thead>
			<tr>
				<th><?php echo h('Title'); ?></th>
				<th><?php echo h('Author'); ?></th>
				<th><?php echo h('Category'); ?></th>
				<th><?php echo h('Availability'); ?></th>
				<th><?php echo h('Action'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($books)): ?>
			<tr><td colspan="5"><?php echo h('No results found'); ?></td></tr>
		<?php else: ?>
			<?php foreach ($books as $b): ?>
			<tr>
				<td>
					<?php if (!empty($b['cover_url'])): ?>
						<div><img src="<?php echo h($b['cover_url']); ?>" alt="<?php echo h($b['title']); ?>" style="width:80px;height:auto;"></div>
					<?php else: ?>
						<div><?php echo h('No image'); ?></div>
					<?php endif; ?>
					<div><?php echo h($b['title']); ?></div>
				</td>
				<td><?php echo h($b['author']); ?></td>
				<td><?php echo h($b['category']); ?></td>
				<td><?php echo $b['availability'] ? h('Available') : h('Borrowed'); ?></td>
				<td>
					<?php if ((int)$b['availability'] === 1): ?>
						<form method="post" action="borrow.php" style="display:inline">
						<?php echo csrf_field(); ?>
							<input type="hidden" name="book_id" value="<?php echo h((string)$b['book_id']); ?>">
							<button type="submit"><?php echo h('Borrow'); ?></button>
						</form>
					<?php else: ?>
						<span><?php echo h('—'); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>

	<div class="pagination">
		<?php if ($page > 1): ?>
			<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page-1)); ?>"><?php echo h('Prev'); ?></a>
		<?php else: ?><span><?php echo h('Prev'); ?></span><?php endif; ?>
		<span><?php echo h("Page {$page} of {$totalPages}"); ?></span>
		<?php if ($page < $totalPages): ?>
			<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page+1)); ?>"><?php echo h('Next'); ?></a>
		<?php else: ?><span><?php echo h('Next'); ?></span><?php endif; ?>
	</div>

</div>
</body>
</html>


