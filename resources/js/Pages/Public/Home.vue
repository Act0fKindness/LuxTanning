<template>
  <div class="page grain">
    <Head title="Lux Tanning Studio Strood | Premium UV Tanning">
      <meta name="description" content="Premium UV tanning in Strood. Luxura & K9S beds, stand-up tower, spotless studio standards, expert guidance. 3 Station Road, ME2 4AX." />
      <meta property="og:title" content="Lux Tanning Studio Strood | Premium UV Tanning" />
      <meta property="og:description" content="White page, black blocks, premium UV tanning. Book online, walk in, or log in to manage your sessions." />
      <meta property="og:type" content="website" />
      <meta name="theme-color" content="#ffffff" />
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
      <script type="application/ld+json" v-html="structuredData"></script>
    </Head>

    <a class="skip" href="#top">Skip to content</a>

    <header :class="['nav', { scrolled: navScrolled }]" id="nav">
      <div class="container">
        <div class="nav-inner">
          <a class="brand" href="#top" aria-label="Lux Tanning home" @click="scrollToSection($event, '#top')">
            <span class="brand-mark">
              <img src="/assets/luxtanning-logo.png" alt="Lux Tanning logo" width="40" height="40" />
            </span>
            <span class="brand-name">
              <b>Lux Tanning</b>
              <span>Strood • Premium UV Studio</span>
            </span>
          </a>

          <nav class="links" aria-label="Primary navigation">
            <a href="#why" @click="scrollToSection($event, '#why')">Why Lux</a>
            <a href="#first-time" @click="scrollToSection($event, '#first-time')">First time</a>
            <a href="#beds" @click="scrollToSection($event, '#beds')">Beds</a>
            <a href="#pricing" @click="scrollToSection($event, '#pricing')">Book</a>
            <a href="#visit" @click="scrollToSection($event, '#visit')">Visit</a>
          </nav>

          <div class="nav-cta">
            <a class="btn" href="tel:+441634713989" aria-label="Call Lux Tanning">01634 713989</a>

            <button v-if="!isLoggedIn" class="btn" type="button" aria-label="Customer login" @click="openAuth('login')">
              Login
            </button>

            <div v-else class="account" ref="accountWrapRef">
              <button class="btn" type="button" aria-label="Open account menu" @click.stop="toggleAccountMenu">
                My account
              </button>
              <div class="account-menu" role="menu" aria-label="Account menu" v-show="accountMenuOpen">
                <a :href="CONFIG.ACCOUNT_URL" role="menuitem">Account</a>
                <a href="#pricing" role="menuitem" @click="handleAccountMenuLink($event, '#pricing')">Book a session</a>
                <button type="button" role="menuitem" @click="handleLogout">Log out</button>
              </div>
            </div>

            <a class="btn primary" href="#pricing" aria-label="Book now" @click.prevent="goToBooking">Book now</a>

            <button class="btn burger" type="button" aria-label="Open menu" :aria-expanded="String(menuOpen)" @click="toggleMenu">
              <svg class="svg" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16v2H4zM4 11h16v2H4zM4 16h16v2H4z" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div class="mobile" aria-label="Mobile menu" v-show="menuOpen">
        <div class="container">
          <a href="#why" @click="handleMobileLink($event, '#why')">Why Lux <span>→</span></a>
          <a href="#first-time" @click="handleMobileLink($event, '#first-time')">First time <span>→</span></a>
          <a href="#beds" @click="handleMobileLink($event, '#beds')">Beds <span>→</span></a>
          <a href="#pricing" @click="handleMobileLink($event, '#pricing')">Book <span>→</span></a>
          <a href="#visit" @click="handleMobileLink($event, '#visit')">Visit <span>→</span></a>

          <div class="row">
            <button class="btn primary" type="button" style="width: 100%;" @click="openAuth('login')">Customer login</button>
            <a class="btn" href="tel:+441634713989" style="width: 100%; justify-content: center;">Call</a>
          </div>
        </div>
      </div>
    </header>

    <main id="top" class="hero">
      <div class="container">
        <div class="block hero-wrap reveal">
          <div class="hero-grid">
            <div class="hero-left">
              <div>
                <div class="hero-kicker">
                  <span class="chip"><span class="dot"></span> Open daily</span>
                  <span class="chip">Mon–Fri 9–9 • Sat 9–8 • Sun 10–6</span>
                  <span class="chip">3 Station Road • ME2 4AX</span>
                </div>

                <h1 class="hero-title">White page.<br />Black-tie glow.</h1>
                <p class="hero-sub">
                  Premium UV tanning in Strood with a sleek, spotless setup and a bed line-up built for consistent results.
                  Walk in, book online, or log in to manage your sessions.
                </p>

                <div class="hero-actions">
                  <a class="btn primary" href="#pricing" id="bookHero" @click.prevent="goToBooking">Book online</a>
                  <button class="btn" type="button" id="loginHero" @click="openAuth('login')">Customer login</button>
                  <a class="btn" href="#first-time" @click="scrollToSection($event, '#first-time')">First time? Start here</a>
                </div>

                <div class="hero-metrics">
                  <div class="metric">
                    <b>Premium beds</b>
                    <span>Luxura + K9S for comfort and performance.</span>
                  </div>
                  <div class="metric">
                    <b>Stand-up option</b>
                    <span>MegaSun tower for quick sessions.</span>
                  </div>
                  <div class="metric">
                    <b>Lotions available</b>
                    <span>Pick the right one for your skin goals.</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="hero-right" aria-label="Studio hero image">
              <div class="hero-card">
                <div class="mini">
                  <b>First time package</b>
                  <span>Consultation + tan session. One clean start.</span>
                </div>
                <a class="btn primary" href="#pricing" @click.prevent="goToBooking">Book first time</a>
              </div>
            </div>
          </div>
        </div>

        <div class="divider"></div>
      </div>
    </main>

    <section id="why" class="section loose">
      <div class="container">
        <div class="block offset-left">
          <div class="block-inner">
            <div class="section-head reveal">
              <div>
                <div class="tag"><i></i> Studio standard</div>
                <h2>Luxury, without trying too hard</h2>
                <p>Minimal, clean, premium — the kind of place you don’t have to “brace yourself” to walk into.</p>
              </div>
              <div class="section-note">One studio. One standard.<br />Every time.</div>
            </div>

            <div class="why-layout">
              <div class="why-copy reveal">
                <div class="kicker"><i></i> The promise</div>
                <h3 class="copy-title">Clean rooms. Calm vibe. Better results.</h3>
                <p class="copy-sub">
                  We keep it simple: a spotless flow, guidance that’s actually useful, and premium equipment that delivers consistency.
                  No drama. No weird upsell energy. Just “in, tan, out”.
                </p>

                <div class="why-points">
                  <div class="point">
                    <span class="badge-dot"></span>
                    <div><b>Hygiene first</b><span>Wiped down properly, every session. Like it should be.</span></div>
                  </div>
                  <div class="point">
                    <span class="badge-dot"></span>
                    <div><b>Guidance (if you want it)</b><span>Skin type, timings, routine, lotions. No guessing games.</span></div>
                  </div>
                  <div class="point">
                    <span class="badge-dot"></span>
                    <div><b>Consistency</b><span>Same standard. Same clean experience. Every visit.</span></div>
                  </div>
                </div>

                <div class="btn-row">
                  <a class="btn primary" href="#first-time" @click="scrollToSection($event, '#first-time')">Start properly</a>
                  <a class="btn" href="#beds" @click="scrollToSection($event, '#beds')">See the beds</a>
                </div>
              </div>

              <div class="grid why-cards">
                <article class="card tall reveal">
                  <div class="card-pad">
                    <div class="kicker"><i></i> Results</div>
                    <h3>Consistent glow</h3>
                    <p>Comfort-first beds with performance in mind. No chaos. No guesswork. Just a better finish.</p>
                    <div class="divider" style="margin: 16px 0;"></div>
                    <div class="info-grid">
                      <div class="info">
                        <b>New to UV?</b>
                        <span>We’ll set a routine you can actually follow.</span>
                      </div>
                      <div class="info">
                        <b>Regular tanner?</b>
                        <span>Book, walk in, or manage everything via login.</span>
                      </div>
                    </div>
                  </div>
                </article>

                <article class="card reveal">
                  <div class="card-pad">
                    <div class="kicker"><i></i> Studio</div>
                    <h3>Spotless setup</h3>
                    <p>Clean rooms, tidy flow, and a calm feel — because “premium” should be obvious immediately.</p>
                  </div>
                </article>

                <article class="card reveal">
                  <div class="card-pad">
                    <div class="kicker"><i></i> Guidance</div>
                    <h3>Expert help</h3>
                    <p>Switching your routine? We’ll help you pick the right plan and lotion for your skin goals.</p>
                  </div>
                </article>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="first-time" class="section">
      <div class="container">
        <div class="block offset-right">
          <div class="block-inner">
            <div class="section-head reveal">
              <div>
                <div class="tag"><i></i> First time</div>
                <h2>Start properly (and actually tan)</h2>
                <p>No awkward guessing. We guide your skin type, set your routine, then you get your first tan session too.</p>
              </div>
              <div class="section-note">Consult + tan.<br />Done.</div>
            </div>

            <div class="first">
              <div class="card reveal">
                <div class="card-pad">
                  <div class="kicker"><i></i> First time flow</div>
                  <h3>Three steps, zero drama</h3>
                  <p>Fast, clean, and genuinely helpful — because “good luck” isn’t a service.</p>

                  <div class="steps">
                    <div class="step">
                      <div class="num">1</div>
                      <div>
                        <b>Quick consultation</b>
                        <span>We check skin type, goals, and what you’ve done before (if anything).</span>
                      </div>
                    </div>

                    <div class="step">
                      <div class="num">2</div>
                      <div>
                        <b>Set your plan</b>
                        <span>Recommended routine + timings, plus lotion guidance if you want it.</span>
                      </div>
                    </div>

                    <div class="step">
                      <div class="num">3</div>
                      <div>
                        <b>First tan session</b>
                        <span>You tan. Not just talk about tanning. Iconic behaviour, really.</span>
                      </div>
                    </div>
                  </div>

                  <div class="btn-row">
                    <a class="btn primary" href="#pricing" @click.prevent="goToBooking">Book first time package</a>
                    <button class="btn" type="button" @click="openAuth('login')">Login</button>
                  </div>

                  <p class="section-note warning">
                    UV tanning carries risks. Follow guidance and recommended exposure times.
                  </p>
                </div>
              </div>

              <div class="first-visual reveal" aria-label="First time visual">
                <div class="price-sticker">
                  <b>Starter</b>
                  <span>£70</span>
                </div>

                <div class="first-float">
                  <div>
                    <b>First Time Starter</b>
                    <span>Consultation (£20) + UV session (£50)</span>
                  </div>
                  <a class="btn primary" href="#pricing" @click.prevent="goToBooking">Book</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="beds" class="section loose">
      <div class="container">
        <div class="block offset-left">
          <div class="block-inner">
            <div class="section-head reveal">
              <div>
                <div class="tag"><i></i> Equipment</div>
                <h2>The bed line-up</h2>
                <p>Premium options — laydown comfort, high-performance sessions, and a stand-up tower for quick visits.</p>
              </div>
              <div class="section-note">Clean look.<br />Clean result.</div>
            </div>

            <div class="beds-layout">
              <div class="gallery">
                <div class="tile reveal tall-tile">
                  <img src="/assets/bed-1.jpg" alt="K9S tanning bed" loading="lazy" />
                  <div class="tile-label">
                    <div>
                      <b>K9S</b>
                      <span>High-performance feel, comfort-first session.</span>
                    </div>
                    <div class="badge">Signature</div>
                  </div>
                </div>

                <div class="stack">
                  <div class="tile reveal">
                    <img src="/assets/bed-2.jpg" alt="Luxura tanning bed" loading="lazy" />
                    <div class="tile-label">
                      <div>
                        <b>Luxura</b>
                        <span>Premium comfort for regular sessions.</span>
                      </div>
                      <div class="badge">Laydown</div>
                    </div>
                  </div>

              <div class="tile reveal">
                <img src="/assets/megasun-tower.jpg" alt="Stand-up tanning tower" loading="lazy" />
                    <div class="tile-label">
                      <div>
                        <b>MegaSun Tower</b>
                        <span>Stand-up option for fast visits.</span>
                      </div>
                      <div class="badge">Stand-up</div>
                    </div>
                  </div>
                </div>
              </div>

              <aside class="beds-rail reveal" aria-label="Bed details">
                <div>
                  <div class="kicker"><i></i> Quick picks</div>

                  <div class="rail-item">
                    <b>Want quick?</b>
                    <span>Stand-up tower sessions are great when you’re in a rush.</span>
                  </div>

                  <div class="rail-item">
                    <b>Want comfort?</b>
                    <span>Laydown beds for a calm, consistent routine.</span>
                  </div>

                  <div class="rail-item">
                    <b>Want guidance?</b>
                    <span>We’ll help you match timings and lotion to your skin goals.</span>
                  </div>
                </div>

                <div class="rail-cta">
                  <a class="btn primary" href="#pricing" id="bookBeds" @click.prevent="goToBooking">Book online</a>
                  <a class="btn" href="#first-time" @click="scrollToSection($event, '#first-time')">First time</a>
                </div>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section tight">
      <div class="container">
        <div class="strip reveal">
          <div class="strip-inner">
            <div>
              <strong>Ready when you are.</strong>
              <span class="strip-copy">Book online in seconds, or call if you prefer a human.</span>
            </div>
            <div class="btn-row">
              <a class="btn primary" href="#pricing" id="bookStrip" @click.prevent="goToBooking">Book now</a>
              <a class="btn" href="tel:+441634713989">Call 01634 713989</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="pricing" class="section">
      <div class="container">
        <div class="block offset-right">
          <div class="block-inner">
            <div class="section-head reveal">
              <div>
                <div class="tag"><i></i> Booking</div>
                <h2>Book online</h2>
                <p>Choose a service and book in seconds. If you’ve got an account, log in to manage bookings and details.</p>
              </div>
              <div class="section-note">Or call:<br /><a class="link" href="tel:+441634713989">01634 713989</a></div>
            </div>

            <div class="pricing">
              <article class="card reveal price-card featured">
                <div class="price">
                  <div class="kicker"><i></i> Best first visit</div>
                  <h3 class="price-title">First Time Starter</h3>
                  <div class="amount">£70</div>
                  <div class="fine">Consultation (£20) + UV session (£50) • Start properly, then tan.</div>

                  <div class="list">
                    <div class="li"><span class="tick">✓</span>Skin-type guidance</div>
                    <div class="li"><span class="tick">✓</span>Routine + timings set</div>
                    <div class="li"><span class="tick">✓</span>Includes tan session</div>
                  </div>

                  <div class="price-spacer"></div>

                  <div class="price-actions">
                    <a class="btn primary" href="#" id="bookFirstTime" @click.prevent="goToBooking">Book now</a>
                    <button class="btn" type="button" @click="openAuth('login')">Login</button>
                  </div>

                  <p class="section-note" style="text-align: left;">
                    If you’re new, this is the one. If you’re not new… you can still take it. We won’t tell anyone.
                  </p>
                </div>
              </article>

              <article class="card reveal price-card">
                <div class="price">
                  <div class="kicker"><i></i> Start smart</div>
                  <h3>Tan Consultation</h3>
                  <div class="amount">£20</div>
                  <div class="fine">30 minutes • Ideal if you’re new or changing routine.</div>
                  <div class="list">
                    <div class="li"><span class="tick">✓</span>Skin-type guidance</div>
                    <div class="li"><span class="tick">✓</span>Recommended routine</div>
                    <div class="li"><span class="tick">✓</span>Lotion suggestions</div>
                  </div>
                  <div class="price-spacer"></div>
                  <div class="price-actions">
                    <a class="btn primary" href="#" id="bookConsult" @click.prevent="goToBooking">Book now</a>
                    <button class="btn" type="button" @click="openAuth('login')">Login</button>
                  </div>
                </div>
              </article>

              <article class="card reveal price-card">
                <div class="price">
                  <div class="kicker"><i></i> Optimise</div>
                  <h3>Tanning Techniques</h3>
                  <div class="amount">£40</div>
                  <div class="fine">Guidance session • Great if you want to level up results.</div>
                  <div class="list">
                    <div class="li"><span class="tick">✓</span>Routine refinement</div>
                    <div class="li"><span class="tick">✓</span>Best practices</div>
                    <div class="li"><span class="tick">✓</span>Consistency focus</div>
                  </div>
                  <div class="price-spacer"></div>
                  <div class="price-actions">
                    <a class="btn primary" href="#" id="bookTech" @click.prevent="goToBooking">Book now</a>
                    <button class="btn" type="button" @click="openAuth('login')">Login</button>
                  </div>
                </div>
              </article>

              <article class="card reveal price-card">
                <div class="price">
                  <div class="kicker"><i></i> Classic</div>
                  <h3>UV Tanning Session</h3>
                  <div class="amount">£50</div>
                  <div class="fine">1 hour • For those who know what they want.</div>
                  <div class="list">
                    <div class="li"><span class="tick">✓</span>Premium session</div>
                    <div class="li"><span class="tick">✓</span>Comfort-first</div>
                    <div class="li"><span class="tick">✓</span>Optional lotions</div>
                  </div>
                  <div class="price-spacer"></div>
                  <div class="price-actions">
                    <a class="btn primary" href="#" id="bookUV" @click.prevent="goToBooking">Book now</a>
                    <button class="btn" type="button" @click="openAuth('login')">Login</button>
                  </div>
                </div>
              </article>
            </div>

            <p class="section-note reveal" style="text-align: left;">
              If you want minutes / bundles / memberships instead of these services, say the word and I’ll swap the whole block.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section id="visit" class="section">
      <div class="container">
        <div class="block offset-left">
          <div class="block-inner">
            <div class="section-head reveal">
              <div>
                <div class="tag"><i></i> Location</div>
                <h2>Visit the studio</h2>
                <p>3 Station Road, Strood, Kent, ME2 4AX. Easy to get to, easy to get in and out.</p>
              </div>
              <div class="section-note">Open daily<br />Long hours</div>
            </div>

            <div class="visit">
              <div class="tile reveal" style="overflow: hidden;">
                <iframe
                  class="map"
                  title="Map to Lux Tanning, Strood"
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                  src="https://www.google.com/maps?q=3%20Station%20Road%2C%20Strood%2C%20ME2%204AX&output=embed"
                ></iframe>
              </div>

              <div class="card reveal">
                <div class="contact">
                  <div class="kicker"><i></i> Details</div>
                  <h3>Lux Tanning Studio</h3>
                  <div class="row">
                    <div class="info">
                      <b>Address</b>
                      <span>3 Station Road, Strood, Kent, ME2 4AX</span>
                    </div>
                    <div class="info">
                      <b>Phone</b>
                      <span><a class="link" href="tel:+441634713989">01634 713989</a></span>
                    </div>
                    <div class="info">
                      <b>Opening times</b>
                      <span>Mon–Fri 9am–9pm • Sat 9am–8pm • Sun 10am–6pm</span>
                    </div>
                  </div>

                  <div class="btn-row">
                    <a class="btn primary" href="#pricing" @click.prevent="goToBooking">Book now</a>
                    <button class="btn" type="button" @click="openAuth('login')">Customer login</button>
                  </div>

                  <p class="section-note warning">
                    UV tanning carries risks. Follow guidance and recommended exposure times.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer>
      <div class="footer-top">
        <div class="container">
          <div class="footer-grid">
            <div>
              <div class="footer-brand">
                <div class="mark">
                  <img src="/assets/luxtanning-logo.png" alt="Lux Tanning logo" />
                </div>
                <div>
                  <div class="footer-name">Lux Tanning</div>
                  <div class="footer-sub">Strood • Kent</div>
                </div>
              </div>
              <div class="footer-desc">
                Premium UV tanning with a clean studio standard and a bed line-up built for consistent results.
              </div>
            </div>

            <div class="col">
              <h4>Studio</h4>
              <a href="#why" @click="scrollToSection($event, '#why')">Why Lux</a>
              <a href="#first-time" @click="scrollToSection($event, '#first-time')">First time</a>
              <a href="#beds" @click="scrollToSection($event, '#beds')">Beds</a>
              <a href="#pricing" @click="scrollToSection($event, '#pricing')">Book</a>
              <a href="#visit" @click="scrollToSection($event, '#visit')">Visit</a>
            </div>

            <div class="col">
              <h4>Customers</h4>
              <a href="#" @click.prevent="openAuth('login')">Login</a>
              <a href="#" @click.prevent="openAuth('register')">Create account</a>
              <a href="#pricing" @click="scrollToSection($event, '#pricing')">Services</a>
              <a href="tel:+441634713989">Call</a>
            </div>

            <div class="col">
              <h4>Legal</h4>
              <a href="#">Privacy</a>
              <a href="#">Terms</a>
              <a href="#">UV Safety</a>
              <a href="#">Cookies</a>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="container footer-bottom-inner">
          <span>© {{ year }} Lux Tanning Studio. All rights reserved.</span>
          <span>White page. Black blocks. One accent. No chaos.</span>
        </div>
      </div>
    </footer>

    <div class="modal" :class="{ on: showAuth }" id="authModal" role="dialog" aria-label="Customer login" :aria-hidden="showAuth ? 'false' : 'true'" @click.self="closeAuth">
      <div class="modal-panel">
        <div class="modal-left">
          <div class="copy">
            <b>Customer account</b>
            <span>Log in to manage bookings and details. If you don’t have an account, create one in seconds.</span>
          </div>
        </div>

        <div class="modal-right">
          <div class="modal-top">
            <div class="tabs" role="tablist">
              <button class="tab" :class="{ on: authMode === 'login' }" type="button" role="tab" :aria-selected="authMode === 'login'" @click="setAuthMode('login')">Login</button>
              <button class="tab" :class="{ on: authMode === 'register' }" type="button" role="tab" :aria-selected="authMode === 'register'" @click="setAuthMode('register')">Create account</button>
            </div>
            <button class="x" type="button" aria-label="Close" @click="closeAuth">✕</button>
          </div>

          <div class="form" v-show="authMode === 'login'">
            <h3>Welcome back</h3>
            <p>Use your email to sign in. (Hook this up to your real auth endpoint when ready.)</p>

            <div class="fields">
              <div class="field">
                <label for="loginEmail">Email</label>
                <input id="loginEmail" ref="loginEmailRef" v-model="loginForm.email" type="email" autocomplete="email" placeholder="you@example.com" />
              </div>
              <div class="field">
                <label for="loginPass">Password</label>
                <input id="loginPass" v-model="loginForm.password" type="password" autocomplete="current-password" placeholder="••••••••" />
              </div>
            </div>

            <div class="form-actions">
              <button class="btn primary" id="doLogin" type="button" @click="handleLogin">Login</button>
              <a class="smalllink" href="#" id="forgotLink" @click.prevent="handleForgot">Forgot password</a>
            </div>

            <div class="msg" :class="[{ on: !!loginMsg.text }, loginMsg.type === 'ok' ? 'ok' : 'err']">
              {{ loginMsg.text }}
            </div>
          </div>

          <div class="form" v-show="authMode === 'register'">
            <h3>Create your account</h3>
            <p>Save time next visit. Book faster. Manage your details.</p>

            <div class="fields">
              <div class="field">
                <label for="regName">Name</label>
                <input id="regName" ref="registerNameRef" v-model="registerForm.name" type="text" autocomplete="name" placeholder="Your name" />
              </div>
              <div class="field">
                <label for="regEmail">Email</label>
                <input id="regEmail" v-model="registerForm.email" type="email" autocomplete="email" placeholder="you@example.com" />
              </div>
              <div class="field">
                <label for="regPass">Password</label>
                <input id="regPass" v-model="registerForm.password" type="password" autocomplete="new-password" placeholder="Create a password" />
              </div>
            </div>

            <div class="form-actions">
              <button class="btn primary" id="doRegister" type="button" @click="handleRegister">Create account</button>
              <button class="btn" id="switchToLogin" type="button" @click="setAuthMode('login')">I already have one</button>
            </div>

            <div class="msg" :class="[{ on: !!registerMsg.text }, registerMsg.type === 'ok' ? 'ok' : 'err']">
              {{ registerMsg.text }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'

const CONFIG = Object.freeze({
  BOOKING_URL: 'https://www.luxtanningstrood.com/book-online',
  ACCOUNT_URL: '/account',
  LOGIN_URL: null,
  REGISTER_URL: null,
  LOGOUT_URL: null,
})

const menuOpen = ref(false)
const navScrolled = ref(false)
const showAuth = ref(false)
const authMode = ref('login')
const accountMenuOpen = ref(false)
const year = new Date().getFullYear()
const loginForm = reactive({ email: '', password: '' })
const registerForm = reactive({ name: '', email: '', password: '' })
const loginMsg = reactive({ text: '', type: 'ok' })
const registerMsg = reactive({ text: '', type: 'ok' })
const loginEmailRef = ref(null)
const registerNameRef = ref(null)
const accountWrapRef = ref(null)
const structuredData = JSON.stringify(
  {
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: 'Lux Tanning Studio',
    telephone: '+44 1634 713989',
    address: {
      '@type': 'PostalAddress',
      streetAddress: '3 Station Road',
      addressLocality: 'Strood',
      addressRegion: 'Kent',
      postalCode: 'ME2 4AX',
      addressCountry: 'GB',
    },
    openingHours: ['Mo-Fr 09:00-21:00', 'Sa 09:00-20:00', 'Su 10:00-18:00'],
  },
  null,
  2,
)

const getStoredUser = () => {
  if (typeof window === 'undefined') return null
  try {
    const raw = window.localStorage.getItem('lux_user')
    return raw ? JSON.parse(raw) : null
  } catch (error) {
    console.warn('Unable to read saved user', error)
    return null
  }
}

const user = ref(getStoredUser())
const isLoggedIn = computed(() => !!user.value)
let observer

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

const scrollToSection = (event, selector) => {
  if (event) event.preventDefault()
  if (typeof window === 'undefined' || !selector) return
  const target = document.querySelector(selector)
  if (!target) return
  const offset = 76
  const y = target.getBoundingClientRect().top + window.scrollY - offset
  window.scrollTo({ top: y, behavior: 'smooth' })
}

const handleMobileLink = (event, selector) => {
  scrollToSection(event, selector)
  menuOpen.value = false
}

const goToBooking = () => {
  if (typeof window === 'undefined') return
  window.location.href = CONFIG.BOOKING_URL
}

const setAuthMode = (mode) => {
  authMode.value = mode
  clearMessage(loginMsg)
  clearMessage(registerMsg)
  nextTick(() => {
    if (mode === 'login') {
      loginEmailRef.value?.focus()
    } else {
      registerNameRef.value?.focus()
    }
  })
}

const openAuth = (mode = 'login') => {
  setAuthMode(mode)
  showAuth.value = true
}

const closeAuth = () => {
  showAuth.value = false
}

const handleForgot = () => {
  setMessage(loginMsg, 'Hook this to your password reset page (e.g. /forgot-password).', 'ok')
}

const toggleAccountMenu = () => {
  accountMenuOpen.value = !accountMenuOpen.value
}

const handleAccountMenuLink = (event, selector) => {
  scrollToSection(event, selector)
  accountMenuOpen.value = false
}

const setMessage = (target, text, type = 'ok') => {
  target.text = text
  target.type = type === 'ok' ? 'ok' : 'err'
}

const clearMessage = (target) => {
  target.text = ''
  target.type = 'ok'
}

const apiCall = async (url, payload) => {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })
  if (!res.ok) throw new Error('Request failed')
  return await res.json()
}

