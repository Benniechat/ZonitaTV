@extends('layouts.app')

@section('title', 'Copyright & Content Complaints Policy - Zonita TV')
@section('meta_description', 'Official Copyright, Content Complaints, Notice-and-Takedown Procedure, and Intellectual Property Policy for Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Document Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                        Intellectual Property Protection
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Copyright & Complaints Policy</h1>
                    <p class="text-sm text-gray-400 mt-1">Procedures for rights holders, authorised representatives, and content takedown notices</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Statutory Framework:</span> <span class="text-amber-400 font-medium">Nigerian Copyright Act 2022 & International Principles</span></p>
                </div>
            </div>

            <!-- Fast Action Banner -->
            <div class="bg-gradient-to-r from-red-950/40 via-red-900/20 to-transparent border border-red-500/30 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-white font-bold text-sm">Need to report potentially unauthorised content?</h3>
                    <p class="text-xs text-gray-300 mt-0.5">Use our formal online notice system to generate a traceable tracking reference number.</p>
                </div>
                <a href="{{ route('legal.report') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-500 transition shadow-lg shadow-red-600/30 shrink-0">
                    Submit Takedown Notice &rarr;
                </a>
            </div>
        </div>

        <!-- Document Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar TOC -->
            <div class="hidden lg:block lg:col-span-1">
                <div class="sticky top-28 bg-[#121620] border border-gray-800/80 rounded-xl p-4 text-xs space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3 text-gray-400">Policy Outline</p>
                    <nav class="space-y-1.5">
                        <a href="#section-1" class="block text-gray-400 hover:text-amber-400 transition">1. Our Commitment</a>
                        <a href="#section-2" class="block text-gray-400 hover:text-amber-400 transition">2. Nature of Embeds</a>
                        <a href="#section-3" class="block text-gray-400 hover:text-amber-400 transition">3. Who Can Submit</a>
                        <a href="#section-4" class="block text-gray-400 hover:text-amber-400 transition">4. Required Information</a>
                        <a href="#section-5" class="block text-gray-400 hover:text-amber-400 transition">5. Notice & Review Process</a>
                        <a href="#section-6" class="block text-gray-400 hover:text-amber-400 transition">6. Permanent Exclusion</a>
                        <a href="#section-7" class="block text-gray-400 hover:text-amber-400 transition">7. Limitations of Scope</a>
                        <a href="#section-8" class="block text-gray-400 hover:text-amber-400 transition">8. Counter-Notices</a>
                        <a href="#section-9" class="block text-gray-400 hover:text-amber-400 transition">9. Designated Agent</a>
                    </nav>
                </div>
            </div>

            <!-- Content Articles -->
            <div class="lg:col-span-3 space-y-10 text-sm leading-relaxed">

                <!-- 1. Commitment to Intellectual Property -->
                <section id="section-1" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">1. Commitment to Intellectual Property Rights</h2>
                    <p class="mb-3">
                        Zonita TV (<strong class="text-white">"we"</strong>, <strong class="text-white">"our"</strong>, or <strong class="text-white">"the Platform"</strong>) operates in compliance with applicable intellectual property legislation, including the <strong class="text-white">Nigerian Copyright Act 2022</strong> and recognised international copyright conventions.
                    </p>
                    <p>
                        We take claims of intellectual property infringement seriously. Our policy is to promptly investigate credible notifications of alleged copyright, trademark, or broadcast right infringements and take appropriate corrective action, including disabling embedded video placements and purging associated items from future broadcast schedules.
                    </p>
                </section>

                <!-- 2. Nature of YouTube Embedding & Legal Boundaries -->
                <section id="section-2" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">2. Nature of YouTube Embedding & Legal Boundaries</h2>
                    <p class="mb-3">
                        Zonita TV provides an electronic guide and virtual player interface that utilizes YouTube's official embedded player technology.
                    </p>
                    <p class="mb-3">
                        When an embedded video is displayed on Zonita TV:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400 mb-4">
                        <li>The video file is hosted on YouTube servers and streams directly to the viewer's device.</li>
                        <li>Zonita TV does not host, duplicate, re-encode, or pirate video files on our own servers.</li>
                        <li>The video's embeddability is governed by the upload settings established by the original uploader on YouTube.</li>
                    </ul>
                    <p class="text-xs text-amber-200/90 bg-amber-500/10 p-3.5 rounded-xl border border-amber-500/20">
                        <strong class="text-amber-300">Important Statement:</strong> Zonita TV does not claim that the mere existence of a public embed on YouTube automatically eliminates our compliance obligations. If a video on YouTube contains material uploaded without the rights holder's consent, we do not wish to index or display it on Zonita TV. We act expeditiously upon notice to remove our links and embeds.
                    </p>
                </section>

                <!-- 3. Who Can Submit a Notice -->
                <section id="section-3" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">3. Who Can Submit a Notice</h2>
                    <p class="mb-3">A content complaint or takedown request may be submitted by:</p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                        <li>The owner of the exclusive copyright, broadcast right, or trademark.</li>
                        <li>A person or organization legally authorized to act on behalf of the rights owner.</li>
                        <li>Legal counsel representing the rights holder.</li>
                    </ul>
                </section>

                <!-- 4. Required Information for Valid Notification -->
                <section id="section-4" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">4. Information Required in a Complaint</h2>
                    <p class="mb-3">To enable our compliance team to investigate and identify the disputed content accurately, your notice should provide:</p>
                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800 space-y-2 text-xs">
                        <p><strong class="text-white">1. Complainant Identification:</strong> Your full name or organisation name, role or relationship to the owner, and valid contact email address.</p>
                        <p><strong class="text-white">2. Identification of Protected Work:</strong> Clear description of the copyrighted work, film, broadcast, music, or trademark claimed to be infringed.</p>
                        <p><strong class="text-white">3. Exact Location on Zonita TV:</strong> The specific URL on Zonita TV where the material is displayed (e.g. <code class="text-amber-400">https://zonitatv.onrender.com/channel/example-channel</code>).</p>
                        <p><strong class="text-white">4. Content Reference:</strong> The YouTube Video ID, YouTube URL, or channel title, if known.</p>
                        <p><strong class="text-white">5. Explanation of Infringement:</strong> Clear particulars explaining why the use is unauthorised.</p>
                        <p><strong class="text-white">6. Good Faith Declaration:</strong> A declaration that you believe in good faith that the disputed use is not authorised by the copyright owner, its agent, or the law.</p>
                        <p><strong class="text-white">7. Confirmation of Authority:</strong> Confirmation under penalty of false representation that the information provided is accurate and that you are authorized to act.</p>
                    </div>
                </section>

                <!-- 5. Notice and Review Procedure -->
                <section id="section-5" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">5. Notice & Review Procedure</h2>
                    <p class="mb-3">When a complaint is submitted through our system:</p>
                    <ol class="list-decimal pl-5 space-y-2 text-gray-400 mb-4">
                        <li><strong class="text-gray-200">Reference Assignment:</strong> Our automated system generates an official Reference Number (e.g. <code class="text-amber-400">ZTV-CMP-20261024-XXXX</code>) and logs the entry in our secure administrative compliance register.</li>
                        <li><strong class="text-gray-200">Urgent Interim Action:</strong> Where a credible, urgent claim of unauthorised broadcasting is identified, our administrators may immediately disable the embed or listing on Zonita TV while the investigation proceeds.</li>
                        <li><strong class="text-gray-200">Investigation:</strong> Our compliance team inspects the link, verifies rights documentation, and checks third-party platform metadata.</li>
                        <li><strong class="text-gray-200">Determination & Action:</strong> If the content is confirmed or credibly suspected to be unauthorised, the placement is permanently disabled on Zonita TV.</li>
                        <li><strong class="text-gray-200">Notification:</strong> The complainant receives an outcome update referencing their complaint ID.</li>
                    </ol>
                    <p class="text-xs text-gray-400">
                        *Note: Submitting a complaint does not automatically establish that legal infringement has occurred, but Zonita TV commits to reviewing credible complaints in good faith without unreasonable delay.
                    </p>
                </section>

                <!-- 6. Permanent Discovery Exclusion -->
                <section id="section-6" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">6. Permanent Discovery Exclusion</h2>
                    <p class="mb-3">
                        A core architectural principle of Zonita TV is that <strong class="text-white">takedowns are permanent within our systems</strong>.
                    </p>
                    <p>
                        When a video is marked as <code class="text-amber-400">disabled</code> or <code class="text-amber-400">removed</code> following a complaint, its identifier is recorded in our exclusion register. Our automated YouTube discovery engine and 24/7 channel programming scheduler are hard-coded to reject the identifier, preventing the content from ever re-entering active broadcast queues automatically.
                    </p>
                </section>

                <!-- 7. Limitations of Scope -->
                <section id="section-7" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">7. Important Limitations of Scope</h2>
                    <p class="mb-3">
                        Disabling or removing a video on Zonita TV removes the embedded player and guide listing from <strong class="text-white">our website only</strong>.
                    </p>
                    <p class="mb-3">
                        Because Zonita TV does not host the underlying video files, <strong class="text-white">taking down content on Zonita TV does not delete or remove the video from YouTube LLC's servers</strong>.
                    </p>
                    <p class="text-xs text-gray-400 bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                        To have the video removed from YouTube globally or from other websites, the copyright owner must also file a standard DMCA / Copyright Takedown Notice directly with YouTube LLC via <a href="https://www.youtube.com/copyright_complaint_form" target="_blank" rel="noopener noreferrer" class="text-amber-400 underline">YouTube's Official Copyright Takedown Webform</a>.
                    </p>
                </section>

                <!-- 8. Counter-Notification Procedure -->
                <section id="section-8" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">8. Counter-Notification Procedure</h2>
                    <p class="mb-3">
                        If an affected content partner, channel operator, or creator believes that their content was disabled on Zonita TV as a result of mistake, misidentification, or bad-faith misrepresentation, they may submit a formal counter-notification to <a href="mailto:copyright@zonitatv.com" class="text-amber-400 underline">copyright@zonitatv.com</a> citing the reference number.
                    </p>
                    <p>
                        The counter-notification must state the grounds for believing the content is authorized and include proof of ownership, a valid open licence, or appropriate documentation.
                    </p>
                </section>

                <!-- 9. Designated Copyright Agent -->
                <section id="section-9" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">9. Designated Copyright & Compliance Contact</h2>
                    <p class="mb-4">
                        Please direct all formal copyright communications, notices, and queries to our Designated Compliance Officer:
                    </p>
                    <div class="bg-[#0b0d13] p-5 rounded-xl border border-gray-800 space-y-2 text-xs">
                        <p><strong class="text-white">Zonita TV Copyright & Compliance Office</strong></p>
                        <p><span class="text-gray-500">Operating Entity:</span> <strong class="text-white">[Zonita Media Ltd. / Operating Entity To Be Confirmed]</strong></p>
                        <p><span class="text-gray-500">Designated Agent:</span> <span class="text-gray-300">Compliance & Legal Directorate</span></p>
                        <p><span class="text-gray-500">Official Notice Email:</span> <a href="mailto:copyright@zonitatv.com" class="text-amber-400 underline">copyright@zonitatv.com</a></p>
                        <p><span class="text-gray-500">Online Reporting Form:</span> <a href="{{ route('legal.report') }}" class="text-amber-400 underline font-semibold">{{ url(route('legal.report')) }}</a></p>
                        <p><span class="text-gray-500">Registered Office:</span> <span class="text-gray-400">[Physical Registered Business Address in Nigeria - To Be Confirmed]</span></p>
                    </div>
                </section>

            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-400">
            <span>Related Legal Policies:</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('legal.disclaimer') }}" class="text-gray-300 hover:text-amber-400 transition">Legal Disclaimer</a>
                <a href="{{ route('legal.terms') }}" class="text-gray-300 hover:text-amber-400 transition">Terms of Use</a>
                <a href="{{ route('legal.content-rights') }}" class="text-gray-300 hover:text-amber-400 transition">Content Rights</a>
                <a href="{{ route('legal.privacy') }}" class="text-gray-300 hover:text-amber-400 transition">Privacy Policy</a>
                <a href="{{ route('legal.advertising') }}" class="text-gray-300 hover:text-amber-400 transition">Advertising Disclosure</a>
            </div>
        </div>

    </div>
</div>
@endsection
