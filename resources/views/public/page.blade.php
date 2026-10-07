@extends('layouts.public')

@section('meta')
    <x-seo :title="$title" :canonical="$canonical" />
@endsection

@section('content')
    <div class="container magazine">
        <div class="page-block">
            <h1 class="page-title">{{ $title }}</h1>
            @if (filled($body))
                <div class="article-body">{!! nl2br(e($body)) !!}</div>
            @else
                <div class="empty">This page is on the menu. Its text has not been added in the newsroom settings yet.</div>
            @endif
        </div>
        <x-site-sidebar />
    </div>
@endsection
