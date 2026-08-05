<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    public function findAllActive(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name FROM {$this->table}
             WHERE deleted_at IS NULL AND is_active = 1
             ORDER BY name ASC"
        );

        return $stmt->fetchAll();
    }
}
