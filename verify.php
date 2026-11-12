<?php
/**
 * -----------------------------------------------------------------------------
 * PUBLIC CLEARANCE CERTIFICATE VERIFICATION
 * -----------------------------------------------------------------------------
 * Anyone (employer, NYSC, another institution) can confirm that a downloaded
 * clearance certificate is genuine by entering its Certificate No. No login is
 * required. All data is read live from the database, so a forged/edited PDF can
 * never pass verification.
 */

declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$ref      = strtoupper(input('ref'));
$record   = null;
$units    = [];
$complete = false;
$searched = ($ref !== '');

if ($searched) {
    $pdo  = db();
    $stmt = $pdo->prepare(
        'SELECT cc.serial, cc.issued_at, u.id AS uid, u.full_name, u.matric_no,
                sp.programme, sp.level
         FROM clearance_certificates cc
         JOIN users u ON u.id = cc.user_id
         LEFT JOIN student_profiles sp ON sp.user_id = u.id
         WHERE cc.serial = ?'
    );
    $stmt->execute([$ref]);
    $record = $stmt->fetch();

    if ($record) {
        $complete = clearance_is_complete((int) $record['uid']);
        $q = $pdo->prepare(
            'SELECT cu.name, cr.status, cr.reviewed_at, r.full_name AS reviewer
             FROM clearance_requests cr
             JOIN clearance_units cu ON cu.id = cr.unit_id
             LEFT JOIN users r ON r.id = cr.reviewed_by
             WHERE cr.user_id = ?
             ORDER BY cu.sort_order'
        );
        $q->execute([(int) $record['uid']]);
        $units = $q->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify Clearance Certificate &middot; <?= e(INSTITUTION) ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.jpg">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body style="background:var(--canvas,#eef2f0)">
<div style="max-width:680px;margin:40px auto;padding:0 18px">
  <div class="flex items-center gap-12" style="justify-content:center;margin-bottom:18px">
    <img src="<?= BASE_URL ?>/assets/img/logo.jpg" style="width:52px;height:52px;border-radius:50%" alt="FPN">
    <div style="text-align:center">
      <div class="fw-700" style="font-size:18px;color:var(--brand,#0b6b3a)"><?= e(INSTITUTION) ?></div>
      <div class="text-muted text-sm">Clearance Certificate Verification</div>
    </div>
  </div>

  <div class="card">
    <div class="card__body">
      <form method="get" class="flex gap-8" style="flex-wrap:wrap">
        <input class="input" name="ref" value="<?= e($ref) ?>" placeholder="Enter Certificate No. e.g. FPN-CS-2026-0007-A1B2" style="flex:1;min-width:220px" required>
        <button class="btn btn-primary">Verify</button>
      </form>

      <?php if ($searched && !$record): ?>
        <div class="alert alert-danger mt-16 mb-0"><span class="ico">&#10007;</span>
          <div><b>No certificate found.</b> The certificate number <b><?= e($ref) ?></b> does not exist in our records. This document may be invalid or forged.</div>
        </div>
      <?php elseif ($searched && $record && !$complete): ?>
        <div class="alert alert-warning mt-16 mb-0"><span class="ico">&#9888;</span>
          <div><b>Not currently valid.</b> A record exists for this number, but the student's clearance is not fully approved at this time.</div>
        </div>
      <?php elseif ($searched && $record && $complete): ?>
        <div class="alert alert-success mt-16"><span class="ico">&#10003;</span>
          <div><b>Genuine certificate.</b> This clearance certificate was issued by <?= e(INSTITUTION) ?> and the student has been cleared by all units.</div>
        </div>
        <table class="data" style="width:100%;margin-top:6px">
          <tbody>
            <tr><th style="width:42%">Certificate No.</th><td><?= e($record['serial']) ?></td></tr>
            <tr><th>Name</th><td><?= e($record['full_name']) ?></td></tr>
            <tr><th>Matric No.</th><td><?= e($record['matric_no']) ?></td></tr>
            <tr><th>Programme</th><td><?= e($record['programme'] ?? '—') ?></td></tr>
            <tr><th>Level</th><td><?= e($record['level'] ?? '—') ?></td></tr>
            <tr><th>Status</th><td><span class="badge badge-success">FULLY CLEARED</span></td></tr>
            <tr><th>Issued</th><td><?= $record['issued_at'] ? date('d M Y', strtotime($record['issued_at'])) : '—' ?></td></tr>
          </tbody>
        </table>

        <p class="fw-700 mt-16" style="font-size:14px">Cleared units</p>
        <div class="table-wrap">
          <table class="data" style="width:100%">
            <thead><tr><th>Unit / Office</th><th>Status</th><th>Cleared By</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($units as $it): ?>
              <tr>
                <td><?= e($it['name']) ?></td>
                <td><?= status_badge($it['status']) ?></td>
                <td><?= e($it['reviewer'] ?: 'Department Officer') ?></td>
                <td><?= $it['reviewed_at'] ? date('d M Y', strtotime($it['reviewed_at'])) : '—' ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-2 text-sm mt-16 mb-0">Enter the Certificate No. printed on a clearance certificate (or scan its QR code) to confirm it is authentic.</p>
      <?php endif; ?>
    </div>
  </div>
  <p class="text-center text-muted text-sm mt-16"><?= e(INSTITUTION) ?> &middot; <?= e(MOTTO) ?></p>
</div>
</body>
</html>
