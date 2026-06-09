<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\AccessRequestApprovedNotification;
use App\Notifications\AccessRequestRejectedNotification;
use App\Services\AuditLogService;
use App\Services\TemporaryLinuxCredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessRequestController extends Controller
{
    public function index(): View
    {
        $accessRequests = AccessRequest::query()
            ->with(['user', 'targetServer', 'jitSession'])
            ->latest()
            ->paginate(20);

        return view('admin.access-requests.index', compact('accessRequests'));
    }

    public function show(AccessRequest $accessRequest): View
    {
        $accessRequest->load(['user', 'targetServer', 'approvedBy', 'rejectedBy', 'jitSession']);

        return view('admin.access-requests.show', compact('accessRequest'));
    }

    public function approve(
        Request $request,
        AccessRequest $accessRequest,
        AuditLogService $auditLog,
        TemporaryLinuxCredentialService $temporaryCredentials
    ): RedirectResponse {
        if (! $accessRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $temporaryCredentialsEnabled = $temporaryCredentials->enabled();
        $now = now();
        $jitSession = $accessRequest->jitSession()->create([
            'user_id' => $accessRequest->user_id,
            'target_server_id' => $accessRequest->target_server_id,
            'started_at' => $now,
            'expires_at' => $now->copy()->addMinutes($accessRequest->requested_duration_minutes),
            'status' => $temporaryCredentialsEnabled ? JitSession::STATUS_CLOSED : JitSession::STATUS_ACTIVE,
            'uses_temporary_credential' => $temporaryCredentialsEnabled,
            'temporary_credential_status' => $temporaryCredentialsEnabled
                ? JitSession::TEMPORARY_CREDENTIAL_PENDING
                : null,
        ]);

        if ($temporaryCredentialsEnabled) {
            $result = $temporaryCredentials->create($jitSession);

            if (! $result['ok']) {
                $safeError = $result['message'];

                $auditLog->log(
                    $request->user(),
                    'temporary_credential_create_failed',
                    $accessRequest,
                    "Temporary credential creation failed for access request #{$accessRequest->id}.",
                    [
                        'target_server_id' => $accessRequest->target_server_id,
                        'temporary_username' => $result['username'],
                        'error' => $safeError,
                    ]
                );

                $jitSession->delete();

                return back()->with('error', 'Temporary Linux credential creation failed: '.$safeError);
            }

            $jitSession->update([
                'temporary_username' => $result['username'],
                'temporary_password_encrypted' => $temporaryCredentials->encryptedPassword($result['password']),
                'temporary_credential_status' => JitSession::TEMPORARY_CREDENTIAL_CREATED,
                'temporary_credential_created_at' => now(),
                'temporary_credential_error' => null,
            ]);
        }

        DB::transaction(function () use ($request, $accessRequest, $jitSession): void {
            $now = now();

            $accessRequest->update([
                'status' => AccessRequest::STATUS_ACTIVE,
                'approved_by' => $request->user()->id,
                'approved_at' => $now,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            $jitSession->update([
                'status' => JitSession::STATUS_ACTIVE,
                'started_at' => $now,
                'expires_at' => $now->copy()->addMinutes($accessRequest->requested_duration_minutes),
            ]);
        });

        if ($temporaryCredentialsEnabled) {
            $auditLog->log(
                $request->user(),
                'temporary_credential_created',
                $jitSession,
                "Temporary credential created for JIT session #{$jitSession->id}.",
                [
                    'target_server_id' => $jitSession->target_server_id,
                    'temporary_username' => $jitSession->temporary_username,
                ]
            );
        }

        $jitSession->refresh();
        $accessRequest->load(['user', 'targetServer', 'jitSession']);
        $accessRequest->user->notify(new AccessRequestApprovedNotification($accessRequest));

        $auditLog->log(
            $request->user(),
            'access_request_approved',
            $accessRequest,
            "Access request #{$accessRequest->id} approved.",
            ['requester_id' => $accessRequest->user_id]
        );

        $auditLog->log(
            $request->user(),
            'jit_session_created',
            $jitSession,
            "JIT session #{$jitSession->id} created for access request #{$accessRequest->id}.",
            ['access_request_id' => $accessRequest->id, 'expires_at' => $jitSession->expires_at?->toDateTimeString()]
        );

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('success', 'Access request approved and JIT session started.');
    }

    public function reject(Request $request, AccessRequest $accessRequest, AuditLogService $auditLog): RedirectResponse
    {
        if (! $accessRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $accessRequest->update([
            'status' => AccessRequest::STATUS_REJECTED,
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $accessRequest->load(['user', 'targetServer']);
        $accessRequest->user->notify(new AccessRequestRejectedNotification($accessRequest));

        $auditLog->log(
            $request->user(),
            'access_request_rejected',
            $accessRequest,
            "Access request #{$accessRequest->id} rejected.",
            ['requester_id' => $accessRequest->user_id, 'rejection_reason' => $accessRequest->rejection_reason]
        );

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('success', 'Access request rejected.');
    }
}
