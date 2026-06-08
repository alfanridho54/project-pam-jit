<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\TargetServer;
use App\Models\User;
use App\Notifications\AccessRequestCreatedNotification;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessRequestController extends Controller
{
    public function index(Request $request): View
    {
        $accessRequests = AccessRequest::query()
            ->with(['targetServer', 'jitSession'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('requests.index', compact('accessRequests'));
    }

    public function create(): View
    {
        $targetServers = TargetServer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('requests.create', compact('targetServers'));
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'target_server_id' => [
                'required',
                Rule::exists('target_servers', 'id')->where('is_active', true),
            ],
            'reason' => ['required', 'string', 'max:2000'],
            'duration_value' => ['required', 'integer'],
            'duration_unit' => ['required', Rule::in(['minutes', 'hours', 'days'])],
        ]);

        $durationValue = (int) $validated['duration_value'];
        $durationUnit = $validated['duration_unit'];

        $bounds = [
            'minutes' => ['min' => 5, 'max' => 120, 'multiplier' => 1],
            'hours' => ['min' => 1, 'max' => 24, 'multiplier' => 60],
            'days' => ['min' => 1, 'max' => 7, 'multiplier' => 1440],
        ];

        if ($durationValue < $bounds[$durationUnit]['min'] || $durationValue > $bounds[$durationUnit]['max']) {
            return back()
                ->withInput()
                ->withErrors([
                    'duration_value' => "The duration must be between {$bounds[$durationUnit]['min']} and {$bounds[$durationUnit]['max']} {$durationUnit}.",
                ]);
        }

        $durationMinutes = $durationValue * $bounds[$durationUnit]['multiplier'];

        $accessRequest = AccessRequest::create([
            'user_id' => $request->user()->id,
            'target_server_id' => $validated['target_server_id'],
            'reason' => $validated['reason'],
            'requested_duration_minutes' => $durationMinutes,
            'status' => AccessRequest::STATUS_PENDING,
        ]);

        $accessRequest->load(['user', 'targetServer']);

        User::query()
            ->where('role', 'admin')
            ->get()
            ->each
            ->notify(new AccessRequestCreatedNotification($accessRequest));

        $auditLog->log(
            $request->user(),
            'access_request_created',
            $accessRequest,
            "Access request created for {$accessRequest->targetServer->name}.",
            [
                'target_server_id' => $accessRequest->target_server_id,
                'duration_minutes' => $accessRequest->requested_duration_minutes,
                'duration_value' => $durationValue,
                'duration_unit' => $durationUnit,
            ]
        );

        return redirect()
            ->route('requests.show', $accessRequest)
            ->with('success', 'Access request submitted.');
    }

    public function show(Request $request, AccessRequest $accessRequest): View
    {
        abort_unless($accessRequest->user_id === $request->user()->id, 403);

        $accessRequest->load(['targetServer', 'approvedBy', 'rejectedBy', 'jitSession']);

        return view('requests.show', compact('accessRequest'));
    }
}
