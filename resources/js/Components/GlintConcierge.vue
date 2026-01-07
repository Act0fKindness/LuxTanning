<template>
  <Teleport to="body">
    <div v-if="isMounted">
      <button class="glint-chat-fab" type="button" aria-label="Open Lux chat" @click="openPanel">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13" />
          <path d="M22 2l-7 20-4-9-9-4 20-7z" />
        </svg>
      </button>

      <section
        class="glint-chat"
        role="dialog"
        aria-label="Lux Concierge"
        :class="{ open: panelOpen }"
      >
        <header class="glint-chat-header">
          <div class="glint-avatar">L</div>
          <div class="glint-header-copy">
            <p class="glint-title mb-0">Lux Concierge</p>
            <p class="glint-sub mb-0">Courses · Minutes · Studios</p>
          </div>
          <button class="btn-close glint-close" type="button" aria-label="Close" @click="closePanel"></button>
        </header>

        <main class="glint-messages" ref="messagesRef">
          <template v-for="message in messages" :key="message.id">
            <div class="glint-msg" :class="message.role === 'user' ? 'glint-user' : 'glint-bot'">
              {{ message.text }}
            </div>
            <div class="glint-time">{{ formatTime(message.ts) }}</div>
          </template>
        </main>
        <div class="glint-typing" v-if="isTyping">Lux is typing…</div>

        <footer class="glint-input">
          <textarea
            ref="textareaRef"
            v-model="input"
            class="form-control"
            placeholder="Ask about Lux courses, minutes, or studios…"
            @keydown.enter.prevent="handleEnter"
            @input="autoResize"
          ></textarea>
          <button class="glint-send" type="button" :disabled="sending" @click="sendMessage">Send</button>
        </footer>
      </section>
    </div>
  </Teleport>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

const endpoint = '/chat/lux'
const isMounted = ref(false)
const panelOpen = ref(false)
const autoOpened = ref(false)
const messages = ref([])
const input = ref('')
const sending = ref(false)
const isTyping = ref(false)
const textareaRef = ref(null)
const messagesRef = ref(null)
const history = []
let autoOpenTimer = null
let greetingSent = false

const formatTime = timestamp => new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

const appendMessage = (text, role = 'bot') => {
  messages.value.push({
    id: `${role}-${Date.now()}-${Math.random()}`,
    text,
    role,
    ts: Date.now(),
  })
  nextTick(() => {
    if (messagesRef.value) {
      messagesRef.value.scrollTop = messagesRef.value.scrollHeight
    }
  })
}

const fetchCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

const converse = async (message, fromUser = false) => {
  const trimmed = (message || '').trim()
  if (!trimmed) return

  if (fromUser) {
    appendMessage(trimmed, 'user')
    history.push({ role: 'user', text: trimmed })
    input.value = ''
    autoResize()
  }

  sending.value = true
  isTyping.value = true
  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': fetchCsrf(),
      },
      body: JSON.stringify({ message: trimmed, history }),
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.error || 'Chat failed')
    const reply = data.reply || 'Let me double-check that for you.'
    appendMessage(reply, 'bot')
    history.push({ role: 'assistant', text: reply })
  } catch (error) {
    appendMessage('Sorry—we could not reach Lux Concierge right now. Try again shortly?', 'bot')
  } finally {
    sending.value = false
    isTyping.value = false
  }
}

const autoResize = () => {
  nextTick(() => {
    const el = textareaRef.value
    if (!el) return
    el.style.height = 'auto'
    el.style.height = Math.min(el.scrollHeight, 140) + 'px'
  })
}

const focusInput = () => nextTick(() => {
  const el = textareaRef.value
  el?.focus()
  autoResize()
})

const openPanel = (auto = false) => {
  panelOpen.value = true
  if (!auto) focusInput()
  if (!greetingSent) {
    greetingSent = true
    appendMessage('Hi! I’m Lux Concierge. Ask about bookings, minute wallets, or what each studio offers.', 'bot')
  }
}

const closePanel = () => {
  panelOpen.value = false
}

const handleFirstInteraction = () => {
  if (!autoOpened.value) {
    autoOpened.value = true
    autoOpenTimer = window.setTimeout(() => openPanel(true), 2500)
  }
}

const registerInteractionListeners = () => {
  ['scroll', 'pointerdown', 'keydown'].forEach(evt => {
    window.addEventListener(evt, handleFirstInteraction, { once: true })
  })
}

const cleanupListeners = () => {
  ['scroll', 'pointerdown', 'keydown'].forEach(evt => {
    window.removeEventListener(evt, handleFirstInteraction)
  })
  if (autoOpenTimer) window.clearTimeout(autoOpenTimer)
}

