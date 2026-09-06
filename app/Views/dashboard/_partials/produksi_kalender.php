<?php

/** @var string $byDateJson */
$byDateJson = $byDateJson ?? '{}';
$detailBase = rtrim(site_url('manajemen-desain'), '/');
?>
<div class="mb-8">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-[#051747]">Kalender Antrian Produksi</h3>
            <p class="text-sm text-slate-500 mt-0.5">
                Deadline pesanan aktif. Klik tanggal/item untuk melihat detail.
            </p>
        </div>
        <a href="<?= esc(site_url('dashboard')) ?>"
            class="inline-flex items-center justify-center gap-1.5 text-xs font-bold px-4 py-2 rounded-full border-2 border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors w-fit">
            <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
            Beranda
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Kalender -->
        <div class="xl:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6 overflow-x-auto">
            <div class="flex justify-between items-center mb-6 min-w-[280px]">
                <button type="button" id="prevMonth"
                    class="inline-flex items-center justify-center text-xs font-bold px-3 py-1.5 rounded-full border-2 border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors">
                    <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
                </button>
                <h2 id="calMonthYear" class="font-bold text-[#051747] text-lg"></h2>
                <button type="button" id="nextMonth"
                    class="inline-flex items-center justify-center text-xs font-bold px-3 py-1.5 rounded-full border-2 border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors">
                    <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-right', 'class' => 'h-3.5 w-3.5 shrink-0']) ?>
                </button>
            </div>

            <div class="grid grid-cols-7 text-center mb-2 min-w-[280px]">
                <?php foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $hari): ?>
                    <div class="text-xs font-bold text-slate-400 uppercase py-1"><?= esc($hari) ?></div>
                <?php endforeach; ?>
            </div>

            <div id="calGrid" class="min-w-[280px]"></div>

            <div class="flex flex-wrap gap-3 mt-4 px-1">
                <?php
                $legend = [
                    ['dot-terverifikasi', 'Menunggu Desain', '#3B82F6'],
                    ['dot-proses_desain', 'Proses Desain', '#6366F1'],
                    ['dot-proses_revisi', 'Proses Revisi', '#F59E0B'],
                    ['dot-proses_cetak', 'Proses Cetak', '#8B5CF6'],
                    ['dot-finishing', 'Finishing', '#14B8A6'],
                    ['dot-overdue', 'Deadline Lewat', '#EF4444'],
                ];
                foreach ($legend as [$cls, $label, $color]):
                ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:<?= esc($color) ?>"></span>
                        <span class="text-xs text-slate-500"><?= esc($label) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- List samping -->
        <div class="xl:col-span-5 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col min-h-[420px]">
            <div id="listHeader" class="bg-[#051747] text-white px-5 py-4">
                <p id="listTitle" class="font-bold text-sm">Semua Pesanan Aktif</p>
                <p id="listSub" class="text-xs opacity-70 mt-0.5">Klik tanggal di kalender untuk filter per deadline</p>
            </div>
            <div id="orderList" class="overflow-y-auto max-h-[600px] divide-y divide-slate-100 flex-1"></div>
            <div id="emptyState" class="hidden text-center py-12 flex-1">
                <span class="text-4xl">📭</span>
                <p class="font-medium text-slate-500 mt-2">Tidak ada pesanan</p>
                <p class="text-xs text-slate-400 mt-1" id="emptySubtext"></p>
            </div>
        </div>
    </div>
</div>

<style>
    #calGrid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }

    .cal-day {
        min-height: 52px;
        border-radius: 10px;
        padding: 4px;
        cursor: pointer;
        transition: background .15s;
        position: relative;
    }

    .cal-day:hover {
        background: #F0F2F8;
    }

    .cal-day.today {
        background: #EDE9FE;
        font-weight: 700;
    }

    .cal-day.today .cal-date {
        color: #5B21B6;
    }

    .cal-day.has-deadline {
        cursor: pointer;
    }

    .cal-day.other-month .cal-date {
        color: #CBD5E1;
    }

    .cal-day.selected {
        background: #051747 !important;
    }

    .cal-day.selected .cal-date {
        color: #fff !important;
    }

    .cal-date {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        line-height: 1;
        margin-bottom: 3px;
    }

    .cal-dots {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        margin-top: 2px;
    }

    .cal-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dot-terverifikasi {
        background: #3B82F6;
    }

    .dot-proses_desain {
        background: #6366F1;
    }

    .dot-proses_revisi {
        background: #F59E0B;
    }

    .dot-proses_cetak {
        background: #8B5CF6;
    }

    .dot-finishing {
        background: #14B8A6;
    }

    .dot-overdue {
        background: #EF4444;
    }
