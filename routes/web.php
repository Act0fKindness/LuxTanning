<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KioskController;
use App\Http\Controllers\Admin\OrganisationController as AdminOrganisationController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AcceptInviteController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\CustomerSignupController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Kiosk\PairingController;
use App\Http\Controllers\Platform\OrganisationController as PlatformOrganisationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/Home');
})->name('landing');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::post('/register/save-exit', [RegisterController::class, 'saveDraft'])->name('register.save');
Route::get('/customer/register', [CustomerSignupController::class, 'create'])->name('customer.register');
Route::post('/customer/register', [CustomerSignupController::class, 'store'])->name('customer.register.store');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [VerificationController::class, 'send'])->name('verification.send');
    Route::post('/email/change', [VerificationController::class, 'changeEmail'])->name('verification.change');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
});

Route::get('/accept-invite/{token}', [AcceptInviteController::class, 'show'])->name('invites.show');
Route::post('/accept-invite/{token}', [AcceptInviteController::class, 'accept'])->name('invites.accept');
Route::post('/accept-invite/{token}/decline', [AcceptInviteController::class, 'decline'])->name('invites.decline');
Route::post('/invites/resend', [AcceptInviteController::class, 'resendFromLogin'])->name('invites.resend');

Route::middleware(['auth', 'verified'])->prefix('app')->group(function () {
    Route::get('/', DashboardController::class)->name('app.dashboard');

    Route::middleware('role:org_owner,shop_manager,glint_super_admin')->group(function () {
        Route::get('/organisation', [AdminOrganisationController::class, 'show'])->name('app.org.show');
        Route::put('/organisation', [AdminOrganisationController::class, 'update'])->name('app.org.update');

        Route::get('/shops', [ShopController::class, 'index'])->name('app.shops.index');
        Route::post('/shops', [ShopController::class, 'store'])->name('app.shops.store');

        Route::get('/users', [UserController::class, 'index'])->name('app.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('app.users.store');
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('app.users.status');
        Route::post('/users/{user}/resend-invite', [AcceptInviteController::class, 'resendForUser'])->name('app.users.resend');

        Route::get('/kiosks', [KioskController::class, 'index'])->name('app.kiosks.index');
        Route::post('/shops/{shop}/kiosks/pairing-code', [KioskController::class, 'generatePairingCode'])
            ->middleware('shop.access:shop')
            ->name('app.kiosks.generate');
        Route::post('/devices/{device}/revoke', [KioskController::class, 'revoke'])->name('app.devices.revoke');
    });

    Route::middleware('role:staff,shop_manager,org_owner')->group(function () {
        Route::get('/staff', fn() => Inertia::render('App/Staff/Home'))->name('app.staff.home');
    });
});

Route::middleware(['auth', 'verified', 'role:glint_super_admin'])->prefix('platform')->group(function () {
    Route::get('/organisations', [PlatformOrganisationController::class, 'index'])->name('platform.organisations.index');
});

Route::get('/kiosk/pair', [PairingController::class, 'show'])->name('kiosk.pair');
Route::post('/kiosk/pair', [PairingController::class, 'pair'])->name('kiosk.pair.store');

Route::get('/status', fn() => Inertia::render('Public/Status'))->name('status');
