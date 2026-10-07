<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);

        return view('admin.analytics.index', [
            'pulse' => $this->pulse(),
        ]);
    }

    public function live(): JsonResponse
    {
        abort_unless(auth()->user()->hasPermission('analytics.view'), 403);

        return response()->json($this->pulse())->header('Cache-Control', 'no-store');
    }

    private function pulse(): array
    {
        $start = now()->startOfDay()->subDays(13);
        $today = now()->startOfDay();
        $hour = now()->subHour();

        $reads = ArticleView::query()
            ->with(['article.category'])
            ->where('created_at', '>=', $start)
            ->latest('created_at')
            ->get();

        $byDay = $reads->groupBy(fn (ArticleView $view) => $view->created_at->toDateString());
        $totals = collect(range(13, 0))->map(function (int $ago) use ($byDay) {
            $date = now()->startOfDay()->subDays($ago);

            return [
                'label' => $date->format('j M'),
                'total' => $byDay->get($date->toDateString(), collect())->count(),
            ];
        });
        $peak = max(1, (int) $totals->max('total'));
        $days = $totals->map(fn (array $day) => $day + [
            'height' => (int) round($day['total'] / $peak * 100),
        ])->values();

        $top = Article::query()
            ->with('category')
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();
        $topPeak = max(1, (int) $top->max('views_count'));

        return [
            'views_today' => $reads->filter(fn (ArticleView $view) => $view->created_at->gte($today))->count(),
            'views_hour' => $reads->filter(fn (ArticleView $view) => $view->created_at->gte($hour))->count(),
            'views_fortnight' => $reads->count(),
            'views_total' => (int) ArticleView::query()->count(),
            'updated_at' => now()->timezone('Africa/Lagos')->format('H:i:s'),
            'days' => $days,
            'top' => $top->map(fn (Article $article) => [
                'title' => $article->title,
                'category' => $article->category?->name ?: 'Unfiled',
                'views' => (int) $article->views_count,
                'share' => (int) round($article->views_count / $topPeak * 100),
                'url' => $article->isPublic() ? route('articles.show', $article) : route('admin.articles.edit', $article),
            ])->values(),
            'latest' => $reads->take(8)->map(fn (ArticleView $view) => [
                'title' => $view->article?->title ?: 'Removed story',
                'category' => $view->article?->category?->name ?: 'Desk',
                'when' => $view->created_at->timezone('Africa/Lagos')->format('H:i'),
                'ago' => $view->created_at->diffForHumans(),
            ])->values(),
        ];
    }
}
