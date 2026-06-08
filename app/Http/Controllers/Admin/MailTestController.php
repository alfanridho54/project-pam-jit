<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MailTestController extends Controller
{
    public function show(): View
    {
        return view('admin.mail-test', [
            'mailer' => config('mail.default'),
            'fromAddress' => config('mail.from.address'),
            'fromName' => config('mail.from.name'),
        ]);
    }

    public function send(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $recipient = $validated['recipient_email'];
        $mailer = (string) config('mail.default');
        $appName = (string) config('app.name', 'PAM JIT');
        $timestamp = now()->timezone(config('app.timezone'))->format('Y-m-d H:i:s T');

        $body = implode(PHP_EOL, [
            "{$appName} email test",
            '',
            "Application: {$appName}",
            "Timestamp: {$timestamp}",
            '',
            'This is a test email from the PAM JIT admin mail test page.',
            'It does not include SSH credentials, private keys, Proxmox tokens, or other sensitive secrets.',
            '',
            config('app.url'),
        ]);

        try {
            Mail::raw($body, function (Message $message) use ($recipient): void {
                $message
                    ->to($recipient)
                    ->subject('PAM JIT email test');
            });

            $auditLog->log(
                $request->user(),
                'mail_test_sent',
                null,
                'Admin mail test sent.',
                [
                    'recipient_email' => $recipient,
                    'mailer' => $mailer,
                ]
            );

            return back()->with('success', "Test email sent to {$recipient} using the {$mailer} mailer.");
        } catch (Throwable $exception) {
            $auditLog->log(
                $request->user(),
                'mail_test_failed',
                null,
                'Admin mail test failed.',
                [
                    'recipient_email' => $recipient,
                    'mailer' => $mailer,
                    'error_class' => $exception::class,
                ]
            );

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Test email failed. Check the mail configuration and application logs.');
        }
    }
}
