<?php

use App\Http\Controllers\Admin\AdInventoryController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\AdvertisingController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BreakingNewsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'newsroom'])->prefix('portal')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    Route::get('articles/{status?}', [ArticleController::class, 'index'])
        ->where('status', 'draft|pending_review|approved|scheduled|published|rejected|archived')
        ->name('articles.index');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('tags', TagController::class)->except(['show']);
    Route::resource('authors', AuthorController::class)->except(['show']);

    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::resource('breaking', BreakingNewsController::class)->except(['show'])->parameters(['breaking' => 'breakingNews']);
    Route::resource('ads', AdvertisementController::class)->except(['show'])->parameters(['ads' => 'advertisement']);

    Route::prefix('advertising')->name('advertising.')->group(function () {
        Route::get('/', [AdvertisingController::class, 'index'])->name('index');
        Route::get('settings', [AdvertisingController::class, 'settings'])->name('settings');
        Route::put('settings', [AdvertisingController::class, 'updateSettings'])->name('settings.update');
        Route::get('ads-txt', [AdvertisingController::class, 'adsTxt'])->name('ads-txt');
        Route::put('ads-txt', [AdvertisingController::class, 'updateAdsTxt'])->name('ads-txt.update');

        Route::get('networks', [AdInventoryController::class, 'networks'])->name('networks.index');
        Route::get('networks/create', [AdInventoryController::class, 'createNetwork'])->name('networks.create');
        Route::post('networks', [AdInventoryController::class, 'storeNetwork'])->name('networks.store');
        Route::get('networks/{network}/edit', [AdInventoryController::class, 'editNetwork'])->name('networks.edit');
        Route::put('networks/{network}', [AdInventoryController::class, 'updateNetwork'])->name('networks.update');
        Route::delete('networks/{network}', [AdInventoryController::class, 'destroyNetwork'])->name('networks.destroy');

        Route::get('placements', [AdInventoryController::class, 'placements'])->name('placements.index');
        Route::get('placements/create', [AdInventoryController::class, 'createPlacement'])->name('placements.create');
        Route::post('placements', [AdInventoryController::class, 'storePlacement'])->name('placements.store');
        Route::get('placements/{placement}/edit', [AdInventoryController::class, 'editPlacement'])->name('placements.edit');
        Route::put('placements/{placement}', [AdInventoryController::class, 'updatePlacement'])->name('placements.update');
        Route::delete('placements/{placement}', [AdInventoryController::class, 'destroyPlacement'])->name('placements.destroy');

        Route::get('units', [AdInventoryController::class, 'units'])->name('units.index');
        Route::get('units/create', [AdInventoryController::class, 'createUnit'])->name('units.create');
        Route::post('units', [AdInventoryController::class, 'storeUnit'])->name('units.store');
        Route::get('units/{unit}/edit', [AdInventoryController::class, 'editUnit'])->name('units.edit');
        Route::put('units/{unit}', [AdInventoryController::class, 'updateUnit'])->name('units.update');
        Route::delete('units/{unit}', [AdInventoryController::class, 'destroyUnit'])->name('units.destroy');

        Route::get('advertisers', [AdInventoryController::class, 'advertisers'])->name('advertisers.index');
        Route::get('advertisers/create', [AdInventoryController::class, 'createAdvertiser'])->name('advertisers.create');
        Route::post('advertisers', [AdInventoryController::class, 'storeAdvertiser'])->name('advertisers.store');
        Route::get('advertisers/{advertiser}/edit', [AdInventoryController::class, 'editAdvertiser'])->name('advertisers.edit');
        Route::put('advertisers/{advertiser}', [AdInventoryController::class, 'updateAdvertiser'])->name('advertisers.update');
        Route::delete('advertisers/{advertiser}', [AdInventoryController::class, 'destroyAdvertiser'])->name('advertisers.destroy');

        Route::get('campaigns', [AdInventoryController::class, 'campaigns'])->name('campaigns.index');
        Route::get('campaigns/create', [AdInventoryController::class, 'createCampaign'])->name('campaigns.create');
        Route::post('campaigns', [AdInventoryController::class, 'storeCampaign'])->name('campaigns.store');
        Route::get('campaigns/{campaign}/edit', [AdInventoryController::class, 'editCampaign'])->name('campaigns.edit');
        Route::put('campaigns/{campaign}', [AdInventoryController::class, 'updateCampaign'])->name('campaigns.update');
        Route::delete('campaigns/{campaign}', [AdInventoryController::class, 'destroyCampaign'])->name('campaigns.destroy');

        Route::get('creatives', [AdInventoryController::class, 'creatives'])->name('creatives.index');
        Route::get('creatives/create', [AdInventoryController::class, 'createCreative'])->name('creatives.create');
        Route::post('creatives', [AdInventoryController::class, 'storeCreative'])->name('creatives.store');
        Route::get('creatives/{creative}/edit', [AdInventoryController::class, 'editCreative'])->name('creatives.edit');
        Route::put('creatives/{creative}', [AdInventoryController::class, 'updateCreative'])->name('creatives.update');
        Route::delete('creatives/{creative}', [AdInventoryController::class, 'destroyCreative'])->name('creatives.destroy');
    });

    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/roles', [UserController::class, 'assignRoles'])->name('users.roles');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::get('seo', [SettingController::class, 'seo'])->name('seo.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::put('settings/account', [SettingController::class, 'updateAccount'])->name('settings.account');

    Route::get('analytics/live', [AnalyticsController::class, 'live'])->name('analytics.live');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});
