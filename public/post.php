<?php
require_once __DIR__ . '/../src/headers.php';
require_once __DIR__ . '/../src/security.php';
require_once __DIR__ . '/../src/db.php';
set_content_type_html();
set_security_headers();

$post = null;
$error = null;

// Validate and fetch post by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Ensure ID is numeric to prevent SQL injection
    $id_int = validate_int($id);
    if ($id_int === false) {
        $error = 'Invalid post ID.';
    } else {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare('SELECT id, title, slug, content, published_at, created_at FROM posts WHERE id = ? AND published_at IS NOT NULL');
            $stmt->execute([$id_int]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$post) {
                $error = 'Post not found.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . esc_html($e->getMessage());
        }
    }
} else {
    $error = 'No post ID provided.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $post ? htmlspecialchars($post['title']) : 'Post'; ?> - Simple Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
      <a class="navbar-brand" href="/">Simple Blog</a>
    </div>
  </nav>

  <div class="container">
    <?php if ($error): ?>
      <div class="alert alert-danger">
        <p><?php echo $error; ?></p>
        <a href="/" class="btn btn-secondary btn-sm">Back to Home</a>
      </div>
    <?php elseif ($post): ?>
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <article>
            <h1 class="mb-3"><?php echo esc_html($post['title']); ?></h1>
            <p class="text-muted mb-4">
              Published <?php echo esc_html($post['published_at']); ?>
            </p>
            <div class="border-top border-bottom py-4 mb-4">
              <?php echo nl2br(esc_html($post['content'])); ?>
            </div>
            <a href="/" class="btn btn-secondary">Back to Home</a>
          </article>
        </div>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
