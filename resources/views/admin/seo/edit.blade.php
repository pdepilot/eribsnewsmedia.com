@extends('layouts.admin')

@section('title', 'SEO')

@php
    $pageCount = collect(['page_about', 'page_privacy', 'page_terms'])->filter(fn (string $key) => filled($settings[$key] ?? null))->count();
    $analyticsOn = ($settings['analytics_enabled'] ?? '0') === '1';
@endphp

@section('stage')
    <header class="stage-head">
        <div class="head-top">
            <div>
                <p class="eyebrow">Newsroom · The public face</p>
                <h1 class="head-title">SEO</h1>
                <p class="head-copy">The title a search result uses, the publisher mark, and the pages linked from the menu.</p>
            </div>
            <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Desk open</div>
        </div>
        <div class="metric-rail pulse-metrics">
            <div class="metric">
                <span class="label">Suffix</span>
                <span class="value is-text">{{ filled($settings['seo_title_suffix'] ?? null) ? $settings['seo_title_suffix'] : 'None' }}</span>
            </div>
            <div class="metric">
                <span class="label">Publisher</span>
                <span class="value is-text">{{ $settings['publisher_name'] ?? 'ERIBS' }}</span>
            </div>
            <div class="metric">
                <span class="label">Pages</span>
                <span class="value">{{ $pageCount }}</span>
            </div>
            <div class="metric">
                <span class="label">Analytics</span>
                <span class="value is-text">{{ $analyticsOn ? 'On' : 'Off' }}</span>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="seo-board">
        @csrf @method('PUT')
        <input type="hidden" name="seo_form" value="1">

        <section class="setup-sheet" data-stamp="Search">
            <div class="panel-title">
                <div>
                    <h2>Search result</h2>
                    <p>Used when a story does not set its own title or description.</p>
                </div>
            </div>
            <div class="setup-fields">
                <label class="setup-field">Title suffix <input name="seo_title_suffix" value="{{ old('seo_title_suffix', $settings['seo_title_suffix'] ?? '') }}" placeholder="News"></label>
                <label class="setup-field">X handle <input name="twitter_handle" value="{{ old('twitter_handle', $settings['twitter_handle'] ?? '') }}" placeholder="@eribs"></label>
                <label class="setup-field setup-span">Default meta description <textarea name="seo_default_description" rows="4" maxlength="320">{{ old('seo_default_description', $settings['seo_default_description'] ?? '') }}</textarea></label>
            </div>
        </section>

        <section class="setup-sheet seo-mark" data-stamp="Mark">
            <div class="panel-title">
                <div>
                    <h2>Publisher</h2>
                    <p>The name and logo search engines attach to each story.</p>
                </div>
            </div>
            <label class="setup-field">Publisher name <input name="publisher_name" value="{{ old('publisher_name', $settings['publisher_name'] ?? '') }}"></label>
            <div class="article-upload">
                <div>
                    <p class="article-upload-label">Publisher logo</p>
                    <p class="article-upload-hint">JPEG, PNG, GIF, or WebP. This mark is used in the story markup, not typed in as a link.</p>
                    <label class="article-upload-btn">
                        Choose image
                        <input id="publisher-logo" type="file" name="publisher_logo" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                </div>
                <div class="article-upload-preview">
                    <img id="publisher-logo-preview" src="{{ $logoUrl }}" alt="{{ $settings['publisher_name'] ?? 'Publisher logo' }}" @unless($logoUrl) hidden @endunless>
                    @unless ($logoUrl)
                        <span id="publisher-logo-empty">No logo yet</span>
                    @endunless
                </div>
            </div>
            @if ($logoUrl)
                <label class="setup-check"><input type="checkbox" name="remove_publisher_logo" value="1"> Remove the current logo</label>
            @endif
        </section>

        <section class="setup-sheet seo-span" data-stamp="Pages">
            <div class="panel-title">
                <div>
                    <h2>Menu pages</h2>
                    <p>The about page stays blank until the copy is ready. Privacy and terms are already published. A note in either box is added at the end of that page.</p>
                </div>
            </div>
            <div class="seo-pages">
                <label class="setup-field">About page <textarea name="page_about" rows="8">{{ old('page_about', $settings['page_about'] ?? '') }}</textarea></label>
                <label class="setup-field">Privacy note <textarea name="page_privacy" rows="8">{{ old('page_privacy', $settings['page_privacy'] ?? '') }}</textarea></label>
                <label class="setup-field">Terms note <textarea name="page_terms" rows="8">{{ old('page_terms', $settings['page_terms'] ?? '') }}</textarea></label>
            </div>
        </section>

        <section class="setup-sheet seo-span" data-stamp="Tag">
            <div class="panel-title">
                <div>
                    <h2>Analytics</h2>
                    <p>The saved homepage used Google tag GT-5M8TBNT. Leave this off until the property belongs to ERIBS.</p>
                </div>
                <button class="setup-save">Save SEO</button>
            </div>
            <div class="setup-fields">
                <label class="setup-field">Analytics ID <input name="analytics_id" value="{{ old('analytics_id', $settings['analytics_id'] ?? '') }}"></label>
                <input type="hidden" name="analytics_enabled" value="0">
                <label class="setup-check"><input type="checkbox" name="analytics_enabled" value="1" @checked(old('analytics_enabled', $settings['analytics_enabled'] ?? '0') === '1')> Enable analytics</label>
            </div>
        </section>
    </form>
    <script>
        document.getElementById('publisher-logo')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            const preview = document.getElementById('publisher-logo-preview');
            const empty = document.getElementById('publisher-logo-empty');
            if (!file || !preview) {
                return;
            }
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            if (empty) {
                empty.hidden = true;
            }
        });
    </script>
@endsection
