<?php
require_once "db.php";
session_start();

if($_SESSION['role']!=='admin') die("Unauthorized");

$id=$_GET['id'];
$status=$_GET['s'];

$stmt=$conn->prepare(
 "UPDATE donation_tracking SET status=? WHERE id=?"
);
$stmt->bind_param("si",$status,$id);
$stmt->execute();

header("Location: admin_dashboard.php");
