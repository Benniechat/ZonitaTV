@extends('admin.layout')

@section('title', $channel->name . ' - Station Profile & Playout Monitor - Zoniva TV')

@section('admin_content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <a href="{{ route('admin.channels.index') }}" class="hover:text-white transition">Channel Catalogue</a>
            <span>/</span>
            <span class="text-white font-semibold">{{ $channel->name }}</span>
            @if($channel->isVirtual())
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-950/60 text-red-400 border border-red-500/30 uppercase tracking-wider">Virtual 24/7</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <!-- On-Air Toggle Form -->
            <form action="{{ route('admin.channels.toggle-status', $channel->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="toggle_publish">
                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $channel->is_published ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gray-800 hover:bg-gray-700 text-gray-300' }}">
                    <span class="w-2 h-2 rounded-full {{ $channel->is_published ? 'bg-white animate-pulse' : 'bg-gray-500' }}"></span>
                    {{ $channel->is_published ? 'On-Air (Broadcasting)' : 'Off-Air (Slate)' }}
                </button>
            </form>

            @if($channel->sourceRule)
                <form action="{{ route('admin.broadcasting.regenerate', $channel->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-200 transition">
                        ↻ Regenerate 7D EPG
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-200 transition">
                Edit Channel
            </a>
            <a href="{{ route('channels.show', $channel->slug) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-xs font-bold text-white transition">
                Preview Player ↗
            </a>
        </div>
    </div>

    <!-- Station Header & Visual Identity Preview -->
    <div class="relative rounded-2xl overflow-hidden border border-gray-800 bg-[#0d1017]">
        <!-- Banner Background -->
        <div class="h-44 w-full bg-cover bg-center relative" style="background-image: url('{{ $channel->banner_url }}');">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0d1017] via-[#0d1017]/70 to-transparent"></div>
            <!-- Accent Top Line -->
            <div class="absolute top-0 inset-x-0 h-1.5" style="background-color: {{ $channel->accent_color }};"></div>
        </div>

        <div class="relative px-6 pb-6 -mt-16 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
            <div class="flex items-end gap-4">
                <!-- Channel Logo / Badge -->
                <div class="relative w-24 h-24 rounded-2xl overflow-hidden bg-[#12151c] border-2 shadow-2xl flex-shrink-0 flex items-center justify-center p-2" style="border-color: {{ $channel->accent_color }};">
                    <img src="{{ $channel->logo_url }}" alt="{{ $channel->name }}" class="max-w-full max-h-full object-contain">
                    @if($channel->watermark_logo_path)
                        <div class="absolute top-1 right-1 w-4 h-4 rounded-full bg-black/80 flex items-center justify-center text-[8px] text-gray-300 font-bold" title="Station Bug Configured">
                            BUG
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-mono font-bold text-white shadow" style="background-color: {{ $channel->accent_color }};">
                            CH {{ $channel->display_number }}
                        </span>
                        <h1 class="text-2xl font-black text-white">{{ $channel->name }}</h1>
                    </div>
                    @if($channel->tagline)
                        <p class="text-xs text-gray-300 mt-1 italic">"{{ $channel->tagline }}"</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-400">
                        <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-200 uppercase font-mono">{{ $channel->country_code }}</span>
                        <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-200 uppercase font-mono">{{ $channel->language_code }}</span>
                        <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-200 capitalize">{{ str_replace('_', ' ', $channel->playback_mode) }}</span>
                        @if($channel->contentPartner)
                            <span class="text-gray-500">•</span>
                            <a href="{{ route('admin.partners.show', $channel->contentPartner->id) }}" class="text-indigo-400 hover:underline">
                                Partner: {{ $channel->contentPartner->name }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Categories Chips -->
            <div class="flex flex-wrap gap-1.5">
                @foreach($channel->categories as $category)
                    <span class="px-2.5 py-1 rounded-lg bg-gray-800/80 border border-gray-700 text-[11px] font-medium text-gray-300">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Station Health & Buffer Diagnostics Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Health Score -->
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Station Health Score</div>
            <div class="text-2xl font-black mt-1 {{ $playoutHealth['health_score'] >= 80 ? 'text-emerald-400' : ($playoutHealth['health_score'] >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                {{ $playoutHealth['health_score'] }}%
            </div>
            <div class="text-[10px] text-gray-500 mt-1">
                {{ $playoutHealth['health_score'] >= 80 ? 'Optimal Playout Continuity' : 'Requires EPG / Reserve Attention' }}
            </div>
        </div>

        <!-- EPG Schedule Ahead -->
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Scheduled Ahead</div>
            <div class="text-2xl font-black text-white mt-1">
                {{ $playoutHealth['scheduled_hours_ahead'] }} <span class="text-xs font-normal text-gray-400">hrs</span>
            </div>
            <div class="text-[10px] mt-1 {{ $playoutHealth['scheduled_hours_ahead'] >= 48 ? 'text-emerald-400' : 'text-amber-400' }}">
                {{ $playoutHealth['scheduled_hours_ahead'] >= 48 ? 'Rolling EPG Healthy' : 'EPG Running Low (<48h)' }}
            </div>
        </div>

        <!-- Playout Reserve Buffer -->
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Content Reserve Pool</div>
            <div class="text-2xl font-black mt-1 {{ $playoutHealth['reserve_is_sufficient'] ? 'text-indigo-400' : 'text-amber-400' }}">
                {{ $playoutHealth['reserve_available'] }} <span class="text-xs font-normal text-gray-400">/ {{ $playoutHealth['reserve_required'] }} min</span>
            </div>
            <div class="text-[10px] text-gray-500 mt-1">
                {{ $playoutHealth['reserve_is_sufficient'] ? 'Sufficient Verified Reserve' : 'Reserve Below Threshold' }}
            </div>
        </div>

        <!-- Alarms / Failures -->
        <div class="p-4 rounded-xl bg-[#0d1017] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Stream & Playout Alarms</div>
            <div class="text-2xl font-black mt-1 {{ $playoutHealth['unresolved_alerts_count'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                {{ $playoutHealth['unresolved_alerts_count'] }}
            </div>
            <div class="text-[10px] text-gray-500 mt-1">
                {{ $playoutHealth['recent_failures_count'] }} Failures (Last 24h)
            </div>
        </div>
    </div>

    <!-- Playout Monitor: Now Broadcasting vs Next Up -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Currently On-Air -->
        <div class="lg:col-span-2 p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Current On-Air Broadcast</h3>
                </div>
                <span class="text-xs text-gray-400 font-mono">TZ: {{ $playoutHealth['timezone'] }}</span>
            </div>

            @if($playoutHealth['current_entry'])
                @php $entry = $playoutHealth['current_entry']; @endphp
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="relative w-full sm:w-48 h-28 rounded-lg overflow-hidden bg-black flex-shrink-0">
                        @if($entry->video && $entry->video->thumbnail_url)
                            <img src="{{ $entry->video->thumbnail_url }}" alt="{{ $entry->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-gray-600 bg-gray-900">No Image</div>
                        @endif
                        <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/80 text-[10px] font-mono text-white">
                            {{ gmdate("H:i:s", $entry->duration_seconds) }}
                        </span>
                    </div>

                    <div class="flex-grow space-y-2">
                        <div class="text-base font-bold text-white">{{ $entry->title }}</div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                            <span>Air Time: <strong class="text-white font-mono">{{ $entry->start_time->format('H:i') }} - {{ $entry->end_time->format('H:i') }}</strong></span>
                            @if($entry->video)
                                <span>Format: <strong class="text-indigo-400 uppercase">{{ $entry->video->format_type }}</strong></span>
                                <span>Source: <span class="text-gray-300 font-mono">{{ $entry->video->youtube_id }}</span></span>
                            @endif
                        </div>
                        @if($entry->video && $entry->video->channel_title)
                            <div class="text-xs text-gray-400">
                                Creator / Broadcaster: <span class="text-white">{{ $entry->video->channel_title }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-6 rounded-lg bg-gray-900/50 border border-dashed border-gray-800 text-center space-y-2">
                    <div class="text-xs font-semibold text-amber-400">No Active Playout Entry at this Minute</div>
                    <p class="text-xs text-gray-400">
                        Station is broadcasting via Emergency Slate / Fallback Loop Video:
                        <span class="font-mono text-white">{{ $channel->fallback_video_id ?: 'DEFAULT_ZONITA_LOOP' }}</span>
                    </p>
                    @if($channel->sourceRule)
                        <form action="{{ route('admin.broadcasting.regenerate', $channel->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="mt-2 px-4 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-xs font-bold text-white transition">
                                Trigger Playout Schedule Fill Now
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <!-- Upcoming Schedule Queue -->
            <div class="pt-2 border-t border-gray-800/80">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Next in Lineup</h4>
                <div class="space-y-2">
                    @forelse($playoutHealth['upcoming_entries'] as $up)
                        <div class="p-2.5 rounded-lg bg-gray-900/60 border border-gray-800/60 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-gray-400 font-bold w-12">{{ $up->start_time->format('H:i') }}</span>
                                <span class="text-white font-medium truncate max-w-md">{{ $up->title }}</span>
                            </div>
                            <span class="text-gray-500 font-mono">{{ gmdate("i:s", $up->duration_seconds) }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 italic">No upcoming schedule entries queued.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Station Branding & Playout Rules Sidecard -->
        <div class="space-y-6">
            <!-- Branding Details -->
            <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-3">
                <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Station Identity Specs</h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Dial Number</dt>
                        <dd class="font-mono text-white font-bold">CH {{ $channel->display_number }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-400">Accent Color</dt>
                        <dd class="flex items-center gap-1.5 font-mono text-xs text-white">
                            <span class="w-3.5 h-3.5 rounded" style="background-color: {{ $channel->accent_color }};"></span>
                            {{ $channel->accent_color }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">TVG-ID Identifier</dt>
                        <dd class="font-mono text-gray-300 truncate max-w-[160px]">{{ $channel->tvg_id ?: 'Unset' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Watermark Bug</dt>
                        <dd class="text-gray-300">{{ $channel->watermark_logo_path ? 'Custom Overlay' : 'Default Logo' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Station Ident Clip</dt>
                        <dd class="font-mono text-gray-300">{{ $channel->station_ident_video_id ?: 'Standard Bumper' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Fallback Video Slate</dt>
                        <dd class="font-mono text-gray-300">{{ $channel->fallback_video_id ?: 'Default Loop' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Playout Priority Rules (if virtual) -->
            @if($channel->sourceRule)
                @php $rule = $channel->sourceRule; @endphp
                <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-2">
                        <h3 class="text-sm font-bold text-white">Playout Priority Rules</h3>
                        <a href="{{ route('admin.broadcasting.rules.edit', $channel->id) }}" class="text-[11px] text-red-400 hover:underline">Edit &rarr;</a>
                    </div>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block mb-1">Rotation Priority Level:</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-indigo-950 text-indigo-300 border border-indigo-500/30">
                                {{ str_replace('_', ' ', $rule->priority_level ?? 'balanced') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1">Curation Keywords:</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($rule->search_keywords ?? [] as $kw)
                                    <span class="px-1.5 py-0.5 rounded bg-gray-800 text-[10px] text-gray-300">{{ $kw }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-800/60">
                            <div>
                                <span class="text-gray-500 text-[10px] block">Repeat Cooldown</span>
                                <span class="font-bold text-white">{{ $rule->repeat_cooldown_hours }} hours</span>
                            </div>
                            <div>
                                <span class="text-gray-500 text-[10px] block">Scheduling Mode</span>
                                <span class="font-bold text-white capitalize">{{ $rule->scheduling_mode }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 text-[10px] block">Min Duration</span>
                                <span class="font-bold text-white">{{ $rule->min_duration_minutes }} min</span>
                            </div>
                            <div>
                                <span class="text-gray-500 text-[10px] block">Max Duration</span>
                                <span class="font-bold text-white">{{ $rule->max_duration_minutes }} min</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
