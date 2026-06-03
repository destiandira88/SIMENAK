<?php

namespace App\Controllers;

use App\Models\KatalogModel;
use CodeIgniter\HTTP\RedirectResponse;

class FormTemplateController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index(int $idKatalog)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        try {
            $katalogModel = model(KatalogModel::class);
            $katalog      = $katalogModel->find($idKatalog);

            if ($katalog === null) {
                return redirect()->to(site_url('katalog/kelola'))
                    ->with('error', 'Produk katalog tidak ditemukan.');
            }

            $db = \Config\Database::connect();

            $fields = $db->table('form_templates')
                ->where('id_katalog', $idKatalog)
                ->orderBy('urutan', 'ASC')
                ->get()
                ->getResultArray();

            return view('katalog/form_template', [
                'katalog' => $katalog,
                'fields'  => $fields,
                'title'   => 'Kelola Form: ' . ($katalog['nama_produk'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'FormTemplate index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('katalog/kelola'))
                ->with('error', 'Gagal memuat form template.');
        }
    }

    public function store(int $idKatalog)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $rules = [
            'field_key'   => 'required|max_length[50]|regex_match[/^[a-z0-9_]+$/]',
            'field_label' => 'required|max_length[100]',
            'field_type'  => 'required|in_list[text,date,time,textarea,file]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            $katalogModel = model(KatalogModel::class);
            $katalog      = $katalogModel->find($idKatalog);

            if ($katalog === null) {
                return redirect()->to(site_url('katalog/kelola'))
                    ->with('error', 'Produk katalog tidak ditemukan.');
            }

            $db = \Config\Database::connect();

            $maxRow = $db->table('form_templates')
                ->where('id_katalog', $idKatalog)
                ->selectMax('urutan', 'max_urutan')
                ->get()
                ->getRowArray();

            $maxUrutan = (int) ($maxRow['max_urutan'] ?? 0);
            $fieldKey    = strtolower((string) $this->request->getPost('field_key'));

            $exists = $db->table('form_templates')
                ->where('id_katalog', $idKatalog)
                ->where('field_key', $fieldKey)
                ->countAllResults();

            if ($exists > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Field key "' . $this->request->getPost('field_key') . '" sudah ada untuk produk ini.');
            }

            $db->table('form_templates')->insert([
                'id_katalog'  => $idKatalog,
                'field_key'   => $fieldKey,
                'field_label' => $this->request->getPost('field_label'),
                'field_type'  => $this->request->getPost('field_type'),
                'placeholder' => $this->request->getPost('placeholder') ?? '',
                'is_required' => $this->request->getPost('is_required') ? 1 : 0,
                'urutan'      => $maxUrutan + 1,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'FormTemplate store: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan field. Silakan coba lagi.');
        }

        return redirect()->back()->with('success', 'Field berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $rules = [
            'field_label' => 'required|max_length[100]',
            'placeholder' => 'permit_empty|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        try {
            $db = \Config\Database::connect();

            $db->table('form_templates')
                ->where('id_template', $id)
                ->set([
                    'field_label' => $this->request->getPost('field_label'),
                    'placeholder' => $this->request->getPost('placeholder') ?? '',
                    'is_required' => $this->request->getPost('is_required') ? 1 : 0,
                ])
                ->update();
        } catch (\Throwable $e) {
            log_message('error', 'FormTemplate update: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui field. Silakan coba lagi.');
        }

        return redirect()->back()->with('success', 'Field berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        try {
            $db = \Config\Database::connect();

            $db->table('form_templates')
                ->where('id_template', $id)
                ->delete();
        } catch (\Throwable $e) {
            log_message('error', 'FormTemplate delete: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus field. Silakan coba lagi.');
        }

        return redirect()->back()->with('success', 'Field dihapus.');
    }

    private function ensureAdmin(): ?RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak.');
        }

        return null;
    }
}
