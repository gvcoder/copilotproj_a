<?php

// Display flash message if one exists, then clear it
function display_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    
    return $flash;
}

// Set a flash message
function set_flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type, // 'success', 'error', 'warning', 'info'
    ];
}

// Render flash message HTML
function render_flash_html(array $flash): string
{
    $html = '<div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show" role="alert">' . PHP_EOL;
    $html .= htmlspecialchars($flash['message']) . PHP_EOL;
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' . PHP_EOL;
    $html .= '</div>' . PHP_EOL;
    
    return $html;
}
