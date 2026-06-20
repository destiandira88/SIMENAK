<?php if (!session()->get('isLoggedIn')) {
    return;
}

$csrfHeader = config('Security')->headerName;
$csrfCookie = config('Security')->cookieName;
?>
<style>
    #notifModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #notifModal.is-open {
        opacity: 1;
    }

    #notifModal .notif-modal-panel {
        transform: scale(.96) translateY(8px);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #notifModal.is-open .notif-modal-panel {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    #notifModal .notif-modal-card:hover {
        transform: none;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .notif-item {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        box-shadow: var(--shadow-sm);
        transition: all .15s;
        cursor: pointer;
        text-align: left;
        width: 100%;
    }

    .notif-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .notif-item.unread {
        border-left: 3px solid var(--blue-accent);
        background: #fafcff;
    }

    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .ni-blue { background: #DBEAFE; }
    .ni-green { background: #DCFCE7; }
    .ni-amber { background: #FEF3C7; }
    .ni-red { background: #FEE2E2; }

    .notif-content { flex: 1; min-width: 0; }
    .notif-title { font-size: 13px; font-weight: 600; color: var(--navy); margin-bottom: 2px; }
    .notif-sub { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
    .notif-time { font-size: 10px; color: var(--text-muted); margin-top: 4px; }
    .notif-dot {
        width: 8px;
        height: 8px;
        background: var(--blue-accent);
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 6px;
    }

    .btn-notif-mark-all {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--blue-accent);
        border: 1.5px solid var(--blue-accent);
        border-radius: 999px;
        padding: 6px 14px;
        background: #fff;
        transition: background .2s, color .2s;
    }

    .btn-notif-mark-all:hover:not(:disabled) {
        background: var(--blue-accent);
        color: #fff;
    }

    .btn-notif-mark-all:disabled {
        opacity: .45;
        cursor: not-allowed;
    }
</style>

<div id="notifModal"
    class="fixed inset-0 z-[85] hidden items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="notifModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-notif-modal></div>
    <div class="notif-modal-panel notif-modal-card relative w-full max-w-xl bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg z-10 flex flex-col max-h-[min(88vh,720px)]">
        <div class="flex items-start justify-between gap-4 p-6 pb-4 border-b shrink-0" style="border-color:var(--border);">
            <div>
                <h4 id="notifModalTitle" class="text-lg font-extrabold text-[#051747] leading-tight">Notifikasi</h4>
                <p class="text-xs mt-1" style="color:var(--text-muted);">
                    <span id="notifModalSubtitle">Memuat...</span>
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" id="notifMarkAllBtn" class="btn-notif-mark-all" disabled>
                    Tandai dibaca
                </button>
                <button type="button" data-close-notif-modal class="p-2 rounded-lg hover:bg-slate-50 text-slate-500" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="notifModalBody" class="flex-1 overflow-y-auto p-6 pt-4 min-h-[200px]">
            <div id="notifLoading" class="flex flex-col items-center justify-center py-12 text-sm" style="color:var(--text-muted);">
                <svg class="animate-spin h-8 w-8 mb-3 text-[#2E5CE6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memuat notifikasi...
            </div>
            <div id="notifEmpty" class="hidden text-center py-12">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-2xl">🔔</div>
                <p class="text-sm font-semibold text-[#051747]">Belum ada notifikasi</p>
                <p class="text-xs mt-1" style="color:var(--text-muted);">Notifikasi penting akan muncul di sini.</p>
            </div>
            <div id="notifList" class="notif-list hidden"></div>
        </div>
    </div>
</div>

<script>
(() => {
    const notifModal = document.getElementById('notifModal');
    const notifList = document.getElementById('notifList');
    const notifLoading = document.getElementById('notifLoading');
    const notifEmpty = document.getElementById('notifEmpty');
    const notifSubtitle = document.getElementById('notifModalSubtitle');
    const notifMarkAllBtn = document.getElementById('notifMarkAllBtn');
    const bellBadge = document.getElementById('notifBellBadge');
    const csrfHeader = <?= json_encode($csrfHeader) ?>;
    const csrfCookie = <?= json_encode($csrfCookie) ?>;

    if (!notifModal) {
        return;
    }

    const listUrl = <?= json_encode(site_url('notifikasi/list')) ?>;
    const markAllUrl = <?= json_encode(site_url('notifikasi/read-all')) ?>;
    const markReadBase = <?= json_encode(site_url('notifikasi/read')) ?>;

    let closeTimer = null;
    let loadedOnce = false;

    const getCsrfToken = () => {
        const match = document.cookie.match(new RegExp('(?:^|; )' + csrfCookie.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    };

    const fetchJson = async (url, method = 'GET') => {
        const headers = { 'X-Requested-With': 'XMLHttpRequest' };
        const token = getCsrfToken();
        if (token) {
            headers[csrfHeader] = token;
        }

        const res = await fetch(url, {
            method,
            headers,
            credentials: 'same-origin',
        });

        return res.json();
    };

    const updateBadge = (count) => {
        if (!bellBadge) {
            return;
        }

        if (count > 0) {
            bellBadge.textContent = count > 99 ? '99+' : String(count);
            bellBadge.classList.remove('hidden');
        } else {
            bellBadge.classList.add('hidden');
        }
    };

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    };

    const renderItems = (items) => {
        notifList.innerHTML = '';

        items.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'notif-item' + (item.is_read === 0 ? ' unread' : '');
            btn.dataset.idNotif = String(item.id_notif);
            if (item.action_url) {
                btn.dataset.actionUrl = item.action_url;
            }

            btn.innerHTML = `
                <div class="notif-icon ${escapeHtml(item.icon_class)}">${escapeHtml(item.icon)}</div>
                <div class="notif-content">
                    <div class="notif-title">${escapeHtml(item.judul)}</div>
                    <div class="notif-sub">${escapeHtml(item.pesan)}</div>
                    <div class="notif-time">${escapeHtml(item.time_ago)}</div>
                </div>
                ${item.is_read === 0 ? '<span class="notif-dot" aria-hidden="true"></span>' : ''}
            `;

            btn.addEventListener('click', () => onNotifClick(item, btn));
            notifList.appendChild(btn);
        });
    };

    const setSubtitle = (unread, total) => {
        if (total === 0) {
            notifSubtitle.textContent = 'Tidak ada notifikasi';
            return;
        }
        if (unread > 0) {
            notifSubtitle.textContent = unread + ' belum dibaca dari ' + total + ' notifikasi';
        } else {
            notifSubtitle.textContent = total + ' notifikasi-semua sudah dibaca';
        }
    };

    const loadNotifications = async () => {
        notifLoading.classList.remove('hidden');
        notifEmpty.classList.add('hidden');
        notifList.classList.add('hidden');

        try {
            const data = await fetchJson(listUrl);
            notifLoading.classList.add('hidden');

            if (!data.success) {
                notifEmpty.classList.remove('hidden');
                notifSubtitle.textContent = 'Gagal memuat notifikasi';
                return;
            }

            const items = data.items || [];
            const unread = data.unread ?? 0;

            updateBadge(unread);
            setSubtitle(unread, items.length);
            notifMarkAllBtn.disabled = unread === 0;

            if (items.length === 0) {
                notifEmpty.classList.remove('hidden');
                return;
            }

            renderItems(items);
            notifList.classList.remove('hidden');
        } catch (e) {
            notifLoading.classList.add('hidden');
            notifEmpty.classList.remove('hidden');
            notifSubtitle.textContent = 'Gagal memuat notifikasi';
        }
    };

    const onNotifClick = async (item, btn) => {
        if (item.is_read === 0) {
            try {
                const data = await fetchJson(markReadBase + '/' + item.id_notif, 'POST');
                if (data.success) {
                    btn.classList.remove('unread');
                    const dot = btn.querySelector('.notif-dot');
                    if (dot) {
                        dot.remove();
                    }
                    item.is_read = 1;
                    updateBadge(data.unread ?? 0);
                    const unreadLeft = notifList.querySelectorAll('.notif-item.unread').length;
                    notifMarkAllBtn.disabled = unreadLeft === 0;
                    const total = notifList.querySelectorAll('.notif-item').length;
                    setSubtitle(unreadLeft, total);
                }
            } catch (e) {
                /* ignore */
            }
        }

        if (item.action_url) {
            window.location.href = item.action_url;
        }
    };

    const openNotifModal = () => {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }

        notifModal.classList.remove('hidden');
        notifModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            notifModal.classList.add('is-open');
        });

        if (!loadedOnce) {
            loadedOnce = true;
            loadNotifications();
        } else {
            loadNotifications();
        }
    };

    const closeNotifModal = () => {
        notifModal.classList.remove('is-open');

        closeTimer = setTimeout(() => {
            notifModal.classList.add('hidden');
            notifModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            closeTimer = null;
        }, 250);
    };

    document.querySelectorAll('[data-open-notif-modal]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openNotifModal();
        });
    });

    document.querySelectorAll('[data-close-notif-modal]').forEach((btn) => {
        btn.addEventListener('click', closeNotifModal);
    });

    if (notifMarkAllBtn) {
        notifMarkAllBtn.addEventListener('click', async () => {
            if (notifMarkAllBtn.disabled) {
                return;
            }

            try {
                const data = await fetchJson(markAllUrl, 'POST');
                if (data.success) {
                    notifList.querySelectorAll('.notif-item.unread').forEach((el) => {
                        el.classList.remove('unread');
                        const dot = el.querySelector('.notif-dot');
                        if (dot) {
                            dot.remove();
                        }
                    });
                    updateBadge(0);
                    notifMarkAllBtn.disabled = true;
                    const total = notifList.querySelectorAll('.notif-item').length;
                    setSubtitle(0, total);
                }
            } catch (e) {
                /* ignore */
            }
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && notifModal.classList.contains('is-open')) {
            closeNotifModal();
        }
    });

    if (window.location.search.includes('openNotif=1')) {
        openNotifModal();
        const url = new URL(window.location.href);
        url.searchParams.delete('openNotif');
        window.history.replaceState({}, '', url.pathname + url.search);
    }
})();
</script>
