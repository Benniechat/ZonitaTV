@extends('admin.layout')

@section('title', 'Content Rights & Legal Governance - Zoniva TV')

@section('admin_content')
<div class="space-y-6" x-data="{ showNewModal: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Content Rights & Distribution Ledger</h1>
            <p class="text-xs text-gray-400 mt-1">Manage legal classifications, bilateral licences, territorial permissions, and expiration monitors.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showNewModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Rights Agreement</span>
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Agreements</div>
            <div class="text-2xl font-black text-white mt-1">{{ number_format($metrics['total_agreements']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">All Recorded Rights</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Active & Approved</div>
            <div class="text-2xl font-black text-emerald-400 mt-1">{{ number_format($metrics['active_rights']) }}</div>
            <div class="text-[10px] text-emerald-500/80 mt-1">Authorized Playout</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pending Review</div>
            <div class="text-2xl font-black text-amber-400 mt-1">{{ number_format($metrics['pending_review']) }}</div>
            <div class="text-[10px] text-gray-500 mt-1">Awaiting Clearance</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Expiring in 7 Days</div>
            <div class="text-2xl font-black text-amber-500 mt-1">{{ number_format($metrics['expiring_soon']) }}</div>
            <div class="text-[10px] text-amber-400/80 mt-1">Action Required</div>
        </div>
        <div class="p-4 rounded-xl bg-[#12151c] border border-gray-800">
            <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Expired / Purged</div>
            <div class="text-2xl font-black text-rose-500 mt-1">{{ number_format($metrics['expired']) }}</div>
            <div class="text-[10px] text-rose-400/80 mt-1">De-listed Assets</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="p-4 rounded-xl bg-[#10131a] border border-gray-800">
        <form method="GET" action="{{ route('admin.rights.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-center">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search rights holder, contract ref, licence..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <select name="classification" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="">All Classifications</option>
                    <option value="authorized_embed" {{ $classification === 'authorized_embed' ? 'selected' : '' }}>Authorized Embed</option>
                    <option value="direct_licence" {{ $classification === 'direct_licence' ? 'selected' : '' }}>Direct Bilateral Licence</option>
                    <option value="public_domain" {{ $classification === 'public_domain' ? 'selected' : '' }}>Public Domain</option>
                    <option value="creative_commons" {{ $classification === 'creative_commons' ? 'selected' : '' }}>Creative Commons</option>
                    <option value="owned_original" {{ $classification === 'owned_original' ? 'selected' : '' }}>Owned Original</option>
                </select>
            </div>
            <div>
                <select name="status" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="">All Statuses</option>
                    <option value="embedding_approved" {{ $status === 'embedding_approved' ? 'selected' : '' }}>Embedding Approved</option>
                    <option value="directory_approved" {{ $status === 'directory_approved' ? 'selected' : '' }}>Directory Approved</option>
                    <option value="redistribution_approved" {{ $status === 'redistribution_approved' ? 'selected' : '' }}>Redistribution Approved</option>
                    <option value="pending_review" {{ $status === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="restricted" {{ $status === 'restricted' ? 'selected' : '' }}>Restricted</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-1.5 text-xs text-amber-400 cursor-pointer">
                    <input type="checkbox" name="expiring_soon" value="1" {{ $expiringSoon ? 'checked' : '' }} class="rounded border-gray-700 bg-gray-900 text-amber-500 focus:ring-0">
                    <span>Expiring (7d)</span>
                </label>
                <button type="submit" class="ml-auto px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Rights Table -->
    <div class="bg-[#12151c] border border-gray-800 rounded-xl overflow-hidden shadow">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-900/60 text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-3.5 px-4">Target Asset</th>
                        <th class="py-3.5 px-4">Rights Holder & Classification</th>
                        <th class="py-3.5 px-4">Contract / Licence</th>
                        <th class="py-3.5 px-4">Territories</th>
                        <th class="py-3.5 px-4">Expiration</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Review Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($rights as $right)
                        <tr class="hover:bg-gray-900/40 transition" x-data="{ editing: false }">
                            <!-- Target Asset -->
                            <td class="py-3 px-4 font-bold text-white">
                                @if($right->channel)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-900/30 text-blue-400 text-[9px] uppercase font-bold">Channel</span>
                                        <span class="truncate max-w-xs">{{ $right->channel->name }}</span>
                                    </div>
                                @elseif($right->video)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-900/30 text-emerald-400 text-[9px] uppercase font-bold">Video</span>
                                        <span class="truncate max-w-xs">{{ $right->video->title }}</span>
                                    </div>
                                @elseif($right->movie)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-amber-900/30 text-amber-400 text-[9px] uppercase font-bold">Movie</span>
                                        <span class="truncate max-w-xs">{{ $right->movie->title }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Network Master Agreement</span>
                                @endif
                            </td>

                            <!-- Rights Holder & Classification -->
                            <td class="py-3 px-4 space-y-0.5">
                                <div class="font-semibold text-gray-200">{{ $right->rights_holder_name }}</div>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-mono uppercase bg-gray-800 text-gray-400 border border-gray-700">
                                    {{ str_replace('_', ' ', $right->classification ?? 'authorized_embed') }}
                                </span>
                            </td>

                            <!-- Contract / Licence -->
                            <td class="py-3 px-4 space-y-0.5">
                                <div class="font-mono text-[10px] text-gray-300">{{ $right->contract_reference ?: 'REF-STANDARD' }}</div>
                                <div class="text-[9px] text-gray-500 truncate max-w-xs">{{ $right->licence_name ?: 'Standard Terms' }}</div>
                            </td>

                            <!-- Territories -->
                            <td class="py-3 px-4">
                                @if(!empty($right->permitted_territories) && is_array($right->permitted_territories))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($right->permitted_territories, 0, 3) as $code)
                                            <span class="px-1 py-0.2 rounded bg-gray-800 text-[9px] text-gray-300 font-mono">{{ $code }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[10px] text-gray-500 font-mono">GLOBAL</span>
                                @endif
                            </td>

                            <!-- Expiration -->
                            <td class="py-3 px-4">
                                @if($right->expires_at)
                                    @php
                                        $isExp = $right->isExpired();
                                        $isSoon = $right->isExpiringSoon(7);
                                    @endphp
                                    <div class="{{ $isExp ? 'text-rose-400 font-bold' : ($isSoon ? 'text-amber-400 font-bold' : 'text-gray-300') }}">
                                        {{ $right->expires_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-[9px] {{ $isExp ? 'text-rose-500' : ($isSoon ? 'text-amber-500' : 'text-gray-500') }}">
                                        {{ $isExp ? 'EXPIRED' : ($isSoon ? 'Expires in ' . now()->diffInDays($right->expires_at) . 'd' : 'Active') }}
                                    </div>
                                @else
                                    <span class="text-gray-500 text-[10px]">Indefinite / Perpetual</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @php
                                    $badgeColor = match($right->status) {
                                        'embedding_approved', 'redistribution_approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                        'directory_approved' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                        'expired', 'rejected' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                                        default => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                    };
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeColor }}">
                                    {{ str_replace('_', ' ', $right->status) }}
                                </span>
                            </td>

                            <!-- Review Action -->
                            <td class="py-3 px-4 text-right">
                                <button @click="editing = !editing" class="text-xs text-red-400 hover:text-red-300 font-semibold transition">
                                    Update &rarr;
                                </button>

                                <!-- Modal Popover -->
                                <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
                                    <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 max-w-lg w-full text-left space-y-4 shadow-2xl">
                                        <h3 class="text-base font-bold text-white">Review Rights: {{ $right->rights_holder_name }}</h3>
                                        
                                        <form action="{{ route('admin.rights.update-status', $right->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-gray-400 mb-1">Status Decision</label>
                                                    <select name="status" class="w-full bg-gray-900 text-xs text-white p-2.5 rounded-lg border border-gray-700">
                                                        <option value="embedding_approved" {{ $right->status === 'embedding_approved' ? 'selected' : '' }}>Embedding Approved</option>
                                                        <option value="directory_approved" {{ $right->status === 'directory_approved' ? 'selected' : '' }}>Directory Approved</option>
                                                        <option value="redistribution_approved" {{ $right->status === 'redistribution_approved' ? 'selected' : '' }}>Redistribution Approved</option>
                                                        <option value="pending_review" {{ $right->status === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                                        <option value="restricted" {{ $right->status === 'restricted' ? 'selected' : '' }}>Restricted</option>
                                                        <option value="expired" {{ $right->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                                        <option value="rejected" {{ $right->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-400 mb-1">Classification</label>
                                                    <select name="classification" class="w-full bg-gray-900 text-xs text-white p-2.5 rounded-lg border border-gray-700">
                                                        <option value="authorized_embed" {{ $right->classification === 'authorized_embed' ? 'selected' : '' }}>Authorized Embed</option>
                                                        <option value="direct_licence" {{ $right->classification === 'direct_licence' ? 'selected' : '' }}>Direct Bilateral Licence</option>
                                                        <option value="public_domain" {{ $right->classification === 'public_domain' ? 'selected' : '' }}>Public Domain</option>
                                                        <option value="creative_commons" {{ $right->classification === 'creative_commons' ? 'selected' : '' }}>Creative Commons</option>
                                                        <option value="owned_original" {{ $right->classification === 'owned_original' ? 'selected' : '' }}>Owned Original</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2 text-[11px] text-gray-300">
                                                <label class="flex items-center space-x-1 cursor-pointer">
                                                    <input type="checkbox" name="allow_directory_listing" value="1" {{ $right->allow_directory_listing ? 'checked' : '' }}>
                                                    <span>Listing</span>
                                                </label>
                                                <label class="flex items-center space-x-1 cursor-pointer">
                                                    <input type="checkbox" name="allow_web_embedding" value="1" {{ $right->allow_web_embedding ? 'checked' : '' }}>
                                                    <span>Embed</span>
                                                </label>
                                                <label class="flex items-center space-x-1 cursor-pointer">
                                                    <input type="checkbox" name="allow_direct_hls" value="1" {{ $right->allow_direct_hls ? 'checked' : '' }}>
                                                    <span>Direct HLS</span>
                                                </label>
                                            </div>

                                            <div>
                                                <label class="block text-xs text-gray-400 mb-1">Expiration Date</label>
                                                <input type="date" name="expires_at" value="{{ $right->expires_at ? $right->expires_at->format('Y-m-d') : '' }}" class="w-full bg-gray-900 text-xs text-white p-2.5 rounded-lg border border-gray-700">
                                            </div>

                                            <div>
                                                <label class="block text-xs text-gray-400 mb-1">Reviewer Notes</label>
                                                <textarea name="reviewer_notes" rows="2" class="w-full bg-gray-900 text-xs text-white p-2 rounded-lg border border-gray-700">{{ $right->reviewer_notes }}</textarea>
                                            </div>

                                            <div class="flex justify-end space-x-3 pt-3 border-t border-gray-800">
                                                <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-800 text-xs text-gray-300 rounded-lg">Cancel</button>
                                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-xs text-white font-bold rounded-lg shadow">Save Review</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                No rights agreements found matching the query.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rights->hasPages())
            <div class="p-4 border-t border-gray-800">
                {{ $rights->links() }}
            </div>
        @endif
    </div>

    <!-- New Rights Agreement Modal -->
    <div x-show="showNewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
        <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 max-w-2xl w-full text-left space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h3 class="text-base font-bold text-white">Add Content Rights Agreement</h3>
                <button @click="showNewModal = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('admin.rights.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Rights Holder Name *</label>
                        <input type="text" name="rights_holder_name" required placeholder="e.g. Paramount / Nollywood Studio" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Contract Reference</label>
                        <input type="text" name="contract_reference" placeholder="e.g. CTR-2026-NGA-01" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Classification *</label>
                        <select name="classification" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="direct_licence">Direct Bilateral Licence</option>
                            <option value="authorized_embed">Authorized Embed</option>
                            <option value="public_domain">Public Domain</option>
                            <option value="creative_commons">Creative Commons</option>
                            <option value="owned_original">Owned Original</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Licence Name / Badge</label>
                        <input type="text" name="licence_name" placeholder="e.g. Broadcast Distribution Agreement v2" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Target Channel</label>
                        <select name="channel_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="">None / General Content</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Status Decision *</label>
                        <select name="status" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                            <option value="embedding_approved">Embedding Approved</option>
                            <option value="directory_approved">Directory Approved</option>
                            <option value="redistribution_approved">Redistribution Approved</option>
                            <option value="pending_review">Pending Review</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Effective Date *</label>
                        <input type="date" name="effective_from" value="{{ date('Y-m-d') }}" required class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Expiration Date</label>
                        <input type="date" name="expires_at" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Permitted Territories (comma-separated ISO-3 codes or GLOBAL)</label>
                        <input type="text" name="permitted_territories" placeholder="e.g. NGA, GHA, ZAF, GLOBAL" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Attribution Requirements</label>
                        <input type="text" name="attribution_requirements" placeholder="e.g. Courtesy of Nollywood Classic Studios" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2 text-xs text-gray-300">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="allow_directory_listing" value="1" checked class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Directory Listing</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="allow_web_embedding" value="1" checked class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Web Embedding</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="allow_direct_hls" value="1" class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Direct HLS</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="auto_delist_on_expiry" value="1" checked class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                        <span>Auto-delist on expiry</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
                    <button type="button" @click="showNewModal = false" class="px-4 py-2 bg-gray-800 text-xs text-gray-300 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-xs text-white font-bold rounded-lg shadow">Register Agreement</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
