<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue"
import { usePage, Link } from "@inertiajs/vue3"
import AuthModal from "../Components/AuthModal.vue"
import NotificationModal from "../Components/NotificationModal.vue"
import { useAuthModal } from "../composables/useAuthModal.js"
import ThemeToggle from "../Pages/components/ThemeToggle.vue"
import ChatBot from "../Components/ChatBot.vue"
import OnboardingTour from "../Components/OnboardingTour.vue"
import { AnimatePresence, motion } from "motion-v"
import { motionPresets as m } from "../lib/motion.js"

const page = usePage()
const { openLogin } = useAuthModal()
const mobileOpen = ref(false)
const profileOpen = ref(false)

const site = computed(() => page.props.site ?? {})
const siteName = computed(() => site.value.site_name || 'Brilliant')
const siteTagline = computed(() => site.value.tagline || 'Premium Store')
const siteLogo = computed(() => site.value.logo_url || null)

const dropdownVariants = {
  hidden: { opacity: 0, y: -8, scale: 0.96 },
  visible: { opacity: 1, y: 0, scale: 1, transition: { duration: 0.18, ease: m.easeOutExpo } },
  exit: { opacity: 0, y: -8, scale: 0.96, transition: { duration: 0.12, ease: "easeIn" } },
}

const backdropVariants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { duration: 0.2, ease: "easeOut" } },
  exit: { opacity: 0, transition: { duration: 0.2, ease: "easeIn" } },
}

const drawerVariants = {
  hidden: { x: "100%" },
  visible: { x: 0, transition: { type: "spring", stiffness: 320, damping: 34, mass: 0.8 } },
  exit: { x: "100%", transition: { duration: 0.25, ease: m.easeOutExpo } },
}

const pageVariants = {
  initial: { opacity: 0 },
  enter: { opacity: 1, transition: { duration: 0.22, ease: m.easeOutExpo } },
  exit: { opacity: 0, transition: { duration: 0.15, ease: "easeIn" } },
}

function toggleProfile(event) {
  event.stopPropagation()
  profileOpen.value = !profileOpen.value
}

function closeProfile() {
  profileOpen.value = false
}

function closeMobile() {
  mobileOpen.value = false
}

function onKeydown(event) {
  if (event.key === "Escape") {
    closeMobile()
  }
}

watch(mobileOpen, (isOpen) => {
  document.body.style.overflow = isOpen ? "hidden" : ""
})

watch(() => page.url, closeMobile)

onMounted(() => {
  document.addEventListener("click", closeProfile)
  document.addEventListener("keydown", onKeydown)
})
onBeforeUnmount(() => {
  document.removeEventListener("click", closeProfile)
  document.removeEventListener("keydown", onKeydown)
  document.body.style.overflow = ""
})

const currentPath = computed(() => {
  try { return new URL(page.url, window.location.origin).pathname }
  catch { return page.url }
})

function isActive(path) {
  if (path === "/") return currentPath.value === "/"
  return currentPath.value.startsWith(path)
}

const activeNavClasses = 'bg-[#FAF7F2] dark:bg-[#1A1A1A] text-[#D4AF37] dark:text-[#D4AF37] border border-[#D4AF37]/30 dark:border-[#D4AF37]/30 shadow-sm shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/20'
const inactiveNavClasses = 'text-[#1A1A1A] dark:text-[#F5F5F5] bg-transparent border border-gray-200 dark:border-[#D4AF37]/20   '

const activeMobileNavClasses = 'bg-[#FAF7F2] dark:bg-[#1A1A1A] text-[#D4AF37] dark:text-[#D4AF37] border border-[#D4AF37]/30 dark:border-[#D4AF37]/30 shadow-sm shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/20'
const inactiveMobileNavClasses = 'text-[#1A1A1A] dark:text-[#F5F5F5] bg-transparent border border-gray-200 dark:border-[#D4AF37]/20   '
</script>

