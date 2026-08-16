<?php
require_once __DIR__ . '/../core/Database.php';

class Notification
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function forUser(int $userId, ?string $type = null): array
    {
        if ($type && $type !== 'All') {
            return $this->db->select(
                "SELECT * FROM notifications WHERE (user_id IS NULL OR user_id=?) AND type=? ORDER BY created_at DESC",
                'is', [$userId, $type]
            );
        }
        return $this->db->select(
            "SELECT * FROM notifications WHERE (user_id IS NULL OR user_id=?) ORDER BY created_at DESC",
            'i', [$userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->db->execute("UPDATE notifications SET is_read=1 WHERE user_id IS NULL OR user_id=?", 'i', [$userId]);
    }

    public function markRead(int $id): void
    {
        $this->db->execute("UPDATE notifications SET is_read=1 WHERE notification_id=?", 'i', [$id]);
    }

    public function counts(int $userId): array
    {
        $critical = (int) ($this->db->selectOne("SELECT COUNT(*) c FROM notifications WHERE severity='critical' AND is_read=0 AND (user_id IS NULL OR user_id=?)", 'i', [$userId])['c'] ?? 0);
        $unread = (int) ($this->db->selectOne("SELECT COUNT(*) c FROM notifications WHERE is_read=0 AND (user_id IS NULL OR user_id=?)", 'i', [$userId])['c'] ?? 0);
        return compact('critical', 'unread');
    }
}
