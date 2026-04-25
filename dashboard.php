<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once "db.php";

if (!isset($_SESSION['user_email'])) {
    header("Location: login.html");
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role === "donor") {
    include "donor_dashboard.php";
} elseif ($role === "volunteer") {
    // volunteer_dashboard.php when created
    echo "<p>Volunteer dashboard coming soon.</p>";
} elseif ($role === "admin") {
    include "admin_dashboard.php";
} else {
    session_destroy();
    header("Location: login.html");
    exit;
}
