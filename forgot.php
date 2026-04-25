<?php
include("db.php");
include("mail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: forgot.html?error=1");
        exit;
    }

    $token = bin2hex(random_bytes(32));

    // Use prepared statement — was SQL injection
    $stmt = $conn->prepare("INSERT INTO password_resets(email,token) VALUES(?,?) ON DUPLICATE KEY UPDATE token=?, created_at=NOW()");
    $stmt->bind_param("sss", $email, $token, $token);
    $stmt->execute();

    $resetLink = "http://localhost/adhaar/reset.php?token=" . $token;

    sendMail(
        $email,
        "Reset Your Password – Adhaar",
        "<p>Click the link below to reset your password:</p><p><a href='$resetLink'>$resetLink</a></p><p>This link expires in 1 hour.</p>"
    );

    header("Location: forgot.html?sent=1");
    exit;
}

header("Location: forgot.html");
exit;
