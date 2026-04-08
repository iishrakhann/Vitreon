<?php

use App\Core\Env;

return [
    'app' => [
        'name' => 'VITREON',
        'base_url' => Env::get('APP_BASE_URL'),
        'base_path' => Env::get('APP_BASE_PATH', ''),
        'google_maps_embed_url' => Env::get(
            'GOOGLE_MAPS_EMBED_URL',
            'https://www.google.com/maps?q=Baner%20Pune&z=11&output=embed'
        ),
    ],
    'google' => [
        'client_id' => Env::get('GOOGLE_CLIENT_ID', 'google-client-id'),
        'client_secret' => Env::get('GOOGLE_CLIENT_SECRET', 'google-client-secret'),
        'redirect_uri' => Env::get('GOOGLE_REDIRECT_URI'),
    ],
    'razorpay' => [
        'webhook_secret' => Env::get('RAZORPAY_WEBHOOK_SECRET', 'replace-with-real-secret'),
    ],
    'twilio' => [
        'sid' => Env::get('TWILIO_ACCOUNT_SID', ''),
        'auth_token' => Env::get('TWILIO_AUTH_TOKEN', ''),
        'from_number' => Env::get('TWILIO_FROM_NUMBER', ''),
    ],
    'mail' => [
        'host' => Env::get('MAIL_HOST', ''),
        'port' => Env::get('MAIL_PORT', '587'),
        'username' => Env::get('MAIL_USERNAME', ''),
        'password' => Env::get('MAIL_PASSWORD', ''),
        'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
        'from_email' => Env::get('MAIL_FROM_EMAIL', ''),
        'from_name' => Env::get('MAIL_FROM_NAME', 'VITREON'),
    ],
    'database' => [
        'host' => Env::get('DB_HOST', '127.0.0.1'),
        'port' => Env::get('DB_PORT', '3306'),
        'name' => Env::get('DB_NAME', 'pune_event_hub'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', ''),
        'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    ],
    'booking_validator' => [
        'base_url' => Env::get('BOOKING_VALIDATOR_URL', 'http://localhost:8081'),
    ],
    'razorpay_api' => [
        'key_id' => Env::get('RAZORPAY_KEY_ID', ''),
        'key_secret' => Env::get('RAZORPAY_KEY_SECRET', ''),
    ],
    'payments' => [
        'confirmation_fee' => (float) Env::get('BOOKING_CONFIRMATION_FEE', '5000'),
        'upi_id' => Env::get('BOOKING_UPI_ID', 'vitreon@upi'),
        'upi_name' => Env::get('BOOKING_UPI_NAME', 'VITREON'),
    ],
];
