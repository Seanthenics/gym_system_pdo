<?php
require_once 'core/dbConfig.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']);
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Gym Management System</title>
<link rel="stylesheet" href="css/style.css"></head>
<body class="auth-page">
<div class="auth-card">
  <h1>Gym Management<br>System</h1>
  <h2>Login</h2>
  <?php if ($flash): ?><div class="alert ok"><?= h($flash) ?></div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="alert err"><?= h($e) ?></div><?php endforeach; ?>
  <form method="post" action="core/handleForms.php">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="login">
    <label>Username <input name="username" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn">Login</button>
  </form>
  <p>No account yet? <a href="register.php">Register</a></p>
</div>
</body></html>
