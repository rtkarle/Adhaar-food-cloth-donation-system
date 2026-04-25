<?php
require_once "db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.html");
    exit;
}

$email = $_SESSION['user_email'];

$stmt = $conn->prepare("SELECT name, email, mobile, address FROM register WHERE email=? AND role='donor' AND verified=1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) die("Access Denied");
$user = $res->fetch_assoc();

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name    = trim($_POST['name'] ?? '');
    $mobile  = trim($_POST['mobile'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$mobile) {
        $error = "Name and mobile are required.";
    } else {
        $up = $conn->prepare("UPDATE register SET name=?, mobile=?, address=? WHERE email=?");
        $up->bind_param("ssss", $name, $mobile, $address, $email);
        if ($up->execute()) {
            $success = "Profile updated successfully.";
            $user['name']    = $name;
            $user['mobile']  = $mobile;
            $user['address'] = $address;
        } else {
            $error = "Update failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile | Adhaar</title>
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
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
body{
  min-height:100vh;background:var(--bg);
  display:flex;align-items:center;justify-content:center;padding:24px;
}
.card{
  background:#fff;width:100%;max-width:480px;
  padding:48px 44px;border-radius:28px;
  box-shadow:0 30px 80px rgba(60,55,35,.14);
  animation:fadeUp .5s ease;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}
.card-header{margin-bottom:32px}
.card-header h2{font-size:24px;font-weight:800;color:var(--text)}
.card-header p{font-size:13px;color:var(--muted);margin-top:4px}

.field{margin-bottom:20px}
.field label{
  display:block;font-size:12px;font-weight:700;
  color:var(--muted);margin-bottom:7px;
  text-transform:uppercase;letter-spacing:.5px;
}
.field input,
.field textarea{
  width:100%;padding:13px 16px;
  border:2px solid #e5e3d8;border-radius:12px;
  font-size:14px;color:var(--text);background:#fafaf6;
  transition:.25s ease;outline:none;
  font-family:'Inter',sans-serif;
}
.field input:focus,
.field textarea:focus{border-color:var(--accent);background:#fff}
.field input:disabled{opacity:.55;cursor:not-allowed}
.field textarea{resize:vertical;min-height:90px}

.btn{
  width:100%;padding:14px;border:none;border-radius:50px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  color:#fff;font-size:15px;font-weight:700;cursor:pointer;
  box-shadow:0 12px 30px rgba(122,125,63,.4);
  transition:.3s ease;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 18px 40px rgba(122,125,63,.55)}

.back{
  display:block;text-align:center;margin-top:18px;
  color:var(--muted);text-decoration:none;font-size:13px;font-weight:600;
  transition:.2s;
}
.back:hover{color:var(--accent)}

.alert{
  padding:12px 16px;border-radius:10px;font-size:13px;
  margin-bottom:20px;font-weight:600;
}
.alert.success{background:#d1fae5;color:#065f46}
.alert.error{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>

<div class="card">
  <div class="card-header">
    <h2>Edit Profile</h2>
    <p>Update your personal information</p>
  </div>

  <?php if($success): ?>
    <div class="alert success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="alert error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <?= csrf_field() ?>
    <div class="field">
      <label>Full Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>

    <div class="field">
      <label>Email (read-only)</label>
      <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>

    <div class="field">
      <label>Mobile Number</label>
      <input type="tel" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label>Address</label>
      <textarea name="address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
    </div>

    <button class="btn" type="submit">Save Changes →</button>
  </form>

  <a class="back" href="donor_dashboard.php">← Back to Dashboard</a>
</div>

</body>
</html>
