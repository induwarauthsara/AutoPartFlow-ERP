<?php
/**
 * Run this ONCE from the command line after importing schema.sql:
 *      php database/seed.php
 * Creates the default admin login:
 *      email: admin@autopartflow.com
 *      password: Admin@123
 * (change the password immediately after first login)
 */
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance();

$existing = $db->selectOne("SELECT user_id FROM users WHERE email = ?", 's', ['admin@autopartflow.com']);
if ($existing) {
    echo "Admin user already exists — nothing to do.\n";
    exit;
}

$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$db->execute(
    "INSERT INTO users (full_name, email, password_hash, role_id, status) VALUES (?,?,?,?, 'active')",
    'sssi',
    ['Alex Rivera', 'admin@autopartflow.com', $hash, 1]
);

echo "Admin user created:\n  email: admin@autopartflow.com\n  password: Admin@123\n";
