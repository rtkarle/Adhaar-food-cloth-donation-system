<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_email'])) { header("Location: login.html"); exit; }
$email = $_SESSION['user_email'];

$q = $conn->prepare("SELECT name,email,mobile,address,volunteer_reason FROM register WHERE email=? AND role='volunteer' AND verified=1");
$q->bind_param("s",$email); $q->execute();
$res = $q->get_result();
if ($res->num_rows !== 1) { header("Location: login.html"); exit; }
$user = $res->fetch_assoc();

$af = $conn->prepare("SELECT id,'Food' AS type,quantity,pickup_address,contact,status,created_at,image,donor_email,notes FROM food_donations WHERE volunteer_email=? AND status NOT IN ('delivered','rejected') ORDER BY created_at DESC");
$af->bind_param("s",$email); $af->execute();
$assigned_food = $af->get_result()->fetch_all(MYSQLI_ASSOC);

$ac = $conn->prepare("SELECT id,'Cloth' AS type,quantity,pickup_address,contact,status,created_at,image,donor_email,notes FROM cloth_donations WHERE volunteer_email=? AND status NOT IN ('delivered','rejected') ORDER BY created_at DESC");
$ac->bind_param("s",$email); $ac->execute();
$assigned_cloth = $ac->get_result()->fetch_all(MYSQLI_ASSOC);

$assigned = array_merge($assigned_food, $assigned_cloth);
usort($assigned, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));

$cf = $conn->prepare("SELECT id,'Food' AS type,quantity,pickup_address,status,created_at,donor_email FROM food_donations WHERE volunteer_email=? AND status='delivered' ORDER BY created_at DESC");
$cf->bind_param("s",$email); $cf->execute();
$comp_food = $cf->get_result()->fetch_all(MYSQLI_ASSOC);

$cc = $conn->prepare("SELECT id,'Cloth' AS type,quantity,pickup_address,status,created_at,donor_email FROM cloth_donations WHERE volunteer_email=? AND status='delivered' ORDER BY created_at DESC");
$cc->bind_param("s",$email); $cc->execute();
$comp_cloth = $cc->get_result()->fetch_all(MYSQLI_ASSOC);

