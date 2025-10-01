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
include __DIR__ . '/includes/header.php';
?>

<main class="section">
	<div class="container">
		<div class="catalog-header">
			<div>
				<h1 class="catalog-title"><?php echo h('Book Catalog'); ?></h1>
				<p class="catalog-subtitle"><?php echo h('Discover and borrow from our collection'); ?></p>
			</div>
		</div>
		
		<?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>
		<?php if ($success): ?><div class="alert alert-success"><?php echo h($success); ?></div><?php endif; ?>

		<div class="search-filters">
			<form method="get" action="">
				<div class="form">
					<div class="search-input">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
						<input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search by title, author, or category">
					</div>
					<div>
						<label><?php echo h('Category'); ?></label>
						<select name="category">
							<option value=""><?php echo h('All Categories'); ?></option>
							<?php foreach ($categories as $cat): ?>
								<option value="<?php echo h($cat); ?>" <?php echo $category===$cat?'selected':''; ?>><?php echo h($cat); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<label><?php echo h('Availability'); ?></label>
						<select name="avail">
							<option value="all" <?php echo $avail==='all'?'selected':''; ?>><?php echo h('All Books'); ?></option>
							<option value="available" <?php echo $avail==='available'?'selected':''; ?>><?php echo h('Available Only'); ?></option>
						</select>
					</div>
					<button class="btn btn-accent" type="submit">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
						<?php echo h('Search'); ?>
					</button>
				</div>
			</form>
		</div>

		<?php if (empty($books)): ?>
			<div class="empty-card">
				<svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; color: var(--color-text-muted);">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
				<h3><?php echo h('No books found'); ?></h3>
				<p><?php echo h('Try adjusting your search criteria or browse all books.'); ?></p>
			</div>
		<?php else: ?>
			<div class="card-grid">
				<?php foreach ($books as $b): ?>
					<div class="book-card">
						<div class="cover">
							<?php if (!empty($b['cover_url'])): ?>
								<img src="<?php echo h($b['cover_url']); ?>" alt="<?php echo h($b['title']); ?>">
							<?php else: ?>
								<img src="https://via.placeholder.com/150x200?text=No+Cover" alt="<?php echo h($b['title']); ?>">
							<?php endif; ?>
						</div>
						<div class="book-card-content">
							<h3><?php echo h($b['title']); ?></h3>
							<p class="author"><?php echo h($b['author']); ?></p>
							<span class="category"><?php echo h($b['category']); ?></span>
							<div class="availability">
								<?php if ((int)$b['availability'] === 1): ?>
									<span class="badge success"><?php echo h('Available'); ?></span>
								<?php else: ?>
									<span class="badge warn"><?php echo h('Borrowed'); ?></span>
								<?php endif; ?>
							</div>
                            <?php if ((int)$b['availability'] === 1): ?>
                                <form method="post" action="<?php echo h($user ? 'borrow.php' : 'login.php'); ?>">
                                    <?php if ($user): ?><?php echo csrf_field(); ?><?php endif; ?>
                                    <input type="hidden" name="book_id" value="<?php echo h((string)$b['book_id']); ?>">
                                    <button class="btn btn-accent" type="submit">
										<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"></path>
											<path d="M4 4v15"></path>
											<path d="M8 4v15"></path>
											<path d="M12 4v15"></path>
										</svg>
										<?php echo h('Borrow Book'); ?>
									</button>
								</form>
							<?php else: ?>
								<button class="btn btn-muted" disabled>
									<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<path d="M9 12l2 2 4-4"></path>
										<circle cx="12" cy="12" r="10"></circle>
									</svg>
									<?php echo h('Currently Borrowed'); ?>
								</button>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ($totalPages > 1): ?>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page-1)); ?>" class="btn btn-outline">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="15,18 9,12 15,6"></polyline>
						</svg>
						<?php echo h('Previous'); ?>
					</a>
				<?php endif; ?>
				
				<span class="pagination-info"><?php echo h("Page {$page} of {$totalPages}"); ?></span>
				
				<?php if ($page < $totalPages): ?>
					<a href="?<?php echo h($baseQuery . ($baseQuery? '&':'') . 'page=' . ($page+1)); ?>" class="btn btn-outline">
						<?php echo h('Next'); ?>
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="9,18 15,12 9,6"></polyline>
						</svg>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>


