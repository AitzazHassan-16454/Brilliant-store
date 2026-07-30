<script setup>
</script>

<template>
<div class="relative w-full h-full min-h-[360px] sm:min-h-[440px] flex items-center justify-center overflow-hidden">

  <!-- Background glow -->
  <div class="absolute w-64 h-64 bg-[#D4AF37] rounded-full animate-logo-glow opacity-40"></div>
  <div class="absolute w-48 h-48 bg-[#1a1f36] rounded-full animate-logo-glow opacity-20" style="animation-delay: 2s;"></div>

  <!-- Orbiting dots -->
  <div class="absolute inset-0 flex items-center justify-center">
    <div class="relative w-0 h-0">
      <!-- Dot 1 -->
      <div class="absolute animate-logo-orbit" style="top: 0; left: 0;">
        <div class="w-3 h-3 bg-[#D4AF37] rounded-full shadow-lg shadow-[#D4AF37]/50"></div>
      </div>
      <!-- Dot 2 -->
      <div class="absolute animate-logo-orbit" style="top: 0; left: 0; animation-delay: -1s;">
        <div class="w-2.5 h-2.5 bg-[#B8960F] rounded-full shadow-lg shadow-[#B8960F]/50"></div>
      </div>
      <!-- Dot 3 -->
      <div class="absolute animate-logo-orbit-reverse" style="top: 0; left: 0; animation-delay: -1s;">
        <div class="w-2 h-2 bg-[#D4AF37] rounded-full"></div>
      </div>
      <!-- Dot 4 -->
      <div class="absolute animate-logo-orbit-reverse" style="top: 0; left: 0; animation-delay: -1s;">
        <div class="w-2 h-2 bg-[#D4AF37] rounded-full"></div>
      </div>
    </div>
  </div>

  <!-- Main logo container -->
  <div class="relative animate-logo-float">

    <!-- Pulse rings -->
    <div class="absolute inset-0 -m-6 border-2 border-gray-200 dark:border-[#D4AF37]/20 rounded-3xl animate-logo-pulse-ring"></div>
    <div class="absolute inset-0 -m-10 border border-gray-100 dark:border-[#D4AF37]/20 rounded-3xl animate-logo-pulse-ring" style="animation-delay: 2.8s;"></div>

    <!-- Logo card -->
    <div class="relative bg-[#1a1f36] rounded-3xl p-8 sm:p-10 shadow-2xl shadow-gray-900/30">

      <!-- Bag icon -->
      <div class="relative">
        <svg class="w-20 h-20 sm:w-24 sm:h-24 text-white mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round"/>
          <path d="M16 10a4 4 0 0 1-8 0" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <!-- Floating sparkle top-right -->
        <div class="absolute -top-3 -right-3 animate-logo-twinkle" style="animation-delay: 2.3s;">
          <svg class="w-5 h-5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
          </svg>
        </div>

        <!-- Floating sparkle bottom-left -->
        <div class="absolute -bottom-2 -left-4 animate-logo-twinkle" style="animation-delay: 2.2s;">
          <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
          </svg>
        </div>

        <!-- Floating sparkle left -->
        <div class="absolute top-1/2 -left-8 -translate-y-1/2 animate-logo-twinkle" style="animation-delay: 2.8s;">
          <div class="w-2 h-2 bg-[#D4AF37] rounded-full"></div>
        </div>

        <!-- Floating sparkle right -->
        <div class="absolute top-1/2 -right-8 -translate-y-1/2 animate-logo-twinkle" style="animation-delay: 2.8s;">
          <div class="w-2 h-2 bg-[#D4AF37] rounded-full"></div>
        </div>
      </div>

      <!-- Brand name -->
      <div class="mt-6 text-center">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Brilliant</h1>
        <div class="mt-2 flex items-center justify-center gap-2">
          <span class="w-8 h-px bg-[#D4AF37]"></span>
          <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#D4AF37]">Premium Store</span>
          <span class="w-8 h-px bg-[#D4AF37]"></span>
        </div>
      </div>

      <!-- Decorative rotating border -->
      <div class="absolute -inset-px rounded-3xl border border-transparent bg-gradient-to-br from-[#D4AF37]/20 via-transparent to-[#D4AF37]/20 animate-logo-spin-slow pointer-events-none" style="animation-duration: 20s;"></div>
    </div>
  </div>

  <!-- Floating badge -->
  <div class="absolute bottom-6 right-6 sm:bottom-8 sm:right-8 animate-logo-badge-pop">
    <div class="bg-white dark:bg-[#1A1A1A] rounded-xl px-4 py-2.5 shadow-lg border border-gray-100 dark:border-[#D4AF37]/20 flex items-center gap-2">
      <div class="w-8 h-8 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-linecap="round" stroke-linejoin="round"/>
          <polyline points="22 4 12 14.01 9 11.01" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-green-600 dark:text-green-400">Trusted</p>
        <p class="text-xs font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">Secure Shopping</p>
      </div>
    </div>
  </div>

  <!-- Top-left floating badge -->
  <div class="absolute top-6 left-6 sm:top-8 sm:left-8 animate-logo-badge-pop" style="animation-delay: 2.5s;">
    <div class="bg-white dark:bg-[#1A1A1A] rounded-xl px-4 py-2.5 shadow-lg border border-gray-100 dark:border-[#D4AF37]/20 flex items-center gap-2">
      <div class="w-8 h-8 bg-[#D4AF37]/10 dark:bg-[#D4AF37]/20 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#D4AF37] dark:text-[#D4AF37]">Curated</p>
        <p class="text-xs font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">Handpicked Items</p>
      </div>
    </div>
  </div>

</div>
</template>