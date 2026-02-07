<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/security.php';

init_session();

$error = '';
$redirect = $_GET['redirect'] ?? '/admin/';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        } elseif (login_user($username, $password)) {
            // Successful login
            header('Location: ' . ($redirect ?: '/admin/'));
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

// If already logged in, redirect to admin dashboard
if (is_logged_in()) {
    header('Location: /admin/');
    exit;
}

$csrf_token = get_csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - Simple Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f5f5f5; }
    .login-container { max-width: 400px; width: 100%; }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="card shadow">
      <div class="card-body p-5">
        <h1 class="h3 mb-4 text-center">Blog Admin</h1>
        
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo esc_html($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form method="POST">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required placeholder="admin">
          </div>
          
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="password123">
          </div>
          
          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?php echo esc_attr($csrf_token); ?>">
          
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="mt-4 p-3 bg-light rounded">
          <small class="text-muted">
            <strong>Demo credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>password123</code>
          </small>
        </div>
      </div>
    </div>
    
    <div class="text-center mt-3">
      <a href="/" class="text-muted">Back to Blog</a>
    </div>
  </div>
</body>
</html>
