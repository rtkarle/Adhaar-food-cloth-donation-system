<?php
require "db.php";
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.html");
    exit;
}

// CSRF check
csrf_verify();

$donor_email = $_SESSION['user_email'];

if (!isset($_FILES['image'])) {
    die("No image uploaded");
}

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowed = ['image/jpeg','image/png','image/webp','image/gif'];
if (!in_array($_FILES['image']['type'], $allowed)) {
    die("Invalid image type.");
}

$imageName = time() . "_" . basename($_FILES['image']['name']);
$targetPath = $uploadDir . $imageName;
$dbPath = "uploads/" . $imageName;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
    die("Image upload failed");
}

$prepared_at    = trim($_POST['prepared_at'] ?? '');
$safe_hours     = (int)($_POST['safe_hours'] ?? 0);
$quantity       = (int)($_POST['quantity'] ?? 0);
$priority       = trim($_POST['priority'] ?? 'medium');
$pickup_address = trim($_POST['pickup_address'] ?? '');
$contact        = trim($_POST['contact'] ?? '');

if (!$prepared_at || !$safe_hours || !$quantity || !$pickup_address || !$contact) {
    die("All fields are required.");
}

$stmt = $conn->prepare(
    "INSERT INTO food_donations
     (donor_email, food_time, safe_hours, quantity, priority, pickup_address, contact, image, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
);
$stmt->bind_param(
    "ssiissss",
    $donor_email,
    $prepared_at,
    $safe_hours,
    $quantity,
    $priority,
    $pickup_address,
    $contact,
    $dbPath
);

if (!$stmt->execute()) {
    die("DB Error: " . $stmt->error);
}

header("Location: donor_dashboard.php?success=food");
exit;
