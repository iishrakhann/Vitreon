<?php

use App\Controllers\AuthController;
use App\Controllers\BookingsController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PaymentController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/venues', [HomeController::class, 'venues']],
    ['GET', '/venues/{slug}', [HomeController::class, 'showVenue']],
    ['GET', '/about', [HomeController::class, 'about']],
    ['GET', '/contact', [HomeController::class, 'contact']],
    ['GET', '/bookings', [BookingsController::class, 'index']],
    ['GET', '/dashboard', [DashboardController::class, 'index']],
    ['GET', '/owners/dashboard', [DashboardController::class, 'index']],
    ['POST', '/dashboard/venues/update', [DashboardController::class, 'updateVenue']],
    ['POST', '/dashboard/slots/update', [DashboardController::class, 'updateSlot']],
    ['POST', '/dashboard/bookings/review', [DashboardController::class, 'reviewBooking']],
    ['POST', '/dashboard/users/change-role', [DashboardController::class, 'changeUserRole']],
    ['POST', '/dashboard/users/toggle-status', [DashboardController::class, 'toggleUserStatus']],
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login/request-otp', [AuthController::class, 'requestLoginOtp']],
    ['GET', '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register/request-otp', [AuthController::class, 'requestRegisterOtp']],
    ['POST', '/otp/verify', [AuthController::class, 'verifyOtp']],
    ['POST', '/otp/resend', [AuthController::class, 'resendOtp']],
    ['GET', '/logout', [AuthController::class, 'logout']],
    ['POST', '/bookings/deposit/initiate', [PaymentController::class, 'initiateDeposit']],
    ['POST', '/bookings/payment/manual/callback', [PaymentController::class, 'handleManualPaymentCallback']],
    ['POST', '/webhooks/razorpay', [PaymentController::class, 'handleRazorpayWebhook']],
    ['GET', '/bookings/deposit/success', [PaymentController::class, 'depositSuccess']],
];
