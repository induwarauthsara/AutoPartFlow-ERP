-- AutoPartFlow ERP Database Schema
-- Run this in MySQL to set up the database

CREATE DATABASE IF NOT EXISTS autopartflow_erp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE autopartflow_erp;

CREATE TABLE IF NOT EXISTS parts (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    part_number VARCHAR(50)  NOT NULL UNIQUE,
    name        VARCHAR(150) NOT NULL,
    category    VARCHAR(100) NOT NULL,
    price       DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity    INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO parts (part_number, name, category, price, quantity) VALUES
    ('BRK-001', 'Front Brake Pad Set', 'Brakes', 45.99, 120),
    ('FLT-002', 'Oil Filter', 'Filters', 12.50, 300),
    ('SPK-003', 'Spark Plug (Iridium)', 'Ignition', 8.75, 500),
    ('ALT-004', 'Alternator 120A', 'Electrical', 189.00, 25),
    ('SHK-005', 'Front Shock Absorber', 'Suspension', 75.00, 60);
