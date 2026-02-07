<?php
require_once __DIR__ . '/../../src/auth.php';

init_session();
logout_user();

// Redirect to login page with success message
header('Location: /admin/login.php?success=1');
exit;
