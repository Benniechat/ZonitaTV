@extends('admin.layout')

@section('title', 'Configure Programming Rules - ' . $channel->name)

@section('admin_content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.broadcasting.index') }}" class="text-xs text-gray-400 hover:text-white flex items-center gap-1 transition mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Virtual Broadcasting Network
            </a>
            <h1 class="text-2xl font-black text-white">Programming Rules: {{ $channel->name }}</h1>
            <p class="text-xs text-gray-400 mt-1">Configure automated discovery heuristics, cooldown restrictions, and playlist packing parameters.</p>
        </div>
    </div>

    <form action="{{ route('admin.broadcasting.rules.update', $channel) }}" method="POST" class="bg-[#10131a] border border-gray-800 rounded-xl p-6 space-y-6">
        @csrf

        <!-- Search Keywords -->
        <div>
            <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                Discovery Search Keywords <span class="text-red-400">*</span>
            </label>
            <p class="text-[11px] text-gray-400 mb-2">Comma-separated terms used by the discovery engine to ingest candidate YouTube videos.</p>
            <input type="text" name="search_keywords" 
                   value="{{ old('search_keywords', is_array($rule->search_keywords) ? implode(', ', $rule->search_keywords) : '') }}"
                   class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 font-mono"
                   required placeholder="Premier League, Champions League, Football Highlights">
            @error('search_keywords') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <!-- Excluded Keywords -->
        <div>
            <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                Excluded Keywords (Hard Rejections)
            </label>
            <p class="text-[11px] text-gray-400 mb-2">Any candidate matching these keywords will be rejected immediately (e.g. video game playthroughs, fan reaction vlogs).</p>
            <input type="text" name="excluded_keywords" 
                   value="{{ old('excluded_keywords', is_array($rule->excluded_keywords) ? implode(', ', $rule->excluded_keywords) : '') }}"
                   class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 font-mono"
                   placeholder="fifa gameplay, gaming, ps5 gameplay, reaction video">
            @error('excluded_keywords') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <!-- Approved Publishers -->
        <div>
            <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                Approved Channels / Publishers
            </label>
            <p class="text-[11px] text-gray-400 mb-2">Comma-separated authoritative YouTube channels prioritised for content scheduling.</p>
            <input type="text" name="approved_publishers" 
                   value="{{ old('approved_publishers', is_array($rule->approved_publishers) ? implode(', ', $rule->approved_publishers) : '') }}"
                   class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 font-mono"
                   placeholder="UEFA, SuperSport, Sky Sports Football">
            @error('approved_publishers') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <!-- Duration Bounds Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                    Min Duration (Minutes) <span class="text-red-400">*</span>
                </label>
                <input type="number" name="min_duration_minutes" 
                       value="{{ old('min_duration_minutes', $rule->min_duration_minutes ?? 5) }}" min="1"
                       class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500" required>
                @error('min_duration_minutes') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                    Max Duration (Minutes) <span class="text-red-400">*</span>
                </label>
                <input type="number" name="max_duration_minutes" 
                       value="{{ old('max_duration_minutes', $rule->max_duration_minutes ?? 60) }}" min="2"
                       class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500" required>
                @error('max_duration_minutes') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Cooldown and Reserve Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                    Repeat Cooldown (Hours) <span class="text-red-400">*</span>
                </label>
                <p class="text-[10px] text-gray-400 mb-1.5">No video can repeat within this window.</p>
                <input type="number" name="repeat_cooldown_hours" 
                       value="{{ old('repeat_cooldown_hours', $rule->repeat_cooldown_hours ?? 6) }}" min="1"
                       class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500" required>
                @error('repeat_cooldown_hours') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                    Min Reserve Count <span class="text-red-400">*</span>
                </label>
                <p class="text-[10px] text-gray-400 mb-1.5">Verified standby items for fallback.</p>
                <input type="number" name="min_reserve_count" 
                       value="{{ old('min_reserve_count', $rule->min_reserve_count ?? 5) }}" min="1"
                       class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500" required>
                @error('min_reserve_count') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                    Broadcast Timezone <span class="text-red-400">*</span>
                </label>
                <p class="text-[10px] text-gray-400 mb-1.5">Channel primary local time.</p>
                <input type="text" name="timezone" 
                       value="{{ old('timezone', $rule->timezone ?? 'Africa/Lagos') }}"
                       class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500 font-mono" required>
                @error('timezone') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Scheduling Mode -->
        <div>
            <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1">
                Playout Mode <span class="text-red-400">*</span>
            </label>
            <select name="scheduling_mode" class="w-full bg-[#0a0c10] border border-gray-800 rounded-lg px-4 py-2.5 text-xs text-white focus:outline-none focus:border-red-500">
                <option value="hybrid" {{ old('scheduling_mode', $rule->scheduling_mode ?? 'hybrid') === 'hybrid' ? 'selected' : '' }}>Hybrid (Themed Daypart Blocks + Continuous Dynamic Packing)</option>
                <option value="continuous" {{ old('scheduling_mode', $rule->scheduling_mode ?? '') === 'continuous' ? 'selected' : '' }}>Continuous Rolling (Fluid Sequential Packing)</option>
                <option value="blocks" {{ old('scheduling_mode', $rule->scheduling_mode ?? '') === 'blocks' ? 'selected' : '' }}>Themed Fixed Blocks</option>
            </select>
            @error('scheduling_mode') <span class="text-red-400 text-[11px]">{{ $message }}</span> @enderror
        </div>

        <div class="pt-4 border-t border-gray-800 flex items-center justify-end gap-3">
            <a href="{{ route('admin.broadcasting.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-400 hover:text-white transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-500 text-white font-bold text-xs rounded-lg transition shadow-lg shadow-red-600/30">
                Save Programming Rules
            </button>
        </div>
    </form>
</div>
@endsection
