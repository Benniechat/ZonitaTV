@extends('layouts.app')

@section('title', 'Advertising & Sponsorship Disclosure - Zonita TV')
@section('meta_description', 'Official advertising disclosure, sponsored content standards, affiliate link practices, and advertiser policies on Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        Commercial Transparency
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Advertising & Sponsorship Disclosure</h1>
                    <p class="text-sm text-gray-400 mt-1">Standards for commercial relationships, sponsored placements, and video player integrity</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Regulatory Framework:</span> <span class="text-amber-400 font-medium">Nigerian Consumer & Advertising Standards (ARCON)</span></p>
                </div>
            </div>

            <div class="bg-[#161b26] border border-gray-800 rounded-xl p-4 text-xs text-gray-300 leading-relaxed">
                <strong class="font-bold text-white block mb-1">Commercial Transparency Principle:</strong>
                Zonita TV believes in straightforward, transparent communication with our audience. This disclosure explains how commercial partnerships, banner advertisements, sponsored channel guides, and affiliate arrangements are managed on our platform, and clarifies the separation between our platform advertising and third-party video monetisation.
            </div>
        </div>

        <!-- Content Area -->
        <div class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-10 space-y-8 text-sm leading-relaxed">

            <section>
                <h2 class="text-base font-bold text-white mb-2">1. Monetisation & Commercial Disclosures</h2>
                <p class="mb-3">
                    To maintain our platform infrastructure, continuous EPG guide updates, and high-availability digital streaming directory, Zonita TV may accept advertising, brand sponsorships, and commercial partnerships.
                </p>
                <p>
                    These commercial integrations may include web banners, featured directory tiles, newsletter sponsorships, or affiliate recommendations.
                </p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white mb-2">2. Strict Video Player Advertising Boundary (YouTube Compliance)</h2>
                <div class="bg-[#0b0d13] p-5 rounded-xl border border-amber-500/20 mb-3">
                    <strong class="text-amber-300 block mb-1">Strict Technical Player Integrity:</strong>
                    Zonita TV does <strong class="text-white">NOT</strong> inject, overlay, alter, or insert advertisements inside third-party embedded YouTube streams.
                </div>
                <p class="mb-2">In compliance with YouTube Developer Terms of Service and applicable broadcasting principles:</p>
                <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                    <li>Any in-stream advertisements, pre-rolls, mid-rolls, or banners appearing inside an embedded video are served directly by YouTube LLC and the original creator.</li>
                    <li>Zonita TV does not intercept, block, suppress, or modify any third-party ads appearing within the YouTube player.</li>
                    <li>Zonita TV does not share in or receive revenue from YouTube in-stream advertisements unless the specific content belongs to an official Zonita TV proprietary channel.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-white mb-2">3. Identification of Sponsored Content</h2>
                <p class="mb-3">
                    In compliance with the Advertising Regulatory Council of Nigeria (ARCON) guidelines and international commercial disclosure standards:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-gray-400">
                    <li>All sponsored banner positions, paid directory features, and promotional articles will be clearly designated with labels such as <strong class="text-gray-200">"Sponsored"</strong>, <strong class="text-gray-200">"Advertisement"</strong>, or <strong class="text-gray-200">"Promoted"</strong>.</li>
                    <li>Advertisers have no editorial control over independent channel reviews, directory classifications, or objective technical stream ratings.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-white mb-2">4. Disclaimer of Endorsement</h2>
                <p class="mb-3">
                    The appearance of an advertisement, brand sponsorship, or promotional link on Zonita TV does <strong class="text-white">NOT</strong> constitute an endorsement, guarantee, or warranty by Zonita TV of the quality, safety, legality, efficacy, or truthfulness of any product, service, or commercial claim.
                </p>
                <p>
                    Users are strongly encouraged to exercise their own due diligence before entering into commercial transactions with any third-party advertiser.
                </p>
            </section>

            <section>
                <h2 class="text-base font-bold text-white mb-2">5. Third-Party Advertiser Privacy Policies</h2>
                <p>
                    Advertisers and external commercial partners whose links appear on Zonita TV operate independently and maintain their own privacy policies, cookies, and tracking practices. We advise users to review the privacy terms of any third-party merchant or service provider before providing payment or personal information.
                </p>
            </section>

            <section class="pt-4 border-t border-gray-800">
                <h2 class="text-base font-bold text-white mb-2">6. Advertising Enquiries</h2>
                <p class="text-xs text-gray-400">
                    For advertising partnerships, direct sponsorships, or compliance queries regarding commercial standards on Zonita TV, please contact our commercial desk: <a href="mailto:advertising@zonitatv.com" class="text-amber-400 underline">advertising@zonitatv.com</a>.
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
                <a href="{{ route('legal.content-rights') }}" class="text-gray-300 hover:text-amber-400 transition">Content Rights</a>
                <a href="{{ route('legal.privacy') }}" class="text-gray-300 hover:text-amber-400 transition">Privacy Policy</a>
            </div>
        </div>

    </div>
</div>
@endsection
