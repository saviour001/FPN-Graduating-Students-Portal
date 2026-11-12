<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(dashboard_url());
}

$errors = [];
if (is_post()) {
    verify_csrf();
    $identifier = input('identifier');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $errors[] = 'Please enter your login ID and password.';
    } else {
        $res = attempt_login($identifier, $password);
        if ($res['ok']) {
            clear_old();
            if (!empty($res['must_change_pw'])) {
                set_flash('warning', 'For security, please change your password.');
                redirect($res['role'] . '/change_password.php');
            }
            set_flash('success', 'Welcome back!');
            redirect(dashboard_url());
        } else {
            $errors[] = $res['error'];
        }
    }
    remember_old($_POST);
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In &middot; FPN <?= e(PORTAL_NAME) ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.jpg">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth">
  <div class="auth__hero">
    <div class="auth__logo">
      <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="FPN">
      <div><b><?= e(INSTITUTION) ?></b><span><?= e(SCHOOL) ?></span></div>
    </div>
    <div>
      <h2>Graduating Students Portal</h2>
      <p class="lead">Sign in to manage your clearance, final year project, results and alumni registration.</p>
      <ul class="auth__points">
        <li><span class="tick">&#10003;</span> Track clearance across every unit</li>
        <li><span class="tick">&#10003;</span> Submit and monitor your project</li>
        <li><span class="tick">&#10003;</span> View your CGPA and results</li>
      </ul>
    </div>
    <p class="foot"><?= e(MOTTO) ?></p>
  </div>

  <div class="auth__form">
    <div class="auth__card">
      <h3>Welcome back</h3>
      <p class="sub">Sign in with your matric number, staff ID or email.</p>

      <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><span class="ico">&#9679;</span><div><?= e($flash['msg']) ?></div></div><?php endif; ?>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger"><span class="ico">&#9679;</span><div><?= e($err) ?></div></div><?php endforeach; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-row">
          <label class="lbl" for="identifier">Login ID <span class="req">*</span></label>
          <input class="input" type="text" id="identifier" name="identifier" value="<?= old('identifier') ?>" placeholder="e.g. FPN/CS/21/1234" autofocus required>
        </div>
        <div class="form-row">
          <label class="lbl" for="password">Password <span class="req">*</span></label>
          <div style="position:relative">
            <input class="input" type="password" id="password" name="password" placeholder="Your password" required>
            <button type="button" class="btn btn-sm" data-toggle-pw="password" style="position:absolute;right:6px;top:5px;background:transparent;color:var(--brand)">Show</button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
      </form>

      <p class="auth__switch">New graduating student? <a href="<?= BASE_URL ?>/register.php">Create an account</a></p>
      <p class="auth__switch" style="margin-top:6px"><a href="<?= BASE_URL ?>/index.php">&larr; Back to home</a></p>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
