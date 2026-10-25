<?php

use App\Http\Controllers\Admin\BroadcastingAdminController;
use App\Http\Controllers\Admin\ChannelAdminController;
use App\Http\Controllers\Admin\CloudinaryMediaController;
use App\Http\Controllers\Admin\ComplaintAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiagnosticsAdminController;
use App\Http\Controllers\Admin\IngestAdminController;
use App\Http\Controllers\Admin\RightsAdminController;
use App\Http\Controllers\Admin\SportsAdminController;
use App\Http\Controllers\Api\PlaybackController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\GospelFilmController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IslamicChannelController;
use App\Http\Controllers\LegalComplianceController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NollywoodController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SportsController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

// --- Public Discovery Experience ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
Route::get('/channel/{slug}', [ChannelController::class, 'show'])->name('channels.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/gospel-cinema', [GospelFilmController::class, 'index'])->name('gospel.index');
Route::get('/islamic', [IslamicChannelController::class, 'index'])->name('islamic.index');
Route::get('/nollywood', [NollywoodController::class, 'index'])->name('nollywood.index');
Route::get('/nollywood-cinema', [NollywoodController::class, 'index']);

// --- Automated Live Sports Broadcasting & Discovery Engine ---
Route::prefix('sports')->name('sports.')->group(function () {
    Route::get('/', [SportsController::class, 'index'])->name('index');
    Route::get('/live', [SportsController::class, 'live'])->name('live');
    Route::get('/upcoming', [SportsController::class, 'upcoming'])->name('upcoming');
    Route::get('/schedule', [SportsController::class, 'schedule'])->name('schedule');
    Route::get('/match/{fixture}', [SportsController::class, 'show'])->name('show');
});

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

// Legal Disclaimer & Platform Compliance Framework
Route::get('/legal-disclaimer', [LegalComplianceController::class, 'disclaimer'])->name('legal.disclaimer');
Route::get('/terms-of-use', [LegalComplianceController::class, 'terms'])->name('legal.terms');
Route::get('/copyright-policy', [LegalComplianceController::class, 'copyrightPolicy'])->name('legal.copyright');
Route::get('/privacy-policy', [LegalComplianceController::class, 'privacyPolicy'])->name('legal.privacy');
Route::get('/advertising-policy', [LegalComplianceController::class, 'advertisingPolicy'])->name('legal.advertising');
Route::get('/content-rights', [LegalComplianceController::class, 'contentRights'])->name('legal.content-rights');
Route::get('/report-content', [LegalComplianceController::class, 'reportForm'])->name('legal.report');
Route::post('/report-content', [LegalComplianceController::class, 'submitReport'])->name('legal.report.submit')->middleware('throttle:5,10');

// Static info pages & backward-compatible aliases
Route::view('/about', 'pages.about')->name('pages.about');
Route::redirect('/privacy', '/privacy-policy', 301);
Route::redirect('/terms', '/terms-of-use', 301);

// Authentication & Identity
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- Administration Operations ---
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Channels
    Route::resource('channels', ChannelAdminController::class);

    // 24/7 Virtual Broadcasting Engine
    Route::prefix('broadcasting')->name('broadcasting.')->group(function () {
        Route::get('/', [BroadcastingAdminController::class, 'index'])->name('index');
        Route::post('/regenerate/{channel}', [BroadcastingAdminController::class, 'regenerateSchedule'])->name('regenerate');
        Route::post('/reverify', [BroadcastingAdminController::class, 'reverifyVideos'])->name('reverify');
        Route::post('/alert/{alert}/resolve', [BroadcastingAdminController::class, 'resolveAlert'])->name('resolve-alert');
        Route::get('/rules/{channel}', [BroadcastingAdminController::class, 'editRule'])->name('rules.edit');
        Route::post('/rules/{channel}', [BroadcastingAdminController::class, 'updateRule'])->name('rules.update');
    });

    // Sports Master Control Room (MCR)
    Route::prefix('sports')->name('sports.')->group(function () {
        Route::get('/', [SportsAdminController::class, 'index'])->name('index');
        Route::post('/sync-fixtures', [SportsAdminController::class, 'syncFixtures'])->name('sync-fixtures');
        Route::post('/verify-broadcasts', [SportsAdminController::class, 'verifyBroadcasts'])->name('verify-broadcasts');
        Route::post('/sync-schedule', [SportsAdminController::class, 'syncSchedule'])->name('sync-schedule');
        Route::post('/sessions/{session}/emergency-fallback', [SportsAdminController::class, 'emergencyFallback'])->name('emergency-fallback');
        Route::post('/sessions/{session}/force-mode-a', [SportsAdminController::class, 'forceModeA'])->name('force-mode-a');
        Route::post('/fixtures/{fixture}/toggle-mode', [SportsAdminController::class, 'toggleMode'])->name('toggle-mode');
    });

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

    // Legal Compliance & Content Complaints Management
    Route::get('complaints', [ComplaintAdminController::class, 'index'])->name('complaints.index');
    Route::get('complaints/{complaint}', [ComplaintAdminController::class, 'show'])->name('complaints.show');
    Route::post('complaints/{complaint}/status', [ComplaintAdminController::class, 'updateStatus'])->name('complaints.status');
    Route::post('complaints/{complaint}/takedown', [ComplaintAdminController::class, 'takedown'])->name('complaints.takedown');

    // Cloudinary Media Storage & Upload Widget Operations
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [CloudinaryMediaController::class, 'index'])->name('index');
        Route::get('/signature', [CloudinaryMediaController::class, 'signature'])->name('signature');
        Route::post('/upload', [CloudinaryMediaController::class, 'upload'])->name('upload');
    });
});
