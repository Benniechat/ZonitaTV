@extends('admin.layout')

@section('title', 'Content Rights & Legal Governance - Zonita TV')

@section('admin_content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Content Rights & Distribution Compliance</h1>
        <p class="text-xs text-gray-400">Manage legal permissions, contract references, territory limits, and review states.</p>
    </div>

    <div class="bg-[#12151c] border border-gray-800 rounded-2xl overflow-hidden shadow">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-900/60 text-gray-400 uppercase text-[10px] tracking-wider border-b border-gray-800">
                <tr>
                    <th class="py-3.5 px-4">Channel</th>
                    <th class="py-3.5 px-4">Rights Holder</th>
                    <th class="py-3.5 px-4">Contract Ref</th>
                    <th class="py-3.5 px-4">Permitted Scopes</th>
                    <th class="py-3.5 px-4">Expires At</th>
                    <th class="py-3.5 px-4">Current Status</th>
                    <th class="py-3.5 px-4 text-right">Review Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($rights as $right)
                    <tr class="hover:bg-gray-900/40 transition" x-data="{ editing: false }">
                        <td class="py-3 px-4 font-bold text-white">
                            {{ $right->channel->name ?? 'Channel #' . $right->channel_id }}
                        </td>
                        <td class="py-3 px-4 text-gray-300">
                            {{ $right->rights_holder_name }}
                        </td>
                        <td class="py-3 px-4 font-mono text-[10px] text-gray-400">
                            {{ $right->contract_reference ?: 'N/A' }}
                        </td>
                        <td class="py-3 px-4 space-x-1">
                            @if($right->allow_directory_listing)<span class="px-1.5 py-0.5 rounded bg-gray-800 text-[9px] text-gray-300">Listing</span>@endif
                            @if($right->allow_web_embedding)<span class="px-1.5 py-0.5 rounded bg-blue-900/40 text-[9px] text-blue-300">Embed</span>@endif
                            @if($right->allow_direct_hls)<span class="px-1.5 py-0.5 rounded bg-emerald-900/40 text-[9px] text-emerald-300">HLS</span>@endif
                        </td>
                        <td class="py-3 px-4 text-gray-400">
                            {{ $right->expires_at ? $right->expires_at->format('M d, Y') : 'Indefinite' }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $badgeColor = match($right->status) {
                                    'embedding_approved', 'redistribution_approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                    'directory_approved' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                    'expired', 'rejected' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                    default => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                };
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeColor }}">
                                {{ str_replace('_', ' ', $right->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <button @click="editing = !editing" class="text-xs text-red-400 hover:text-red-300 font-semibold">
                                Update
                            </button>

                            <!-- Inline modal / popover -->
                            <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                                <div class="bg-[#12151c] border border-gray-800 rounded-2xl p-6 max-w-lg w-full text-left space-y-4">
                                    <h3 class="text-base font-bold text-white">Review Rights: {{ $right->channel->name ?? '' }}</h3>
                                    
                                    <form action="{{ route('admin.rights.update-status', $right->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Status Decision</label>
                                            <select name="status" class="w-full bg-gray-900 text-xs text-white p-2.5 rounded-lg border border-gray-700">
                                                <option value="directory_approved" {{ $right->status === 'directory_approved' ? 'selected' : '' }}>Directory Approved</option>
                                                <option value="embedding_approved" {{ $right->status === 'embedding_approved' ? 'selected' : '' }}>Embedding Approved</option>
                                                <option value="redistribution_approved" {{ $right->status === 'redistribution_approved' ? 'selected' : '' }}>Redistribution Approved</option>
                                                <option value="restricted" {{ $right->status === 'restricted' ? 'selected' : '' }}>Restricted</option>
                                                <option value="expired" {{ $right->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                                <option value="rejected" {{ $right->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-3 gap-2 text-[11px] text-gray-300">
                                            <label class="flex items-center space-x-1">
                                                <input type="checkbox" name="allow_directory_listing" value="1" {{ $right->allow_directory_listing ? 'checked' : '' }}>
                                                <span>Listing</span>
                                            </label>
                                            <label class="flex items-center space-x-1">
                                                <input type="checkbox" name="allow_web_embedding" value="1" {{ $right->allow_web_embedding ? 'checked' : '' }}>
                                                <span>Embed</span>
                                            </label>
                                            <label class="flex items-center space-x-1">
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

                                        <div class="flex justify-end space-x-3 pt-3">
                                            <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-800 text-xs text-gray-300 rounded-lg">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-xs text-white font-bold rounded-lg">Save Review</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        {{ $rights->links() }}
    </div>

</div>

@endsection
