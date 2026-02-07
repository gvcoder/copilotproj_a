<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/security.php';

// Require login and check session timeout
require_login();
if (!check_session_timeout()) {
    header('Location: /admin/login.php?error=Session expired');
    exit;
}

$pdo = get_db();
$post_count = $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - Simple Blog</title>
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
            <span class="nav-link">Welcome, <strong><?php echo esc_html($_SESSION['admin_username']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/admin/logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row mb-4">
      <div class="col-md-8">
        <h1>Admin Dashboard</h1>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="/" class="btn btn-outline-secondary">View Blog</a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Total Posts</h5>
            <p class="display-4"><?php echo (int)$post_count; ?></p>
            <a href="posts/list.php" class="btn btn-primary">Manage Posts</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Session Info</h5>
            <p class="text-muted">
              Logged in: <?php echo esc_html($_SESSION['admin_username']); ?><br>
              Session ID: <code><?php echo esc_html(substr(session_id(), 0, 12)); ?>...</code><br>
              Login time: <?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?><br>
              Session expires in: 1 hour
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">⚙️ Security Status</h5>
            <p class="text-muted">Check database, file permissions, and security settings.</p>
            <a href="security-check.php" class="btn btn-warning">View Status</a>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-12">
        <div class="alert alert-info">
          <strong>Note:</strong> This is a demo admin dashboard. Full post management features (create, edit, delete) can be added by creating further admin pages.
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
