<!DOCTYPE html>
<html lang="en-NG">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <title>@yield('title', 'Dashboard') — ERIBS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/portal.css', 'resources/js/app.js'])
</head>
<body class="portal">
@php
    $status = request()->route('status');
    $rail = [
        ['Desk', 'admin.dashboard'],
        ['Copy', 'admin.articles.index'],
        ['Drafts', 'admin.articles.index', 'draft'],
        ['Review', 'admin.articles.index', 'pending_review'],
        ['Timed', 'admin.articles.index', 'scheduled'],
        ['Live', 'admin.articles.index', 'published'],
        ['Sections', 'admin.categories.index'],
        ['Tags', 'admin.tags.index'],
        ['Bylines', 'admin.authors.index'],
        ['Media', 'admin.media.index'],
        ['Wire', 'admin.breaking.index'],
        ['Advertising', 'admin.advertising.index'],
        ['Mail', 'admin.messages.index'],
        ['Talk', 'admin.comments.index'],
        ['People', 'admin.users.index'],
        ['Roles', 'admin.roles.index'],
        ['SEO', 'admin.seo.edit'],
        ['Setup', 'admin.settings.edit'],
        ['Pulse', 'admin.analytics.index'],
        ['Site', 'home'],
    ];
@endphp
<div class="vault">
    <aside class="rail" aria-label="Newsroom navigation">
        <input class="rail-toggle-input" id="newsroom-toggle" type="checkbox" aria-controls="newsroom-menu" aria-expanded="false">
        <div class="rail-top">
            <a class="rail-logo" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/favicon.png') }}" alt="ERIBS Media" width="52" height="52"></a>
            <label class="rail-toggle" for="newsroom-toggle">
                <span class="rail-toggle-open">Menu</span>
                <span class="rail-toggle-shut">Close</span>
            </label>
        </div>
        <nav class="rail-nav" id="newsroom-menu">
            @foreach ($rail as $item)
                @php
                    $itemStatus = $item[2] ?? null;
                    $active = $item[1] === 'admin.articles.index'
                        ? request()->routeIs('admin.articles.index') && $status === $itemStatus
                        : request()->routeIs($item[1]);
                @endphp
                <a class="rail-btn {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif href="{{ isset($item[2]) ? route($item[1], $item[2]) : route($item[1]) }}"><span>{{ $item[0] }}</span></a>
            @endforeach
        </nav>
        <form class="rail-exit-form" method="post" action="{{ route('logout') }}">
            @csrf
            <button class="rail-exit" type="submit" title="Log out">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"></path>
                    <path d="M15 12H3"></path>
                    <path d="M6 9l-3 3 3 3"></path>
                </svg>
                <strong>Log out</strong>
            </button>
        </form>
    </aside>
    <div class="stage">
        @hasSection('stage')
            @yield('stage')
        @else
            <header class="stage-head">
                <div class="head-top">
                    <div>
                        <p class="eyebrow">Newsroom · {{ auth()->user()->roleLabel() }}</p>
                        <h1 class="head-title">@yield('title', 'Dashboard')</h1>
                        <p class="head-copy">{{ auth()->user()->name }} is signed in to the desk.</p>
                    </div>
                    <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Desk open</div>
                </div>
            </header>
        @endif
        <main class="workspace">
            @if (session('status'))
                <p class="desk-flash">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="desk-flash is-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script>
    function eribsClock() {
        const text = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Africa/Lagos',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hourCycle: 'h23',
        }).format(new Date());
        document.querySelectorAll('[data-clock]').forEach((node) => { node.textContent = text; });
    }
    eribsClock();
    setInterval(eribsClock, 1000);

    const newsroomToggle = document.getElementById('newsroom-toggle');
    if (newsroomToggle) {
        const closeNewsroomMenu = () => {
            newsroomToggle.checked = false;
            newsroomToggle.setAttribute('aria-expanded', 'false');
        };
        newsroomToggle.addEventListener('change', () => {
            newsroomToggle.setAttribute('aria-expanded', newsroomToggle.checked ? 'true' : 'false');
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeNewsroomMenu();
        });
        document.addEventListener('click', (event) => {
            if (!newsroomToggle.checked || event.target.closest('.rail')) return;
            closeNewsroomMenu();
        });
        document.querySelectorAll('#newsroom-menu a').forEach((link) => {
            link.addEventListener('click', closeNewsroomMenu);
        });
    }
</script>
</body>
</html>
