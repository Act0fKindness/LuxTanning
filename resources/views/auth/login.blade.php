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
  .spinner{width:16px;height:16px;border-radius:9999px;border:2px solid rgba(0,0,0,.15);border-top-color:#000;animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>
@endpush

@section('content')
<div id="gl-auth" class="min-h-screen text-[15px] leading-6 text-zinc-900 antialiased py-24 px-4">

  <main class="mx-auto flex w-full max-w-[460px] items-center justify-center">
    <div class="relative w-full">
      <section class="relative rounded-[28px] border border-zinc-200/80 bg-white shadow-elev">
        <header class="flex items-center gap-3 px-6 pt-6">
          <img src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" alt="Glint Labs" class="h-9 w-9 object-contain" style="border-radius:8px;">
          <div class="select-none">
            <p class="text-[11px] uppercase tracking-[.16em] text-zinc-500">Glint Labs Ops</p>
            <h1 class="text-[20px] font-medium tracking-tight">Welcome back</h1>
          </div>
        </header>

        <div class="px-6 pb-7">
          <div class="mt-5 rounded-2xl border border-zinc-200/70 bg-zinc-50/80 px-4 py-3 text-[13px] text-zinc-600">
            Use your staff credentials to enter the manager, owner, support, or platform consoles. Customers should head to <a class="underline" href="/customer/login">customer login</a>.
          </div>

          @if (session('status'))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">
              {{ session('status') }}
            </div>
          @endif

          <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" autocomplete="on">
            @csrf
            <div>
              <label for="email" class="block text-[13px] text-zinc-600">Work email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@glintlabs.com" class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
              @error('email')
                <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password" class="block text-[13px] text-zinc-600">Password</label>
              <div class="relative mt-1">
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" class="peer w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 pr-11 outline-none focus:border-zinc-800 focus:ring-2 focus:ring-black/10" />
                <button type="button" class="absolute inset-y-0 right-2 my-1 px-2 rounded-lg text-zinc-500 hover:text-zinc-800 focus:outline-none focus:ring-2 focus:ring-black/10" aria-label="Show password" data-toggle-password="password">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              @error('password')
                <p class="mt-1 text-[12px] text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 text-[13px] text-zinc-600">
              <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-black/10" {{ old('remember') ? 'checked' : '' }}>
                Remember device
              </label>
              @if (Route::has('password.request'))
                <a class="text-zinc-700 hover:text-black" href="{{ route('password.request') }}">Forgot password?</a>
              @endif
            </div>

            <button type="submit" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-xl bg-black px-4 py-3 text-white transition active:opacity-90">
              <span>Sign in</span>
              <span class="spinner hidden" id="spin-in"></span>
            </button>
          </form>

          <div class="my-6 flex items-center gap-3 text-zinc-400"><div class="h-px flex-1 bg-zinc-200"></div><span class="text-[12px]">or</span><div class="h-px flex-1 bg-zinc-200"></div></div>

          <div class="grid gap-4">
            <a href="/auth/magic-link" class="group flex items-center justify-between gap-4 rounded-2xl border border-emerald-100 bg-white px-4 py-3 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
              <div>
                <p class="text-[11px] uppercase tracking-[.22em] text-emerald-500">Passwordless</p>
                <p class="text-base font-semibold text-zinc-900 group-hover:underline">Send a magic link</p>
                <p class="text-[13px] text-zinc-600">Instant access for any staff role.</p>
              </div>
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <i class="bi bi-lightning-charge-fill text-lg"></i>
              </div>
            </a>

            <div class="rounded-2xl border border-zinc-200 bg-zinc-50/80 px-4 py-4 text-[13px] text-zinc-600">
              Need access? <a href="{{ route('register') }}" class="font-semibold text-black">Request a staff login</a>. Owners can also invite teammates from <code>/owner/roles</code>.
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

  const form = document.querySelector('form')
  const spinner = document.getElementById('spin-in')
  form?.addEventListener('submit', () => {
    spinner?.classList.remove('hidden')
  })
</script>
@endsection
