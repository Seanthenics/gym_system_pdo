<?php
/* ---------- USERS ---------- */
function registerUser($pdo, $d) {
    $sql = "INSERT INTO users (first_name,last_name,address,age,email,username,password_hash)
            VALUES (?,?,?,?,?,?,?)";
    return $pdo->prepare($sql)->execute([
        $d['first_name'], $d['last_name'], $d['address'], $d['age'],
        $d['email'], $d['username'], password_hash($d['password'], PASSWORD_DEFAULT)
    ]);
}
function userExists($pdo, $username, $email) {
    $s = $pdo->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
    $s->execute([$username, $email]);
    return (bool)$s->fetch();
}
function findUserByUsername($pdo, $username) {
    $s = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $s->execute([$username]);
    return $s->fetch();
}

/* ---------- TRAINERS (parent) ---------- */
function getTrainers($pdo) {
    $sql = "SELECT t.*, a.username AS added_by_name, u.username AS updated_by_name,
                   (SELECT COUNT(*) FROM clients c WHERE c.trainer_id = t.trainer_id) AS client_count
            FROM trainers t
            LEFT JOIN users a ON a.user_id = t.added_by
            LEFT JOIN users u ON u.user_id = t.updated_by
            ORDER BY t.trainer_id DESC";
    return $pdo->query($sql)->fetchAll();
}
function getTrainer($pdo, $id) {
    $s = $pdo->prepare("SELECT * FROM trainers WHERE trainer_id=?");
    $s->execute([$id]);
    return $s->fetch();
}
function addTrainer($pdo, $d, $uid) {
    $sql = "INSERT INTO trainers (first_name,last_name,email,phone_number,specialty,added_by)
            VALUES (?,?,?,?,?,?)";
    return $pdo->prepare($sql)->execute([$d['first_name'],$d['last_name'],$d['email'],
                                         $d['phone_number'],$d['specialty'],$uid]);
}
function updateTrainer($pdo, $id, $d, $uid) {
    $sql = "UPDATE trainers SET first_name=?,last_name=?,email=?,phone_number=?,specialty=?,updated_by=?
            WHERE trainer_id=?";
    return $pdo->prepare($sql)->execute([$d['first_name'],$d['last_name'],$d['email'],
                                         $d['phone_number'],$d['specialty'],$uid,$id]);
}
function deleteTrainer($pdo, $id) {
    return $pdo->prepare("DELETE FROM trainers WHERE trainer_id=?")->execute([$id]);
}

/* ---------- CLIENTS (children) ---------- */
function getClients($pdo) {
    $sql = "SELECT c.*, CONCAT(t.first_name,' ',t.last_name) AS trainer_name,
                   a.username AS added_by_name, u.username AS updated_by_name
            FROM clients c
            JOIN trainers t ON t.trainer_id = c.trainer_id
            LEFT JOIN users a ON a.user_id = c.added_by
            LEFT JOIN users u ON u.user_id = c.updated_by
            ORDER BY c.client_id DESC";
    return $pdo->query($sql)->fetchAll();
}
function getClient($pdo, $id) {
    $s = $pdo->prepare("SELECT * FROM clients WHERE client_id=?");
    $s->execute([$id]);
    return $s->fetch();
}
function addClient($pdo, $d, $uid) {
    $sql = "INSERT INTO clients (trainer_id,first_name,last_name,age,phone_number,membership_type,added_by)
            VALUES (?,?,?,?,?,?,?)";
    return $pdo->prepare($sql)->execute([$d['trainer_id'],$d['first_name'],$d['last_name'],
                                         $d['age'] ?: null,$d['phone_number'],$d['membership_type'],$uid]);
}
function updateClient($pdo, $id, $d, $uid) {
    $sql = "UPDATE clients SET trainer_id=?,first_name=?,last_name=?,age=?,phone_number=?,
            membership_type=?,updated_by=? WHERE client_id=?";
    return $pdo->prepare($sql)->execute([$d['trainer_id'],$d['first_name'],$d['last_name'],
                                         $d['age'] ?: null,$d['phone_number'],$d['membership_type'],$uid,$id]);
}
function deleteClient($pdo, $id) {
    return $pdo->prepare("DELETE FROM clients WHERE client_id=?")->execute([$id]);
}
