<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Part extends Model
{
    protected string $table = 'parts';

    public function create(array $data): int
    {
        return $this->insert($data);
    }

    public function updatePart(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    public function findByPartNumber(string $partNumber): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE part_number = :part_number LIMIT 1");
        $stmt->execute(['part_number' => $partNumber]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
