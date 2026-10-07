@extends('layouts.admin')

@section('title', 'Dashboard')

@section('stage')
    <header class="stage-head">
        <div class="head-top">
            <div>
                <p class="eyebrow">Newsroom · The house desk</p>
                <h1 class="head-title">Dashboard</h1>
                <p class="head-copy">{{ auth()->user()->name }}, the desk is open. File a story, clear the review queue, or send a piece live.</p>
            </div>
            <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Desk open</div>
        </div>
        <div class="metric-rail">
            @foreach (['draft' => 'Drafts', 'pending_review' => 'Pending', 'scheduled' => 'Scheduled', 'published' => 'Published'] as $status => $label)
                <a class="metric" href="{{ route('admin.articles.index', $status) }}">
                    <span class="label">{{ $label }}</span>
                    <span class="value">{{ $counts[$status] ?? 0 }}</span>
                </a>
            @endforeach
            <a class="metric" href="{{ route('admin.messages.index') }}">
                <span class="label">Mail</span>
                <span class="value">{{ $unreadMessages }}</span>
            </a>
        </div>
    </header>
@endsection

@section('content')
    <div class="ribbon" aria-label="Recent stories">
        <div class="ribbon-label">Pulse</div>
        <div class="ticker">
            <p class="ticker-track">
                @if ($recent->isEmpty())
                    <span>The desk is clear. File the first story.</span>
                    <span>The desk is clear. File the first story.</span>
                @else
                    @foreach ($recent->concat($recent) as $article)
                        <span><b>{{ $article->title }}</b> · {{ $article->status->label() }}</span>
                    @endforeach
                @endif
            </p>
        </div>
    </div>

    <div class="split">
        <div class="split-stack">
            <section class="surface" data-stamp="Copy">
                <div class="panel-title">
                    <div>
                        <h2>On the desk</h2>
                        <p>Stories last touched in the newsroom.</p>
                    </div>
                    <a class="ghost-btn" href="{{ route('admin.articles.create') }}">New article</a>
                </div>
                <div class="tickets">
                    @forelse ($recent as $article)
                        <a class="ticket" href="{{ route('admin.articles.edit', $article) }}">
                            <span class="ticket-code">{{ strtoupper(substr($article->status->value, 0, 4)) }}</span>
                            <div>
                                <h3>{{ $article->title }}</h3>
                                <p>{{ $article->category?->name ?: 'Unfiled' }} · {{ $article->author?->name ?: 'Desk' }}</p>
                            </div>
                            <em>{{ $article->postedLabel() ?? $article->updated_at?->timezone('Africa/Lagos')->format('j M Y') }}</em>
                        </a>
                    @empty
                        <p class="quiet-note">No articles yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
        <section class="surface" data-stamp="House">
            <div class="panel-title">
                <div>
                    <h2>The house</h2>
                    <p>What the desk is holding right now.</p>
                </div>
            </div>
            <div class="status-row">
                <div class="status-item"><span>Published</span><strong>{{ $counts['published'] ?? 0 }}</strong></div>
                <div class="status-item"><span>In review</span><strong>{{ $counts['pending_review'] ?? 0 }}</strong></div>
                <div class="status-item"><span>Views today</span><strong>{{ $viewsToday }}</strong></div>
                <div class="status-item"><span>Unread mail</span><strong>{{ $unreadMessages }}</strong></div>
            </div>
            <div class="matrix">
                <a class="cell" href="{{ route('admin.articles.create') }}">
                    <div>
                        <div class="tag">Copy</div>
                        <h3>File a story</h3>
                        <p>Open a draft and send it through review.</p>
                    </div>
                    <div class="go">New article</div>
                </a>
                <a class="cell" href="{{ route('admin.articles.index', 'pending_review') }}">
                    <div>
                        <div class="tag">Review</div>
                        <h3>Clear the queue</h3>
                        <p>{{ $counts['pending_review'] ?? 0 }} waiting for an editor.</p>
                    </div>
                    <div class="go">Open review</div>
                </a>
                <a class="cell" href="{{ route('admin.breaking.index') }}">
                    <div>
                        <div class="tag">Wire</div>
                        <h3>Breaking line</h3>
                        <p>Put a headline on the live wire.</p>
                    </div>
                    <div class="go">Open wire</div>
                </a>
                <a class="cell" href="{{ route('admin.messages.index') }}">
                    <div>
                        <div class="tag">Mail</div>
                        <h3>Reader letters</h3>
                        <p>{{ $unreadMessages }} unread on the desk.</p>
                    </div>
                    <div class="go">Open mail</div>
                </a>
            </div>
        </section>
    </div>
@endsection
