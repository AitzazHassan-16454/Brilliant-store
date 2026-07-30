<script setup>
import { ref, nextTick, onMounted, watch, computed } from "vue";

const isOpen = ref(false);
const messages = ref([]);
const input = ref("");
const loading = ref(false);
const conversationId = ref(null);
const chatRef = ref(null);
const fileInputRef = ref(null);
const selectedImage = ref(null);

const imagePreview = computed(() => {
  if (!selectedImage.value) return null;
  return URL.createObjectURL(selectedImage.value);
});

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
  if ((!text && !selectedImage.value) || loading.value) return;

  input.value = "";
  const userMsg = { role: "user", content: text, image: imagePreview.value };
  messages.value.push(userMsg);
  loading.value = true;
  scrollToBottom();

  const assistantMsg = { role: "assistant", content: "", products: [] };
  messages.value.push(assistantMsg);

  const formData = new FormData();
  formData.append("message", text || "What product matches this image?");
  if (conversationId.value) {
    formData.append("conversation_id", conversationId.value);
  }
  if (selectedImage.value) {
    formData.append("image", selectedImage.value);
  }

  selectedImage.value = null;

  try {
    const res = await fetch("/ai/chat", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "",
      },
      body: formData,
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
            if (parsed.products) {
              assistantMsg.products = parsed.products;
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

const pickImage = () => {
  fileInputRef.value?.click();
};

const handleFileSelect = (e) => {
  const file = e.target.files?.[0];
  if (file && file.type.startsWith("image/")) {
    selectedImage.value = file;
  }
  e.target.value = "";
};

const removeImage = () => {
  selectedImage.value = null;
};

const clearChat = () => {
  messages.value = [];
  conversationId.value = null;
  localStorage.removeItem("chat_history");
};
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[55] bg-black/10 dark:bg-black/30 backdrop-blur-[2px]"
        @click="toggle"
      ></div>
    </Transition>
    <div class="fixed bottom-6 right-6 z-[60] flex flex-col items-end gap-3">
      <Transition name="chat-slide">
        <div
          v-if="isOpen"
          class="w-[360px] max-w-[calc(100vw-3rem)] bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-2xl border border-gray-200/70 dark:border-[#D4AF37]/15 overflow-hidden flex flex-col"
          style="max-height: 450px"
        >
          <div
            class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-[#D4AF37]/15 bg-gradient-to-r from-[#D4AF37] to-[#B8941E]"
          >
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-white tracking-tight">Support</p>
                <p class="text-[10px] text-white/70 font-medium tracking-tight">Orders &amp; Products</p>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <button
                @click="clearChat"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
                title="Clear chat"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2" />
                </svg>
              </button>
              <button
                @click="toggle"
                class="w-7 h-7 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10 transition cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div
            ref="chatRef"
            class="flex-1 overflow-y-auto px-4 py-4 space-y-4 scroll-smooth bg-stone-50/50 dark:bg-transparent"
          >
            <div
              v-if="messages.length === 0"
              class="text-center py-10 px-6"
            >
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#D4AF37]/10 to-[#D4AF37]/5 flex items-center justify-center mx-auto mb-4 ring-1 ring-[#D4AF37]/10">
                <svg class="w-7 h-7 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
              </div>
              <p class="text-sm font-semibold text-gray-800 dark:text-[#F5F5F5]">Hi, how can I help you?</p>
              <p class="text-xs text-gray-500 dark:text-[#A0A0A0] mt-1.5 max-w-[220px] mx-auto leading-relaxed">Ask about your orders, browse products, or send a photo to find matching items.</p>
            </div>

            <div
              v-for="(msg, i) in messages"
              :key="i"
              :class="[
                'flex items-end gap-2',
                msg.role === 'user' ? 'justify-end' : 'justify-start',
              ]"
            >
              <div
                v-if="msg.role === 'assistant'"
                class="w-6 h-6 rounded-full bg-gradient-to-br from-[#D4AF37] to-[#B8941E] flex items-center justify-center shrink-0 shadow-sm"
              >
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
              </div>
              <div
                :class="[
                  'max-w-[80%] rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed',
                  msg.role === 'user'
                    ? 'bg-gradient-to-br from-[#D4AF37] to-[#B8941E] text-white rounded-br-sm shadow-sm shadow-[#D4AF37]/20'
                    : 'bg-white dark:bg-[#2A2A2A] text-gray-700 dark:text-[#E8E8E8] rounded-bl-sm shadow-sm border border-gray-100 dark:border-transparent',
                ]"
              >
                <img
                  v-if="msg.image"
                  :src="msg.image"
                  class="max-w-full rounded-lg mb-1.5 max-h-32 object-cover"
                  alt="Uploaded"
                />
                <span v-if="msg.content || loading" class="whitespace-pre-wrap">{{ msg.content }}</span>
                <span
                  v-if="i === messages.length - 1 && msg.role === 'assistant' && loading"
                  class="inline-flex gap-0.5 ml-0.5"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce" style="animation-delay: 0ms"></span>
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce" style="animation-delay: 150ms"></span>
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-[#8b949e] animate-bounce" style="animation-delay: 300ms"></span>
                </span>
                <div
                  v-if="msg.products?.length"
                  class="mt-2.5 space-y-1.5"
                >
                  <a
                    v-for="p in msg.products"
                    :key="p.uid"
                    :href="`/products/${p.uid}`"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium no-underline transition-all bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/15 hover:border-[#D4AF37] dark:hover:border-[#D4AF37] text-gray-700 dark:text-[#F5F5F5] shadow-sm hover:shadow-md"
                  >
                    <span class="w-6 h-6 rounded-lg shrink-0 flex items-center justify-center bg-[#D4AF37]/10 text-[#D4AF37] text-[10px] font-bold">#</span>
                    <span class="truncate font-medium">{{ p.name }}</span>
                    <span class="ml-auto shrink-0 font-bold text-[#D4AF37]">${{ p.price }}</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div
            class="border-t border-gray-100 dark:border-[#D4AF37]/15 px-3 py-3 bg-white dark:bg-[#1A1A1A] flex flex-col gap-2"
          >
            <div v-if="selectedImage" class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-200 dark:border-[#D4AF37]/20 shrink-0 shadow-sm">
              <img :src="imagePreview" class="w-full h-full object-cover" alt="Preview" />
              <button @click="removeImage" class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full flex items-center justify-center bg-black/50 text-white cursor-pointer border-none text-[10px] leading-none hover:bg-black/70 transition">&times;</button>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="pickImage"
                :disabled="loading"
                class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-400 dark:text-[#8b949e] hover:border-[#D4AF37] dark:hover:border-[#D4AF37] hover:text-[#D4AF37] hover:bg-[#D4AF37]/5 disabled:opacity-50"
                title="Attach image"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                </svg>
              </button>
              <input
                ref="fileInputRef"
                @change="handleFileSelect"
                type="file"
                accept="image/*"
                class="hidden"
              />
              <input
                v-model="input"
                @keydown.enter="send"
                type="text"
                placeholder="Type a message..."
                :disabled="loading"
                class="flex-1 rounded-xl px-3.5 py-2 text-sm outline-none border border-gray-200 dark:border-[#D4AF37]/20 bg-gray-50 dark:bg-[#0A0A0A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-[#8b949e] focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37]/20 disabled:opacity-50 transition"
              />
              <button
                @click="send"
                :disabled="loading || (!input.trim() && !selectedImage)"
                class="px-3.5 py-2 rounded-xl text-sm font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-gradient-to-br from-[#D4AF37] to-[#B8941E] hover:from-[#B8941E] hover:to-[#A3851A] flex items-center justify-center shadow-sm transition-all active:scale-95"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <button
        @click="toggle"
        class="w-14 h-14 rounded-2xl shadow-lg flex items-center justify-center cursor-pointer transition-all duration-200 active:scale-90 bg-gradient-to-br from-[#D4AF37] to-[#B8941E] hover:shadow-xl hover:shadow-[#D4AF37]/30 text-white"
      >
        <svg
          v-if="!isOpen"
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
        </svg>
        <svg
          v-else
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </Teleport>
</template>

<style>
.chat-slide-enter-active,
.chat-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.chat-slide-enter-from,
.chat-slide-leave-to {
  opacity: 0;
  transform: translateY(16px) scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
