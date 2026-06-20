<div
    id="katalogGambarZoomModal"
    class="hidden fixed inset-0 z-[90] bg-black/75 p-4 sm:p-6 flex items-center justify-center"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="katalogGambarZoomCaption">
    <button
        type="button"
        id="katalogGambarZoomClose"
        class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 text-white text-xl font-bold hover:bg-white/25 transition-colors"
        aria-label="Tutup preview foto">
        ×
    </button>
    <div class="flex flex-col items-center max-w-[95vw]">
        <img
            id="katalogGambarZoomImg"
            src=""
            alt=""
            class="max-w-full max-h-[82vh] rounded-2xl object-contain shadow-2xl bg-white/5">
        <p id="katalogGambarZoomCaption" class="mt-3 text-sm text-white/90 text-center font-medium"></p>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('katalogGambarZoomModal');
        const img = document.getElementById('katalogGambarZoomImg');
        const caption = document.getElementById('katalogGambarZoomCaption');
        const closeBtn = document.getElementById('katalogGambarZoomClose');

        if (!modal || !img) {
            return;
        }

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        const openModal = (src, alt) => {
            img.src = src;
            img.alt = alt || 'Gambar produk';
            if (caption) {
                caption.textContent = alt || '';
            }
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        };

        document.querySelectorAll('[data-katalog-zoom]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                openModal(trigger.dataset.katalogZoom || '', trigger.dataset.katalogZoomAlt || '');
            });
        });

        closeBtn?.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
