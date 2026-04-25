<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_email'])) { header("Location: login.html"); exit; }
$email  = $_SESSION['user_email'];
$filter = $_GET['filter'] ?? 'all';

$type_where = $status_where = '';
if ($filter === 'food')      $type_where   = "AND type='Food'";
elseif ($filter === 'cloth') $type_where   = "AND type='Clothes'";
elseif ($filter === 'pending')   $status_where = "AND status='pending'";
elseif ($filter === 'delivered') $status_where = "AND status='delivered'";

$sql = "
  SELECT * FROM (
    (SELECT 'Food' AS type, status, quantity, pickup_address, created_at FROM food_donations WHERE donor_email=?)
    UNION ALL
    (SELECT 'Clothes' AS type, status, quantity, pickup_address, created_at FROM cloth_donations WHERE donor_email=?)
  ) combined
  WHERE 1=1 $type_where $status_where
  ORDER BY created_at DESC
";
$h = $conn->prepare($sql);
$h->bind_param("ss", $email, $email); $h->execute();
$rows = $h->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donation History | Adhaar</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{ --bg:#f6f5f0; --accent:#7a7d3f; --accent2:#9a8f5c; --text:#2f2e26; --muted:#5a594d; --card:#fff; --shadow:0 8px 32px rgba(47,46,38,.09); --radius:18px; }
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Inter',system-ui,sans-serif; }
body{ background:var(--bg); color:var(--text); min-height:100vh; padding:40px 20px; }
.container{ max-width:900px; margin:auto; }
.back-link{ display:inline-flex; align-items:center; gap:6px; margin-bottom:22px; color:var(--accent); font-weight:700; font-size:14px; text-decoration:none; }
.back-link:hover{ text-decoration:underline; }
.history-head{ margin-bottom:24px; }
.history-head h2{ font-size:26px; font-weight:800; margin-bottom:4px; }
.history-head p{ color:var(--muted); font-size:14px; }

.filter-bar{ display:flex; gap:8px; flex-wrap:wrap; margin-bottom:24px; background:#fff; padding:14px 18px; border-radius:14px; box-shadow:var(--shadow); }
.filter-btn{ padding:8px 18px; border-radius:10px; border:1.5px solid #e0ddd5; background:#fafaf6; font-size:13px; font-weight:600; color:var(--muted); text-decoration:none; transition:.25s; }
.filter-btn:hover{ border-color:var(--accent); color:var(--accent); }
.filter-btn.active{ background:linear-gradient(135deg,#7a7d3f,#9a8f5c); color:#fff; border-color:transparent; box-shadow:0 4px 14px rgba(122,125,63,.3); }

.history-table-wrap{ background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; }
.history-table{ width:100%; border-collapse:collapse; font-size:13px; }
.history-table thead tr{ background:#f6f5f0; }
.history-table th{ padding:13px 16px; text-align:left; font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
.history-table td{ padding:14px 16px; border-bottom:1px solid #f0ede5; color:var(--text); vertical-align:middle; }
.history-table tr:last-child td{ border-bottom:none; }
.history-table tbody tr{ transition:.2s; animation:rowFade .35s ease forwards; opacity:0; }
.history-table tbody tr:hover td{ background:#fafaf6; }
.history-table tbody tr:nth-child(1){ animation-delay:.04s }
.history-table tbody tr:nth-child(2){ animation-delay:.08s }
.history-table tbody tr:nth-child(3){ animation-delay:.12s }
.history-table tbody tr:nth-child(4){ animation-delay:.16s }
.history-table tbody tr:nth-child(n+5){ animation-delay:.20s }
@keyframes rowFade{ to{ opacity:1; transform:translateY(0) } from{ opacity:0; transform:translateY(6px) } }

.type-badge{ display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
.type-food  { background:#fef3c7; color:#92400e; }
.type-cloth { background:#dbeafe; color:#1e40af; }

.pill{ display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
.pill.pending      { background:#fef3c7; color:#92400e; }
.pill.accepted     { background:#dbeafe; color:#1e40af; }
.pill.scheduled    { background:#ede9fe; color:#5b21b6; }
.pill.out_for_pickup{ background:#fce7f3; color:#9d174d; }
.pill.picked_up,
.pill.delivered    { background:#d1fae5; color:#065f46; }
.pill.rejected     { background:#fee2e2; color:#991b1b; }

.empty{ text-align:center; padding:52px 24px; color:var(--muted); font-size:14px; }
.empty .emoji{ font-size:40px; margin-bottom:12px; }

@media(max-width:640px){
  .history-table th:nth-child(3),.history-table td:nth-child(3){ display:none; }
  .history-table th,.history-table td{ padding:10px 12px; }
}
</style>
</head>
<body>
<div class="container">
  <a href="donor_dashboard.php" class="back-link">← Back to Dashboard</a>
  <div class="history-head">
    <h2>Donation History</h2>
    <p>All your food and clothing donations in one place.</p>
  </div>
  <div class="filter-bar">
    <a href="history.php?filter=all"       class="filter-btn <?= $filter==='all'       ? 'active' : '' ?>">All</a>
    <a href="history.php?filter=food"      class="filter-btn <?= $filter==='food'      ? 'active' : '' ?>">🍱 Food</a>
    <a href="history.php?filter=cloth"     class="filter-btn <?= $filter==='cloth'     ? 'active' : '' ?>">👕 Clothes</a>
    <a href="history.php?filter=pending"   class="filter-btn <?= $filter==='pending'   ? 'active' : '' ?>">⏳ Pending</a>
    <a href="history.php?filter=delivered" class="filter-btn <?= $filter==='delivered' ? 'active' : '' ?>">✅ Delivered</a>
  </div>
  <div class="history-table-wrap">
    <?php if (empty($rows)): ?>
      <div class="empty"><div class="emoji">📭</div><p>No donations found for this filter.</p></div>
    <?php else: ?>
    <table class="history-table">
      <thead><tr><th>Type</th><th>Quantity</th><th>Pickup Address</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td><span class="type-badge <?= $row['type']==='Food' ? 'type-food' : 'type-cloth' ?>"><?= $row['type']==='Food'?'🍱':'👕' ?> <?= $row['type'] ?></span></td>
          <td><?= htmlspecialchars($row['quantity'] ?? '—') ?></td>
          <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($row['pickup_address'] ?? '—') ?></td>
          <td><?= date("d M Y • h:i A", strtotime($row['created_at'])) ?></td>
          <td><span class="pill <?= $row['status'] ?>"><?= ucfirst(str_replace('_',' ',$row['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
