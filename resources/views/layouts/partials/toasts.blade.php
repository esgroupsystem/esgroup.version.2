{{--
    Top-right toasts for the standalone Blade pages (login, lock screen, error pages).
    Same behaviour as the React app (resources/js/react/lib/notify.ts): every message
    hides after 5 seconds. Collects session flashes, laracasts flash() messages,
    validation errors, and an optional $toast passed in by the page.
--}}
@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
    $levelFromFlash = ['danger' => 'error', 'error' => 'error', 'warning' => 'warning', 'info' => 'info', 'success' => 'success'];
    $jgToasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $level) {
        if (is_string(session($level)) && session($level) !== '') {
            $jgToasts[] = ['level' => $level, 'title' => session($level)];
        }
    }

    foreach (collect(session('flash_notification', [])) as $item) {
        $jgToasts[] = ['level' => $levelFromFlash[$item->level ?? 'info'] ?? 'info', 'title' => (string) ($item->message ?? '')];
    }

    $jgErrors = isset($errors) ? $errors->all() : [];
    if (count($jgErrors) === 1) {
        $jgToasts[] = ['level' => 'error', 'title' => $jgErrors[0]];
    } elseif (count($jgErrors) > 1) {
        $jgToasts[] = ['level' => 'error', 'title' => 'Please check the highlighted fields.', 'description' => $jgErrors[0].' (+'.(count($jgErrors) - 1).' more)'];
    }

    if (isset($toast) && is_array($toast)) {
        $jgToasts[] = $toast;
    }
@endphp

<div class="jg-toasts" id="jgToasts" aria-live="polite" aria-atomic="false"></div>

<style>
    .jg-toasts {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 2147483000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: min(380px, calc(100vw - 32px));
        pointer-events: none;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }

    .jg-toast {
        pointer-events: auto;
        display: grid;
        grid-template-columns: 20px 1fr 20px;
        gap: 10px;
        align-items: start;
        padding: 12px 12px 12px 14px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #0f172a;
        box-shadow: 0 10px 30px -10px rgba(15, 23, 42, .35);
        font-size: 14px;
        line-height: 1.4;
        animation: jg-toast-in .25s ease-out both;
    }

    .jg-toast.is-leaving {
        animation: jg-toast-out .2s ease-in both;
    }

    .jg-toast svg {
        width: 18px;
        height: 18px;
        margin-top: 1px;
    }

    .jg-toast-title {
        font-weight: 600;
        word-break: break-word;
    }

    .jg-toast-desc {
        margin-top: 2px;
        font-size: 13px;
        opacity: .85;
        word-break: break-word;
    }

    .jg-toast-close {
        border: 0;
        background: transparent;
        padding: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        color: inherit;
        opacity: .55;
        cursor: pointer;
        border-radius: 4px;
    }

    .jg-toast-close:hover {
        opacity: 1;
    }

    .jg-toast-close svg {
        width: 14px;
        height: 14px;
        margin: 0;
    }

    .jg-toast[data-level="success"] { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .jg-toast[data-level="error"] { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .jg-toast[data-level="warning"] { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .jg-toast[data-level="info"] { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

    @keyframes jg-toast-in {
        from { opacity: 0; transform: translateX(16px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes jg-toast-out {
        to { opacity: 0; transform: translateX(16px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .jg-toast, .jg-toast.is-leaving { animation: none; }
    }
</style>

<script nonce="{{ $nonce }}">
    (function() {
        var DURATION = 5000;
        var icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><path d="M15 9l-6 6"/><path d="m9 9 6 6"/></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
        };
        var closeIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';

        function text(value) {
            var span = document.createElement('div');
            span.textContent = value;
            return span;
        }

        function dismiss(node) {
            if (!node.isConnected || node.classList.contains('is-leaving')) return;
            node.classList.add('is-leaving');
            setTimeout(function() { node.remove(); }, 200);
        }

        /** window.jgToast('error', 'Title', 'Optional description') */
        window.jgToast = function(level, title, description) {
            var root = document.getElementById('jgToasts');
            if (!root || !title) return;
            level = icons[level] ? level : 'info';

            var node = document.createElement('div');
            node.className = 'jg-toast';
            node.dataset.level = level;
            node.setAttribute('role', level === 'error' ? 'alert' : 'status');

            var icon = document.createElement('span');
            icon.innerHTML = icons[level];

            var body = document.createElement('div');
            var heading = text(title);
            heading.className = 'jg-toast-title';
            body.appendChild(heading);
            if (description) {
                var desc = text(description);
                desc.className = 'jg-toast-desc';
                body.appendChild(desc);
            }

            var close = document.createElement('button');
            close.type = 'button';
            close.className = 'jg-toast-close';
            close.setAttribute('aria-label', 'Close');
            close.innerHTML = closeIcon;
            close.addEventListener('click', function() { dismiss(node); });

            node.appendChild(icon);
            node.appendChild(body);
            node.appendChild(close);
            root.appendChild(node);

            var timer = setTimeout(function() { dismiss(node); }, DURATION);
            node.addEventListener('mouseenter', function() { clearTimeout(timer); });
            node.addEventListener('mouseleave', function() { timer = setTimeout(function() { dismiss(node); }, 2000); });
        };

        var queued = @json($jgToasts);
        function flush() {
            queued.forEach(function(item) { window.jgToast(item.level, item.title, item.description || ''); });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', flush);
        } else {
            flush();
        }
    })();
</script>
