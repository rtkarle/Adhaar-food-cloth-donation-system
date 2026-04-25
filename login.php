<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (!$email || !$pass) {
        header("Location: login.html?error=1");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM register WHERE email=? AND verified=1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = $user['role'];

            if ($user['role'] === 'donor') {
                header("Location: donor_dashboard.php");
            } elseif ($user['role'] === 'volunteer') {
                header("Location: volunteer_dashboard.php");
            } else {
                header("Location: donor_dashboard.php");
            }
            exit;
        }
    }

    header("Location: login.html?error=1");
    exit;
}

header("Location: login.html");
exit;
