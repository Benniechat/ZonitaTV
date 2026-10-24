<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LegalComplianceController extends Controller
{
    /**
     * Display the Legal Disclaimer.
     */
    public function disclaimer(): View
    {
        return view('pages.legal.disclaimer', [
            'pageTitle' => 'Legal Disclaimer & Third-Party Platform Notice',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the Terms of Use.
     */
    public function terms(): View
    {
        return view('pages.legal.terms', [
            'pageTitle' => 'Terms of Use',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the Copyright & Content Complaints Policy.
     */
    public function copyrightPolicy(): View
    {
        return view('pages.legal.copyright', [
            'pageTitle' => 'Copyright & Content Complaints Policy',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the Privacy Policy.
     */
    public function privacyPolicy(): View
    {
        return view('pages.legal.privacy', [
            'pageTitle' => 'Privacy & Personal Data Protection Policy',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the Advertising & Sponsorship Disclosure.
     */
    public function advertisingPolicy(): View
    {
        return view('pages.legal.advertising', [
            'pageTitle' => 'Advertising & Sponsorship Disclosure',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the Content Rights & Removal Policy.
     */
    public function contentRights(): View
    {
        return view('pages.legal.content-rights', [
            'pageTitle' => 'Content Rights, Verification & Removal Policy',
            'version' => '1.0 (Draft for Professional Legal Review)',
            'lastUpdated' => 'October 24, 2026',
        ]);
    }

    /**
     * Display the formal Content Complaint Submission Form.
     */
    public function reportForm(Request $request): View
    {
        return view('pages.legal.report-content', [
            'pageTitle' => 'Submit a Content Complaint or Takedown Notice',
            'prefillUrl' => $request->query('url', ''),
            'prefillRef' => $request->query('ref', ''),
        ]);
    }

    /**
     * Handle incoming copyright or content complaint submission.
     */
    public function submitReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'complainant_name' => ['required', 'string', 'max:255'],
            'complainant_email' => ['required', 'email', 'max:255'],
            'complainant_organization' => ['nullable', 'string', 'max:255'],
            'relationship_to_owner' => ['required', 'string', 'in:owner,authorized_agent,legal_counsel,other'],
            'complaint_type' => ['required', 'string', 'in:copyright,trademark,privacy,unauthorized_broadcast,other'],
            'copyrighted_work_description' => ['required', 'string', 'min:10', 'max:3000'],
            'infringing_url' => ['required', 'url', 'max:1000'],
            'content_reference' => ['nullable', 'string', 'max:255'],
            'infringement_details' => ['required', 'string', 'min:20', 'max:5000'],
            'good_faith_declaration' => ['accepted'],
            'authority_confirmation' => ['accepted'],
        ], [
            'good_faith_declaration.accepted' => 'You must declare in good faith that the disputed use is not authorised.',
            'authority_confirmation.accepted' => 'You must confirm that you have the authority to act on behalf of the owner.',
        ]);

        // Attempt to associate with an existing Video or Channel in the database
        $videoId = null;
        $channelId = null;

        if (! empty($validated['content_reference'])) {
            $ref = trim($validated['content_reference']);
            $video = Video::where('youtube_id', $ref)
                ->orWhere('title', 'like', "%{$ref}%")
                ->first();
            if ($video) {
                $videoId = $video->id;
            }

            $channel = Channel::where('slug', $ref)
                ->orWhere('name', 'like', "%{$ref}%")
                ->first();
            if ($channel) {
                $channelId = $channel->id;
            }
        }

        $referenceNumber = ContentComplaint::generateReferenceNumber();

        $complaint = ContentComplaint::create([
            'reference_number' => $referenceNumber,
            'complainant_name' => $validated['complainant_name'],
            'complainant_email' => $validated['complainant_email'],
            'complainant_organization' => $validated['complainant_organization'] ?? null,
            'relationship_to_owner' => $validated['relationship_to_owner'],
            'complaint_type' => $validated['complaint_type'],
            'copyrighted_work_description' => $validated['copyrighted_work_description'],
            'infringing_url' => $validated['infringing_url'],
            'content_reference' => $validated['content_reference'] ?? null,
            'video_id' => $videoId,
            'channel_id' => $channelId,
            'infringement_details' => $validated['infringement_details'],
            'good_faith_declaration' => true,
            'authority_confirmation' => true,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        Log::info("Content complaint filed: Reference {$referenceNumber} by {$complaint->complainant_email} against {$complaint->infringing_url}");

        return redirect()->route('legal.report')
            ->with('complaint_success', true)
            ->with('reference_number', $referenceNumber)
            ->with('success', "Your notice has been formally recorded with Reference Number {$referenceNumber}. Our compliance team will review credible submissions promptly.");
    }
}
