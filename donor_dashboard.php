<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_email'])) { header("Location: login.html"); exit; }
$email = $_SESSION['user_email'];

$u = $conn->prepare("SELECT name FROM register WHERE email=? AND role='donor' AND verified=1");
$u->bind_param("s", $email); $u->execute();
$user = $u->get_result()->fetch_assoc();
if (!$user) { header("Location: login.html"); exit; }

function countDonations($conn, $table, $email) {
  $q = $conn->prepare("SELECT COUNT(*) c FROM $table WHERE donor_email=?");
  $q->bind_param("s", $email); $q->execute();
  return (int)$q->get_result()->fetch_assoc()['c'];
}

$food  = countDonations($conn, "food_donations",  $email);
$cloth = countDonations($conn, "cloth_donations", $email);
$total = $food + $cloth;
$goal  = 20;
$percent = min(100, ($total / max(1,$goal)) * 100);

$rf = $conn->prepare("SELECT 'Food' type, quantity, pickup_address, status, created_at FROM food_donations WHERE donor_email=? ORDER BY created_at DESC LIMIT 5");
$rf->bind_param("s",$email); $rf->execute();
$recentFood = $rf->get_result()->fetch_all(MYSQLI_ASSOC);

$rc = $conn->prepare("SELECT 'Clothes' type, quantity, pickup_address, status, created_at FROM cloth_donations WHERE donor_email=? ORDER BY created_at DESC LIMIT 5");
$rc->bind_param("s",$email); $rc->execute();
$recentCloth = $rc->get_result()->fetch_all(MYSQLI_ASSOC);

$recent = array_merge($recentFood, $recentCloth);
usort($recent, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
$recent = array_slice($recent, 0, 5);

$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Adhaar</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="dashboard.css">
<style>
.quick-cta{
  display:inline-flex;align-items:center;gap:8px;
  padding:13px 28px;background:linear-gradient(135deg,#7a7d3f,#9a8f5c);
  color:#fff;border-radius:12px;font-weight:700;font-size:15px;
  text-decoration:none;box-shadow:0 6px 20px rgba(122,125,63,.35);
  transition:.3s;margin-bottom:32px;
}
.quick-cta:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(122,125,63,.45)}
.success-banner{
  background:linear-gradient(135deg,#d1fae5,#a7f3d0);border:1.5px solid #6ee7b7;
  color:#065f46;padding:14px 20px;border-radius:12px;font-weight:600;font-size:14px;
  margin-bottom:24px;display:flex;align-items:center;gap:10px;
}
</style>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <h2 class="logo">🌿 Adhaar</h2>
    <a href="donor_dashboard.php" class="nav-btn active">🏠 Dashboard</a>
    <a href="donate.php"          class="nav-btn">🎁 Donate</a>
    <a href="history.php"         class="nav-btn">📋 History</a>
    <a href="track.php"           class="nav-btn">📍 Track</a>
    <a href="edit_profile.php"    class="nav-btn">👤 Profile</a>
    <a href="logout.php"          class="nav-btn" style="margin-top:auto;color:#f87171;">← Logout</a>
  </aside>

  <main class="main">
    <div class="header">
      <h3>Welcome, <span><?= htmlspecialchars($user['name']) ?></span> 👋</h3>
      <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if($success): ?>
    <div class="success-banner">✅ Your <?= $success==='food'?'food':'clothing' ?> donation was submitted! We'll review it shortly.</div>
    <?php endif; ?>

    <a href="donate.php" class="quick-cta"><span>🎁</span> Quick Donate</a>

    <div class="cards">
      <div class="card highlight">
        <p>Total Contributions</p>
        <h1 data-count="<?= $total ?>">0</h1>
        <span class="card-sub">Food + Clothing combined</span>
      </div>
      <div class="card">
        <p>Food Donations</p>
        <h1 data-count="<?= $food ?>">0</h1>
        <span class="card-sub">Meals supported</span>
      </div>
      <div class="card">
        <p>Clothing Donations</p>
        <h1 data-count="<?= $cloth ?>">0</h1>
        <span class="card-sub">Lives warmed</span>
      </div>
    </div>

    <div class="recent-section">
      <h3>Recent Donations</h3>
      <?php if (empty($recent)): ?>
        <p style="color:#9a8f5c;font-size:14px;">No donations yet. <a href="donate.php" style="color:#7a7d3f;font-weight:600;">Make your first donation →</a></p>
      <?php else: ?>
      <table class="recent-table">
        <thead><tr><th>Type</th><th>Quantity</th><th>Address</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['type']) ?></td>
            <td><?= htmlspecialchars($r['quantity']) ?></td>
            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($r['pickup_address'] ?? '—') ?></td>
            <td><?= date("d M Y", strtotime($r['created_at'])) ?></td>
            <td><span class="pill <?= $r['status'] ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <div class="impact-wrap">
      <div class="impact-info">
        <h2>Your Impact</h2>
        <p>Every donation is verified, tracked, and delivered through trusted volunteers and partner organisations.</p>
        <ul class="impact-points">
          <li>Verified donation approval</li>
          <li>Pickup coordinated by volunteers</li>
          <li>Status tracking: Pending → Accepted → Delivered</li>
          <li>Full transparency at every step</li>
        </ul>
        <a href="donate.php" class="primary-btn">Donate Now →</a>
      </div>
      <div class="jar-container">
        <div class="jar-neck"></div>
        <div class="jar-body">
          <div class="jar-liquid" data-percent="<?= (int)$percent ?>"></div>
        </div>
        <p class="jar-text"><?= $total ?> / <?= $goal ?> Donations</p>
      </div>
    </div>

    <section class="how-it-works">
      <h2>How Adhaar Works</h2>
      <p class="how-sub">A transparent, verified, and dignified donation journey.</p>
      <div class="steps">
        <div class="step"><div class="icon">📦</div><h4>You Donate</h4><p>Submit food or clothing details.</p></div>
        <div class="step"><div class="icon">🛡️</div><h4>Admin Verification</h4><p>Donations are reviewed and approved.</p></div>
        <div class="step"><div class="icon">🚚</div><h4>Pickup</h4><p>Volunteer collects from your address.</p></div>
        <div class="step"><div class="icon">🤝</div><h4>Delivered</h4><p>Reaches people in need with dignity.</p></div>
      </div>
    </section>
  </main>
</div>
<script src="dashboard.js"></script>
</body>
</html>
