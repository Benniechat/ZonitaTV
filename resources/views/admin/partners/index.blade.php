@extends('admin.layout')

@section('title', 'Content Partner CRM - Zoniva TV Administration')

@section('admin_content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Content Partner CRM & Acquisition</h1>
            <p class="text-xs text-gray-400 mt-1">Manage relationships with film studios, TV networks, independent creators, and religious broadcasters.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.partners.create') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Onboard Partner</span>
            </a>
        </div>
    </div>

    <!-- Pipeline Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Partners</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['total_partners']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">CRM Database</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">In Pipeline</div>
            <div class="text-2xl font-black text-amber-400 mt-1">{{ number_format($metrics['prospects']) }}</div>
            <div class="text-[10px] text-amber-400/80 mt-1">Prospect / Negotiation</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Partners</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($metrics['active_partners']) }}</div>
            <div class="text-[10px] text-emerald-500/80 mt-1">Broadcasting Live</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Contracts</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['agreements_count']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Distribution Agreements</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active Licences</div>
            <div class="text-2xl font-black text-indigo-400 mt-1">{{ number_format($metrics['active_agreements']) }}</div>
            <div class="text-[10px] text-indigo-400/80 mt-1">Live in Rights Ledger</div>
        </div>
    </div>

    <!-- Filter & Stage Bar -->
    <div class="p-4 rounded-xl bg-[#10131a] border border-gray-800 space-y-3">
        <!-- Stage Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-gray-800/80 text-xs">
            <a href="{{ route('admin.partners.index') }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ empty($stage) ? 'bg-red-600/20 text-red-400 border border-red-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                All Stages
            </a>
            <a href="{{ route('admin.partners.index', ['stage' => 'prospect']) }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $stage === 'prospect' ? 'bg-amber-600/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Prospects
            </a>
            <a href="{{ route('admin.partners.index', ['stage' => 'negotiation']) }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $stage === 'negotiation' ? 'bg-amber-600/20 text-amber-400 border border-amber-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Negotiation
            </a>
            <a href="{{ route('admin.partners.index', ['stage' => 'agreement_signed']) }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $stage === 'agreement_signed' ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Agreement Signed
            </a>
            <a href="{{ route('admin.partners.index', ['stage' => 'content_received']) }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $stage === 'content_received' ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Content Received
            </a>
            <a href="{{ route('admin.partners.index', ['stage' => 'active']) }}" class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap {{ $stage === 'active' ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30' : 'text-gray-400 hover:text-white hover:bg-gray-800/50' }}">
                Active
            </a>
        </div>

        <form method="GET" action="{{ route('admin.partners.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            @if($stage)
                <input type="hidden" name="stage" value="{{ $stage }}">
            @endif
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search partner name, contact person, email..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <select name="type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="">All Partner Types</option>
                    <option value="studio" {{ $type === 'studio' ? 'selected' : '' }}>Film & TV Studio</option>
                    <option value="tv_network" {{ $type === 'tv_network' ? 'selected' : '' }}>Television Network</option>
                    <option value="religious_broadcaster" {{ $type === 'religious_broadcaster' ? 'selected' : '' }}>Faith / Religious Broadcaster</option>
                    <option value="sports_league" {{ $type === 'sports_league' ? 'selected' : '' }}>Sports League / Org</option>
                    <option value="independent_filmmaker" {{ $type === 'independent_filmmaker' ? 'selected' : '' }}>Independent Creator</option>
                    <option value="distributor" {{ $type === 'distributor' ? 'selected' : '' }}>Content Distributor</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                    Filter CRM
                </button>
            </div>
        </form>
    </div>

    <!-- Partners Table -->
    <div class="rounded-xl border border-gray-800 bg-[#0d1017] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-[#12151c] text-gray-400 font-semibold uppercase text-[10px] tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Partner Organization</th>
                        <th class="py-3 px-4">Type & Country</th>
                        <th class="py-3 px-4">Pipeline Stage</th>
                        <th class="py-3 px-4">Agreements / Assets</th>
                        <th class="py-3 px-4">Primary Contact</th>
                        <th class="py-3 px-4 text-right">CRM Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($partners as $partner)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.partners.show', $partner->id) }}" class="font-bold text-white hover:text-red-400 transition block">
                                    {{ $partner->name }}
                                </a>
                                @if($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" class="text-[10px] text-gray-500 hover:underline">{{ $partner->website_url }}</a>
                                @endif
                            </td>

                            <td class="py-3 px-4 space-y-0.5">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono uppercase bg-gray-800 text-gray-300">
                                    {{ str_replace('_', ' ', $partner->partner_type) }}
                                </span>
                                <div class="text-[10px] text-gray-500 font-mono">{{ $partner->country_code }}</div>
                            </td>

                            <td class="py-3 px-4">
                                @php
                                    $stageColor = match($partner->pipeline_stage) {
                                        'active' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                        'agreement_signed', 'content_received' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                                        'negotiation', 'contact_made' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                        'churned' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                                        default => 'bg-gray-800 text-gray-300 border-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $stageColor }}">
                                    {{ str_replace('_', ' ', $partner->pipeline_stage) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 space-y-0.5">
                                <div class="text-[11px] text-white font-semibold">
                                    {{ $partner->agreements->count() }} Agreements
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    {{ $partner->channels_count }} Channels • {{ $partner->videos_count }} Videos
                                </div>
                            </td>

                            <td class="py-3 px-4 space-y-0.5">
                                <div class="text-white font-medium">{{ $partner->contact_name ?: 'No contact specified' }}</div>
                                <div class="text-gray-500 text-[10px]">{{ $partner->contact_email ?: $partner->contact_phone }}</div>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.partners.show', $partner->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white font-semibold text-[11px] transition inline-block">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                No content partners found matching the selected pipeline criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($partners->hasPages())
            <div class="p-4 border-t border-gray-800 bg-[#0d1017]">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
