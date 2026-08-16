<?php
require_once __DIR__ . '/../core/Database.php';

class Employee
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function todayAttendance(): array
    {
        $total = (int) ($this->db->selectOne("SELECT COUNT(*) c FROM employees WHERE is_deleted=0")['c'] ?? 0);
        $present = (int) ($this->db->selectOne(
            "SELECT COUNT(*) c FROM attendance_logs WHERE log_date = CURDATE() AND status='present'"
        )['c'] ?? 0);
        $onLeave = (int) ($this->db->selectOne(
            "SELECT COUNT(*) c FROM attendance_logs WHERE log_date = CURDATE() AND status='on_leave'"
        )['c'] ?? 0);
        $late = (int) ($this->db->selectOne(
            "SELECT COUNT(*) c FROM attendance_logs WHERE log_date = CURDATE() AND status='late'"
        )['c'] ?? 0);
        return compact('total', 'present', 'onLeave', 'late');
    }

    public function paginate(int $page = 1, int $perPage = 10, string $department = '', string $status = 'active'): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $where = "e.is_deleted = 0";
        $types = '';
        $params = [];
        if ($department !== '' && $department !== 'All Departments') {
            $where .= " AND e.department = ?"; $types .= 's'; $params[] = $department;
        }
        if ($status !== '' && $status !== 'All') {
            $where .= " AND e.employment_status = ?"; $types .= 's'; $params[] = $status;
        }
        $sql = "SELECT e.*, u.full_name, u.email, u.profile_image
                FROM employees e JOIN users u ON u.user_id = e.user_id
                WHERE $where ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
        $types .= 'ii'; $params[] = $perPage; $params[] = $offset;
        $rows = $this->db->select($sql, $types, $params);

        $countSql = "SELECT COUNT(*) c FROM employees e WHERE $where";
        $countTypes = substr($types, 0, -2);
        $countParams = array_slice($params, 0, -2);
        $total = (int) ($this->db->selectOne($countSql, $countTypes, $countParams)['c'] ?? 0);

        return ['rows' => $rows, 'total' => $total];
    }

    public function departments(): array
    {
        return array_column($this->db->select("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND is_deleted=0"), 'department');
    }

    public function teamPerformanceSummary(): array
    {
        $row = $this->db->selectOne(
            "SELECT AVG(performance_score) avg_score FROM employees WHERE is_deleted = 0"
        );
        return ['avg_score' => round((float) ($row['avg_score'] ?? 0), 1)];
    }
}
