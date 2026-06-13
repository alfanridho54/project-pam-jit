<?php

namespace App\Console\Commands;

use App\Services\PtyTerminalService;
use App\Services\TerminalWebSocketHandler;
use Illuminate\Console\Command;
use React\EventLoop\Loop;
use React\Socket\TcpServer;

/**
 * Artisan command that starts the interactive SSH PTY terminal WebSocket server.
 *
 * Uses ratchet/rfc6455 for RFC 6455 WebSocket protocol compliance
 * and React\ChildProcess for non-blocking SSH process management.
 *
 * This command runs as a long-lived foreground process.
 * Stop with Ctrl+C (SIGINT) or kill (SIGTERM) for graceful shutdown.
 *
 * @internal Experimental feature — authorised lab use only.
 */
class PtyTerminalServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terminal:pty-server
                            {--host=0.0.0.0 : Host address to bind the WebSocket server to}
                            {--port=8090 : Port number for the WebSocket server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Experimental] Start the interactive SSH PTY terminal WebSocket server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        $this->info("PAM JIT PTY Terminal Server");
        $this->info("Listening on ws://{$host}:{$port}");
        $this->info("Press Ctrl+C to stop.");
        $this->line('');

        $loop = Loop::get();

        $ptyService = new PtyTerminalService;
        $handler = new TerminalWebSocketHandler($ptyService, $loop);

        $server = new TcpServer("{$host}:{$port}", $loop);
        $handler->attach($server);

        // Graceful shutdown on SIGINT (Ctrl+C) and SIGTERM
        if (function_exists('pcntl_signal')) {
            $shutdown = function () use ($handler, $server, $loop) {
                $this->line('');
                $this->info('Shutting down... closing all terminal sessions.');
                $handler->shutdownAll();
                $server->close();
                $loop->stop();
            };

            pcntl_signal(SIGINT, $shutdown);
            pcntl_signal(SIGTERM, $shutdown);

            // Enable async signal dispatching
            pcntl_async_signals(true);
        }

        $loop->run();

        $this->info('Terminal server stopped.');

        return self::SUCCESS;
    }
}
