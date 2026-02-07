<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/db.php';
require_once __DIR__ . '/../../../src/flash.php';

require_login();
check_session_timeout();

// Get and validate post ID
$post_id = $_GET['id'] ?? null;
$confirm = $_GET['confirm'] ?? '0';

if (!$post_id || !is_numeric($post_id)) {
    set_flash('Invalid post ID.', 'error');
    header('Location: list.php');
    exit;
}

$post_id = (int)$post_id;

// Require explicit confirmation
if ($confirm !== '1') {
    set_flash('Delete action cancelled.', 'warning');
    header('Location: list.php');
    exit;
}

$pdo = get_db();

// Verify post exists
$stmt = $pdo->prepare('SELECT id FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
if (!$stmt->fetch()) {
    set_flash('Post not found.', 'error');
    header('Location: list.php');
    exit;
}

// Delete post with prepared statement
try {
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$post_id]);
    
    set_flash('Post deleted successfully!', 'success');
} catch (Exception $e) {
    set_flash('Error deleting post: ' . htmlspecialchars($e->getMessage()), 'error');
}

header('Location: list.php');
exit;
