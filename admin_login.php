<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');

if (!$email || !$pass) {
    header("Location: admin_login.html?error=1");
    exit;
}

csrf_verify();

$stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($admin && password_verify($pass, $admin['password'])) {
    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    header("Location: admin_dashboard.php");
} else {
    header("Location: admin_login.html?error=1");
}
exit;