const setLoggedInUser = (data) => {
  user.value = data
  if (typeof window === 'undefined') return
  if (data) {
    window.localStorage.setItem('lux_user', JSON.stringify(data))
  } else {
    window.localStorage.removeItem('lux_user')
  }
}

const handleLogin = async () => {
  clearMessage(loginMsg)
  const email = loginForm.email.trim()
  const password = loginForm.password.trim()
  if (!email || !password) return setMessage(loginMsg, 'Please enter email and password.', 'err')
  try {
    if (CONFIG.LOGIN_URL) {
      const response = await apiCall(CONFIG.LOGIN_URL, { email, password })
      setLoggedInUser({ name: response.name || 'Customer', email })
    } else {
      if (!email.includes('@')) return setMessage(loginMsg, 'That email looks suspicious. Try again.', 'err')
      setLoggedInUser({ name: 'Customer', email })
    }
    setMessage(loginMsg, 'Logged in. Your glow awaits.', 'ok')
    setTimeout(() => {
      closeAuth()
      accountMenuOpen.value = false
    }, 450)
  } catch (error) {
    console.error(error)
    setMessage(loginMsg, 'Login failed. Check details and try again.', 'err')
  }
}

const handleRegister = async () => {
  clearMessage(registerMsg)
  const name = registerForm.name.trim()
  const email = registerForm.email.trim()
  const password = registerForm.password.trim()
  if (!name || !email || !password) return setMessage(registerMsg, 'Fill in all fields to create your account.', 'err')
  try {
    if (CONFIG.REGISTER_URL) {
      const response = await apiCall(CONFIG.REGISTER_URL, { name, email, password })
      setLoggedInUser({ name: response.name || name, email })
    } else {
      if (!email.includes('@') || password.length < 6) {
        return setMessage(registerMsg, 'Use a valid email and a stronger password.', 'err')
      }
      setLoggedInUser({ name, email })
    }
    setMessage(registerMsg, 'Account created. You’re in.', 'ok')
    setTimeout(() => {
      closeAuth()
      accountMenuOpen.value = false
    }, 450)
  } catch (error) {
    console.error(error)
    setMessage(registerMsg, 'Couldn’t create the account. Try again.', 'err')
  }
}

