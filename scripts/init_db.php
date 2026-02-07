php scripts/init_db.phpphp scripts/init_db.php<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db();

$schema = <<<SQL
CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    content TEXT NOT NULL,
    published_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL;

$pdo->exec($schema);

$count = $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
if ($count == 0) {
    $stmt = $pdo->prepare('INSERT INTO posts (title, slug, content, published_at) VALUES (?, ?, ?, ?)');
    $stmt->execute(['Hello World', 'hello-world', 'This is the first post created by the init script.', date('Y-m-d H:i:s')]);
    $stmt->execute(['Second Post', 'second-post', "Another sample post to demonstrate the list.", date('Y-m-d H:i:s')]);
    echo "Inserted sample posts\n";
} else {
    echo "Posts table already has data\n";
}

echo "Database initialized at " . realpath(__DIR__ . '/../storage/blog.sqlite') . PHP_EOL;