$completed = array_merge($comp_food, $comp_cloth);
usort($completed, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Volunteer Dashboard | Adhaar</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{ --bg:#f6f5f0; --accent:#7a7d3f; --accent2:#9a8f5c; --text:#2f2e26; --muted:#5a594d; --card:#fff; }
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Inter',system-ui,sans-serif; }
body{ background:var(--bg); color:var(--text); }
.app{ display:flex; min-height:100vh; }

.sidebar{
  width:240px; background:linear-gradient(180deg,#2f2e26 0%,#3d3c30 60%,#4a4838 100%);
  padding:32px 18px; display:flex; flex-direction:column;
  position:sticky; top:0; height:100vh; overflow-y:auto;
  box-shadow:4px 0 24px rgba(47,46,38,.18);
}
.logo{ color:#fff; font-size:20px; font-weight:800; margin-bottom:32px; }
.nav-btn{
  display:flex; align-items:center; gap:10px; text-decoration:none;
  color:rgba(255,255,255,.72); padding:11px 14px; margin-bottom:4px;
  border-radius:10px; font-size:14px; font-weight:500; transition:.25s;
  cursor:pointer; background:none; border:none; width:100%; text-align:left;
}
.nav-btn:hover{ background:rgba(255,255,255,.12); color:#fff; transform:translateX(3px); }
.nav-btn.active{ background:linear-gradient(135deg,#7a7d3f,#9a8f5c); color:#fff; font-weight:700; box-shadow:0 4px 14px rgba(122,125,63,.35); }
.nav-btn.logout-btn{ margin-top:auto; color:#f87171; }

.main{ flex:1; padding:36px 40px; overflow-x:hidden; }
.top-bar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; }
.top-bar h3{ font-size:18px; font-weight:700; }
.top-bar h3 span{ color:var(--accent); }
.logout-link{ text-decoration:none; padding:8px 18px; border:1.5px solid var(--accent); color:var(--accent); border-radius:8px; font-size:13px; font-weight:600; transition:.25s; }
.logout-link:hover{ background:var(--accent); color:#fff; }

.stat-row{ display:flex; gap:16px; flex-wrap:wrap; margin-bottom:28px; }
.stat-chip{ background:#fff; border-radius:14px; padding:18px 24px; box-shadow:0 4px 18px rgba(47,46,38,.07); flex:1; min-width:140px; border-top:4px solid var(--accent2); }
.stat-chip p{ font-size:12px; color:var(--muted); font-weight:600; margin-bottom:6px; }
.stat-chip h2{ font-size:28px; font-weight:800; color:var(--text); }

.tabs{ display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
.tab-btn{ padding:10px 22px; border-radius:10px; border:1.5px solid #ddd; background:#fff; font-size:14px; font-weight:600; cursor:pointer; color:var(--muted); transition:.25s; }
.tab-btn.active{ background:linear-gradient(135deg,#7a7d3f,#9a8f5c); color:#fff; border-color:transparent; box-shadow:0 4px 14px rgba(122,125,63,.3); }
.tab-btn:hover:not(.active){ border-color:var(--accent); color:var(--accent); }

.tab-panel{ display:none; }
.tab-panel.active{ display:block; }

.donation-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; }
.don-card{ background:#fff; border-radius:18px; overflow:hidden; box-shadow:0 6px 24px rgba(47,46,38,.09); transition:.3s; border:1px solid #ede9df; }
.don-card:hover{ transform:translateY(-4px); box-shadow:0 14px 36px rgba(122,125,63,.14); }
.don-card-img{ width:100%; height:160px; object-fit:cover; display:block; background:#f0ede5; }
.don-card-img-ph{ width:100%; height:160px; background:linear-gradient(135deg,#f0ede5,#e8e4d8); display:flex; align-items:center; justify-content:center; font-size:40px; color:#9a8f5c; }
.don-card-body{ padding:18px 20px; }
.don-card-type{ display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px; }
.type-food{ background:#fef3c7; color:#92400e; }
.type-cloth{ background:#dbeafe; color:#1e40af; }
.don-card-body h4{ font-size:15px; font-weight:700; margin-bottom:8px; }
.don-card-meta{ font-size:13px; color:var(--muted); line-height:1.7; }
.don-card-meta strong{ color:var(--text); }
.don-card-footer{ padding:14px 20px; border-top:1px solid #f0ede5; display:flex; gap:10px; flex-wrap:wrap; }
.action-btn{ flex:1; padding:9px 14px; border-radius:10px; border:none; font-size:13px; font-weight:700; cursor:pointer; transition:.25s; text-align:center; text-decoration:none; display:inline-block; }
.btn-pickup{ background:#fef3c7; color:#92400e; } .btn-pickup:hover{ background:#fde68a; }
.btn-delivered{ background:#d1fae5; color:#065f46; } .btn-delivered:hover{ background:#a7f3d0; }

.pill{ display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
.pill.pending{ background:#fef3c7; color:#92400e; }
.pill.accepted{ background:#dbeafe; color:#1e40af; }
.pill.scheduled{ background:#ede9fe; color:#5b21b6; }
.pill.out_for_pickup{ background:#fce7f3; color:#9d174d; }
.pill.picked_up,.pill.delivered{ background:#d1fae5; color:#065f46; }
.pill.rejected{ background:#fee2e2; color:#991b1b; }

.profile-card{ background:#fff; border-radius:18px; padding:32px 36px; box-shadow:0 6px 24px rgba(47,46,38,.09); max-width:520px; }
.profile-avatar{ width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#7a7d3f,#9a8f5c); display:flex; align-items:center; justify-content:center; font-size:28px; color:#fff; font-weight:800; margin-bottom:20px; }
.profile-row{ margin-bottom:14px; }
.profile-row label{ font-size:12px; color:var(--muted); font-weight:600; display:block; margin-bottom:3px; }
.profile-row p{ font-size:15px; font-weight:600; color:var(--text); }

.empty-state{ text-align:center; padding:48px 24px; background:#fff; border-radius:18px; color:var(--muted); font-size:14px; box-shadow:0 4px 18px rgba(47,46,38,.07); }
.empty-state .emoji{ font-size:40px; margin-bottom:12px; }

@media(max-width:700px){
  .app{ flex-direction:column; }
  .sidebar{ width:100%; height:auto; position:relative; flex-direction:row; flex-wrap:wrap; padding:14px; gap:6px; }
  .logo{ width:100%; margin-bottom:8px; }
  .nav-btn{ flex:1; min-width:80px; justify-content:center; padding:8px 10px; font-size:12px; }
  .main{ padding:20px 16px; }
  .donation-grid{ grid-template-columns:1fr; }
}
</style>
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <h2 class="logo">🌿 Adhaar</h2>
    <button class="nav-btn active" onclick="openTab('assigned')">📦 Assigned Pickups</button>
    <button class="nav-btn" onclick="openTab('completed')">✅ Completed</button>
    <button class="nav-btn" onclick="openTab('profile')">👤 My Profile</button>
    <a href="logout.php" class="nav-btn logout-btn">← Logout</a>
  </aside>

  <main class="main">
    <div class="top-bar">
      <h3>Welcome, <span><?= htmlspecialchars($user['name']) ?></span> 👋</h3>
      <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="stat-row">
      <div class="stat-chip"><p>Assigned Pickups</p><h2><?= count($assigned) ?></h2></div>
      <div class="stat-chip"><p>Completed Deliveries</p><h2><?= count($completed) ?></h2></div>
    </div>

    <div class="tabs">
      <button class="tab-btn active" onclick="openTab('assigned')">📦 Assigned Pickups</button>
      <button class="tab-btn" onclick="openTab('completed')">✅ Completed</button>
      <button class="tab-btn" onclick="openTab('profile')">👤 Profile</button>
    </div>

    <!-- ASSIGNED -->
    <div id="tab-assigned" class="tab-panel active">
      <?php if (empty($assigned)): ?>
        <div class="empty-state"><div class="emoji">📭</div><p>No assigned pickups right now.</p></div>
      <?php else: ?>
      <div class="donation-grid">
        <?php foreach ($assigned as $d):
          $tbl = ($d['type']==='Food') ? 'food_donations' : 'cloth_donations';
          $img = !empty($d['image']) ? htmlspecialchars($d['image']) : '';
        ?>
        <div class="don-card">
          <?php if ($img): ?><img src="<?= $img ?>" alt="" class="don-card-img">
          <?php else: ?><div class="don-card-img-ph"><?= $d['type']==='Food'?'🍱':'👕' ?></div><?php endif; ?>
          <div class="don-card-body">
            <span class="don-card-type <?= $d['type']==='Food'?'type-food':'type-cloth' ?>"><?= $d['type'] ?></span>
            <h4>Qty: <?= htmlspecialchars($d['quantity']) ?></h4>
            <div class="don-card-meta">
              <strong>Address:</strong> <?= htmlspecialchars($d['pickup_address']??'—') ?><br>
              <strong>Contact:</strong> <?= htmlspecialchars($d['contact']??'—') ?><br>
              <strong>Donor:</strong> <?= htmlspecialchars($d['donor_email']) ?><br>
              <strong>Status:</strong> <span class="pill <?= $d['status'] ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span>
            </div>
          </div>
          <div class="don-card-footer">
            <form method="POST" action="update_status.php" style="flex:1">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <input type="hidden" name="table" value="<?= $tbl ?>">
              <input type="hidden" name="status" value="picked_up">
              <button type="submit" class="action-btn btn-pickup">📦 Mark Picked Up</button>
            </form>
            <form method="POST" action="update_status.php" style="flex:1">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <input type="hidden" name="table" value="<?= $tbl ?>">
              <input type="hidden" name="status" value="delivered">
              <button type="submit" class="action-btn btn-delivered">✅ Mark Delivered</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- COMPLETED -->
    <div id="tab-completed" class="tab-panel">
      <?php if (empty($completed)): ?>
        <div class="empty-state"><div class="emoji">🏆</div><p>No completed deliveries yet. Keep going!</p></div>
      <?php else: ?>
      <div class="donation-grid">
        <?php foreach ($completed as $c): ?>
        <div class="don-card">
          <div class="don-card-img-ph"><?= $c['type']==='Food'?'🍱':'👕' ?></div>
          <div class="don-card-body">
            <span class="don-card-type <?= $c['type']==='Food'?'type-food':'type-cloth' ?>"><?= $c['type'] ?></span>
            <h4>Qty: <?= htmlspecialchars($c['quantity']) ?></h4>
            <div class="don-card-meta">
              <strong>Address:</strong> <?= htmlspecialchars($c['pickup_address']??'—') ?><br>
              <strong>Donor:</strong> <?= htmlspecialchars($c['donor_email']) ?><br>
              <strong>Delivered:</strong> <?= date("d M Y", strtotime($c['created_at'])) ?><br>
              <strong>Status:</strong> <span class="pill delivered">Delivered</span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- PROFILE -->
    <div id="tab-profile" class="tab-panel">
      <div class="profile-card">
        <div class="profile-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
        <div class="profile-row"><label>Full Name</label><p><?= htmlspecialchars($user['name']) ?></p></div>
        <div class="profile-row"><label>Email</label><p><?= htmlspecialchars($user['email']) ?></p></div>
        <div class="profile-row"><label>Mobile</label><p><?= htmlspecialchars($user['mobile']??'—') ?></p></div>
        <?php if (!empty($user['address'])): ?>
        <div class="profile-row"><label>Address</label><p><?= htmlspecialchars($user['address']) ?></p></div>
        <?php endif; ?>
        <a href="edit_profile.php" style="display:inline-block;margin-top:16px;padding:10px 22px;background:linear-gradient(135deg,#7a7d3f,#9a8f5c);color:#fff;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;">Edit Profile</a>
      </div>
    </div>
  </main>
</div>

<script>
function openTab(id) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn, .nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  document.querySelectorAll('.tab-btn').forEach(b => {
    if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(id)) b.classList.add('active');
  });
  document.querySelectorAll('.nav-btn').forEach(b => {
    if (b.getAttribute('onclick') && b.getAttribute('onclick').includes(id)) b.classList.add('active');
  });
}
</script>
</body>
</html>
