<?php

namespace App\Controllers;

class LandingController extends BaseController
{
    public function index()
    {
        $katalogAktif = [];

        try {
            $db = \Config\Database::connect();

            $katalogAktif = $db->table('katalog')
                ->select('id_katalog, nama_produk, kategori, harga_dasar, satuan, deskripsi, gambar')
                ->where('is_active', 1)
                ->orderBy('id_katalog', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Landing index error: {message}', ['message' => $e->getMessage()]);
        }

        return view('landing/index', [
            'katalogAktif' => $katalogAktif,
        ]);
    }
}

