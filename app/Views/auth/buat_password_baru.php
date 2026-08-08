<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Buat Password Baru<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    body {
        background: var(--navy);
    }

    body .text-center.mb-8 a span {
        color: #fff !important;
    }

    body .text-center.text-xs.mt-6 {
        color: rgba(255, 255, 255, 0.55) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6 text-center">
    <h1 class="mt-4 text-2xl font-extrabold tracking-tight" style="color:var(--navy);">
        Buat Password Baru
    </h1>
    <p class="mt-1.5 text-sm" style="color:var(--text-muted);">
        Halo<?= !empty($nama) ? ', ' . esc((string) $nama) : '' ?>.
        Ini login pertama Anda. Untuk keamanan, silakan buat password Anda sendiri sebelum masuk ke portal.
    </p>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <?= esc((string) session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('warning')): ?>
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <?= esc((string) session()->getFlashdata('warning')) ?>
    </div>
<?php endif; ?>

<form action="<?= site_url('buat-password-baru') ?>" method="post" class="space-y-5">
    <?= csrf_field() ?>

    <div>
        <label for="password" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Password Baru
        </label>
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="8"
            autocomplete="new-password"
            placeholder="Minimal 8 karakter"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]">
    </div>

    <div>
        <label for="password_confirm" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Konfirmasi Password Baru
        </label>
        <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            required
            minlength="8"
            autocomplete="new-password"
            placeholder="Ulangi password baru"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]">
    </div>

    <button
        type="submit"
        class="btn-primary w-full py-3 text-white text-sm mt-2">
        Simpan &amp; Lanjut
    </button>
</form>

<p class="text-center text-sm mt-6" style="color:var(--text-muted);">
    Bukan Anda?
    <a href="<?= site_url('logout') ?>" class="font-semibold hover:underline" style="color:var(--blue-accent);">
        Keluar
    </a>
</p>

<?= $this->endSection() ?>