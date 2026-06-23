@props([
    'code' => '500',
    'title' => 'System Error',
    'message' => 'Something went wrong while processing your request.',
    'icon' => 'fas fa-triangle-exclamation',
    'tone' => 'danger',
    'details' => [],
    'showReload' => true,
])

@php
    use Illuminate\Support\Facades\Route;

    $dashboardUrl = Route::has('dashboard.index') ? route('dashboard.index') : url('/dashboard');

    $toneMap = [
        'primary' => [
            'soft' => 'rgba(44, 123, 229, .10)',
            'main' => '#2c7be5',
            'deep' => '#1b5fc1',
            'border' => 'rgba(44, 123, 229, .22)',
            'shadow' => 'rgba(44, 123, 229, .20)',
        ],
        'danger' => [
            'soft' => 'rgba(230, 55, 87, .10)',
            'main' => '#e63757',
            'deep' => '#c82343',
            'border' => 'rgba(230, 55, 87, .22)',
            'shadow' => 'rgba(230, 55, 87, .20)',
        ],
        'warning' => [
            'soft' => 'rgba(245, 128, 62, .12)',
            'main' => '#f5803e',
            'deep' => '#d96b2e',
            'border' => 'rgba(245, 128, 62, .25)',
            'shadow' => 'rgba(245, 128, 62, .20)',
        ],
        'info' => [
            'soft' => 'rgba(0, 168, 200, .11)',
            'main' => '#00a8c8',
            'deep' => '#078da7',
            'border' => 'rgba(0, 168, 200, .24)',
            'shadow' => 'rgba(0, 168, 200, .20)',
        ],
        'secondary' => [
            'soft' => 'rgba(116, 129, 148, .12)',
            'main' => '#748194',
            'deep' => '#5c6878',
            'border' => 'rgba(116, 129, 148, .25)',
            'shadow' => 'rgba(116, 129, 148, .18)',
        ],
    ];

    $theme = $toneMap[$tone] ?? $toneMap['danger'];

    $safeDetails = collect(is_array($details) ? $details : [$details])
        ->filter(fn($detail) => filled($detail))
        ->values()
        ->all();

    $statusText = match ((int) $code) {
        401 => 'Unauthorized request',
        403 => 'Access blocked',
        404 => 'Page not found',
        419 => 'Session expired',
        429 => 'Too many requests',
        500 => 'Server error',
        503 => 'Service unavailable',
        default => 'Request failed',
    };

    $requestId = strtoupper(substr(hash('sha256', request()->fullUrl() . now()->timestamp), 0, 10));
@endphp

