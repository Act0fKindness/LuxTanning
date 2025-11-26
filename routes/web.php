<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\PageContextResolver;
use App\Http\Controllers\Cleaner\JobsController as CleanerJobsController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\Glint\CompaniesController;
use App\Http\Controllers\Glint\CustomersController;
use App\Http\Controllers\Glint\JobsController;
use App\Http\Controllers\Owner\JobsController as OwnerJobsController;
use App\Http\Controllers\PublicTrackingController;
use App\Http\Controllers\Owner\BrandingController as OwnerBrandingController;
use Illuminate\Support\Facades\Log;

if (! function_exists('registerBlueprintRoutes')) {
    /**
     * Helper to register many inertial blueprint pages that share a component shell.
     */
    function registerBlueprintRoutes(string $component, array $pages, array $options = []): void
    {
        Route::group($options, function () use ($component, $pages) {
            foreach ($pages as $page) {
                Route::get($page['uri'], function (...$params) use ($component, $page) {
                    $context = [];
                    if (isset($page['context']) && is_callable($page['context'])) {
                        $context = $page['context'](...$params);
                    } elseif (isset($page['context']) && is_array($page['context'])) {
                        $context = $page['context'];
                    }

                    $context = app(PageContextResolver::class)->resolve($page['key'], $context);

                    return Inertia::render($component, [
                        'pageKey' => $page['key'],
                        'context' => $context,
                    ]);
                });
            }
        });
    }
}

Route::get('/', function (Request $request) {
    return Inertia::render('Public/PageShell', [
        'pageKey' => 'public.home',
    ]);
});

Route::get('/track', PublicTrackingController::class)->name('public.track');
Route::post('/js-debug', function (Request $request) {
    Log::channel('stack')->error('JS_RUNTIME_ERROR', [
        'path' => $request->input('href'),
        'component' => $request->input('component'),
        'message' => $request->input('message'),
        'stack' => $request->input('stack'),
        'type' => $request->input('type'),
        'user' => optional($request->user())->id,
    ]);

    return response()->noContent();
})->name('js-debug');

// Public site + guest tools
Route::get('/book', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'public.book']));
Route::get('/checkout', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'public.checkout']));
Route::get('/booking/confirmed/{sessionId}', fn(string $sessionId) => Inertia::render('Public/PageShell', [
    'pageKey' => 'public.booking-confirmed',
    'context' => ['sessionId' => $sessionId],
]));
Route::get('/find', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'public.find']));
Route::get('/manage/{token}', fn(string $token) => Inertia::render('Public/PageShell', [
    'pageKey' => 'public.manage',
    'context' => ['token' => $token],
]));
Route::get('/track/{trackingId}', fn(string $trackingId) => Inertia::render('Public/PageShell', [
    'pageKey' => 'public.track',
    'context' => ['trackingId' => $trackingId],
]));
Route::get('/receipt/{id}', fn(string $id) => Inertia::render('Public/PageShell', [
    'pageKey' => 'public.receipt',
    'context' => ['id' => $id],
]));