<template>
  <div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5] overflow-x-clip w-full max-w-full" style="font-family:'Inter',system-ui,sans-serif;">

    <!-- HEADER (Sticky) -->
    <motion.header
      :initial="{ y: -60, opacity: 0 }"
      :animate="{ y: 0, opacity: 1 }"
      :transition="{ duration: 0.5, ease: m.easeOutExpo }"
      class="sticky top-0 z-50 transition-all duration-300 bg-[rgba(250,247,242,0.85)] dark:bg-[#1A1A1A]/85 backdrop-blur-xl border-b border-gray-200 dark:border-[#D4AF37]/20 shadow-sm shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 shrink-0 no-underline group logo-link">
          <div v-if="siteLogo" class="relative w-9 h-9 rounded-xl flex items-center justify-center overflow-hidden logo-icon bg-white transition-shadow duration-300  ">
            <img :src="siteLogo" :alt="siteName" class="w-full h-full object-contain p-0.5" />
          </div>
          <div v-else class="relative w-9 h-9 rounded-xl flex items-center justify-center overflow-hidden logo-icon transition-shadow duration-300  " style="background:linear-gradient(135deg,var(--gold),var(--gold-dark));">
            <svg class="w-5 h-5 relative z-10" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M6 3h12l4 6-10 13L2 9z"/>
              <path d="M2 9h20"/>
              <path d="M12 22L6 9"/>
              <path d="M12 22l6-13"/>
            </svg>
          </div>
          <div class="flex flex-col leading-none">
            <span class="text-lg font-extrabold tracking-tight transition-colors duration-200 logo-text text-[#1A1A1A] dark:text-[#F5F5F5]">{{ siteName }}</span>
            <span class="text-[10px] font-medium tracking-widest uppercase transition-colors duration-200 logo-tagline text-[#9B9B9B] dark:text-[#A0A0A0]">{{ siteTagline }}</span>
          </div>
        </Link>

        <!-- Right Actions -->
        <div class="flex items-center gap-2">

          <!-- Theme Toggle -->
          <ThemeToggle />

          <!-- Home -->
          <Link href="/"
            class="hidden lg:inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/') && !isActive('/dashboard') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span class="text-sm font-semibold">Home</span>
          </Link>

          <!-- Products -->
          <Link href="/all-products"
            class="hidden lg:inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/all-products') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span class="text-sm font-semibold">Products</span>
          </Link>

          <!-- Custom Order -->
          <Link href="/custom-order"
            class="hidden lg:inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/custom-order') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
            </svg>
            <span class="text-sm font-semibold">Custom Order</span>
          </Link>

          <!-- Cart -->
          <Link v-if="page.props.auth.user" href="/cart"
            class="inline-flex h-10 w-10 lg:h-auto lg:w-auto items-center justify-center gap-2 rounded-xl px-2 py-2 lg:px-3 transition-all duration-200 no-underline group relative"
            :class="isActive('/cart') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span class="hidden lg:inline text-sm font-semibold">Cart</span>
            <span v-if="page.props.cartCount"
              class="absolute -top-1 -right-1 px-1 py-0.5 text-[9px] font-bold rounded-full leading-none"
              :class="isActive('/cart') ? 'bg-gray-800/15 dark:bg-white/15 text-[#0A0A0A] dark:text-[#F5F5F5]' : 'bg-[#D4AF37] text-white'"
            >
              <motion.span
                :key="page.props.cartCount"
                :initial="{ scale: 0.4, opacity: 0 }"
                :animate="{ scale: 1, opacity: 1 }"
                :transition="{ type: 'spring', stiffness: 500, damping: 18 }"
                class="block"
              >
                {{ page.props.cartCount }}
              </motion.span>
            </span>
          </Link>

          <!-- Profile dropdown -->
          <div v-if="page.props.auth.user" class="relative hidden lg:block"
            @mouseenter="profileOpen = true"
            @mouseleave="profileOpen = false">
            <button @click.stop="toggleProfile"
              class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 cursor-pointer border border-transparent"
              :class="profileOpen || isActive('/Profile') || isActive('/EditProfile') ? activeNavClasses : inactiveNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <span class="text-sm font-semibold">Profile</span>
              <span v-if="page.props.wishlistCount"
                class="absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-red-500"></span>
            </button>

            <AnimatePresence>
              <motion.div
                v-if="profileOpen"
                :variants="dropdownVariants"
                initial="hidden"
                animate="visible"
                exit="exit"
                class="absolute right-0 mt-2 w-60 rounded-2xl overflow-hidden border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-2xl shadow-black/10 z-50"
              >
                <div class="px-4 py-3 border-b border-gray-100 dark:border-[#D4AF37]/10">
                  <p class="text-sm font-bold text-gray-900 dark:text-[#F5F5F5]">{{ page.props.auth.user.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-[#A0A0A0] truncate">{{ page.props.auth.user.email }}</p>
                </div>
                <div class="p-1.5 flex flex-col">
                  <Link href="/Profile" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold no-underline transition-all"
                    :class="isActive('/Profile') ? 'bg-[#D4AF37]/10 text-[#D4AF37]' : 'text-gray-700 dark:text-[#E5E5E5] hover:bg-black/5 dark:hover:bg-white/5'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                  </Link>
                  <Link href="/wishlist" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold no-underline transition-all"
                    :class="isActive('/wishlist') ? 'bg-[#D4AF37]/10 text-[#D4AF37]' : 'text-gray-700 dark:text-[#E5E5E5] hover:bg-black/5 dark:hover:bg-white/5'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    Wishlist
                    <span v-if="page.props.wishlistCount"
                      class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full leading-none bg-red-600 text-white">
                      {{ page.props.wishlistCount }}
                    </span>
                  </Link>
                  <Link href="/track-order" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold no-underline transition-all"
                    :class="isActive('/track-order') ? 'bg-[#D4AF37]/10 text-[#D4AF37]' : 'text-gray-700 dark:text-[#E5E5E5] hover:bg-black/5 dark:hover:bg-white/5'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    Track Order
                  </Link>
                  <Link href="/EditProfile" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold no-underline transition-all"
                    :class="isActive('/EditProfile') ? 'bg-[#D4AF37]/10 text-[#D4AF37]' : 'text-gray-700 dark:text-[#E5E5E5] hover:bg-black/5 dark:hover:bg-white/5'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    Edit Profile
                  </Link>
                </div>
              </motion.div>
            </AnimatePresence>
          </div>

          <!-- Admin -->
          <Link v-if="page.props.permissions?.canViewDashboard" href="/dashboard"
            class="hidden lg:inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 no-underline"
            :class="isActive('/dashboard') ? activeNavClasses : inactiveNavClasses"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Admin
          </Link>

          <!-- Logout / Sign In -->
          <Link v-if="page.props.auth.user" href="/logout" method="post" as="button"
            class="hidden lg:inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 cursor-pointer border-none text-[#DC2626] dark:text-[#f87171] bg-red-500/6 dark:bg-red-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="text-sm font-semibold">Logout</span>
          </Link>
          <button v-else @click="openLogin"
            class="hidden lg:inline-flex items-center justify-center px-3 py-2 rounded-xl transition-all duration-200 cursor-pointer bg-[#0A0A0A] dark:bg-[#1A1A1A] text-white shadow-sm shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/10   ">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M17 11l4-4m0 0l-4-4m4 4H9"/>
            </svg>
          </button>

          <!-- Mobile hamburger -->
          <button @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-controls="mobile-drawer"
            aria-label="Open menu"
            class="lg:hidden flex flex-col gap-[5px] justify-center items-center w-10 h-10 rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]    transition-shadow">
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? 'translate-y-[7px] rotate-45' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? 'opacity-0' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
            <span :class="['block w-5 h-[2px] rounded transition-all duration-300', mobileOpen ? '-translate-y-[7px] -rotate-45' : '']" class="bg-[#1A1A1A] dark:bg-[#F5F5F5]"></span>
          </button>
        </div>
      </div>
    </motion.header>

    <!-- MOBILE DRAWER -->
    <Teleport to="body">
      <AnimatePresence>
        <motion.div
          v-if="mobileOpen"
          key="drawer-backdrop"
          :variants="backdropVariants"
          initial="hidden"
          animate="visible"
          exit="exit"
          @click="closeMobile"
          class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm lg:hidden"
        />

        <motion.aside
          v-if="mobileOpen"
          key="drawer-panel"
          id="mobile-drawer"
          :variants="drawerVariants"
          initial="hidden"
          animate="visible"
          exit="exit"
          role="dialog"
          aria-modal="true"
          aria-label="Mobile navigation"
          class="fixed top-0 right-0 z-[70] h-dvh w-[86%] max-w-sm flex flex-col bg-[#FAF7F2] dark:bg-[#1A1A1A] border-l border-gray-200 dark:border-[#D4AF37]/20 shadow-2xl shadow-black/20 lg:hidden"
        >
          <!-- Drawer header -->
          <div class="flex items-center justify-between gap-3 px-5 h-16 shrink-0 border-b border-gray-200 dark:border-[#D4AF37]/20">
            <Link href="/" @click="closeMobile" class="flex items-center gap-3 shrink-0 no-underline">
              <div v-if="siteLogo" class="w-9 h-9 rounded-xl flex items-center justify-center overflow-hidden bg-white">
                <img :src="siteLogo" :alt="siteName" class="w-full h-full object-contain p-0.5" />
              </div>
              <div v-else class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,var(--gold),var(--gold-dark));">
                <svg class="w-5 h-5" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                  <path d="M6 3h12l4 6-10 13L2 9z"/><path d="M2 9h20"/><path d="M12 22L6 9"/><path d="M12 22l6-13"/>
                </svg>
              </div>
              <div class="flex flex-col leading-none">
                <span class="text-lg font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ siteName }}</span>
                <span class="text-[10px] font-medium tracking-widest uppercase text-[#9B9B9B] dark:text-[#A0A0A0]">{{ siteTagline }}</span>
              </div>
            </Link>

            <button @click="closeMobile" aria-label="Close menu"
              class="flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]   transition-shadow cursor-pointer">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Drawer navigation -->
          <nav class="flex-1 flex flex-col gap-1.5 px-3 py-4 overflow-y-auto">
            <Link href="/" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/') && !isActive('/dashboard') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Home
            </Link>

            <Link href="/all-products" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/all-products') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
              </svg>
              Products
            </Link>

            <Link href="/custom-order" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/custom-order') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
              </svg>
              Custom Order
            </Link>

            <Link href="/about" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/about') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12l4 6-10 13L2 9z"/><path d="M2 9h20"/><path d="M12 22L6 9"/><path d="M12 22l6-13"/>
              </svg>
              About Us
            </Link>

            <Link v-if="page.props.auth.user" href="/cart" @click="closeMobile"
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

            <div class="h-px my-1 bg-gray-200 dark:bg-[#D4AF37]/10"></div>

            <Link v-if="page.props.auth.user" href="/Profile" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/Profile') || isActive('/EditProfile') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              Profile
            </Link>

            <Link v-if="page.props.auth.user" href="/wishlist" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/wishlist') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
              Wishlist
              <span v-if="page.props.wishlistCount"
                class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full leading-none bg-red-600 text-white">
                {{ page.props.wishlistCount }}
              </span>
            </Link>

            <Link v-if="page.props.permissions?.canViewDashboard" href="/dashboard" @click="closeMobile"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold no-underline transition-all"
              :class="isActive('/dashboard') ? activeMobileNavClasses : inactiveMobileNavClasses">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
              Admin
            </Link>
          </nav>

          <!-- Drawer footer -->
          <div class="shrink-0 flex flex-col gap-2 px-3 py-4 border-t border-gray-200 dark:border-[#D4AF37]/20">
            <Link v-if="page.props.auth.user" href="/logout" method="post" as="button" @click="closeMobile"
              class="flex items-center justify-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold w-full border-none cursor-pointer transition-all text-[#DC2626] dark:text-[#f87171] bg-red-500/6 dark:bg-red-500/10">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              Logout
            </Link>

            <button v-else @click="openLogin(); closeMobile"
              class="flex items-center justify-center gap-3 px-4 py-3 rounded-xl text-sm font-bold w-full border-none cursor-pointer bg-[#0A0A0A] dark:bg-[#1A1A1A] text-white transition-all shadow-sm shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/10   ">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
              </svg>
              Sign In
            </button>
          </div>
        </motion.aside>
      </AnimatePresence>
    </Teleport>

    <!-- PAGE CONTENT -->
    <main class="w-full max-w-full overflow-x-clip">
      <AnimatePresence mode="wait">
        <motion.div
          :key="page.url"
          :variants="pageVariants"
          initial="initial"
          animate="enter"
          exit="exit"
        >
          <slot />
        </motion.div>
      </AnimatePresence>
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
