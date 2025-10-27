<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>2FA — GeoProfs</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="page page--auth">
<aside class="visual">
    <div class="visual__overlay"></div>
    <div class="visual__brand">
        <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" class="logo">
            <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 6l6 6" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        <span>GeoProfs</span>
    </div>
</aside>

<main class="card-2fa" role="main" aria-labelledby="title">
    <header class="card__header" style="margin-bottom: 1rem;">
        <h1 id="title" style="margin:0;">Voer je 2FA-code in</h1>
        <p class="muted" style="margin:.25rem 0 0;">
            We hebben een 6-cijferige code gemaild. Geldig 10 minuten.
        </p>
    </header>

    @if (session('status'))
        <div class="alert alert-success" role="status" style="margin-bottom:.75rem;">
            {{ session('status') }}
        </div>
    @endif

    @error('code')
    <div class="alert alert-danger" role="alert" style="margin-bottom:.75rem;">
        {{ $message }}
    </div>
    @enderror

    {{-- resources/views/auth/2fa.blade.php --}}
    <form class="fa-form" method="POST" action="{{ route('2fa.verify') }}" id="twofa-form" autocomplete="one-time-code">
        @csrf
        <input type="hidden" name="code" id="code" value="">

        <div style="display:flex; gap:.5rem; justify-content:center;">
            <input class="numbers-2fa" name="d1" maxlength="1" inputmode="numeric" pattern="[0-9]*" autofocus>
            <input class="numbers-2fa" name="d2" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input class="numbers-2fa" name="d3" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input class="numbers-2fa" name="d4" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input class="numbers-2fa" name="d5" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input class="numbers-2fa" name="d6" maxlength="1" inputmode="numeric" pattern="[0-9]*">
        </div>

        <div style="display:flex; gap:.75rem; justify-content:center; margin-top:-23px; flex-wrap:wrap;">
            <button class="stuur-button" type="submit">Verifiëren</button>
        </div>
    </form>

    {{-- IMPORTANT: this is a SEPARATE form, not nested --}}
    <form method="POST" action="{{ route('2fa.resend') }}" style="margin-top:12px; display:flex; justify-content:center;">
        @csrf
        <button class="btn btn--ghost" type="submit">Code opnieuw sturen</button>
    </form>

    <footer class="card-2fa-footer" style="margin-top:1rem; text-align:center;">
        <span class="muted">Geen code ontvangen? Check je spam of stuur opnieuw.</span>
    </footer>
</main>

<script>
    (function () {
        const inputs = Array.from(document.querySelectorAll('.numbers-2fa'));
        const hidden = document.getElementById('code');
        const form = document.getElementById('twofa-form');

        // Only digits, auto-advance, backspace to previous
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
                if (e.target.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                    inputs[index + 1].select?.();
                }
                syncHidden();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    syncHidden();
                }
                if (e.key === 'ArrowLeft' && index > 0) {
                    inputs[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            // Paste full code (e.g. from email)
            input.addEventListener('paste', (e) => {
                const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
                if (!txt) return;
                e.preventDefault();
                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].value = txt[i] ?? '';
                }
                inputs[Math.min(txt.length, inputs.length) - 1]?.focus();
                syncHidden();
            });
        });

        function syncHidden() {
            hidden.value = inputs.map(i => i.value || '').join('').slice(0,6);
        }

        // Prevent nested form submit (resend)
        const resendForm = document.getElementById('resend-form');
        resendForm?.addEventListener('submit', (e) => {
            e.stopPropagation();
        });

        // Validate 6 digits before submit
        form.addEventListener('submit', (e) => {
            syncHidden();
            if (!/^\d{6}$/.test(hidden.value)) {
                e.preventDefault();
                // simple visual cue
                inputs.forEach((i) => i.classList.add('shake'));
                setTimeout(() => inputs.forEach(i => i.classList.remove('shake')), 300);
            }
        });

        // Autofocus first empty on load
        const firstEmpty = inputs.find(i => !i.value);
        (firstEmpty ?? inputs[0]).focus();
    })();
</script>
</body>
</html>
