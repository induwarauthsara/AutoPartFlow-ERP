<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => 'AutoPartFlow ERP | Enterprise Auto Parts Management',
            'flash' => $this->getFlash(),
        ]);
    }

    public function login(): void
    {
        // Already logged in? Send to the right place instead of showing the form again.
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->homeRouteForRole((int) ($_SESSION['role_id'] ?? 0)));
        }

        $this->view('auth/login', [
            'title' => 'Sign In | AutoPartFlow ERP',
            'flash' => $this->getFlash(),
        ], 'main');
    }

    public function register(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->homeRouteForRole((string) ($_SESSION['role_slug'] ?? '')));
        }

        $this->view('auth/register', [
            'title' => 'Create Account | AutoPartFlow ERP',
            'flash' => $this->getFlash(),
        ], 'main');
    }

    public function doLogin(): void
    {
        $db = Database::getConnection();

        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/login');
        }

        $identity = trim((string) $this->input('username', $this->input('email', '')));
        $password = (string) $this->input('password', '');

        if ($identity === '' || $password === '') {
            $this->setFlash('error', 'Please enter your username or email and password.');
            $this->redirect('/login');
        }

        $stmt = $db->prepare(
            "SELECT u.id, u.full_name, u.email, u.password_hash, u.role_id, u.is_active, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE (u.email = :identity1 OR u.username = :identity2) AND u.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['identity1' => $identity, 'identity2' => $identity]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->setFlash('error', 'Invalid username/email or password.');
            $this->redirect('/login');
        }

        if (!$user['is_active']) {
            $this->setFlash('error', 'Your account is inactive. Contact an administrator.');
            $this->redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['role_id']    = $user['role_id'];
        $_SESSION['role_slug']  = $user['role_slug'];
        $_SESSION['full_name']  = $user['full_name'];
        $_SESSION['customer_id'] = null;

        // A shop-customer account is linked to its customer record at login.
        // This keeps My Orders, checkout and tracking scoped to the signed-in account.
        if ((string) $user['role_slug'] === 'shop_customer') {
            $customerStmt = $db->prepare(
                "SELECT id FROM customers
                 WHERE email = :email
                   AND customer_type = 'shop'
                   AND deleted_at IS NULL
                   AND is_active = 1
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $customerStmt->execute(['email' => $user['email']]);
            $customerId = $customerStmt->fetchColumn();
            $_SESSION['customer_id'] = $customerId !== false ? (int) $customerId : null;
        }

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")
           ->execute(['id' => $user['id']]);

        $this->setFlash('success', 'Successfully signed in as ' . $user['full_name'] . '.');
        $this->redirect($this->homeRouteForRole((string) $user['role_slug']));
    }

    public function doRegister(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Your session expired. Please try again.');
            $this->redirect('/register');
        }

        $db = Database::getConnection();
        $fullName = trim((string) $this->input('full_name', ''));
        $username = trim((string) $this->input('username', ''));
        $email = strtolower(trim((string) $this->input('email', '')));
        $phone = trim((string) $this->input('phone', ''));
        $password = (string) $this->input('password', '');
        $confirmation = (string) $this->input('password_confirmation', '');
        $roleSlug = (string) $this->input('role_slug', 'shop_customer');
        $allowedRoles = ['owner', 'sales_rep', 'store_manager', 'shop_customer'];

        if ($fullName === '' || $username === '' || $email === '' || $password === '') {
            $this->setFlash('error', 'Name, username, email and password are required.');
            $this->redirect('/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Enter a valid email address.');
            $this->redirect('/register');
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,60}$/', $username)) {
            $this->setFlash('error', 'Username must be 3–60 characters using letters, numbers, dots, dashes or underscores.');
            $this->redirect('/register');
        }
        if (strlen($password) < 8 || $password !== $confirmation) {
            $this->setFlash('error', 'Passwords must match and contain at least 8 characters.');
            $this->redirect('/register');
        }
        if (!in_array($roleSlug, $allowedRoles, true)) {
            $this->setFlash('error', 'Choose a valid account type.');
            $this->redirect('/register');
        }

        $duplicate = $db->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND deleted_at IS NULL LIMIT 1');
        $duplicate->execute(['email' => $email, 'username' => $username]);
        if ($duplicate->fetch()) {
            $this->setFlash('error', 'That email address or username is already registered.');
            $this->redirect('/register');
        }

        try {
            $db->beginTransaction();
            $role = $db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
            $role->execute(['slug' => $roleSlug]);
            $roleId = (int) ($role->fetchColumn() ?: 0);
            if ($roleId === 0) {
                throw new \RuntimeException('The selected role is not configured.');
            }

            $insert = $db->prepare(
                'INSERT INTO users (role_id, username, email, password_hash, full_name, phone)
                 VALUES (:role_id, :username, :email, :password_hash, :full_name, :phone)'
            );
            $insert->execute([
                'role_id' => $roleId,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $fullName,
                'phone' => $phone !== '' ? $phone : null,
            ]);
            $userId = (int) $db->lastInsertId();

            if (in_array($roleSlug, ['owner', 'sales_rep', 'store_manager'], true)) {
                $department = $roleSlug === 'sales_rep' ? 'sales' : ($roleSlug === 'store_manager' ? 'store' : 'admin');
                $designation = $roleSlug === 'sales_rep' ? 'Sales Representative' : ($roleSlug === 'store_manager' ? 'Store Manager' : 'Business Owner');
                $employee = $db->prepare(
                    'INSERT INTO employees (user_id, employee_code, designation, department, hire_date)
                     VALUES (:user_id, :employee_code, :designation, :department, CURDATE())'
                );
                $employee->execute([
                    'user_id' => $userId,
                    'employee_code' => 'EMP-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT),
                    'designation' => $designation,
                    'department' => $department,
                ]);
            } else {
                $customerCode = 'CUS-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT);
                $customer = $db->prepare(
                    "INSERT INTO customers (customer_code, customer_type, name, contact_person, phone, email)
                     VALUES (:code, 'shop', :name, :contact, :phone, :email)"
                );
                $customer->execute([
                    'code' => $customerCode,
                    'name' => $fullName,
                    'contact' => $fullName,
                    'phone' => $phone !== '' ? $phone : null,
                    'email' => $email,
                ]);
                $customerId = (int) $db->lastInsertId();
                $shop = $db->prepare('INSERT INTO shops (customer_id, shop_name) VALUES (:customer_id, :shop_name)');
                $shop->execute(['customer_id' => $customerId, 'shop_name' => $fullName]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', 'We could not create the account. Please review the details and try again.');
            $this->redirect('/register');
        }

        $this->setFlash('success', 'Account created. You can now sign in.');
        $this->redirect('/login');
    }

    public function help(): void
    {
        $this->view('home/help', [
            'title' => 'Help Center | AutoPartFlow',
        ], 'public');
    }

    public function privacy(): void
    {
        $this->view('home/privacy', [
            'title' => 'Privacy Policy | AutoPartFlow',
        ], 'public');
    }

    public function terms(): void
    {
        $this->view('home/terms', [
            'title' => 'Terms of Service | AutoPartFlow',
        ], 'public');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    private function homeRouteForRole(string|int $role): string
    {
        if (is_int($role)) {
            return match ($role) {
                1 => '/admin/dashboard',
                2 => '/sales',
                3 => '/inventory',
                4 => '/catalog',
                default => '/',
            };
        }

        return match ($role) {
            'owner' => '/admin/dashboard',
            'sales_rep' => '/sales',
            'store_manager' => '/inventory',
            'shop_customer' => '/catalog',
            default => '/',
        };
    }
}
