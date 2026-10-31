<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';
requireLogin();                         // not logged in -> redirected to login.php

$trainers = getTrainers($pdo);
$clients  = getClients($pdo);
$editTrainer = isset($_GET['edit_trainer']) ? getTrainer($pdo, (int)$_GET['edit_trainer']) : null;
$editClient  = isset($_GET['edit_client'])  ? getClient($pdo, (int)$_GET['edit_client'])   : null;
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$memberships = ['Basic','Standard','Premium'];
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gym Management System</title>
<link rel="stylesheet" href="css/style.css"></head>
<body>
<header class="hero">
  <nav>
    <span class="who">Logged in as <b><?= h($_SESSION['username']) ?></b></span>
    <a href="#trainers">Trainers</a><a href="#clients">Clients</a>
    <a href="logout.php" class="logout">Logout</a>
  </nav>
  <h1>Gym Management System</h1>
  <p><b>One trainer, many clients.</b> Register your coaches first, then assign every new member to the coach who will train them.</p>
  <div class="stats">
    <div><strong><?= count($trainers) ?></strong><span>Trainers on staff</span></div>
    <div><strong><?= count($clients) ?></strong><span>Clients registered</span></div>
  </div>
</header>
<div class="stripe"></div>

<main>
<?php if ($flash): ?><div class="alert ok"><?= h($flash) ?></div><?php endif; ?>

<!-- ================= TRAINERS (PARENT) ================= -->
<section id="trainers">
  <h2>Trainers <small>Parent: each trainer can have many clients</small></h2>
  <div class="grid">
    <form class="panel" method="post" action="core/handleForms.php">
      <h3><?= $editTrainer ? 'Edit trainer' : 'Add a trainer' ?></h3>
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="<?= $editTrainer ? 'update_trainer' : 'add_trainer' ?>">
      <?php if ($editTrainer): ?><input type="hidden" name="trainer_id" value="<?= $editTrainer['trainer_id'] ?>"><?php endif; ?>
      <div class="row">
        <label>First name <input name="first_name" required value="<?= h($editTrainer['first_name'] ?? '') ?>"></label>
        <label>Last name <input name="last_name" required value="<?= h($editTrainer['last_name'] ?? '') ?>"></label>
      </div>
      <label>Email <input type="email" name="email" required value="<?= h($editTrainer['email'] ?? '') ?>"></label>
      <label>Phone <input name="phone_number" value="<?= h($editTrainer['phone_number'] ?? '') ?>"></label>
      <label>Specialty <input name="specialty" value="<?= h($editTrainer['specialty'] ?? '') ?>"></label>
      <button class="btn"><?= $editTrainer ? 'Update trainer' : 'Add trainer' ?></button>
      <?php if ($editTrainer): ?><a class="btn ghost" href="index.php#trainers">Cancel</a><?php endif; ?>
    </form>

    <div class="tablewrap">
      <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Specialty</th><th>Clients</th>
            <th>Added by</th><th>Date added</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr>
        <?php if (!$trainers): ?><tr><td colspan="11" class="empty">No trainers yet. Use the form to add your first trainer.</td></tr><?php endif; ?>
        <?php foreach ($trainers as $t): ?>
        <tr>
          <td><?= $t['trainer_id'] ?></td>
          <td><?= h($t['first_name'].' '.$t['last_name']) ?></td>
          <td><?= h($t['email']) ?></td><td><?= h($t['phone_number']) ?></td><td><?= h($t['specialty']) ?></td>
          <td><?= $t['client_count'] ?></td>
          <td><?= h($t['added_by_name'] ?? '—') ?></td><td><?= h($t['date_added']) ?></td>
          <td><?= h($t['updated_by_name'] ?? '—') ?></td><td><?= h($t['last_updated'] ?? '—') ?></td>
          <td class="actions">
            <a class="btn sm" href="?edit_trainer=<?= $t['trainer_id'] ?>#trainers">Edit</a>
            <form method="post" action="core/handleForms.php" onsubmit="return confirm('Delete this trainer and all their clients?')">
              <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="delete_trainer">
              <input type="hidden" name="trainer_id" value="<?= $t['trainer_id'] ?>">
              <button class="btn sm danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</section>

<!-- ================= CLIENTS (CHILDREN) ================= -->
<section id="clients">
  <h2>Clients <small>Child: each client belongs to one trainer</small></h2>
  <div class="grid">
    <form class="panel" method="post" action="core/handleForms.php">
      <h3><?= $editClient ? 'Edit client' : 'Add a client' ?></h3>
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
      <input type="hidden" name="action" value="<?= $editClient ? 'update_client' : 'add_client' ?>">
      <?php if ($editClient): ?><input type="hidden" name="client_id" value="<?= $editClient['client_id'] ?>"><?php endif; ?>
      <label>Trainer
        <select name="trainer_id" required>
          <option value="">— choose a trainer —</option>
          <?php foreach ($trainers as $t): ?>
            <option value="<?= $t['trainer_id'] ?>" <?= ($editClient['trainer_id'] ?? '') == $t['trainer_id'] ? 'selected' : '' ?>>
              <?= h($t['first_name'].' '.$t['last_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="row">
        <label>First name <input name="first_name" required value="<?= h($editClient['first_name'] ?? '') ?>"></label>
        <label>Last name <input name="last_name" required value="<?= h($editClient['last_name'] ?? '') ?>"></label>
      </div>
      <div class="row">
        <label>Age <input type="number" min="1" name="age" value="<?= h($editClient['age'] ?? '') ?>"></label>
        <label>Phone <input name="phone_number" value="<?= h($editClient['phone_number'] ?? '') ?>"></label>
      </div>
      <label>Membership
        <select name="membership_type">
          <?php foreach ($memberships as $m): ?>
            <option <?= ($editClient['membership_type'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn"><?= $editClient ? 'Update client' : 'Add client' ?></button>
      <?php if ($editClient): ?><a class="btn ghost" href="index.php#clients">Cancel</a><?php endif; ?>
    </form>

    <div class="tablewrap">
      <table>
        <tr><th>ID</th><th>Name</th><th>Trainer</th><th>Age</th><th>Phone</th><th>Membership</th>
            <th>Added by</th><th>Date added</th><th>Last updated by</th><th>Last updated</th><th>Actions</th></tr>
        <?php if (!$clients): ?><tr><td colspan="11" class="empty">No clients yet.</td></tr><?php endif; ?>
        <?php foreach ($clients as $c): ?>
        <tr>
          <td><?= $c['client_id'] ?></td>
          <td><?= h($c['first_name'].' '.$c['last_name']) ?></td>
          <td><?= h($c['trainer_name']) ?></td><td><?= h($c['age']) ?></td>
          <td><?= h($c['phone_number']) ?></td><td><?= h($c['membership_type']) ?></td>
          <td><?= h($c['added_by_name'] ?? '—') ?></td><td><?= h($c['date_added']) ?></td>
          <td><?= h($c['updated_by_name'] ?? '—') ?></td><td><?= h($c['last_updated'] ?? '—') ?></td>
          <td class="actions">
            <a class="btn sm" href="?edit_client=<?= $c['client_id'] ?>#clients">Edit</a>
            <form method="post" action="core/handleForms.php" onsubmit="return confirm('Delete this client?')">
              <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
              <input type="hidden" name="action" value="delete_client">
              <input type="hidden" name="client_id" value="<?= $c['client_id'] ?>">
              <button class="btn sm danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</section>
</main>
</body></html>
