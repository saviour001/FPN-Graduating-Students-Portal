<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect(dashboard_url());
}

$programmes = [
    'ND Computer Science'               => 'ND II',
    'HND Software & Web Development'     => 'HND II',
    'HND Networking & Cloud Computing'  => 'HND II',
];

$errors = [];
if (is_post()) {
    verify_csrf();

    $fullName  = input('full_name');
    $matric    = strtoupper(input('matric_no'));
    $email     = strtolower(input('email'));
    $phone     = input('phone');
    $programme = input('programme');
    $pw        = $_POST['password'] ?? '';
    $pw2       = $_POST['password2'] ?? '';

    if ($fullName === '' || mb_strlen($fullName) < 3)      $errors['full_name'] = 'Enter your full name.';
    if (!valid_matric($matric))                            $errors['matric_no'] = 'Enter a valid matriculation number (e.g. FPN/CS/21/1234).';
    if (!valid_email($email))                              $errors['email'] = 'Enter a valid email address.';
    if ($phone !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $phone)) $errors['phone'] = 'Enter a valid phone number.';
    if (!array_key_exists($programme, $programmes))        $errors['programme'] = 'Select your programme.';
    if (strlen($pw) < PW_MIN_LENGTH)                       $errors['password'] = 'Password must be at least ' . PW_MIN_LENGTH . ' characters.';
    if ($pw !== $pw2)                                       $errors['password2'] = 'Passwords do not match.';

    // Uniqueness checks
    if (!isset($errors['matric_no']) || !isset($errors['email'])) {
        $stmt = db()->prepare('SELECT matric_no, email FROM users WHERE matric_no = ? OR email = ?');
        $stmt->execute([$matric, $email]);
        foreach ($stmt->fetchAll() as $row) {
            if (strcasecmp($row['matric_no'] ?? '', $matric) === 0) $errors['matric_no'] = 'This matric number is already registered.';
            if (strcasecmp($row['email'] ?? '', $email) === 0)      $errors['email'] = 'This email is already registered.';
        }
    }

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            $ins = $pdo->prepare(
                'INSERT INTO users (role, matric_no, email, password_hash, full_name, phone)
                 VALUES ("student", ?, ?, ?, ?, ?)'
            );
            $ins->execute([$matric, $email, password_hash($pw, PASSWORD_DEFAULT), $fullName, $phone ?: null]);
            $newId = (int) $pdo->lastInsertId();

            $prof = $pdo->prepare(
                'INSERT INTO student_profiles (user_id, programme, level) VALUES (?, ?, ?)'
            );
            $prof->execute([$newId, $programme, $programmes[$programme]]);

            // Auto-create clearance rows for every active unit
            $units = $pdo->query('SELECT id FROM clearance_units WHERE is_active = 1')->fetchAll();
            $clr = $pdo->prepare('INSERT INTO clearance_requests (user_id, unit_id) VALUES (?, ?)');
            foreach ($units as $unit) {
                $clr->execute([$newId, $unit['id']]);
            }

            $pdo->commit();
            log_activity($newId, 'Registered account');
            clear_old();
            set_flash('success', 'Account created successfully. Please sign in.');
            redirect('login.php');
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors['general'] = APP_DEBUG ? $ex->getMessage() : 'Registration failed. Please try again.';
        }
    }
    remember_old($_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register &middot; FPN <?= e(PORTAL_NAME) ?></title>
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
      <h2>Start your graduation journey</h2>
      <p class="lead">Create your account to begin clearance, submit your final year project and register as an alumnus of the <?= e(DEPARTMENT) ?> Department.</p>
      <ul class="auth__points">
        <li><span class="tick">&#10003;</span> One account for the whole process</li>
        <li><span class="tick">&#10003;</span> Real-time clearance tracking</li>
        <li><span class="tick">&#10003;</span> Secure document uploads</li>
      </ul>
    </div>
    <p class="foot"><?= e(MOTTO) ?></p>
  </div>

  <div class="auth__form">
    <div class="auth__card">
      <h3>Create your account</h3>
      <p class="sub">For graduating (finalist) Computer Science students only.</p>

      <?php if (!empty($errors['general'])): ?><div class="alert alert-danger"><span class="ico">&#9679;</span><div><?= e($errors['general']) ?></div></div><?php endif; ?>

      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-row">
          <label class="lbl" for="full_name">Full name <span class="req">*</span></label>
          <input class="input <?= isset($errors['full_name']) ? 'is-error' : '' ?>" id="full_name" name="full_name" value="<?= old('full_name') ?>" required>
          <?php if (isset($errors['full_name'])): ?><div class="field-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
        </div>
        <div class="form-grid">
          <div class="form-row">
            <label class="lbl" for="matric_no">Matric number <span class="req">*</span></label>
            <input class="input <?= isset($errors['matric_no']) ? 'is-error' : '' ?>" id="matric_no" name="matric_no" value="<?= old('matric_no') ?>" placeholder="FPN/CS/21/1234" required>
            <?php if (isset($errors['matric_no'])): ?><div class="field-error"><?= e($errors['matric_no']) ?></div><?php endif; ?>
          </div>
          <div class="form-row">
            <label class="lbl" for="phone">Phone</label>
            <input class="input <?= isset($errors['phone']) ? 'is-error' : '' ?>" id="phone" name="phone" value="<?= old('phone') ?>" placeholder="0803...">
            <?php if (isset($errors['phone'])): ?><div class="field-error"><?= e($errors['phone']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <label class="lbl" for="email">Email <span class="req">*</span></label>
          <input class="input <?= isset($errors['email']) ? 'is-error' : '' ?>" type="email" id="email" name="email" value="<?= old('email') ?>" required>
          <?php if (isset($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="form-row">
          <label class="lbl" for="programme">Programme <span class="req">*</span></label>
          <select class="select <?= isset($errors['programme']) ? 'is-error' : '' ?>" id="programme" name="programme" required>
            <option value="">-- Select programme --</option>
            <?php foreach (array_keys($programmes) as $p): ?>
              <option value="<?= e($p) ?>" <?= (($_SESSION['_old']['programme'] ?? '') === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['programme'])): ?><div class="field-error"><?= e($errors['programme']) ?></div><?php endif; ?>
        </div>
        <div class="form-grid">
          <div class="form-row">
            <label class="lbl" for="password">Password <span class="req">*</span></label>
            <input class="input <?= isset($errors['password']) ? 'is-error' : '' ?>" type="password" id="password" name="password" required>
            <?php if (isset($errors['password'])): ?><div class="field-error"><?= e($errors['password']) ?></div><?php endif; ?>
          </div>
          <div class="form-row">
            <label class="lbl" for="password2">Confirm password <span class="req">*</span></label>
            <input class="input <?= isset($errors['password2']) ? 'is-error' : '' ?>" type="password" id="password2" name="password2" required>
            <?php if (isset($errors['password2'])): ?><div class="field-error"><?= e($errors['password2']) ?></div><?php endif; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-8">Create Account</button>
      </form>

      <p class="auth__switch">Already registered? <a href="<?= BASE_URL ?>/login.php">Sign in</a></p>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
