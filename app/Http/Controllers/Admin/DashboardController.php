<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\ContactMessage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = Article::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recent = Article::query()->with(['author', 'category'])->latest('updated_at')->limit(8)->get();

        return view('admin.dashboard', [
            'counts' => $counts,
            'recent' => $recent,
            'viewsToday' => ArticleView::query()->where('created_at', '>=', now()->startOfDay())->count(),
            'unreadMessages' => ContactMessage::query()->whereNull('read_at')->count(),
        ]);
    }
}
