@extends('layouts.app')

@section('title', 'Content Rights, Verification & Removal Policy - Zonita TV')
@section('meta_description', 'Operational framework for content eligibility, broadcast rights documentation, third-party embeds, and takedown enforcement on Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        Operational Compliance
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Content Rights & Removal Policy</h1>
                    <p class="text-sm text-gray-400 mt-1">Operational standards for content sourcing, rights documentation, verification, and automated exclusion</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Core Standard:</span> <span class="text-amber-400 font-medium">Software-Enforced Rights Lifecycle</span></p>
                </div>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-xs text-amber-200 leading-relaxed">
                <strong class="font-bold text-amber-300 block mb-1">Fundamental Operational Rule:</strong>
                A legal disclaimer alone does not substitute for lawful operations. Zonita TV combines transparent legal notices with rigorous, code-enforced software controls. The mere public availability of a video on YouTube or the internet does not automatically grant Zonita TV permission to reproduce, redistribute, or commercially exploit that content. This policy details how content is classified, verified, scheduled, and permanently removed when required.
            </div>
        </div>

        <!-- Content Area -->
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-10 space-y-8 text-sm leading-relaxed">

            <!-- 1. Content Categories & Sourcing Framework -->
            <section>
                <h2 class="text-base font-bold text-white mb-2">1. Content Classification & Sourcing Framework</h2>
                <p class="mb-3">Zonita TV categorizes all video assets across five distinct operational classifications:</p>
                
                <div class="space-y-3">
                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800">
                        <strong class="text-white text-xs uppercase tracking-wider block mb-1">A. Zonita Original Productions:</strong>
                        <p class="text-xs text-gray-400">Proprietary video productions, original studio shows, and podcasts created by or commissioned exclusively for Zonita TV. All worldwide broadcasting, copyright, and distribution rights are held directly by Zonita TV.</p>
                    </div>

                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800">
                        <strong class="text-white text-xs uppercase tracking-wider block mb-1">B. Contractually Licensed Content:</strong>
                        <p class="text-xs text-gray-400">Broadcast streams and programmes licensed from rights holders, content syndicators, or production houses under written agreements specifying approved territories, platforms, and validity dates.</p>
                    </div>

                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800">
                        <strong class="text-white text-xs uppercase tracking-wider block mb-1">C. Official Third-Party Broadcaster Embeds:</strong>
                        <p class="text-xs text-gray-400">Embedded streams originating from verified, official YouTube channels belonging to national broadcasters, news networks, sports leagues, and established creators who have explicitly enabled public embedding via YouTube's official player API.</p>
                    </div>

                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800">
                        <strong class="text-white text-xs uppercase tracking-wider block mb-1">D. Open Licenced & Creative Commons Works:</strong>
                        <p class="text-xs text-gray-400">Content made available under recognised Creative Commons licences (e.g. CC-BY), adhering strictly to attribution, non-commercial, and share-alike constraints.</p>
                    </div>

                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800">
                        <strong class="text-white text-xs uppercase tracking-wider block mb-1">E. Verified Public Domain Material:</strong>
                        <p class="text-xs text-gray-400">Historical broadcasts and educational works where intellectual property protection has expired or was formally dedicated to the public domain.</p>
                    </div>
                </div>
            </section>

            <!-- 2. Software-Enforced Rights Lifecycle -->
            <section>
                <h2 class="text-base font-bold text-white mb-2">2. Software-Enforced Rights Lifecycle</h2>
                <p class="mb-3">
                    Every video record in our system is subject to an automated, code-enforced lifecycle status that directly governs its eligibility for inclusion in our 24/7 channel playouts:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-amber-400 font-bold block">pending_review</code>
                        <span class="text-gray-400">Newly discovered item; not yet eligible for live broadcast scheduling.</span>
                    </div>
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-emerald-400 font-bold block">approved</code>
                        <span class="text-gray-400">Relevance, embeddability, and quality verified; active for playout.</span>
                    </div>
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-blue-400 font-bold block">licensed</code>
                        <span class="text-gray-400">Backed by signed contract and stored rights documentation.</span>
                    </div>
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-yellow-400 font-bold block">restricted</code>
                        <span class="text-gray-400">Limited by territory, broadcast window, or age guidelines.</span>
                    </div>
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-orange-400 font-bold block">complaint_received</code>
                        <span class="text-gray-400">Temporary hold applied pending compliance review.</span>
                    </div>
                    <div class="bg-[#0b0d13] p-3 rounded-lg border border-gray-800">
                        <code class="text-red-400 font-bold block">disabled / removed</code>
                        <span class="text-gray-400">Permanently purged from schedule; hard-coded discovery exclusion.</span>
                    </div>
                </div>
            </section>

            <!-- 3. Automated Discovery Restrictions -->
            <section>
                <h2 class="text-base font-bold text-white mb-2">3. Automated Discovery Restrictions</h2>
                <p class="mb-3">
                    Zonita TV's YouTube discovery engine does <strong class="text-white">NOT</strong> automatically stream search results. Candidate videos undergo a strict multi-tier pipeline:
                </p>
                <ol class="list-decimal pl-5 space-y-1.5 text-gray-400">
                    <li><strong class="text-gray-200">Candidate Discovery:</strong> Candidate metadata (title, channel, duration) is captured.</li>
                    <li><strong class="text-gray-200">Exclusion Verification:</strong> The system verifies that the video ID or publisher has not been disabled by a previous complaint.</li>
                    <li><strong class="text-gray-200">Relevance Analysis:</strong> Semantic content filtering verifies that the video aligns with the specific channel's editorial mandate.</li>
                    <li><strong class="text-gray-200">Technical Playability Check:</strong> oEmbed headers and YouTube API flags confirm embedding is actively permitted.</li>
                    <li><strong class="text-gray-200">Scheduling Eligibility:</strong> Only approved candidates are introduced into the playout schedule.</li>
                    <li><strong class="text-gray-200">Health Monitoring:</strong> The 24/7 background monitor reverifies streams every 12 hours. If a video is deleted on YouTube, it is automatically retired.</li>
                </ol>
            </section>

            <!-- 4. Immediate Administrative Takedown Controls -->
            <section>
                <h2 class="text-base font-bold text-white mb-2">4. Takedown & Exclusion Controls</h2>
                <p class="mb-3">
                    Zonita TV administrators possess dedicated compliance controls to enforce immediate takedowns:
                </p>
                <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                    <li><strong class="text-white">Immediate Live Purge:</strong> When an administrator executes a takedown, any current or future queue entries are cancelled instantly.</li>
                    <li><strong class="text-white">Permanent Blacklisting:</strong> The video identifier is transitioned to <code class="text-red-400">disabled</code> or <code class="text-red-400">removed</code>, which our discovery workers cannot re-add.</li>
                    <li><strong class="text-white">Audit Trail Logging:</strong> Every takedown records the responsible administrator's user ID, the originating complaint reference number, timestamp, and legal rationale.</li>
                </ul>
            </section>

            <!-- 5. Permission Expiry and Territory Monitoring -->
            <section>
                <h2 class="text-base font-bold text-white mb-2">5. Permission Expiry and Territorial Management</h2>
                <p class="mb-3">
                    For licensed content, Zonita TV enforces automatic expiration dates (<code class="text-amber-400">expires_at</code>). When a licence window closes, the scheduling engine automatically retires the asset from active rotation without requiring manual intervention.
                </p>
            </section>

            <!-- 6. Contacting the Rights Administration Desk -->
            <section class="pt-4 border-t border-gray-800">
                <h2 class="text-base font-bold text-white mb-2">6. Rights Partnerships & Inquiries</h2>
                <p class="text-xs text-gray-400">
                    Broadcasters, production companies, and creators interested in featuring their official channels on Zonita TV or submitting formal licensing agreements can reach our acquisitions desk at: <a href="mailto:rights@zonitatv.com" class="text-amber-400 underline">rights@zonitatv.com</a>.
                </p>
            </section>

        </div>

        <!-- Footer Links -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-400">
            <span>Related Legal Policies:</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('legal.disclaimer') }}" class="text-gray-300 hover:text-amber-400 transition">Legal Disclaimer</a>
                <a href="{{ route('legal.terms') }}" class="text-gray-300 hover:text-amber-400 transition">Terms of Use</a>
                <a href="{{ route('legal.copyright') }}" class="text-gray-300 hover:text-amber-400 transition">Copyright Policy</a>
                <a href="{{ route('legal.privacy') }}" class="text-gray-300 hover:text-amber-400 transition">Privacy Policy</a>
                <a href="{{ route('legal.advertising') }}" class="text-gray-300 hover:text-amber-400 transition">Advertising Disclosure</a>
            </div>
        </div>

    </div>
</div>
@endsection
