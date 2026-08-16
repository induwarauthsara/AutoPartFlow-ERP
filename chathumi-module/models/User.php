<?php
require_once __DIR__ . '/../core/Database.php';

class User
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function countActive(): int
    {
        return (int) ($this->db->selectOne("SELECT COUNT(*) c FROM users WHERE is_deleted=0 AND status='active'")['c'] ?? 0);
    }

    public function countInactive(): int
    {
        return (int) ($this->db->selectOne("SELECT COUNT(*) c FROM users WHERE is_deleted=0 AND status!='active'")['c'] ?? 0);
    }

    public function countTotal(): int
    {
        return (int) ($this->db->selectOne("SELECT COUNT(*) c FROM users WHERE is_deleted=0")['c'] ?? 0);
    }

    public function countByRole(): array
    {
        return $this->db->select(
            "SELECT r.role_name, COUNT(u.user_id) c FROM roles r
             LEFT JOIN users u ON u.role_id = r.role_id AND u.is_deleted = 0
             GROUP BY r.role_id ORDER BY r.role_id"
        );
    }

    public function paginate(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $rows = $this->db->select(
                "SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id=u.role_id
                 WHERE u.is_deleted=0 AND (u.full_name LIKE ? OR u.email LIKE ?)
                 ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
                'ssii', [$like, $like, $perPage, $offset]
            );
            $total = (int) ($this->db->selectOne(
                "SELECT COUNT(*) c FROM users WHERE is_deleted=0 AND (full_name LIKE ? OR email LIKE ?)",
                'ss', [$like, $like]
            )['c'] ?? 0);
        } else {
            $rows = $this->db->select(
                "SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id=u.role_id
                 WHERE u.is_deleted=0 ORDER BY u.created_at DESC LIMIT ? OFFSET ?",
                'ii', [$perPage, $offset]
            );
            $total = $this->countTotal();
        }
        return ['rows' => $rows, 'total' => $total];
    }

    public function findById(int $id): ?array
    {
        return $this->db->selectOne("SELECT u.*, r.role_name FROM users u JOIN roles r ON r.role_id=u.role_id WHERE u.user_id=? AND u.is_deleted=0", 'i', [$id]);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $row = $this->db->selectOne("SELECT user_id FROM users WHERE email=? AND user_id!=?", 'si', [$email, $excludeId]);
        return (bool) $row;
    }

    public function create(string $name, string $email, string $password, int $roleId): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->execute(
            "INSERT INTO users (full_name, email, password_hash, role_id, status) VALUES (?,?,?,?, 'active')",
            'sssi', [$name, $email, $hash, $roleId]
        );
        return $this->db->lastId;
    }

    public function update(int $id, string $name, string $email, int $roleId, string $status): void
    {
        $this->db->execute(
            "UPDATE users SET full_name=?, email=?, role_id=?, status=? WHERE user_id=?",
            'ssisi', [$name, $email, $roleId, $status, $id]
        );
    }

    public function softDelete(int $id): void
    {
        $this->db->execute("UPDATE users SET is_deleted=1 WHERE user_id=?", 'i', [$id]);
    }

    public function roles(): array
    {
        return $this->db->select("SELECT * FROM roles ORDER BY role_id");
    }
}
