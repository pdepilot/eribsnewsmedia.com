@extends('layouts.public')

@section('meta')
    <x-seo title="About Us" :canonical="route('pages.about')" />
@endsection

@section('content')
    <div class="container about-desk">
        <header class="about-intro">
            <p class="kicker">Lagos newsroom</p>
            <h1 class="about-title">About the desk</h1>
            <p>{{ $siteName }} publishes from Lagos.</p>
        </header>

        <section class="about-spread">
            <aside class="about-mark">
                <p class="about-word" aria-hidden="true">ERIBS</p>
                <img class="about-logo" src="{{ asset('images/logo.png').'?v=2' }}" alt="{{ $siteName }}" width="652" height="263">
                <p class="about-place">Lagos</p>
                <p class="about-when"><time datetime="{{ now()->toDateString() }}">{{ now()->timezone('Africa/Lagos')->format('l, j F Y') }}</time></p>
                @if (filled($tagline))
                    <p class="about-tag">{{ $tagline }}</p>
                @endif
                @if (filled($deskEmail))
                    <a class="about-mail" href="mailto:{{ $deskEmail }}">{{ $deskEmail }}</a>
                @endif
            </aside>

            <article class="about-letter">
                <p class="about-kicker">From the newsroom</p>
                @if (filled($body))
                    <div class="about-copy">{{ $body }}</div>
                @else
                    <p class="about-stand">The longer note for this page is still with the newsroom.</p>
                    <p class="about-quiet">When it is filed in Setup, it appears here in place of this line.</p>
                @endif
            </article>
        </section>

        @if ($desks->isNotEmpty())
            <section class="about-desks">
                <p class="kicker">Open desks</p>
                <ol>
                    @foreach ($desks as $desk)
                        <li>
                            <a href="{{ route('categories.show', $desk) }}">
                                <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <strong>{{ $desk->name }}</strong>
                                @if (filled($desk->description))
                                    <em>{{ $desk->description }}</em>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <nav class="about-ways" aria-label="Elsewhere on the site">
            <a href="{{ route('pages.contact') }}">
                <span>01</span>
                <strong>Write to the desk</strong>
            </a>
            <a href="{{ route('home') }}">
                <span>02</span>
                <strong>The front page</strong>
            </a>
            <a href="{{ route('feeds.rss') }}">
                <span>03</span>
                <strong>The wire</strong>
            </a>
        </nav>
    </div>
@endsection
