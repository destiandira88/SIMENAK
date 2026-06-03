<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderAttrModel extends Model
{
    protected $table         = 'order_attributes';
    protected $primaryKey    = 'id_attr';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_order',
        'attribute_key',
        'attribute_val',
    ];

    /**
     * @return array<string, string>
     */
    public function getByOrder(int $idOrder): array
    {
        $rows = $this->where('id_order', $idOrder)
            ->findAll();

        return array_column($rows, 'attribute_val', 'attribute_key');
    }

    /**
     * @param array<string, string> $data
     */
    public function saveAttributes(int $idOrder, array $data): void
    {
        $db  = \Config\Database::connect();
        $sql = 'INSERT INTO order_attributes (id_order, attribute_key, attribute_val)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE attribute_val = VALUES(attribute_val)';

        foreach ($data as $key => $val) {
            $db->query($sql, [$idOrder, $key, $val]);
        }
    }
}
