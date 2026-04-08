<?php

declare(strict_types=1);

namespace App\Services;

final class WebhookAuditService
{
    public function record(string $channel, array $payload): void
    {
        $directory = dirname(__DIR__, 2) . '/storage';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $line = json_encode([
            'channel' => $channel,
            'recorded_at' => gmdate('c'),
            'payload' => $payload,
        ], JSON_UNESCAPED_SLASHES);

        file_put_contents($directory . '/integration-events.log', $line . PHP_EOL, FILE_APPEND);
    }
}
