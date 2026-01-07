<template>
  <div class="luma-assistant" :class="{ 'is-open': isOpen }">
    <button class="assistant-toggle" @click="toggle">{{ isOpen ? 'Close' : botName }}</button>
    <div class="assistant-panel" v-if="isOpen">
      <header>
        <strong>{{ botName }} Support</strong>
        <span>{{ shopName }}</span>
      </header>
      <section ref="scrollContainer" class="assistant-messages">
        <article v-for="message in messages" :key="message.id" :class="message.role">
          <p>{{ message.text }}</p>
          <small v-if="message.meta">{{ message.meta }}</small>
        </article>
        <article v-if="loading" class="bot">
          <p>Thinking…</p>
        </article>
      </section>
      <footer>
        <form @submit.prevent="send">
          <input type="text" v-model="input" placeholder="Ask about pricing, hours, etc." :disabled="loading" />
          <button type="submit" :disabled="loading || !input.trim()">Send</button>
        </form>
        <p v-if="error" class="error-text">{{ error }}</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';

const config = window.__LUMA_CHATBOT || {};
const botName = config.bot_name || 'LUMA';
const shopName = config.shop_name || 'Our Shop';
const baseUrl = config.url || 'http://localhost:8001';
const isOpen = ref(false);
const messages = ref([
  { id: 'welcome', role: 'bot', text: `Hi! I'm ${botName}. Ask me about memberships, opening hours, or bookings.`, meta: null },
]);
const input = ref('');
const loading = ref(false);
const error = ref('');
const scrollContainer = ref(null);
const sessionId = `luma-${crypto.randomUUID?.() || Date.now()}`;

const toggle = () => {
  isOpen.value = !isOpen.value;
  error.value = '';
  nextTick(() => scrollToEnd());
};

const scrollToEnd = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
  }
};

const appendMessage = (role, text, meta = null) => {
  messages.value.push({ id: `${role}-${Date.now()}-${Math.random()}`, role, text, meta });
  nextTick(() => scrollToEnd());
};

const send = async () => {
  if (!input.value.trim()) {
    return;
  }
  const question = input.value.trim();
  input.value = '';
  appendMessage('user', question);
  error.value = '';
  loading.value = true;
  try {
    const response = await fetch(`${baseUrl}/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ session_id: sessionId, message: question }),
    });
    if (!response.ok) {
      throw new Error('Request failed');
    }
    const payload = await response.json();
    let meta = '';
    if (payload.sources?.length) {
      meta += `Sources: ${payload.sources.map(src => src.doc).join(', ')}`;
    }
    if (payload.safety_notes) {
      meta += meta ? ` • ${payload.safety_notes}` : payload.safety_notes;
    }
    appendMessage('bot', payload.answer || 'I could not find that information.', meta || null);
    if (payload.follow_up_question) {
      appendMessage('bot', payload.follow_up_question, null);
    }
  } catch (err) {
    error.value = 'Chatbot is offline. Please try again soon.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.luma-assistant {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: 'Manrope', system-ui, sans-serif;
}
.assistant-toggle {
  background: #5438ff;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 0.75rem 1.25rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  cursor: pointer;
}
.assistant-panel {
  width: 320px;
  max-height: 460px;
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(15,23,42,0.25);
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
}
.assistant-panel header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(15,23,42,0.08);
}
.assistant-panel header strong {
  display: block;
}
.assistant-panel header span {
  font-size: 0.85rem;
  color: #6b7280;
}
.assistant-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.assistant-messages article {
  padding: 0.75rem;
  border-radius: 12px;
  max-width: 90%;
  font-size: 0.95rem;
}
.assistant-messages article.bot {
  background: #f3f4ff;
  align-self: flex-start;
}
.assistant-messages article.user {
  background: #5438ff;
  color: #fff;
  align-self: flex-end;
}
.assistant-messages small {
  display: block;
  font-size: 0.75rem;
  color: #4b5563;
  margin-top: 0.5rem;
}
footer {
  padding: 0.75rem 1rem 1rem;
  border-top: 1px solid rgba(15,23,42,0.08);
}
footer form {
  display: flex;
  gap: 0.5rem;
}
footer input {
  flex: 1;
  border-radius: 999px;
  border: 1px solid rgba(15,23,42,0.2);
  padding: 0.6rem 1rem;
  font-size: 0.95rem;
}
footer button {
  background: #0f172a;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 0 1rem;
}
.error-text {
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 0.5rem;
}
</style>
