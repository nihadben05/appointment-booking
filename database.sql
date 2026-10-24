CREATE DATABASE IF NOT EXISTS appointment_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE appointment_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role ENUM('manager', 'client') NOT NULL DEFAULT 'client',
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    age TINYINT UNSIGNED NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointment_slots (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `time` TIME NOT NULL,
    is_booked TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_slot (`date`, `time`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    slot_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_appointments_user (user_id),
    KEY idx_appointments_slot (slot_id),
    KEY idx_appointments_status (status),
    CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_slot FOREIGN KEY (slot_id) REFERENCES appointment_slots (id) ON DELETE CASCADE
) ENGINE=InnoDB;