const handleLogout = async () => {
  if (CONFIG.LOGOUT_URL) {
    try {
      await apiCall(CONFIG.LOGOUT_URL, {})
    } catch (error) {
      console.error(error)
    }
  }
  setLoggedInUser(null)
  accountMenuOpen.value = false
}

const handleScroll = () => {
  if (typeof window === 'undefined') return
  navScrolled.value = window.scrollY > 6
}

const handleOutsideClick = (event) => {
  if (!accountMenuOpen.value) return
  const wrap = accountWrapRef.value
  if (wrap && !wrap.contains(event.target)) {
    accountMenuOpen.value = false
  }
}

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    if (showAuth.value) closeAuth()
    else if (menuOpen.value) menuOpen.value = false
    else if (accountMenuOpen.value) accountMenuOpen.value = false
  }
}

onMounted(() => {
  if (typeof window === 'undefined') return
  handleScroll()
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('click', handleOutsideClick)
  window.addEventListener('keydown', handleKeydown)
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('on')
      })
    },
    { threshold: 0.12 },
  )
  document.querySelectorAll('.reveal').forEach((el) => observer.observe(el))
})

onBeforeUnmount(() => {
  if (typeof window === 'undefined') return
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('click', handleOutsideClick)
  window.removeEventListener('keydown', handleKeydown)
  observer?.disconnect()
})

