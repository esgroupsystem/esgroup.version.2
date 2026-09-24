{{--
    One template for every HTTP error page (errors/{code}.blade.php include it).
    Wording comes from App\Support\HttpStatusMessage so pages and toasts match.
--}}
@extends('layouts.auth')

@php
    $status = \App\Support\HttpStatusMessage::for((int) ($code ?? 500));
    $signedIn = auth()->check();
    // Show the exception's own message only for deliberate aborts (abort(403, '...')), never raw
    // server errors or framework messages that name internal classes/routes.
    $rawMessage = isset($exception) && method_exists($exception, 'getMessage') ? (string) $exception->getMessage() : '';
    $detail = in_array($status['code'], [403, 409, 429], true) && $rawMessage !== '' && ! str_contains($rawMessage, '\\')
        ? $rawMessage
        : null;
    $toast = ['level' => $status['code'] >= 500 ? 'error' : 'warning', 'title' => $status['code'].' '.$status['title'], 'description' => $detail ?? $status['message']];
@endphp

@section('title', $status['code'].' '.$status['title'].' | Jell Group of Company')
@section('body_class', 'er-body')

@push('styles')
    <style>
        .er-body {
            min-height: 100vh;
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(37, 99, 235, .10), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(14, 165, 233, .10), transparent 60%),
                #f8fafc;
            color: #0f172a;
        }

        .er-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px 16px;
        }

        .er-card {
            width: min(520px, 100%);
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 20px 50px -24px rgba(15, 23, 42, .35);
            padding: 40px 36px 32px;
            text-align: center;
        }

        .er-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #334155;
        }

        .er-brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .er-code {
            margin: 0;
            font-size: 72px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -.04em;
            background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .er-title {
            margin: 12px 0 8px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .er-message {
            margin: 0 auto;
            max-width: 380px;
            font-size: 15px;
            line-height: 1.6;
            color: #64748b;
        }

        .er-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 28px;
        }

        .er-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f172a;
            font: inherit;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }

        .er-btn:hover {
            background: #f1f5f9;
        }

        .er-btn-primary {
            border-color: #1d63ed;
            background: #1d63ed;
            color: #fff;
        }

        .er-btn-primary:hover {
            background: #1552cc;
        }

        .er-btn svg {
            width: 16px;
            height: 16px;
        }

        .er-foot {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
@endpush

@section('content')
    <main class="er-page">
        <div class="er-card">
            <div class="er-brand">
                <img src="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}" alt="">
                Jell Group of Company
            </div>

            <p class="er-code">{{ $status['code'] }}</p>
            <h1 class="er-title">{{ $status['title'] }}</h1>
            <p class="er-message">{{ $detail ?? $status['message'] }}</p>

            <div class="er-actions">
                <button type="button" class="er-btn" id="erBack">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7" /><path d="M19 12H5" /></svg>
                    Go back
                </button>

                @if ($status['code'] === 419)
                    <a class="er-btn er-btn-primary" href="{{ url()->previous() }}">Refresh page</a>
                @elseif ($status['code'] === 503)
                    <a class="er-btn er-btn-primary" href="{{ url('/') }}">Try again</a>
                @elseif ($signedIn)
                    <a class="er-btn er-btn-primary" href="{{ route('dashboard.index') }}">Go to Dashboard</a>
                @else
                    <a class="er-btn er-btn-primary" href="{{ route('login') }}">Sign in</a>
                @endif
            </div>

            <p class="er-foot">Need help? Contact the IT Department.</p>
        </div>
    </main>

    @push('scripts')
        <script nonce="{{ app()->bound('csp_nonce') ? app('csp_nonce') : '' }}">
            document.getElementById('erBack').addEventListener('click', function() {
                if (history.length > 1) history.back();
                else window.location.href = @json($signedIn ? route('dashboard.index') : route('login'));
            });
        </script>
    @endpush
@endsection
