<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/jpeg" href="/images/lux-logo.png" />

    <!-- Scripts -->
    <script data-cfasync="false" src="{{ mix('/js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <style>
      html, body, #app { height: 100%; }
      body { margin:0; }
      #app { min-height: 100vh; display: flex; flex-direction: column; }
      /* Ensure content clears the fixed navbar */
      #app > main { flex: 0 0 auto; display: block; padding-top: calc(var(--nav-h, 70px) + 24px); }
      footer { margin-top: auto; }
      /* Burger and drawer styles */
      .burger{ position:relative; width:42px; height:42px; border:none; background:transparent; display:inline-flex; align-items:center; justify-content:center; margin-left:auto; cursor:pointer; outline:none }
      .burger span{ position:absolute; width:22px; height:2px; background:#fff; border-radius:2px; transition:transform .25s ease, opacity .2s ease, top .25s ease }
      .burger span:nth-child(1){ top:14px }
      .burger span:nth-child(2){ top:20px }
      .burger span:nth-child(3){ top:26px }
      .burger.open span:nth-child(1){ transform:rotate(45deg); top:20px }
      .burger.open span:nth-child(2){ opacity:0 }
      .burger.open span:nth-child(3){ transform:rotate(-45deg); top:20px }
      .burger.small span{ width:18px }
      .drawer-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:saturate(120%) blur(2px); z-index:1049; animation:fadeIn .2s ease }
      @keyframes fadeIn{ from{opacity:0} to{opacity:1} }
      .drawer{ position:fixed; top:0; left:0; height:100vh; width:min(86vw, 360px); background:#0D0D0D; color:#fff; z-index:1050; transform:translateX(-100%); transition:transform .3s cubic-bezier(.4,0,.2,1); box-shadow: 10px 0 40px rgba(0,0,0,.35) }
      .drawer.open{ transform:translateX(0) }
      .drawer-header{ padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.08) }
      .drawer-header img{ height:28px }
      .drawer-menu{ display:flex; flex-direction:column; padding:10px 6px }
      .drawer-link{ padding:10px 14px; color:#E7E7E7; text-decoration:none; border-radius:10px; display:flex; align-items:center }
      .drawer-link:hover{ background:rgba(255,255,255,.06); color:#fff }
      .drawer-cta{ margin:10px 14px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:.6rem 1rem; background:linear-gradient(135deg,#ffbe3d,#ff4e68); color:#12081d; text-decoration:none; font-weight:700 }
      .btn-link.drawer-link{ background:none; border:none; text-align:left; width:100% }
      .no-scroll { overflow:hidden }
      :root{ --glint-primary:#ff8c43; --glint-primary-dark:#1a1034; --glint-bg:#fff6f0; --glint-border:rgba(12,7,20,0.12); }
      .glint-chat-fab{ position:fixed; right:20px; bottom:20px; width:58px; height:58px; border-radius:50%; border:1px solid rgba(255,140,67,.4); background:linear-gradient(135deg,#ffbe3d,#ff4e68); color:#12081d; box-shadow:0 16px 32px rgba(10,5,20,.35); display:grid; place-items:center; cursor:pointer; z-index:1055 }
      .glint-chat-fab svg{ width:24px; height:24px }
      .glint-chat-fab:hover{ transform:translateY(-2px); transition:transform .2s ease }
      .glint-chat{ position:fixed; right:20px; bottom:20px; width:360px; max-width:calc(100vw - 40px); height:520px; background:#fff; border-radius:22px; border:1px solid var(--glint-border); box-shadow:0 40px 80px rgba(3,12,10,.25); display:none; flex-direction:column; overflow:hidden; z-index:1055 }
      .glint-chat.open{ display:flex }
      .glint-chat-header{ background:linear-gradient(135deg, rgba(255,140,67,.18), rgba(255,78,104,.45)); padding:16px; border-bottom:1px solid var(--glint-border); display:flex; align-items:center; gap:12px }
      .glint-avatar{ width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,#ffbe3d,#ff4e68); color:#0c0714; display:grid; place-items:center; font-weight:700; font-size:.95rem }
      .glint-header-copy{ display:flex; flex-direction:column; gap:2px }
      .glint-title{ font-weight:700; font-size:1rem; color:#071c18 }
      .glint-sub{ font-size:.82rem; color:#d6d0e6; margin:0 }
      .glint-close{ margin-left:auto; filter:invert(14%) sepia(12%) saturate(1564%) hue-rotate(305deg) brightness(95%); }
      .glint-messages{ flex:1; padding:16px; background:var(--glint-bg); overflow-y:auto }
      .glint-msg{ max-width:86%; padding:10px 14px; border-radius:16px; margin:6px 0 2px; line-height:1.35; font-size:.92rem; box-shadow:0 6px 18px rgba(5,11,9,.08); white-space:pre-wrap }
      .glint-user{ margin-left:auto; background:#fff; border:1px solid rgba(18,8,29,.08) }
      .glint-bot{ margin-right:auto; background:rgba(255,142,67,.12); border:1px solid rgba(255,142,67,.3) }
      .glint-time{ font-size:.68rem; color:#8b9894; margin-bottom:4px }
      .glint-input{ border-top:1px solid var(--glint-border); background:#fff; padding:12px; display:flex; gap:10px; align-items:flex-end }
      .glint-input textarea{ resize:none; min-height:48px; max-height:140px; height:auto; overflow:hidden; border-radius:14px; border:1px solid rgba(12,7,20,.15); padding:12px; font-size:.92rem; outline:none; transition:border-color .2s ease; flex:1 1 auto }
      .glint-input textarea:focus{ border-color:var(--glint-primary) }
      .glint-send{ border-radius:14px; border:none; padding:0 18px; background:linear-gradient(135deg,#ffbe3d,#ff4e68); color:#12081d; font-weight:600; box-shadow:0 8px 18px rgba(255,78,104,.25); height:48px; flex:0 0 auto }
      .glint-send:disabled{ opacity:.7; box-shadow:none }
      .glint-typing{ font-size:.8rem; color:#7d8a86; padding:0 16px 8px }
      @media (max-width:520px){
        .glint-chat-fab{ right:14px; bottom:14px }
        .glint-chat{ right:10px; left:10px; width:auto; height:70vh }
      }
    </style>
    @stack('head')
</head>
<body>
    <div id="app">
        <!-- Site-wide Navbar (matches homepage) -->
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
            <div class="@auth container-fluid px-3 @else container @endauth">
                <a class="navbar-brand" href="/">
                    <img src="/images/lux-logo.png" alt="Lux Tanning" />
                </a>
                <button class="burger" id="burger" type="button" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
                <div id="navmenu" class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav align-items-lg-center">
                        @auth
                            <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="/tenant/schedule">Schedule</a></li>
                            <li class="nav-item"><a class="nav-link" href="/tenant/customers">Customers</a></li>
                            <li class="nav-item"><a class="nav-link" href="/tenant/payments">Payments</a></li>
                            <li class="nav-item"><a class="nav-link" href="/tenant/staff">Staff</a></li>
                            <li class="nav-item ms-lg-3">
                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="/locations">Studios</a></li>
                            <li class="nav-item"><a class="nav-link" href="/membership">Membership</a></li>
                            <li class="nav-item"><a class="nav-link" href="/#technology">Technology</a></li>
                            <li class="nav-item"><a class="nav-link" href="/status">Status</a></li>
                            <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                            <li class="nav-item ms-lg-3">
                                <a class="btn btn-ghost btn-sm" href="/book"><i class="bi bi-lightning-charge me-1"></i>Book a sun bed</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile Drawer -->
        <div class="drawer-overlay" id="drawer_overlay" style="display:none"></div>
        <aside class="drawer" id="drawer" tabindex="-1">
            <div class="drawer-header d-flex align-items-center justify-content-between">
                <a href="/" class="d-inline-flex align-items-center text-decoration-none">
                    <img src="/images/lux-logo.png" alt="Lux Tanning" />
                </a>
                <button class="burger small open" id="burger_close" type="button" aria-label="Close menu"><span></span><span></span><span></span></button>
            </div>
            <nav class="drawer-menu">
                @auth
                    <a href="/dashboard" class="drawer-link"><i class="bi bi-speedometer2 me-2"></i>Overview</a>
                    <a href="/tenant/schedule" class="drawer-link"><i class="bi bi-calendar3 me-2"></i>Schedule</a>
                    <a href="/tenant/customers" class="drawer-link"><i class="bi bi-people me-2"></i>Customers</a>
                    <a href="/tenant/payments" class="drawer-link"><i class="bi bi-credit-card me-2"></i>Payments</a>
                    <a href="/tenant/invoices" class="drawer-link"><i class="bi bi-receipt me-2"></i>Invoices</a>
                    <a href="/tenant/staff" class="drawer-link"><i class="bi bi-person-badge me-2"></i>Staff</a>
                    <a href="/tenant/settings/brand" class="drawer-link"><i class="bi bi-gear me-2"></i>Settings</a>
                    <form id="drawer-logout-form" action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="drawer-link btn-link"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                @else
                    <a href="/courses" class="drawer-link">Courses</a>
                    <a href="/locations" class="drawer-link">Studios</a>
                    <a href="/membership" class="drawer-link">Membership</a>
                    <a href="/#technology" class="drawer-link">Technology</a>
                    <a href="/status" class="drawer-link">Status</a>
                    <a href="/login" class="drawer-link">Login</a>
                    <a href="/book" class="drawer-cta">Book a sun bed</a>
                @endauth
            </nav>
        </aside>

        <main>
            @yield('content')
        </main>
        <!-- Site-wide Footer (hide when logged in) -->
        @guest
        <footer>
            <div class="@auth container-fluid px-3 @else container @endauth">
                <div class="row align-items-center gy-3">
                    <div class="col-md-4 text-center text-md-start">
                        <img src="/images/lux-logo.png" alt="Lux Tanning" class="mb-2" />
                        <div class="small">© 2025 Lux Tanning Studios Ltd.</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <a href="/privacy" class="me-3">Privacy</a>
                        <a href="/terms" class="me-3">Terms</a>
                        <a href="/status">Status</a>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <a class="me-2" aria-label="Twitter" href="#"><i class="bi bi-twitter"></i></a>
                        <a class="me-2" aria-label="LinkedIn" href="#"><i class="bi bi-linkedin"></i></a>
                        <a aria-label="Instagram" href="#"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </footer>
        @endguest
    </div>
    <div id="glint-chat" data-route="{{ route('chat.glint') }}">
      <button class="glint-chat-fab" type="button" aria-label="Open Lux chat">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13"></path>
          <path d="M22 2l-7 20-4-9-9-4 20-7z"></path>
        </svg>
      </button>
      <section class="glint-chat" role="dialog" aria-label="Lux Concierge">
        <header class="glint-chat-header">
          <div class="glint-avatar">L</div>
          <div class="glint-header-copy">
            <p class="glint-title mb-0">Lux Concierge</p>
            <p class="glint-sub mb-0">Courses · Minutes · Studios</p>
          </div>
          <button class="btn-close glint-close" type="button" aria-label="Close chat"></button>
        </header>
        <main class="glint-messages" aria-live="polite"></main>
        <div class="glint-typing d-none">Lux is typing…</div>
        <footer class="glint-input">
          <textarea placeholder="Ask about Lux courses, minutes, or studios…" aria-label="Chat input"></textarea>
          <button type="button" class="glint-send">Send</button>
        </footer>
      </section>
    </div>
    <script data-cfasync="false">
      (function(){
        const html = document.documentElement;
        const burger = document.getElementById('burger');
        const drawer = document.getElementById('drawer');
        const overlay = document.getElementById('drawer_overlay');
        const closeBtn = document.getElementById('burger_close');
        function setOpen(v){
          if(!drawer||!overlay||!burger) return;
          drawer.classList.toggle('open', v);
          overlay.style.display = v ? 'block' : 'none';
          burger.classList.toggle('open', v);
          html.classList.toggle('no-scroll', v);
        }
        burger?.addEventListener('click', ()=> setOpen(!drawer.classList.contains('open')));
        closeBtn?.addEventListener('click', ()=> setOpen(false));
        overlay?.addEventListener('click', ()=> setOpen(false));
        document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') setOpen(false); });
        drawer?.addEventListener('click', (e)=>{ if(e.target.closest('a')) setOpen(false); });
      })();
      (function(){
        const chatRoot = document.getElementById('glint-chat');
        if(!chatRoot) return;
        const fab = chatRoot.querySelector('.glint-chat-fab');
        const panel = chatRoot.querySelector('.glint-chat');
        const closeBtn = chatRoot.querySelector('.glint-close');
        const textarea = chatRoot.querySelector('textarea');
        const sendBtn = chatRoot.querySelector('.glint-send');
        const messagesEl = chatRoot.querySelector('.glint-messages');
        const typingEl = chatRoot.querySelector('.glint-typing');
        const endpoint = chatRoot.getAttribute('data-route');
        const history = [];
        let autoOpened = false;
        let autoTimer = null;
        let greetingSent = false;
        let sending = false;

        const formatTime = ts => new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const appendMessage = (text, role = 'bot') => {
          const wrap = document.createElement('div');
          wrap.className = `glint-msg ${role === 'user' ? 'glint-user' : 'glint-bot'}`;
          wrap.textContent = text;
          messagesEl.appendChild(wrap);
          const time = document.createElement('div');
          time.className = 'glint-time';
          time.textContent = formatTime(Date.now());
          messagesEl.appendChild(time);
          messagesEl.scrollTop = messagesEl.scrollHeight;
        };

        const autoResize = () => {
          if(!textarea) return;
          textarea.style.height = 'auto';
          textarea.style.height = Math.min(textarea.scrollHeight, 140) + 'px';
        };

        const openPanel = (auto = false) => {
          panel.classList.add('open');
          if(!auto) textarea?.focus();
          autoResize();
          if(!greetingSent){
            greetingSent = true;
            appendMessage('Hi! I’m Lux Concierge. Ask about bookings, minute wallets, or what each studio offers.', 'bot');
          }
        };

        const closePanel = () => panel.classList.remove('open');

        fab?.addEventListener('click', () => openPanel());
        closeBtn?.addEventListener('click', closePanel);

        const fetchCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const converse = async message => {
          if(sending) return;
          const trimmed = (message || '').trim();
          if(!trimmed) return;
          appendMessage(trimmed, 'user');
          history.push({ role: 'user', text: trimmed });
          textarea.value = '';
          autoResize();
          textarea.disabled = true;
          sendBtn.disabled = true;
          typingEl?.classList.remove('d-none');
          sending = true;
          try {
            const response = await fetch(endpoint, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': fetchCsrf(),
              },
              body: JSON.stringify({ message: trimmed, history }),
            });
            const data = await response.json();
            if(!response.ok) throw new Error(data.error || 'Chat failed');
            const reply = data.reply || 'Let me double-check that for you.';
            appendMessage(reply, 'bot');
            history.push({ role: 'assistant', text: reply });
          } catch (error) {
            appendMessage('Sorry—we couldn’t reach Lux Concierge right now. Try again shortly?', 'bot');
          } finally {
            sending = false;
            textarea.disabled = false;
            sendBtn.disabled = false;
            typingEl?.classList.add('d-none');
            textarea?.focus();
            autoResize();
          }
        };

        sendBtn?.addEventListener('click', () => converse(textarea.value));
        textarea?.addEventListener('input', autoResize);
        textarea?.addEventListener('keydown', e => {
          if(e.key === 'Enter' && !e.shiftKey){
            e.preventDefault();
            converse(textarea.value);
          }
        });

        const handleFirstInteraction = () => {
          if(!autoOpened){
            autoOpened = true;
            autoTimer = window.setTimeout(() => openPanel(true), 2500);
          }
        };

        ['scroll','pointerdown','keydown'].forEach(evt => {
          window.addEventListener(evt, handleFirstInteraction, { once:true });
        });

        window.addEventListener('beforeunload', () => {
          ['scroll','pointerdown','keydown'].forEach(evt => window.removeEventListener(evt, handleFirstInteraction));
          if(autoTimer) window.clearTimeout(autoTimer);
        });

        autoResize();
      })();
    </script>
  </body>
</html>
