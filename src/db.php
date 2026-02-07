<?php
function get_db(): PDO
{
    $path = __DIR__ . '/../storage/blog.sqlite';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $pdo->exec('PRAGMA busy_timeout = 5000;');
    return $pdo;
}
