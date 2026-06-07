<?php

namespace App\Http\Controllers;

use App\Models\JitSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JitSessionController extends Controller
{
    public function index(Request $request): View
    {
        $jitSessions = JitSession::query()
            ->with(['accessRequest', 'targetServer'])
            ->where('user_id', $request->user()->id)
            ->latest('started_at')
            ->paginate(15);

        return view('sessions.index', compact('jitSessions'));
    }

    public function show(Request $request, JitSession $jitSession): View
    {
        abort_unless($jitSession->user_id === $request->user()->id, 403);

        $jitSession->load(['accessRequest', 'targetServer', 'revokedBy']);

        return view('sessions.show', compact('jitSession'));
    }
}
