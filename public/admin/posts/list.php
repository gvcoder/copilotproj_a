<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/db.php';
require_once __DIR__ . '/../../../src/flash.php';

require_login();
check_session_timeout();

$pdo = get_db();
$posts = $pdo->query('SELECT id, title, slug, published_at, created_at FROM posts ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$flash = display_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Posts - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">Simple Blog Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="../">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="../posts/list.php">Posts</a>
          </li>
          <li class="nav-item">
            <span class="nav-link">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row mb-4">
      <div class="col-md-8">
        <h1>Blog Posts</h1>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="create.php" class="btn btn-primary">+ New Post</a>
      </div>
    </div>

    <?php if ($flash): ?>
      <?php echo render_flash_html($flash); ?>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
      <div class="alert alert-info">
        No posts yet. <a href="create.php">Create one now</a>.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>Title</th>
              <th>Slug</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posts as $post): ?>
              <tr>
                <td><?php echo htmlspecialchars($post['title']); ?></td>
                <td><code><?php echo htmlspecialchars($post['slug']); ?></code></td>
                <td>
                  <?php if ($post['published_at']): ?>
                    <span class="badge bg-success">Published</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Draft</span>
                  <?php endif; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                <td>
                  <a href="edit.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="delete.php?id=<?php echo (int)$post['id']; ?>&confirm=1" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
