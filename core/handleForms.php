<?php
session_start();
require_once 'models.php';

function clean($v) { return trim($v ?? ''); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {

            /* ---------- TRAINERS (PARENT) ---------- */
            case 'add_trainer':
                insertTrainer($pdo, clean($_POST['first_name']), clean($_POST['last_name']),
                              clean($_POST['email']), clean($_POST['phone_number']), clean($_POST['specialty']));
                $_SESSION['msg'] = 'Trainer added successfully.';
                break;

            case 'update_trainer':
                updateTrainer($pdo, (int)$_POST['trainer_id'], clean($_POST['first_name']), clean($_POST['last_name']),
                              clean($_POST['email']), clean($_POST['phone_number']), clean($_POST['specialty']));
                $_SESSION['msg'] = 'Trainer updated successfully.';
                break;

            case 'delete_trainer':
                deleteTrainer($pdo, (int)$_POST['trainer_id']);
                $_SESSION['msg'] = 'Trainer (and their clients) deleted successfully.';
                break;

            /* ---------- CLIENTS (CHILD) ---------- */
            case 'add_client':
                insertClient($pdo, (int)$_POST['trainer_id'], clean($_POST['first_name']), clean($_POST['last_name']),
                             (int)$_POST['age'], clean($_POST['phone_number']), clean($_POST['membership_type']));
                $_SESSION['msg'] = 'Client added successfully.';
                break;

            case 'update_client':
                updateClient($pdo, (int)$_POST['client_id'], (int)$_POST['trainer_id'], clean($_POST['first_name']),
                             clean($_POST['last_name']), (int)$_POST['age'], clean($_POST['phone_number']),
                             clean($_POST['membership_type']));
                $_SESSION['msg'] = 'Client updated successfully.';
                break;

            case 'delete_client':
                deleteClient($pdo, (int)$_POST['client_id']);
                $_SESSION['msg'] = 'Client deleted successfully.';
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['msg'] = ($e->getCode() == 23000)
            ? 'Error: that email is already registered.'
            : 'Database error: ' . $e->getMessage();
    }
}

header('Location: ../index.php');
exit;
