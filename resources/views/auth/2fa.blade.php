<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Inloggen — GeoProfs</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
    {{-- CSS vanuit /public/css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="page page--auth">
<aside class="visual">
    <div class="visual__overlay"></div>
    <div class="visual__brand">
        {{-- Vervang door jouw logo --}}
        <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" class="logo">
            <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 6l6 6" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        <span>GeoProfs</span>
    </div>
</aside>

<main class="card-2fa">

    {{-- Puur design (geen actie) --}}
    <div>
        <h1>We hebben een code gesuutrd naar je mail vul het in aub</h1>
    </div>

    <form class="fa-form">
        <input class="numbers-2fa" type="text">
        <input class="numbers-2fa" type="text">
        <input class="numbers-2fa" type="text">
        <input class="numbers-2fa" type="text">
        <input class="numbers-2fa" type="text">
        <input class="numbers-2fa" type="text">
    </form>

    <button class="stuur-button"> Stuur</button>


    <footer class="card-2fa-footer">
        <a  class="link" href="#">Geen Code ontvangen?</a>
    </footer>

</main>
</body>
</html>

<script>
    document.querySelectorAll('.numbers-2fa').forEach((input, index, all) => {
        input.setAttribute('maxlength', 1);
        input.setAttribute('inputmode', 'numeric');
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, ''); // only numbers
            if (e.target.value && index < all.length - 1) all[index + 1].focus(); // auto jump to next
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                all[index - 1].focus();
            }
        });
    });
</script>
