<?php
/**
 * -----------------------------------------------------------------------------
 * ONE-CLICK INSTALLER
 * -----------------------------------------------------------------------------
 * Just open this page in your browser:  http://localhost/fpn-portal/install.php
 *
 * It performs the ENTIRE setup automatically:
 *   1. Connects to your MySQL server (using config/config.php credentials)
 *   2. Creates the `fpn_portal` database + all tables + seed data
 *      (runs database/fpn_portal.sql for you - no phpMyAdmin import needed)
 *   3. Creates the default Admin & Staff accounts with securely hashed passwords
 *   4. Self-locks once everything exists (safe to re-open)
 *
 * DELETE this file after a successful install for production use.
 */

declare(strict_types=1);

// Load configuration + helpers (defines constants and the e() function).
require_once __DIR__ . '/includes/functions.php';

$steps   = [];   // human-readable progress lines
$error   = null; // fatal error message
$locked  = false;
$success = false;

/** Build a PDO connection to the MySQL *server* (no database selected yet). */
function server_pdo(): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true, // allow running the multi-statement .sql dump
    ]);
}

$defaults = [
    ['role' => 'admin', 'staff_id' => 'ADMIN001', 'email' => 'admin@fpn.edu.ng', 'name' => 'HOD Computer Science',  'pw' => 'Admin@12345'],
    ['role' => 'staff', 'staff_id' => 'STAFF001', 'email' => 'staff@fpn.edu.ng', 'name' => 'Project Coordinator', 'pw' => 'Staff@12345'],
];

