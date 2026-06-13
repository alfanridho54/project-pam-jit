<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Access') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Interactive Terminal') }}
                    <span class="ml-2 inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20 align-middle">{{ __('Experimental') }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('sessions.show', $jitSession) }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    {{ __('Back to Session') }}
                </a>
                <a href="{{ route('sessions.commands.index', $jitSession) }}" class="inline-flex items-center gap-1.5 rounded-md border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                    {{ __('SSH Commands') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-4">

        {{-- Session info banner --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0">
                        <svg class="h-4.5 w-4.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 6 0m-6 0H3m16.5 0a3 3 0 0 1-3 3m3-3a3 3 0 1 0-6 0m6 0h1.5m-1.5 0a3 3 0 0 1-3-3m0 0a3 3 0 0 1 3-3m-3 3h-1.5m-9-3a3 3 0 0 1 3-3m-3 3h-1.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $jitSession->targetServer->name }}</h3>
                        <p class="text-xs font-mono text-slate-500">{{ $jitSession->targetServer->host }}:{{ $jitSession->targetServer->port }}</p>
                    </div>
                </div>
                <div id="status-bar" class="flex items-center gap-1.5">
                    <span id="status-dot" class="h-2 w-2 rounded-full bg-slate-400"></span>
                    <span id="status-text" class="text-xs font-semibold text-slate-500">{{ __('Initialising...') }}</span>
                </div>
            </div>
        </div>

        {{-- Security notice --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4">
            <div class="flex items-start gap-2">
                <svg class="h-4 w-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <div class="text-xs text-amber-800 leading-relaxed">
                    <span class="font-bold block mb-1">{{ __('Experimental Feature — Interactive Shell') }}</span>
                    {{ __('This terminal provides a full interactive SSH shell. Command policy restrictions from the SSH Command page are not enforced here. Individual commands typed in this terminal are not logged to the command audit log. Only terminal open/close events are recorded.') }}
                </div>
            </div>
        </div>

        {{-- Terminal container --}}
        <div class="rounded-xl overflow-hidden border border-slate-950 shadow-lg bg-[#0b0f19]">
            {{-- Terminal title bar --}}
            <div class="bg-slate-900 px-4 py-2.5 border-b border-slate-950/80 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="inline-block h-3 w-3 rounded-full bg-rose-500 cursor-pointer" id="btn-close-terminal" title="{{ __('Close Terminal') }}"></span>
                    <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span>
                    <span class="inline-block h-3 w-3 rounded-full bg-emerald-500"></span>
                </div>
                <span class="text-xs font-mono text-slate-500 select-none">{{ $jitSession->targetServer->name }} — PTY Terminal</span>
                <button id="btn-disconnect" type="button" class="text-xs font-semibold text-slate-500 hover:text-rose-400 transition">
                    {{ __('Disconnect') }}
                </button>
            </div>

            {{-- xterm.js terminal --}}
            <div id="terminal" style="height: 520px; padding: 4px;"></div>
        </div>

        {{-- Token expiry note --}}
        <p class="text-xs text-slate-400 text-center">
            {{ __('Terminal connection token expires at') }}
            <span class="font-mono font-semibold text-slate-500">{{ \Carbon\Carbon::parse($tokenExpiresAt)->timezone('Asia/Jakarta')->format('H:i:s') }}</span>.
            {{ __('If disconnected, return to the session page to generate a new token.') }}
        </p>
    </div>

    {{-- xterm.js via CDN --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@xterm/xterm@5.5.0/css/xterm.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@xterm/xterm@5.5.0/lib/xterm.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@xterm/addon-fit@0.10.0/lib/addon-fit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@xterm/addon-web-links@0.11.0/lib/addon-web-links.min.js"></script>

    <script>
        (function () {
            const WS_URL    = @json($wsUrl);
            const TOKEN     = @json($terminalToken);
            const SESSION_ID = @json($jitSession->id);

            // ── Status helpers ──────────────────────────────────────────────
            const statusDot  = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            function setStatus(label, color, animate) {
                statusText.textContent = label;
                statusDot.className = 'h-2 w-2 rounded-full bg-' + color + (animate ? ' animate-pulse' : '');
            }

            // ── xterm.js setup ──────────────────────────────────────────────
            const term = new Terminal({
                cursorBlink: true,
                fontSize: 14,
                fontFamily: '"JetBrains Mono", "Fira Code", Menlo, Monaco, "Courier New", monospace',
                theme: {
                    background: '#0b0f19',
                    foreground: '#e2e8f0',
                    cursor: '#60a5fa',
                    selectionBackground: '#334155',
                    black: '#1e293b',
                    red: '#f87171',
                    green: '#4ade80',
                    yellow: '#facc15',
                    blue: '#60a5fa',
                    magenta: '#c084fc',
                    cyan: '#22d3ee',
                    white: '#e2e8f0',
                },
                allowProposedApi: true,
            });

            const fitAddon = new FitAddon.FitAddon();
            const webLinksAddon = new WebLinksAddon.WebLinksAddon();

            term.loadAddon(fitAddon);
            term.loadAddon(webLinksAddon);
            term.open(document.getElementById('terminal'));
            fitAddon.fit();

            window.addEventListener('resize', () => fitAddon.fit());

            term.writeln('\x1b[1;34m PAM JIT Interactive Terminal\x1b[0m');
            term.writeln('\x1b[90m Session #' + SESSION_ID + ' — connecting...\x1b[0m');
            term.writeln('');

            setStatus('Connecting...', 'amber-500', true);

            // ── WebSocket connection ────────────────────────────────────────
            let ws;
            let connected = false;

            try {
                ws = new WebSocket(WS_URL);
                ws.binaryType = 'arraybuffer';
            } catch (e) {
                term.writeln('\x1b[1;31m Failed to create WebSocket connection.\x1b[0m');
                setStatus('Error', 'rose-500', false);
                return;
            }

            ws.onopen = function () {
                // Send auth token as first message
                ws.send(TOKEN);
                setStatus('Authenticating...', 'amber-500', true);
            };

            ws.onmessage = function (event) {
                if (event.data instanceof ArrayBuffer) {
                    // Binary frame = SSH terminal output
                    const decoder = new TextDecoder();
                    term.write(decoder.decode(event.data));
                    return;
                }

                // Text frame = control messages (JSON)
                try {
                    const msg = JSON.parse(event.data);

                    if (msg.type === 'error') {
                        term.writeln('\x1b[1;31m ' + msg.message + '\x1b[0m');
                        setStatus('Error', 'rose-500', false);
                        return;
                    }

                    if (msg.type === 'exit') {
                        term.writeln('');
                        term.writeln('\x1b[1;33m SSH session ended (exit code: ' + (msg.code ?? '?') + ')\x1b[0m');
                        setStatus('Disconnected', 'slate-500', false);
                        connected = false;
                        return;
                    }

                    if (msg.type === 'close') {
                        term.writeln('');
                        const reason = msg.reason === 'session_expired'
                            ? '\x1b[1;31m Session expired or revoked — terminal closed by server.\x1b[0m'
                            : '\x1b[1;33m Terminal closed by server.\x1b[0m';
                        term.writeln(reason);
                        setStatus('Closed', 'slate-500', false);
                        connected = false;
                        return;
                    }
                } catch (e) {
                    // Non-JSON text frame — display as-is
                    term.write(event.data);
                }
            };

            ws.onerror = function () {
                term.writeln('\x1b[1;31m WebSocket error. Is the terminal server running?\x1b[0m');
                setStatus('Error', 'rose-500', false);
            };

            ws.onclose = function (event) {
                if (connected || event.code !== 1006) {
                    term.writeln('');
                    term.writeln('\x1b[90m Connection closed (code: ' + event.code + ')\x1b[0m');
                } else {
                    term.writeln('\x1b[1;31m Could not connect to terminal server.\x1b[0m');
                    term.writeln('\x1b[90m Ensure `php artisan terminal:pty-server` is running.\x1b[0m');
                }
                setStatus('Disconnected', 'slate-500', false);
                connected = false;
            };

            // After a brief delay, mark as connected (token validation is async)
            setTimeout(function () {
                if (ws.readyState === WebSocket.OPEN) {
                    connected = true;
                    setStatus('Connected', 'emerald-500', true);
                }
            }, 800);

            // ── Keystroke → WebSocket (SSH stdin) ──────────────────────────
            term.onData(function (data) {
                if (ws.readyState === WebSocket.OPEN) {
                    ws.send(data);
                }
            });

            // Handle binary data from SSH stdout
            term.onBinary(function (data) {
                if (ws.readyState === WebSocket.OPEN) {
                    // Encode string to Uint8Array for binary send
                    const encoder = new TextEncoder();
                    ws.send(encoder.encode(data));
                }
            });

            // ── Close / Disconnect buttons ─────────────────────────────────
            document.getElementById('btn-close-terminal').addEventListener('click', function () {
                if (ws.readyState === WebSocket.OPEN) {
                    ws.close(1000, 'User closed terminal');
                }
            });

            document.getElementById('btn-disconnect').addEventListener('click', function () {
                if (ws.readyState === WebSocket.OPEN) {
                    ws.close(1000, 'User disconnected');
                }
            });

            // ── Resize handling (send new dimensions to server) ────────────
            window.addEventListener('resize', function () {
                if (ws.readyState === WebSocket.OPEN) {
                    try {
                        ws.send(JSON.stringify({
                            type: 'resize',
                            cols: term.cols,
                            rows: term.rows,
                        }));
                    } catch (e) { /* ignore */ }
                }
            });
        })();
    </script>
</x-app-layout>
