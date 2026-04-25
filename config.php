<?php
/**
 * Adhaar – The SoulServe
 * Central config — keep this file OUT of public web root in production
 * or protect it via .htaccess: deny from all
 */

// ── DATABASE ──────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adhaar_db');

// ── MAIL ──────────────────────────────────────────
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'rtkarle03@gmail.com');
define('MAIL_PASSWORD', 'bcgfgyxgphpxdold');   // Gmail App Password
define('MAIL_FROM',     'rtkarle03@gmail.com');
define('MAIL_FROM_NAME','Adhaar – The SoulServe');

// ── APP ───────────────────────────────────────────
define('APP_URL',  'http://localhost/adhaar');
define('APP_NAME', 'Adhaar – The SoulServe');
