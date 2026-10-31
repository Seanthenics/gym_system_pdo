-- ALREADY HAVE trainers & clients? Run this ONLY ONCE instead of data.sql.
-- It keeps your old records and adds the user table + tracking columns.
USE gym_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address VARCHAR(255),
    age INT,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    date_registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Old rows have no owner, so added_by is allowed to be NULL here.
ALTER TABLE trainers
    ADD COLUMN added_by INT NULL,
    ADD COLUMN updated_by INT NULL,
    ADD COLUMN last_updated TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    ADD FOREIGN KEY (added_by) REFERENCES users(user_id),
    ADD FOREIGN KEY (updated_by) REFERENCES users(user_id);

ALTER TABLE clients
    ADD COLUMN added_by INT NULL,
    ADD COLUMN updated_by INT NULL,
    ADD COLUMN last_updated TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    ADD FOREIGN KEY (added_by) REFERENCES users(user_id),
    ADD FOREIGN KEY (updated_by) REFERENCES users(user_id);
