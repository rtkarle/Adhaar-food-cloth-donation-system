<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_email'])){
    die("Login required");
}

$email = $_SESSION['user_email'];

// FOOD
$food = $conn->prepare("SELECT * FROM food_donations WHERE donor_email=? ORDER BY created_at DESC");
$food->bind_param("s",$email);
$food->execute();
$food_res = $food->get_result();

// CLOTH
$cloth = $conn->prepare("SELECT * FROM cloth_donations WHERE donor_email=? ORDER BY created_at DESC");
$cloth->bind_param("s",$email);
$cloth->execute();
$cloth_res = $cloth->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Track Donations | Adhaar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f6f5f0;
  --accent:#7a7d3f;
  --accent2:#9a8f5c;
  --text:#2f2e26;
  --muted:#5a594d;
  --card:#fff;
  --shadow:0 20px 50px rgba(60,55,35,.1);
  --radius:20px;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
body{background:var(--bg);color:var(--text);min-height:100vh;padding:40px 20px}
.container{max-width:900px;margin:auto}
h2{font-size:26px;font-weight:800;margin-bottom:8px}
.sub{font-size:14px;color:var(--muted);margin-bottom:32px}
h3{font-size:18px;font-weight:700;margin:32px 0 16px;color:var(--accent)}

.card{
  background:var(--card);padding:28px 32px;
  border-radius:var(--radius);box-shadow:var(--shadow);
  margin-bottom:20px;border-left:4px solid var(--accent);
  transition:.3s ease;
}
.card:hover{transform:translateY(-3px)}
.card-title{font-size:16px;font-weight:700;margin-bottom:12px}
.card-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;font-size:14px}
.card-row span{color:var(--muted)}

.badge{
  display:inline-block;padding:5px 14px;border-radius:20px;
  font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
}
.submitted,.pending{background:#fef3c7;color:#92400e}
.accepted{background:#dbeafe;color:#1e40af}
.scheduled{background:#ede9fe;color:#5b21b6}
.out_for_pickup{background:#fce7f3;color:#9d174d}
.picked_up,.delivered{background:#d1fae5;color:#065f46}
.rejected{background:#fee2e2;color:#991b1b}

/* TIMELINE */
.timeline-bar{margin-top:20px}
.tl-steps{display:flex;align-items:center;gap:0}
.tl-step{
  display:flex;flex-direction:column;align-items:center;
  font-size:11px;color:var(--muted);flex:1;text-align:center;
}
.tl-dot{
  width:14px;height:14px;border-radius:50%;
  background:#d4d0c4;margin-bottom:6px;transition:.3s;
}
.tl-step.done .tl-dot{background:var(--accent);box-shadow:0 0 0 4px rgba(122,125,63,.2)}
.tl-step.done{color:var(--accent);font-weight:700}
.tl-line{flex:1;height:2px;background:#d4d0c4;margin-bottom:20px}
.tl-line.done{background:var(--accent)}

.progress-bar{
  height:6px;background:#e5e3d8;border-radius:10px;
  margin-top:16px;overflow:hidden;
}
.progress-fill{
  height:100%;
  background:linear-gradient(to right,var(--accent),var(--accent2));
  transition:width 1s ease;
}

.empty{
  text-align:center;padding:40px;
  background:var(--card);border-radius:var(--radius);
  color:var(--muted);font-size:14px;
}
.back-link{
  display:inline-block;margin-bottom:24px;
  color:var(--accent);font-weight:600;font-size:14px;text-decoration:none;
}
.back-link:hover{text-decoration:underline}
</style>
</head>
<body>

<div class="container">
  <a href="donor_dashboard.php" class="back-link">← Back to Dashboard</a>
  <h2>Track Your Donations</h2>
  <p class="sub">Real-time status of all your food and clothing donations.</p>

<?php
function renderCard($row, $type) {
    $steps = ['submitted','accepted','scheduled','out_for_pickup','picked_up'];
    $current = array_search($row['status'], $steps);
    if ($current === false) $current = 0;
    $width = (($current + 1) / count($steps)) * 100;
    $status = $row['status'];
?>
<div class="card">
  <div class="card-title">#<?= (int)$row['id'] ?> — <?= ucfirst($type) ?> Donation</div>

  <div class="card-row">
    <span>Quantity:</span>
    <strong><?= htmlspecialchars($row['quantity']) ?></strong>
  </div>
  <div class="card-row">
    <span>Status:</span>
    <span class="badge <?= htmlspecialchars($status) ?>"><?= ucfirst(str_replace('_',' ',$status)) ?></span>
  </div>
  <?php if (!empty($row['pickup_date'])): ?>
  <div class="card-row">
    <span>Pickup Date:</span>
    <strong><?= htmlspecialchars($row['pickup_date']) ?></strong>
  </div>
  <div class="card-row">
    <span>Pickup Time:</span>
    <strong><?= htmlspecialchars($row['pickup_time'] ?? 'TBD') ?></strong>
  </div>
  <div class="card-row">
    <span>Volunteer:</span>
    <strong><?= htmlspecialchars($row['volunteer_email'] ?? 'Not Assigned') ?></strong>
  </div>
  <?php endif; ?>

  <div class="progress-bar">
    <div class="progress-fill" style="width:<?= $width ?>%"></div>
  </div>

  <div class="timeline-bar">
    <div class="tl-steps">
      <?php foreach ($steps as $i => $step): ?>
        <div class="tl-step <?= $i <= $current ? 'done' : '' ?>">
          <div class="tl-dot"></div>
          <?= ucfirst(str_replace('_',' ',$step)) ?>
        </div>
        <?php if ($i < count($steps)-1): ?>
          <div class="tl-line <?= $i < $current ? 'done' : '' ?>"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php } ?>

<h3>🍲 Food Donations</h3>
<?php if ($food_res->num_rows > 0):
  while ($row = $food_res->fetch_assoc()) renderCard($row, 'food');
else: ?>
  <div class="empty">No food donations yet.</div>
<?php endif; ?>

<h3>👕 Clothing Donations</h3>
<?php if ($cloth_res->num_rows > 0):
  while ($row = $cloth_res->fetch_assoc()) renderCard($row, 'cloth');
else: ?>
  <div class="empty">No clothing donations yet.</div>
<?php endif; ?>

</div>

<script>
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>