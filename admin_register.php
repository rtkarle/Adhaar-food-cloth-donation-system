<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

csrf_verify();

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');

if (!$name || !$email || !$pass) {
    header("Location: admin_register.html?error=missing");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: admin_register.html?error=email");
    exit;
}

// Check duplicate
$check = $conn->prepare("SELECT id FROM admins WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: admin_register.html?error=exists");
    exit;
}

$hashed = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO admins(name,email,password) VALUES(?,?,?)");
$stmt->bind_param("sss", $name, $email, $hashed);

if ($stmt->execute()) {
    header("Location: admin_login.html?registered=1");
} else {
    header("Location: admin_register.html?error=db");
}
exit;
