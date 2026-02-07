<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/headers.php';
require_once __DIR__ . '/../../src/security.php';

require_login();
check_session_timeout();

// Gather all security status information
$security_status = [
    'php_version' => phpversion(),
    'php_extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_sqlite' => extension_loaded('pdo_sqlite'),
        'filter' => extension_loaded('filter'),
        'hash' => extension_loaded('hash'),
    ],
    'server_info' => [
        'os' => php_uname(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    ],
];

// Database checks
try {
    $pdo = get_db();
    $security_status['database'] = validate_db_connection($pdo);
    
    $db_path = __DIR__ . '/../../storage/blog.sqlite';
    $security_status['db_writable'] = is_db_writable($db_path);
} catch (Exception $e) {
    $security_status['database'] = ['error' => htmlspecialchars($e->getMessage())];
}

// File system checks
$security_status['filesystem'] = [
    'storage_dir' => __DIR__ . '/../../storage',
    'storage_writable' => is_writable(__DIR__ . '/../../storage'),
    'logs_dir' => __DIR__ . '/../../storage/logs',
    'logs_writable' => is_dir(__DIR__ . '/../../storage/logs') ? is_writable(__DIR__ . '/../../storage/logs') : 'Not created yet',
];

// Session settings check
$security_status['session'] = [
    'cookie_httponly' => (bool)(ini_get('session.cookie_httponly')),
    'cookie_secure' => (bool)(ini_get('session.cookie_secure')),
    'cookie_samesite' => ini_get('session.cookie_samesite'),
];

