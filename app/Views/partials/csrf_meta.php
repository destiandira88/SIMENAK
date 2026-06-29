<meta name="csrf-field" content="<?= esc(config('Security')->tokenName) ?>">
<meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
