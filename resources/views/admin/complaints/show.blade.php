@extends('admin.layout')

@section('title', "Complaint {$complaint->reference_number} - Zonita TV Admin")

@section('admin_content')
<div class="space-y-6 max-w-5xl">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-6">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.complaints.index') }}" class="text-xs text-gray-400 hover:text-white transition">&larr; Back to Register</a>
                <span class="text-gray-600">|</span>
                <span class="font-mono text-xs font-bold text-amber-400">{{ $complaint->reference_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white mt-1">Complaint Investigation & Action Portal</h1>
        </div>
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                @if($complaint->status === 'pending') bg-amber-500/20 text-amber-300 border border-amber-500/30
                @elseif($complaint->status === 'under_investigation') bg-blue-500/20 text-blue-300 border border-blue-500/30
                @elseif($complaint->status === 'content_disabled' || $complaint->status === 'content_removed') bg-red-500/20 text-red-300 border border-red-500/30
                @elseif($complaint->status === 'resolved') bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                @else bg-gray-700/40 text-gray-400 @endif">
                {{ str_replace('_', ' ', $complaint->status) }}
            </span>
        </div>
    </div>

    <!-- 2 Column Layout: Details on left, Actions on right -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Evidence & Particulars -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Complainant Card -->
            <div class="bg-[#121620] border border-gray-800 rounded-xl p-6">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                    Complainant & Authority
                </h2>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block">Complainant Name:</span>
                        <strong class="text-white">{{ $complaint->complainant_name }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Contact Email:</span>
                        <a href="mailto:{{ $complaint->complainant_email }}" class="text-amber-400 underline">{{ $complaint->complainant_email }}</a>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Organisation:</span>
                        <span class="text-gray-300">{{ $complaint->complainant_organization ?: 'None specified (Individual)' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Claimed Authority:</span>
                        <span class="text-gray-300 font-semibold">{{ ucwords(str_replace('_', ' ', $complaint->relationship_to_owner)) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Good Faith Declaration:</span>
                        <span class="text-emerald-400 font-medium">&#10003; Certified by Complainant</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Submission IP:</span>
                        <span class="font-mono text-gray-400">{{ $complaint->ip_address ?: 'Unknown' }}</span>
                    </div>
                </div>
            </div>

            <!-- Reported Content Card -->
            <div class="bg-[#121620] border border-gray-800 rounded-xl p-6">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                    Disputed Content & Particulars
                </h2>
                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-gray-500 block mb-1">Target URL on Zonita TV:</span>
                        <a href="{{ $complaint->infringing_url }}" target="_blank" class="text-amber-400 hover:text-amber-300 underline font-mono break-all flex items-center gap-1">
                            <span>{{ $complaint->infringing_url }}</span>
                            <svg class="w-3.5 h-3.5 inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    @if($complaint->content_reference)
                        <div>
                            <span class="text-gray-500 block mb-1">Content Reference / Video ID:</span>
                            <span class="font-mono bg-[#0b0d13] px-3 py-1.5 rounded-lg border border-gray-800 text-white inline-block">
                                {{ $complaint->content_reference }}
                            </span>
                        </div>
                    @endif

                    @if($complaint->video)
                        <div class="p-3.5 bg-[#0b0d13] rounded-xl border border-gray-800">
                            <span class="text-gray-500 block text-[11px] mb-1">Associated Internal Video Record:</span>
                            <div class="flex items-center justify-between">
                                <div>
                                    <strong class="text-white block">{{ $complaint->video->title }}</strong>
                                    <span class="text-[11px] text-gray-400">YouTube ID: {{ $complaint->video->youtube_id }} | Current Status: <span class="font-mono text-amber-400">{{ $complaint->video->availability_status }}</span></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <span class="text-gray-500 block mb-1">Description of Copyrighted / Protected Work:</span>
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800 text-gray-200 whitespace-pre-wrap leading-relaxed">
                            {{ $complaint->copyrighted_work_description }}
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-500 block mb-1">Infringement Explanation & Grounds:</span>
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800 text-gray-200 whitespace-pre-wrap leading-relaxed">
                            {{ $complaint->infringement_details }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail & Resolution History -->
            @if($complaint->resolution_notes || $complaint->resolved_at)
                <div class="bg-[#121620] border border-gray-800 rounded-xl p-6">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                        Audit Trail & Compliance Actions
                    </h2>
                    <div class="text-xs space-y-2 text-gray-300">
                        @if($complaint->resolved_at)
                            <p><span class="text-gray-500">Action Timestamp:</span> {{ $complaint->resolved_at->format('M d, Y H:i:s T') }}</p>
                        @endif
                        @if($complaint->resolver)
                            <p><span class="text-gray-500">Handled By Officer:</span> <strong class="text-white">{{ $complaint->resolver->name }}</strong> ({{ $complaint->resolver->email }})</p>
                        @endif
                        @if($complaint->action_taken)
                            <p><span class="text-gray-500">Action Applied:</span> <strong class="text-amber-400">{{ $complaint->action_taken }}</strong></p>
                        @endif
                        @if($complaint->resolution_notes)
                            <div class="mt-3 pt-3 border-t border-gray-800">
                                <span class="text-gray-500 block mb-1">Internal Case Notes:</span>
                                <div class="bg-[#0b0d13] p-3 rounded-xl border border-gray-800 font-mono text-[11px] text-gray-300 whitespace-pre-wrap">
                                    {{ $complaint->resolution_notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Col: Administrative Actions -->
        <div class="space-y-6">

            <!-- 1-Click Takedown Panel -->
            <div class="bg-red-950/20 border border-red-500/40 rounded-xl p-6 shadow-xl">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Execute Immediate Takedown</h2>
                </div>
                <p class="text-xs text-gray-300 mb-4 leading-relaxed">
                    Executing a takedown immediately marks the video as <code class="text-red-400">disabled</code>, cancels any upcoming queue entries, and adds the content to the discovery exclusion register.
                </p>

                <form action="{{ route('admin.complaints.takedown', $complaint) }}" method="POST" class="space-y-3.5"
                      onsubmit="return confirm('Confirm takedown? This will immediately purge and disable the content from active EPG playout.');">
                    @csrf

                    <div>
                        <label for="takedown_action" class="block text-[11px] font-semibold text-gray-400 mb-1">Takedown Scope</label>
                        <select name="takedown_action" id="takedown_action" required
                                class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="disable_video">Disable Video & Purge Active Schedules</option>
                            <option value="remove_video">Permanently Blacklist Video From Schedulers</option>
                        </select>
                    </div>

                    <div>
                        <label for="admin_reason" class="block text-[11px] font-semibold text-gray-400 mb-1">Reason for Action <span class="text-red-400">*</span></label>
                        <textarea name="admin_reason" id="admin_reason" rows="3" required
                                  class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg p-2 text-xs text-white placeholder-gray-600 focus:outline-none focus:border-red-500"
                                  placeholder="e.g. Valid copyright takedown notice filed by rights owner; verified unauthorised stream."></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-500 transition shadow-lg shadow-red-600/30 uppercase tracking-wider">
                        Execute Legal Takedown
                    </button>
                </form>
            </div>

            <!-- Case Status & Notes Update -->
            <div class="bg-[#121620] border border-gray-800 rounded-xl p-6 shadow-xl">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">
                    Update Investigation Status
                </h2>

                <form action="{{ route('admin.complaints.status', $complaint) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="status" class="block text-[11px] font-semibold text-gray-400 mb-1">Case Status</label>
                        <select name="status" id="status" required
                                class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg p-2 text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="pending" {{ $complaint->status === 'pending' ? 'selected' : '' }}>Pending Initial Review</option>
                            <option value="under_investigation" {{ $complaint->status === 'under_investigation' ? 'selected' : '' }}>Under Investigation</option>
                            <option value="content_disabled" {{ $complaint->status === 'content_disabled' ? 'selected' : '' }}>Content Disabled</option>
                            <option value="content_removed" {{ $complaint->status === 'content_removed' ? 'selected' : '' }}>Content Permanently Removed</option>
                            <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Resolved (Amicable / Withdrawn)</option>
                            <option value="rejected" {{ $complaint->status === 'rejected' ? 'selected' : '' }}>Rejected (Invalid / Insufficient Evidence)</option>
                        </select>
                    </div>

                    <div>
                        <label for="action_taken" class="block text-[11px] font-semibold text-gray-400 mb-1">Action Summary (Optional)</label>
                        <input type="text" name="action_taken" id="action_taken" value="{{ old('action_taken', $complaint->action_taken) }}"
                               class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg p-2 text-xs text-white placeholder-gray-600 focus:outline-none focus:border-amber-500"
                               placeholder="e.g. Verified with creator; stream authorized under CC-BY.">
                    </div>

                    <div>
                        <label for="resolution_notes" class="block text-[11px] font-semibold text-gray-400 mb-1">Internal Investigation Notes</label>
                        <textarea name="resolution_notes" id="resolution_notes" rows="4"
                                  class="w-full bg-[#0b0d13] border border-gray-800 rounded-lg p-2 text-xs text-white placeholder-gray-600 focus:outline-none focus:border-amber-500"
                                  placeholder="Record findings, communications with rights holders, or legal review notes.">{{ old('resolution_notes', $complaint->resolution_notes) }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-black bg-amber-500 hover:bg-amber-400 transition shadow-lg shadow-amber-500/20 uppercase tracking-wider">
                        Save Case Updates
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
