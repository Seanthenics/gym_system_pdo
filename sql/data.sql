CREATE DATABASE IF NOT EXISTS gym_db;
USE gym_db;

-- PARENT table: ONE trainer
CREATE TABLE IF NOT EXISTS trainers (
    trainer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    specialty VARCHAR(100),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CHILD table: MANY clients (each client has exactly ONE trainer)
CREATE TABLE IF NOT EXISTS clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    age INT,
    phone_number VARCHAR(20),
    membership_type VARCHAR(20),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
