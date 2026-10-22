<?php

use App\Jobs\Zonita\GenerateChannelReports;
use App\Jobs\Zonita\MonitorScheduleHealth;
use App\Jobs\Zonita\UpdateVideoEligibility;
use App\Models\Channel;
use App\Jobs\Zonita\RefreshChannelQueue;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- Zonita TV 24/7 Virtual Broadcasting Engine Scheduled Tasks ---

// 1. Health & Queue Monitoring: Run every 15 minutes to guarantee no channel schedule runs dry
Schedule::call(function () {
    MonitorScheduleHealth::dispatch();
})->everyFifteenMinutes()->name('zonita:monitor-schedule-health');

// 2. Continuous Video Eligibility Reverification: Run hourly in batches
Schedule::call(function () {
    UpdateVideoEligibility::dispatch(batchLimit: 30);
})->hourly()->name('zonita:update-video-eligibility');

// 3. Rolling Schedule Maintenance: Ensure all channels maintain minimum 48 hours rolling queue
Schedule::call(function () {
    $channels = Channel::whereHas('sourceRule')->get();
    foreach ($channels as $channel) {
        RefreshChannelQueue::dispatch($channel, minFutureHours: 48);
    }
})->everySixHours()->name('zonita:refresh-channel-queues');

// 4. Daily Broadcast Reports & Uptime Metrics
Schedule::call(function () {
    GenerateChannelReports::dispatch(lookbackHours: 24);
})->dailyAt('00:05')->name('zonita:daily-broadcast-reports');
