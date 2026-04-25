<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_id'])) die("Unauthorized");

$id = (int)$_GET['id'];
$status = $_GET['s'];

if (!in_array($status,['accepted','rejected'])) die("Invalid status");

$stmt = $conn->prepare(
  "UPDATE cloth_donations SET status=? WHERE id=?"
);
$stmt->bind_param("si",$status,$id);
$stmt->execute();

header("Location: admin_dashboard.php");
exit;
