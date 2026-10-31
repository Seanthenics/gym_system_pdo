<?php
require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';

/* This file handles every POST form in the system */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../index.php'); exit; }
csrfCheck();
$action = $_POST['action'] ?? '';

/* ---------- REGISTER ---------- */
if ($action === 'register') {
    $d = array_map('trim', $_POST);
    $errors = [];
    foreach (['first_name','last_name','address','age','email','username','password'] as $k)
        if (($d[$k] ?? '') === '') $errors[] = ucfirst(str_replace('_',' ',$k)) . ' is required.';
    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (!ctype_digit($d['age'] ?? '') || (int)$d['age'] < 1) $errors[] = 'Age must be a positive number.';
    if (strlen($d['password'] ?? '') < 6) $errors[] = 'Password must be at least 6 characters.';
    if (($d['password'] ?? '') !== ($d['confirm'] ?? '')) $errors[] = 'Passwords do not match.';
    if (!$errors && userExists($pdo, $d['username'], $d['email'])) $errors[] = 'Username or email already taken.';

    if ($errors) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $d;
        header('Location: ../register.php'); exit;
    }
    registerUser($pdo, $d);
    $_SESSION['flash'] = 'Registration successful! You can now log in.';
    header('Location: ../login.php'); exit;
}

/* ---------- LOGIN ---------- */
if ($action === 'login') {
    $u = findUserByUsername($pdo, trim($_POST['username'] ?? ''));
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['username'] = $u['username'];
        header('Location: ../index.php'); exit;
    }
    $_SESSION['errors'] = ['Invalid username or password.'];
    header('Location: ../login.php'); exit;
}

/* ---------- Everything below needs a logged-in user ---------- */
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
$uid = $_SESSION['user_id'];

function clean($post, $keys) {
    $out = [];
    foreach ($keys as $k) $out[$k] = trim($post[$k] ?? '');
    return $out;
}
function back($msg, $anchor) {
    $_SESSION['flash'] = $msg;
    header('Location: ../index.php#' . $anchor); exit;
}

try {
    switch ($action) {
        /* ----- TRAINERS ----- */
        case 'add_trainer':
            $d = clean($_POST, ['first_name','last_name','email','phone_number','specialty']);
            if ($d['first_name']==='' || $d['last_name']==='' || $d['email']==='') back('Please fill in the required trainer fields.', 'trainers');
            addTrainer($pdo, $d, $uid);
            back('Trainer added.', 'trainers');
        case 'update_trainer':
            $d = clean($_POST, ['first_name','last_name','email','phone_number','specialty']);
            updateTrainer($pdo, (int)$_POST['trainer_id'], $d, $uid);
            back('Trainer updated.', 'trainers');
        case 'delete_trainer':
            deleteTrainer($pdo, (int)$_POST['trainer_id']);
            back('Trainer deleted (their clients were removed too).', 'trainers');

        /* ----- CLIENTS ----- */
        case 'add_client':
            $d = clean($_POST, ['trainer_id','first_name','last_name','age','phone_number','membership_type']);
            if ($d['trainer_id']==='' || $d['first_name']==='' || $d['last_name']==='') back('Please fill in the required client fields.', 'clients');
            addClient($pdo, $d, $uid);
            back('Client added.', 'clients');
        case 'update_client':
            $d = clean($_POST, ['trainer_id','first_name','last_name','age','phone_number','membership_type']);
            updateClient($pdo, (int)$_POST['client_id'], $d, $uid);
            back('Client updated.', 'clients');
        case 'delete_client':
            deleteClient($pdo, (int)$_POST['client_id']);
            back('Client deleted.', 'clients');
    }
} catch (PDOException $e) {
    back('Database error: ' . $e->getMessage(), 'trainers');
}
header('Location: ../index.php');
