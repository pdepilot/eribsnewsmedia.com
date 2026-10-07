@extends('layouts.admin')

@section('title', 'Mail')

@section('stage')
    <header class="stage-head">
        <div class="head-top">
            <div>
                <p class="eyebrow">Newsroom · The letter tray</p>
                <h1 class="head-title">Mail</h1>
                <p class="head-copy">Letters sent from the public contact page. Opening this tray marks them read.</p>
            </div>
            <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Desk open</div>
        </div>
        <div class="metric-rail pulse-metrics">
            <div class="metric">
                <span class="label">Letters</span>
                <span class="value">{{ $total }}</span>
            </div>
            <div class="metric">
                <span class="label">Today</span>
                <span class="value">{{ $today }}</span>
            </div>
            <div class="metric">
                <span class="label">Opened</span>
                <span class="value">{{ $opened->count() }}</span>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="mail-board">
        @forelse ($messages as $message)
            <article class="mail-letter {{ $opened->contains($message->id) ? 'is-fresh' : '' }}">
                <header class="mail-top">
                    <h2>{{ $message->subject }}</h2>
                    <time datetime="{{ $message->created_at?->toAtomString() }}">{{ $message->created_at?->timezone('Africa/Lagos')->format('j M Y, g:i A') }}</time>
                </header>
                <p class="mail-from">
                    <strong>{{ $message->name }}</strong>
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </p>
                <p class="mail-body">{{ $message->message }}</p>
                <form method="post" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this letter?')">
                    @csrf
                    @method('DELETE')
                    <button class="row-delete" type="submit">Delete</button>
                </form>
            </article>
        @empty
            <p class="mail-empty">The tray is empty. Letters from the contact page will land here.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