watch(showAuth, (val) => {
  if (typeof document === 'undefined') return
  document.body.style.overflow = val ? 'hidden' : ''
  if (!val) {
    clearMessage(loginMsg)
    clearMessage(registerMsg)
  }
})
</script>

<style scoped>
.page {
  --page: #ffffff;
  --ink: #070707;
  --blk: #0a0a0a;
  --blk2: #101010;
  --text: #ffffff;
  --muted: rgba(255, 255, 255, 0.74);
  --muted2: rgba(255, 255, 255, 0.56);
  --line: rgba(255, 255, 255, 0.1);
  --line2: rgba(255, 255, 255, 0.16);
  --accent: #7c3aed;
  --accent-soft: rgba(124, 58, 237, 0.18);
  --radius-xl: 28px;
  --radius-lg: 20px;
  --radius-md: 14px;
  --shadow: 0 28px 80px rgba(0, 0, 0, 0.22);
  --shadow2: 0 18px 50px rgba(0, 0, 0, 0.18);
  --max: 1180px;
  --navH: 78px;
  position: relative;
  min-height: 100%;
  background: var(--page);
  color: var(--ink);
  font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  overflow-x: hidden;
  padding-bottom: 40px;
}

.page * {
  box-sizing: border-box;
}

.page::after {
  content: '';
  position: fixed;
  inset: -20%;
  pointer-events: none;
  background:
    radial-gradient(700px 480px at 12% 10%, rgba(124, 58, 237, 0.06), transparent 60%),
    radial-gradient(680px 460px at 86% 22%, rgba(124, 58, 237, 0.05), transparent 62%),
    radial-gradient(820px 520px at 55% 96%, rgba(124, 58, 237, 0.04), transparent 62%);
  z-index: 0;
  filter: saturate(1.1);
}