// PHP security settings
$security_status['php_security'] = [
    'display_errors' => ini_get('display_errors'),
    'log_errors' => ini_get('log_errors'),
    'expose_php' => ini_get('expose_php'),
];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Security Status - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .status-badge-ok { background-color: #198754; }
    .status-badge-warning { background-color: #ffc107; color: #000; }
    .status-badge-error { background-color: #dc3545; }
    .tech-table td { font-family: monospace; word-break: break-all; }
  </style>
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
            <a class="nav-link" href="posts/list.php">Posts</a>
          </li>
          <li class="nav-item">
            <span class="nav-link">Welcome, <strong><?php echo esc_html($_SESSION['admin_username']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../logout.php">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <h1 class="mb-4">Security Status Check</h1>

    <!-- Database Status -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Database</h5>
      </div>
      <div class="card-body">
        <?php if (isset($security_status['database']['error'])): ?>
          <div class="alert alert-danger">Database Error: <?php echo esc_html($security_status['database']['error']); ?></div>
        <?php else: ?>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Connected:</strong> <span class="badge <?php echo $security_status['database']['connected'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['database']['connected'] ? 'Yes' : 'No'; ?>
              </span></p>
              <p><strong>Posts Table:</strong> <span class="badge <?php echo $security_status['database']['posts_table'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['database']['posts_table'] ? 'OK' : 'Missing'; ?>
              </span></p>
            </div>
            <div class="col-md-6">
              <p><strong>Database File Writable:</strong> 
                <span class="badge <?php echo $security_status['db_writable']['overall_ok'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                  <?php echo $security_status['db_writable']['overall_ok'] ? 'Yes' : 'No'; ?>
                </span></p>
              <p><small class="text-muted"><?php echo esc_html($security_status['db_writable']['path']); ?></small></p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- File System -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">File System</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <tbody>
            <tr>
              <td><strong>Storage Directory</strong></td>
              <td><code><?php echo esc_html($security_status['filesystem']['storage_dir']); ?></code></td>
              <td><span class="badge <?php echo $security_status['filesystem']['storage_writable'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['filesystem']['storage_writable'] ? 'Writable' : 'Read-only'; ?>
              </span></td>
            </tr>
            <tr>
              <td><strong>Logs Directory</strong></td>
              <td><code><?php echo esc_html($security_status['filesystem']['logs_dir']); ?></code></td>
              <td><span class="badge <?php echo (is_string($security_status['filesystem']['logs_writable']) && $security_status['filesystem']['logs_writable'] === true) ? 'status-badge-ok' : 'status-badge-warning'; ?>">
                <?php echo $security_status['filesystem']['logs_writable'] === true ? 'Writable' : (is_string($security_status['filesystem']['logs_writable']) ? esc_html($security_status['filesystem']['logs_writable']) : 'Writable'); ?>
              </span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Session Security -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Session Security</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <tbody>
            <tr>
              <td><strong>HTTPOnly Cookies</strong></td>
              <td><span class="badge <?php echo $security_status['session']['cookie_httponly'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['session']['cookie_httponly'] ? 'Enabled' : 'Disabled'; ?>
              </span></td>
              <td><small class="text-muted">Prevents JavaScript access to session cookies (XSS)</small></td>
            </tr>
            <tr>
              <td><strong>Secure Cookies</strong></td>
              <td><span class="badge status-badge-warning">
                <?php echo $security_status['session']['cookie_secure'] ? 'Enabled' : 'Disabled'; ?>
              </span></td>
              <td><small class="text-muted">Enable in production with HTTPS</small></td>
            </tr>
            <tr>
              <td><strong>SameSite Policy</strong></td>
              <td><span class="badge <?php echo $security_status['session']['cookie_samesite'] ? 'status-badge-ok' : 'status-badge-warning'; ?>">
                <?php echo esc_html($security_status['session']['cookie_samesite'] ?: 'Not set'); ?>
              </span></td>
              <td><small class="text-muted">CSRF protection</small></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PHP Configuration -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">PHP Security Settings</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <tbody>
            <tr>
              <td><strong>Display Errors</strong></td>
              <td><span class="badge <?php echo !$security_status['php_security']['display_errors'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['php_security']['display_errors'] ? 'On' : 'Off'; ?>
              </span></td>
              <td><small class="text-muted">Should be OFF in production</small></td>
            </tr>
            <tr>
              <td><strong>Log Errors</strong></td>
              <td><span class="badge <?php echo $security_status['php_security']['log_errors'] ? 'status-badge-ok' : 'status-badge-warning'; ?>">
                <?php echo $security_status['php_security']['log_errors'] ? 'On' : 'Off'; ?>
              </span></td>
              <td><small class="text-muted">Errors logged but not displayed to users</small></td>
            </tr>
            <tr>
              <td><strong>Expose PHP Header</strong></td>
              <td><span class="badge <?php echo !$security_status['php_security']['expose_php'] ? 'status-badge-ok' : 'status-badge-error'; ?>">
                <?php echo $security_status['php_security']['expose_php'] ? 'On' : 'Off'; ?>
              </span></td>
              <td><small class="text-muted">Should be OFF to avoid leaking PHP version</small></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Installed Extensions -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">PHP Extensions</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <tbody>
            <?php foreach ($security_status['php_extensions'] as $ext => $loaded): ?>
              <tr>
                <td><code><?php echo htmlspecialchars($ext); ?></code></td>
                <td><span class="badge <?php echo $loaded ? 'status-badge-ok' : 'status-badge-error'; ?>">
                  <?php echo $loaded ? 'Loaded' : 'Not loaded'; ?>
                </span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- System Info -->
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">System Information</h5>
      </div>
      <div class="card-body tech-table">
        <table class="table table-sm">
          <tbody>
            <tr>
              <td><strong>PHP Version</strong></td>
              <td><?php echo esc_html($security_status['php_version']); ?></td>
            </tr>
            <tr>
              <td><strong>Operating System</strong></td>
              <td><?php echo esc_html($security_status['server_info']['os']); ?></td>
            </tr>
            <tr>
              <td><strong>Server Software</strong></td>
              <td><?php echo esc_html($security_status['server_info']['server_software']); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="alert alert-info">
      <strong>Note:</strong> This status page is for administrators only. All sensitive information is accessed securely. Review these settings regularly to ensure your application remains secure.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
