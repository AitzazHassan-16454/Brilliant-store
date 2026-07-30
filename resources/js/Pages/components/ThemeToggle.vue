<script setup>
import { useTheme } from "../../composables/useTheme.js";

const { isDark, toggle } = useTheme();
</script>

<template>
  <button
    @click="toggle"
    class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-gray-500 dark:text-[#A0A0A0] hover:bg-gray-100 dark:hover:bg-[#2A2A2A] hover:text-[#1A1A1A] dark:hover:text-[#F5F5F5] transition-all duration-200 cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20"
    :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
  >
    <!-- Sun icon (shown in dark mode) -->
    <svg v-if="isDark" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <!-- Moon icon (shown in light mode) -->
    <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
    <span class="text-xs font-semibold whitespace-nowrap hidden sm:inline">{{ isDark ? 'Light' : 'Dark' }}</span>
  </button>
</template>
