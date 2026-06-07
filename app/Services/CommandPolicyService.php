<?php

namespace App\Services;

class CommandPolicyService
{
    /**
     * @return array{allowed: bool, reason: string}
     */
    public function check(string $command): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', strtolower($command)) ?? '');

        if ($normalized === '') {
            return [
                'allowed' => false,
                'reason' => 'Command is required.',
            ];
        }

        $patterns = [
            'rm -rf /',
            'rm -rf /*',
            'mkfs',
            'dd if=',
            'shutdown',
            'reboot',
            'poweroff',
            'halt',
            'passwd',
            'userdel',
            'deluser',
            'chmod 777 /',
            'chown -r',
            ':(){ :|:& };:',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return [
                    'allowed' => false,
                    'reason' => "Command blocked by policy: {$pattern}",
                ];
            }
        }

        return [
            'allowed' => true,
            'reason' => 'Command allowed.',
        ];
    }
}
