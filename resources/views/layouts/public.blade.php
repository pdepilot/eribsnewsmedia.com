<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($networkHead = app(\App\Services\Advertising\AdvertisingService::class)->networkHeadMarkup())
        {!! $networkHead !!}
    @endif
    @if ($adsenseLoader = app(\App\Services\Advertising\AdvertisingService::class)->adsenseLoader())
        {!! $adsenseLoader !!}
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,640&family=PT+Serif:ital,wght@0,400;0,700;1,400&family=Raleway:wght@500;600;700&family=Rubik:wght@500;700&display=swap" rel="stylesheet">
    @yield('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="boxed">
<div class="site" x-data="{ navOpen: false, searchOpen: false }">
    <x-site-header />
    @if (session('status'))
        <p class="flash">{{ session('status') }}</p>
    @endif
    <main id="top">
        @yield('content')
    </main>
    <x-ad-slot name="mobile-anchor" />
    <x-ad placement="footer" />
    <x-site-footer />
    <a class="to-top" href="#top" aria-label="Back to top">↑</a>
</div>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }
</script>
@if (app(\App\Services\Advertising\AdvertisingService::class)->wantsImpressionBeacon())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            document.querySelectorAll('[data-ad-seen]').forEach(function (el) {
                if (el.getClientRects().length === 0) return;
                fetch(el.getAttribute('data-ad-seen'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    keepalive: true
                });
            });
        });
    </script>
@endif
</body>
</html>
