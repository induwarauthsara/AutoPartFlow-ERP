<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Tiny wrapper around mysqli that forces prepared statements
 * everywhere, so no controller/model can accidentally build a
 * raw SQL Injection-prone query.
 */
class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->conn->set_charset(DB_CHARSET);
        $this->conn->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    /**
     * Run a prepared SELECT and return all rows as an assoc array.
     * @param string $sql  e.g. "SELECT * FROM users WHERE role_id = ?"
     * @param string $types e.g. "i", "s", "is"
     * @param array $params
     */
    public function select(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->conn->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function selectOne(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = $this->select($sql, $types, $params);
        return $rows[0] ?? null;
    }

    /**
     * Run a prepared INSERT/UPDATE/DELETE. Returns affected rows,
     * and stores the last insert id on $this->lastId.
     */
    public function execute(string $sql, string $types = '', array $params = []): int
    {
        $stmt = $this->conn->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $this->lastId = $stmt->insert_id;
        $stmt->close();
        return $affected;
    }

    public int $lastId = 0;

    public function beginTransaction(): void { $this->conn->begin_transaction(); }
    public function commit(): void { $this->conn->commit(); }
    public function rollback(): void { $this->conn->rollback(); }
}
