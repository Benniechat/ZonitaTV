<?php

namespace App\Jobs\Zonita;

use App\Models\SystemAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAdministrators implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SystemAlert $alert
    ) {}

    public function handle(): void
    {
        $channelName = $this->alert->channel?->name ?? 'Network Core';

        Log::alert("BROADCAST ALERT [{$this->alert->level}] for channel [{$channelName}] (Alert: {$this->alert->alert_type}): {$this->alert->message}");

        // If email or Slack webhook is configured, dispatch notification here
    }
}
