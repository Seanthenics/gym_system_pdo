<?php
require_once 'core/dbConfig.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']);
$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
function old($k) { global $old; return h($old[$k] ?? ''); }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register | Gym Management System</title>
<link rel="stylesheet" href="css/style.css"></head>
<body class="auth-page">
<div class="auth-card">
  <h1>Gym Management<br>System</h1>
  <h2>Register</h2>
  <?php foreach ($errors as $e): ?><div class="alert err"><?= h($e) ?></div><?php endforeach; ?>
  <form method="post" action="core/handleForms.php">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="register">
    <div class="row">
      <label>First name <input name="first_name" value="<?= old('first_name') ?>" required></label>
      <label>Last name <input name="last_name" value="<?= old('last_name') ?>" required></label>
    </div>
    <label>Address <input name="address" value="<?= old('address') ?>" required></label>
    <div class="row">
      <label>Age <input type="number" min="1" name="age" value="<?= old('age') ?>" required></label>
      <label>Email <input type="email" name="email" value="<?= old('email') ?>" required></label>
    </div>
    <label>Username <input name="username" value="<?= old('username') ?>" required></label>
    <div class="row">
      <label>Password <input type="password" name="password" required></label>
      <label>Confirm password <input type="password" name="confirm" required></label>
    </div>
    <button class="btn">Create account</button>
  </form>
  <p>Already registered? <a href="login.php">Login</a></p>
</div>
</body></html>
