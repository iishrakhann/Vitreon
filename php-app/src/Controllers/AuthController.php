<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\UserRepository;
use App\Services\MailOtpService;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $redirect = trim((string) ($_GET['redirect'] ?? ''));
        if ($redirect !== '') {
            $_SESSION['post_login_redirect'] = $redirect;
        }

        $this->render('auth/login', [
            'title' => 'Login | VITREON',
            'redirect' => $_SESSION['post_login_redirect'] ?? '',
        ]);
    }

    public function showRegister(): void
    {
        $redirect = trim((string) ($_GET['redirect'] ?? ''));
        if ($redirect !== '') {
            $_SESSION['post_login_redirect'] = $redirect;
        }

        $this->render('auth/register', [
            'title' => 'Register | VITREON',
            'redirect' => $_SESSION['post_login_redirect'] ?? '',
        ]);
    }

    public function requestLoginOtp(): void
    {
        $identity = trim((string) ($_POST['identity'] ?? ''));
        $user = $identity !== '' ? (new UserRepository())->findByEmailOrPhone($identity) : null;

        if ($user === null) {
            $this->render('auth/login', [
                'title' => 'Login | VITREON',
                'error' => 'No account matched that email or phone number.',
                'identity' => $identity,
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        if ((int) ($user['is_active'] ?? 1) !== 1) {
            $this->render('auth/login', [
                'title' => 'Login | VITREON',
                'error' => 'This account is inactive. Please contact the administrator.',
                'identity' => $identity,
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        $contactValue = (string) ($user['email'] ?? '');
        if ($contactValue === '') {
            $this->render('auth/login', [
                'title' => 'Login | VITREON',
                'error' => 'That account does not have an email address available for OTP delivery.',
                'identity' => $identity,
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        $otpResult = $this->issueOtp('login', [
            'user_id' => (int) $user['id'],
            'identity' => $identity,
            'contact_value' => $contactValue,
        ]);

        $this->render('auth/verify-otp', [
            'title' => 'Verify OTP | VITREON',
            'mode' => 'login',
            'identity' => $contactValue,
            'otpChannel' => 'email',
            'demoOtp' => $otpResult['show_demo'] ? $otpResult['otp'] : '',
            'message' => $otpResult['message'],
            'resendAvailableIn' => 60,
        ]);
    }

    public function requestRegisterOtp(): void
    {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phoneNumber = trim((string) ($_POST['phone_number'] ?? ''));
        $requestedRole = strtoupper(trim((string) ($_POST['role'] ?? 'CUSTOMER')));
        $role = in_array($requestedRole, ['CUSTOMER', 'OWNER'], true) ? $requestedRole : 'CUSTOMER';

        if ($fullName === '' || $email === '' || $phoneNumber === '') {
            $this->render('auth/register', [
                'title' => 'Register | VITREON',
                'error' => 'Name, email, and phone number are all required.',
                'form' => compact('fullName', 'email', 'phoneNumber', 'role'),
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        if (preg_match('/^[6-9][0-9]{9}$/', $phoneNumber) !== 1) {
            $this->render('auth/register', [
                'title' => 'Register | VITREON',
                'error' => 'Enter a valid 10-digit Indian mobile number.',
                'form' => compact('fullName', 'email', 'phoneNumber', 'role'),
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        $userRepository = new UserRepository();
        if ($userRepository->phoneOrEmailExists($email, $phoneNumber)) {
            $this->render('auth/register', [
                'title' => 'Register | VITREON',
                'error' => 'That email or phone number is already registered.',
                'form' => compact('fullName', 'email', 'phoneNumber', 'role'),
                'redirect' => $_SESSION['post_login_redirect'] ?? '',
            ]);
            return;
        }

        $contactValue = $email;
        $otpResult = $this->issueOtp('register', [
            'full_name' => $fullName,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'role' => $role,
            'contact_value' => $contactValue,
        ]);

        $this->render('auth/verify-otp', [
            'title' => 'Verify OTP | VITREON',
            'mode' => 'register',
            'identity' => $contactValue,
            'otpChannel' => 'email',
            'demoOtp' => $otpResult['show_demo'] ? $otpResult['otp'] : '',
            'message' => $otpResult['message'],
            'resendAvailableIn' => 60,
        ]);
    }

    public function verifyOtp(): void
    {
        $code = trim((string) ($_POST['otp'] ?? ''));
        $pending = $_SESSION['pending_otp'] ?? null;

        if (!is_array($pending) || $code === '') {
            $this->redirect('/login');
        }

        $expired = (int) ($pending['expires_at'] ?? 0) < time();
        if ($expired || !hash_equals((string) ($pending['otp'] ?? ''), $code)) {
            $this->render('auth/verify-otp', [
                'title' => 'Verify OTP | VITREON',
                'mode' => (string) ($pending['mode'] ?? 'login'),
                'identity' => (string) (($pending['payload']['contact_value'] ?? $pending['payload']['identity'] ?? $pending['payload']['phone_number'] ?? '')),
                'otpChannel' => 'email',
                'demoOtp' => (string) ($pending['otp'] ?? ''),
                'error' => $expired ? 'That OTP has expired. Request a new code.' : 'The OTP you entered is incorrect.',
                'resendAvailableIn' => 60,
            ]);
            return;
        }

        $userRepository = new UserRepository();
        if (($pending['mode'] ?? 'login') === 'register') {
            $user = $userRepository->createOtpUser($pending['payload']);
        } else {
            $user = $userRepository->findByEmailOrPhone((string) ($pending['payload']['identity'] ?? '')) ?? [];
        }

        $_SESSION['user'] = [
            'id' => $user['id'] ?? null,
            'name' => $user['full_name'] ?? 'Account',
            'email' => $user['email'] ?? null,
            'phone_number' => $user['phone_number'] ?? null,
            'role' => $user['role'] ?? 'CUSTOMER',
            'is_active' => $user['is_active'] ?? 1,
        ];

        unset($_SESSION['pending_otp']);
        $redirectTo = $_SESSION['post_login_redirect'] ?? '/dashboard';
        unset($_SESSION['post_login_redirect']);
        $this->redirect((string) $redirectTo);
    }

    public function logout(): void
    {
        unset($_SESSION['user'], $_SESSION['pending_otp']);
        $this->redirect('/');
    }

    public function resendOtp(): void
    {
        $pending = $_SESSION['pending_otp'] ?? null;
        if (!is_array($pending) || empty($pending['payload'])) {
            $this->redirect('/login');
        }

        $otpResult = $this->issueOtp((string) ($pending['mode'] ?? 'login'), (array) $pending['payload']);
        $identity = (string) (($pending['payload']['contact_value'] ?? $pending['payload']['identity'] ?? $pending['payload']['phone_number'] ?? ''));

        $this->render('auth/verify-otp', [
            'title' => 'Verify OTP | VITREON',
            'mode' => (string) ($pending['mode'] ?? 'login'),
            'identity' => $identity,
            'otpChannel' => 'email',
            'demoOtp' => $otpResult['show_demo'] ? $otpResult['otp'] : '',
            'message' => $otpResult['message'],
            'resendAvailableIn' => 60,
        ]);
    }

    private function issueOtp(string $mode, array $payload): array
    {
        $otp = (string) random_int(100000, 999999);
        $_SESSION['pending_otp'] = [
            'mode' => $mode,
            'payload' => $payload,
            'otp' => $otp,
            'expires_at' => time() + 300,
        ];

        $delivery = (new MailOtpService())->sendOtp((string) ($payload['contact_value'] ?? ''), $otp);

        return [
            'otp' => $otp,
            'show_demo' => !$delivery['sent'],
            'message' => $delivery['sent'] ? 'OTP sent successfully.' : $delivery['message'],
        ];
    }
}