<style>
    body {
        overflow: hidden;
    }

    .jg-error-page {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 12% 12%, {{ $theme['soft'] }}, transparent 34%),
            radial-gradient(circle at 88% 18%, rgba(44, 123, 229, .11), transparent 32%),
            linear-gradient(135deg, #f7f9fc 0%, #eef3f9 48%, #f8fafc 100%);
    }

    .jg-error-page::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(15, 34, 58, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 34, 58, .035) 1px, transparent 1px);
        background-size: 42px 42px;
        mask-image: radial-gradient(circle at center, black 0%, transparent 78%);
        pointer-events: none;
    }

    .jg-error-shell {
        min-height: 100vh;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }

    .jg-error-card {
        width: min(980px, 100%);
        display: grid;
        grid-template-columns: .95fr 1.05fr;
        overflow: hidden;
        border-radius: 2rem;
        background: rgba(255, 255, 255, .95);
        border: 1px solid rgba(216, 226, 239, .95);
        box-shadow:
            0 2rem 5rem rgba(15, 34, 58, .16),
            0 0 0 1px rgba(255, 255, 255, .74) inset;
        backdrop-filter: blur(18px);
    }

    .jg-error-visual {
        position: relative;
        min-height: 520px;
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(circle at 24% 20%, {{ $theme['soft'] }}, transparent 36%),
            linear-gradient(160deg, #ffffff 0%, #f3f7fc 100%);
        border-right: 1px solid #e6edf7;
    }

    .jg-error-visual-box {
        position: relative;
        width: min(310px, 100%);
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .jg-error-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 1px dashed {{ $theme['border'] }};
        animation: jgErrorSpin 18s linear infinite;
    }

    .jg-error-ring::before,
    .jg-error-ring::after {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: {{ $theme['main'] }};
        box-shadow: 0 .75rem 1.5rem {{ $theme['shadow'] }};
    }

    .jg-error-ring::before {
        top: 26px;
        left: 46px;
    }

    .jg-error-ring::after {
        right: 30px;
        bottom: 52px;
        background: #2c7be5;
    }

    .jg-error-icon-wrap {
        width: 164px;
        height: 164px;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 2rem;
        background:
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e6edf7;
        box-shadow:
            0 1.25rem 3rem rgba(15, 34, 58, .13),
            0 0 0 12px {{ $theme['soft'] }};
        color: {{ $theme['main'] }};
        font-size: 4rem;
    }

    .jg-error-code-large {
        position: absolute;
        right: -8px;
        bottom: 18px;
        z-index: 3;
        min-width: 104px;
        text-align: center;
        padding: .65rem .9rem;
        border-radius: 999px;
        background: {{ $theme['main'] }};
        color: #ffffff;
        font-weight: 900;
        letter-spacing: .08em;
        box-shadow: 0 1rem 2rem {{ $theme['shadow'] }};
    }

    .jg-error-content {
        padding: 2.25rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .jg-error-badge {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .4rem .75rem;
        margin-bottom: 1rem;
        border-radius: 999px;
        background: {{ $theme['soft'] }};
        color: {{ $theme['deep'] }};
        border: 1px solid {{ $theme['border'] }};
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .jg-error-title {
        margin: 0 0 .65rem;
        color: #172b4d;
        font-size: clamp(1.65rem, 3vw, 2.25rem);
        font-weight: 900;
        letter-spacing: -.035em;
        line-height: 1.12;
    }

    .jg-error-message {
        margin: 0;
        max-width: 560px;
        color: #64748b;
        font-size: 1rem;
        line-height: 1.75;
    }

    .jg-error-diagnostics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
        margin: 1.5rem 0;
    }

    .jg-error-diagnostic-item {
        padding: .9rem;
        border-radius: 1rem;
        background: #f8fafc;
        border: 1px solid #e6edf7;
    }

    .jg-error-diagnostic-label {
        margin-bottom: .2rem;
        color: #8a98ad;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .jg-error-diagnostic-value {
        color: #344050;
        font-size: .88rem;
        font-weight: 800;
        word-break: break-word;
    }

    .jg-error-details {
        margin-bottom: 1.25rem;
        border-radius: 1rem;
        border: 1px solid #e6edf7;
        background: #ffffff;
        overflow: hidden;
    }

    .jg-error-details summary {
        cursor: pointer;
        list-style: none;
        padding: 1rem;
        color: #344050;
        font-size: .85rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .jg-error-details summary::-webkit-details-marker {
        display: none;
    }

    .jg-error-details summary::after {
        content: "\f078";
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free";
        font-weight: 900;
        color: #8a98ad;
        font-size: .75rem;
        transition: transform .18s ease-in-out;
    }

    .jg-error-details[open] summary::after {
        transform: rotate(180deg);
    }

    .jg-error-details ul {
        margin: 0;
        padding: 0 1rem 1rem 2rem;
        color: #64748b;
        font-size: .9rem;
        line-height: 1.7;
    }

    .jg-error-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .jg-error-primary-action,
    .jg-error-secondary-action {
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        border-radius: 999px;
        padding: .8rem 1.15rem;
        font-weight: 800;
        text-decoration: none;
        transition: .18s ease-in-out;
    }

    .jg-error-primary-action {
        flex: 1 1 230px;
        border: 0;
        color: #ffffff;
        background: {{ $theme['main'] }};
        box-shadow: 0 .85rem 1.65rem {{ $theme['shadow'] }};
    }

    .jg-error-primary-action:hover {
        color: #ffffff;
        transform: translateY(-1px);
        background: {{ $theme['deep'] }};
    }

    .jg-error-secondary-action {
        flex: 0 1 150px;
        border: 1px solid #d8e2ef;
        color: #344050;
        background: #ffffff;
    }

    .jg-error-secondary-action:hover {
        color: #172b4d;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .jg-error-footer {
        margin-top: 1.25rem;
        color: #8a98ad;
        font-size: .78rem;
        line-height: 1.6;
    }

    @keyframes jgErrorSpin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 991.98px) {
        .jg-error-card {
            grid-template-columns: 1fr;
        }

        .jg-error-visual {
            min-height: 280px;
            border-right: 0;
            border-bottom: 1px solid #e6edf7;
        }

        .jg-error-visual-box {
            width: 230px;
        }

        .jg-error-icon-wrap {
            width: 128px;
            height: 128px;
            font-size: 3rem;
        }
    }

    @media (max-width: 575.98px) {
        .jg-error-shell {
            padding: .85rem;
        }

        .jg-error-card {
            border-radius: 1.35rem;
        }

        .jg-error-content {
            padding: 1.35rem;
        }

        .jg-error-diagnostics {
            grid-template-columns: 1fr;
        }

        .jg-error-actions {
            flex-direction: column;
        }

        .jg-error-primary-action,
        .jg-error-secondary-action {
            width: 100%;
            flex: 1 1 auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .jg-error-ring {
            animation: none;
        }

        .jg-error-primary-action,
        .jg-error-secondary-action {
            transition: none;
        }
    }
</style>

<main class="jg-error-page">
    <div class="jg-error-shell">
        <section class="jg-error-card" role="alert" aria-labelledby="jg-error-title">
            <div class="jg-error-visual" aria-hidden="true">
                <div class="jg-error-visual-box">
                    <div class="jg-error-ring"></div>

                    <div class="jg-error-icon-wrap">
                        <span class="{{ $icon }}"></span>
                    </div>

                    <div class="jg-error-code-large">
                        {{ $code }}
                    </div>
                </div>
            </div>

            <div class="jg-error-content">
                <div class="jg-error-badge">
                    <span class="fas fa-circle-info"></span>
                    {{ $statusText }}
                </div>

                <h1 id="jg-error-title" class="jg-error-title">
                    {{ $title }}
                </h1>

                <p class="jg-error-message">
                    {{ $message }}
                </p>

                <div class="jg-error-diagnostics">
                    <div class="jg-error-diagnostic-item">
                        <div class="jg-error-diagnostic-label">
                            Error Code
                        </div>
                        <div class="jg-error-diagnostic-value">
                            {{ $code }}
                        </div>
                    </div>

                    <div class="jg-error-diagnostic-item">
                        <div class="jg-error-diagnostic-label">
                            Status
                        </div>
                        <div class="jg-error-diagnostic-value">
                            {{ $statusText }}
                        </div>
                    </div>

                    <div class="jg-error-diagnostic-item">
                        <div class="jg-error-diagnostic-label">
                            Request ID
                        </div>
                        <div class="jg-error-diagnostic-value">
                            {{ $requestId }}
                        </div>
                    </div>
                </div>

                @if (!empty($safeDetails))
                    <details class="jg-error-details">
                        <summary>
                            What happened
                        </summary>

                        <ul>
                            @foreach ($safeDetails as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                <div class="jg-error-actions">
                    <a href="{{ $dashboardUrl }}" class="jg-error-primary-action">
                        <span class="fas fa-check-circle"></span>
                        Okay, Return to Dashboard
                    </a>

                    @if ($showReload)
                        <button type="button" class="jg-error-secondary-action" onclick="window.location.reload()">
                            <span class="fas fa-rotate-right"></span>
                            Try Again
                        </button>
                    @endif
                </div>

                <div class="jg-error-footer">
                    The system stopped this request safely. No action was submitted twice.
                    <br>
                    © {{ date('Y') }} Jell Group of Company
                </div>
            </div>
        </section>
    </div>
</main>
