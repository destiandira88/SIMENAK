<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    public function list()
    {
        $idUser = (int) session()->get('id_user');
        if ($idUser <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
        }

        $role  = (string) session()->get('role');
        $model = new NotificationModel();

        $rows = $model->getListForUser($idUser, 50);
        $items = [];

        helper('notification');

        foreach ($rows as $row) {
            $judul = (string) ($row['judul'] ?? '');
            $meta  = getNotifIconMeta($judul);
            $items[] = [
                'id_notif'   => (int) $row['id_notif'],
                'judul'      => $judul,
                'pesan'      => (string) ($row['pesan'] ?? ''),
                'is_read'    => (int) ($row['is_read'] ?? 0),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'time_ago'   => formatNotifTimeAgo((string) ($row['created_at'] ?? '')),
                'kode_order' => $row['kode_order'] ?? null,
                'id_order'   => isset($row['id_order']) ? (int) $row['id_order'] : null,
                'icon'       => $meta['icon'],
                'icon_class' => $meta['class'],
                'action_url' => getNotifActionUrl(
                    $role,
                    $row['kode_order'] ?? null,
                    isset($row['id_order']) ? (int) $row['id_order'] : null,
                    $judul
                ),
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'unread'  => $model->getUnreadCount($idUser),
            'items'   => $items,
        ]);
    }

    public function markRead(int $idNotif)
    {
        $idUser = (int) session()->get('id_user');
        if ($idUser <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
        }

        $model = new NotificationModel();
        $ok    = $model->markAsRead($idNotif, $idUser);

        if (!$ok) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'unread'  => $model->getUnreadCount($idUser),
        ]);
    }

    public function markAllRead()
    {
        $idUser = (int) session()->get('id_user');
        if ($idUser <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
        }

        $model = new NotificationModel();
        $model->markAllRead($idUser);

        return $this->response->setJSON([
            'success' => true,
            'unread'  => 0,
        ]);
    }
}
