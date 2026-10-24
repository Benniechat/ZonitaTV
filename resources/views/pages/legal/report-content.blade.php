@extends('layouts.app')

@section('title', 'Report Content or Submit Takedown Notice - Zonita TV')
@section('meta_description', 'Official portal to submit a content complaint, copyright infringement notice, or takedown request on Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-8 mb-8 shadow-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 mb-3">
                Designated Reporting Portal
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Submit Content Complaint or Takedown Notice</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-2 leading-relaxed">
                Use this form to notify our compliance team of potentially unauthorised content, copyright infringements, or broadcast rights violations. Every submission receives a tracking reference number and is logged in our compliance audit register.
            </p>
        </div>

        @if(session('complaint_success'))
            <!-- Success Confirmation Screen -->
            <div class="bg-emerald-950/30 border border-emerald-500/40 rounded-2xl p-8 mb-8 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Notice Successfully Registered</h2>
                        <p class="text-xs text-gray-300 mt-1">Your notice has been formally recorded and queued for priority compliance investigation.</p>

                        <div class="bg-[#0b0d13] border border-emerald-500/30 rounded-xl p-4 my-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="text-[11px] text-gray-400 uppercase tracking-wider block">Complaint Reference Number</span>
                                <span class="text-xl font-black text-amber-400 tracking-wider font-mono">{{ session('reference_number') }}</span>
                            </div>
                            <span class="text-xs text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-lg border border-emerald-500/20">
                                Status: Pending Initial Review
                            </span>
                        </div>

                        <div class="text-xs text-gray-400 space-y-2">
                            <p><strong>Next Steps:</strong></p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>A compliance officer will review the particulars and verify the reported URL and media identifiers.</li>
                                <li>Where urgent, the placement may be temporarily disabled from the live EPG during the review.</li>
                                <li>If confirmed unauthorised, the content will be permanently purged and blocked from automated re-discovery.</li>
                                <li>Please quote your reference number <code class="text-white">{{ session('reference_number') }}</code> in any follow-up emails to <a href="mailto:copyright@zonitatv.com" class="text-amber-400 underline">copyright@zonitatv.com</a>.</li>
                            </ul>
                        </div>

                        <div class="mt-6 pt-6 border-t border-emerald-500/20 flex gap-4">
                            <a href="{{ route('home') }}" class="text-xs font-semibold text-emerald-300 hover:text-white transition">
                                &larr; Return to Home
                            </a>
                            <a href="{{ route('legal.copyright') }}" class="text-xs font-semibold text-gray-400 hover:text-gray-200 transition">
                                Read Copyright Policy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else

            <!-- Complaint Submission Form -->
            <form action="{{ route('legal.report.submit') }}" method="POST" class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl">
                @csrf

                <!-- Complainant Details Section -->
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-gray-800 pb-2 mb-4">
                        1. Complainant Information
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="complainant_name" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Full Name or Organisation <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="complainant_name" id="complainant_name" value="{{ old('complainant_name') }}" required
                                   class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                   placeholder="e.g. Jane Doe or African Media Rights Ltd">
                            @error('complainant_name')
                                <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="complainant_email" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Official Contact Email <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="complainant_email" id="complainant_email" value="{{ old('complainant_email') }}" required
                                   class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                   placeholder="rights@yourcompany.com">
                            @error('complainant_email')
                                <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="complainant_organization" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Organisation / Company Name (Optional)
                            </label>
                            <input type="text" name="complainant_organization" id="complainant_organization" value="{{ old('complainant_organization') }}"
                                   class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                   placeholder="e.g. Broadcast Corporation">
                        </div>

                        <div>
                            <label for="relationship_to_owner" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Relationship to Rights Holder <span class="text-red-400">*</span>
                            </label>
                            <select name="relationship_to_owner" id="relationship_to_owner" required
                                    class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                                <option value="owner" {{ old('relationship_to_owner') === 'owner' ? 'selected' : '' }}>I am the copyright/trademark owner</option>
                                <option value="authorized_agent" {{ old('relationship_to_owner') === 'authorized_agent' ? 'selected' : '' }}>I am an authorised representative/agent</option>
                                <option value="legal_counsel" {{ old('relationship_to_owner') === 'legal_counsel' ? 'selected' : '' }}>I am legal counsel for the rights owner</option>
                                <option value="other" {{ old('relationship_to_owner') === 'other' ? 'selected' : '' }}>Other interested party</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Complaint Details Section -->
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-gray-800 pb-2 mb-4">
                        2. Content in Dispute
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="complaint_type" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Complaint Category <span class="text-red-400">*</span>
                            </label>
                            <select name="complaint_type" id="complaint_type" required
                                    class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-amber-500">
                                <option value="copyright" {{ old('complaint_type') === 'copyright' ? 'selected' : '' }}>Copyright Infringement (Unauthorised Video / Broadcast)</option>
                                <option value="unauthorized_broadcast" {{ old('complaint_type') === 'unauthorized_broadcast' ? 'selected' : '' }}>Unauthorised Linear Channel Re-stream</option>
                                <option value="trademark" {{ old('complaint_type') === 'trademark' ? 'selected' : '' }}>Trademark / Brand Misuse</option>
                                <option value="privacy" {{ old('complaint_type') === 'privacy' ? 'selected' : '' }}>Privacy / Personality Rights Violation</option>
                                <option value="other" {{ old('complaint_type') === 'other' ? 'selected' : '' }}>Other Legal / Regulatory Dispute</option>
                            </select>
                        </div>

                        <div>
                            <label for="copyrighted_work_description" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Description of Protected Work <span class="text-red-400">*</span>
                            </label>
                            <textarea name="copyrighted_work_description" id="copyrighted_work_description" rows="3" required
                                      class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl p-3 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                      placeholder="Identify the film, TV show, sports match, musical recording, or registered trademark (e.g. 'Live Football League Match Broadcast produced on October 20, 2026').">{{ old('copyrighted_work_description') }}</textarea>
                            @error('copyrighted_work_description')
                                <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="infringing_url" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                    Exact Zonita TV URL <span class="text-red-400">*</span>
                                </label>
                                <input type="url" name="infringing_url" id="infringing_url" value="{{ old('infringing_url', $prefillUrl) }}" required
                                       class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                       placeholder="https://zonitatv.onrender.com/channel/super-sports">
                                @error('infringing_url')
                                    <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="content_reference" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                    YouTube ID or Programme Title (Optional)
                                </label>
                                <input type="text" name="content_reference" id="content_reference" value="{{ old('content_reference', $prefillRef) }}"
                                       class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                       placeholder="e.g. dQw4w9WgXcQ or Channel Name">
                            </div>
                        </div>

                        <div>
                            <label for="infringement_details" class="block text-xs font-semibold text-gray-300 mb-1.5">
                                Explanation of Infringement / Claim Particulars <span class="text-red-400">*</span>
                            </label>
                            <textarea name="infringement_details" id="infringement_details" rows="4" required
                                      class="w-full bg-[#0b0d13] border border-gray-800 focus:border-amber-500 rounded-xl p-3 text-xs text-white placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                      placeholder="Explain why this content is unauthorised, details of territorial rights, lack of distribution licence, or evidence of copyright ownership.">{{ old('infringement_details') }}</textarea>
                            @error('infringement_details')
                                <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Declarations Section -->
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-gray-800 pb-2 mb-4">
                        3. Good-Faith Declarations
                    </h2>

                    <div class="space-y-3 bg-[#0b0d13] p-4 rounded-xl border border-gray-800 text-xs">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="good_faith_declaration" value="1" {{ old('good_faith_declaration') ? 'checked' : '' }} required
                                   class="mt-0.5 w-4 h-4 rounded bg-[#121620] border-gray-700 text-amber-500 focus:ring-0 shrink-0">
                            <span class="text-gray-300">
                                I state in good faith that the use of the copyrighted or protected material identified above is not authorised by the copyright owner, its agent, or the law. <span class="text-red-400">*</span>
                            </span>
                        </label>
                        @error('good_faith_declaration')
                            <p class="text-red-400 text-[11px] pl-7">{{ $message }}</p>
                        @enderror

                        <label class="flex items-start gap-3 cursor-pointer pt-2 border-t border-gray-800/80">
                            <input type="checkbox" name="authority_confirmation" value="1" {{ old('authority_confirmation') ? 'checked' : '' }} required
                                   class="mt-0.5 w-4 h-4 rounded bg-[#121620] border-gray-700 text-amber-500 focus:ring-0 shrink-0">
                            <span class="text-gray-300">
                                I confirm that the information in this notice is accurate and that I am the owner, or authorised to act on behalf of the owner, of the exclusive right that is allegedly infringed. <span class="text-red-400">*</span>
                            </span>
                        </label>
                        @error('authority_confirmation')
                            <p class="text-red-400 text-[11px] pl-7">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-6 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 transition shadow-lg shadow-red-600/20 uppercase tracking-wider">
                        Submit Formal Notice & Generate Reference Number
                    </button>
                    <p class="text-[11px] text-gray-500 text-center mt-3">
                        Submissions are handled strictly in accordance with our <a href="{{ route('legal.privacy') }}" class="underline text-gray-400">Privacy Policy</a>. We collect only information necessary to investigate the claim.
                    </p>
                </div>
            </form>
        @endif

    </div>
</div>
@endsection
