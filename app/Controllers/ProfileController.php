<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ProfileController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getConnection();
    }

    public function index(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setFlash('error', 'User account not found.');
            $this->redirect('/login');
            return;
        }

        $employee = null;
        $customer = null;

        if (in_array($user['role_slug'], ['owner', 'sales_rep', 'store_manager'], true)) {
            $empStmt = $this->db->prepare(
                'SELECT * FROM employees WHERE user_id = :uid AND deleted_at IS NULL LIMIT 1'
            );
            $empStmt->execute(['uid' => $userId]);
            $employee = $empStmt->fetch() ?: null;
        }

        if ($user['role_slug'] === 'shop_customer') {
            $cusStmt = $this->db->prepare(
                'SELECT c.*, s.shop_name, s.tax_number, s.registration_no, s.credit_limit, s.credit_balance
                 FROM customers c
                 LEFT JOIN shops s ON s.customer_id = c.id
                 WHERE (c.email = :email OR c.phone = :phone) AND c.deleted_at IS NULL
                 LIMIT 1'
            );
            $cusStmt->execute([
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
            ]);
            $customer = $cusStmt->fetch() ?: null;
        }

        $roleSlug = $user['role_slug'];
        $layout = match ($roleSlug) {
            'sales_rep'     => 'sales-rep',
            'store_manager' => 'inventory',
            'owner'         => 'admin',
            default         => 'public',
        };

        $this->view('profile/index', [
            'title'    => 'My Profile & Account Settings',
            'user'     => $user,
            'employee' => $employee,
            'customer' => $customer,
            'flash'    => $this->getFlash(),
        ], $layout);
    }

    public function updateProfile(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/profile');
            return;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $fullName = trim((string) $this->input('full_name', ''));
        $username = trim((string) $this->input('username', ''));
        $email = strtolower(trim((string) $this->input('email', '')));
        $phone = trim((string) $this->input('phone', ''));

        if ($fullName === '') {
            $this->setFlash('error', 'Full name is required.');
            $this->redirect('/profile');
            return;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please provide a valid email address.');
            $this->redirect('/profile');
            return;
        }

        if ($username === '' || !preg_match('/^[A-Za-z0-9._-]{3,60}$/', $username)) {
            $this->setFlash('error', 'Username must be 3–60 characters (letters, numbers, dot, dash, underscore).');
            $this->redirect('/profile');
            return;
        }

        $dupEmail = $this->db->prepare(
            'SELECT id FROM users WHERE email = :email AND id != :uid AND deleted_at IS NULL LIMIT 1'
        );
        $dupEmail->execute(['email' => $email, 'uid' => $userId]);
        if ($dupEmail->fetch()) {
            $this->setFlash('error', 'That email address is already in use by another account.');
            $this->redirect('/profile');
            return;
        }

        $dupUser = $this->db->prepare(
            'SELECT id FROM users WHERE username = :uname AND id != :uid AND deleted_at IS NULL LIMIT 1'
        );
        $dupUser->execute(['uname' => $username, 'uid' => $userId]);
        if ($dupUser->fetch()) {
            $this->setFlash('error', 'That username is already taken. Please choose another.');
            $this->redirect('/profile');
            return;
        }

        try {
            $this->db->beginTransaction();

            $upd = $this->db->prepare(
                'UPDATE users
                 SET full_name = :full_name,
                     username  = :username,
                     email     = :email,
                     phone     = :phone,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $upd->execute([
                'full_name' => $fullName,
                'username'  => $username,
                'email'     => $email,
                'phone'     => $phone !== '' ? $phone : null,
                'id'        => $userId,
            ]);

            $_SESSION['full_name'] = $fullName;
            $_SESSION['username']  = $username;
            $_SESSION['email']     = $email;

            $roleSlug = (string) ($_SESSION['role_slug'] ?? $_SESSION['role'] ?? '');
            if ($roleSlug === 'shop_customer') {
                $shopName  = trim((string) $this->input('shop_name', ''));
                $address   = trim((string) $this->input('address', ''));
                $city      = trim((string) $this->input('city', ''));
                $taxNumber = trim((string) $this->input('tax_number', ''));

                $cStmt = $this->db->prepare('SELECT id FROM customers WHERE email = :email OR phone = :phone LIMIT 1');
                $cStmt->execute(['email' => $email, 'phone' => $phone]);
                $customerId = (int) ($cStmt->fetchColumn() ?: 0);

                if ($customerId > 0) {
                    $updCus = $this->db->prepare(
                        'UPDATE customers SET name = :name, contact_person = :name, phone = :phone, email = :email, address = :addr, city = :city WHERE id = :cid'
                    );
                    $updCus->execute([
                        'name'  => $fullName,
                        'phone' => $phone !== '' ? $phone : null,
                        'email' => $email,
                        'addr'  => $address !== '' ? $address : null,
                        'city'  => $city !== '' ? $city : null,
                        'cid'   => $customerId,
                    ]);

                    if ($shopName !== '' || $taxNumber !== '') {
                        $updShop = $this->db->prepare(
                            'UPDATE shops SET shop_name = COALESCE(NULLIF(:sname, ""), shop_name), tax_number = :tax WHERE customer_id = :cid'
                        );
                        $updShop->execute([
                            'sname' => $shopName,
                            'tax'   => $taxNumber !== '' ? $taxNumber : null,
                            'cid'   => $customerId,
                        ]);
                    }
                }
            }

            $this->db->commit();
            $this->setFlash('success', 'Your profile details have been saved successfully.');
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->setFlash('error', 'Failed to update profile: ' . $e->getMessage());
        }

        $this->redirect('/profile');
    }

    public function changePassword(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/profile');
            return;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $currentPassword = (string) $this->input('current_password', '');
        $newPassword     = (string) $this->input('new_password', '');
        $confirmPassword = (string) $this->input('new_password_confirmation', '');

        if ($currentPassword === '' || $newPassword === '') {
            $this->setFlash('error', 'Current password and new password are required.');
            $this->redirect('/profile');
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->setFlash('error', 'The new password must be at least 8 characters long.');
            $this->redirect('/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'The new password and confirmation password do not match.');
            $this->redirect('/profile');
            return;
        }

        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $hash = (string) ($stmt->fetchColumn() ?: '');

        if (!password_verify($currentPassword, $hash)) {
            $this->setFlash('error', 'Your current password was entered incorrectly.');
            $this->redirect('/profile');
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $this->db->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id');
        $upd->execute(['hash' => $newHash, 'id' => $userId]);

        $this->setFlash('success', 'Your password has been changed successfully.');
        $this->redirect('/profile');
    }
}
