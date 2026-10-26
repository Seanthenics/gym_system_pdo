<?php
require_once 'dbConfig.php';

/* ===================== PARENT: TRAINERS ===================== */

function getAllTrainers($pdo) {
    $sql = "SELECT t.*, COUNT(c.client_id) AS total_clients
            FROM trainers t
            LEFT JOIN clients c ON c.trainer_id = t.trainer_id
            GROUP BY t.trainer_id
            ORDER BY t.trainer_id DESC";
    return $pdo->query($sql)->fetchAll();
}

function getTrainerById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE trainer_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function insertTrainer($pdo, $first, $last, $email, $phone, $specialty) {
    $stmt = $pdo->prepare("INSERT INTO trainers (first_name, last_name, email, phone_number, specialty)
                           VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$first, $last, $email, $phone, $specialty]);
}

function updateTrainer($pdo, $id, $first, $last, $email, $phone, $specialty) {
    $stmt = $pdo->prepare("UPDATE trainers
                           SET first_name = ?, last_name = ?, email = ?, phone_number = ?, specialty = ?
                           WHERE trainer_id = ?");
    return $stmt->execute([$first, $last, $email, $phone, $specialty, $id]);
}

function deleteTrainer($pdo, $id) {
    // ON DELETE CASCADE also removes this trainer's clients
    $stmt = $pdo->prepare("DELETE FROM trainers WHERE trainer_id = ?");
    return $stmt->execute([$id]);
}

/* ===================== CHILD: CLIENTS ===================== */

function getAllClients($pdo) {
    $sql = "SELECT c.*, t.first_name AS trainer_first, t.last_name AS trainer_last
            FROM clients c
            INNER JOIN trainers t ON t.trainer_id = c.trainer_id
            ORDER BY c.client_id DESC";
    return $pdo->query($sql)->fetchAll();
}

function getClientById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function insertClient($pdo, $trainerId, $first, $last, $age, $phone, $membership) {
    $stmt = $pdo->prepare("INSERT INTO clients (trainer_id, first_name, last_name, age, phone_number, membership_type)
                           VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$trainerId, $first, $last, $age, $phone, $membership]);
}

function updateClient($pdo, $id, $trainerId, $first, $last, $age, $phone, $membership) {
    $stmt = $pdo->prepare("UPDATE clients
                           SET trainer_id = ?, first_name = ?, last_name = ?, age = ?, phone_number = ?, membership_type = ?
                           WHERE client_id = ?");
    return $stmt->execute([$trainerId, $first, $last, $age, $phone, $membership, $id]);
}

function deleteClient($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM clients WHERE client_id = ?");
    return $stmt->execute([$id]);
}
