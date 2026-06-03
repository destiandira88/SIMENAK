<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id_notif';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_user',
        'id_order',
        'judul',
        'pesan',
        'is_read',
        'created_at',
    ];

    public function getUnreadCount(int $idUser): int
    {
        return $this->where('id_user', $idUser)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getByUser(int $idUser, int $limit = 10): array
    {
        return $this->where('id_user', $idUser)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getListForUser(int $idUser, int $limit = 50): array
    {
        return $this->select('notifications.*, orders.kode_order')
            ->join('orders', 'orders.id_order = notifications.id_order', 'left')
            ->where('notifications.id_user', $idUser)
            ->orderBy('notifications.created_at', 'DESC')
            ->findAll($limit);
    }

    public function markAsRead(int $idNotif, int $idUser): bool
    {
        $row = $this->where('id_notif', $idNotif)
            ->where('id_user', $idUser)
            ->first();

        if ($row === null) {
            return false;
        }

        if ((int) ($row['is_read'] ?? 0) === 1) {
            return true;
        }

        return $this->update($idNotif, ['is_read' => 1]);
    }

    public function markAllRead(int $idUser): void
    {
        $this->where('id_user', $idUser)
            ->where('is_read', 0)
            ->set(['is_read' => 1])
            ->update();
    }
}
