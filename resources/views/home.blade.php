@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row g-4">
        <!-- Aside -->
        <aside class="col-lg-2">
            <div class="dash-aside">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar me-2"><i class="bi bi-person"></i></div>
                    <div>
                        <div class="fw-bold">{{ Auth::user()->name }}</div>
                        <div class="text-muted small">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <nav class="menu">
                    <a href="/dashboard" class="menu-link active"><i class="bi bi-speedometer2 me-2"></i>Overview</a>
                    <a href="/tenant/schedule" class="menu-link"><i class="bi bi-calendar3 me-2"></i>Schedule</a>
                    <a href="/tenant/customers" class="menu-link"><i class="bi bi-people me-2"></i>Customers</a>
                    <a href="/tenant/payments" class="menu-link"><i class="bi bi-credit-card me-2"></i>Payments</a>
                    <a href="/tenant/invoices" class="menu-link"><i class="bi bi-receipt me-2"></i>Invoices</a>
                    <a href="/tenant/staff" class="menu-link"><i class="bi bi-person-badge me-2"></i>Staff</a>
                    <a href="/tenant/settings/brand" class="menu-link"><i class="bi bi-gear me-2"></i>Settings</a>
                </nav>
            </div>
        </aside>

        <!-- Main content -->
        <section class="col-lg-10">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-1">Welcome back</h5>
                    <p class="text-muted mb-0">Quick links to today’s work.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat glass-tile">
                        <div class="label">Jobs today</div>
                        <div class="value">—</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat glass-tile">
                        <div class="label">Revenue</div>
                        <div class="value">—</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat glass-tile">
                        <div class="label">Failed payments</div>
                        <div class="value">—</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root{ --line:#E9EAF0; --mint:#4FE1C1; --bg:#F7F8FB; }
    .dash-aside{ position:sticky; top:calc(var(--nav-h) + 24px); border:1px solid var(--line); border-radius:16px; padding:16px; background:#fff; box-shadow:0 10px 30px rgba(20,20,40,.06) }
    .dash-aside .avatar{ width:36px; height:36px; border-radius:10px; display:grid; place-items:center; background:linear-gradient(135deg, rgba(79,225,193,.18), rgba(79,225,193,.08)); }
    .dash-aside .menu{ display:flex; flex-direction:column; gap:6px; }
    .dash-aside .menu-link{ display:flex; align-items:center; gap:.25rem; padding:.55rem .75rem; border-radius:10px; text-decoration:none; color:#0B0C0F; border:1px solid transparent; }
    .dash-aside .menu-link:hover{ background:rgba(79,225,193,.06); border-color:rgba(79,225,193,.35); }
    .dash-aside .menu-link.active{ background:rgba(79,225,193,.12); border-color:rgba(79,225,193,.45); }
    .glass-tile{ border:1px solid var(--line); border-radius:16px; padding:16px; background:#fff; box-shadow:0 10px 30px rgba(20,20,40,.06) }
    .stat .label{ color:#6B7280; font-size:.9rem }
    .stat .value{ font-weight:800; font-size:1.35rem }
</style>
@endpush
