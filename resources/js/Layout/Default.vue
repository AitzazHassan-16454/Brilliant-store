<script setup>
import { ref, computed } from "vue"
import { usePage, Link } from "@inertiajs/vue3"
import AuthModal from "../Components/AuthModal.vue"
import NotificationModal from "../Components/NotificationModal.vue"
import { useAuthModal } from "../composables/useAuthModal.js"
import ThemeToggle from "../Pages/components/ThemeToggle.vue"
import ChatBot from "../Components/ChatBot.vue"
import OnboardingTour from "../Components/OnboardingTour.vue"

const page = usePage()
const { openLogin } = useAuthModal()
const mobileOpen = ref(false)

const currentPath = computed(() => {
  try { return new URL(page.url, window.location.origin).pathname }
  catch { return page.url }
})

function isActive(path) {
  if (path === "/") return currentPath.value === "/"
  return currentPath.value.startsWith(path)
}

const activeNavClasses = 'bg-[#FAF7F2] dark:bg-[#1A1A1A] text-[#D4AF37] dark:text-[#D4AF37] border border-[#D4AF37]/30 dark:border-[#D4AF37]/30 shadow-sm shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/20'
const inactiveNavClasses = 'text-[#1A1A1A] dark:text-[#F5F5F5] bg-transparent border border-gray-200 dark:border-[#D4AF37]/20 hover:shadow-sm hover:shadow-[#D4AF37]/10 dark:hover:shadow-[#D4AF37]/10'

const activeMobileNavClasses = 'bg-[#FAF7F2] dark:bg-[#1A1A1A] text-[#D4AF37] dark:text-[#D4AF37] border border-[#D4AF37]/30 dark:border-[#D4AF37]/30 shadow-sm shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/20'
const inactiveMobileNavClasses = 'text-[#1A1A1A] dark:text-[#F5F5F5] bg-transparent border border-gray-200 dark:border-[#D4AF37]/20 hover:shadow-sm hover:shadow-[#D4AF37]/10 dark:hover:shadow-[#D4AF37]/10'
</script>

