<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class MailOtpService
{
    public function sendOtp(string $recipientEmail, string $otpCode): array
    {
        $mailConfig = Config::get('services.mail', []);

        if ($recipientEmail === '') {
            return [
                'sent' => false,
                'message' => 'Recipient email is missing.',
            ];
        }

        if (empty($mailConfig['from_email'])) {
            return [
                'sent' => false,
                'message' => 'Mail sender is not configured. Add MAIL_FROM_EMAIL to enable PHPMailer OTP delivery.',
            ];
        }

        if (!$this->loadMailerLibrary()) {
            return [
                'sent' => false,
                'message' => 'PHPMailer is not installed. Run Composer in ' . dirname(__DIR__, 2) . ' to add the mail dependency before using email OTP delivery.',
            ];
        }

        try {
            $mailer = new PHPMailer(true);
            $host = (string) ($mailConfig['host'] ?? '');
            $username = (string) ($mailConfig['username'] ?? '');
            $password = (string) ($mailConfig['password'] ?? '');

            if ($host !== '') {
                $mailer->isSMTP();
                $mailer->Host = $host;
                $mailer->Port = (int) ($mailConfig['port'] ?? 587);
                $mailer->SMTPAuth = $username !== '';
                $mailer->Username = $username;
                $mailer->Password = $password;

                $encryption = strtolower((string) ($mailConfig['encryption'] ?? 'tls'));
                if ($encryption === 'ssl') {
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($encryption !== '' && $encryption !== 'none') {
                    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            } else {
                $mailer->isMail();
            }

            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom(
                (string) $mailConfig['from_email'],
                (string) ($mailConfig['from_name'] ?? 'VITREON')
            );
            $mailer->addAddress($recipientEmail);
            $mailer->isHTML(true);
            $mailer->Subject = 'Your VITREON OTP Code';
            $mailer->Body = sprintf(
                '<p>Your one-time password for VITREON is <strong>%s</strong>.</p><p>This code expires in 5 minutes.</p>',
                htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8')
            );
            $mailer->AltBody = sprintf(
                'Your VITREON OTP code is %s. This code expires in 5 minutes.',
                $otpCode
            );
            $mailer->send();

            return [
                'sent' => true,
                'message' => 'OTP sent successfully using PHPMailer.',
            ];
        } catch (Exception $exception) {
            return [
                'sent' => false,
                'message' => 'PHPMailer could not send the OTP: ' . $exception->getMessage(),
            ];
        }
    }

    private function loadMailerLibrary(): bool
    {
        if (class_exists(PHPMailer::class) && class_exists(Exception::class)) {
            return true;
        }

        $projectRoot = dirname(__DIR__, 2);
        $autoloadCandidates = [
            $projectRoot . '/vendor/autoload.php',
            dirname($projectRoot) . '/vendor/autoload.php',
        ];

        foreach ($autoloadCandidates as $autoloadPath) {
            if (is_file($autoloadPath)) {
                require_once $autoloadPath;
                if (class_exists(PHPMailer::class) && class_exists(Exception::class)) {
                    return true;
                }
            }
        }

        return false;
    }
}
