@extends('admin.layout')

@section('title', 'Onboard Content Partner - Zoniva TV')

@section('admin_content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Onboard Content Partner</h1>
            <p class="text-xs text-gray-400 mt-1">Register a new studio, network, broadcaster, or filmmaker into the acquisition CRM pipeline.</p>
        </div>
        <a href="{{ route('admin.partners.index') }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
            &larr; Back to Pipeline
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs space-y-1">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.partners.store') }}" class="space-y-6">
        @csrf

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">1. Organization Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Partner Organization Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Genesis Film Studios / TVC News" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Partner Type *</label>
                    <select name="partner_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="studio" {{ old('partner_type') === 'studio' ? 'selected' : '' }}>Film & Television Studio</option>
                        <option value="tv_network" {{ old('partner_type') === 'tv_network' ? 'selected' : '' }}>Television Network / Broadcaster</option>
                        <option value="religious_broadcaster" {{ old('partner_type') === 'religious_broadcaster' ? 'selected' : '' }}>Faith / Religious Broadcaster</option>
                        <option value="sports_league" {{ old('partner_type') === 'sports_league' ? 'selected' : '' }}>Sports League / Organization</option>
                        <option value="independent_filmmaker" {{ old('partner_type') === 'independent_filmmaker' ? 'selected' : '' }}>Independent Creator / Producer</option>
                        <option value="distributor" {{ old('partner_type') === 'distributor' ? 'selected' : '' }}>Content Distributor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Pipeline Stage *</label>
                    <select name="pipeline_stage" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="prospect" {{ old('pipeline_stage') === 'prospect' ? 'selected' : '' }}>1. Prospect</option>
                        <option value="contact_made" {{ old('pipeline_stage') === 'contact_made' ? 'selected' : '' }}>2. Contact Made</option>
                        <option value="negotiation" {{ old('pipeline_stage') === 'negotiation' ? 'selected' : '' }}>3. In Negotiation</option>
                        <option value="agreement_signed" {{ old('pipeline_stage') === 'agreement_signed' ? 'selected' : '' }}>4. Agreement Signed</option>
                        <option value="content_received" {{ old('pipeline_stage') === 'content_received' ? 'selected' : '' }}>5. Content Received</option>
                        <option value="active" {{ old('pipeline_stage') === 'active' ? 'selected' : '' }}>6. Active Broadcaster</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Country of Origin (ISO-3) *</label>
                    <input type="text" name="country_code" value="{{ old('country_code', 'NGA') }}" required maxlength="3" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white uppercase focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Official Website URL</label>
                    <input type="url" name="website_url" value="{{ old('website_url') }}" placeholder="https://..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">2. Contact & CRM Ownership</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Primary Contact Person</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" placeholder="Name & Title" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Contact Email Address</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="partnerships@company.com" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Contact Telephone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+234..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Assigned Acquisition Manager</label>
                    <select name="assigned_agent_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Acquisition Notes & Strategy</label>
                    <textarea name="notes" rows="3" placeholder="Key catalog strengths, terms discussed, distribution rights target..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.partners.index') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-lg transition">
                Onboard Content Partner
            </button>
        </div>
    </form>
</div>
@endsection