// Static / system pages
Route::get('/help', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.help']));
Route::get('/status', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.status']));
Route::get('/privacy', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.privacy']));
Route::get('/terms', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.terms']));
Route::redirect('/privacy-ploicy', '/privacy', 301);
Route::get('/404', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.404']));
Route::get('/403', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.403']));
Route::get('/500', fn() => Inertia::render('Public/PageShell', ['pageKey' => 'shared.500']));

// Auth helper pages
Route::prefix('auth')->group(function () {
    Route::view('/magic-link', 'auth.magic-link')->name('magic-link');
    Route::post('/magic-link', function (Request $request) {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'in:email,sms'],
            'workspace' => ['nullable', 'string', 'max:255'],
        ]);

        $channel = $data['channel'] === 'sms' ? 'SMS' : 'email';

        return back()->with('status', "Magic link queued. Check your {$channel} inbox in a moment.");
    })->name('magic-link.send');

    Route::get('/verify', fn() => Inertia::render('Auth/PageShell', ['pageKey' => 'auth.verify']));
});

Route::post('/chat/glint', SupportChatController::class)->name('chat.glint');

Route::middleware('auth')->group(function () {
    Route::get('/me', function (Request $request) {
        return Inertia::render('Shared/PageShell', [
            'pageKey' => 'shared.me',
            'context' => ['role' => $request->user()?->role],
        ]);
    });
});

// Customer portal
registerBlueprintRoutes('Customer/PageShell', [
    ['uri' => 'dashboard', 'key' => 'customer.dashboard'],
    ['uri' => 'cleans', 'key' => 'customer.cleans'],
    ['uri' => 'cleans/{jobId}', 'key' => 'customer.clean-detail', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'track/{jobId}', 'key' => 'customer.track', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'addresses', 'key' => 'customer.addresses'],
    ['uri' => 'billing', 'key' => 'customer.billing'],
    ['uri' => 'invoices', 'key' => 'customer.invoices'],
    ['uri' => 'preferences', 'key' => 'customer.preferences'],
    ['uri' => 'security', 'key' => 'customer.security'],
    ['uri' => 'support', 'key' => 'customer.support'],
], ['prefix' => 'customer', 'middleware' => ['auth']]);

// Cleaner PWA
registerBlueprintRoutes('Cleaner/PageShell', [
    ['uri' => 'today', 'key' => 'cleaner.today'],
    ['uri' => 'jobs/{jobId}', 'key' => 'cleaner.job-detail', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'jobs/{jobId}/navigate', 'key' => 'cleaner.navigate', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'jobs/{jobId}/start', 'key' => 'cleaner.start', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'jobs/{jobId}/pause', 'key' => 'cleaner.pause', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'jobs/{jobId}/finish', 'key' => 'cleaner.finish', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'jobs/{jobId}/cancel', 'key' => 'cleaner.cancel', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'history', 'key' => 'cleaner.history'],
    ['uri' => 'earnings', 'key' => 'cleaner.earnings'],
    ['uri' => 'inbox', 'key' => 'cleaner.inbox'],
    ['uri' => 'settings', 'key' => 'cleaner.settings'],
    ['uri' => 'offline', 'key' => 'cleaner.offline'],
], ['prefix' => 'cleaner', 'middleware' => ['auth']]);

Route::middleware('auth')->group(function () {
    Route::post('/cleaner/jobs/{job}/status', [CleanerJobsController::class, 'updateStatus'])->name('cleaner.jobs.status');
    Route::post('/cleaner/jobs/{job}/location', [CleanerJobsController::class, 'updateLocation'])->name('cleaner.jobs.location');
});

// Manager console
registerBlueprintRoutes('Manager/PageShell', [
    ['uri' => 'dispatch/board', 'key' => 'manager.dispatch.board'],
    ['uri' => 'dispatch/routes', 'key' => 'manager.dispatch.routes'],
    ['uri' => 'dispatch/exceptions', 'key' => 'manager.dispatch.exceptions'],
    ['uri' => 'dispatch/bulk', 'key' => 'manager.dispatch.bulk'],
    ['uri' => 'jobs', 'key' => 'owner.jobs'],
    ['uri' => 'jobs/new', 'key' => 'manager.jobs.new'],
    ['uri' => 'jobs/{jobId}', 'key' => 'manager.jobs.detail', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'checklists', 'key' => 'manager.checklists'],
    ['uri' => 'addons', 'key' => 'manager.addons'],
    ['uri' => 'live/map', 'key' => 'manager.live.map'],
    ['uri' => 'live/timeline', 'key' => 'manager.live.timeline'],
    ['uri' => 'customers', 'key' => 'manager.customers.list'],
    ['uri' => 'customers/{id}', 'key' => 'manager.customers.detail', 'context' => fn(string $id) => ['customerId' => $id]],
    ['uri' => 'subscriptions', 'key' => 'manager.subscriptions'],
    ['uri' => 'staff', 'key' => 'manager.staff.list'],
    ['uri' => 'staff/{id}', 'key' => 'manager.staff.detail', 'context' => fn(string $id) => ['staffId' => $id]],
    ['uri' => 'shifts', 'key' => 'manager.shifts'],
    ['uri' => 'announcements', 'key' => 'manager.announcements'],
    ['uri' => 'refunds', 'key' => 'manager.refunds'],
    ['uri' => 'adjustments', 'key' => 'manager.adjustments'],
    ['uri' => 'reports/operations', 'key' => 'manager.reports.operations'],
    ['uri' => 'reports/quality', 'key' => 'manager.reports.quality'],
    ['uri' => 'reports/volume', 'key' => 'manager.reports.volume'],
    ['uri' => 'settings/policies', 'key' => 'manager.settings.policies'],
    ['uri' => 'settings/notifications', 'key' => 'manager.settings.notifications'],
    ['uri' => 'settings/integrations', 'key' => 'manager.settings.integrations'],
], ['prefix' => 'manager', 'middleware' => ['auth']]);

// Owner console (gets everything plus admin controls)
registerBlueprintRoutes('Owner/PageShell', [
    ['uri' => 'overview', 'key' => 'owner.overview'],
    ['uri' => 'billing/stripe', 'key' => 'owner.billing.stripe'],
    ['uri' => 'invoices', 'key' => 'owner.invoices'],
    ['uri' => 'payouts', 'key' => 'owner.payouts'],
    ['uri' => 'taxes', 'key' => 'owner.taxes'],
    ['uri' => 'chargebacks', 'key' => 'owner.chargebacks'],
    ['uri' => 'roles', 'key' => 'owner.roles'],
    ['uri' => 'audit-log', 'key' => 'owner.audit-log'],
    ['uri' => 'data-retention', 'key' => 'owner.data-retention'],
    ['uri' => 'legal', 'key' => 'owner.legal'],
    ['uri' => 'branding', 'key' => 'owner.branding'],
    ['uri' => 'pricing', 'key' => 'owner.pricing'],
    ['uri' => 'policies', 'key' => 'owner.policies'],
    ['uri' => 'domains', 'key' => 'owner.domains'],
    ['uri' => 'api-keys', 'key' => 'owner.api-keys'],
    ['uri' => 'integrations', 'key' => 'owner.integrations'],
    ['uri' => 'dispatch/board', 'key' => 'owner.dispatch.board'],
    ['uri' => 'dispatch/routes', 'key' => 'owner.routes'],
    ['uri' => 'jobs', 'key' => 'owner.jobs'],
    ['uri' => 'customers', 'key' => 'manager.customers.list'],
    ['uri' => 'subscriptions', 'key' => 'manager.subscriptions'],
    ['uri' => 'staff', 'key' => 'owner.staff'],
    ['uri' => 'staff/roster', 'key' => 'manager.shifts'],
], ['prefix' => 'owner', 'middleware' => ['auth']]);

Route::middleware(['auth'])->prefix('owner')->group(function () {
    Route::post('/branding/update', [OwnerBrandingController::class, 'update'])->name('owner.branding.update');
    Route::get('/jobs/new', [OwnerJobsController::class, 'create'])->name('owner.jobs.new');
    Route::post('/jobs', [OwnerJobsController::class, 'store'])->name('owner.jobs.store');
});

// Accountant workspace
registerBlueprintRoutes('Accountant/PageShell', [
    ['uri' => 'invoices', 'key' => 'accountant.invoices'],
    ['uri' => 'payments', 'key' => 'accountant.payments'],
    ['uri' => 'payouts', 'key' => 'accountant.payouts'],
    ['uri' => 'taxes', 'key' => 'accountant.taxes'],
    ['uri' => 'adjustments', 'key' => 'accountant.adjustments'],
    ['uri' => 'disputes', 'key' => 'accountant.disputes'],
    ['uri' => 'exports', 'key' => 'accountant.exports'],
], ['prefix' => 'accountant', 'middleware' => ['auth']]);

// Support desk
registerBlueprintRoutes('Support/PageShell', [
    ['uri' => 'tickets', 'key' => 'support.tickets'],
    ['uri' => 'customers/{id}', 'key' => 'support.customer-detail', 'context' => fn(string $id) => ['customerId' => $id]],
    ['uri' => 'jobs/{jobId}', 'key' => 'support.job-detail', 'context' => fn(string $jobId) => ['jobId' => $jobId]],
    ['uri' => 'tools', 'key' => 'support.tools'],
], ['prefix' => 'support', 'middleware' => ['auth']]);

// Glint platform owner
Route::middleware(['auth'])->prefix('glint')->group(function () {
    Route::get('/companies', [CompaniesController::class, 'index'])->name('glint.companies');
    Route::post('/companies', [CompaniesController::class, 'store'])->name('glint.companies.store');
    Route::post('/companies/{tenant}/staff', [CompaniesController::class, 'storeStaff'])->name('glint.companies.staff');
    Route::post('/companies/{tenant}/customers', [CompaniesController::class, 'storeCustomer'])->name('glint.companies.customers');
    Route::get('/customers', [CustomersController::class, 'index'])->name('glint.customers');
    Route::patch('/tenants/{tenant}/customers/{customer}', [CustomersController::class, 'update'])->name('glint.customers.update');
    Route::post('/tenants/{tenant}/customers/from-job', [CustomersController::class, 'convertJobLead'])->name('glint.customers.from-job');
    Route::post('/tenants/{tenant}/customers/merge-job', [CustomersController::class, 'mergeJobLead'])->name('glint.customers.merge-job');
    Route::get('/jobs', [JobsController::class, 'index'])->name('glint.jobs');
    Route::post('/tenants/{tenant}/jobs', [JobsController::class, 'store'])->name('glint.jobs.store');
});

registerBlueprintRoutes('Glint/PageShell', [
    ['uri' => 'staff', 'key' => 'glint.staff'],
    ['uri' => 'jobs', 'key' => 'glint.jobs'],
    ['uri' => 'tenants/{id}/overview', 'key' => 'glint.tenants.overview', 'context' => fn(string $id) => ['tenantId' => $id]],
    ['uri' => 'tenants/{id}/impersonate', 'key' => 'glint.tenants.impersonate', 'context' => fn(string $id) => ['tenantId' => $id]],
    ['uri' => 'platform', 'key' => 'glint.platform-overview'],
    ['uri' => 'health/queues', 'key' => 'glint.health.queues'],
    ['uri' => 'health/webhooks', 'key' => 'glint.health.webhooks'],
    ['uri' => 'health/services', 'key' => 'glint.health.services'],
    ['uri' => 'billing/plans', 'key' => 'glint.billing.plans'],
    ['uri' => 'billing/fees', 'key' => 'glint.billing.fees'],
    ['uri' => 'billing/settlements', 'key' => 'glint.billing.settlements'],
    ['uri' => 'audit', 'key' => 'glint.audit'],
    ['uri' => 'gdpr/sar', 'key' => 'glint.gdpr.sar'],
    ['uri' => 'data-exports', 'key' => 'glint.data-exports'],
    ['uri' => 'retention', 'key' => 'glint.retention'],
    ['uri' => 'feature-flags', 'key' => 'glint.feature-flags'],
    ['uri' => 'templates', 'key' => 'glint.templates'],
    ['uri' => 'checklists', 'key' => 'glint.checklists'],
    ['uri' => 'maps', 'key' => 'glint.maps'],
    ['uri' => 'security', 'key' => 'glint.security'],
    ['uri' => 'abuse', 'key' => 'glint.abuse'],
    ['uri' => 'logs', 'key' => 'glint.logs'],
    ['uri' => 'metrics', 'key' => 'glint.metrics'],
    ['uri' => 'incidents', 'key' => 'glint.incidents'],
    ['uri' => 'cms', 'key' => 'glint.cms'],
], ['prefix' => 'glint', 'middleware' => ['auth']]);

// Legacy dashboard shim
Route::middleware('auth')->get('/dashboard', function (Request $request) {
    $role = $request->user()?->role;
    return match ($role) {
        'owner' => redirect('/owner/overview'),
        'manager' => redirect('/manager/dispatch/board'),
        'cleaner' => redirect('/cleaner/today'),
        'accountant' => redirect('/accountant/invoices'),
        'support' => redirect('/support/tickets'),
        'platform_admin' => redirect('/glint/platform'),
        'customer' => redirect('/customer/dashboard'),
        default => redirect('/'),
    };
})->name('home');
Route::redirect('/home', '/dashboard', 301);

Auth::routes();

// Customer auth blades retained for backwards compatibility
Route::view('/customer/login', 'customer.login');
Route::view('/customer/register', 'customer.register');
