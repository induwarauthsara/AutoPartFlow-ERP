<?php
require_once __DIR__ . '/../core/Database.php';

class Setting
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array
    {
        $rows = $this->db->select("SELECT setting_key, setting_value FROM settings");
        $out = [];
        foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
        return $out;
    }

    public function set(string $key, string $value, string $group = 'general'): void
    {
        $existing = $this->db->selectOne("SELECT setting_id FROM settings WHERE setting_key=?", 's', [$key]);
        if ($existing) {
            $this->db->execute("UPDATE settings SET setting_value=? WHERE setting_key=?", 'ss', [$value, $key]);
        } else {
            $this->db->execute("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?,?,?)", 'sss', [$key, $value, $group]);
        }
    }
}
