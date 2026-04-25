<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}

// Pagination
$per_page   = 20;
$food_page  = max(1, (int)($_GET['fp'] ?? 1));
$cloth_page = max(1, (int)($_GET['cp'] ?? 1));
$food_offset  = ($food_page  - 1) * $per_page;
$cloth_offset = ($cloth_page - 1) * $per_page;

$food  = $conn->query("SELECT * FROM food_donations  ORDER BY created_at DESC LIMIT $per_page OFFSET $food_offset");
$cloth = $conn->query("SELECT * FROM cloth_donations ORDER BY created_at DESC LIMIT $per_page OFFSET $cloth_offset");

$food_total  = (int)$conn->query("SELECT COUNT(*) c FROM food_donations") ->fetch_assoc()['c'];
$cloth_total = (int)$conn->query("SELECT COUNT(*) c FROM cloth_donations")->fetch_assoc()['c'];
$food_pages  = (int)ceil($food_total  / $per_page);
$cloth_pages = (int)ceil($cloth_total / $per_page);

$food_count  = $food_total;
$cloth_count = $cloth_total;
$pending     = $conn->query("SELECT COUNT(*) c FROM food_donations WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$pending    += $conn->query("SELECT COUNT(*) c FROM cloth_donations WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Adhaar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f6f5f0;
  --sidebar:#1e1d18;
  --accent:#7a7d3f;
  --accent2:#9a8f5c;
  --card:#fff;
  --text:#2f2e26;
  --muted:#5a594d;
  --danger:#dc2626;
  --success:#16a34a;
  --warning:#d97706;
  --info:#2563eb;
  --radius:16px;
  --shadow:0 8px 30px rgba(0,0,0,.08);
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
body{background:var(--bg);color:var(--text);min-height:100vh}

/* LAYOUT */
.app{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{
  width:240px;min-width:240px;
  background:var(--sidebar);
  padding:28px 18px;
  display:flex;flex-direction:column;gap:4px;
  position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sidebar-logo{
  font-size:20px;font-weight:800;color:#fff;
  margin-bottom:28px;padding-bottom:18px;
  border-bottom:1px solid rgba(255,255,255,.1);
  display:flex;align-items:center;gap:8px;
}
.nav-item{
  padding:11px 14px;border-radius:10px;
  color:rgba(255,255,255,.65);font-size:13.5px;font-weight:500;
  cursor:pointer;transition:.2s ease;display:flex;align-items:center;gap:9px;
  border:none;background:none;width:100%;text-align:left;
}
.nav-item:hover{background:rgba(255,255,255,.1);color:#fff}
.nav-item.active{background:var(--accent);color:#fff;font-weight:700}
.sidebar-footer{margin-top:auto;padding-top:16px}
.logout-link{
  display:block;text-align:center;padding:10px;border-radius:10px;
  background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);
  text-decoration:none;font-size:13px;font-weight:600;transition:.2s;
}
.logout-link:hover{background:rgba(220,38,38,.3);color:#fff}

/* MAIN */
.main{flex:1;padding:36px 44px;overflow-y:auto}

/* TOPBAR */
.topbar{
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:32px;
}
.topbar h2{font-size:22px;font-weight:700}
.admin-badge{
  background:var(--accent);color:#fff;
  padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;
}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:32px}
.stat{
  background:var(--card);padding:24px;border-radius:var(--radius);
  box-shadow:var(--shadow);border-left:4px solid var(--accent);
  transition:.25s ease;
}
.stat:hover{transform:translateY(-3px)}
.stat .s-label{font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.stat .s-value{font-size:32px;font-weight:800;color:var(--text)}

/* TABS */
.tabs{display:flex;gap:8px;margin-bottom:24px}
.tab-btn{
  padding:10px 22px;border-radius:50px;border:2px solid var(--accent);
  background:transparent;color:var(--accent);font-weight:700;font-size:13px;
  cursor:pointer;transition:.25s ease;
}
.tab-btn.active,.tab-btn:hover{background:var(--accent);color:#fff}

/* TABLE */
.table-wrap{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.table-wrap table{width:100%;border-collapse:collapse}
.table-wrap th{
  background:#f8f7f2;padding:14px 16px;text-align:left;
  font-size:12px;font-weight:700;color:var(--muted);
  text-transform:uppercase;letter-spacing:.5px;
  border-bottom:2px solid #ede9df;
}
.table-wrap td{
  padding:14px 16px;font-size:13.5px;
  border-bottom:1px solid #f0ede4;vertical-align:middle;
}
.table-wrap tr:last-child td{border-bottom:none}
.table-wrap tr:hover td{background:#fafaf6}
.table-wrap img{width:70px;height:55px;object-fit:cover;border-radius:8px}

/* STATUS PILLS */
.pill{
  display:inline-block;padding:4px 12px;border-radius:20px;
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
}
.pill.pending{background:#fef3c7;color:#92400e}
.pill.accepted{background:#d1fae5;color:#065f46}
.pill.rejected{background:#fee2e2;color:#991b1b}
.pill.scheduled{background:#dbeafe;color:#1e40af}
.pill.out_for_pickup{background:#ede9fe;color:#5b21b6}
.pill.picked_up,.pill.delivered{background:#d1fae5;color:#065f46}

/* ACTION BUTTONS */
.action-form{display:inline}
.btn{
  padding:6px 14px;border:none;border-radius:8px;
  font-size:12px;font-weight:700;cursor:pointer;transition:.2s ease;
  margin:2px;
}
.btn:hover{opacity:.85;transform:translateY(-1px)}
.btn-accept{background:#16a34a;color:#fff}
.btn-reject{background:#dc2626;color:#fff}
.btn-schedule{background:#7c3aed;color:#fff}
.btn-pickup{background:#2563eb;color:#fff}
.btn-done{background:#059669;color:#fff}

/* SCHEDULE FORM */
.schedule-inputs{
  display:flex;flex-direction:column;gap:6px;margin-top:8px;
}
.schedule-inputs input{
  padding:7px 10px;border-radius:8px;border:1.5px solid #d1d5db;
  font-size:12px;background:#f9f9f6;
}

/* HIDDEN */
.tab-panel{display:none}
.tab-panel.active{display:block}

/* RESPONSIVE */
@media(max-width:1100px){
  .main{padding:24px 20px}
  .stats{grid-template-columns:1fr 1fr}
}
@media(max-width:700px){
  .sidebar{display:none}
  .stats{grid-template-columns:1fr}
  .table-wrap{overflow-x:auto}
}
</style>
</head>
<body>
<div class="app">

  <aside class="sidebar">
    <div class="sidebar-logo">🌿 Adhaar Admin</div>
    <button class="nav-item active" onclick="switchTab('food',this)">🍲 Food Donations</button>
    <button class="nav-item" onclick="switchTab('cloth',this)">👕 Cloth Donations</button>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,.1)">
      <a href="distribution_system.php" style="display:flex;align-items:center;gap:9px;text-decoration:none;color:rgba(255,255,255,.72);padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;transition:.2s;" onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='none'">🚚 Distribution System</a>
    </div>
    <div class="sidebar-footer">
      <a href="logout.php" class="logout-link">← Logout</a>
    </div>
  </aside>

  <main class="main">

    <div class="topbar">
      <h2>Admin Dashboard</h2>
      <span class="admin-badge">Admin</span>
    </div>

    <div class="stats">
      <div class="stat">
        <div class="s-label">Food Donations</div>
        <div class="s-value"><?= (int)$food_count ?></div>
      </div>
      <div class="stat">
        <div class="s-label">Cloth Donations</div>
        <div class="s-value"><?= (int)$cloth_count ?></div>
      </div>
      <div class="stat" style="border-color:#d97706">
        <div class="s-label">Pending Review</div>
        <div class="s-value" style="color:#d97706"><?= (int)$pending ?></div>
      </div>
    </div>

    <!-- FOOD TAB -->
    <div id="tab-food" class="tab-panel active">
      <div class="tabs">
        <button class="tab-btn active">🍲 Food Donations</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Donor</th><th>Food Time</th><th>Qty</th>
              <th>Safe Hrs</th><th>Pickup Address</th><th>Contact</th>
              <th>Image</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($f = $food->fetch_assoc()): ?>
          <tr>
            <td><?= (int)$f['id'] ?></td>
            <td><?= htmlspecialchars($f['donor_email']) ?></td>
            <td><?= htmlspecialchars($f['food_time'] ?? 'N/A') ?></td>
            <td><?= (int)$f['quantity'] ?></td>
            <td><?= (int)$f['safe_hours'] ?>h</td>
            <td><?= htmlspecialchars($f['pickup_address']) ?></td>
            <td><?= htmlspecialchars($f['contact']) ?></td>
            <td>
              <?php if(!empty($f['image'])): ?>
                <img src="<?= htmlspecialchars($f['image']) ?>" alt="donation">
              <?php else: ?><span style="color:var(--muted);font-size:12px">No image</span><?php endif; ?>
            </td>
            <td>
              <?php $s = $f['status']; ?>
              <span class="pill <?= htmlspecialchars($s) ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></span>
            </td>
            <td>
              <?php if($f['status'] === 'pending'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <input type="hidden" name="table" value="food_donations">
                  <input type="hidden" name="status" value="accepted">
                  <button class="btn btn-accept">Accept</button>
                </form>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <input type="hidden" name="table" value="food_donations">
                  <input type="hidden" name="status" value="rejected">
                  <button class="btn btn-reject">Reject</button>
                </form>
              <?php elseif($f['status'] === 'accepted'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <input type="hidden" name="table" value="food_donations">
                  <input type="hidden" name="status" value="scheduled">
                  <div class="schedule-inputs">
                    <input type="date" name="pickup_date" required>
                    <input type="text" name="pickup_time" placeholder="e.g. 10 AM – 12 PM" required>
                    <input type="email" name="volunteer_email" placeholder="Volunteer email" required>
                    <button class="btn btn-schedule">Schedule</button>
                  </div>
                </form>
              <?php elseif($f['status'] === 'scheduled'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <input type="hidden" name="table" value="food_donations">
                  <input type="hidden" name="status" value="out_for_pickup">
                  <button class="btn btn-pickup">Out for Pickup</button>
                </form>
              <?php elseif($f['status'] === 'out_for_pickup'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                  <input type="hidden" name="table" value="food_donations">
                  <input type="hidden" name="status" value="picked_up">
                  <button class="btn btn-done">Mark Picked</button>
                </form>
              <?php else: ?>
                <span style="color:var(--muted);font-size:12px">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div><!-- end tab-food -->

    <!-- FOOD PAGINATION -->
    <?php if ($food_pages > 1): ?>
    <div style="display:flex;gap:8px;justify-content:center;margin-top:16px;flex-wrap:wrap;">
      <?php for($p=1;$p<=$food_pages;$p++): ?>
      <a href="?fp=<?=$p?>&cp=<?=$cloth_page?>" style="padding:7px 14px;border-radius:8px;border:1.5px solid <?=$p===$food_page?'#7a7d3f':'#ddd'?>;background:<?=$p===$food_page?'#7a7d3f':'#fff'?>;color:<?=$p===$food_page?'#fff':'#5a594d'?>;font-size:13px;font-weight:600;text-decoration:none;"><?=$p?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <!-- CLOTH TAB -->
    <div id="tab-cloth" class="tab-panel">
      <div class="tabs">
        <button class="tab-btn active">👕 Cloth Donations</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Donor</th><th>Type</th><th>Qty</th>
              <th>Pickup Address</th><th>Contact</th>
              <th>Image</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($c = $cloth->fetch_assoc()): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= htmlspecialchars($c['donor_email']) ?></td>
            <td><?= htmlspecialchars($c['cloth_type']) ?></td>
            <td><?= (int)$c['quantity'] ?></td>
            <td><?= htmlspecialchars($c['pickup_address']) ?></td>
            <td><?= htmlspecialchars($c['contact']) ?></td>
            <td>
              <?php if(!empty($c['image'])): ?>
                <img src="<?= htmlspecialchars($c['image']) ?>" alt="donation">
              <?php else: ?><span style="color:var(--muted);font-size:12px">No image</span><?php endif; ?>
            </td>
            <td>
              <?php $s = $c['status']; ?>
              <span class="pill <?= htmlspecialchars($s) ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></span>
            </td>
            <td>
              <?php if($c['status'] === 'pending'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="table" value="cloth_donations">
                  <input type="hidden" name="status" value="accepted">
                  <button class="btn btn-accept">Accept</button>
                </form>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="table" value="cloth_donations">
                  <input type="hidden" name="status" value="rejected">
                  <button class="btn btn-reject">Reject</button>
                </form>
              <?php elseif($c['status'] === 'accepted'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="table" value="cloth_donations">
                  <input type="hidden" name="status" value="scheduled">
                  <div class="schedule-inputs">
                    <input type="date" name="pickup_date" required>
                    <input type="text" name="pickup_time" placeholder="e.g. 10 AM – 12 PM" required>
                    <input type="email" name="volunteer_email" placeholder="Volunteer email" required>
                    <button class="btn btn-schedule">Schedule</button>
                  </div>
                </form>
              <?php elseif($c['status'] === 'scheduled'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="table" value="cloth_donations">
                  <input type="hidden" name="status" value="out_for_pickup">
                  <button class="btn btn-pickup">Out for Pickup</button>
                </form>
              <?php elseif($c['status'] === 'out_for_pickup'): ?>
                <form class="action-form" method="POST" action="update_status.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="table" value="cloth_donations">
                  <input type="hidden" name="status" value="picked_up">
                  <button class="btn btn-done">Mark Picked</button>
                </form>
              <?php else: ?>
                <span style="color:var(--muted);font-size:12px">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div><!-- end tab-cloth -->

    <!-- CLOTH PAGINATION -->
    <?php if ($cloth_pages > 1): ?>
    <div style="display:flex;gap:8px;justify-content:center;margin-top:16px;flex-wrap:wrap;">
      <?php for($p=1;$p<=$cloth_pages;$p++): ?>
      <a href="?fp=<?=$food_page?>&cp=<?=$p?>" style="padding:7px 14px;border-radius:8px;border:1.5px solid <?=$p===$cloth_page?'#7a7d3f':'#ddd'?>;background:<?=$p===$cloth_page?'#7a7d3f':'#fff'?>;color:<?=$p===$cloth_page?'#fff':'#5a594d'?>;font-size:13px;font-weight:600;text-decoration:none;"><?=$p?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<script>
function switchTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}
</script>
</body>
</html>