<template>
  <div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]" style="font-family:'Inter',system-ui,sans-serif;">

    <!-- HEADER (Sticky) -->
    <header class="sticky top-0 z-50 transition-all duration-300 bg-[rgba(250,247,242,0.85)] dark:bg-[#1A1A1A]/85 backdrop-blur-xl border-b border-gray-200 dark:border-[#D4AF37]/20 shadow-sm shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 shrink-0 no-underline group logo-link">
          <div class="relative w-9 h-9 rounded-xl flex items-center justify-center overflow-hidden logo-icon transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#D4AF37]/30" style="background:linear-gradient(135deg,#D4AF37,#B8960F);">
            <svg class="w-5 h-5 relative z-10" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M6 3h12l4 6-10 13L2 9z"/>
              <path d="M2 9h20"/>
              <path d="M12 22L6 9"/>
              <path d="M12 22l6-13"/>
            </svg>
          </div>
          <div class="flex flex-col leading-none">
            <span class="text-lg font-extrabold tracking-tight transition-colors duration-200 logo-text text-[#1A1A1A] dark:text-[#F5F5F5]">Brilliant</span>
            <span class="text-[10px] font-medium tracking-widest uppercase transition-colors duration-200 logo-tagline text-[#9B9B9B] dark:text-[#A0A0A0]">Premium Store</span>
          </div>
        </Link>

        <!-- Right Actions -->
        <div class="flex items-center gap-2">

          <!-- Theme Toggle -->
          <ThemeToggle />

          <!-- Home -->
          <Link href="/"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/') && !isActive('/dashboard') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </Link>

          <!-- Custom Order -->
          <Link href="/custom-order"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/custom-order') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
            </svg>
          </Link>

          <!-- Wishlist -->
          <Link v-if="page.props.auth.user" href="/wishlist"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 no-underline group relative"
            :class="isActive('/wishlist') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span v-if="page.props.wishlistCount"
              class="absolute -top-1 -right-1 px-1 py-0.5 text-[9px] font-bold rounded-full leading-none"
              :class="isActive('/wishlist') ? 'bg-gray-800/15 dark:bg-white/15 text-[#0A0A0A] dark:text-[#F5F5F5]' : 'bg-red-600 text-white'"
            >
              {{ page.props.wishlistCount }}
            </span>
          </Link>
          <!-- Cart -->
          <Link v-if="page.props.auth.user" href="/cart"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 no-underline group relative"
            :class="isActive('/cart') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span v-if="page.props.cartCount"
              class="absolute -top-1 -right-1 px-1 py-0.5 text-[9px] font-bold rounded-full leading-none"
              :class="isActive('/cart') ? 'bg-gray-800/15 dark:bg-white/15 text-[#0A0A0A] dark:text-[#F5F5F5]' : 'bg-[#D4AF37] text-white'"
            >
              {{ page.props.cartCount }}
            </span>
          </Link>

          <!-- Profile -->
          <Link v-if="page.props.auth.user" href="/Profile"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/Profile') || isActive('/EditProfile') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
          </Link>

          <!-- Admin -->
          <Link v-if="page.props.permissions?.canViewDashboard" href="/dashboard"
            class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/dashboard') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Admin
          </Link>

          <!-- Logout / Sign In -->
          <Link v-if="page.props.auth.user" href="/logout" method="post" as="button"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 cursor-pointer border-none text-[#DC2626] dark:text-[#f87171] bg-red-500/6 dark:bg-red-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </Link>
          <button v-else @click="openLogin"
            class="hidden sm:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 cursor-pointer bg-[#0A0A0A] dark:bg-[#1A1A1A] text-white shadow-sm shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/10 hover:shadow-md hover:shadow-[#D4AF37]/20 dark:hover:shadow-[#D4AF37]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M17 11l4-4m0 0l-4-4m4 4H9"/>
            </svg>
          </button>

          <!-- Mobile hamburger -->
          <button @click="mobileOpen = !mobileOpen"
            class="md:hidden flex flex-col gap-[5px] justify-center items-center w-10 h-10 rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] hover:shadow-sm hover:shadow-[#D4AF37]/10 dark:hover:shadow-[#D4AF37]/10 transition-shadow">
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? 'translate-y-[7px] rotate-45' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? 'opacity-0' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? '-translate-y-[7px] -rotate-45' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
          </button>
        </div>
      </div>

      <!-- Mobile drawer -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2">
        <div v-if="mobileOpen" class="md:hidden px-4 pt-3 pb-5 flex flex-col gap-2 bg-[rgba(250,247,242,0.95)] dark:bg-[#1A1A1A]/95 backdrop-blur-xl border-t border-gray-200 dark:border-[#D4AF37]/20">

          <!-- Mobile Theme Toggle -->
          <div class="px-4 py-2">
            <ThemeToggle />
          </div>

          <div class="h-px my-1 bg-gray-200 dark:bg-[#D4AF37]/10"></div>

          <Link href="/" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
            :class="isActive('/') && !isActive('/dashboard') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Home
          </Link>

          <Link href="/custom-order" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
            :class="isActive('/custom-order') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
            </svg>
            Custom Order
          </Link>

          <Link v-if="page.props.auth.user" href="/wishlist" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline group transition-all"
            :class="isActive('/wishlist') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            Wishlist
            <span v-if="page.props.wishlistCount"
              class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full leading-none"
              :class="isActive('/wishlist') ? 'bg-gray-800/15 dark:bg-white/15 text-[#0A0A0A] dark:text-[#F5F5F5]' : 'bg-red-600 text-white'">
              {{ page.props.wishlistCount }}
            </span>
          </Link>
          <Link v-if="page.props.auth.user" href="/cart" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline group transition-all"
            :class="isActive('/cart') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            Cart
            <span v-if="page.props.cartCount"
              class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full leading-none"
              :class="isActive('/cart') ? 'bg-gray-800/15 dark:bg-white/15 text-[#0A0A0A] dark:text-[#F5F5F5]' : 'bg-[#D4AF37] text-white'">
              {{ page.props.cartCount }}
            </span>
          </Link>
          <button v-else @click="openLogin(); mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline block cursor-pointer text-left w-full group transition-all"
            :class="inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Sign In
          </button>

          <div class="h-px my-1 bg-gray-200 dark:bg-[#D4AF37]/10"></div>

          <Link v-if="page.props.auth.user" href="/Profile" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
            :class="isActive('/Profile') || isActive('/EditProfile') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profile
          </Link>
          <Link v-if="page.props.permissions?.canViewDashboard" href="/dashboard" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
            :class="isActive('/dashboard') ? activeMobileNavClasses : inactiveMobileNavClasses">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Admin
          </Link>
          <Link v-if="page.props.auth.user" href="/logout" method="post" as="button" @click="mobileOpen=false"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold block w-full text-left border-none cursor-pointer transition-all text-[#DC2626] dark:text-[#f87171] bg-red-500/6 dark:bg-red-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Logout
          </Link>
          <button v-else @click="openLogin(); mobileOpen=false"
            class="flex items-center justify-center gap-3 px-4 py-3 rounded-xl text-sm font-bold block w-full border-none cursor-pointer bg-[#0A0A0A] dark:bg-[#1A1A1A] text-white transition-all shadow-sm shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/10 hover:shadow-md hover:shadow-[#D4AF37]/20 dark:hover:shadow-[#D4AF37]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Sign In
          </button>
        </div>
      </transition>
    </header>

    <!-- PAGE CONTENT -->
    <main>
      <slot />
    </main>

    <!-- AUTH MODAL -->
    <AuthModal />

    <!-- NOTIFICATION -->
    <NotificationModal />

    <!-- CHATBOT -->
    <ChatBot />

    <!-- FIRST-LOGIN WELCOME TUTORIAL -->
    <OnboardingTour />

  </div>
</template>

<style scoped>
.logo-icon {
  transition: box-shadow 0.3s ease;
}

.logo-link:hover .logo-icon {
  box-shadow: 0 4px 16px rgba(212,175,55,0.35);
}
</style>
