<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($this->renderSection('title') ?: 'Masuk') ?> — Z'Plack SIMENAK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --navy:        #051747;
            --navy-mid:    #0a2860;
            --blue-accent: #2E5CE6;
            --bg-page:     #F0F2F8;
            --bg-white:    #FFFFFF;
            --text-body:   #4A5568;
            --text-muted:  #8896A5;
            --border:      #E2E8F0;
            --radius-sm:   8px;
            --radius-md:   14px;
            --radius-lg:   20px;
            --radius-xl:   28px;
            --shadow-sm:   0 1px 3px rgba(5,23,71,.06);
            --shadow-md:   0 4px 16px rgba(5,23,71,.08);
            --shadow-lg:   0 12px 40px rgba(5,23,71,.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-body);
            background: var(--bg-page);
        }

        .auth-card {
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            max-width: 400px;
        }

        .btn-primary {
            background: var(--navy);
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: background-color .2s ease;
        }

        .btn-primary:hover {
            background: var(--blue-accent);
        }

        .input-field {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .input-field:focus {
            border-color: var(--blue-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8">

    <div class="w-full max-w-[400px]">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="<?= site_url('/') ?>" class="inline-block">
                <span class="block text-3xl font-extrabold tracking-tight" style="color:var(--navy);">Z'PLACK</span>
                <span class="block text-sm font-bold tracking-[0.25em] mt-1" style="color:var(--blue-accent);">SIMENAK</span>
            </a>
            <p class="mt-3 text-sm" style="color:var(--text-muted);">
                Sistem Informasi Pemesanan Percetakan
            </p>
        </div>

        <!-- Auth Card -->
        <div class="auth-card w-full p-8">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('warning')) ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>

        <p class="text-center text-xs mt-6" style="color:var(--text-muted);">
            &copy; <?= date('Y') ?> Z'Plack. All rights reserved.
        </p>
    </div>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
