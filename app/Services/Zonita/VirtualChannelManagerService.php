<?php

namespace App\Services\Zonita;

use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelSourceRule;
use App\Models\ContentRight;
use App\Models\PlaybackFailure;
use App\Models\ScheduleEntry;
use App\Models\SystemAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class VirtualChannelManagerService
{
    public function __construct(
        protected ChannelReserveService $reserveService,
        protected ProgrammeScheduleService $scheduleService
    ) {}

    /**
     * Create a new 24/7 Virtual Linear Channel with complete station branding and playout rules.
     */
    public function createVirtualChannel(array $data, ?User $user = null): Channel
    {
        $name = trim($data['name']);
        $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($name);

        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Channel::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $channelNumber = ! empty($data['channel_number'])
            ? trim($data['channel_number'])
            : $this->generateNextChannelNumber();

        $channel = Channel::create([
            'name' => $name,
            'slug' => $slug,
            'channel_number' => $channelNumber,
            'tvg_id' => $data['tvg_id'] ?? Str::studly($name).'.zonita',
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'banner_path' => $data['banner_path'] ?? null,
            'brand_color' => ! empty($data['brand_color']) ? $data['brand_color'] : '#E50914',
            'watermark_logo_path' => $data['watermark_logo_path'] ?? null,
            'broadcaster_name' => $data['broadcaster_name'] ?? 'Zonita TV Networks',
            'broadcaster_website' => $data['broadcaster_website'] ?? config('app.url'),
            'content_partner_id' => $data['content_partner_id'] ?? null,
            'official_destination_url' => $data['official_destination_url'] ?? url("/channel/{$slug}"),
            'country_code' => strtoupper($data['country_code'] ?? 'NGA'),
            'language_code' => strtolower($data['language_code'] ?? 'en'),
            'playback_mode' => $data['playback_mode'] ?? 'zoniva_original',
            'is_virtual' => true,
            'curation_type' => $data['curation_type'] ?? 'linear_streamer',
            'fallback_video_id' => $data['fallback_video_id'] ?? null,
            'station_ident_video_id' => $data['station_ident_video_id'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_published' => (bool) ($data['is_published'] ?? false),
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);

        // Attach Categories
        if (! empty($data['categories'])) {
            $channel->categories()->sync($data['categories']);
        } elseif (! empty($data['category_slug'])) {
            $category = Category::where('slug', $data['category_slug'])->first();
            if ($category) {
                $channel->categories()->sync([$category->id]);
            }
        }

        // Configure Source & Playout Priority Rules
        $rulesData = $data['rules'] ?? [];
        $searchKeywords = $this->parseArrayField($rulesData['search_keywords'] ?? ($data['search_keywords'] ?? [$name]));
        $excludedKeywords = $this->parseArrayField($rulesData['excluded_keywords'] ?? ($data['excluded_keywords'] ?? []));
        $approvedPublishers = $this->parseArrayField($rulesData['approved_publishers'] ?? ($data['approved_publishers'] ?? []));
        $preferredFormats = $this->parseArrayField($rulesData['preferred_formats'] ?? ($data['preferred_formats'] ?? ['movie', 'episode', 'documentary', 'highlights']));

        ChannelSourceRule::create([
            'channel_id' => $channel->id,
            'search_keywords' => $searchKeywords,
            'excluded_keywords' => $excludedKeywords,
            'approved_publishers' => $approvedPublishers,
            'preferred_formats' => $preferredFormats,
            'min_duration_minutes' => (int) ($rulesData['min_duration_minutes'] ?? ($data['min_duration_minutes'] ?? 5)),
            'max_duration_minutes' => (int) ($rulesData['max_duration_minutes'] ?? ($data['max_duration_minutes'] ?? 180)),
            'preferred_language' => strtolower($rulesData['preferred_language'] ?? ($data['language_code'] ?? 'en')),
            'repeat_cooldown_hours' => (int) ($rulesData['repeat_cooldown_hours'] ?? ($data['repeat_cooldown_hours'] ?? 8)),
            'min_reserve_count' => (int) ($rulesData['min_reserve_count'] ?? ($data['min_reserve_count'] ?? 5)),
            'allow_evergreen' => (bool) ($rulesData['allow_evergreen'] ?? true),
            'scheduling_mode' => $rulesData['scheduling_mode'] ?? ($data['scheduling_mode'] ?? 'hybrid'),
            'priority_level' => $rulesData['priority_level'] ?? ($data['priority_level'] ?? 'balanced'),
            'interstitial_ident_interval' => (int) ($rulesData['interstitial_ident_interval'] ?? 4),
            'timezone' => $rulesData['timezone'] ?? ($data['timezone'] ?? 'Africa/Lagos'),
        ]);

        // Create default ContentRight for the virtual channel
        ContentRight::firstOrCreate(
            ['channel_id' => $channel->id],
            [
                'rights_holder_name' => $channel->broadcaster_name ?? 'Zonita TV Networks',
                'classification' => 'direct_licence',
                'status' => 'embedding_approved',
                'allow_directory_listing' => true,
                'allow_web_embedding' => true,
                'allow_direct_hls' => true,
                'allow_monetisation' => true,
                'is_exclusive' => false,
                'effective_from' => now(),
                'reviewed_by_user_id' => $user?->id,
                'reviewed_at' => now(),
            ]
        );

        return $channel;
    }

    /**
     * Update station branding visual assets and thematic styling.
     */
    public function updateStationBranding(Channel $channel, array $brandingData): Channel
    {
        $channel->update([
            'brand_color' => $brandingData['brand_color'] ?? $channel->brand_color,
            'tagline' => $brandingData['tagline'] ?? $channel->tagline,
            'description' => $brandingData['description'] ?? $channel->description,
            'logo_path' => $brandingData['logo_path'] ?? $channel->logo_path,
            'banner_path' => $brandingData['banner_path'] ?? $channel->banner_path,
            'watermark_logo_path' => $brandingData['watermark_logo_path'] ?? $channel->watermark_logo_path,
            'fallback_video_id' => $brandingData['fallback_video_id'] ?? $channel->fallback_video_id,
            'station_ident_video_id' => $brandingData['station_ident_video_id'] ?? $channel->station_ident_video_id,
        ]);

        return $channel;
    }

    /**
     * Update playout priority rules and daypart configuration.
     */
    public function updatePlayoutPriorityRules(Channel $channel, array $rulesData): ChannelSourceRule
    {
        $rule = $channel->sourceRule ?? new ChannelSourceRule(['channel_id' => $channel->id]);

        $searchKeywords = isset($rulesData['search_keywords']) ? $this->parseArrayField($rulesData['search_keywords']) : $rule->search_keywords;
        $excludedKeywords = isset($rulesData['excluded_keywords']) ? $this->parseArrayField($rulesData['excluded_keywords']) : $rule->excluded_keywords;
        $approvedPublishers = isset($rulesData['approved_publishers']) ? $this->parseArrayField($rulesData['approved_publishers']) : $rule->approved_publishers;
        $preferredFormats = isset($rulesData['preferred_formats']) ? $this->parseArrayField($rulesData['preferred_formats']) : $rule->preferred_formats;

        $rule->fill([
            'search_keywords' => $searchKeywords,
            'excluded_keywords' => $excludedKeywords,
            'approved_publishers' => $approvedPublishers,
            'preferred_formats' => $preferredFormats,
            'min_duration_minutes' => (int) ($rulesData['min_duration_minutes'] ?? $rule->min_duration_minutes ?? 5),
            'max_duration_minutes' => (int) ($rulesData['max_duration_minutes'] ?? $rule->max_duration_minutes ?? 180),
            'repeat_cooldown_hours' => (int) ($rulesData['repeat_cooldown_hours'] ?? $rule->repeat_cooldown_hours ?? 8),
            'min_reserve_count' => (int) ($rulesData['min_reserve_count'] ?? $rule->min_reserve_count ?? 5),
            'allow_evergreen' => (bool) ($rulesData['allow_evergreen'] ?? $rule->allow_evergreen ?? true),
            'scheduling_mode' => $rulesData['scheduling_mode'] ?? $rule->scheduling_mode ?? 'hybrid',
            'priority_level' => $rulesData['priority_level'] ?? $rule->priority_level ?? 'balanced',
            'interstitial_ident_interval' => (int) ($rulesData['interstitial_ident_interval'] ?? $rule->interstitial_ident_interval ?? 4),
            'timezone' => $rulesData['timezone'] ?? $rule->timezone ?? 'Africa/Lagos',
        ]);

        $rule->save();

        return $rule;
    }

    /**
     * Computes comprehensive 360-degree playout health and station status.
     */
    public function getChannelPlayoutHealth(Channel $channel): array
    {
        $timezone = $channel->sourceRule?->timezone ?? 'Africa/Lagos';
        $now = Carbon::now($timezone);

        // 1. Current on-air playout item
        $currentEntry = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->with('video')
            ->first();

        // 2. Upcoming schedule items
        $upcomingEntries = ScheduleEntry::where('channel_id', $channel->id)
            ->where('start_time', '>', $now)
            ->orderBy('start_time')
            ->take(5)
            ->with('video')
            ->get();

        // 3. Playout coverage hours
        $maxScheduledEnd = ScheduleEntry::where('channel_id', $channel->id)
            ->where('end_time', '>', $now)
            ->max('end_time');

        $scheduledHoursAhead = $maxScheduledEnd
            ? round($now->diffInMinutes(Carbon::parse($maxScheduledEnd, $timezone)) / 60, 1)
            : 0;

        // 4. Reserve buffer health
        $reserveStatus = $this->reserveService->getReserveStatus($channel);

        // 5. Unresolved alerts and errors
        $unresolvedAlerts = SystemAlert::where('channel_id', $channel->id)
            ->where('is_resolved', false)
            ->count();

        $recentFailures = PlaybackFailure::where('channel_id', $channel->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        // 6. Playout health score (0-100)
        $healthScore = 100;
        if ($scheduledHoursAhead < 24) {
            $healthScore -= 35;
        } elseif ($scheduledHoursAhead < 72) {
            $healthScore -= 15;
        }

        if (! ($reserveStatus['is_sufficient'] ?? false)) {
            $healthScore -= 30;
        }

        if ($unresolvedAlerts > 0) {
            $healthScore -= min(25, $unresolvedAlerts * 10);
        }

        if ($recentFailures > 0) {
            $healthScore -= min(20, $recentFailures * 5);
        }

        $healthScore = max(0, min(100, $healthScore));

        return [
            'channel' => $channel,
            'is_on_air' => $channel->is_active && $channel->is_published,
            'current_entry' => $currentEntry,
            'upcoming_entries' => $upcomingEntries,
            'scheduled_hours_ahead' => $scheduledHoursAhead,
            'reserve_available' => $reserveStatus['current_reserve_count'] ?? 0,
            'reserve_required' => $reserveStatus['min_required'] ?? 5,
            'reserve_is_sufficient' => $reserveStatus['is_sufficient'] ?? false,
            'unresolved_alerts_count' => $unresolvedAlerts,
            'recent_failures_count' => $recentFailures,
            'health_score' => $healthScore,
            'timezone' => $timezone,
        ];
    }

    /**
     * Fast toggle for broadcast state (on-air, off-air, maintenance).
     */
    public function toggleBroadcastStatus(Channel $channel, string $action): Channel
    {
        match ($action) {
            'go_on_air' => $channel->update(['is_published' => true, 'is_active' => true]),
            'go_off_air' => $channel->update(['is_published' => false]),
            'toggle_publish' => $channel->update(['is_published' => ! $channel->is_published]),
            'toggle_active' => $channel->update(['is_active' => ! $channel->is_active]),
            default => null,
        };

        return $channel->fresh();
    }

    /**
     * Generate next suggested channel dial number.
     */
    protected function generateNextChannelNumber(): string
    {
        $maxNum = Channel::whereNotNull('channel_number')
            ->get()
            ->map(fn ($ch) => (int) preg_replace('/\D/', '', $ch->channel_number))
            ->filter(fn ($n) => $n >= 100)
            ->max();

        return strval(($maxNum ?: 100) + 1);
    }

    /**
     * Parse array, comma-separated string, or JSON list safely.
     */
    protected function parseArrayField(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (is_string($value)) {
            if (str_starts_with(trim($value), '[') && str_ends_with(trim($value), ']')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('trim', $decoded)));
                }
            }

            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }
}