const sendMessage = () => converse(input.value, true)

const handleEnter = event => {
  if (event.shiftKey) return
  sendMessage()
}

onMounted(() => {
  isMounted.value = true
  registerInteractionListeners()
  autoResize()
})

onBeforeUnmount(() => {
  cleanupListeners()
})
</script>

<style scoped>
:root {
  --glint-primary: #ff8c43;
  --glint-primary-dark: #1a1034;
  --glint-bg: #fff6f0;
  --glint-border: rgba(12, 7, 20, 0.12);
}


.glint-chat-fab {
  position: fixed;
  right: 20px;
  bottom: 20px;
  width: 58px;
  height: 58px;
  border-radius: 50%;
  border: 1px solid rgba(255, 140, 67, 0.4);
  background: linear-gradient(135deg, #ffbe3d, #ff4e68);
  color: #1a1034;
  box-shadow: 0 16px 32px rgba(10, 5, 20, 0.35);
  display: grid;
  place-items: center;
  cursor: pointer;
  z-index: 1055;
}

.glint-chat-fab svg {
  width: 24px;
  height: 24px;
}

.glint-chat-fab:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease;
}

.glint-chat {
  position: fixed;
  right: 20px;
  bottom: 20px;
  width: 360px;
  max-width: calc(100vw - 40px);
  height: 520px;
  background: #fff;
  border-radius: 22px;
  border: 1px solid var(--glint-border);
  box-shadow: 0 40px 80px rgba(10, 5, 20, 0.3);
  display: none;
  flex-direction: column;
  overflow: hidden;
  z-index: 1055;
}

.glint-chat.open {
  display: flex;
}

.glint-chat-header {
  background: linear-gradient(135deg, rgba(255, 140, 67, 0.18), rgba(255, 78, 104, 0.45));
  padding: 16px;
  border-bottom: 1px solid var(--glint-border);
  display: flex;
  align-items: center;
  gap: 12px;
}

.glint-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ffbe3d, #ff4e68);
  color: #0c0714;
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 0.95rem;
}

.glint-title {
  font-weight: 700;
  font-size: 1rem;
  color: #12081d;
}

.glint-header-copy {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.glint-sub {
  font-size: 0.82rem;
  color: #d6d0e6;
  margin: 0;
}

.glint-close {
  margin-left: auto;
  filter: invert(14%) sepia(12%) saturate(1564%) hue-rotate(305deg) brightness(95%);
}

.glint-messages {
  flex: 1;
  padding: 16px;
  background: var(--glint-bg);
  overflow-y: auto;
}

.glint-msg {
  max-width: 86%;
  padding: 10px 14px;
  border-radius: 16px;
  margin: 6px 0 2px;
  line-height: 1.35;
  font-size: 0.92rem;
  box-shadow: 0 6px 18px rgba(5, 11, 9, 0.08);
  white-space: pre-wrap;
}

.glint-user {
  margin-left: auto;
  background: #ffffff;
  border: 1px solid rgba(18, 8, 29, 0.08);
}

.glint-bot {
  margin-right: auto;
  background: rgba(255, 142, 67, 0.12);
  border: 1px solid rgba(255, 142, 67, 0.3);
}

.glint-time {
  font-size: 0.68rem;
  color: #8b9894;
  margin-bottom: 4px;
}

.glint-input {
  border-top: 1px solid var(--glint-border);
  background: #fff;
  padding: 12px;
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.glint-input textarea {
  resize: none;
  min-height: 48px;
  max-height: 140px;
  height: auto;
  overflow: hidden;
  border-radius: 14px;
  border: 1px solid rgba(12, 7, 20, 0.15);
  padding: 12px;
  font-size: 0.92rem;
  outline: none;
  transition: border-color 0.2s ease;
  flex: 1 1 auto;
}

.glint-input textarea:focus {
  border-color: var(--glint-primary);
}

.glint-send {
  border-radius: 14px;
  border: none;
  padding: 0 18px;
  background: linear-gradient(135deg, #ffbe3d, #ff4e68);
  color: #12081d;
  font-weight: 600;
  box-shadow: 0 8px 18px rgba(255, 78, 104, 0.25);
  height: 48px;
  flex: 0 0 auto;
}

.glint-send:disabled {
  opacity: 0.7;
  box-shadow: none;
}

.glint-typing {
  font-size: 0.8rem;
  color: #7d8a86;
  padding: 0 16px 8px;
}

@media (max-width: 520px) {
  .glint-chat-fab {
    right: 14px;
    bottom: 14px;
  }

  .glint-chat {
    right: 10px;
    left: 10px;
    width: auto;
    height: 70vh;
  }
}
</style>
