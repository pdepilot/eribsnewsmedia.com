@extends('layouts.admin')

@section('title', 'Pulse')

@section('stage')
    <header class="stage-head">
        <div class="head-top">
            <div>
                <p class="eyebrow">Newsroom · The reading room</p>
                <h1 class="head-title">Pulse</h1>
                <p class="head-copy">Reads from the live site, counted in Lagos time. This desk refreshes itself as new readers arrive.</p>
            </div>
            <div class="live-pill"><i class="live-dot"></i> <span data-clock>00:00:00</span> · Live</div>
        </div>
        <div class="metric-rail pulse-metrics">
            <div class="metric">
                <span class="label">Today</span>
                <span class="value" data-pulse="views_today">{{ $pulse['views_today'] }}</span>
            </div>
            <div class="metric">
                <span class="label">This hour</span>
                <span class="value" data-pulse="views_hour">{{ $pulse['views_hour'] }}</span>
            </div>
            <div class="metric">
                <span class="label">14 days</span>
                <span class="value" data-pulse="views_fortnight">{{ $pulse['views_fortnight'] }}</span>
            </div>
            <div class="metric">
                <span class="label">All reads</span>
                <span class="value" data-pulse="views_total">{{ $pulse['views_total'] }}</span>
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="pulse-board" data-live="{{ route('admin.analytics.live') }}">
        <section class="surface" data-stamp="14 days">
            <div class="panel-title">
                <div>
                    <h2>The fortnight</h2>
                    <p>Each bar is a Lagos day. Updated <span data-pulse="updated_at">{{ $pulse['updated_at'] }}</span> WAT</p>
                </div>
            </div>
            <div class="pulse-chart" data-pulse-chart>
                @foreach ($pulse['days'] as $day)
                    <div class="pulse-col">
                        <span class="pulse-plot"><span class="pulse-bar" style="height: {{ max($day['height'], $day['total'] > 0 ? 8 : 2) }}%"></span></span>
                        <strong>{{ $day['total'] }}</strong>
                        <em>{{ $day['label'] }}</em>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="surface" data-stamp="Now">
            <div class="panel-title">
                <div>
                    <h2>Just read</h2>
                    <p>The latest opens on the public site.</p>
                </div>
            </div>
            <div class="pulse-feed" data-pulse-latest>
                @forelse ($pulse['latest'] as $read)
                    <article class="pulse-read">
                        <time>{{ $read['when'] }}</time>
                        <div>
                            <h3>{{ $read['title'] }}</h3>
                            <p>{{ $read['category'] }} · {{ $read['ago'] }}</p>
                        </div>
                    </article>
                @empty
                    <p class="quiet-note">No reads yet. Open a story on the site and it will show up here.</p>
                @endforelse
            </div>
        </section>

        <section class="surface pulse-span" data-stamp="Most read">
            <div class="panel-title">
                <div>
                    <h2>Most read</h2>
                    <p>Stories ranked by the reads stored on each article.</p>
                </div>
            </div>
            <div class="pulse-rank" data-pulse-top>
                @forelse ($pulse['top'] as $article)
                    <a class="pulse-row" href="{{ $article['url'] }}">
                        <span class="pulse-track"><i style="width: {{ max($article['share'], $article['views'] > 0 ? 8 : 0) }}%"></i></span>
                        <span>
                            <strong>{{ $article['title'] }}</strong>
                            <em>{{ $article['category'] }}</em>
                        </span>
                        <b>{{ $article['views'] }}</b>
                    </a>
                @empty
                    <p class="quiet-note">No stories on the desk yet.</p>
                @endforelse
            </div>
        </section>
    </div>
    <script>
        const board = document.querySelector('.pulse-board');
        const liveUrl = board?.dataset.live;
        const setText = (key, value) => {
            document.querySelectorAll(`[data-pulse="${key}"]`).forEach((node) => {
                if (node.textContent !== String(value)) {
                    node.textContent = value;
                    node.classList.add('is-hot');
                    setTimeout(() => node.classList.remove('is-hot'), 700);
                }
            });
        };
        const paint = (pulse) => {
            ['views_today', 'views_hour', 'views_fortnight', 'views_total', 'updated_at'].forEach((key) => setText(key, pulse[key]));

            const chart = document.querySelector('[data-pulse-chart]');
            chart.replaceChildren(...pulse.days.map((day) => {
                const column = document.createElement('div');
                column.className = 'pulse-col';
                const plot = document.createElement('span');
                plot.className = 'pulse-plot';
                const bar = document.createElement('span');
                bar.className = 'pulse-bar';
                bar.style.height = `${Math.max(day.height, day.total > 0 ? 8 : 2)}%`;
                plot.append(bar);
                const count = document.createElement('strong');
                count.textContent = day.total;
                const label = document.createElement('em');
                label.textContent = day.label;
                column.append(plot, count, label);
                return column;
            }));

            const latest = document.querySelector('[data-pulse-latest]');
            if (pulse.latest.length === 0) {
                const note = document.createElement('p');
                note.className = 'quiet-note';
                note.textContent = 'No reads yet. Open a story on the site and it will show up here.';
                latest.replaceChildren(note);
            } else {
                latest.replaceChildren(...pulse.latest.map((read) => {
                    const item = document.createElement('article');
                    item.className = 'pulse-read';
                    const time = document.createElement('time');
                    time.textContent = read.when;
                    const copy = document.createElement('div');
                    const title = document.createElement('h3');
                    title.textContent = read.title;
                    const meta = document.createElement('p');
                    meta.textContent = `${read.category} · ${read.ago}`;
                    copy.append(title, meta);
                    item.append(time, copy);
                    return item;
                }));
            }

            const rank = document.querySelector('[data-pulse-top]');
            if (pulse.top.length === 0) {
                const note = document.createElement('p');
                note.className = 'quiet-note';
                note.textContent = 'No stories on the desk yet.';
                rank.replaceChildren(note);
            } else {
                rank.replaceChildren(...pulse.top.map((article) => {
                    const row = document.createElement('a');
                    row.className = 'pulse-row';
                    row.href = article.url;
                    const track = document.createElement('span');
                    track.className = 'pulse-track';
                    const fill = document.createElement('i');
                    fill.style.width = `${Math.max(article.share, article.views > 0 ? 8 : 0)}%`;
                    track.append(fill);
                    const copy = document.createElement('span');
                    const title = document.createElement('strong');
                    title.textContent = article.title;
                    const category = document.createElement('em');
                    category.textContent = article.category;
                    copy.append(title, category);
                    const count = document.createElement('b');
                    count.textContent = article.views;
                    row.append(track, copy, count);
                    return row;
                }));
            }
        };
        const refresh = async () => {
            if (!liveUrl || document.hidden) {
                return;
            }
            try {
                const response = await fetch(liveUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!response.ok) {
                    return;
                }
                paint(await response.json());
            } catch (error) {
                return;
            }
        };
        setInterval(refresh, 5000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                refresh();
            }
        });
    </script>
@endsection
