<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class TwilioService
{
    public function sendOtp(string $toNumber, string $otpCode): array
    {
        $message = sprintf(
            'Your VITREON OTP is %s. It expires in 5 minutes.',
            $otpCode
        );
        $sent = $this->sendMessage($toNumber, $message);

        return [
            'sent' => $sent,
            'message' => $sent
                ? 'OTP sent to your phone using Twilio.'
                : 'Twilio SMS is not configured correctly. Showing the development OTP below for local testing.',
        ];
    }

    public function sendDepositConfirmation(string $toNumber, string $message): bool
    {
        return $this->sendMessage($toNumber, $message);
    }

    private function sendMessage(string $toNumber, string $message): bool
    {
        $sid = (string) Config::get('services.twilio.sid', '');
        $token = (string) Config::get('services.twilio.auth_token', '');
        $from = (string) Config::get('services.twilio.from_number', '');

        if ($sid === '' || $token === '' || $from === '' || $toNumber === '') {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: Basic ' . base64_encode($sid . ':' . $token),
                    'Content-Type: application/x-www-form-urlencoded',
                ]) . "\r\n",
                'content' => http_build_query([
                    'From' => $from,
                    'To' => $toNumber,
                    'Body' => $message,
                ]),
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', rawurlencode($sid));
        $response = file_get_contents($url, false, $context);

        return is_string($response) && $response !== '';
    }
}
