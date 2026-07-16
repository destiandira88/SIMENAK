<?php
/**
 * Kartu rekening transfer Z'Plack + tombol salin nomor rekening.
 *
 * @var string $bankName
 * @var string $accountNumber
 * @var string $accountHolder
 */
$bankName       = (string) ($bankName ?? 'BCA');
$accountNumber  = (string) ($accountNumber ?? '1234567890');
$accountHolder  = (string) ($accountHolder ?? "Z'Plack Percetakan");
$displayNumber  = trim(chunk_split($accountNumber, 4, ' '));
?>
<div class="payment-rekening-card-wrap">
    <div class="payment-rekening-card relative overflow-hidden rounded-2xl p-5 text-white shadow-md">
        <div class="pointer-events-none absolute -right-10 top-1/2 h-44 w-44 -translate-y-1/2 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"
            style="background:linear-gradient(120deg,transparent 40%,rgba(255,255,255,.18) 50%,transparent 60%);"></div>
        <div class="relative">
            <p class="text-lg font-extrabold tracking-wide"><?= esc($bankName) ?></p>
            <p class="mt-5 text-[10px] font-bold uppercase tracking-widest text-white/70">Nomor Rekening</p>
            <p class="mt-1 font-mono text-xl font-semibold tracking-[0.15em] sm:text-2xl"><?= esc($displayNumber) ?></p>
            <div class="mt-6 flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[10px] text-white/60">Atas Nama</p>
                    <p class="truncate text-sm font-semibold"><?= esc($accountHolder) ?></p>
                </div>
                <div class="shrink-0 text-right">
                    <p class="text-[10px] text-white/60">Metode</p>
                    <p class="text-sm font-semibold">Transfer Bank</p>
                </div>
            </div>
        </div>
    </div>
    <button
        type="button"
        class="js-copy-rekening-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#051747] transition-colors hover:border-[#2E5CE6] hover:text-[#2E5CE6]"
        data-copy-rekening="<?= esc($accountNumber) ?>"
        aria-label="Salin nomor rekening">
        <?= view('partials/ui_svg_icon', ['icon' => 'copy', 'class' => 'h-4 w-4 shrink-0']) ?>
        <span class="js-copy-rekening-label">Salin nomor rekening</span>
    </button>
</div>

<style>
    .payment-rekening-card {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 38%, #051747 100%);
    }
</style>

<script>
    (function() {
        if (window.__simenakRekeningCopyInit) {
            return;
        }
        window.__simenakRekeningCopyInit = true;

        const copyText = async (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return;
            }

            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
        };

        document.addEventListener('click', async (event) => {
            const btn = event.target.closest('.js-copy-rekening-btn');
            if (!btn) {
                return;
            }

            const value = btn.getAttribute('data-copy-rekening') || '';
            if (value === '') {
                return;
            }

            const label = btn.querySelector('.js-copy-rekening-label');
            const original = label ? label.textContent : '';

            try {
                await copyText(value);
                if (label) {
                    label.textContent = 'Nomor rekening disalin!';
                    btn.classList.add('border-emerald-300', 'text-emerald-700');
                    setTimeout(() => {
                        label.textContent = original;
                        btn.classList.remove('border-emerald-300', 'text-emerald-700');
                    }, 2000);
                }
            } catch (err) {
                if (label) {
                    label.textContent = 'Gagal menyalin, salin manual';
                    setTimeout(() => {
                        label.textContent = original;
                    }, 2000);
                }
            }
        });
    })();
</script>
