<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Hub\TenantsController as HubTenants;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\AddressesController;
use App\Http\Controllers\Api\QuotesController;
use App\Http\Controllers\Api\BookingsController;
use App\Http\Controllers\Api\SubscriptionsController;
use App\Http\Controllers\Api\JobsController;
use App\Http\Controllers\Api\RoutesController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\PayoutsController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\TrackingPingsController;
use App\Http\Controllers\Api\TrackingSessionController;
use App\Http\Controllers\Api\EtaController;
use App\Http\Controllers\Api\JobFlowController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\JobsBoardController;
use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\Api\ExportsController;
use App\Http\Controllers\Api\WebhooksController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Hub (platform admin)
Route::prefix('hub')->group(function () {
    Route::post('/tenants', [HubTenants::class, 'store']);
    Route::patch('/tenants/{id}', [HubTenants::class, 'update']);
    Route::get('/tenants', [HubTenants::class, 'index']);
    Route::post('/tenants/{id}/impersonate', [HubTenants::class, 'impersonate']);
});

// Customers & addresses
Route::get('/customers', [CustomersController::class, 'index']);
Route::post('/customers', [CustomersController::class, 'store']);
Route::patch('/customers/{id}', [CustomersController::class, 'update']);
Route::delete('/customers/{id}', [CustomersController::class, 'destroy']);
Route::post('/customers/{id}/addresses', [AddressesController::class, 'store']);
Route::patch('/addresses/{id}', [AddressesController::class, 'update']);

// Pricing & quotes
Route::post('/quotes', [QuotesController::class, 'store']);

// Bookings
Route::post('/bookings', [BookingsController::class, 'store']);
Route::post('/bookings/{id}/cancel', [BookingsController::class, 'cancel']);

// Subscriptions
Route::post('/subscriptions', [SubscriptionsController::class, 'store']);
Route::patch('/subscriptions/{id}', [SubscriptionsController::class, 'update']);
Route::delete('/subscriptions/{id}', [SubscriptionsController::class, 'destroy']);

// Jobs & routes
Route::get('/jobs', [JobsController::class, 'index']);
Route::patch('/jobs/{id}', [JobsController::class, 'update']);
Route::post('/jobs/{id}/start_trip', [JobsController::class, 'startTrip']);
Route::post('/jobs/{id}/arrived', [JobsController::class, 'arrived']);
Route::post('/jobs/{id}/complete', [JobsController::class, 'complete']);
Route::post('/jobs/{id}/assign', [JobsController::class, 'assign']);
Route::post('/jobs/auto_assign', [JobsController::class, 'autoAssign']);
Route::get('/routes', [RoutesController::class, 'index']);
Route::post('/routes/optimise', [RoutesController::class, 'optimise']);

// Payments & payouts
Route::post('/payments/intent', [PaymentsController::class, 'intent']);
Route::get('/payments', [PaymentsController::class, 'index']);
Route::post('/payments/{id}/retry', [PaymentsController::class, 'retry']);
Route::post('/payments/{id}/refund', [PaymentsController::class, 'refund']);
Route::get('/payouts', [PayoutsController::class, 'index']);

// Tracking (public share)
Route::get('/track/{share_token}', [TrackingController::class, 'show']);

// Staffing
Route::post('/staff/invite', [StaffController::class, 'invite']);
Route::post('/staff/accept', [StaffController::class, 'accept']);
Route::get('/jobs/board', [JobsBoardController::class, 'board']);
Route::post('/jobs/{id}/claim', [JobsBoardController::class, 'claim']);

// Comms (abstracted)
Route::post('/messages/email', [MessagesController::class, 'email']);
Route::post('/messages/sms', [MessagesController::class, 'sms']);

// Exports
Route::post('/exports/{type}', [ExportsController::class, 'store']);
Route::get('/exports/{id}', [ExportsController::class, 'show']);

// Webhooks
Route::post('/webhooks/stripe', [WebhooksController::class, 'stripe']);
Route::post('/webhooks/bacs', [WebhooksController::class, 'bacs']);

// Authenticated cleaner navigation + ETA
Route::middleware('auth')->group(function () {
    Route::post('/tracking/session', [TrackingSessionController::class, 'start']);
    Route::post('/tracking/session/close', [TrackingSessionController::class, 'close']);
    Route::post('/tracking/ping', [TrackingPingsController::class, 'store']);
    Route::get('/eta/{job}', [EtaController::class, 'show']);
    Route::post('/jobs/{job}/start', [JobFlowController::class, 'startJob']);
    Route::post('/jobs/{job}/finish', [JobFlowController::class, 'finishJob']);
    Route::post('/jobs/{job}/cancel', [JobFlowController::class, 'cancelJob']);
});
