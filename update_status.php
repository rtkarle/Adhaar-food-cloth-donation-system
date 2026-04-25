<?php
require_once "db.php";
require_once "mail.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$isAdmin     = isset($_SESSION['admin_id']);
$isVolunteer = isset($_SESSION['user_email']);

if (!$isAdmin && !$isVolunteer) {
    header("Location: login.html");
    exit;
}

// CSRF check
csrf_verify();

$id     = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$table  = trim($_POST['table'] ?? 'food_donations');

$allowed_statuses = ['accepted','rejected','scheduled','out_for_pickup','picked_up','delivered'];
$allowed_tables   = ['food_donations','cloth_donations'];

if (!$id || !in_array($status, $allowed_statuses) || !in_array($table, $allowed_tables)) {
    die("Invalid request.");
}

// Fetch donor email + type before updating
$row = $conn->query("SELECT donor_email FROM $table WHERE id=$id")->fetch_assoc();
$donorEmail = $row['donor_email'] ?? '';
$donationType = ($table === 'food_donations') ? 'food' : 'cloth';

$details = [];

if ($status === 'scheduled') {
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $vol_email   = trim($_POST['volunteer_email'] ?? '');
    $stmt = $conn->prepare("UPDATE $table SET status=?, pickup_date=?, pickup_time=?, volunteer_email=? WHERE id=?");
    $stmt->bind_param("ssssi", $status, $pickup_date, $pickup_time, $vol_email, $id);
    $details = ['pickup_date' => $pickup_date, 'pickup_time' => $pickup_time, 'volunteer_email' => $vol_email];
} else {
    $stmt = $conn->prepare("UPDATE $table SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
}

if (!$stmt->execute()) {
    die("DB Error: " . $stmt->error);
}

// Send email notification to donor
if ($donorEmail) {
    sendStatusNotification($donorEmail, $donationType, $status, $details);
}

// Smart redirect
$ref = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($ref, 'distribution_system') !== false) {
    header("Location: distribution_system.php");
} elseif ($isAdmin) {
    header("Location: admin_dashboard.php");
} else {
    header("Location: volunteer_dashboard.php");
}
exit;
