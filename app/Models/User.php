<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    public function allUsers(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id, u.role_id, u.username, u.email, u.full_name, u.phone, u.avatar,
                    u.is_active, u.last_login_at, u.created_at,
                    r.name AS role_name, r.slug AS role_slug,
                    e.id AS employee_id, e.employee_code, e.designation, e.department, e.base_salary
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN employees e ON e.user_id = u.id AND e.deleted_at IS NULL
             WHERE u.deleted_at IS NULL
             ORDER BY u.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function allRoles(): array
    {
        return $this->db->query("SELECT id, name, slug, description, permissions FROM roles ORDER BY id ASC")->fetchAll();
    }

    public function recentActivity(int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $stmt = $this->db->query(
            "SELECT al.id, al.action, al.description, al.created_at,
                    COALESCE(u.full_name, 'System') AS actor
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.module = 'users'
             ORDER BY al.created_at DESC
             LIMIT {$limit}"
        );
        return array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'type' => (string) $row['action'],
            'actor' => (string) $row['actor'],
            'text' => (string) $row['description'],
            'time' => strtotime((string) $row['created_at']) * 1000,
        ], $stmt->fetchAll());
    }

    public function findUser(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug,
                    e.id AS employee_id, e.employee_code, e.designation, e.department, e.base_salary
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN employees e ON e.user_id = u.id AND e.deleted_at IS NULL
             WHERE u.id = :id AND u.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveUser(array $data, ?int $operatorId = null): array
    {
        $id = (int) ($data['id'] ?? 0);
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $username = trim((string) ($data['username'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $roleId = (int) ($data['role_id'] ?? 0);
        $phone = trim((string) ($data['phone'] ?? '')) ?: null;
        $isActive = isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1;
        $password = (string) ($data['password'] ?? '');

        if ($roleId <= 0 && !empty($data['role_slug'])) {
            $rStmt = $this->db->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
            $rStmt->execute(['slug' => $data['role_slug']]);
            $roleId = (int) ($rStmt->fetchColumn() ?: 0);
        }

        if ($fullName === '' || $email === '' || $roleId <= 0) {
            throw new \InvalidArgumentException('Full name, valid email, and role are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Please provide a valid email address.');
        }
        if ($username !== '' && !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
            throw new \InvalidArgumentException('Username must be 3-50 characters using letters, numbers, dots, dashes, or underscores.');
        }
        $roleCheck = $this->db->prepare('SELECT slug FROM roles WHERE id=:id LIMIT 1');
        $roleCheck->execute(['id' => $roleId]);
        $newRoleSlug = $roleCheck->fetchColumn();
        if ($newRoleSlug === false) {
            throw new \InvalidArgumentException('Select a valid system role.');
        }

        $this->db->beginTransaction();
        try {
            if ($id > 0) {
                $currentStmt = $this->db->prepare(
                    'SELECT u.username, u.is_active, r.slug role_slug
                     FROM users u JOIN roles r ON r.id=u.role_id
                     WHERE u.id=:id AND u.deleted_at IS NULL FOR UPDATE'
                );
                $currentStmt->execute(['id' => $id]);
                $current = $currentStmt->fetch();
                if (!$current) {
                    throw new \RuntimeException('User account not found.');
                }
                if ($username === '') {
                    $username = (string) $current['username'];
                }
                if ($operatorId === $id && $isActive === 0) {
                    throw new \RuntimeException('You cannot deactivate your own account.');
                }
                if (($current['role_slug'] === 'owner' && (string) $newRoleSlug !== 'owner') ||
                    ($current['role_slug'] === 'owner' && (int) $current['is_active'] === 1 && $isActive === 0)) {
                    $ownerCount = (int) $this->db->query(
                        "SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id
                         WHERE r.slug='owner' AND u.is_active=1 AND u.deleted_at IS NULL"
                    )->fetchColumn();
                    if ($ownerCount <= 1) {
                        throw new \RuntimeException('The last active Business Owner cannot be demoted or deactivated.');
                    }
                }

                // Check email and username uniqueness among other users
                $check = $this->db->prepare(
                    'SELECT id FROM users
                     WHERE (email=:email OR username=:username) AND id!=:id AND deleted_at IS NULL LIMIT 1'
                );
                $check->execute(['email' => $email, 'username' => $username, 'id' => $id]);
                if ($check->fetch()) {
                    throw new \InvalidArgumentException('Another user already uses this email or username.');
                }

                if ($password !== '') {
                    if (strlen($password) < 6) {
                        throw new \InvalidArgumentException('Password must be at least 6 characters.');
                    }
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $this->db->prepare(
                        'UPDATE users SET full_name=:name, username=:username, email=:email, phone=:phone, role_id=:role,
                                          password_hash=:hash, password_changed_at=NOW(), is_active=:active
                         WHERE id=:id AND deleted_at IS NULL'
                    );
                    $stmt->execute(['name' => $fullName, 'username' => $username, 'email' => $email, 'phone' => $phone, 'role' => $roleId, 'hash' => $hash, 'active' => $isActive, 'id' => $id]);
                } else {
                    $stmt = $this->db->prepare(
                        'UPDATE users SET full_name=:name, username=:username, email=:email, phone=:phone, role_id=:role, is_active=:active
                         WHERE id=:id AND deleted_at IS NULL'
                    );
                    $stmt->execute(['name' => $fullName, 'username' => $username, 'email' => $email, 'phone' => $phone, 'role' => $roleId, 'active' => $isActive, 'id' => $id]);
                }

                // Update employee info if designation or department provided
                $designation = trim((string) ($data['designation'] ?? ''));
                $dept = trim((string) ($data['department'] ?? 'sales'));
                $salary = (float) ($data['base_salary'] ?? 0.0);

                if ($designation !== '') {
                    $empCheck = $this->db->prepare('SELECT id FROM employees WHERE user_id = :uid AND deleted_at IS NULL LIMIT 1');
                    $empCheck->execute(['uid' => $id]);
                    $empId = $empCheck->fetchColumn();

                    if ($empId) {
                        $updEmp = $this->db->prepare(
                            'UPDATE employees SET designation=:desig, department=:dept, base_salary=:sal WHERE id=:id'
                        );
                        $updEmp->execute(['desig' => $designation, 'dept' => $dept, 'sal' => $salary, 'id' => $empId]);
                    } else {
                        $code = 'EMP-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
                        $insEmp = $this->db->prepare(
                            'INSERT INTO employees (user_id, employee_code, designation, department, hire_date, base_salary)
                             VALUES (:uid, :code, :desig, :dept, CURDATE(), :sal)'
                        );
                        $insEmp->execute(['uid' => $id, 'code' => $code, 'desig' => $designation, 'dept' => $dept, 'sal' => $salary]);
                    }
                }

                $this->logActivity($operatorId, 'update', 'users', $id, "Updated user account: {$email}");
                $this->db->commit();
                return ['id' => $id, 'action' => 'updated'];
            }

            // Create new user
            if ($username === '') {
                $username = strtolower(explode('@', $email)[0]) . '_' . rand(100, 999);
            }

            if ($password === '' || strlen($password) < 6) {
                throw new \InvalidArgumentException('A secure password of at least 6 characters is required.');
            }

            // Uniqueness check
            $check = $this->db->prepare('SELECT id FROM users WHERE (email = :email OR username = :uname) AND deleted_at IS NULL LIMIT 1');
            $check->execute(['email' => $email, 'uname' => $username]);
            if ($check->fetch()) {
                throw new \InvalidArgumentException('A user with that email or username already exists.');
            }

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare(
                'INSERT INTO users (role_id, username, email, password_hash, full_name, phone, is_active)
                 VALUES (:role, :uname, :email, :hash, :name, :phone, :active)'
            );
            $stmt->execute([
                'role' => $roleId,
                'uname' => $username,
                'email' => $email,
                'hash' => $hash,
                'name' => $fullName,
                'phone' => $phone,
                'active' => $isActive,
            ]);
            $userId = (int) $this->db->lastInsertId();

            // Provision employee profile if designation or department specified
            $designation = trim((string) ($data['designation'] ?? 'Staff Member'));
            $dept = in_array($data['department'] ?? '', ['sales', 'store', 'admin', 'delivery'], true)
                ? $data['department']
                : 'sales';
            $salary = (float) ($data['base_salary'] ?? 0.0);

            $code = 'EMP-' . str_pad((string) $userId, 5, '0', STR_PAD_LEFT);
            $insEmp = $this->db->prepare(
                'INSERT INTO employees (user_id, employee_code, designation, department, hire_date, base_salary)
                 VALUES (:uid, :code, :desig, :dept, CURDATE(), :sal)'
            );
            $insEmp->execute(['uid' => $userId, 'code' => $code, 'desig' => $designation, 'dept' => $dept, 'sal' => $salary]);

            $this->logActivity($operatorId, 'create', 'users', $userId, "Created new user account: {$email} ({$username})");
            $this->db->commit();
            return ['id' => $userId, 'action' => 'created'];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteUser(int $id, int $currentUserId): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Invalid user ID.');
        }

        if ($id === $currentUserId) {
            throw new \InvalidArgumentException('You cannot delete or deactivate your own account.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT u.id, u.email, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id AND u.deleted_at IS NULL FOR UPDATE');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new \RuntimeException('User not found or already deleted.');
            }

            if ($user['role_slug'] === 'owner') {
                $ownerCount = (int) $this->db->query(
                    "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'owner' AND u.deleted_at IS NULL AND u.is_active = 1"
                )->fetchColumn();
                if ($ownerCount <= 1) {
                    throw new \RuntimeException('Cannot delete the last remaining active system owner.');
                }
            }

            $delUser = $this->db->prepare('UPDATE users SET is_active = 0, deleted_at = NOW() WHERE id = :id');
            $delUser->execute(['id' => $id]);

            $delEmp = $this->db->prepare('UPDATE employees SET deleted_at = NOW() WHERE user_id = :id');
            $delEmp->execute(['id' => $id]);

            $this->logActivity($currentUserId, 'delete', 'users', $id, "Deactivated/deleted user {$user['email']}");
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function logActivity(?int $userId, string $action, string $module, ?int $recordId, string $desc): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO activity_logs (user_id, action, module, record_type, record_id, description, ip_address)
                 VALUES (:uid, :act, :mod, :rectype, :recid, :desc, :ip)'
            );
            $stmt->execute([
                'uid' => $userId,
                'act' => $action,
                'mod' => $module,
                'rectype' => 'user',
                'recid' => $recordId,
                'desc' => $desc,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ]);
        } catch (\Throwable) {
            // activity logging is non-blocking
        }
    }
}
