<?php

use App\Http\Controllers\Admin\ChannelAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiagnosticsAdminController;
use App\Http\Controllers\Admin\IngestAdminController;
use App\Http\Controllers\Admin\RightsAdminController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

// --- Public Discovery Experience ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
Route::get('/channel/{slug}', [ChannelController::class, 'show'])->name('channels.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');

// Audience Engine & Growth
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->name('newsletter.subscribe');
Route::get('/wa/join', [HomeController::class, 'whatsappRedirect'])->name('wa.join');
Route::post('/channel/{channel}/report', [ChannelController::class, 'reportProblem'])->name('channels.report');

// User / Favourites Interaction
Route::post('/channel/{channel}/favourite', [ChannelController::class, 'toggleFavourite'])->name('channels.favourite');
Route::get('/my-guide', [GuideController::class, 'personalized'])->name('guide.personalized');

// Zonita Intelligent Playout Engine API
Route::prefix('api/playback')->group(function () {
    Route::post('/report-failure', [PlaybackController::class, 'reportFailure'])->withoutMiddleware([ValidateCsrfToken::class]);
    Route::get('/epg/{slug}', [PlaybackController::class, 'getEpg']);
});

// Static info pages
Route::view('/about', 'pages.about')->name('pages.about');
Route::view('/privacy', 'pages.privacy')->name('pages.privacy');
Route::view('/terms', 'pages.terms')->name('pages.terms');

// --- Administration Operations ---
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Channels
    Route::resource('channels', ChannelAdminController::class);

    // Rights & Permissions
    Route::get('rights', [RightsAdminController::class, 'index'])->name('rights.index');
    Route::post('rights/{right}/status', [RightsAdminController::class, 'updateStatus'])->name('rights.update-status');

    // Diagnostics
    Route::get('diagnostics', [DiagnosticsAdminController::class, 'index'])->name('diagnostics.index');
    Route::post('diagnostics/check/{stream}', [DiagnosticsAdminController::class, 'runCheck'])->name('diagnostics.check');

    // IPTV-ORG Ingest & EPG Sync
    Route::get('ingest', [IngestAdminController::class, 'index'])->name('ingest.index');
    Route::post('ingest/playlist', [IngestAdminController::class, 'runPlaylistIngest'])->name('ingest.playlist');
    Route::post('ingest/epg', [IngestAdminController::class, 'runEpgSync'])->name('ingest.epg');
});
