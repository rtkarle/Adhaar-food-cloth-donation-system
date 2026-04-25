<?php
include("db.php");
if (session_status() === PHP_SESSION_NONE) session_start();

$token = trim($_GET['token'] ?? '');
if (!$token) {
    header("Location: login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Use prepared statement — was SQL injection
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        die("Invalid or expired reset link.");
    }

    $email = $row['email'];

    $upd = $conn->prepare("UPDATE register SET password=? WHERE email=?");
    $upd->bind_param("ss", $pass, $email);
    $upd->execute();

    $del = $conn->prepare("DELETE FROM password_resets WHERE email=?");
    $del->bind_param("s", $email);
    $del->execute();

    header("Location: login.html?reset=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password | Adhaar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="auth-container">
  <div class="box">
    <h2>Reset Password</h2>
    <p class="subtitle">Enter your new password below.</p>
    <form method="POST">
      <div class="input-group">
        <input type="password" name="password" placeholder="New Password" required minlength="8">
      </div>
      <button class="primary-btn" type="submit">Reset Password</button>
    </form>
  </div>
</div>
</body>
</html>
