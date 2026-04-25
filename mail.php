<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/PHPMailer/src/Exception.php";
require __DIR__ . "/PHPMailer/src/PHPMailer.php";
require __DIR__ . "/PHPMailer/src/SMTP.php";

if (!defined('MAIL_USERNAME')) {
    require_once __DIR__ . '/config.php';
}

function sendMail(string $to, string $subject, string $body): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>','<br/>'], "\n", $body));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error to $to: " . $mail->ErrorInfo);
        return false;
    }
}

/* ── OTP EMAIL ─────────────────────────────────── */
function sendOTPMail(string $to, string $otp): bool {
    $subject = "Your OTP – Adhaar Verification";
    $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f6f5f0;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f5f0;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" style="max-width:520px;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px rgba(60,55,35,.12);">
      <tr><td style="background:linear-gradient(135deg,#7a7d3f,#9a8f5c);padding:36px 40px;text-align:center;">
        <h1 style="margin:0;color:#fff;font-size:24px;font-weight:800;">🌿 Adhaar – The SoulServe</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">Email Verification</p>
      </td></tr>
      <tr><td style="padding:40px 44px;">
        <p style="margin:0 0 8px;font-size:16px;color:#2f2e26;font-weight:600;">Hello 👋</p>
        <p style="margin:0 0 28px;font-size:14px;color:#5a594d;line-height:1.7;">Use the OTP below to verify your email and complete registration.</p>
        <div style="background:#f6f5f0;border:2px dashed #9a8f5c;border-radius:16px;padding:28px;text-align:center;margin-bottom:28px;">
          <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:#9a8f5c;letter-spacing:2px;text-transform:uppercase;">Your OTP Code</p>
          <p style="margin:0;font-size:42px;font-weight:900;color:#7a7d3f;letter-spacing:12px;">' . htmlspecialchars($otp) . '</p>
        </div>
        <div style="background:#fef3c7;border-radius:10px;padding:14px 18px;margin-bottom:24px;">
          <p style="margin:0;font-size:13px;color:#92400e;font-weight:600;">⏱ Valid for <strong>10 minutes</strong> only.</p>
        </div>
        <p style="margin:0;font-size:13px;color:#5a594d;line-height:1.7;">If you did not request this, please ignore this email.</p>
      </td></tr>
      <tr><td style="background:#f6f5f0;padding:24px 44px;border-top:1px solid #ede9df;">
        <p style="margin:0;font-size:12px;color:#9a8f5c;text-align:center;">© 2026 Adhaar – The SoulServe | Kopargaon, Maharashtra<br>
        <a href="mailto:adhaarsoulserve@gmail.com" style="color:#7a7d3f;text-decoration:none;">adhaarsoulserve@gmail.com</a></p>
      </td></tr>
    </table>
  </td></tr>
</table></body></html>';
    return sendMail($to, $subject, $body);
}

/* ── DONATION STATUS NOTIFICATION ─────────────── */
function sendStatusNotification(string $donorEmail, string $type, string $status, array $details = []): bool {
    $statusLabels = [
        'accepted'      => ['label' => 'Accepted ✅',      'color' => '#065f46', 'bg' => '#d1fae5', 'msg' => 'Your donation has been reviewed and accepted. We will schedule a pickup soon.'],
        'rejected'      => ['label' => 'Rejected ❌',      'color' => '#991b1b', 'bg' => '#fee2e2', 'msg' => 'Unfortunately your donation could not be accepted at this time. Please contact us for more details.'],
        'scheduled'     => ['label' => 'Pickup Scheduled 📅', 'color' => '#1e40af', 'bg' => '#dbeafe', 'msg' => 'A volunteer has been assigned and your pickup is scheduled.'],
        'out_for_pickup'=> ['label' => 'Out for Pickup 🚚', 'color' => '#9d174d', 'bg' => '#fce7f3', 'msg' => 'Our volunteer is on the way to collect your donation.'],
        'picked_up'     => ['label' => 'Picked Up 📦',     'color' => '#5b21b6', 'bg' => '#ede9fe', 'msg' => 'Your donation has been picked up and is being processed.'],
        'delivered'     => ['label' => 'Delivered 🤝',     'color' => '#065f46', 'bg' => '#d1fae5', 'msg' => 'Your donation has been successfully delivered to those in need. Thank you! 🙏'],
    ];

    if (!isset($statusLabels[$status])) return false;

    $s     = $statusLabels[$status];
    $label = $s['label'];
    $msg   = $s['msg'];
    $color = $s['color'];
    $bg    = $s['bg'];
    $typeLabel = ucfirst($type);

    $extraRows = '';
    if (!empty($details['pickup_date'])) {
        $extraRows .= '<tr><td style="padding:8px 0;font-size:14px;color:#5a594d;border-bottom:1px solid #f0ede5;"><strong>Pickup Date:</strong> ' . htmlspecialchars($details['pickup_date']) . '</td></tr>';
    }
    if (!empty($details['pickup_time'])) {
        $extraRows .= '<tr><td style="padding:8px 0;font-size:14px;color:#5a594d;border-bottom:1px solid #f0ede5;"><strong>Pickup Time:</strong> ' . htmlspecialchars($details['pickup_time']) . '</td></tr>';
    }
    if (!empty($details['volunteer_email'])) {
        $extraRows .= '<tr><td style="padding:8px 0;font-size:14px;color:#5a594d;"><strong>Volunteer:</strong> ' . htmlspecialchars($details['volunteer_email']) . '</td></tr>';
    }

    $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f6f5f0;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f5f0;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" style="max-width:520px;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px rgba(60,55,35,.12);">
      <tr><td style="background:linear-gradient(135deg,#7a7d3f,#9a8f5c);padding:32px 40px;text-align:center;">
        <h1 style="margin:0;color:#fff;font-size:22px;font-weight:800;">🌿 Adhaar – The SoulServe</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:13px;">Donation Status Update</p>
      </td></tr>
      <tr><td style="padding:36px 40px;">
        <p style="margin:0 0 16px;font-size:16px;color:#2f2e26;font-weight:600;">Hello 👋</p>
        <p style="margin:0 0 20px;font-size:14px;color:#5a594d;line-height:1.7;">Your <strong>' . $typeLabel . ' donation</strong> status has been updated.</p>
        <div style="background:' . $bg . ';border-radius:12px;padding:18px 22px;margin-bottom:20px;text-align:center;">
          <p style="margin:0;font-size:18px;font-weight:800;color:' . $color . ';">' . $label . '</p>
        </div>
        <p style="margin:0 0 20px;font-size:14px;color:#5a594d;line-height:1.7;">' . $msg . '</p>
        ' . ($extraRows ? '<table width="100%" style="border-top:1px solid #f0ede5;margin-bottom:20px;">' . $extraRows . '</table>' : '') . '
        <a href="' . APP_URL . '/track.php" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#7a7d3f,#9a8f5c);color:#fff;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;">Track Your Donation →</a>
      </td></tr>
      <tr><td style="background:#f6f5f0;padding:20px 40px;border-top:1px solid #ede9df;text-align:center;">
        <p style="margin:0;font-size:12px;color:#9a8f5c;">© 2026 Adhaar – The SoulServe | <a href="mailto:adhaarsoulserve@gmail.com" style="color:#7a7d3f;text-decoration:none;">adhaarsoulserve@gmail.com</a></p>
      </td></tr>
    </table>
  </td></tr>
</table></body></html>';

    return sendMail($donorEmail, "Donation Update: $label – Adhaar", $body);
}
