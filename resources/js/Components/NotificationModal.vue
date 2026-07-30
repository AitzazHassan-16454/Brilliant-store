<script setup>
import { useNotification } from "../composables/useNotification.js"

const { isVisible, message, type, close } = useNotification()
</script>

<template>
  <Transition name="toast">
    <div
      v-if="isVisible"
      class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-lg cursor-pointer max-w-md bg-white dark:bg-[#1A1A1A] border dark:border-[#D4AF37]/20"
      :style="{
        borderColor: type === 'success' ? 'rgba(212,175,55,0.2)' : 'rgba(220,38,38,0.2)',
        boxShadow: type === 'success'
          ? '0 8px 30px rgba(212,175,55,0.12), 0 2px 8px rgba(0,0,0,0.04)'
          : '0 8px 30px rgba(220,38,38,0.12), 0 2px 8px rgba(0,0,0,0.04)',
      }"
      @click="close"
    >
      <!-- Icon -->
      <div
        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
        :style="{
          background: type === 'success' ? 'rgba(212,175,55,0.1)' : 'rgba(220,38,38,0.1)',
        }"
      >
        <svg v-if="type === 'success'" class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <svg v-else class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </div>

      <!-- Message -->
      <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ message }}</p>

      <!-- Close -->
      <button
        class="ml-2 w-6 h-6 rounded-md flex items-center justify-center shrink-0 transition-colors duration-200 cursor-pointer bg-transparent dark:hover:bg-[#1A1A1A] hover:bg-black/5 text-[#9B9B9B] dark:text-[#A0A0A0] border-none"
        @click.stop="close"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </Transition>
</template>

<style scoped>
.toast-enter-active {
  transition: all 0.35s cubic-bezier(0.21, 1.02, 0.73, 1);
}
.toast-leave-active {
  transition: all 0.25s cubic-bezier(0.06, 0.71, 0.55, 1);
}
.toast-enter-from {
  opacity: 0;
  transform: translate(-50%, -16px) scale(0.96);
}
.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, -10px) scale(0.97);
}
</style>
