<?php
/**
 * Shared public header — include at top of every public HTML page.
 * Usage: <?php $pageTitle = 'About'; $activeNav = 'about'; require 'includes/header.php'; ?>
 */
$pageTitle  = $pageTitle  ?? 'Adhaar – The SoulServe';
$activeNav  = $activeNav  ?? '';
$extraCss   = $extraCss   ?? '';   // e.g. '<link rel="stylesheet" href="about.css">'
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?> | Adhaar – The SoulServe</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Adhaar – The SoulServe connects surplus food and clothing to communities in need.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<?= $extraCss ?>
</head>
<body>

<header class="header">
  <div class="nav-container">
    <a href="index.html" class="logo-box">
      <img src="logo.jpg" class="logo-img" alt="Adhaar Logo">
      <div class="logo-text">The SoulServes</div>
    </a>

    <nav class="nav" id="mobileMenu">
      <a href="index.html"      <?= $activeNav==='home'      ? 'class="active"' : '' ?>>Home</a>
      <a href="about.html"      <?= $activeNav==='about'     ? 'class="active"' : '' ?>>About</a>
      <a href="activities.html" <?= $activeNav==='stories'   ? 'class="active"' : '' ?>>Our Stories</a>
      <a href="impact.html"     <?= $activeNav==='impact'    ? 'class="active"' : '' ?>>Impact</a>
      <a href="contact.html"    <?= $activeNav==='contact'   ? 'class="active"' : '' ?>>Contact</a>
      <a href="donate.html"     <?= $activeNav==='donate'    ? 'class="active"' : '' ?>>Donate</a>
      <a href="register.php" class="btn-nav">Sign Up</a>
    </nav>

    <div class="menu-icon" id="menuToggle">☰</div>
  </div>
</header>