.page a {
  color: inherit;
  text-decoration: none;
}

.page img {
  max-width: 100%;
  display: block;
}

.page .container {
  max-width: var(--max);
  margin: 0 auto;
  padding: 0 22px;
}

.grain::before {
  content: '';
  position: fixed;
  inset: -30%;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.7' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='220' height='220' filter='url(%23n)' opacity='.35'/%3E%3C/svg%3E");
  opacity: 0.03;
  transform: rotate(6deg);
  z-index: 0;
}

.skip {
  position: absolute;
  left: -9999px;
  top: 12px;
  background: #fff;
  color: #000;
  border-radius: 12px;
  padding: 10px 12px;
  border: 1px solid rgba(0, 0, 0, 0.14);
  z-index: 200;
}

.skip:focus {
  left: 12px;
}

.nav {
  position: sticky;
  top: 0;
  z-index: 50;
  height: var(--navH);
  background: #000;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav.scrolled {
  border-bottom-color: rgba(255, 255, 255, 0.18);
}

.nav-inner {
  height: var(--navH);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 240px;
  color: #fff;
}

.brand-mark {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  overflow: hidden;
  border-radius: 0;
  border: none;
  background: transparent;
  box-shadow: none;
}

.brand-mark img {
  width: 40px !important;
  height: 40px !important;
  object-fit: contain;
}

.brand-name {
  display: flex;
  flex-direction: column;
  line-height: 1.05;
}

