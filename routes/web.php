<?php

use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\Admin\AccessRequestController as AdminAccessRequestController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CommandLogController;
use App\Http\Controllers\Admin\JitSessionController as AdminJitSessionController;
use App\Http\Controllers\Admin\ProxmoxController;
use App\Http\Controllers\Admin\TargetServerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JitSessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionCommandController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'user'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/requests', [AccessRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [AccessRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [AccessRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{accessRequest}', [AccessRequestController::class, 'show'])->name('requests.show');

    Route::get('/sessions', [JitSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{jitSession}/commands', [SessionCommandController::class, 'index'])->name('sessions.commands.index');
    Route::post('/sessions/{jitSession}/commands', [SessionCommandController::class, 'store'])->name('sessions.commands.store');
    Route::get('/sessions/{jitSession}/sftp-profile', [JitSessionController::class, 'downloadSftpProfile'])->name('sessions.sftp-profile.download');
    Route::get('/sessions/{jitSession}', [JitSessionController::class, 'show'])->name('sessions.show');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

Route::get('/admin', [DashboardController::class, 'admin'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/access-requests', [AdminAccessRequestController::class, 'index'])->name('access-requests.index');
        Route::get('/access-requests/{accessRequest}', [AdminAccessRequestController::class, 'show'])->name('access-requests.show');
        Route::post('/access-requests/{accessRequest}/approve', [AdminAccessRequestController::class, 'approve'])->name('access-requests.approve');
        Route::post('/access-requests/{accessRequest}/reject', [AdminAccessRequestController::class, 'reject'])->name('access-requests.reject');

        Route::get('/sessions', [AdminJitSessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/{jitSession}', [AdminJitSessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{jitSession}/revoke', [AdminJitSessionController::class, 'revoke'])->name('sessions.revoke');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/command-logs', [CommandLogController::class, 'index'])->name('command-logs.index');
        Route::get('/command-logs/{commandLog}', [CommandLogController::class, 'show'])->name('command-logs.show');

        Route::get('/proxmox', [ProxmoxController::class, 'index'])->name('proxmox.index');
        Route::post('/proxmox/test', [ProxmoxController::class, 'test'])->name('proxmox.test');
        Route::get('/proxmox/vms', [ProxmoxController::class, 'vms'])->name('proxmox.vms');
        Route::post('/proxmox/vms/{vmid}/import', [ProxmoxController::class, 'import'])
            ->whereNumber('vmid')
            ->name('proxmox.vms.import');

        Route::post('/target-servers/{targetServer}/test-connection', [TargetServerController::class, 'testConnection'])
            ->name('target-servers.test-connection');

        Route::resource('target-servers', TargetServerController::class)
            ->except(['show']);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
