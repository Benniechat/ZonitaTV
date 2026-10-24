@extends('layouts.app')

@section('title', 'Terms of Use - Zonita TV')
@section('meta_description', 'Terms and Conditions governing user access, acceptable use, account obligations, and legal rights on Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Document Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        Binding Agreement
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Terms of Use</h1>
                    <p class="text-sm text-gray-400 mt-1">General terms and conditions governing visitor access and registered user accounts</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Jurisdiction Context:</span> <span class="text-amber-400 font-medium">Federal Republic of Nigeria</span></p>
                </div>
            </div>

            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 text-xs text-blue-200 leading-relaxed">
                <strong class="font-bold text-blue-300 block mb-1">Please Read These Terms Carefully:</strong>
                By browsing Zonita TV, registering an account, or interacting with our virtual television guide and embedded media players, you acknowledge that you have read, understood, and agreed to be bound by these Terms of Use and our associated <a href="{{ route('legal.privacy') }}" class="underline text-blue-300">Privacy Policy</a> and <a href="{{ route('legal.disclaimer') }}" class="underline text-blue-300">Legal Disclaimer</a>. If you do not agree to these terms, you must refrain from using the platform.
            </div>
        </div>

        <!-- Document Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Table of Contents Sidebar -->
            <div class="hidden lg:block lg:col-span-1">
                <div class="sticky top-28 bg-[#121620] border border-gray-800/80 rounded-xl p-4 text-xs space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3 text-gray-400">Sections</p>
                    <nav class="space-y-1.5">
                        <a href="#section-1" class="block text-gray-400 hover:text-amber-400 transition">1. Acceptance of Terms</a>
                        <a href="#section-2" class="block text-gray-400 hover:text-amber-400 transition">2. Eligibility & Access</a>
                        <a href="#section-3" class="block text-gray-400 hover:text-amber-400 transition">3. Permitted & Prohibited Use</a>
                        <a href="#section-4" class="block text-gray-400 hover:text-amber-400 transition">4. User Accounts</a>
                        <a href="#section-5" class="block text-gray-400 hover:text-amber-400 transition">5. Third-Party Embedding</a>
                        <a href="#section-6" class="block text-gray-400 hover:text-amber-400 transition">6. Intellectual Property</a>
                        <a href="#section-7" class="block text-gray-400 hover:text-amber-400 transition">7. Advertising Disclosures</a>
                        <a href="#section-8" class="block text-gray-400 hover:text-amber-400 transition">8. Suspension & Termination</a>
                        <a href="#section-9" class="block text-gray-400 hover:text-amber-400 transition">9. Service Modifications</a>
                        <a href="#section-10" class="block text-gray-400 hover:text-amber-400 transition">10. Dispute Resolution</a>
                        <a href="#section-11" class="block text-gray-400 hover:text-amber-400 transition">11. Changes to Terms</a>
                        <a href="#section-12" class="block text-gray-400 hover:text-amber-400 transition">12. Contact Information</a>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3 space-y-10 text-sm leading-relaxed">

                <!-- 1. Acceptance of Terms -->
                <section id="section-1" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">1. Acceptance of Terms</h2>
                    <p class="mb-3">
                        These Terms of Use constitute a legally binding agreement between you (<strong class="text-white">"User"</strong>, <strong class="text-white">"Viewer"</strong>, or <strong class="text-white">"you"</strong>) and <span class="text-amber-400">[Operating Entity: Zonita Media Ltd. / Operating Entity To Be Confirmed]</span> governing your access to and use of <strong class="text-white">Zonita TV</strong> (the <strong class="text-white">"Platform"</strong>).
                    </p>
                    <p>
                        Your access to and use of the platform is conditioned upon your acceptance of and compliance with these Terms. These Terms apply to all visitors, registered users, and others who access or use the service.
                    </p>
                </section>

                <!-- 2. Eligibility & Permitted Use -->
                <section id="section-2" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">2. Eligibility & Permitted Access</h2>
                    <p class="mb-3">
                        You represent and warrant that you are at least 13 years of age (or the minimum age of digital consent in your jurisdiction). If you are between 13 and 18 years of age, you may use Zonita TV only under the supervision of a parent or legal guardian who agrees to be bound by these Terms.
                    </p>
                    <p>
                        Zonita TV grants you a limited, non-exclusive, non-transferable, revocable licence to access and view public television channels, video streams, and programme guides strictly for personal, non-commercial, entertainment, and informational use in accordance with these Terms.
                    </p>
                </section>

                <!-- 3. Acceptable Use Requirements & Prohibited Conduct -->
                <section id="section-3" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">3. Acceptable Use Requirements & Prohibited Conduct</h2>
                    <p class="mb-3">You agree to use Zonita TV in good faith and in full compliance with applicable laws. You expressly agree not to:</p>
                    <ul class="list-disc pl-5 space-y-2 text-gray-400 mb-4">
                        <li><strong class="text-gray-300">Unauthorised Stream Scraping:</strong> Circumvent, disable, or tamper with the security, digital rights management (DRM), or technological protection measures of the platform, the YouTube player, or any third-party streaming endpoint.</li>
                        <li><strong class="text-gray-300">Video Downloading & Re-broadcasting:</strong> Rip, record, re-stream, redistribute, sell, sublicence, or commercially exploit any third-party broadcasts or embedded YouTube videos accessible through the platform.</li>
                        <li><strong class="text-gray-300">Automated Abuse:</strong> Use automated tools, robots, spiders, scrapers, or scripts to flood, extract data, or generate synthetic traffic on Zonita TV APIs or video players.</li>
                        <li><strong class="text-gray-300">Interference & Attacks:</strong> Introduce malware, trojans, worms, or denial-of-service (DoS) attacks designed to impair, degrade, or overburden server infrastructure.</li>
                        <li><strong class="text-gray-300">Fraudulent Reporting:</strong> Submit malicious, deceptive, fraudulent, or harassing copyright or content complaints.</li>
                    </ul>
                </section>

                <!-- 4. User Accounts and Security -->
                <section id="section-4" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">4. User Accounts and Security</h2>
                    <p class="mb-3">
                        When creating an account on Zonita TV (via email registration or Google OAuth), you must provide accurate, current, and complete information. You are solely responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
                    </p>
                    <p>
                        You must notify us immediately at <a href="mailto:support@zonitatv.com" class="text-amber-400 underline">support@zonitatv.com</a> upon becoming aware of any breach of security or unauthorised use of your account.
                    </p>
                </section>

                <!-- 5. Third-Party Services and Content -->
                <section id="section-5" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">5. Third-Party Services and Content</h2>
                    <p class="mb-3">
                        Zonita TV utilizes the YouTube Embedded Player API and may link to external broadcaster endpoints. By using Zonita TV, you also agree to be bound by the <a href="https://www.youtube.com/t/terms" target="_blank" rel="noopener noreferrer" class="text-amber-400 underline">YouTube Terms of Service</a> and acknowledge the <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-amber-400 underline">Google Privacy Policy</a>.
                    </p>
                    <p>
                        Zonita TV does not control, supervise, or assume responsibility for the continuous availability, editorial content, or legality of third-party streams hosted outside our infrastructure.
                    </p>
                </section>

                <!-- 6. Intellectual Property Rights -->
                <section id="section-6" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">6. Intellectual Property Rights</h2>
                    <p class="mb-3">
                        The Zonita TV brand, logo, website interface, layout design, software code, database architecture, and proprietary original productions are protected by intellectual property laws and remain the exclusive property of Zonita TV and its licensors.
                    </p>
                    <p>
                        All third-party trademarks, channel logos, programme titles, and video content appearing on the service are the property of their respective owners. Their reference on Zonita TV is solely for descriptive, indexing, and navigation purposes.
                    </p>
                </section>

                <!-- 7. Advertising, Sponsorship, and External Promotions -->
                <section id="section-7" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">7. Advertising, Sponsorship, and External Promotions</h2>
                    <p class="mb-3">
                        Zonita TV may display advertisements, banner sponsorships, and promotional links. Commercial relationships are governed by our <a href="{{ route('legal.advertising') }}" class="text-amber-400 underline">Advertising & Sponsorship Disclosure</a>.
                    </p>
                    <p>
                        We do not alter, inject ads into, or tamper with third-party YouTube videos. Any video-level advertisements are served directly by YouTube and the respective content creator.
                    </p>
                </section>

                <!-- 8. Account Suspension and Termination -->
                <section id="section-8" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">8. Account Suspension and Termination</h2>
                    <p class="mb-3">
                        We reserve the right, without prior notice or liability, to suspend or terminate your account or access to the platform if you breach these Terms, engage in unlawful conduct, or compromise platform integrity or security.
                    </p>
                    <p>
                        Upon termination, your right to use the platform will immediately cease. You may also request deletion of your account and personal data at any time by contacting our support team.
                    </p>
                </section>

                <!-- 9. Service Modifications and Availability -->
                <section id="section-9" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">9. Service Modifications and Availability</h2>
                    <p>
                        Zonita TV constantly evolves to improve user experience. We reserve the right to modify, replace, suspend, or discontinue any aspect of the platform (including specific channels, EPG features, or viewing categories) at any time without notice. We are not liable to you or any third party for any service modification or interruption.
                    </p>
                </section>

                <!-- 10. Dispute Resolution & Governing Law -->
                <section id="section-10" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">10. Dispute Resolution & Governing Law</h2>
                    <p class="mb-3">
                        These Terms shall be governed by and construed in accordance with the laws of the <strong class="text-white">Federal Republic of Nigeria</strong>, without regard to its conflict-of-law provisions.
                    </p>
                    <p class="mb-3">
                        In the event of any dispute, claim, or controversy arising out of or relating to these Terms or the platform, the parties agree to first seek an amicable resolution through good-faith informal negotiations by notifying <a href="mailto:legal@zonitatv.com" class="text-amber-400 underline">legal@zonitatv.com</a>.
                    </p>
                    <p>
                        If informal resolution fails within thirty (30) days, the dispute shall be submitted to the exclusive jurisdiction of the competent courts of the Federal Republic of Nigeria, subject to applicable mandatory consumer protection statutory rights.
                    </p>
                </section>

                <!-- 11. Amendments to Terms -->
                <section id="section-11" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">11. Amendments to Terms</h2>
                    <p class="mb-3">
                        We reserve the right to revise these Terms at any time. When material updates are made, we will update the "Last Reviewed" date at the top of this page and post a prominent notice on the platform.
                    </p>
                    <p>
                        Your continued access to or use of the service after any revisions become effective constitutes your acceptance of the updated Terms.
                    </p>
                </section>

                <!-- 12. Contact Information -->
                <section id="section-12" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">12. Contact & Notices</h2>
                    <p class="mb-3">For questions, legal notices, or feedback concerning these Terms of Use, please contact:</p>
                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800 text-xs space-y-1.5">
                        <p><strong class="text-white">[Zonita Media Ltd. / Operating Entity To Be Confirmed]</strong></p>
                        <p class="text-gray-400">Legal Department: <a href="mailto:legal@zonitatv.com" class="text-amber-400 underline">legal@zonitatv.com</a></p>
                        <p class="text-gray-400">General Support: <a href="mailto:support@zonitatv.com" class="text-amber-400 underline">support@zonitatv.com</a></p>
                        <p class="text-gray-500">[Physical Registered Address in Nigeria - To Be Confirmed]</p>
                    </div>
                </section>

            </div>
        </div>

        <!-- Footer Policy Links -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-400">
            <span>Related Legal Policies:</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('legal.disclaimer') }}" class="text-gray-300 hover:text-amber-400 transition">Legal Disclaimer</a>
                <a href="{{ route('legal.copyright') }}" class="text-gray-300 hover:text-amber-400 transition">Copyright Policy</a>
                <a href="{{ route('legal.content-rights') }}" class="text-gray-300 hover:text-amber-400 transition">Content Rights</a>
                <a href="{{ route('legal.privacy') }}" class="text-gray-300 hover:text-amber-400 transition">Privacy Policy</a>
                <a href="{{ route('legal.advertising') }}" class="text-gray-300 hover:text-amber-400 transition">Advertising Disclosure</a>
            </div>
        </div>

    </div>
</div>
@endsection
