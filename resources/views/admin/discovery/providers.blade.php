@extends('admin.layout')

@section('title', 'Content Providers - Discovery Workbench')

@section('admin_content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Content Providers & Ingestion Feeds</h1>
            <p class="text-xs text-gray-400 mt-1">Manage partner channels, RSS/Atom feeds, public archives, and verified filmmakers.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.discovery.index') }}" class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold text-xs rounded-xl border border-gray-700 transition">
                &larr; Discovery Workbench
            </a>
        </div>
    </div>

    <!-- Provider Registration Form -->
    <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
        <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">Register New Content Provider</h3>
        
        <form method="POST" action="{{ route('admin.discovery.providers.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Provider Name *</label>
                <input type="text" name="name" required placeholder="e.g. Cinema Libre Studio" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Provider Type *</label>
                <select name="provider_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="youtube_channel">YouTube Channel</option>
                    <option value="internet_archive">Internet Archive</option>
                    <option value="direct_creator">Direct Independent Filmmaker</option>
                    <option value="film_distributor">Film Distributor</option>
                    <option value="public_domain_repository">Public Domain Repository</option>
                    <option value="rss_feed">RSS / Video Feed</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Default Rights Classification *</label>
                <select name="default_rights_basis" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                    <option value="official_embed_permitted">Official Embed Permitted (YouTube API)</option>
                    <option value="public_domain">Public Domain</option>
                    <option value="creative_commons">Creative Commons</option>
                    <option value="direct_licence">Direct Bilateral Licence</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Website URL</label>
                <input type="url" name="website_url" placeholder="https://..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Feed / Channel / Archive URL</label>
                <input type="url" name="source_feed_url" placeholder="https://youtube.com/@channel or RSS" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1">Contact Email</label>
                <input type="email" name="contact_email" placeholder="rights@partner.com" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
            </div>

            <div class="md:col-span-3 flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 text-xs text-gray-300 cursor-pointer">
                    <input type="checkbox" name="is_trusted" value="1" class="rounded border-gray-700 bg-gray-900 text-red-600 focus:ring-0">
                    <span>Trusted Provider (Eligible for prioritized auto-staging)</span>
                </label>

                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition">
                    + Register Provider
                </button>
            </div>
        </form>
    </div>

    <!-- Providers List -->
    <div class="rounded-xl border border-gray-800 bg-[#0d1017] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-[#12151c] text-gray-400 font-semibold uppercase text-[10px] tracking-wider border-b border-gray-800">
                    <tr>
                        <th class="py-3 px-4">Provider</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Intake Staged / Approved</th>
                        <th class="py-3 px-4">Trust Status</th>
                        <th class="py-3 px-4">Contact</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $provider->name }}</div>
                                @if($provider->website_url)
                                    <a href="{{ $provider->website_url }}" target="_blank" class="text-[10px] text-gray-500 hover:underline">{{ $provider->website_url }}</a>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-gray-800 text-gray-300 uppercase">
                                    {{ str_replace('_', ' ', $provider->provider_type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-amber-400 font-bold">{{ $provider->candidates_count }} staged</span>
                                <span class="text-gray-600">•</span>
                                <span class="text-emerald-400 font-bold">{{ $provider->approved_candidates_count }} active</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($provider->is_trusted)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Trusted Partner</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-800 text-gray-400">Standard Intake</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-400">
                                {{ $provider->contact_email ?: 'No email registered' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                No content providers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="p-4 border-t border-gray-800 bg-[#0d1017]">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
