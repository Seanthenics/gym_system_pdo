<?php
session_start();
require_once 'core/models.php';

$trainers = getAllTrainers($pdo);
$clients  = getAllClients($pdo);

// Edit mode (loads the record into the form)
$editTrainer = isset($_GET['edit_trainer']) ? getTrainerById($pdo, (int)$_GET['edit_trainer']) : null;
$editClient  = isset($_GET['edit_client'])  ? getClientById($pdo, (int)$_GET['edit_client'])   : null;

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gym Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --concrete: #e3e5e8;
        --chalk:    #f6f7f8;
        --steel:    #1c2733;
        --steel-2:  #2b3a4a;
        --strap:    #f2c230;
        --strap-dk: #c99a0a;
        --rule:     #c4c9cf;
        --muted:    #5d6a77;
        --danger:   #b3261e;
        --ok:       #1f6b45;
        --head: "Barlow Condensed", "Arial Narrow", Impact, sans-serif;
        --body: "Barlow", "Segoe UI", Arial, sans-serif;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body { margin: 0; background: var(--concrete); color: var(--steel); font-family: var(--body); font-size: 16px; line-height: 1.5; }

    /* ---------- Header ---------- */
    .masthead { background: var(--steel); color: #fff; position: relative; padding: 34px 6vw 30px; }
    .masthead::after { /* barbell-plate stripe */
        content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 10px;
        background: repeating-linear-gradient(90deg, var(--strap) 0 46px, var(--steel) 46px 56px);
    }
    .topline { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
    .db-status { font-size: 14px; color: #b8c4d0; display: flex; align-items: center; gap: 8px; margin: 0; }
    .db-status::before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: #4ad48a; }
    .nav a { color: #fff; text-decoration: none; font-family: var(--head); font-weight: 600; font-size: 19px; margin-left: 22px; padding-bottom: 3px; border-bottom: 3px solid transparent; }
    .nav a:hover { border-bottom-color: var(--strap); }
    h1 { font-family: var(--head); font-weight: 800; font-size: clamp(46px, 8vw, 96px); line-height: .92; margin: 26px 0 14px; letter-spacing: -.5px; }
    .lede { max-width: 56ch; font-size: 18px; color: #d3dbe3; margin: 0; }
    .lede b { color: var(--strap); font-weight: 600; }
    .totals { display: flex; gap: 14px; margin-top: 26px; flex-wrap: wrap; }
    .total { border: 1px solid #3c4d5f; padding: 8px 18px 8px 14px; border-left: 5px solid var(--strap); min-width: 150px; }
    .total strong { display: block; font-family: var(--head); font-size: 42px; line-height: 1; }
    .total span { font-size: 14px; color: #b8c4d0; }

    /* ---------- Layout ---------- */
    main { padding: 34px 6vw 60px; max-width: 1500px; margin: 0 auto; }
    section { margin-bottom: 56px; scroll-margin-top: 16px; }
    .section-head { display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap; border-bottom: 3px solid var(--steel); padding-bottom: 8px; margin-bottom: 22px; }
    h2 { font-family: var(--head); font-weight: 800; font-size: 40px; line-height: 1; margin: 0; }
    .tag { font-size: 14px; color: var(--muted); }
    .split { display: grid; grid-template-columns: 290px minmax(0, 1fr); gap: 28px; align-items: start; }
    @media (max-width: 1240px) { .split { grid-template-columns: 1fr; } }

    .notice { background: var(--chalk); border-left: 6px solid var(--ok); padding: 12px 16px; margin-bottom: 28px; font-weight: 500; }
    .notice.err { border-left-color: var(--danger); }

    /* ---------- Form panel ---------- */
    .panel { background: var(--chalk); border: 1px solid var(--rule); border-top: 6px solid var(--steel); padding: 20px 20px 22px; }
    .panel.editing { border-top-color: var(--strap); box-shadow: 0 0 0 2px var(--strap); }
    .panel h3 { font-family: var(--head); font-size: 26px; margin: 0 0 14px; }
    .panel .hint { color: var(--muted); font-size: 14px; margin: -8px 0 14px; }
    label { display: block; font-weight: 600; font-size: 14px; margin: 12px 0 4px; }
    input[type=text], input[type=email], input[type=number], select {
        width: 100%; padding: 10px 11px; font: inherit; color: var(--steel);
        background: #fff; border: 1px solid #9aa5b0; border-radius: 2px;
    }
    input:focus, select:focus, button:focus-visible, a:focus-visible { outline: 3px solid var(--strap); outline-offset: 1px; border-color: var(--steel); }
    .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .actions { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }

    .btn { font-family: var(--head); font-weight: 700; font-size: 19px; letter-spacing: .3px; padding: 9px 20px; border: 2px solid var(--steel); background: var(--strap); color: var(--steel); cursor: pointer; text-decoration: none; display: inline-block; line-height: 1.2; border-radius: 2px; }
    .btn:hover { background: var(--strap-dk); }
    .btn.ghost { background: transparent; }
    .btn.ghost:hover { background: var(--steel); color: #fff; }
    .btn.sm { font-size: 16px; padding: 4px 12px; }
    .btn.danger { background: transparent; border-color: var(--danger); color: var(--danger); }
    .btn.danger:hover { background: var(--danger); color: #fff; }
    form.inline { display: inline; }

    /* ---------- Tables ---------- */
    .table-wrap { overflow-x: auto; background: var(--chalk); border: 1px solid var(--rule); }
    table.list { border-collapse: collapse; width: 100%; min-width: 700px; }
    table.list th { background: var(--steel); color: #fff; font-family: var(--head); font-weight: 600; font-size: 18px; text-align: left; padding: 10px 9px; white-space: nowrap; }
    table.list td { padding: 11px 9px; border-bottom: 1px solid var(--rule); vertical-align: middle; }
    table.list tbody tr:nth-child(even) { background: #eceef0; }
    table.list tbody tr:hover { background: #fbefc0; }
    td.id { font-family: var(--head); font-weight: 700; font-size: 20px; color: var(--muted); }
    td.nowrap { white-space: nowrap; }
    td.date { color: var(--muted); font-size: 14px; white-space: nowrap; }
    td.acts { white-space: nowrap; }
    td.acts .btn { margin-right: 2px; }
    @media (min-width: 1241px) { .panel { position: sticky; top: 16px; } }
    .count { display: inline-block; min-width: 34px; text-align: center; font-family: var(--head); font-weight: 800; font-size: 20px; background: var(--steel); color: var(--strap); padding: 0 8px; border-radius: 2px; }
    .badge { display: inline-block; font-size: 13px; font-weight: 600; padding: 2px 9px; border: 1.5px solid var(--steel-2); border-radius: 20px; }
    .badge.Yearly { background: var(--steel); color: #fff; }
    .badge.Quarterly { background: var(--strap); border-color: var(--strap-dk); }
    .coach { display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; }
    .coach i { font-style: normal; font-family: var(--head); font-weight: 800; background: var(--steel); color: var(--strap); width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; font-size: 15px; }
    .empty { padding: 36px 20px; text-align: center; color: var(--muted); }
    .empty strong { display: block; font-family: var(--head); font-size: 26px; color: var(--steel); }
</style>
</head>
<body>

<header class="masthead">
    <div class="topline">
        <p class="db-status"><?= h($dbMessage) ?></p>
        <nav class="nav"><a href="#trainers">Trainers</a><a href="#clients">Clients</a></nav>
    </div>
    <h1>Gym Management<br>System</h1>
    <p class="lede"><b>One trainer, many clients.</b> Register your coaches first, then assign every new member to the coach who will train them.</p>
    <div class="totals">
        <div class="total"><strong><?= count($trainers) ?></strong><span>Trainers on staff</span></div>
        <div class="total"><strong><?= count($clients) ?></strong><span>Clients registered</span></div>
    </div>
</header>

<main>
<?php if ($msg): ?>
    <div class="notice <?= stripos($msg, 'error') !== false ? 'err' : '' ?>" role="status"><?= h($msg) ?></div>
<?php endif; ?>

<!-- =========================== PARENT: TRAINERS =========================== -->
<section id="trainers">
    <div class="section-head"><h2>Trainers</h2><span class="tag">Parent: each trainer can have many clients</span></div>
    <div class="split">
        <form class="panel <?= $editTrainer ? 'editing' : '' ?>" action="core/handleForms.php" method="POST">
            <h3><?= $editTrainer ? 'Edit trainer' : 'Add a trainer' ?></h3>
            <input type="hidden" name="action" value="<?= $editTrainer ? 'update_trainer' : 'add_trainer' ?>">
            <?php if ($editTrainer): ?><input type="hidden" name="trainer_id" value="<?= (int)$editTrainer['trainer_id'] ?>"><?php endif; ?>

            <div class="row2">
                <div><label for="t_first">First name</label><input id="t_first" type="text" name="first_name" required value="<?= h($editTrainer['first_name'] ?? '') ?>"></div>
                <div><label for="t_last">Last name</label><input id="t_last" type="text" name="last_name" required value="<?= h($editTrainer['last_name'] ?? '') ?>"></div>
            </div>
            <label for="t_email">Email</label><input id="t_email" type="email" name="email" required value="<?= h($editTrainer['email'] ?? '') ?>">
            <label for="t_phone">Phone number</label><input id="t_phone" type="text" name="phone_number" value="<?= h($editTrainer['phone_number'] ?? '') ?>">
            <label for="t_spec">Specialty</label>
            <select id="t_spec" name="specialty">
                <?php foreach (['Weight Training','Cardio','Yoga','Boxing','CrossFit','Zumba'] as $s): ?>
                    <option <?= (($editTrainer['specialty'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <div class="actions">
                <button type="submit" class="btn"><?= $editTrainer ? 'Save changes' : 'Add trainer' ?></button>
                <?php if ($editTrainer): ?><a class="btn ghost" href="index.php#trainers">Cancel</a><?php endif; ?>
            </div>
        </form>

        <div class="table-wrap">
            <table class="list">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Specialty</th><th>Clients</th><th>Date added</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($trainers as $t): ?>
                <tr>
                    <td class="id"><?= $t['trainer_id'] ?></td>
                    <td class="nowrap"><span class="coach"><i><?= h(strtoupper(substr($t['first_name'],0,1) . substr($t['last_name'],0,1))) ?></i><?= h($t['first_name'] . ' ' . $t['last_name']) ?></span></td>
                    <td><?= h($t['email']) ?></td>
                    <td class="nowrap"><?= h($t['phone_number']) ?></td>
                    <td><?= h($t['specialty']) ?></td>
                    <td><span class="count"><?= (int)$t['total_clients'] ?></span></td>
                    <td class="date"><?= h(date('M d, Y', strtotime($t['date_added']))) ?></td>
                    <td class="acts">
                        <a class="btn ghost sm" href="index.php?edit_trainer=<?= $t['trainer_id'] ?>#trainers">Edit</a>
                        <form class="inline" action="core/handleForms.php" method="POST"
                              onsubmit="return confirm('Delete this trainer? All of their clients will also be deleted.');">
                            <input type="hidden" name="action" value="delete_trainer">
                            <input type="hidden" name="trainer_id" value="<?= $t['trainer_id'] ?>">
                            <button type="submit" class="btn danger sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!$trainers): ?>
                <div class="empty"><strong>No trainers yet</strong>Use the form to add your first trainer.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- =========================== CHILD: CLIENTS =========================== -->
<section id="clients">
    <div class="section-head"><h2>Clients</h2><span class="tag">Child: each client has exactly one trainer</span></div>
    <div class="split">
        <?php if (!$trainers): ?>
            <div class="panel"><h3>Add a client</h3><p class="hint">Add a trainer first. Every client needs a trainer assigned.</p></div>
        <?php else: ?>
        <form class="panel <?= $editClient ? 'editing' : '' ?>" action="core/handleForms.php" method="POST">
            <h3><?= $editClient ? 'Edit client' : 'Add a client' ?></h3>
            <input type="hidden" name="action" value="<?= $editClient ? 'update_client' : 'add_client' ?>">
            <?php if ($editClient): ?><input type="hidden" name="client_id" value="<?= (int)$editClient['client_id'] ?>"><?php endif; ?>

            <label for="c_trainer">Assigned trainer</label>
            <select id="c_trainer" name="trainer_id" required>
                <option value="">Select a trainer</option>
                <?php foreach ($trainers as $t): ?>
                    <option value="<?= $t['trainer_id'] ?>" <?= (($editClient['trainer_id'] ?? '') == $t['trainer_id']) ? 'selected' : '' ?>>
                        <?= h($t['first_name'] . ' ' . $t['last_name']) ?> (<?= h($t['specialty']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="row2">
                <div><label for="c_first">First name</label><input id="c_first" type="text" name="first_name" required value="<?= h($editClient['first_name'] ?? '') ?>"></div>
                <div><label for="c_last">Last name</label><input id="c_last" type="text" name="last_name" required value="<?= h($editClient['last_name'] ?? '') ?>"></div>
            </div>
            <div class="row2">
                <div><label for="c_age">Age</label><input id="c_age" type="number" name="age" min="10" max="100" required value="<?= h($editClient['age'] ?? '') ?>"></div>
                <div><label for="c_phone">Phone number</label><input id="c_phone" type="text" name="phone_number" value="<?= h($editClient['phone_number'] ?? '') ?>"></div>
            </div>
            <label for="c_mem">Membership</label>
            <select id="c_mem" name="membership_type">
                <?php foreach (['Monthly','Quarterly','Yearly'] as $m): ?>
                    <option <?= (($editClient['membership_type'] ?? '') === $m) ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
            <div class="actions">
                <button type="submit" class="btn"><?= $editClient ? 'Save changes' : 'Add client' ?></button>
                <?php if ($editClient): ?><a class="btn ghost" href="index.php#clients">Cancel</a><?php endif; ?>
            </div>
        </form>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="list">
                <thead><tr><th>ID</th><th>Name</th><th>Age</th><th>Phone</th><th>Membership</th><th>Trainer</th><th>Date added</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td class="id"><?= $c['client_id'] ?></td>
                    <td class="nowrap"><?= h($c['first_name'] . ' ' . $c['last_name']) ?></td>
                    <td><?= (int)$c['age'] ?></td>
                    <td class="nowrap"><?= h($c['phone_number']) ?></td>
                    <td><span class="badge <?= h($c['membership_type']) ?>"><?= h($c['membership_type']) ?></span></td>
                    <td><span class="coach"><i><?= h(strtoupper(substr($c['trainer_first'],0,1) . substr($c['trainer_last'],0,1))) ?></i><?= h($c['trainer_first'] . ' ' . $c['trainer_last']) ?></span></td>
                    <td class="date"><?= h(date('M d, Y', strtotime($c['date_added']))) ?></td>
                    <td class="acts">
                        <a class="btn ghost sm" href="index.php?edit_client=<?= $c['client_id'] ?>#clients">Edit</a>
                        <form class="inline" action="core/handleForms.php" method="POST" onsubmit="return confirm('Delete this client?');">
                            <input type="hidden" name="action" value="delete_client">
                            <input type="hidden" name="client_id" value="<?= $c['client_id'] ?>">
                            <button type="submit" class="btn danger sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!$clients): ?>
                <div class="empty"><strong>No clients yet</strong>Add a client and assign them to a trainer.</div>
            <?php endif; ?>
        </div>
    </div>
</section>
</main>

</body>
</html>
