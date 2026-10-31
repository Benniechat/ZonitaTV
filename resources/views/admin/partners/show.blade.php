@extends('admin.layout')

@section('title', $partner->name . ' - Content Partner 360 CRM')

@section('admin_content')
<div class="space-y-6" x-data="{ showAgreementModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-extrabold text-white">{{ $partner->name }}</h1>
                @php
                    $stageColor = match($partner->pipeline_stage) {
                        'active' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'agreement_signed', 'content_received' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                        'negotiation', 'contact_made' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        'churned' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                        default => 'bg-gray-800 text-gray-300 border-gray-700',
                    };
                @endphp
                <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase border {{ $stageColor }}">
                    {{ str_replace('_', ' ', $partner->pipeline_stage) }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                Type: <span class="text-gray-200 capitalize">{{ str_replace('_', ' ', $partner->partner_type) }}</span> • Origin: <span class="text-gray-200 font-mono">{{ $partner->country_code }}</span>
                @if($partner->assignedAgent)
                    • Lead Manager: <span class="text-indigo-400">{{ $partner->assignedAgent->name }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.partners.edit', $partner->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                Edit Profile
            </a>
            <a href="{{ route('admin.partners.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                &larr; Pipeline
            </a>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Agreements</div>
            <div class="text-2xl font-black text-indigo-400 mt-1">{{ $metrics['active_agreements'] }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Contracts Signed</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Delivered Channels</div>
            <div class="text-2xl font-black text-white mt-1">{{ $metrics['total_channels'] }}</div>
            <div class="text-[10px] text-emerald-400 mt-1">Linear Broadcasts</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Delivered Content</div>
            <div class="text-2xl font-black text-white mt-1">{{ $metrics['total_videos'] }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Verified Video Assets</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active in Rotation</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ $metrics['active_videos'] }}</div>
            <div class="text-[10px] text-emerald-500/80 mt-1">Broadcasting Live</div>
        </div>
    </div>

    <!-- Pipeline Stage Advancement Bar -->
    <div class="p-4 rounded-xl bg-[#10131a] border border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-white block">Pipeline Progression</span>
            <span class="text-[11px] text-gray-400">Advance or retreat partner stage as acquisition milestones complete.</span>
        </div>
        <form method="POST" action="{{ route('admin.partners.stage', $partner->id) }}" class="flex items-center gap-2">
            @csrf
            <select name="pipeline_stage" class="bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-red-500">
                <option value="prospect" {{ $partner->pipeline_stage === 'prospect' ? 'selected' : '' }}>1. Prospect</option>
                <option value="contact_made" {{ $partner->pipeline_stage === 'contact_made' ? 'selected' : '' }}>2. Contact Made</option>
                <option value="negotiation" {{ $partner->pipeline_stage === 'negotiation' ? 'selected' : '' }}>3. In Negotiation</option>
                <option value="agreement_signed" {{ $partner->pipeline_stage === 'agreement_signed' ? 'selected' : '' }}>4. Agreement Signed</option>
                <option value="content_received" {{ $partner->pipeline_stage === 'content_received' ? 'selected' : '' }}>5. Content Received</option>
                <option value="active" {{ $partner->pipeline_stage === 'active' ? 'selected' : '' }}>6. Active Broadcaster</option>
                <option value="churned" {{ $partner->pipeline_stage === 'churned' ? 'selected' : '' }}>Churned / Inactive</option>
            </select>
            <button type="submit" class="px-4 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                Update Stage
            </button>
        </form>
    </div>

    <!-- Distribution Agreements Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white">Distribution Agreements & Licensing Terms</h3>
            <button @click="showAgreementModal = true" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Agreement</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($partner->agreements as $agreement)
                <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-xs font-mono text-red-400 font-bold block">{{ $agreement->agreement_number }}</span>
                            <h4 class="text-sm font-bold text-white mt-0.5">{{ $agreement->title }}</h4>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $agreement->status === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-gray-800 text-gray-400' }}">
                            {{ $agreement->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs py-2 border-y border-gray-800 text-gray-400">
                        <div>
                            <span class="text-[10px] text-gray-500 block">Agreement Model:</span>
                            <span class="text-white capitalize font-semibold">{{ str_replace('_', ' ', $agreement->agreement_type) }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-500 block">Exclusivity:</span>
                            <span class="text-white capitalize font-semibold">{{ str_replace('_', ' ', $agreement->exclusivity_type) }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-500 block">Effective Window:</span>
                            <span class="text-white font-mono">{{ $agreement->start_date->format('M Y') }} &rarr; {{ $agreement->end_date ? $agreement->end_date->format('M Y') : 'Perpetual' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-500 block">Rights Ledger Link:</span>
                            <span class="font-mono {{ $agreement->content_right_id ? 'text-emerald-400' : 'text-gray-500' }}">
                                {{ $agreement->content_right_id ? 'Linked (#'.$agreement->content_right_id.')' : 'Unlinked' }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($agreement->financial_terms))
                        <div class="text-[11px] text-gray-300 bg-gray-900/50 p-2.5 rounded-lg border border-gray-800/80">
                            <span class="text-[10px] text-gray-500 font-semibold block uppercase">Commercial Terms</span>
                            @if(isset($agreement->financial_terms['revenue_share_percentage']))
                                <div>Rev Share: <span class="text-emerald-400 font-bold">{{ $agreement->financial_terms['revenue_share_percentage'] }}%</span></div>
                            @endif
                            @if(isset($agreement->financial_terms['flat_fee_amount']))
                                <div>Fee: <span class="text-amber-400 font-bold">{{ number_format($agreement->financial_terms['flat_fee_amount']) }} {{ $agreement->financial_terms['currency'] ?? 'USD' }}</span></div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="md:col-span-2 p-8 text-center text-gray-500 rounded-xl bg-[#0d1017] border border-gray-800">
                    No distribution agreements registered yet for this partner.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Delivered Content Inventory -->
    <div class="p-5 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
        <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Delivered Assets & Broadcaster Inventory</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-xs font-semibold text-gray-400 block mb-2">Delivered Channels ({{ $partner->channels->count() }})</span>
                @forelse($partner->channels as $ch)
                    <div class="p-2 rounded bg-gray-900 border border-gray-800 flex items-center justify-between mb-1.5">
                        <span class="font-bold text-white">{{ $ch->name }}</span>
                        <a href="{{ route('admin.channels.show', $ch->id) }}" class="text-red-400 underline">View Channel &rarr;</a>
                    </div>
                @empty
                    <div class="text-gray-500 italic">No linear channels directly associated.</div>
                @endforelse
            </div>

            <div>
                <span class="text-xs font-semibold text-gray-400 block mb-2">Delivered Catalog Assets ({{ $partner->videos->count() }})</span>
                @forelse($partner->videos->take(5) as $vid)
                    <div class="p-2 rounded bg-gray-900 border border-gray-800 flex items-center justify-between mb-1.5">
                        <span class="font-bold text-white truncate max-w-xs">{{ $vid->title }}</span>
                        <a href="{{ route('admin.catalogue.show', $vid->id) }}" class="text-red-400 underline ml-2">Inspect &rarr;</a>
                    </div>
                @empty
                    <div class="text-gray-500 italic">No video assets directly assigned.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- New Distribution Agreement Modal -->
    <div x-show="showAgreementModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 max-w-xl w-full text-left space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h3 class="text-base font-bold text-white">Create Distribution Agreement</h3>
                <button @click="showAgreementModal = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('admin.partners.agreements.store', $partner->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Agreement Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Master Linear & VOD Distribution Agreement" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Agreement Model *</label>
                        <select name="agreement_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="revenue_share">Revenue Share (%)</option>
                            <option value="flat_fee">Flat Licensing Fee</option>
                            <option value="promotional_exchange">Promotional Exchange</option>
                            <option value="royalty_free">Royalty Free / Open</option>
                            <option value="hybrid">Hybrid (Flat + Rev Share)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Exclusivity *</label>
                        <select name="exclusivity_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="non_exclusive">Non-Exclusive</option>
                            <option value="exclusive_window">Exclusive Window (First Run)</option>
                            <option value="fully_exclusive">Fully Exclusive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Rev Share % (if applicable)</label>
                        <input type="number" step="0.1" name="rev_share_percentage" placeholder="e.g. 30" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Flat Fee (if applicable)</label>
                        <input type="number" step="0.01" name="flat_fee_amount" placeholder="e.g. 5000" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Start Date *</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">End Date</label>
                        <input type="date" name="end_date" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Contract Document Cloud URL</label>
                        <input type="url" name="contract_document_url" placeholder="https://..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-xs text-emerald-400 cursor-pointer pt-2">
                            <input type="checkbox" name="auto_activate" value="1" checked class="rounded border-gray-700 bg-gray-900 text-emerald-500 focus:ring-0">
                            <span>Auto-Activate and Mirror into Content Rights Ledger</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-800">
                    <button type="button" @click="showAgreementModal = false" class="px-4 py-2 bg-gray-800 text-xs text-gray-300 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-xs text-white font-bold rounded-lg shadow">Register Agreement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
