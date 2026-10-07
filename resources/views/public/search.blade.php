@extends('layouts.public')

@section('meta')
    <x-seo title="Search" :canonical="route('search')" />
@endsection

@section('content')
    <div class="container magazine">
        <div class="page-block search-page">
            <h1 class="page-title">Search</h1>
            <form action="{{ route('search') }}" method="get">
                <input type="search" name="q" value="{{ $term }}" placeholder="Type and hit enter..." aria-label="Search">
                <button class="btn" type="submit">Search</button>
            </form>
            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
            @endif
            @if (mb_strlen($term) > 0 && mb_strlen($term) < 2)
                <p>Enter at least two characters.</p>
            @elseif ($articles)
                <p class="quiet">{{ $articles->total() }} result(s) for “{{ $term }}”.</p>
                <ul class="mag-list">
                    @forelse ($articles as $article)
                        <li><x-story :article="$article" variant="magazine" /></li>
                    @empty
                        <li><div class="empty">No published stories matched that search.</div></li>
                    @endforelse
                </ul>
                <div class="pager">{{ $articles->links() }}</div>
            @endif
        </div>
        <x-site-sidebar :except="collect($articles?->pluck('id'))" />
    </div>
@endsection
