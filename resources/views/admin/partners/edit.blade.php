@extends('admin.layout')

@section('title', 'Edit Content Partner - ' . $partner->name . ' - Zoniva TV')

@section('admin_content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Edit Partner: {{ $partner->name }}</h1>
            <p class="text-xs text-gray-400 mt-1">Update organization credentials, pipeline status, and contact assignments.</p>
        </div>
        <a href="{{ route('admin.partners.show', $partner->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
            &larr; Back to Partner
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

    <form method="POST" action="{{ route('admin.partners.update', $partner->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">1. Organization Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Partner Organization Name *</label>
                    <input type="text" name="name" value="{{ old('name', $partner->name) }}" required class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Partner Type *</label>
                    <select name="partner_type" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        @foreach(['studio' => 'Film & Television Studio', 'tv_network' => 'Television Network / Broadcaster', 'religious_broadcaster' => 'Faith / Religious Broadcaster', 'sports_league' => 'Sports League / Organization', 'independent_filmmaker' => 'Independent Creator / Producer', 'distributor' => 'Content Distributor'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('partner_type', $partner->partner_type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Pipeline Stage *</label>
                    <select name="pipeline_stage" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        @foreach(['prospect' => '1. Prospect', 'contact_made' => '2. Contact Made', 'negotiation' => '3. In Negotiation', 'agreement_signed' => '4. Agreement Signed', 'content_received' => '5. Content Received', 'active' => '6. Active Broadcaster', 'churned' => 'Churned / Inactive'] as $sVal => $sLbl)
                            <option value="{{ $sVal }}" {{ old('pipeline_stage', $partner->pipeline_stage) === $sVal ? 'selected' : '' }}>{{ $sLbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Country of Origin (ISO-3) *</label>
                    <input type="text" name="country_code" value="{{ old('country_code', $partner->country_code) }}" required maxlength="3" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white uppercase focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Official Website URL</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $partner->website_url) }}" placeholder="https://..." class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <div class="p-6 rounded-xl bg-[#0d1017] border border-gray-800 space-y-4">
            <h3 class="text-sm font-bold text-white border-b border-gray-800 pb-2">2. Contact & CRM Ownership</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Primary Contact Person</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $partner->contact_name) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Contact Email Address</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $partner->contact_email) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Contact Telephone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $partner->contact_phone) }}" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Assigned Acquisition Manager</label>
                    <select name="assigned_agent_id" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('assigned_agent_id', $partner->assigned_agent_id) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Acquisition Notes & Strategy</label>
                    <textarea name="notes" rows="4" class="w-full bg-[#0a0c10] border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-red-500">{{ old('notes', $partner->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.partners.show', $partner->id) }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-lg transition">
                Update Partner Profile
            </button>
        </div>
    </form>
</div>
@endsection
