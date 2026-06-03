<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Daftar<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-extrabold tracking-tight" style="color:var(--navy);">
        Buat Akun Baru
    </h1>
    <p class="mt-1.5 text-sm" style="color:var(--text-muted);">
        Daftar untuk mulai memesan di SIMENAK
    </p>
</div>

<form action="<?= site_url('register') ?>" method="post" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="nama" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Nama Lengkap
        </label>
        <input
            type="text"
            id="nama"
            name="nama"
            value="<?= esc(old('nama')) ?>"
            required
            autocomplete="name"
            placeholder="Nama lengkap kamu"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]"
        >
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Email
        </label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= esc(old('email')) ?>"
            required
            autocomplete="email"
            placeholder="nama@email.com"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]"
        >
    </div>

    <div>
        <label for="no_telp" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            No. Telepon
        </label>
        <input
            type="tel"
            id="no_telp"
            name="no_telp"
            value="<?= esc(old('no_telp')) ?>"
            required
            autocomplete="tel"
            placeholder="08xxxxxxxxxx"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]"
        >
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Password
        </label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="Minimal 8 karakter"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]"
        >
    </div>

    <div>
        <label for="password_confirm" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Konfirmasi Password
        </label>
        <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            required
            autocomplete="new-password"
            placeholder="Ulangi password"
            class="input-field w-full px-4 py-2.5 text-sm text-[#051747] placeholder:text-[#8896A5]"
        >
    </div>

    <button
        type="submit"
        class="btn-primary w-full py-3 text-white text-sm mt-2"
    >
        Daftar Sekarang
    </button>
</form>

<p class="text-center text-sm mt-6" style="color:var(--text-muted);">
    Sudah punya akun?
    <a href="<?= site_url('login') ?>" class="font-semibold hover:underline" style="color:var(--blue-accent);">
        Masuk
    </a>
</p>

<?= $this->endSection() ?>
