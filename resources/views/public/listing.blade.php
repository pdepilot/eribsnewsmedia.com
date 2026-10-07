@extends('layouts.public')

@section('meta')
    <x-seo :title="$heading" :description="$intro" :canonical="url()->current()" />
@endsection

@section('content')
    <div class="container magazine">
        <div class="page-block">
            <h1 class="mag-title"><span>{{ $heading }}</span></h1>
            @if (filled($intro))
                <p>{{ $intro }}</p>
            @endif
            @if ($articles->isEmpty())
                <div class="empty">No published stories in this list yet.</div>
            @else
                <ul class="mag-list">
                    @foreach ($articles as $article)
                        <li><x-story :article="$article" variant="magazine" /></li>
                    @endforeach
                </ul>
                <div class="pager">{{ $articles->links() }}</div>
            @endif
        </div>
        <x-site-sidebar :except="$articles->pluck('id')" />
    </div>
@endsection
