<?php
/**
 * Shared status-quick scripts for Manajemen Desain & Beranda Produksi.
 *
 * @var bool $enableStatusQuick
 */
$enableStatusQuick = (bool) ($enableStatusQuick ?? false);
$statusUpdateUrl   = site_url('produksi/update-status');
?>
<script>
    window.produksiStatusQuick = (function () {
        const enabled = <?= $enableStatusQuick ? 'true' : 'false' ?>;
        const updateUrl = <?= json_encode($statusUpdateUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        let tableApi = null;
        let openMenu = null;
        let openBtn = null;
        let wired = false;

        function closeMenu() {
            if (openMenu) {
                openMenu.classList.remove('is-open');
            }
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'false');
            }
            openMenu = null;
            openBtn = null;
        }

        function positionMenu(menu, trigger) {
            menu.classList.add('is-open');
            const rect = trigger.getBoundingClientRect();
            const margin = 8;
            let left = rect.left;
            let top = rect.bottom + 6;
            const width = menu.offsetWidth;
            const height = menu.offsetHeight;

            if (left + width > window.innerWidth - margin) {
                left = Math.max(margin, window.innerWidth - width - margin);
            }
            if (left < margin) {
                left = margin;
            }
            if (top + height > window.innerHeight - margin) {
                top = Math.max(margin, rect.top - height - 6);
            }

            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
        }

        function syncCsrfFromHtml(html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const token = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) {
                    meta.setAttribute('content', token);
                }
            }
        }

        function extractFlashFromHtml(html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const flashes = [];

            doc.querySelectorAll('[data-flash-toast]').forEach(function (el) {
                const msgEl = el.querySelector('.flash-toast-message');
                const message = (msgEl?.textContent || '').trim();
                if (message === '') {
                    return;
                }

                let type = 'info';
                if (msgEl?.classList.contains('text-emerald-900')) {
                    type = 'success';
                } else if (msgEl?.classList.contains('text-[#991B1B]')) {
                    type = 'error';
                } else if (msgEl?.classList.contains('text-amber-900')) {
                    type = 'warning';
                } else if (msgEl?.classList.contains('text-sky-900')) {
                    type = 'info';
                }

                flashes.push({ type: type, message: message });
            });

            return flashes;
        }

        function showClientToast(type, message) {
            const styles = {
                success: {
                    bg: 'bg-emerald-50',
                    border: 'border-emerald-200/80',
                    text: 'text-emerald-900',
                    iconBg: 'bg-emerald-500',
                    bar: 'bg-emerald-500',
                    iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
                },
                error: {
                    bg: 'bg-[#FEE2E2]',
                    border: 'border-[#FECACA]',
                    text: 'text-[#991B1B]',
                    iconBg: 'bg-red-500',
                    bar: 'bg-red-500',
                    iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
                },
            };
            const style = styles[type] || styles.error;
            let stack = document.getElementById('flash-toast-stack');

            if (!stack) {
                stack = document.createElement('div');
                stack.id = 'flash-toast-stack';
                stack.setAttribute('aria-live', 'polite');
                stack.setAttribute('aria-atomic', 'true');
                stack.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;display:flex;flex-direction:column;gap:0.75rem;width:min(100vw - 2rem,420px);pointer-events:none;';
                document.body.appendChild(stack);
            }

            const toast = document.createElement('div');
            toast.className = 'flash-toast border ' + style.border + ' ' + style.bg;
            toast.setAttribute('data-flash-toast', '');
            toast.setAttribute('role', 'alert');
            toast.innerHTML =
                '<div class="flash-toast-body">'
                + '<div class="flash-toast-icon ' + style.iconBg + '">'
                + '<svg viewBox="0 0 24 24" aria-hidden="true">' + style.iconSvg + '</svg>'
                + '</div>'
                + '<p class="flash-toast-message ' + style.text + '">' + message.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>'
                + '<button type="button" class="flash-toast-close" data-flash-toast-close aria-label="Tutup notifikasi">'
                + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
                + '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>'
                + '</svg></button></div>'
                + '<div class="flash-toast-track"><span class="flash-toast-progress ' + style.bar + '" data-flash-toast-progress></span></div>';

            stack.appendChild(toast);

            if (typeof window.initFlashToast === 'function') {
                window.initFlashToast(toast);
            } else {
                window.setTimeout(function () {
                    toast.remove();
                }, 5000);
            }
        }

        function replaceBadgeWithFinishing(wrap) {
            const span = document.createElement('span');
            span.className = 'inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-[#CFFAFE] text-[#164E63]';
            span.textContent = 'Finishing';
            wrap.replaceWith(span);
        }

        function setStatusBadgeLoading(btn, loading) {
            const spinner = btn.querySelector('.produksi-status-badge-spinner');
            btn.classList.toggle('is-loading', loading);
            btn.disabled = loading;
            if (spinner) {
                spinner.classList.toggle('hidden', !loading);
            }
        }

        async function submitFinishingStatus(btn) {
            const wrap = btn.closest('.produksi-status-quick');
            const row = btn.closest('tr.data-table-row');
            const menu = wrap?.querySelector('.produksi-status-quick-menu');
            const idOrder = btn.dataset.idOrder || '';

            if (!wrap || !row || idOrder === '') {
                return;
            }

            closeMenu();
            setStatusBadgeLoading(btn, true);

            const csrfField = document.querySelector('meta[name="csrf-field"]')?.getAttribute('content') || 'csrf_test_name';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData = new FormData();
            formData.append(csrfField, csrfToken);
            formData.append('id_order', idOrder);
            formData.append('new_status', 'finishing');

            try {
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const html = await response.text();
                syncCsrfFromHtml(html);
                const flashes = extractFlashFromHtml(html);
                const errorFlash = flashes.find(function (f) { return f.type === 'error'; });
                const successFlash = flashes.find(function (f) { return f.type === 'success'; });

                if (errorFlash) {
                    setStatusBadgeLoading(btn, false);
                    showClientToast('error', errorFlash.message);
                    return;
                }

                row.dataset.status = 'finishing';
                replaceBadgeWithFinishing(wrap);
                if (menu) {
                    menu.remove();
                }

                if (successFlash) {
                    showClientToast('success', successFlash.message);
                }

                if (tableApi) {
                    tableApi.applyTableState();
                }
            } catch (err) {
                setStatusBadgeLoading(btn, false);
                showClientToast('error', 'Gagal memperbarui status pesanan. Periksa koneksi lalu coba lagi.');
            }
        }

        function wireTriggers() {
            document.querySelectorAll('[data-status-quick-trigger]').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (btn.disabled || btn.classList.contains('is-loading')) {
                        return;
                    }

                    const menu = btn.closest('.produksi-status-quick')?.querySelector('.produksi-status-quick-menu');
                    if (!menu) {
                        return;
                    }

                    if (openBtn === btn && menu.classList.contains('is-open')) {
                        closeMenu();
                        return;
                    }

                    closeMenu();
                    positionMenu(menu, btn);
                    btn.setAttribute('aria-expanded', 'true');
                    openMenu = menu;
                    openBtn = btn;
                });
            });

            document.querySelectorAll('.produksi-status-quick-option[data-status-target="finishing"]').forEach(function (option) {
                option.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const wrap = option.closest('.produksi-status-quick');
                    const btn = wrap?.querySelector('[data-status-quick-trigger]');
                    if (!btn) {
                        return;
                    }

                    closeMenu();

                    const kode = btn.getAttribute('data-kode-order') || '-';
                    if (typeof window.openActionConfirmCustom === 'function') {
                        window.openActionConfirmCustom({
                            variant: 'status-finishing',
                            title: 'Ubah status ke Finishing?',
                            message: kode,
                            onConfirm: function () {
                                submitFinishingStatus(btn);
                            },
                        });
                        return;
                    }

                    submitFinishingStatus(btn);
                });
            });

            document.querySelectorAll('.produksi-status-quick-menu').forEach(function (menu) {
                menu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            });
        }

        function init(api) {
            tableApi = api || null;
            if (!enabled) {
                return;
            }

            wireTriggers();

            if (!wired) {
                document.addEventListener('click', function () {
                    closeMenu();
                });
                window.addEventListener('resize', function () {
                    closeMenu();
                });
                window.addEventListener('scroll', function () {
                    closeMenu();
                }, true);
                wired = true;
            }
        }

        return {
            init: init,
            closeMenu: closeMenu,
            enabled: enabled,
        };
    })();
</script>
