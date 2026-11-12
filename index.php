<?php
require_once __DIR__ . '/includes/auth.php';
// If already signed in, go straight to the right dashboard
if (is_logged_in()) {
    redirect(dashboard_url());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FPN <?= e(PORTAL_NAME) ?> &middot; <?= e(DEPARTMENT) ?></title>
<meta name="description" content="Official graduation and clearance portal for graduating Computer Science students of the Federal Polytechnic Nasarawa.">
<link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.jpg">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="land-nav">
  <div class="land-nav__in">
    <div class="land-brand">
      <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="FPN logo">
      <div>
        <b>Federal Polytechnic Nasarawa</b><br>
        <span><?= e(DEPARTMENT) ?> &middot; Graduating Students Portal</span>
      </div>
    </div>
    <div class="flex items-center gap-12">
      <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Sign In</a>
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Register</a>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="hero__in">
    <span class="eyebrow">&#127891; <?= e(SCHOOL) ?></span>
    <h1>Your final lap, fully digital &mdash; clearance, project &amp; graduation in one place.</h1>
    <p>A dedicated portal for graduating students of the Department of <?= e(DEPARTMENT) ?>. Complete your departmental clearance, submit your final year project, track your CGPA and register as an alumnus &mdash; all online.</p>
    <div class="hero__cta">
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-accent">Get Started &rarr;</a>
      <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.4)">I already have an account</a>
    </div>
  </div>
</header>

<section class="section">
  <h2 class="sec-title">Everything a finalist needs</h2>
  <p class="sec-sub">Built specifically for the graduation workflow of the <?= e(DEPARTMENT) ?> Department, <?= e(INSTITUTION) ?>.</p>
  <div class="grid grid-3" style="margin-top:38px">
    <div class="feature"><div class="feature__ico">&#10003;</div><h4>Online Clearance</h4><p>Request and track clearance across the department, library, bursary, student affairs and more &mdash; unit by unit, in real time.</p></div>
    <div class="feature"><div class="feature__ico">&#128196;</div><h4>Project Submission</h4><p>Upload your final year project, get a supervisor assigned, and receive review feedback and grades securely.</p></div>
    <div class="feature"><div class="feature__ico">&#128202;</div><h4>Results &amp; CGPA</h4><p>View your semester GPA history and cumulative CGPA computed automatically as records are released.</p></div>
    <div class="feature"><div class="feature__ico">&#127891;</div><h4>Alumni Registration</h4><p>Keep the department connected to you after graduation with a quick alumni data form.</p></div>
    <div class="feature"><div class="feature__ico">&#128226;</div><h4>Announcements</h4><p>Never miss a deadline. Departmental notices are targeted to your exact programme.</p></div>
    <div class="feature"><div class="feature__ico">&#128274;</div><h4>Secure by design</h4><p>Hashed passwords, CSRF protection, prepared statements and role-based access keep your data safe.</p></div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <h2 class="sec-title">Programmes supported</h2>
  <p class="sec-sub">Aligned with the current NBTE programme structure of the Department of <?= e(DEPARTMENT) ?>.</p>
  <div class="grid grid-3" style="margin-top:38px">
    <div class="prog-card">
      <div class="prog-card__top" style="background:linear-gradient(135deg,#0b6b3a,#084d2a)"><b>National Diploma</b><h4>ND Computer Science</h4></div>
      <div class="prog-card__body"><ul><li>Foundational computing</li><li>Programming &amp; databases</li><li>Two-year diploma programme</li></ul></div>
    </div>
    <div class="prog-card">
      <div class="prog-card__top" style="background:linear-gradient(135deg,#c1121f,#8f0d17)"><b>Higher National Diploma</b><h4>HND Software &amp; Web Development</h4></div>
      <div class="prog-card__body"><ul><li>Advanced software engineering</li><li>Web &amp; mobile development</li><li>Capstone project</li></ul></div>
    </div>
    <div class="prog-card">
      <div class="prog-card__top" style="background:linear-gradient(135deg,#d4a017,#a87c10)"><b>Higher National Diploma</b><h4>HND Networking &amp; Cloud Computing</h4></div>
      <div class="prog-card__body"><ul><li>Network administration</li><li>Cloud infrastructure</li><li>Capstone project</li></ul></div>
    </div>
  </div>
</section>

<footer class="landing-foot">
  <p><b><?= e(INSTITUTION) ?></b> &mdash; <?= e(SCHOOL) ?></p>
  <p style="margin-top:6px">Department of <?= e(DEPARTMENT) ?> &middot; <em><?= e(MOTTO) ?></em></p>
  <p style="margin-top:12px;opacity:.7">&copy; <?= date('Y') ?> All rights reserved. Support: <?= e(SUPPORT_EMAIL) ?></p>
</footer>

</body>
</html>
