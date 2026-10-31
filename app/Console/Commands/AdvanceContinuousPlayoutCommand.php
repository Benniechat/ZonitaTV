<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Services\Zonita\ContinuousPlayoutService;
use Illuminate\Console\Command;

class AdvanceContinuousPlayoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playout:advance 
                            {--channel= : Specific channel slug to process}
                            {--replenish : Automatically replenish rolling schedule if horizon is low}
                            {--heartbeat : Output real-time playout health heartbeat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advances real-time linear playout progression, activates active programmes, fills gaps, and enforces failover continuity';

    /**
     * The console command aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['playout:tick'];

    /**
     * Execute the console command.
     */
    public function handle(ContinuousPlayoutService $playoutService): int
    {
        $channelSlug = $this->option('channel');
        $autoReplenish = (bool) $this->option('replenish');
        $showHeartbeat = (bool) $this->option('heartbeat');

        if ($showHeartbeat) {
            $heartbeat = $playoutService->getPlayoutHealthHeartbeat();
            $this->info('=== Zonita Playout Health Heartbeat ===');
            $this->table(['Metric', 'Value'], [
                ['System Status', $heartbeat['status']],
                ['Active Virtual Channels', $heartbeat['active_channels_count']],
                ['On-Air Channels Active', $heartbeat['on_air_channels_count']],
                ['Channels With Gaps', $heartbeat['channels_with_gaps_count']],
                ['Failures (Past 24h)', $heartbeat['failures_last_24h']],
                ['Total Broadcasts Completed', $heartbeat['total_broadcasts_completed']],
                ['Heartbeat Timestamp', $heartbeat['heartbeat_at']],
            ]);

            return self::SUCCESS;
        }

        if ($channelSlug) {
            $channel = Channel::where('slug', $channelSlug)->first();
            if (! $channel) {
                $this->error("Channel [{$channelSlug}] not found.");

                return self::FAILURE;
            }

            $this->info("Advancing continuous playout for [{$channel->name}] ({$channel->slug})...");
            $result = $playoutService->advanceChannelPlayout($channel, null, $autoReplenish);

            $this->table(['Attribute', 'Result'], [
                ['Channel', $result['channel_slug']],
                ['Completed Expired Entries', $result['completed_count']],
                ['Activated On-Air Entries', $result['activated_count']],
                ['Active Programme', $result['active_entry']['title'] ?? 'None (Standby)'],
                ['Active Video ID', $result['active_entry']['video_id'] ?? 'N/A'],
                ['Auto-Recovered Dead Stream', $result['recovered'] ? 'YES' : 'NO'],
                ['Emergency Gap Filled', $result['gap_filled'] ? 'YES' : 'NO'],
                ['Queue Horizon Replenished', $result['replenished'] ? 'YES' : 'NO'],
            ]);

            return self::SUCCESS;
        }

        $this->info('Advancing continuous playout across all active linear channels...');
        $summary = $playoutService->advanceAllChannels(null, $autoReplenish);

        $this->info("Processed {$summary['channels_processed']} channels.");
        $this->table(['Metric', 'Total Count'], [
            ['Channels Processed', $summary['channels_processed']],
            ['Entries Transitioned to Completed', $summary['total_completed']],
            ['Entries Activated to Playing', $summary['total_activated']],
            ['Channels Auto-Recovered', $summary['total_recovered']],
            ['Emergency Gaps Filled', $summary['total_gaps_filled']],
            ['Schedules Replenished', $summary['total_replenished']],
        ]);

        return self::SUCCESS;
    }
}
