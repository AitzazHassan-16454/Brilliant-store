<script setup>
import { usePage, Link, Head } from "@inertiajs/vue3"
import { useInView } from "../composables/useInView.js"

const page = usePage()
const user = page.props.auth.user

defineProps({
  ordersCount: Number
})

const { target: statsRef, isInView: statsVisible } = useInView()
const { target: actionsRef, isInView: actionsVisible } = useInView()
</script>

<template>
<Head title="My Profile" />

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]" style="font-family:'Inter',system-ui,sans-serif;">

  <!-- HERO / PROFILE BANNER -->
  <div class="bg-white/60 dark:bg-[#1A1A1A] border-b border-gray-200 dark:border-[#D4AF37]/20 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">

      <div class="flex items-center gap-6">
        <div class="relative shrink-0">
          <div class="w-20 h-20 rounded-2xl overflow-hidden flex items-center justify-center border border-white/25 dark:border-[#D4AF37]/20 bg-white/45 dark:bg-[#0A0A0A] shadow-xl">
            <img
              v-if="user.avatar"
              :src="user.avatar"
              :alt="user.name"
              class="w-full h-full object-cover"
            />
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-gray-400 dark:text-[#A0A0A0]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
          </div>
          <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-[#D4AF37] border-2 border-[#FAF7F2] dark:border-[#0d1117]"></span>
        </div>

        <div>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-[#F5F5F5]">{{ user.name }}</h2>
          <p class="text-sm mt-1 text-gray-500 dark:text-[#A0A0A0]">{{ user.email }}</p>
          <div class="flex flex-wrap gap-2 mt-3">
            <span v-if="user.role === 'user'"
              class="px-3 py-1 rounded-full text-xs font-semibold bg-white/60 dark:bg-[#1A1A1A]/60 border border-black/10 dark:border-[#D4AF37]/20 text-gray-500 dark:text-[#A0A0A0]">
              Customer
            </span>
            <span v-else-if="user.role === 'admin'"
              class="px-3 py-1 rounded-full text-xs font-semibold bg-[#D4AF37]/10 border border-[#D4AF37]/20 text-[#D4AF37] dark:text-[#D4AF37]">
              Administrator
            </span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <Link
          href="/orders"
          class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 hover:-translate-y-0.5 no-underline border border-black/10 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A]/60 text-gray-900 dark:text-[#F5F5F5]">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
          My Orders
        </Link>
        <Link
          href="/EditProfile"
          class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 hover:-translate-y-0.5 no-underline text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
          Edit Profile
        </Link>
      </div>

    </div>
  </div>

  <!-- BODY -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- STATS -->
    <div ref="statsRef" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

      <div :class="[statsVisible ? 'animate-fade-in-up' : 'opacity-0']"
        class="rounded-2xl p-6 flex items-center justify-between transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl" style="animation-delay:0s">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Cart Items</p>
          <p class="text-4xl font-bold mt-2 tracking-tight text-gray-900 dark:text-[#F5F5F5]">{{ $page.props.cartCount }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
        </div>
      </div>

      <div :class="[statsVisible ? 'animate-fade-in-up' : 'opacity-0']"
        class="rounded-2xl p-6 flex items-center justify-between transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl" style="animation-delay:0.08s">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Total Orders</p>
          <p class="text-4xl font-bold mt-2 tracking-tight text-gray-900 dark:text-[#F5F5F5]">{{ ordersCount }}</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-gray-900/10 dark:bg-[#c9d1d9]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-900 dark:text-[#F5F5F5]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        </div>
      </div>

      <div :class="[statsVisible ? 'animate-fade-in-up' : 'opacity-0']"
        class="rounded-2xl p-6 flex items-center justify-between transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl" style="animation-delay:0.16s">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Account Status</p>
          <p class="text-3xl font-bold mt-2 tracking-tight text-[#D4AF37] dark:text-[#D4AF37]">Active</p>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>

    </div>

    <!-- QUICK ACTIONS -->
    <p ref="actionsRef" :class="[actionsVisible ? 'animate-fade-in-up' : 'opacity-0']"
      class="text-xs font-bold uppercase tracking-widest mb-4 text-gray-400 dark:text-[#A0A0A0]">Quick Actions</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

      <Link
        href="/cart"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">View Cart</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">Manage items you've added for checkout.</p>
      </Link>

      <Link
        href="/orders"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-gray-900/10 dark:bg-[#c9d1d9]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-900 dark:text-[#F5F5F5]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Orders</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">Track and review your order history.</p>
      </Link>

      <Link
        href="/wishlist"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-red-500/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Wishlist</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">View the products you've saved for later.</p>
      </Link>

      <Link
        href="/track-order"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Track Order</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">Follow your orders using their tracking code.</p>
      </Link>

      <Link
        href="/EditProfile"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Profile</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">Update your personal account settings.</p>
      </Link>

      <Link
        href="/"
        class="group rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 no-underline border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition bg-[#D4AF37]/10">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
        </div>
        <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Explore Shop</p>
        <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">Discover new arrivals and collections.</p>
      </Link>

    </div>

  </div>
</div>
</template>
