
<?php
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = null;
if ($q) {
	require_once __DIR__ . '/../controllers/SearchController.php';
	$results = SearchController::search($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Search Results for "<?= htmlspecialchars($q) ?>"</title>
	<link rel="icon" type="image/png" href="../../assets/images/logos/logo-icon.png">
	<link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
	<?php include '../include/topbar.php'; ?>
	<div class="container py-4">
		<h2>Search Results for "<?= htmlspecialchars($q) ?>"</h2>
		<?php if (!$q): ?>
			<div class="alert alert-warning">No search query provided.</div>
		<?php elseif (!$results): ?>
			<div class="alert alert-danger">No results found.</div>
		<?php else: ?>
			<div class="row">
				<div class="col-md-4">
					<h4>Users</h4>
					<?php if (empty($results['users'])): ?>
						<p class="text-muted">No users found.</p>
					<?php else: ?>
						<ul class="list-group">
						<?php foreach ($results['users'] as $user): ?>
							<li class="list-group-item">
								<img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
								<?= htmlspecialchars($user['full_name']) ?> <br>
								<small><?= htmlspecialchars($user['email']) ?></small>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<div class="col-md-4">
					<h4>Pets</h4>
					<?php if (empty($results['pets'])): ?>
						<p class="text-muted">No pets found.</p>
					<?php else: ?>
						<ul class="list-group">
						<?php foreach ($results['pets'] as $pet): ?>
							<li class="list-group-item">
								<span class="rounded-circle me-2" style="display:inline-block;width:32px;height:32px;background:#eee;text-align:center;line-height:32px;">🐾</span>
								<?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['breed']) ?>)<br>
								<small><?= htmlspecialchars($pet['description']) ?></small>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<div class="col-md-4">
					<h4>Posts</h4>
					<?php if (empty($results['posts'])): ?>
						<p class="text-muted">No posts found.</p>
					<?php else: ?>
						<ul class="list-group">
						<?php foreach ($results['posts'] as $post): ?>
							<li class="list-group-item">
								<span class="rounded-circle me-2" style="display:inline-block;width:32px;height:32px;background:#eee;text-align:center;line-height:32px;">📝</span>
								<small><?= htmlspecialchars($post['content']) ?></small>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>