.brand-name b {
  font-family: 'Times New Roman', Times, serif;
  font-weight: 700;
  letter-spacing: 0.2px;
  font-size: 20px;
  color: #fff;
}

.brand-name span {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.62);
  margin-top: 4px;
}

.links {
  display: flex;
  align-items: center;
  gap: 18px;
  color: rgba(255, 255, 255, 0.82);
  font-weight: 650;
  font-size: 14px;
}

.links a {
  padding: 10px 10px;
  border-radius: 14px;
  transition: background 0.18s ease, color 0.18s ease;
}

.links a:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.nav-cta {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 360px;
  justify-content: flex-end;
  color: #fff;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.03);
  color: #fff;
  font-weight: 750;
  font-size: 14px;
  transition: transform 0.14s ease, background 0.18s ease, border-color 0.18s ease;
  white-space: nowrap;
  cursor: pointer;
}

.btn:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.22);
}

.btn:active {
  transform: translateY(0);
}

.btn.primary {
  border-color: rgba(124, 58, 237, 0.55);
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.42), rgba(124, 58, 237, 0.14));
  box-shadow: 0 18px 55px rgba(124, 58, 237, 0.16);
}

.btn.primary:hover {
  border-color: rgba(124, 58, 237, 0.78);
  background: linear-gradient(135deg, rgba(124, 58, 237, 0.54), rgba(124, 58, 237, 0.18));
}

.btn.icon,
.burger {
  width: 44px;
  height: 44px;
  padding: 0;
  border-radius: 999px;
}

.burger {
  display: none;
}

.svg {
  width: 20px;
  height: 20px;
  fill: currentColor;
}

.mobile {
  display: none;
  position: fixed;
  top: var(--navH);
  left: 0;
  right: 0;
  padding: 14px 0 18px;
  background: rgba(0, 0, 0, 0.96);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 80;
}

.mobile a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 14px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.82);
  font-weight: 750;
  margin-top: 10px;
}

.mobile a:hover {
  border-color: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.mobile .row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 10px;
}

.block {
  position: relative;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02)), var(--blk);
  color: var(--text);
  border-radius: var(--radius-xl);
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: var(--shadow);
  overflow: hidden;
  z-index: 1;
}

.block::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
  opacity: 0.85;
}

.block-inner {
  padding: 28px;
}

.block.offset-left {
  transform: translateX(-10px);
}

.block.offset-right {
  transform: translateX(10px);
}

.section {
  position: relative;
  z-index: 1;
  padding: 46px 0;
}

.section.tight {
  padding: 34px 0;
}

.section.loose {
  padding: 66px 0;
}

.divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
  margin: 18px 0 0;
}

.section-head {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-end;
  margin-bottom: 18px;
}

.section-head h2 {
  margin: 0;
  font-family: Sora, Inter, sans-serif;
  font-size: clamp(22px, 2.4vw, 34px);
  letter-spacing: -0.02em;
  font-weight: 820;
  color: var(--text);
}

.section-head p {
  margin: 10px 0 0;
  color: var(--muted);
  line-height: 1.75;
  max-width: 72ch;
  font-size: 15px;
}

.section-note {
  color: var(--muted2);
  font-size: 13px;
  line-height: 1.5;
  text-align: right;
}

.tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  color: rgba(255, 255, 255, 0.78);
  font-weight: 800;
  font-size: 12px;
  letter-spacing: 0.02em;
  margin-bottom: 10px;
}

.tag i {
  width: 8px;
  height: 8px;
  border-radius: 99px;
  background: var(--accent);
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
  display: inline-block;
}

.hero {
  padding: 28px 0 10px;
  position: relative;
  z-index: 1;
}

.hero-grid {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  align-items: stretch;
  min-height: 650px;
}

.hero-left {
  padding: 46px 46px 40px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  justify-content: space-between;
  background:
    radial-gradient(900px 520px at 8% 18%, rgba(124, 58, 237, 0.14), transparent 58%),
    radial-gradient(720px 460px at 38% 88%, rgba(124, 58, 237, 0.1), transparent 60%),
    #000;
}

.hero-kicker {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  color: rgba(255, 255, 255, 0.84);
  font-weight: 700;
  font-size: 13px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.03);
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 99px;
  background: var(--accent);
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
}

.hero-title {
  margin: 18px 0 0;
  font-family: Sora, Inter, sans-serif;
  font-size: clamp(40px, 4.6vw, 66px);
  font-weight: 840;
  letter-spacing: -0.035em;
  line-height: 1.01;
}

.hero-sub {
  margin: 14px 0 0;
  color: rgba(255, 255, 255, 0.74);
  font-size: 16px;
  line-height: 1.75;
  max-width: 62ch;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.hero-metrics {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.metric {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  padding: 14px 14px 12px;
}

.metric b {
  display: block;
  font-size: 13px;
  letter-spacing: 0.2px;
}

.metric span {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.66);
  line-height: 1.45;
}

.hero-right {
  position: relative;
  background: #000;
  min-height: 420px;
}

.hero-right::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url('/assets/hero.jpg') center / cover no-repeat;
  transform: scale(1.02);
}

.hero-right::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 65% 35%, rgba(124, 58, 237, 0.36), transparent 55%),
    linear-gradient(180deg, rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.92));
  mix-blend-mode: screen;
  opacity: 0.68;
}

.hero-card {
  position: absolute;
  left: 18px;
  right: 18px;
  bottom: 18px;
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(0, 0, 0, 0.72);
  backdrop-filter: blur(10px);
  padding: 16px;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  z-index: 2;
}

.hero-card .mini {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.hero-card .mini b {
  font-size: 13px;
}

.hero-card .mini span {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.35;
}

.grid {
  display: grid;
  gap: 14px;
}

.card {
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
  box-shadow: var(--shadow2);
  overflow: hidden;
  transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
  color: var(--text);
}

.card:hover {
  transform: translateY(-3px);
  border-color: rgba(255, 255, 255, 0.18);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.025));
}

.card-pad {
  padding: 18px;
}

.kicker {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(0, 0, 0, 0.5);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.78);
  margin-bottom: 12px;
  font-weight: 800;
}

.kicker i {
  width: 8px;
  height: 8px;
  border-radius: 99px;
  background: var(--accent);
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
}

.card h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 840;
  letter-spacing: -0.01em;
  color: var(--text);
}

.card p {
  margin: 10px 0 0;
  color: var(--muted);
  font-size: 14px;
  line-height: 1.75;
}

.info-grid {
  display: grid;
  gap: 10px;
  margin-top: 10px;
}

.info {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.45);
  padding: 14px;
}

.info b {
  display: block;
  font-size: 13px;
}

.info span {
  display: block;
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  line-height: 1.55;
}

.why-layout {
  display: grid;
  grid-template-columns: 0.95fr 1.05fr;
  gap: 14px;
  align-items: stretch;
}

.why-copy {
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  padding: 18px;
  box-shadow: var(--shadow2);
}

.copy-title {
  margin: 0;
  font-size: 20px;
  font-weight: 860;
  letter-spacing: -0.01em;
}

