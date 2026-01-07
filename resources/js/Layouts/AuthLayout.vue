<template>
  <div class="auth-layout">
    <div class="panel">
      <header>
        <img src="/images/lux-logo.png" alt="Lux Tanning" />
        <p class="eyebrow">Secure access</p>
      </header>
      <slot />
    </div>
    <div class="aside">
      <h2>Lux login without passwords</h2>
      <p>Magic links, OTP verification, and studio-aware routing keep every role inside the right workspace.</p>
      <ul>
        <li><i class="bi bi-check2"></i> Device posture & session memory</li>
        <li><i class="bi bi-check2"></i> Studio selector for multi-location owners</li>
        <li><i class="bi bi-check2"></i> Support for SMS or email codes</li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.auth-layout { min-height: 100vh; display: grid; grid-template-columns: minmax(320px, 480px) 1fr; background: radial-gradient(circle at top left, rgba(255,140,67,.2), #0b0614); color: #fff; }
.panel { background: #fff; color: #0f172a; padding: 40px; display: flex; flex-direction: column; gap: 20px; box-shadow: 0 20px 60px rgba(15,23,42,.2); }
header { display: flex; justify-content: space-between; align-items: center; }
header img { height: 34px; }
.eyebrow { text-transform: uppercase; font-size: 12px; letter-spacing: .2em; color: #ff8c43; }
.aside { padding: 60px; display: flex; flex-direction: column; gap: 18px; }
.aside h2 { font-size: clamp(28px, 4vw, 42px); margin: 0; }
.aside p { margin: 0; color: rgba(255,255,255,.8); max-width: 540px; }
.aside ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; }
.aside li { display: flex; gap: 10px; align-items: center; }
.bi-check2 { color: #ffbe3d; }
@media (max-width: 900px) {
  .auth-layout { grid-template-columns: 1fr; }
  .aside { display: none; }
}
</style>
