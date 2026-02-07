<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/db.php';
require_once __DIR__ . '/../../../src/flash.php';

require_login();
check_session_timeout();

$errors = [];
$form_data = [
    'title' => '',
    'slug' => '',
    'content' => '',
    'publish' => '0',
];

$pdo = get_db();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $form_data['title'] = trim($_POST['title'] ?? '');
        $form_data['slug'] = trim($_POST['slug'] ?? '');
        $form_data['content'] = trim($_POST['content'] ?? '');
        $form_data['publish'] = $_POST['publish'] ?? '0';
        
        // Validation
        if (empty($form_data['title'])) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($form_data['slug'])) {
            $errors[] = 'Slug is required.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $form_data['slug'])) {
            $errors[] = 'Slug can only contain lowercase letters, numbers, and hyphens.';
        }
        
        if (empty($form_data['content'])) {
            $errors[] = 'Content is required.';
        }
        
        // Check slug uniqueness with prepared statement
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id FROM posts WHERE slug = ?');
            $stmt->execute([$form_data['slug']]);
            if ($stmt->fetch()) {
                $errors[] = 'This slug is already in use. Please choose another.';
            }
        }
        
        // Insert post if no errors
        if (!$errors) {
            try {
                $published_at = $form_data['publish'] === '1' ? date('Y-m-d H:i:s') : null;
                
                $stmt = $pdo->prepare('INSERT INTO posts (title, slug, content, published_at) VALUES (?, ?, ?, ?)');
                $stmt->execute([
                    $form_data['title'],
                    $form_data['slug'],
                    $form_data['content'],
                    $published_at,
                ]);
                
                set_flash('Post created successfully!', 'success');
                header('Location: list.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

$csrf_token = get_csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Post - Admin</title>
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
            <a class="nav-link" href="list.php">Posts</a>
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
    <div class="row">
      <div class="col-lg-8 offset-lg-2">
        <h1 class="mb-4">Create New Post</h1>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Errors:</strong>
            <ul class="mb-0 mt-2">
              <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="title" class="form-label">Post Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($form_data['title']); ?>" required>
            <small class="text-muted">Enter a descriptive title for your post.</small>
          </div>

          <div class="mb-3">
            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($form_data['slug']); ?>" placeholder="e.g., my-first-post" required>
            <small class="text-muted">URL-friendly version of the title. Use lowercase letters, numbers, and hyphens only.</small>
          </div>

          <div class="mb-3">
            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
            <textarea class="form-control" id="content" name="content" rows="12" placeholder="Write your post content here..." required><?php echo htmlspecialchars($form_data['content']); ?></textarea>
            <small class="text-muted">Markdown-style formatting is not processed; plain text is displayed as-is.</small>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="publish" name="publish" value="1" <?php echo $form_data['publish'] === '1' ? 'checked' : ''; ?>>
            <label class="form-check-label" for="publish">
              Publish immediately
            </label>
            <small class="text-muted d-block mt-1">If unchecked, this post will be saved as a draft.</small>
          </div>

          <!-- CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

          <div class="mb-3">
            <button type="submit" class="btn btn-primary">Create Post</button>
            <a href="list.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
