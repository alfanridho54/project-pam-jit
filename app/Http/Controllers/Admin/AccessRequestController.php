<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\JitSession;
use App\Notifications\AccessRequestApprovedNotification;
use App\Notifications\AccessRequestRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessRequestController extends Controller
{
    public function index(): View
    {
        $accessRequests = AccessRequest::query()
            ->with(['user', 'targetServer'])
            ->latest()
            ->paginate(20);

        return view('admin.access-requests.index', compact('accessRequests'));
    }

    public function show(AccessRequest $accessRequest): View
    {
        $accessRequest->load(['user', 'targetServer', 'approvedBy', 'rejectedBy']);

        return view('admin.access-requests.show', compact('accessRequest'));
    }

    public function approve(Request $request, AccessRequest $accessRequest): RedirectResponse
    {
        if (! $accessRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        DB::transaction(function () use ($request, $accessRequest): void {
            $now = now();

            $accessRequest->update([
                'status' => AccessRequest::STATUS_ACTIVE,
                'approved_by' => $request->user()->id,
                'approved_at' => $now,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            $accessRequest->jitSession()->create([
                'user_id' => $accessRequest->user_id,
                'target_server_id' => $accessRequest->target_server_id,
                'started_at' => $now,
                'expires_at' => $now->copy()->addMinutes($accessRequest->requested_duration_minutes),
                'status' => JitSession::STATUS_ACTIVE,
            ]);
        });

        $accessRequest->load(['user', 'targetServer', 'jitSession']);
        $accessRequest->user->notify(new AccessRequestApprovedNotification($accessRequest));

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('success', 'Access request approved and JIT session started.');
    }

    public function reject(Request $request, AccessRequest $accessRequest): RedirectResponse
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

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('success', 'Access request rejected.');
    }
}