try {
    $pdo = server_pdo();
    $steps[] = 'Connected to MySQL server at ' . DB_HOST . ':' . DB_PORT . '.';

    // ---- Does the schema already exist? -------------------------------------
    $dbName  = DB_NAME;
    $hasUsers = false;
    try {
        $pdo->exec('USE `' . str_replace('`', '', $dbName) . '`');
        $hasUsers = (bool) $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    } catch (Throwable $ignore) {
        // database does not exist yet - that is fine, we will create it
    }

    // ---- Run the full schema only if tables are not present ------------------
    if (!$hasUsers) {
        $sqlFile = BASE_PATH . '/database/fpn_portal.sql';
        if (!is_file($sqlFile)) {
            throw new RuntimeException('Schema file not found at database/fpn_portal.sql');
        }
        $sql = file_get_contents($sqlFile);
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Could not read database/fpn_portal.sql');
        }
        // The dump itself contains CREATE DATABASE + USE, so a server-level
        // connection is all we need.
        $pdo->exec($sql);
        $steps[] = 'Created database <b>' . e($dbName) . '</b> with all tables and seed data.';
        // Make sure we are pointed at the new database for the account inserts.
        $pdo->exec('USE `' . str_replace('`', '', $dbName) . '`');
    } else {
        $steps[] = 'Database <b>' . e($dbName) . '</b> already present - kept existing data.';
    }

    // ---- Ensure upload folders exist & are writable -------------------------
    foreach (['passports', 'projects', 'clearance', 'signatures'] as $sub) {
        $dir = UPLOAD_PATH . '/' . $sub;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }
    $steps[] = 'Verified upload folders.';

    // ---- Ensure certificate verification table + settings (idempotent) ------
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `clearance_certificates` (
          `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `user_id`   INT UNSIGNED NOT NULL,
          `serial`    VARCHAR(40)  NOT NULL,
          `issued_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_cert_user` (`user_id`),
          UNIQUE KEY `uq_cert_serial` (`serial`),
          CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $certSettings = [
        'cert_hod_name'          => 'HOD Computer Science',
        'cert_hod_title'         => 'Head of Department, Computer Science',
        'cert_hod_signature'     => '',
        'cert_officer_name'      => 'Directorate of Academic Affairs',
        'cert_officer_title'     => 'Registrar / School Officer',
        'cert_officer_signature' => '',
        'cert_stamp'             => '',
    ];
    $setStmt = $pdo->prepare('INSERT IGNORE INTO settings (skey, svalue) VALUES (?, ?)');
    foreach ($certSettings as $ck => $cv) {
        $setStmt->execute([$ck, $cv]);
    }
    $steps[] = 'Verified clearance-certificate verification table &amp; settings.';

    // ---- Create default accounts (idempotent) ------------------------------
    $created = 0;
    foreach ($defaults as $d) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE staff_id = ?');
        $chk->execute([$d['staff_id']]);
        if ($chk->fetch()) {
            continue;
        }
        $ins = $pdo->prepare('INSERT INTO users (role, staff_id, email, password_hash, full_name) VALUES (?,?,?,?,?)');
        $ins->execute([$d['role'], $d['staff_id'], $d['email'], password_hash($d['pw'], PASSWORD_DEFAULT), $d['name']]);
        $steps[] = 'Created ' . strtoupper($d['role']) . ' account: <b>' . e($d['staff_id']) . '</b>';
        $created++;
    }

    // Lock only when the schema was already there AND no new accounts were made.
    if ($hasUsers && $created === 0) {
        $locked = true;
    } else {
        $success = true;
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Installer &middot; FPN Portal</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body style="background:var(--canvas)">
<div style="max-width:600px;margin:50px auto;padding:0 20px">
  <div class="card">
    <div class="card__head" style="gap:12px">
      <img src="<?= BASE_URL ?>/assets/img/logo.jpg" style="width:44px;height:44px;border-radius:50%" alt="FPN">
      <div><h3>FPN Portal &mdash; One-Click Installer</h3><span class="text-muted text-sm"><?= e(DEPARTMENT) ?> Department</span></div>
    </div>
    <div class="card__body">
      <?php if ($error): ?>
        <div class="alert alert-danger"><span class="ico">&#9679;</span>
          <div><b>Setup could not complete.</b><br><?= e($error) ?></div>
        </div>
        <p class="text-2 text-sm">Checklist:</p>
        <ul style="margin:8px 0 16px 18px;color:var(--text-2);font-size:14px">
          <li>Is <b>MySQL</b> running in the XAMPP Control Panel?</li>
          <li>Are the credentials in <code>config/config.php</code> correct? (default XAMPP is user <code>root</code> with an empty password)</li>
          <li>Does <code>database/fpn_portal.sql</code> exist inside the project?</li>
        </ul>
        <a class="btn btn-primary btn-block" href="">Try again</a>
      <?php elseif ($locked): ?>
        <div class="alert alert-success"><span class="ico">&#10003;</span>
          <div><b>Already installed.</b> Everything is set up and the default accounts exist. For security, please delete <code>install.php</code> now.</div>
        </div>
        <a class="btn btn-primary btn-block" href="<?= BASE_URL ?>/login.php">Go to login</a>
      <?php else: ?>
        <div class="alert alert-success"><span class="ico">&#10003;</span><div><b>Installation complete!</b> Your portal is ready to use.</div></div>
        <ul style="margin:0 0 18px 18px;color:var(--text-2);font-size:14px;line-height:1.9">
          <?php foreach ($steps as $s): ?><li><?= $s ?></li><?php endforeach; ?>
        </ul>
        <div class="card" style="background:var(--surface-2);box-shadow:none">
          <div class="card__body">
            <p class="text-sm fw-700">Default login credentials</p>
            <p class="text-sm text-2 mt-8">Admin (HOD) &mdash; <b>ADMIN001</b> / <b>Admin@12345</b></p>
            <p class="text-sm text-2">Staff &mdash; <b>STAFF001</b> / <b>Staff@12345</b></p>
            <p class="help">Students create their own accounts from the Register page.</p>
          </div>
        </div>
        <div class="alert alert-warning mt-16"><span class="ico">&#9888;</span><div>For security, delete <code>install.php</code> after logging in.</div></div>
        <a class="btn btn-primary btn-block" href="<?= BASE_URL ?>/login.php">Go to login</a>
      <?php endif; ?>
    </div>
  </div>
  <p class="text-center text-muted text-sm mt-16"><?= e(INSTITUTION) ?> &middot; <?= e(MOTTO) ?></p>
</div>
</body>
</html>
