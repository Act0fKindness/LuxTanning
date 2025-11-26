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
  .spinner{width:16px;height:16px;border-radius:999px;border:2px solid rgba(0,0,0,.15);border-top-color:#000;animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
</style>
@endpush

@section('content')
<div id="gl-auth" class="min-h-screen text-[15px] leading-6 text-zinc-900 antialiased py-24 px-4">
  <main class="mx-auto flex w-full max-w-[900px] items-start justify-center">
    <div class="w-full grid gap-8 md:grid-cols-[minmax(0,0.7fr)_minmax(280px,0.3fr)]">
      <section class="rounded-[28px] border border-zinc-200/80 bg-white p-6 shadow-elev">
        <header class="flex items-center gap-3">
          <img src="https://cdn.shopify.com/s/files/1/0820/3947/2469/files/glint-favicon-black.jpg?v=1762130152" alt="Glint Labs" class="h-9 w-9 object-contain" style="border-radius:8px;">
          <div>
            <p class="text-[11px] uppercase tracking-[.2em] text-emerald-500">Passwordless entry</p>
            <h1 class="text-xl font-semibold text-zinc-900">Send yourself a magic link</h1>
            <p class="text-[14px] text-zinc-500">Works for cleaners, managers, owners, support, and Glint platform staff.</p>
          </div>
        </header>

        @if (session('status'))
          <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-900">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('magic-link.send') }}" class="mt-6 space-y-5">
          @csrf
          <div class="space-y-2">
            <label for="identifier" class="text-[13px] font-semibold text-zinc-600">Email or mobile</label>
            <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required autofocus autocomplete="email" placeholder="you@company.co.uk or +44 7000 000000" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 outline-none focus:border-zinc-900 focus:ring-2 focus:ring-black/10" />
            @error('identifier')
              <p class="text-[12px] text-rose-600">{{ $message }}</p>
            @enderror
            <p class="text-[12px] text-zinc-500">We'll look up your tenant memberships automatically.</p>
          </div>

          <div class="space-y-2">
            <span class="text-[13px] font-semibold text-zinc-600">Preferred channel</span>
            <div class="flex flex-wrap gap-3">
              @foreach (['email' => 'Email','sms' => 'SMS / WhatsApp'] as $value => $label)
                <label class="flex flex-1 min-w-[140px] cursor-pointer items-center gap-2 rounded-2xl border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:border-zinc-900">
                  <input type="radio" name="channel" value="{{ $value }}" class="h-4 w-4 text-zinc-900 focus:ring-black/10" {{ old('channel', 'email') === $value ? 'checked' : '' }}>
                  <span>{{ $label }}</span>
                </label>
              @endforeach
            </div>
            @error('channel')
              <p class="text-[12px] text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="space-y-2">
            <label for="workspace" class="text-[13px] font-semibold text-zinc-600">Workspace (optional)</label>
            <input id="workspace" name="workspace" type="text" value="{{ old('workspace') }}" placeholder="e.g. northside.glintlabs.com" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 outline-none focus:border-zinc-900 focus:ring-2 focus:ring-black/10" />
            <p class="text-[12px] text-zinc-500">Leave blank to send to every workspace you're a member of.</p>
          </div>

          <div class="space-y-2">
            <label class="inline-flex items-start gap-2 text-[13px] text-zinc-600">
              <input type="checkbox" name="pin_login" value="1" class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-black/10" {{ old('pin_login') ? 'checked' : '' }}>
              <span>Also generate a one-time PIN fallback.</span>
            </label>
            <label class="inline-flex items-start gap-2 text-[13px] text-zinc-600">
              <input type="checkbox" name="remember_device" value="1" class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-black/10" {{ old('remember_device', true) ? 'checked' : '' }}>
              <span>Remember this device for quicker logins.</span>
            </label>
          </div>

          <button type="submit" class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-black px-4 py-3 text-white font-semibold">
            <span>Send magic link</span>
            <span class="spinner hidden" id="spin-link"></span>
          </button>
        </form>

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50/80 px-4 py-4 text-[13px] text-zinc-600">
          <p class="text-[12px] uppercase tracking-[.2em] text-zinc-500">Need a password instead?</p>
          <p class="mt-1">Use the <a href="{{ route('login') }}" class="font-semibold text-zinc-900">standard login</a> or <a href="{{ route('register') }}" class="font-semibold text-zinc-900">request staff access</a>.</p>
        </div>

        <div class="auth-footer px-0 pt-4">
          <p class="text-[12px] text-zinc-500">Protected by reCAPTCHA and subject to Glint Labs’ <a href="/privacy-ploicy" class="underline hover:no-underline">Privacy Policy</a> & <a href="/terms" class="underline hover:no-underline">Terms</a>.</p>
        </div>
      </section>

      <aside class="rounded-[24px] border border-zinc-200 bg-white p-5 shadow-sm space-y-5">
        <div>
          <p class="text-[12px] uppercase tracking-[.2em] text-emerald-500">How it works</p>
          <ol class="mt-3 space-y-3 text-[13px] text-zinc-600">
            <li><strong class="text-zinc-900">1.</strong> We verify your identifier and tenant memberships.</li>
            <li><strong class="text-zinc-900">2.</strong> You pick the workspace and confirm device posture.</li>
            <li><strong class="text-zinc-900">3.</strong> Magic link arrives via your chosen channel in seconds.</li>
          </ol>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/90 p-4 text-[13px] text-zinc-600">
          <strong class="text-zinc-900">Tip:</strong> For shared devices, tick the OTP checkbox so you always have a PIN fallback even if emails are blocked.
        </div>
        <div class="space-y-3 text-[13px] text-zinc-600">
          <p class="font-semibold text-zinc-900">Need help?</p>
          <ul class="space-y-2">
            <li><i class="bi bi-chat-dots text-emerald-500 me-1"></i>Live chat inside the manager console</li>
            <li><i class="bi bi-shield-check text-emerald-500 me-1"></i>Status page: <a href="/status" class="underline hover:no-underline">/status</a></li>
            <li><i class="bi bi-infinity text-emerald-500 me-1"></i>Links expire in 15 minutes. Request as many as you need.</li>
          </ul>
        </div>
      </aside>
    </div>
  </main>
</div>

<script data-cfasync="false">
  const form = document.querySelector('form')
  const spinner = document.getElementById('spin-link')
  form?.addEventListener('submit', () => {
    spinner?.classList.remove('hidden')
  })
</script>
@endsection
