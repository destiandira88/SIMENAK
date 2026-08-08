<?php
$flashMap = [
    'success' => [
        'bg'      => 'bg-emerald-50',
        'border'  => 'border-emerald-200/80',
        'text'    => 'text-emerald-900',
        'iconBg'  => 'bg-emerald-500',
        'bar'     => 'bg-emerald-500',
        'iconSvg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
    ],
    'error' => [
        'bg'      => 'bg-[#FEE2E2]',
        'border'  => 'border-[#FECACA]',
        'text'    => 'text-[#991B1B]',
        'iconBg'  => 'bg-red-500',
        'bar'     => 'bg-red-500',
        'iconSvg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
    ],
    'warning' => [
        'bg'      => 'bg-amber-50',
        'border'  => 'border-amber-200/80',
        'text'    => 'text-amber-900',
        'iconBg'  => 'bg-amber-500',
        'bar'     => 'bg-amber-500',
        'iconSvg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
    ],
    'info' => [
        'bg'      => 'bg-sky-50',
        'border'  => 'border-sky-200/80',
        'text'    => 'text-sky-900',
        'iconBg'  => 'bg-sky-500',
        'bar'     => 'bg-sky-500',
        'iconSvg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ],
];

$flashMessages = [];

foreach (array_keys($flashMap) as $flashKey) {
    $message = session()->getFlashdata($flashKey);

    if ($message !== null && $message !== '') {
        $flashMessages[] = [
            'type'    => $flashKey,
            'message' => (string) $message,
            'style'   => $flashMap[$flashKey],
        ];
    }
}
?>
<style>
    #flash-toast-stack {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: min(100vw - 2rem, 420px);
        pointer-events: none;
    }

    .flash-toast {
        pointer-events: auto;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        border-width: 1px;
        box-shadow: 0 10px 40px rgba(5, 23, 71, 0.12), 0 2px 8px rgba(5, 23, 71, 0.06);
        transform: translateX(calc(100% + 1.5rem));
        opacity: 0;
        animation: flashToastSlideIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .flash-toast.is-leaving {
        animation: flashToastSlideOut 0.35s cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    @keyframes flashToastSlideIn {
        from {
            transform: translateX(calc(100% + 1.5rem));
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes flashToastSlideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(calc(100% + 1.5rem));
            opacity: 0;
        }
    }

    .flash-toast-body {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        padding: 1rem 1rem 0.75rem;
    }

    .flash-toast-icon {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .flash-toast-icon svg {
        width: 1.125rem;
        height: 1.125rem;
        stroke: currentColor;
        fill: none;
        stroke-width: 2.25;
    }

    .flash-toast-message {
        flex: 1;
        min-width: 0;
        padding-top: 0.125rem;
        font-size: 0.875rem;
        line-height: 1.45;
        font-weight: 500;
    }

    .flash-toast-close {
        flex-shrink: 0;
        width: 1.75rem;
        height: 1.75rem;
        border: 0;
        background: transparent;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: rgba(5, 23, 71, 0.35);
        cursor: pointer;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    .flash-toast-close:hover {
        color: rgba(5, 23, 71, 0.65);
        background: rgba(5, 23, 71, 0.06);
    }

    .flash-toast-close svg {
        width: 1rem;
        height: 1rem;
    }

    .flash-toast-track {
        height: 3px;
        background: rgba(5, 23, 71, 0.08);
    }

    .flash-toast-progress {
        display: block;
        height: 100%;
        width: 100%;
        transform-origin: left center;
    }
</style>

<?php if ($flashMessages !== []): ?>
<div id="flash-toast-stack" aria-live="polite" aria-atomic="true">
    <?php foreach ($flashMessages as $flash): ?>
        <?php $s = $flash['style']; ?>
        <div class="flash-toast border <?= esc($s['border']) ?> <?= esc($s['bg']) ?>" data-flash-toast role="alert">
            <div class="flash-toast-body">
                <div class="flash-toast-icon <?= esc($s['iconBg']) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><?= $s['iconSvg'] ?></svg>
                </div>
                <p class="flash-toast-message <?= esc($s['text']) ?>"><?= esc($flash['message']) ?></p>
                <button type="button" class="flash-toast-close" data-flash-toast-close aria-label="Tutup notifikasi">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flash-toast-track">
                <span class="flash-toast-progress <?= esc($s['bar']) ?>" data-flash-toast-progress></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
    (function() {
        const DURATION = 5000;

        function initFlashToast(el) {
            const progress = el.querySelector('[data-flash-toast-progress]');
            const closeBtn = el.querySelector('[data-flash-toast-close]');
            if (!progress) return;

            let remaining = DURATION;
            let startedAt = 0;
            let timerId = null;
            let leaving = false;

            function syncProgress() {
                progress.style.transition = 'none';
                progress.style.width = '100%';
                progress.offsetWidth;
                progress.style.transition = 'width ' + remaining + 'ms linear';
                progress.style.width = '0%';
            }

            function clearTimer() {
                if (timerId !== null) {
                    clearTimeout(timerId);
                    timerId = null;
                }
            }

            function dismiss() {
                if (leaving) return;
                leaving = true;
                clearTimer();
                progress.style.transition = 'none';
                el.classList.add('is-leaving');
                window.setTimeout(function() {
                    el.remove();
                    const stack = document.getElementById('flash-toast-stack');
                    if (stack && stack.children.length === 0) {
                        stack.remove();
                    }
                }, 350);
            }

            function startCountdown() {
                clearTimer();
                startedAt = Date.now();
                syncProgress();
                timerId = window.setTimeout(dismiss, remaining);
            }

            function pauseCountdown() {
                if (leaving) return;
                clearTimer();
                remaining = Math.max(0, remaining - (Date.now() - startedAt));
                const computed = window.getComputedStyle(progress);
                progress.style.transition = 'none';
                progress.style.width = computed.width;
            }

            function resumeCountdown() {
                if (leaving || remaining <= 0) return;
                startCountdown();
            }

            closeBtn?.addEventListener('click', dismiss);
            el.addEventListener('mouseenter', pauseCountdown);
            el.addEventListener('mouseleave', resumeCountdown);
            el.addEventListener('focusin', pauseCountdown);
            el.addEventListener('focusout', resumeCountdown);

            startCountdown();
        }

        window.initFlashToast = initFlashToast;
        document.querySelectorAll('[data-flash-toast]').forEach(initFlashToast);
    })();
</script>
