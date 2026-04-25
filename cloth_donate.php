<?php
require "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

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

$purchase_time  = trim($_POST['purchase_time'] ?? '');
$quantity       = (int)($_POST['quantity'] ?? 0);
$cloth_type     = trim($_POST['cloth_type'] ?? '');
$condition_type = trim($_POST['condition_type'] ?? 'good');
$is_clean       = (int)(!empty($_POST['is_clean']));
$pickup_address = trim($_POST['pickup_address'] ?? '');
$contact        = trim($_POST['contact'] ?? '');

if (!$quantity || !$cloth_type || !$pickup_address || !$contact) {
    die("All fields are required.");
}

$stmt = $conn->prepare(
    "INSERT INTO cloth_donations
     (donor_email, purchase_time, quantity, cloth_type, condition_type, is_clean, pickup_address, contact, image, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
);
$stmt->bind_param(
    "ssissssss",
    $donor_email,
    $purchase_time,
    $quantity,
    $cloth_type,
    $condition_type,
    $is_clean,
    $pickup_address,
    $contact,
    $dbPath
);

if (!$stmt->execute()) {
    die("DB Error: " . $stmt->error);
}

header("Location: donor_dashboard.php?success=cloth");
exit;