.copy-sub {
  margin-top: 10px;
  color: var(--muted);
  font-size: 14px;
  line-height: 1.75;
}

.why-points {
  display: grid;
  gap: 10px;
  margin-top: 14px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
  line-height: 1.55;
}

.point {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.45);
  padding: 12px;
}

.point b {
  display: block;
  font-size: 13px;
}

.point span {
  display: block;
  margin-top: 4px;
  color: var(--muted2);
  font-size: 13px;
}

.badge-dot {
  width: 12px;
  height: 12px;
  border-radius: 99px;
  background: var(--accent);
  box-shadow: 0 0 0 5px rgba(124, 58, 237, 0.12);
  flex: 0 0 auto;
  margin-top: 3px;
}

.btn-row {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.grid.why-cards {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-content: start;
}

.card.tall {
  grid-row: span 2;
}

.card.tall .card-pad {
  padding: 20px;
}

.first {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: 14px;
  align-items: stretch;
}

.steps {
  display: grid;
  gap: 10px;
  margin-top: 14px;
}

.step {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  padding: 14px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.num {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(124, 58, 237, 0.18);
  border: 1px solid rgba(124, 58, 237, 0.35);
  font-weight: 900;
  color: #fff;
  flex: 0 0 auto;
  box-shadow: 0 14px 30px rgba(124, 58, 237, 0.1);
}

.step b {
  display: block;
  font-size: 13px;
}

.step span {
  display: block;
  margin-top: 5px;
  color: var(--muted2);
  font-size: 13px;
  line-height: 1.55;
}

.first-visual {
  position: relative;
  min-height: 360px;
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  background: #000;
  box-shadow: var(--shadow2);
}

.first-visual::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(90deg, rgba(0, 0, 0, 0.82), rgba(0, 0, 0, 0.14)),
    url('/assets/bed-2.jpg') center / cover no-repeat;
  filter: grayscale(100%) contrast(1.14);
  transform: scale(1.03);
}

.first-visual::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 65% 35%, rgba(124, 58, 237, 0.34), transparent 55%),
    linear-gradient(180deg, rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.92));
  mix-blend-mode: screen;
  opacity: 0.72;
}

.price-sticker {
  position: absolute;
  top: 16px;
  left: 16px;
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  padding: 12px;
  z-index: 2;
  box-shadow: var(--shadow2);
}

.price-sticker b {
  display: block;
  font-size: 12px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.72);
}

.price-sticker span {
  display: block;
  margin-top: 6px;
  font-family: Sora, Inter, sans-serif;
  font-weight: 850;
  font-size: 22px;
}

.first-float {
  position: absolute;
  left: 16px;
  right: 16px;
  bottom: 16px;
  border-radius: 22px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(0, 0, 0, 0.72);
  backdrop-filter: blur(10px);
  padding: 16px;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.first-float b {
  font-size: 13px;
}

.first-float span {
  display: block;
  margin-top: 4px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  line-height: 1.35;
}

.beds-layout {
  display: grid;
  grid-template-columns: 1.35fr 0.65fr;
  gap: 14px;
  align-items: stretch;
}

.gallery {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 14px;
  align-items: stretch;
  height: 100%;
}

.tile {
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: #000;
  overflow: hidden;
  position: relative;
  box-shadow: var(--shadow2);
  min-height: 220px;
}

.tall-tile {
  min-height: 440px;
}

.tile img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: grayscale(100%) contrast(1.12);
  transform: scale(1.02);
}

.tile::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.88)),
    radial-gradient(circle at 70% 20%, rgba(124, 58, 237, 0.24), transparent 55%);
  mix-blend-mode: screen;
  opacity: 0.68;
  pointer-events: none;
}

.tile-label {
  position: absolute;
  left: 16px;
  right: 16px;
  bottom: 16px;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-end;
  z-index: 2;
}

.tile-label b {
  font-size: 16px;
  letter-spacing: -0.01em;
}

.tile-label span {
  display: block;
  margin-top: 4px;
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  line-height: 1.35;
}

.badge {
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(0, 0, 0, 0.55);
  font-size: 12px;
  color: rgba(255, 255, 255, 0.8);
  white-space: nowrap;
  font-weight: 800;
}

.stack {
  display: grid;
  grid-template-rows: 1fr 1fr;
  gap: 14px;
}

.beds-rail {
  border-radius: var(--radius-xl);
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  padding: 18px;
  box-shadow: var(--shadow2);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 14px;
}

.rail-item {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.45);
  padding: 14px;
}

.rail-item b {
  display: block;
  font-size: 13px;
}

.rail-item span {
  display: block;
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.68);
  font-size: 12px;
  line-height: 1.55;
}

.rail-cta {
  margin-top: auto;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.strip {
  background: #000;
  border-radius: 999px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: var(--shadow);
  color: #fff;
  overflow: hidden;
}

.strip-inner {
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.strip strong {
  font-family: Sora, Inter, sans-serif;
  font-weight: 850;
  letter-spacing: -0.01em;
}

.strip-copy {
  display: block;
  margin-top: 2px;
  color: rgba(255, 255, 255, 0.72);
  font-size: 13px;
  line-height: 1.4;
}

.pricing {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  gap: 14px;
  align-items: stretch;
}

.price-card {
  grid-column: span 3;
}

.price-card.featured {
  grid-column: span 6;
}

.price {
  padding: 18px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.price-title {
  margin: 0;
  font-size: 20px;
  font-weight: 860;
  letter-spacing: -0.01em;
}

.amount {
  margin-top: 10px;
  font-family: Sora, Inter, sans-serif;
  font-size: 34px;
  font-weight: 860;
  letter-spacing: -0.02em;
}

.fine {
  margin-top: 6px;
  color: var(--muted2);
  font-size: 13px;
  line-height: 1.55;
}

.list {
  margin-top: 14px;
  display: grid;
  gap: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
}

.li {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.li .tick {
  width: 18px;
  height: 18px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.04);
  display: grid;
  place-items: center;
  flex: 0 0 auto;
  margin-top: 1px;
  color: var(--accent);
  font-weight: 900;
  font-size: 12px;
}

.price-actions {
  margin-top: 16px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.price-actions .btn {
  width: auto;
}

.price-spacer {
  margin-top: auto;
}

.featured {
  border-color: rgba(124, 58, 237, 0.28);
  background:
    radial-gradient(520px 240px at 20% 10%, rgba(124, 58, 237, 0.18), transparent 55%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.07), rgba(255, 255, 255, 0.02));
}

.featured .kicker {
  border-color: rgba(124, 58, 237, 0.35);
  background: rgba(124, 58, 237, 0.16);
}

.visit {
  display: grid;
  grid-template-columns: 0.95fr 1.05fr;
  gap: 14px;
  align-items: stretch;
}

.map {
  width: 100%;
  height: 100%;
  min-height: 360px;
  border: 0;
  filter: grayscale(100%) contrast(1.1) invert(92%);
  opacity: 0.92;
}

.contact {
  padding: 18px;
}

.contact .row {
  display: grid;
  gap: 10px;
  margin-top: 12px;
}

.contact .info {
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.03);
  padding: 14px;
}

.contact .info span {
  margin-top: 6px;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.55;
}

.link {
  text-decoration: underline;
  text-underline-offset: 4px;
}

.warning {
  margin-top: 14px;
  text-align: left;
}

footer {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  background: #000;
  color: #fff;
  position: relative;
  z-index: 1;
  margin-top: 46px;
}

.footer-top {
  padding: 48px 0 26px;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr 1fr;
  gap: 20px;
  align-items: start;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.footer-brand .mark {
  width: 46px;
  height: 46px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  display: grid;
  place-items: center;
  overflow: hidden;
}

.footer-brand img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.footer-name {
  font-family: Sora, Inter, sans-serif;
  font-weight: 820;
  letter-spacing: -0.01em;
}

.footer-sub {
  color: rgba(255, 255, 255, 0.56);
  font-size: 13px;
  margin-top: 4px;
}

.footer-desc {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.75;
  font-size: 14px;
  max-width: 46ch;
}

.col h4 {
  margin: 0;
  font-size: 12px;
  letter-spacing: 0.24em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.78);
}

.col a {
  display: block;
  margin-top: 10px;
  padding: 8px 0;
  color: rgba(255, 255, 255, 0.68);
  font-weight: 650;
  font-size: 14px;
}

.col a:hover {
  color: #fff;
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 16px 0;
  color: rgba(255, 255, 255, 0.52);
  font-size: 13px;
}

.footer-bottom-inner {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.72);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 120;
  padding: 24px;
}

.modal.on {
  display: flex;
}

.modal-panel {
  width: min(980px, 100%);
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(0, 0, 0, 0.98);
  box-shadow: 0 28px 80px rgba(0, 0, 0, 0.65);
  overflow: hidden;
  display: grid;
  grid-template-columns: 0.95fr 1.05fr;
  color: #fff;
}

.modal-left {
  position: relative;
  min-height: 520px;
  background: #000;
}

.modal-left::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(90deg, rgba(0, 0, 0, 0.84), rgba(0, 0, 0, 0.18)),
    url('/assets/bed-1.jpg') center / cover no-repeat;
  filter: grayscale(100%) contrast(1.14);
  transform: scale(1.03);
}

