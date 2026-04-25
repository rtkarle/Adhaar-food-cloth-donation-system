<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    error_log("DB connection failed: " . mysqli_connect_error());
    die("Service temporarily unavailable. Please try again later.");
}

mysqli_set_charset($conn, 'utf8mb4');

/* ── CSRF helpers ─────────────────────────────── */

/**
 * Generate (or reuse) a CSRF token for this session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify the submitted CSRF token. Kills the request on failure.
 */
function csrf_verify(): void {
    $submitted = trim($_POST['csrf_token'] ?? '');
    if (!$submitted || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        die("Invalid request. Please go back and try again.");
    }
}
