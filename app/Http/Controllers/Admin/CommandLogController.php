<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommandLog;
use Illuminate\View\View;

class CommandLogController extends Controller
{
    public function index(): View
    {
        $commandLogs = CommandLog::query()
            ->with(['user', 'targetServer', 'jitSession'])
            ->latest('executed_at')
            ->latest()
            ->paginate(25);

        return view('admin.command-logs.index', compact('commandLogs'));
    }
}
