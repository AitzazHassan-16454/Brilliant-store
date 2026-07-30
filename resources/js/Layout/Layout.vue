<script setup>
import { Link, usePage } from "@inertiajs/vue3"

const page = usePage()
</script>

<template>
  <div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] dark:text-[#F5F5F5]">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 bg-white dark:bg-[#1A1A1A] border-b border-gray-100 dark:border-[#D4AF37]/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-2 shrink-0 no-underline">
          <div class="w-8 h-8 bg-[#1a1f36] rounded-xl flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
          </div>
          <span class="font-extrabold text-lg tracking-tight text-gray-900 dark:text-[#F5F5F5]">Brilliant</span>
        </Link>

        <!-- AUTH / NAV -->
        <div class="hidden md:flex items-center gap-2 ml-auto">

          <Link href="/" class="px-3.5 py-2 border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] transition-all no-underline">
            Home
          </Link>
          
          
          <Link v-if="page.props.auth?.user" href="/logout" method="post" as="button"
            class="px-3.5 py-2 border border-red-200 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-50 transition-all cursor-pointer">
            Logout
          </Link>

        
        </div>

    

      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="max-w-7xl mx-auto px-4 py-6">
      <slot />
    </main>

  </div>
</template>