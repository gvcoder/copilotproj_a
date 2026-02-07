<?php
require_once __DIR__ . '/../src/headers.php';
require_once __DIR__ . '/../src/security.php';
require_once __DIR__ . '/../src/db.php';
set_content_type_html();
set_security_headers();
$pdo = get_db();
$posts = $pdo->query('SELECT id, title, slug, content, published_at, created_at FROM posts WHERE published_at IS NOT NULL ORDER BY published_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Simple Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
      <a class="navbar-brand" href="/">Simple Blog</a>
    </div>
  </nav>

  <div class="container">
    <?php if (empty($posts)): ?>
      <div class="alert alert-info">No posts yet. Run <code>php scripts/init_db.php</code> to create sample posts.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($posts as $post): ?>
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">
                  <a href="post.php?id=<?php echo (int)$post['id']; ?>" class="text-decoration-none">
                    <?php echo esc_html($post['title']); ?>
                  </a>
                </h5>
                <p class="card-text"><?php echo esc_html(substr($post['content'], 0, 300)); ?></p>
                <a href="post.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-primary">Read</a>
                <small class="text-muted float-end"><?php echo esc_html($post['published_at']); ?></small>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
