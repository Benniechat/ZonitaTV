<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ContentComplaint;
use App\Models\NewsletterSubscription;
use App\Models\ProblemReport;
use App\Models\SystemAlert;
use Illuminate\Support\Facades\Log;

class AlertNotificationService
{
    /**
     * Trigger an automated stream failure and dead-stream alert.
     */
    public function notifyStreamFailure(Channel $channel, string $errorCode, ?string $message = null): SystemAlert
    {
        $details = $message ? " - {$message}" : '';
        $alertMessage = "Critical Stream Outage on {$channel->name} (CH {$channel->channel_number}): Code {$errorCode}{$details}";

        // Mark channel stream degraded
        $channel->update(['is_technically_healthy' => false]);

        $alert = SystemAlert::create([
            'channel_id' => $channel->id,
            'level' => 'critical',
            'alert_type' => 'stream_failure',
            'message' => $alertMessage,
            'is_resolved' => false,
            'created_at' => now(),
        ]);

        Log::warning("[ALERT] Stream Failure: {$alertMessage}", [
            'channel_id' => $channel->id,
            'error_code' => $errorCode,
        ]);

        return $alert;
    }

    /**
     * Resolve a system alert and conditionally restore channel stream health.
     */
    public function resolveAlert(SystemAlert $alert): bool
    {
        $updated = $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        // If this was a stream failure, check if any remaining active alerts exist on channel
        if ($alert->alert_type === 'stream_failure' && $alert->channel) {
            $hasActiveAlerts = SystemAlert::where('channel_id', $alert->channel_id)
                ->where('alert_type', 'stream_failure')
                ->where('is_resolved', false)
                ->exists();

            if (! $hasActiveAlerts) {
                $alert->channel->update(['is_technically_healthy' => true]);
            }
        }

        return $updated;
    }

    /**
     * Dispatch an urgent notification when a copyright dispute or takedown is logged.
     */
    public function notifyCopyrightTakedown(ContentComplaint $complaint): SystemAlert
    {
        $channelName = $complaint->channel?->name ?? 'Channel';
        $alertMessage = "DMCA / Copyright Dispute Ticket #{$complaint->reference_number} filed for {$channelName} by {$complaint->complainant_name}";

        $alert = SystemAlert::create([
            'channel_id' => $complaint->channel_id,
            'level' => 'warning',
            'alert_type' => 'takedown_ticket',
            'message' => $alertMessage,
            'is_resolved' => false,
            'created_at' => now(),
        ]);

        Log::notice("[DMCA ALERT] {$alertMessage}", [
            'complaint_id' => $complaint->id,
            'complainant_email' => $complaint->complainant_email,
        ]);

        return $alert;
    }

    /**
     * Dispatch notification when a viewer problem report is received.
     */
    public function notifyProblemReportLogged(ProblemReport $report): SystemAlert
    {
        $channelName = $report->channel?->name ?? 'Channel';
        $issueFormatted = str_replace('_', ' ', $report->issue_type);
        $alertMessage = "Community Problem Report logged on {$channelName}: {$issueFormatted}";

        $alert = SystemAlert::create([
            'channel_id' => $report->channel_id,
            'level' => 'info',
            'alert_type' => 'user_problem_report',
            'message' => $alertMessage,
            'is_resolved' => false,
            'created_at' => now(),
        ]);

        return $alert;
    }

    /**
     * Dispatch WhatsApp Community Guide Broadcast alert.
     */
    public function dispatchWhatsAppGuideAlert(string $targetGroup = 'all', ?string $customMessage = null): array
    {
        $subscriberCount = NewsletterSubscription::where('is_active', true)->count();

        $headline = 'Zoniva TV Community Viewing Guide Alert';
        $body = $customMessage ?: 'Your personalized weekend lineup is ready! Explore continuous live Nollywood hits, comedy channels, and sports action.';
        $portalUrl = url('/my-guide');

        $fullPayload = [
            'headline' => $headline,
            'message' => $body,
            'url' => $portalUrl,
            'target_group' => $targetGroup,
            'recipients_count' => $subscriberCount,
            'dispatched_at' => now()->toIso8601String(),
        ];

        // Record dispatch in SystemAlert audit log
        SystemAlert::create([
            'channel_id' => null,
            'level' => 'info',
            'alert_type' => 'whatsapp_broadcast',
            'message' => "WhatsApp Community Guide broadcast dispatched to {$subscriberCount} subscribers ({$targetGroup})",
            'is_resolved' => true,
            'resolved_at' => now(),
            'created_at' => now(),
        ]);

        Log::info("[COMMUNITY BROADCAST] Dispatched to {$subscriberCount} recipients: {$body}");

        return [
            'success' => true,
            'recipients_count' => $subscriberCount,
            'payload' => $fullPayload,
        ];
    }
}