</style>

<script>
    (function() {
        const orderData = <?= $byDateJson ?>;
        const detailBase = <?= json_encode($detailBase) ?>;

        const STATUS_DOT = {
            terverifikasi: 'dot-terverifikasi',
            proses_desain: 'dot-proses_desain',
            proses_revisi: 'dot-proses_revisi',
            proses_cetak: 'dot-proses_cetak',
            finishing: 'dot-finishing',
        };

        const STATUS_LABEL = {
            terverifikasi: 'Menunggu Desain',
            proses_desain: 'Proses Desain',
            proses_revisi: 'Proses Revisi',
            proses_cetak: 'Proses Cetak',
            finishing: 'Finishing',
        };

        const STATUS_BADGE = {
            terverifikasi: 'bg-blue-100 text-blue-800',
            proses_desain: 'bg-indigo-100 text-indigo-800',
            proses_revisi: 'bg-amber-100 text-amber-800',
            proses_cetak: 'bg-purple-100 text-purple-800',
            finishing: 'bg-teal-100 text-teal-800',
        };

        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth();
        let selectedDate = null;

        function toYMD(year, month, day) {
            return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        }

        function escHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderCalendar(year, month) {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            document.getElementById('calMonthYear').textContent = `${monthNames[month]} ${year}`;

            const grid = document.getElementById('calGrid');
            grid.innerHTML = '';

            const today = new Date();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrev = new Date(year, month, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                const d = daysInPrev - firstDay + i + 1;
                grid.appendChild(createDayCell(year, month - 1, d, true));
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const isToday = d === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear();
                const cell = createDayCell(year, month, d, false, isToday);
                if (selectedDate === toYMD(year, month, d)) {
                    cell.classList.add('selected');
                }
                grid.appendChild(cell);
            }

            const totalCells = firstDay + daysInMonth;
            const remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
            for (let d = 1; d <= remaining; d++) {
                grid.appendChild(createDayCell(year, month + 1, d, true));
            }
        }

        function createDayCell(year, month, day, isOtherMonth, isToday = false) {
            const ymd = toYMD(year, month, day);
            const data = orderData[ymd] || [];

            const cell = document.createElement('div');
            cell.className = 'cal-day' +
                (isOtherMonth ? ' other-month' : '') +
                (isToday ? ' today' : '') +
                (data.length > 0 ? ' has-deadline' : '');

            const dateEl = document.createElement('div');
            dateEl.className = 'cal-date';
            dateEl.textContent = day;
            cell.appendChild(dateEl);

            if (data.length > 0) {
                const dotsEl = document.createElement('div');
                dotsEl.className = 'cal-dots';
                const today2 = new Date();
                today2.setHours(0, 0, 0, 0);
                const deadlineDate = new Date(ymd + 'T00:00:00');
                const isOverdue = deadlineDate < today2;

                data.slice(0, 4).forEach(function(o) {
                    const dot = document.createElement('div');
                    dot.className = 'cal-dot ' + (isOverdue ? 'dot-overdue' : (STATUS_DOT[o.status] || 'dot-proses_desain'));
                    dotsEl.appendChild(dot);
                });

                if (data.length > 4) {
                    const more = document.createElement('div');
                    more.style.cssText = 'font-size:8px;color:#94A3B8;font-weight:700;line-height:1;margin-top:1px';
                    more.textContent = '+' + (data.length - 4);
                    dotsEl.appendChild(more);
                }
                cell.appendChild(dotsEl);
            }

            cell.addEventListener('click', function() {
                if (isOtherMonth) return;

                document.querySelectorAll('.cal-day.selected').forEach(function(el) {
                    el.classList.remove('selected');
                });

                if (selectedDate === ymd) {
                    selectedDate = null;
                    renderAllOrders();
                } else {
                    selectedDate = ymd;
                    cell.classList.add('selected');
                    renderOrdersForDate(ymd, data);
                }
            });

            return cell;
        }

        function renderAllOrders() {
            const all = Object.entries(orderData)
                .sort(function(a, b) {
                    return a[0].localeCompare(b[0]);
                })
                .flatMap(function(entry) {
                    return entry[1];
                });

            document.getElementById('listTitle').textContent = 'Semua Pesanan Aktif';
            document.getElementById('listSub').textContent =
                all.length + ' pesanan · Klik tanggal untuk filter';
            renderOrderList(all, null);
        }

        function renderOrdersForDate(ymd, orders) {
            const dateObj = new Date(ymd + 'T00:00:00');
            const dateLabel = dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });

            document.getElementById('listTitle').textContent = 'Deadline: ' + dateLabel;
            document.getElementById('listSub').textContent =
                orders.length + ' pesanan deadline hari ini';
            renderOrderList(orders, ymd);
        }

        function renderOrderList(orders, filterDate) {
            const list = document.getElementById('orderList');
            const empty = document.getElementById('emptyState');
            list.innerHTML = '';

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (orders.length === 0) {
                list.style.display = 'none';
                empty.classList.remove('hidden');
                document.getElementById('emptySubtext').textContent =
                    filterDate ? 'Tidak ada pesanan deadline tanggal ini.' : 'Tidak ada pesanan aktif dengan deadline.';
                return;
            }

            list.style.display = '';
            empty.classList.add('hidden');

            orders.forEach(function(o) {
                const deadlineDate = new Date(o.deadline + 'T00:00:00');
                const diffDays = Math.ceil((deadlineDate - today) / 86400000);
                const isOverdue = diffDays < 0;
                const isUrgent = diffDays >= 0 && diffDays <= 2;
                const urgencyColor = isOverdue ? '#EF4444' : isUrgent ? '#F59E0B' : '#10B981';

                let deadlineBadge;
                if (isOverdue) {
                    deadlineBadge = '⚠ Lewat ' + Math.abs(diffDays) + ' hari';
                } else if (diffDays === 0) {
                    deadlineBadge = '⚡ Deadline hari ini';
                } else {
                    deadlineBadge = '📅 ' + diffDays + ' hari lagi';
                }

                const item = document.createElement('a');
                item.href = detailBase + '/' + o.id_order;
                item.className = 'block px-5 py-4 hover:bg-slate-50 transition cursor-pointer';

                item.innerHTML =
                    '<div class="flex gap-3 items-start">' +
                    '<div class="w-1 self-stretch rounded-full flex-shrink-0 mt-0.5" style="background:' +
                    urgencyColor + ';min-height:40px"></div>' +
                    '<div class="flex-1 min-w-0">' +
                    '<div class="flex justify-between items-start gap-2">' +
                    '<div>' +
                    '<p class="font-bold text-sm text-[#051747] leading-tight">' + escHtml(o.nama_produk) + '</p>' +
                    '<p class="text-xs text-slate-500 mt-0.5">' + escHtml(o.kode_order) + ' · ' + escHtml(o.nama_pelanggan) + '</p>' +
                    '</div>' +
                    '<span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0 ' +
                    (STATUS_BADGE[o.status] || 'bg-slate-100 text-slate-600') + '">' +
                    (STATUS_LABEL[o.status] || o.status) + '</span>' +
                    '</div>' +
                    '<div class="flex items-center gap-3 mt-2 flex-wrap">' +
                    '<span class="text-xs text-slate-500">📦 ' + o.jumlah_order + ' ' + escHtml(o.satuan) + '</span>' +
                    '<span class="text-xs ' + (parseInt(o.sisa_kuota, 10) <= 1 ? 'text-red-500 font-semibold' : 'text-slate-400') + '">' +
                    '🎨 ' + o.sisa_kuota + '/' + o.kuota_revisi + ' revisi</span>' +
                    '<span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:' +
                    (isOverdue ? '#FEE2E2' : isUrgent ? '#FEF3C7' : '#DCFCE7') + ';color:' +
                    (isOverdue ? '#991B1B' : isUrgent ? '#92400E' : '#166534') + '">' +
                    deadlineBadge + '</span>' +
                    '</div></div></div>';

                list.appendChild(item);
            });
        }

        document.getElementById('prevMonth').addEventListener('click', function() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            selectedDate = null;
            renderCalendar(currentYear, currentMonth);
            renderAllOrders();
        });

        document.getElementById('nextMonth').addEventListener('click', function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            selectedDate = null;
            renderCalendar(currentYear, currentMonth);
            renderAllOrders();
        });

        renderCalendar(currentYear, currentMonth);
        renderAllOrders();
    })();
</script>