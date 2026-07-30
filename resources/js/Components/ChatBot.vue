<script setup>
import { ref, nextTick, onMounted, watch } from "vue";

const isOpen = ref(false);
const messages = ref([]);
const input = ref("");
const loading = ref(false);
const conversationId = ref(null);
const chatRef = ref(null);

const loadHistory = () => {
  try {
    const saved = localStorage.getItem("chat_history");
    if (saved) {
      const parsed = JSON.parse(saved);
      messages.value = parsed.messages || [];
      conversationId.value = parsed.conversationId || null;
    }
  } catch {
    localStorage.removeItem("chat_history");
  }
};

const saveHistory = () => {
  localStorage.setItem(
    "chat_history",
    JSON.stringify({
      messages: messages.value.slice(-50),
      conversationId: conversationId.value,
    })
  );
};

onMounted(loadHistory);

watch(messages, saveHistory, { deep: true });

const toggle = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    nextTick(() => scrollToBottom());
  }
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatRef.value) {
      chatRef.value.scrollTop = chatRef.value.scrollHeight;
    }
  });
};

const send = async () => {
  const text = input.value.trim();
  if (!text || loading.value) return;

  input.value = "";
  messages.value.push({ role: "user", content: text });
  loading.value = true;
  scrollToBottom();

  const assistantMsg = { role: "assistant", content: "" };
  messages.value.push(assistantMsg);

  try {
    const res = await fetch("/ai/chat", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "",
      },
      body: JSON.stringify({
        message: text,
        conversation_id: conversationId.value,
      }),
    });

    if (!res.ok) {
      assistantMsg.content = "Sorry, I couldn't process that request.";
      loading.value = false;
      scrollToBottom();
      return;
    }

    const reader = res.body.getReader();
    const decoder = new TextDecoder();
    let buffer = "";

    while (true) {
      const { done, value } = await reader.read();
      if (done) break;

      buffer += decoder.decode(value, { stream: true });
      const lines = buffer.split("\n");
      buffer = lines.pop() || "";

      for (const line of lines) {
        if (line.startsWith("data: ")) {
          const data = line.slice(6).trim();
          if (data === "[DONE]") continue;
          try {
            const parsed = JSON.parse(data);
            if (parsed.conversation_id) {
              conversationId.value = parsed.conversation_id;
            }
            if (parsed.content) {
              assistantMsg.content += parsed.content;
            }
          } catch {
            assistantMsg.content += data;
          }
        }
      }

      scrollToBottom();
    }

    if (buffer.trim()) {
      assistantMsg.content += buffer.trim();
    }
  } catch {
    assistantMsg.content = "Connection error. Please try again.";
  }

  loading.value = false;
  scrollToBottom();
};

const clearChat = () => {
  messages.value = [];
  conversationId.value = null;
  localStorage.removeItem("chat_history");
};
</script>

<template>
  <Teleport to="body">
    <div class="fixed bottom-6 right-6 z-[60] flex flex-col items-end gap-3">
      <Transition name="chat-slide">
        <div
          v-if="isOpen"
          class="w-[360px] max-w-[calc(100vw-3rem)] bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-2xl border border-gray-100 dark:border-[#D4AF37]/20 overflow-hidden flex flex-col"
          style="max-height: 560px"
        >
          <div
            class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-[#D4AF37]/20 bg-teal-600"
          >
            <div class="flex items-center gap-2.5">
              <div
                class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-white"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
                  />
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-white">Support</p>
                <p class="text-[10px] text-white/70">Orders &amp; Products</p>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <button
                @click="clearChat"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                title="Clear chat"
              >
                <svg
                  class="w-3.5 h-3.5"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <polyline points="3 6 5 6 21 6" />
                  <path
                    d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"
                  />
                </svg>
              </button>
              <button
                @click="toggle"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
              >
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>

          <div
            ref="chatRef"
            class="flex-1 overflow-y-auto px-4 py-3 space-y-3 scroll-smooth"
            style="max-height: 380px"
          >
            <div
              v-if="messages.length === 0"
              class="text-center py-8 px-4"
            >
              <div
                class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-[#2A2A2A] flex items-center justify-center mx-auto mb-3"
              >
                <svg
                  class="w-6 h-6 text-gray-400 dark:text-[#A0A0A0]"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
                  />
                </svg>
              </div>
              <p class="text-sm font-medium text-gray-400 dark:text-[#A0A0A0]">
                Hi! I'm your support assistant.
              </p>
              <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">
                Ask me about your orders or products!
              </p>
            </div>

            <div
              v-for="(msg, i) in messages"
              :key="i"
              :class="[
                'flex',
                msg.role === 'user' ? 'justify-end' : 'justify-start',
              ]"
            >
              <div
                :class="[
                  'max-w-[85%] rounded-xl px-3.5 py-2 text-sm leading-relaxed',
                  msg.role === 'user'
                    ? 'bg-teal-600 text-white rounded-br-md'
                    : 'bg-gray-50 dark:bg-[#2A2A2A] text-gray-700 dark:text-[#F5F5F5] rounded-bl-md',
                ]"
              >
                <span v-if="msg.content || loading">{{ msg.content }}</span>
                <span
                  v-if="i === messages.length - 1 && msg.role === 'assistant' && loading"
                  class="inline-flex gap-0.5 ml-0.5"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce"
                    style="animation-delay: 0ms"
                  ></span>
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce"
                    style="animation-delay: 150ms"
                  ></span>
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce"
                    style="animation-delay: 300ms"
                  ></span>
                </span>
              </div>
            </div>
          </div>

          <div
            class="border-t border-gray-100 dark:border-[#D4AF37]/20 px-3 py-2.5 flex gap-2"
          >
            <input
              v-model="input"
              @keydown.enter="send"
              type="text"
              placeholder="Type a message..."
              :disabled="loading"
              class="flex-1 rounded-xl px-3 py-2 text-sm outline-none border border-gray-200 dark:border-[#D4AF37]/20 bg-gray-50 dark:bg-[#0A0A0A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-[#8b949e] focus:border-teal-600 disabled:opacity-50"
            />
            <button
              @click="send"
              :disabled="loading || !input.trim()"
              class="px-3 py-2 rounded-xl text-sm font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-teal-600 hover:bg-teal-700 flex items-center justify-center"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                />
              </svg>
            </button>
          </div>
        </div>
      </Transition>

      <button
        @click="toggle"
        class="w-14 h-14 rounded-2xl shadow-xl flex items-center justify-center cursor-pointer transition-all duration-200 active:scale-90 bg-teal-600 hover:bg-teal-700 text-white"
      >
        <svg
          v-if="!isOpen"
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
          />
        </svg>
        <svg
          v-else
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  </Teleport>
</template>

<style>
.chat-slide-enter-active,
.chat-slide-leave-active {
  transition: all 0.25s ease;
}
.chat-slide-enter-from,
.chat-slide-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.96);
}
</style>
