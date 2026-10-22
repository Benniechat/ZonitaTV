<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelStream;
use App\Models\StreamCheck;
use App\Services\StreamVerificationService;

class DiagnosticsAdminController extends Controller
{
    protected StreamVerificationService $verificationService;

    public function __construct(StreamVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function index()
    {
        $recentChecks = StreamCheck::with(['channel', 'stream'])
            ->latest('checked_at')
            ->paginate(25);

        return view('admin.diagnostics.index', compact('recentChecks'));
    }

    public function runCheck(ChannelStream $stream)
    {
        $check = $this->verificationService->verifyStream($stream);

        return back()->with('success', 'Verification complete. Status: '.($check->is_reachable ? 'Healthy' : 'Unhealthy ('.$check->error_message.')'));
    }
}
