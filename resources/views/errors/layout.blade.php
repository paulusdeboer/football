@php
    $status = $status ?? 500;
    $variant = $variant ?? 'orange';
    $eyebrow = $eyebrow ?? __('Something went wrong');
    $title = $title ?? __('Something went wrong');
    $message = $message ?? __('Something went wrong on our side. Try again in a moment.');
    $primaryUrl = $primaryUrl ?? url('/');
    $primaryLabel = $primaryLabel ?? __('Back to home');
    $secondaryUrl = $secondaryUrl ?? null;
    $secondaryLabel = $secondaryLabel ?? null;
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Montserrat:600,700,800|Poppins:300,400,500,600,700" rel="stylesheet">
    <title>{{ $status }} · {{ __('app_name') }}</title>
    <style>
        :root {
            --error-navy-950: #0d1d2c;
            --error-navy-900: #132536;
            --error-navy-800: #1b3448;
            --error-ink: #1b2a39;
            --error-muted: #718092;
            --error-surface: #f5f8fc;
            --error-card: rgba(255, 255, 255, 0.86);
            --error-line: #dce5ef;
            --error-accent: #e97817;
            --error-accent-dark: #c95c0c;
            --error-accent-soft: #fff1e5;
            --error-field: #edf3fa;
        }

        .error-page--blue {
            --error-accent: #3678b8;
            --error-accent-dark: #265e93;
            --error-accent-soft: #eaf3fc;
            --error-field: #e7f1fb;
        }

        .error-page--red {
            --error-accent: #cc4450;
            --error-accent-dark: #a9323d;
            --error-accent-soft: #fdecef;
            --error-field: #faeaed;
        }

        .error-page--green {
            --error-accent: #159a68;
            --error-accent-dark: #0e7650;
            --error-accent-soft: #e7f8f0;
            --error-field: #e8f7f0;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-width: 320px;
            color: var(--error-ink);
            background:
                radial-gradient(900px 460px at 0% -14%, color-mix(in srgb, var(--error-accent) 13%, transparent), transparent 68%),
                radial-gradient(720px 440px at 100% 110%, rgba(54, 120, 184, 0.13), transparent 65%),
                var(--error-surface);
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        a {
            color: inherit;
        }

        .error-page {
            position: relative;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        .error-page::after {
            position: absolute;
            right: -8rem;
            bottom: -10rem;
            width: 28rem;
            height: 28rem;
            border: 1px solid color-mix(in srgb, var(--error-accent) 15%, transparent);
            border-radius: 50%;
            box-shadow:
                0 0 0 2.5rem color-mix(in srgb, var(--error-accent) 5%, transparent),
                0 0 0 5rem color-mix(in srgb, var(--error-accent) 3%, transparent);
            content: '';
            pointer-events: none;
        }

        .error-shell {
            position: relative;
            z-index: 1;
            display: flex;
            width: min(1180px, calc(100% - 3rem));
            min-height: 100vh;
            margin: 0 auto;
            flex-direction: column;
            padding: 1.75rem 0 1.15rem;
        }

        .error-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            color: var(--error-navy-900);
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.035em;
            text-decoration: none;
        }

        .error-brand__mark {
            display: grid;
            width: 2.65rem;
            height: 2.65rem;
            place-items: center;
            border: 1px solid color-mix(in srgb, var(--error-accent) 28%, white);
            border-radius: 0.8rem;
            background: var(--error-accent);
            box-shadow: 0 8px 18px color-mix(in srgb, var(--error-accent) 24%, transparent);
        }

        .error-brand__mark img {
            width: 1.65rem;
            height: 1.65rem;
            filter: brightness(0) invert(1);
        }

        .error-status {
            padding: 0.48rem 0.75rem;
            border: 1px solid var(--error-line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            color: var(--error-muted);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .error-card {
            display: grid;
            min-height: 0;
            margin: auto 0;
            grid-template-columns: minmax(0, 1.02fr) minmax(340px, 0.98fr);
            align-items: center;
            gap: clamp(2rem, 6vw, 6.5rem);
            padding: clamp(1.5rem, 4vw, 4.3rem);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 2rem;
            background: var(--error-card);
            box-shadow: 0 22px 55px rgba(25, 48, 70, 0.09);
            backdrop-filter: blur(16px);
        }

        .error-card__visual {
            position: relative;
            display: grid;
            min-height: 330px;
            place-items: center;
        }

        .error-illustration {
            display: block;
            width: min(100%, 520px);
            height: auto;
            overflow: visible;
        }

        .error-illustration__orbit {
            fill: none;
            stroke: color-mix(in srgb, var(--error-accent) 26%, transparent);
            stroke-dasharray: 5 12;
            stroke-width: 2;
        }

        .error-illustration__panel {
            fill: rgba(255, 255, 255, 0.8);
            stroke: color-mix(in srgb, var(--error-accent) 19%, white);
            stroke-width: 2;
        }

        .error-illustration__field {
            fill: var(--error-field);
            stroke: color-mix(in srgb, var(--error-accent) 27%, white);
            stroke-width: 2;
        }

        .error-illustration__field-line {
            fill: none;
            stroke: color-mix(in srgb, var(--error-accent) 34%, white);
            stroke-width: 2;
        }

        .error-illustration__spot {
            fill: var(--error-accent);
            opacity: 0.8;
        }

        .error-illustration__ball {
            fill: var(--error-accent);
            stroke: #fff;
            stroke-width: 5;
        }

        .error-illustration__ball-line {
            fill: none;
            stroke: rgba(255, 255, 255, 0.88);
            stroke-linecap: round;
            stroke-width: 3;
        }

        .error-illustration__spark {
            fill: var(--error-accent);
        }

        .error-visual__code {
            position: absolute;
            top: 50%;
            left: 50%;
            color: color-mix(in srgb, var(--error-accent) 78%, var(--error-navy-900));
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: clamp(5.2rem, 13vw, 8.6rem);
            font-weight: 800;
            letter-spacing: -0.1em;
            line-height: 0.9;
            opacity: 0.14;
            transform: translate(-50%, -52%);
            user-select: none;
        }

        .error-card__tag {
            position: absolute;
            right: 7%;
            bottom: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.48rem 0.72rem;
            border: 1px solid color-mix(in srgb, var(--error-accent) 18%, white);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            color: var(--error-accent-dark);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .error-card__tag::before {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 50%;
            background: var(--error-accent);
            box-shadow: 0 0 0 4px var(--error-accent-soft);
            content: '';
        }

        .error-card__content {
            max-width: 34rem;
        }

        .error-kicker {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1.05rem;
            color: var(--error-muted);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .error-kicker__code {
            display: inline-grid;
            width: 2.1rem;
            height: 2.1rem;
            place-items: center;
            border-radius: 0.62rem;
            background: var(--error-accent-soft);
            color: var(--error-accent-dark);
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .error-card h1 {
            max-width: 14ch;
            margin: 0 0 1rem;
            color: var(--error-navy-900);
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            font-size: clamp(2.05rem, 4vw, 3.8rem);
            font-weight: 700;
            letter-spacing: -0.065em;
            line-height: 1.02;
        }

        .error-card__message {
            max-width: 31rem;
            margin: 0;
            color: var(--error-muted);
            font-size: clamp(0.92rem, 1.3vw, 1.04rem);
            line-height: 1.75;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1.8rem;
        }

        .error-button {
            display: inline-flex;
            min-height: 2.9rem;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.72rem 1.05rem;
            border: 1px solid transparent;
            border-radius: 0.72rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .error-button:hover {
            transform: translateY(-2px);
        }

        .error-button--primary {
            background: var(--error-accent);
            color: #fff;
            box-shadow: 0 8px 16px color-mix(in srgb, var(--error-accent) 24%, transparent);
        }

        .error-button--primary:hover {
            background: var(--error-accent-dark);
            color: #fff;
            box-shadow: 0 12px 22px color-mix(in srgb, var(--error-accent) 28%, transparent);
        }

        .error-button--secondary {
            border-color: var(--error-line);
            background: rgba(255, 255, 255, 0.75);
            color: var(--error-navy-800);
        }

        .error-button--secondary:hover {
            border-color: color-mix(in srgb, var(--error-accent) 25%, var(--error-line));
            background: var(--error-accent-soft);
            color: var(--error-accent-dark);
        }

        .error-button__arrow {
            width: 0.95rem;
            height: 0.95rem;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .error-meta {
            margin: 1.7rem 0 0;
            color: color-mix(in srgb, var(--error-muted) 82%, white);
            font-size: 0.7rem;
        }

        .error-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding-top: 1.25rem;
            color: var(--error-muted);
            font-size: 0.72rem;
        }

        .error-footer__line {
            width: 2rem;
            height: 1px;
            background: var(--error-line);
        }

        @media (max-width: 860px) {
            .error-card {
                grid-template-columns: 1fr;
                gap: 0.8rem;
                margin: 4rem 0 auto;
            }

            .error-card__visual {
                min-height: 260px;
            }

            .error-card__content {
                max-width: 40rem;
                margin: 0 auto;
                text-align: center;
            }

            .error-kicker,
            .error-actions {
                justify-content: center;
            }

            .error-card h1,
            .error-card__message {
                margin-right: auto;
                margin-left: auto;
            }
        }

        @media (max-width: 520px) {
            .error-shell {
                width: min(100% - 1.4rem, 1180px);
                padding-top: 1.1rem;
            }

            .error-brand {
                font-size: 1.05rem;
            }

            .error-brand__mark {
                width: 2.25rem;
                height: 2.25rem;
            }

            .error-brand__mark img {
                width: 1.35rem;
                height: 1.35rem;
            }

            .error-status {
                padding: 0.4rem 0.58rem;
                font-size: 0.62rem;
            }

            .error-card {
                margin-top: 2.2rem;
                padding: 1.2rem;
                border-radius: 1.35rem;
            }

            .error-card__visual {
                min-height: 210px;
            }

            .error-card__tag {
                right: 4%;
                font-size: 0.58rem;
            }

            .error-card h1 {
                font-size: 2.2rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .error-button {
                width: min(100%, 18rem);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-button {
                transition: none;
            }

            .error-button:hover {
                transform: none;
            }
        }
    </style>
</head>
<body class="error-page error-page--{{ $variant }}">
    <main class="error-shell">
        <header class="error-header">
            <a class="error-brand" href="{{ url('/') }}" aria-label="{{ __('app_name') }}">
                <span class="error-brand__mark">
                    <img src="{{ asset('favicon.svg') }}" alt="" aria-hidden="true">
                </span>
                <span>{{ __('app_name') }}</span>
            </a>
            <span class="error-status">{{ __('Error code :code', ['code' => $status]) }}</span>
        </header>

        <section class="error-card" aria-labelledby="error-title">
            <div class="error-card__visual">
                <svg class="error-illustration" viewBox="0 0 520 390" role="img" aria-label="{{ __('Error code :code', ['code' => $status]) }}">
                    <circle class="error-illustration__orbit" cx="260" cy="192" r="157"></circle>
                    <rect class="error-illustration__panel" x="44" y="52" width="432" height="280" rx="30"></rect>
                    <rect class="error-illustration__field" x="66" y="74" width="388" height="236" rx="19"></rect>
                    <path class="error-illustration__field-line" d="M260 74v236M66 192h388"></path>
                    <circle class="error-illustration__field-line" cx="260" cy="192" r="45"></circle>
                    <circle class="error-illustration__spot" cx="260" cy="192" r="4"></circle>
                    <path class="error-illustration__field-line" d="M66 134h50a27 27 0 0 1 27 27v62a27 27 0 0 1-27 27H66M454 134h-50a27 27 0 0 0-27 27v62a27 27 0 0 0 27 27h50"></path>
                    <path class="error-illustration__field-line" d="M66 172h15M66 212h15M454 172h-15M454 212h-15"></path>
                    <g transform="translate(390 95) rotate(18)">
                        <circle class="error-illustration__ball" cx="0" cy="0" r="28"></circle>
                        <path class="error-illustration__ball-line" d="M-9-18 0-7l13-4M-25 7l15-1 8 17M13-11l4 14-10 10M-10-1l10-6 10 7"></path>
                    </g>
                    <path class="error-illustration__spark" d="m102 83 5 13 13 5-13 5-5 13-5-13-13-5 13-5 5-13Z"></path>
                    <path class="error-illustration__spark" d="m420 275 4 10 10 4-10 4-4 10-4-10-10-4 10-4 4-10Z"></path>
                </svg>
                <div class="error-visual__code" aria-hidden="true">{{ $status }}</div>
                <div class="error-card__tag">{{ __('Keep your eye on the ball') }}</div>
            </div>

            <div class="error-card__content">
                <div class="error-kicker">
                    <span class="error-kicker__code">{{ $status }}</span>
                    <span>{{ $eyebrow }}</span>
                </div>
                <h1 id="error-title">{{ $title }}</h1>
                <p class="error-card__message">{{ $message }}</p>

                <div class="error-actions">
                    <a class="error-button error-button--primary" href="{{ $primaryUrl }}">
                        {{ $primaryLabel }}
                        <svg class="error-button__arrow" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M2 8h11M9 4l4 4-4 4"></path>
                        </svg>
                    </a>
                    @if ($secondaryUrl && $secondaryLabel)
                        <a class="error-button error-button--secondary" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                    @endif
                </div>

                <p class="error-meta">{{ __('Error code :code', ['code' => $status]) }}</p>
            </div>
        </section>

        <footer class="error-footer">
            <span class="error-footer__line"></span>
            <span>{{ __('app_name') }}</span>
            <span class="error-footer__line"></span>
        </footer>
    </main>
</body>
</html>
