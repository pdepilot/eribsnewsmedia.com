<?php

use App\Http\Controllers\AdsTxtController;
use App\Http\Controllers\AdTrafficController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/latest-news', [ArticleController::class, 'latest'])->name('articles.latest');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/article/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('/article/{article:slug}/comments', [CommentController::class, 'store'])->middleware('throttle:comments')->name('comments.store');
Route::get('/author/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:search')->name('search');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-of-use', [PageController::class, 'terms'])->name('pages.terms');

Route::get('/ads.txt', [AdsTxtController::class, 'show'])->name('ads.txt');
Route::post('/ad/{campaign}/impression', [AdTrafficController::class, 'impression'])->middleware('throttle:advertising')->name('advertising.impression');
Route::get('/ad/{campaign}/click', [AdTrafficController::class, 'click'])->middleware('throttle:advertising')->name('advertising.click');

Route::get('/robots.txt', function () {
    return response(implode("\n", [
        'User-agent: *',
        'Disallow: /portal',
        'Allow: /',
        '',
        'Sitemap: '.route('feeds.sitemap'),
        'Sitemap: '.route('feeds.news-sitemap'),
    ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

Route::get('/sitemap.xml', [FeedController::class, 'sitemap'])->name('feeds.sitemap');
Route::get('/news-sitemap.xml', [FeedController::class, 'newsSitemap'])->name('feeds.news-sitemap');
Route::get('/rss.xml', [FeedController::class, 'rss'])->name('feeds.rss');

Route::middleware('guest')->group(function () {
    Route::get('/portal/login', [LoginController::class, 'create'])->name('login');
    Route::post('/portal/login', [LoginController::class, 'store']);
});

Route::post('/portal/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::redirect('/admin', '/portal');
Route::get('/admin/{path}', function (string $path) {
    return redirect('/portal/'.$path);
})->where('path', '.*');

require __DIR__.'/admin.php';
