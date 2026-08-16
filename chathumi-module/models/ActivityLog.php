<?php
require_once __DIR__ . '/../core/Database.php';

class ActivityLog
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function recent(int $limit = 10): array
    {
        return $this->db->select(
            "SELECT a.*, u.full_name, u.email FROM activity_logs a
             LEFT JOIN users u ON u.user_id = a.user_id
             ORDER BY a.created_at DESC LIMIT ?",
            'i', [$limit]
        );
    }
}
