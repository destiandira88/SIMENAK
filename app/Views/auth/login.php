<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Masuk<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-extrabold tracking-tight" style="color:var(--navy);">
        Selamat datang 👋
    </h1>
    <p class="mt-1.5 text-sm" style="color:var(--text-muted);">
        Masuk ke akun SIMENAK kamu
    </p>
</div>

<form action="<?= site_url('login') ?>" method="post" class="space-y-5">
    <?= csrf_field() ?>

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
        <label for="password" class="block text-sm font-semibold mb-1.5" style="color:var(--navy);">
            Password
        </label>
        <div class="relative">
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan password"
                class="input-field w-full px-4 py-2.5 pr-11 text-sm text-[#051747] placeholder:text-[#8896A5]"
            >
            <button
                type="button"
                id="togglePassword"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-[#8896A5] hover:text-[#2E5CE6] transition-colors"
                aria-label="Tampilkan password"
            >
                <svg id="iconEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg id="iconEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.03a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>
        </div>
    </div>

    <button
        type="submit"
        class="btn-primary w-full py-3 text-white text-sm mt-2"
    >
        Masuk ke Portal
    </button>
</form>

<p class="text-center text-sm mt-6" style="color:var(--text-muted);">
    Belum punya akun?
    <a href="<?= site_url('register') ?>" class="font-semibold hover:underline" style="color:var(--blue-accent);">
        Daftar
    </a>
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn     = document.getElementById('togglePassword');
        const iconEye       = document.getElementById('iconEye');
        const iconEyeOff    = document.getElementById('iconEyeOff');

        if (!passwordInput || !toggleBtn) return;

        toggleBtn.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            iconEye.classList.toggle('hidden', isHidden);
            iconEyeOff.classList.toggle('hidden', !isHidden);
            toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
    })();
</script>
<?= $this->endSection() ?>
