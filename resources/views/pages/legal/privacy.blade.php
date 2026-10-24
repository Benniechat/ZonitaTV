@extends('layouts.app')

@section('title', 'Privacy & Personal Data Protection Policy - Zonita TV')
@section('meta_description', 'Privacy policy, data collection disclosures, cookie practices, and user rights under the Nigeria Data Protection Act (NDPA) for Zonita TV.')

@section('content')
<div class="min-h-screen bg-[#0b0d13] text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <!-- Header -->
        <div class="bg-[#121620] border border-gray-800 rounded-2xl p-6 sm:p-10 mb-10 shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-800 pb-6 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Data Privacy & Trust
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-3 tracking-tight">Privacy Policy</h1>
                    <p class="text-sm text-gray-400 mt-1">Disclosures on personal data processing, cookies, third-party embeds, and statutory data rights</p>
                </div>
                <div class="text-xs text-gray-400 bg-[#161b26] p-4 rounded-xl border border-gray-800/80 space-y-1">
                    <p><span class="text-gray-500">Document Version:</span> <strong class="text-white">{{ $version }}</strong></p>
                    <p><span class="text-gray-500">Effective Date:</span> <span class="text-gray-200">October 24, 2026</span></p>
                    <p><span class="text-gray-500">Last Reviewed:</span> <span class="text-gray-200">{{ $lastUpdated }}</span></p>
                    <p><span class="text-gray-500">Governing Privacy Law:</span> <span class="text-emerald-400 font-medium">Nigeria Data Protection Act 2023 (NDPA)</span></p>
                </div>
            </div>

            <div class="bg-[#161b26] border border-gray-800 rounded-xl p-4 text-xs text-gray-300 leading-relaxed">
                <strong class="font-bold text-white block mb-1">Our Privacy Commitment:</strong>
                Zonita TV (<span class="text-amber-400">[Operating Entity: Zonita Media Ltd. / Operating Entity To Be Confirmed]</span>) respects your privacy and is committed to processing your personal data lawfully, fairly, and transparently in accordance with the <strong class="text-white">Nigeria Data Protection Act 2023 (NDPA)</strong> and international privacy standards. This policy transparently explains what data we collect, why we collect it, how it is safeguarded, and your legal rights.
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- TOC Sidebar -->
            <div class="hidden lg:block lg:col-span-1">
                <div class="sticky top-28 bg-[#121620] border border-gray-800/80 rounded-xl p-4 text-xs space-y-2">
                    <p class="font-bold text-white uppercase tracking-wider text-[11px] mb-3 text-gray-400">Sections</p>
                    <nav class="space-y-1.5">
                        <a href="#section-1" class="block text-gray-400 hover:text-amber-400 transition">1. Information We Collect</a>
                        <a href="#section-2" class="block text-gray-400 hover:text-amber-400 transition">2. How We Use Data</a>
                        <a href="#section-3" class="block text-gray-400 hover:text-amber-400 transition">3. Cookies & Storage</a>
                        <a href="#section-4" class="block text-gray-400 hover:text-amber-400 transition">4. Third-Party Embeds</a>
                        <a href="#section-5" class="block text-gray-400 hover:text-amber-400 transition">5. Service Providers</a>
                        <a href="#section-6" class="block text-gray-400 hover:text-amber-400 transition">6. Data Retention</a>
                        <a href="#section-7" class="block text-gray-400 hover:text-amber-400 transition">7. Security Measures</a>
                        <a href="#section-8" class="block text-gray-400 hover:text-amber-400 transition">8. Your Rights (NDPA)</a>
                        <a href="#section-9" class="block text-gray-400 hover:text-amber-400 transition">9. Contact DPO</a>
                    </nav>
                </div>
            </div>

            <!-- Content Articles -->
            <div class="lg:col-span-3 space-y-10 text-sm leading-relaxed">

                <!-- 1. Information Collected -->
                <section id="section-1" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">1. Information We Collect</h2>
                    
                    <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">A. Information from General Visitors:</h3>
                    <ul class="list-disc pl-5 space-y-1 text-gray-400 mb-4">
                        <li><strong class="text-gray-300">Technical Device Data:</strong> IP address, browser type, operating system, device screen dimensions, and language settings.</li>
                        <li><strong class="text-gray-300">Viewing Telemetry:</strong> Anonymized stream play requests, channel switches, playback errors, and search queries within the guide.</li>
                    </ul>

                    <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">B. Information from Registered Users:</h3>
                    <ul class="list-disc pl-5 space-y-1 text-gray-400 mb-4">
                        <li><strong class="text-gray-300">Account Credentials:</strong> Full name, email address, and a cryptographically salted bcrypt hash of your chosen password. (We never store plain-text passwords).</li>
                        <li><strong class="text-gray-300">OAuth Profile Data:</strong> If you sign in via Google OAuth, we receive your public name, verified email address, Google account ID, and profile picture URL.</li>
                        <li><strong class="text-gray-300">Preferences:</strong> Favorited channels, custom guide filters, and volume preferences.</li>
                    </ul>

                    <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2">C. Information from Complainants:</h3>
                    <p class="text-xs text-gray-400">
                        When submitting a copyright or content complaint, we collect your name, email address, organisation (if applicable), and claim particulars solely to investigate and process the dispute.
                    </p>
                </section>

                <!-- 2. Legal Grounds and How We Use Data -->
                <section id="section-2" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">2. How We Use Your Information (Legal Grounds)</h2>
                    <p class="mb-3">Under the NDPA, we process your personal data on the following lawful bases:</p>
                    <ul class="list-disc pl-5 space-y-2 text-gray-400">
                        <li><strong class="text-gray-200">Contractual Necessity:</strong> To provide account authentication, personalize your electronic guide, and save your favourite channels.</li>
                        <li><strong class="text-gray-200">Legitimate Interests:</strong> To monitor platform health, prevent automated scraping, detect broken streaming links, and secure our infrastructure.</li>
                        <li><strong class="text-gray-200">Legal Compliance:</strong> To investigate copyright complaints and comply with statutory disclosure orders under Nigerian law.</li>
                        <li><strong class="text-gray-200">Consent:</strong> Where you opt in to receive our weekly programming newsletter or non-essential communications.</li>
                    </ul>
                </section>

                <!-- 3. Cookies and Browser Storage -->
                <section id="section-3" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">3. Cookies and Local Storage Technologies</h2>
                    <p class="mb-3">
                        Zonita TV uses cookies and browser local storage strictly necessary for the technical operation of the website:
                    </p>
                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800 text-xs space-y-2 mb-3">
                        <p><strong class="text-white">zonita_session:</strong> Encrypted session identifier used to maintain your login status across page visits.</p>
                        <p><strong class="text-white">XSRF-TOKEN:</strong> Security token required to protect forms against Cross-Site Request Forgery (CSRF) attacks.</p>
                        <p><strong class="text-white">Browser LocalStorage:</strong> Client-side storage of UI preferences (such as volume level and dark mode state) without transmitting them to our servers.</p>
                    </div>
                    <p class="text-xs text-gray-400">
                        We do not sell personal data or deploy cross-site tracking cookies for behavioural data broker networks.
                    </p>
                </section>

                <!-- 4. Third-Party Video Embeds (YouTube / Google) -->
                <section id="section-4" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">4. Third-Party Video Embeds & YouTube Privacy</h2>
                    <p class="mb-3">
                        Because video streams are rendered via YouTube's embedded iframe player, interacting with an embedded player initiates a direct connection between your browser and Google LLC servers.
                    </p>
                    <p class="mb-3">
                        Google/YouTube may collect technical data (including IP address, viewing history if you are logged into a Google account, and device identifiers) in accordance with <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-amber-400 underline">Google's Privacy Policy</a>.
                    </p>
                    <p class="text-xs text-gray-400">
                        Zonita TV does not receive or store your Google browsing history or YouTube account profile passwords.
                    </p>
                </section>

                <!-- 5. Third-Party Service Providers and Data Sharing -->
                <section id="section-5" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">5. Data Sharing and Service Providers</h2>
                    <p class="mb-3">
                        We do not sell, rent, or trade your personal information. We only disclose personal data to vetted service providers bound by strict confidentiality and data protection agreements:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                        <li><strong class="text-gray-300">Cloud Infrastructure:</strong> Cloud hosting, container orchestration, and server infrastructure providers (Render Services Inc.).</li>
                        <li><strong class="text-gray-300">Database Hosting:</strong> Encrypted database management providers (Supabase Inc. / PostgreSQL).</li>
                        <li><strong class="text-gray-300">Legal & Law Enforcement:</strong> Competent judicial or law enforcement authorities in Nigeria only where compelled by valid court order or statutory obligation.</li>
                    </ul>
                </section>

                <!-- 6. Data Retention -->
                <section id="section-6" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">6. Data Retention Periods</h2>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                        <li><strong class="text-gray-300">Registered Accounts:</strong> Retained for the duration of your active account or until you request account deletion.</li>
                        <li><strong class="text-gray-300">Server & Security Logs:</strong> Automatically rotated and purged within 30 to 90 days.</li>
                        <li><strong class="text-gray-300">Copyright Complaint Records:</strong> Retained for up to 3 years to maintain statutory audit trails and enforce permanent discovery exclusions.</li>
                    </ul>
                </section>

                <!-- 7. Data Security Measures -->
                <section id="section-7" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">7. Data Security Measures</h2>
                    <p class="mb-3">
                        We implement appropriate technical and organisational security measures to protect your personal data, including:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-400">
                        <li>Full end-to-end Transport Layer Security (TLS 1.3 / HTTPS encryption) across all web endpoints.</li>
                        <li>Bcrypt password hashing with per-user cryptographic salts.</li>
                        <li>Strict role-based access controls restricting administrative databases.</li>
                        <li>Rate-limiting on authentication and complaint endpoints to mitigate brute-force and scraping attempts.</li>
                    </ul>
                </section>

                <!-- 8. Your Statutory Rights Under the NDPA -->
                <section id="section-8" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">8. Your Statutory Rights Under the NDPA</h2>
                    <p class="mb-3">Under the Nigeria Data Protection Act 2023, you enjoy statutory rights regarding your personal data:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                            <strong class="text-white block mb-1">Right of Access:</strong> Request a copy of the personal data we hold about you.
                        </div>
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                            <strong class="text-white block mb-1">Right to Rectification:</strong> Request correction of inaccurate or incomplete records.
                        </div>
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                            <strong class="text-white block mb-1">Right to Erasure ("To Be Forgotten"):</strong> Request deletion of your user account and preferences.
                        </div>
                        <div class="bg-[#0b0d13] p-3.5 rounded-xl border border-gray-800">
                            <strong class="text-white block mb-1">Right to Object:</strong> Object at any time to the processing of your data for marketing or newsletter purposes.
                        </div>
                    </div>
                </section>

                <!-- 9. Data Protection Officer & Contact -->
                <section id="section-9" class="bg-[#121620] border border-gray-800/80 rounded-2xl p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-white mb-3">9. Data Protection Contact</h2>
                    <p class="mb-4">
                        To exercise any of your data rights or raise an inquiry regarding our data handling practices, please contact our Data Protection desk:
                    </p>
                    <div class="bg-[#0b0d13] p-4 rounded-xl border border-gray-800 text-xs space-y-1.5">
                        <p><strong class="text-white">Zonita TV Data Protection Office</strong></p>
                        <p class="text-gray-400">Data Protection Email: <a href="mailto:privacy@zonitatv.com" class="text-amber-400 underline">privacy@zonitatv.com</a></p>
                        <p class="text-gray-400">General Support: <a href="mailto:support@zonitatv.com" class="text-amber-400 underline">support@zonitatv.com</a></p>
                        <p class="text-gray-500">[Registered Physical Office in Nigeria - To Be Confirmed]</p>
                    </div>
                </section>

            </div>
        </div>

        <!-- Footer Policy Links -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-400">
            <span>Related Legal Policies:</span>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('legal.disclaimer') }}" class="text-gray-300 hover:text-amber-400 transition">Legal Disclaimer</a>
                <a href="{{ route('legal.terms') }}" class="text-gray-300 hover:text-amber-400 transition">Terms of Use</a>
                <a href="{{ route('legal.copyright') }}" class="text-gray-300 hover:text-amber-400 transition">Copyright Policy</a>
                <a href="{{ route('legal.content-rights') }}" class="text-gray-300 hover:text-amber-400 transition">Content Rights</a>
                <a href="{{ route('legal.advertising') }}" class="text-gray-300 hover:text-amber-400 transition">Advertising Disclosure</a>
            </div>
        </div>

    </div>
</div>
@endsection
