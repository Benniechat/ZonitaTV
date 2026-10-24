@extends('layouts.app')

@section('title', 'Legal Disclaimer & Platform Notice - Zonita TV')
@section('meta_description', 'Comprehensive legal disclaimer, third-party media embedding notice, copyright disclosures, and limitations of liability for Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Document Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        Official Legal Document
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Legal Disclaimer & Platform Notice</h1>
                    <p class="text-sm text-gray-400 mt-1">Platform architecture, embedding technology disclosures, and statutory limitations of responsibility</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Jurisdiction Context:</span> <span class="text-amber-400 font-medium">Federal Republic of Nigeria (Initial Scope)</span></p>
                </div>
            </div>

            <!-- Legal Review Notice Alert -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-xs text-amber-200/90 leading-relaxed">
                <strong class="font-bold text-amber-300 block mb-1">Notice Concerning Regulatory and Legal Review:</strong>
                This platform document constitutes a transparency disclosure and compliance policy. It is drafted for operating transparency and risk management. This text does not constitute formal legal advice, nor does it guarantee immunity from statutory obligations, copyright infringement proceedings, or regulatory oversight under Nigerian law (including the Nigerian Copyright Act 2022 and National Broadcasting Commission Act) or international intellectual property treaties. Operating entity identifiers and contact routes marked as placeholders remain subject to formal corporate confirmation.
            </div>
        </div>

        <!-- Document Grid (TOC + Content) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Desktop Table of Contents Sidebar -->
            <div class="hidden lg:block lg:col-span-1">
                <div class="sticky top-28 bg-[#121620] border border-gray-800/80 rounded-xl p-4 text-xs space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3 text-gray-400">Contents</p>
                    <nav class="space-y-1.5">
                        <a href="#section-1" class="block text-gray-400 hover:text-amber-400 transition">1. Platform Nature</a>
                        <a href="#section-2" class="block text-gray-400 hover:text-amber-400 transition">2. Third-Party Embedding</a>
                        <a href="#section-3" class="block text-gray-400 hover:text-amber-400 transition">3. Publisher Separation</a>
                        <a href="#section-4" class="block text-gray-400 hover:text-amber-400 transition">4. Content Availability</a>
                        <a href="#section-5" class="block text-gray-400 hover:text-amber-400 transition">5. Copyright & IP</a>
                        <a href="#section-6" class="block text-gray-400 hover:text-amber-400 transition">6. Warranties Disclaimer</a>
                        <a href="#section-7" class="block text-gray-400 hover:text-amber-400 transition">7. Limitation of Liability</a>
                        <a href="#section-8" class="block text-gray-400 hover:text-amber-400 transition">8. External Links</a>
                        <a href="#section-9" class="block text-gray-400 hover:text-amber-400 transition">9. Editorial Data</a>
                        <a href="#section-10" class="block text-gray-400 hover:text-amber-400 transition">10. Reporting Infringement</a>
                        <a href="#section-11" class="block text-gray-400 hover:text-amber-400 transition">11. Modification Rights</a>
                        <a href="#section-12" class="block text-gray-400 hover:text-amber-400 transition">12. Governing Law</a>
                    </nav>

                    <div class="pt-4 mt-4 border-t border-gray-800">
                        <a href="{{ route('legal.report') }}" class="inline-flex items-center gap-1.5 text-xs text-red-400 hover:text-red-300 font-semibold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Report Content
                        </a>
                    </div>
                </div>
            </div>

            <!-- Body Articles -->
            <div class="lg:col-span-3 space-y-10 text-sm leading-relaxed">

                <!-- 1. Platform Purpose and Architecture -->
                <section id="section-1" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">1</span>
                        Platform Purpose and Architecture
                    </h2>
                    <p class="mb-3">
                        Zonita TV (<strong class="text-white">"Zonita TV"</strong>, <strong class="text-white">"we"</strong>, <strong class="text-white">"our"</strong>, or <strong class="text-white">"the Platform"</strong>), operated by <span class="text-amber-400">[Operating Entity: Zonita Media Ltd. / Operating Entity To Be Confirmed]</span>, is a digital discovery directory and virtual continuous television platform.
                    </p>
                    <p class="mb-3">
                        The Platform is designed to help viewers discover, browse, and watch publicly accessible video broadcasts, television channels, sports commentary, news bulletins, Christian worship, educational tutorials, and lifestyle programming through an organised electronic programme guide (EPG) and scheduled virtual channel playout interface.
                    </p>
                    <p>
                        Zonita TV acts as an interactive digital index and viewer interface. Unless expressly stated otherwise with respect to verified Zonita TV original productions or contracted proprietary broadcasts, Zonita TV does not claim to own, produce, host, or act as the primary licensed broadcaster of the third-party television feeds or YouTube videos indexed on the service.
                    </p>
                </section>

                <!-- 2. Third-Party Media Embedding Technology -->
                <section id="section-2" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">2</span>
                        Third-Party Media Embedding Technology
                    </h2>
                    <p class="mb-3">
                        The majority of linear video playback on Zonita TV is provided through official third-party embedded video technology, notably the official YouTube Embedded Player API provided by Google LLC / YouTube LLC.
                    </p>
                    <p class="mb-3">
                        When you watch an embedded stream on Zonita TV:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400 mb-4">
                        <li>The video stream is transmitted directly from YouTube's content delivery servers to your client web browser or mobile device.</li>
                        <li>Zonita TV does not download, re-encode, alter, intercept, store, or create secondary proxy copies of the underlying media streams on our servers.</li>
                        <li>YouTube's native branding, watermarks, controls, captions, and publisher monetisation advertisements remain unmodified and fully functional in compliance with the YouTube Developer Terms of Service.</li>
                        <li>All viewing metrics, publisher ad impressions, and subscriber actions accrue directly to the respective content owner's YouTube channel.</li>
                    </ul>
                    <p class="text-xs text-gray-400 bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                        <strong class="text-gray-300">Important Technical Distinction:</strong> Embedding a video made publicly embeddable by its uploader on YouTube utilises platform API permissions. However, Zonita TV recognises that embedding does not automatically resolve all underlying intellectual property considerations, and we actively maintain compliance controls to review and disable content upon notification.
                    </p>
                </section>

                <!-- 3. Distinction Between Zonita TV and External Publishers -->
                <section id="section-3" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">3</span>
                        Distinction Between Zonita TV and External Publishers
                    </h2>
                    <p class="mb-3">
                        Zonita TV is strictly independent from the external creators, news agencies, sports organizations, religious bodies, and media entities whose channels appear on the platform, unless a formal commercial partnership or written licence is expressly designated.
                    </p>
                    <p>
                        The presentation of any external channel, brand, title, or video stream on Zonita TV does not constitute an endorsement, sponsorship, or editorial adoption by Zonita TV of the views, statements, religious doctrines, political opinions, or claims expressed therein. External publishers retain sole editorial discretion over their broadcasts.
                    </p>
                </section>

                <!-- 4. Limitations of Content Availability and Playback -->
                <section id="section-4" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">4</span>
                        Limitations of Content Availability and Playback
                    </h2>
                    <p class="mb-3">
                        Third-party content availability is dynamic and controlled exclusively by the respective content publisher or host platform. Consequently, Zonita TV cannot and does not guarantee that any specific channel, video, programme, or broadcast will remain available or uninterrupted.
                    </p>
                    <p class="mb-3">Content may become temporarily or permanently unplayable due to:</p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400 mb-4">
                        <li>Voluntary deletion, unlisting, or archiving by the original YouTube publisher.</li>
                        <li>Modification of embedding permissions or live stream status by the creator.</li>
                        <li>Geographical territory restrictions (geo-blocking) applied by copyright holders.</li>
                        <li>Age-gating, content warnings, or platform policy enforcement actions by YouTube.</li>
                        <li>Third-party platform service interruptions, network congestion, or maintenance.</li>
                    </ul>
                    <p>
                        Our automated health-monitoring engine regularly inspects scheduled playouts to detect failed streams and replace them with verified evergreen programming. However, transient disruptions may occur.
                    </p>
                </section>

                <!-- 5. Copyright and Intellectual Property -->
                <section id="section-5" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">5</span>
                        Copyright and Intellectual Property
                    </h2>
                    <p class="mb-3">
                        Zonita TV respects the intellectual property rights of all authors, broadcasters, musicians, filmmakers, and rights holders. All copyrights, trademarks, logos, service marks, and trade names appearing on the platform belong to their respective proprietors.
                    </p>
                    <p class="mb-3">
                        Zonita TV does not assert ownership over third-party trademarks, video footage, sound recordings, or promotional art referenced for identification and indexing purposes.
                    </p>
                    <p>
                        We operate a dedicated <a href="{{ route('legal.copyright') }}" class="text-amber-400 underline hover:text-amber-300">Copyright & Content Complaints Procedure</a>. If any copyright owner or authorised representative believes that their protected work is indexed or embedded on Zonita TV without requisite authorisation, we encourage immediate notification using our <a href="{{ route('legal.report') }}" class="text-amber-400 underline hover:text-amber-300">Content Complaint Form</a> for prompt investigation and removal.
                    </p>
                </section>

                <!-- 6. Disclaimer of Warranties -->
                <section id="section-6" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">6</span>
                        Disclaimer of Warranties
                    </h2>
                    <p class="mb-3 uppercase text-xs font-semibold tracking-wider text-gray-400">Provided "As Is" and "As Available"</p>
                    <p class="mb-3">
                        To the maximum extent permitted under applicable law, Zonita TV and all associated software services, schedules, directories, and embedded media players are provided on an <strong class="text-white">"AS IS"</strong> and <strong class="text-white">"AS AVAILABLE"</strong> basis without warranties of any kind, whether express, implied, statutory, or otherwise.
                    </p>
                    <p>
                        Without limiting the generality of the foregoing, Zonita TV expressly disclaims all warranties of merchantability, fitness for a particular purpose, non-infringement, quiet enjoyment, and continuous, error-free, virus-free, or uninterrupted operation. No oral or written advice provided by Zonita TV representatives shall create any warranty not expressly stated herein.
                    </p>
                </section>

                <!-- 7. Qualified Limitation of Liability -->
                <section id="section-7" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">7</span>
                        Qualified Limitation of Liability
                    </h2>
                    <p class="mb-3">
                        To the fullest extent permitted under applicable laws of the Federal Republic of Nigeria and other relevant jurisdictions, in no event shall Zonita TV, its owners, operators, directors, affiliates, licensors, or technical contractors be liable for any indirect, incidental, special, consequential, punitive, or exemplary damages, including but not limited to loss of profits, data loss, goodwill, interruption of business, or device malfunction arising from or in connection with:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400 mb-4">
                        <li>Your access to, use of, or inability to access or use the platform.</li>
                        <li>Any third-party conduct, statements, or content transmitted through embedded players.</li>
                        <li>Any technical failure, stream outage, or modification made by YouTube or other external media hosts.</li>
                        <li>Any unauthorized access to, alteration of, or security compromise of communications or data.</li>
                    </ul>
                    <p class="text-xs text-gray-400 bg-[#0b0d13] p-3 rounded-xl border border-gray-800">
                        Nothing in this disclaimer purports to exclude or limit liability that cannot lawfully be excluded under applicable consumer protection, data privacy, or statutory laws in the governing jurisdiction.
                    </p>
                </section>

                <!-- 8. External Links and Third-Party Services -->
                <section id="section-8" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">8</span>
                        External Links and Third-Party Services
                    </h2>
                    <p class="mb-3">
                        Zonita TV may contain hyperlinks to third-party websites, social channels (such as our official WhatsApp Community Channel), advertiser destinations, and external platform accounts.
                    </p>
                    <p>
                        Such external links are provided solely for convenience and reference. Zonita TV exercises no operational control over external websites and accepts no responsibility for their content, security, data handling practices, or privacy terms. Users access third-party services entirely at their own discretion.
                    </p>
                </section>

                <!-- 9. Editorial Metadata and Programme Schedules -->
                <section id="section-9" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">9</span>
                        Editorial Metadata and Programme Schedules
                    </h2>
                    <p class="mb-3">
                        Electronic Programme Guide (EPG) schedules, programme durations, category assignments, and descriptions displayed across Zonita TV are generated using automated scheduling heuristics, metadata provided by publishers, and public feeds.
                    </p>
                    <p>
                        While we strive to keep programming data accurate and timely, broadcast schedules, live event timings, and programme details are subject to change without notice. Viewers are advised to verify sensitive broadcast times with original event organizers.
                    </p>
                </section>

                <!-- 10. Reporting Unauthorised Content -->
                <section id="section-10" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">10</span>
                        Reporting Potentially Unauthorised Content
                    </h2>
                    <p class="mb-3">
                        Zonita TV maintains an active notice-and-review policy. We provide a streamlined online complaint mechanism that generates an immutable tracking reference number for every submission.
                    </p>
                    <p class="mb-4">
                        Upon receiving a credible complaint with sufficient particulars, our compliance team promptly reviews the placement. Where appropriate, we immediately disable the embedding or listing on Zonita TV and place the identifier on an automated discovery exclusion list to prevent re-introduction.
                    </p>
                    <a href="{{ route('legal.report') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-black bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 transition shadow-md shadow-orange-500/20">
                        Launch Content Complaint Form &rarr;
                    </a>
                </section>

                <!-- 11. Platform Rights of Removal and Modification -->
                <section id="section-11" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">11</span>
                        Platform Rights of Removal and Modification
                    </h2>
                    <p>
                        Zonita TV reserves the right, at its sole discretion and without prior notice or liability, to alter, suspend, discontinue, or remove any channel, stream, index listing, category, or platform feature at any time for legal compliance, technical maintenance, copyright protection, or editorial realignment.
                    </p>
                </section>

                <!-- 12. Governing Law and Legal Contact Placeholders -->
                <section id="section-12" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">12</span>
                        Governing Law and Official Legal Contact
                    </h2>
                    <p class="mb-4">
                        This disclaimer and all related platform policies are formulated with the laws of the <strong class="text-white">Federal Republic of Nigeria</strong> as the primary operating context, subject to applicable international treaties and conflict-of-law principles.
                    </p>
                    <div class="bg-[#0b0d13] p-5 rounded-xl border border-gray-800 space-y-2 text-xs">
                        <p class="font-bold text-white text-sm mb-2">Legal Compliance & Contact Directory</p>
                        <p><span class="text-gray-500">Operating Entity:</span> <strong class="text-white">[Zonita Media Ltd. / Registered Entity Name To Be Confirmed]</strong></p>
                        <p><span class="text-gray-500">Corporate Address:</span> <span class="text-gray-300">[Registered Physical Business Address in Nigeria - To Be Confirmed]</span></p>
                        <p><span class="text-gray-500">Designated Legal Email:</span> <a href="mailto:legal@zonitatv.com" class="text-amber-400 underline">legal@zonitatv.com</a></p>
                        <p><span class="text-gray-500">Copyright Notices:</span> <a href="mailto:copyright@zonitatv.com" class="text-amber-400 underline">copyright@zonitatv.com</a></p>
                        <p><span class="text-gray-500">General Enquiries:</span> <a href="mailto:support@zonitatv.com" class="text-amber-400 underline">support@zonitatv.com</a></p>
                    </div>
                </section>

            </div>
        </div>

        <!-- Sibling Policies Navigation Footer -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-400">
            <span>Related Legal Policies:</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('legal.terms') }}" class="text-gray-300 hover:text-amber-400 transition">Terms of Use</a>
                <a href="{{ route('legal.copyright') }}" class="text-gray-300 hover:text-amber-400 transition">Copyright Policy</a>
                <a href="{{ route('legal.content-rights') }}" class="text-gray-300 hover:text-amber-400 transition">Content Rights</a>
                <a href="{{ route('legal.privacy') }}" class="text-gray-300 hover:text-amber-400 transition">Privacy Policy</a>
                <a href="{{ route('legal.advertising') }}" class="text-gray-300 hover:text-amber-400 transition">Advertising Disclosure</a>
            </div>
        </div>

    </div>
</div>
@endsection