.modal-left::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 65% 35%, rgba(124, 58, 237, 0.34), transparent 55%),
    linear-gradient(180deg, rgba(0, 0, 0, 0.12), rgba(0, 0, 0, 0.92));
  mix-blend-mode: screen;
  opacity: 0.72;
}

.modal-left .copy {
  position: relative;
  z-index: 2;
  padding: 26px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  height: 100%;
  gap: 10px;
}

.modal-left .copy b {
  font-family: Sora, Inter, sans-serif;
  font-size: 22px;
  letter-spacing: -0.02em;
}

.modal-left .copy span {
  color: rgba(255, 255, 255, 0.72);
  line-height: 1.7;
  font-size: 14px;
  max-width: 42ch;
}

.modal-right {
  padding: 22px 22px 20px;
}

.modal-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.tabs {
  display: flex;
  gap: 8px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  border-radius: 999px;
  padding: 6px;
}

.tab {
  padding: 10px 12px;
  border-radius: 999px;
  font-weight: 850;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.68);
  border: 1px solid transparent;
  background: transparent;
  cursor: pointer;
}

.tab.on {
  color: #fff;
  border-color: rgba(124, 58, 237, 0.48);
  background: rgba(124, 58, 237, 0.18);
}

.x {
  width: 44px;
  height: 44px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.03);
  color: #fff;
  cursor: pointer;
  font-weight: 900;
}

.x:hover {
  background: rgba(255, 255, 255, 0.06);
}

.form h3 {
  margin: 10px 0 0;
  font-family: Sora, Inter, sans-serif;
  font-size: 22px;
  letter-spacing: -0.02em;
}

.form p {
  margin: 10px 0 0;
  color: rgba(255, 255, 255, 0.72);
  line-height: 1.75;
  font-size: 14px;
}

.fields {
  margin-top: 16px;
  display: grid;
  gap: 12px;
}

.field label {
  display: block;
  color: rgba(255, 255, 255, 0.72);
  font-size: 12px;
  font-weight: 800;
  margin: 0 0 8px;
  letter-spacing: 0.02em;
}

.field input {
  width: 100%;
  padding: 14px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.03);
  color: #fff;
  outline: none;
  font-size: 14px;
  font-weight: 650;
}

.field input:focus {
  border-color: rgba(124, 58, 237, 0.62);
  box-shadow: 0 0 0 6px rgba(124, 58, 237, 0.14);
}

.form-actions {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
}

.smalllink {
  color: rgba(255, 255, 255, 0.66);
  font-weight: 750;
  font-size: 13px;
  text-decoration: underline;
  text-underline-offset: 4px;
}

.smalllink:hover {
  color: #fff;
}

.msg {
  margin-top: 12px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.03);
  padding: 10px 12px;
  border-radius: 14px;
  display: none;
}

.msg.on {
  display: block;
}

.msg.ok {
  border-color: rgba(124, 58, 237, 0.45);
}

.msg.err {
  border-color: rgba(255, 255, 255, 0.16);
}

.reveal {
  opacity: 0;
  transform: translateY(10px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

.reveal.on {
  opacity: 1;
  transform: translateY(0);
}

@media (max-width: 1080px) {
  .price-card {
    grid-column: span 6;
  }

  .price-card.featured {
    grid-column: span 12;
  }

  .beds-layout {
    grid-template-columns: 1fr;
  }

  .gallery {
    grid-template-columns: 1fr;
  }

  .why-layout {
    grid-template-columns: 1fr;
  }

  .grid.why-cards {
    grid-template-columns: 1fr;
  }

  .card.tall {
    grid-row: auto;
  }
}

@media (max-width: 980px) {
  .links {
    display: none;
  }

  .burger {
    display: inline-flex;
  }

  .nav-cta {
    min-width: auto;
  }

  .mobile {
    display: block;
  }

  .hero-grid {
    grid-template-columns: 1fr;
  }

  .hero-right {
    min-height: 360px;
  }

  .hero-left {
    padding: 34px 22px 26px;
  }

  .hero-metrics {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .first {
    grid-template-columns: 1fr;
  }

  .visit {
    grid-template-columns: 1fr;
  }

  .block-inner,
  .section .block-inner {
    padding: 22px;
  }

  .block.offset-left,
  .block.offset-right {
    transform: none;
  }

  .section-note {
    text-align: left;
  }

  .strip {
    border-radius: 26px;
  }

  .footer-grid {
    grid-template-columns: 1fr 1fr;
    gap: 18px;
  }

  .modal-panel {
    grid-template-columns: 1fr;
  }

  .modal-left {
    min-height: 260px;
  }

  .mobile .row {
    grid-template-columns: 1fr;
  }
}

@media (prefers-reduced-motion: reduce) {
  .reveal,
  .btn,
  .card {
    transition: none;
  }
}
</style>
