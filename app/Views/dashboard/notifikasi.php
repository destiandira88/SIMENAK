<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Notifikasi<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Notifikasi<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Notifikasi<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Lihat semua pemberitahuan dan pembaruan sistem.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card p-6">
    <p class="text-sm" style="color:var(--text-muted);">Daftar notifikasi akan ditampilkan di sini.</p>
</div>
<?= $this->endSection() ?>
