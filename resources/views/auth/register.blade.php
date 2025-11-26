@extends('layouts.app')

@push('head')
<script data-cfasync="false">
  window.tailwind = window.tailwind || {}
  window.tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['-apple-system','BlinkMacSystemFont','SF Pro Text','Inter','Segoe UI','Roboto','Helvetica Neue','Arial','Noto Sans','Apple Color Emoji','Segoe UI Emoji'],
        },
        boxShadow: {
          elev: '0 20px 45px rgba(15,23,42,.08), 0 8px 18px rgba(15,23,42,.05)',
        },
        animation: {
          sheen: 'sheen 2.2s cubic-bezier(.4,0,.2,1) infinite',
        },
        keyframes: {
          sheen: {
            '0%': { transform: 'translateX(-100%)' },
            '100%': { transform: 'translateX(200%)' },
          },
        },
      },
    },
    corePlugins: {
      preflight: false,
    },
  }
</script>
<script data-cfasync="false" src="https://cdn.tailwindcss.com"></script>
<style>
  .btn-press:active{transform:translateY(1px)}
</style>
@endpush

@section('content')
<div id="gl-auth" class="min-h-screen text-[15px] leading-6 text-zinc-900 antialiased py-24 px-4">

  <main class="mx-auto flex w-full max-w-[540px] items-center justify-center">
    <div class="relative w-full">
      <section class="relative rounded-[32px] border border-zinc-200/80 bg-white shadow-elev">
        <header class="flex items-center gap-3 px-6 pt-6">
          <img src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" alt="Glint Labs" class="h-9 w-9 object-contain" style="border-radius:8px;">
          <div class="select-none">
            <p class="text-[11px] uppercase tracking-[.16em] text-zinc-500">Glint Labs Ops</p>
            <h1 class="text-[20px] font-medium tracking-tight">Request staff access</h1>
          </div>
        </header>

        <div class="px-6 pb-8">
          <div class="mt-6 space-y-3">
            <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 text-[13px] text-zinc-600">
              Tell us who you are and which workspace you’re joining. Owners approve roles from <code class="text-[12px]">/owner/roles</code>, or the Glint team will provision new companies.
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-[13px] text-amber-800">
              <strong>Need customer access?</strong> Use the <a href="/customer/register" class="underline">customer signup</a> instead. This form is only for staff and contractors.
            </div>
          </div>

          @php($tenantOptions = isset($tenants) ? $tenants : collect())
          <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4" autocomplete="on">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label for="name" class="block text-[13px] text-zinc-600">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                @error('name')
                  <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label for="email" class="block text-[13px] text-zinc-600">Work email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                @error('email')
                  <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div>
              <label for="role" class="block text-[13px] text-zinc-600">Role you need</label>
              <select id="role" name="role" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-[15px] focus:border-zinc-800 focus:ring-2 focus:ring-black/10">
                <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager / Dispatcher</option>
                <option value="cleaner" {{ old('role') === 'cleaner' ? 'selected' : '' }}>Cleaner / Field</option>
                <option value="accountant" {{ old('role') === 'accountant' ? 'selected' : '' }}>Accountant</option>
              </select>
              @error('role')
                <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div id="company-select" class="grid gap-2 rounded-2xl border border-zinc-200/80 bg-zinc-50/70 p-4">
              <div>
                <label for="tenant_id" class="block text-[13px] text-zinc-600">Which company are you joining?</label>
                <select id="tenant_id" name="tenant_id" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-[15px] focus:border-zinc-800 focus:ring-2 focus:ring-black/10">
                  <option value="">Select a company</option>
                  @forelse($tenantOptions as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id') === $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                  @empty
                    <option value="" disabled>No companies available</option>
                  @endforelse
                </select>
              </div>
              @error('tenant_id')
                <p class="text-[12px] text-rose-600">{{ $message }}</p>
              @enderror
              <p class="text-[12px] text-zinc-500">Managers, cleaners, and accountants must join an existing tenant.</p>
            </div>

            <div id="company-name" class="grid gap-2 rounded-2xl border border-zinc-200/80 bg-emerald-50/60 p-4 hidden">
              <div>
                <label for="company_name" class="block text-[13px] text-zinc-600">New company name</label>
                <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" class="mt-1 w-full rounded-xl border border-emerald-200 bg-white px-3.5 py-3 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" placeholder="e.g. Crystal Clear Glasgow" />
              </div>
              @error('company_name')
                <p class="text-[12px] text-rose-600">{{ $message }}</p>
              @enderror
              <p class="text-[12px] text-emerald-700">Owners get a brand new workspace. We’ll set up everything with this name.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label for="password" class="block text-[13px] text-zinc-600">Password</label>
                <div class="relative mt-1">
                  <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="At least 8 characters" class="peer w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 pr-11 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                  <button type="button" class="absolute inset-y-0 right-2 my-1 px-2 rounded-lg text-zinc-500 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-black/10" aria-label="Show password" data-toggle-password="password">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                @error('password')
                  <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label for="password-confirm" class="block text-[13px] text-zinc-600">Confirm password</label>
                <input id="password-confirm" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
              </div>
            </div>

            <label class="inline-flex items-start gap-3 text-[13px] text-zinc-600 cursor-pointer select-none">
              <input type="checkbox" required class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-black/10">
              <span>I agree to Glint Labs’ <a href="/terms" class="underline">Terms</a> & <a href="/privacy" class="underline">Privacy Policy</a>. Owners approve or revoke roles at any time.</span>
            </label>

            <button type="submit" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl bg-black px-4 py-3 text-white transition active:opacity-90">
              <span>Create staff account</span>
            </button>
          </form>

          <div class="mt-6 grid gap-4">
            <a href="/auth/magic-link" class="group flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-white px-4 py-3 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
              <div>
                <p class="text-[11px] uppercase tracking-[.22em] text-emerald-500">Passwordless</p>
                <p class="text-base font-semibold text-zinc-900 group-hover:underline">Send a magic link</p>
                <p class="text-[13px] text-zinc-600">Invite yourself or teammates instantly.</p>
              </div>
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <i class="bi bi-lightning-charge-fill text-lg"></i>
              </div>
            </a>

            <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-4 text-[13px] text-zinc-600">
              <p class="text-[12px] uppercase tracking-[.2em] text-zinc-500">Already invited?</p>
              <p class="text-[15px] font-medium text-zinc-900">Check your inbox for the activation link.</p>
              <p class="mt-1">If you already have access, <a href="{{ route('login') }}" class="font-semibold text-black">sign in here</a> or request a <a href="/auth/magic-link" class="underline">magic link</a>.</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-4 text-[13px] text-emerald-800">
              Need to register a new franchise or city? Include those details above and the Glint platform team will schedule onboarding within 1–2 business days.
            </div>
          </div>
        </div>

        <div class="auth-footer px-6 pb-6">
          <p class="text-[12px] text-zinc-500">Protected by reCAPTCHA and subject to Glint Labs’ <a href="/privacy-ploicy" class="underline hover:no-underline">Privacy Policy</a> & <a href="/terms" class="underline hover:no-underline">Terms</a>.</p>
        </div>
      </section>
      <div aria-hidden="true" class="pointer-events-none absolute -inset-x-2 -top-1 h-px bg-gradient-to-r from-transparent via-black/20 to-transparent"></div>
    </div>
  </main>
</div>

<script data-cfasync="false">
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-toggle-password')
      const input = document.getElementById(id)
      if (!input) return
      const isPw = input.type === 'password'
      input.type = isPw ? 'text' : 'password'
      btn.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password')
    })
  })

  const roleSelect = document.getElementById('role')
  const existingCompanyBlock = document.getElementById('company-select')
  const newCompanyBlock = document.getElementById('company-name')
  const tenantSelect = document.getElementById('tenant_id')
  const companyNameInput = document.getElementById('company_name')

  function syncCompanyFields() {
    if (!roleSelect) return
    const isOwner = roleSelect.value === 'owner'
    existingCompanyBlock?.classList.toggle('hidden', isOwner)
    newCompanyBlock?.classList.toggle('hidden', !isOwner)
    if (isOwner) {
      tenantSelect && (tenantSelect.value = '')
    } else if (companyNameInput && !isOwner) {
      companyNameInput.value = ''
    }
  }

  roleSelect?.addEventListener('change', syncCompanyFields)
  syncCompanyFields()
</script>
@endsection
