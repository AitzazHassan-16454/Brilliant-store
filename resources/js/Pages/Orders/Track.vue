<script setup>
import { Link, Head, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
  order: Object,
  error: String,
  code: String,
});

const searchCode = ref(props.code || "");
const searching = ref(false);
const copied = ref(false);

watch(
  () => props.code,
  (val) => {
    if (val) searchCode.value = val;
  }
);

const trackOrder = () => {
  const code = searchCode.value.trim();
  if (!code) return;
  searching.value = true;
  router.get("/track-order", { code }, {
    preserveScroll: true,
    replace: true,
    onFinish: () => (searching.value = false),
  });
};

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.order.tracking_code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
  } catch {}
};

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
  } catch {}
};

const statusMeta = (s) => ({
  pending: { label: "Pending", dot: "bg-[#d29922]", bg: "bg-amber-100 text-amber-700 dark:bg-[#d29922]/10 dark:text-[#d29922]" },
  confirmed: { label: "Confirmed", dot: "bg-[#58a6ff]", bg: "bg-blue-100 text-blue-700 dark:bg-[#58a6ff]/10 dark:text-[#58a6ff]" },
  delivered: { label: "Delivered", dot: "bg-[#3fb950]", bg: "bg-emerald-100 text-emerald-700 dark:bg-[#3fb950]/10 dark:text-[#3fb950]" },
}[s] || { label: s, dot: "bg-gray-400", bg: "bg-gray-100 text-gray-700 dark:bg-[#2A2A2A] dark:text-[#A0A0A0]" });

const statusColor = (s) => statusMeta(s).bg;
const statusDot = (s) => statusMeta(s).dot;

const formatTime = (date) => {
  return new Date(date).toLocaleString("en-US", {
    month: "short", day: "numeric", year: "numeric", hour: "numeric", minute: "2-digit",
  });
};
</script>

<template>
<Head>
  <title>Track Your Order — Brilliant</title>
  <meta name="description" content="Track your order status in real time using your unique tracking code." />
</Head>

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-14">

    <!-- HEADER -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#D4AF37]/10 mb-4">
        <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
        </svg>
      </div>
      <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Track Your Order</h1>
      <p class="text-sm text-gray-500 dark:text-[#A0A0A0] mt-2 max-w-md mx-auto">
        Enter the tracking code you received after placing your order to see its live status.
      </p>
    </div>

    <!-- SEARCH -->
    <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5 sm:p-6 shadow-sm">
      <form @submit.prevent="trackOrder" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input
            v-model="searchCode"
            type="text"
            placeholder="e.g. BR-7FK2P9QZ"
            autocomplete="off"
            class="w-full pl-10 pr-4 py-3 rounded-xl text-sm border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-[#8b949e] outline-none transition focus:border-[#D4AF37]/60 font-mono uppercase tracking-wide"
          />
        </div>
        <button
          type="submit"
          :disabled="searching || !searchCode.trim()"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30 hover:-translate-y-0.5"
        >
          <svg v-if="searching" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M16.5 9.5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          {{ searching ? "Searching..." : "Track Order" }}
        </button>
      </form>
    </div>

    <!-- ERROR -->
    <div
      v-if="error"
      class="mt-6 flex items-start gap-3 px-4 py-3.5 rounded-xl border border-red-200 dark:border-[#f85149]/30 bg-red-50 dark:bg-[#f85149]/10 text-red-700 dark:text-[#f85149]"
    >
      <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div>
        <p class="text-sm font-semibold">Order not found</p>
        <p class="text-sm mt-0.5">{{ error }}</p>
      </div>
    </div>

    <!-- ORDER RESULT -->
    <div v-if="order" class="mt-6 space-y-4">

      <!-- TRACKING CODE BANNER -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-[#D4AF37]/30 dark:border-[#D4AF37]/30 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Your Tracking Code</p>
          <p class="mt-1 text-2xl font-bold font-mono tracking-wide text-[#1A1A1A] dark:text-[#D4AF37]">{{ order.tracking_code }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="copyCode" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 text-gray-600 dark:text-[#A0A0A0] hover:bg-gray-50 dark:hover:bg-[#21262d]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            {{ copied ? "Copied!" : "Copy" }}
          </button>
          <button @click="copyLink" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 text-gray-600 dark:text-[#A0A0A0] hover:bg-gray-50 dark:hover:bg-[#21262d]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
            {{ copied ? "Copied!" : "Copy Link" }}
          </button>
        </div>
      </div>

      <!-- SUMMARY -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-50 dark:border-[#D4AF37]/20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#1A1A1A] dark:bg-[#2A2A2A] flex items-center justify-center text-xs font-bold text-white shrink-0">
              #{{ String(order.id).slice(-3) }}
            </div>
            <div>
              <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Order #{{ order.id }}</p>
              <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Placed on {{ formatTime(order.created_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-lg text-[11px] font-semibold uppercase tracking-wide leading-none" :class="statusColor(order.status)">
              {{ order.status }}
            </span>
            <span class="text-lg font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">${{ order.total }}</span>
          </div>
        </div>

        <!-- ITEMS -->
        <div class="px-5 py-3 space-y-2">
          <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <img v-if="item.product?.image" :src="`/storage/${item.product.image}`" class="w-10 h-10 rounded-lg object-cover border border-gray-100 dark:border-[#D4AF37]/20 shrink-0" />
              <div v-else class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-[#2A2A2A] shrink-0"></div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ item.product?.name ?? "Product" }}</p>
                <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Qty: {{ item.quantity }} &times; ${{ item.price }}</p>
              </div>
            </div>
            <span class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] shrink-0 ml-3">${{ (item.price * item.quantity).toFixed(2) }}</span>
          </div>
        </div>

        <!-- SHIPPING -->
        <div v-if="order.shipping_name" class="px-5 py-4 border-t border-gray-50 dark:border-[#D4AF37]/20">
          <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-400 dark:text-[#A0A0A0]">
            <span v-if="order.shipping_name" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              {{ order.shipping_name }}
            </span>
            <span v-if="order.shipping_email" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              {{ order.shipping_email }}
            </span>
            <span v-if="order.shipping_phone" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              {{ order.shipping_phone }}
            </span>
            <span v-if="order.shipping_address" class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ order.shipping_address }}{{ order.shipping_city ? ", " + order.shipping_city : "" }}{{ order.shipping_postal_code ? " " + order.shipping_postal_code : "" }}
            </span>
          </div>
        </div>
      </div>

      <!-- TIMELINE -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5 sm:p-6">
        <div class="flex items-center gap-2 mb-5">
          <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h2 class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Order Progress</h2>
        </div>

        <div v-if="order.statuses?.length" class="relative ml-2.5">
          <div class="absolute left-0 top-1 bottom-1 w-px bg-gray-200 dark:bg-[#30363d]"></div>
          <div v-for="(s, idx) in order.statuses" :key="s.id" class="relative flex items-start gap-3 pb-5 last:pb-0">
            <div :class="['w-2.5 h-2.5 rounded-full border-2 border-white dark:border-[#0A0A0A] shrink-0 mt-1 z-10', statusDot(s.status)]"></div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-semibold capitalize text-[#1A1A1A] dark:text-[#F5F5F5]">{{ s.status }}</span>
                <span class="text-[10px] text-gray-400 dark:text-[#A0A0A0]">{{ formatTime(s.created_at) }}</span>
              </div>
              <p v-if="s.note" class="text-xs text-gray-500 dark:text-[#A0A0A0] mt-0.5">{{ s.note }}</p>
            </div>
            <span v-if="idx === 0" class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wide bg-[#D4AF37]/10 text-[#B8960F] dark:text-[#D4AF37] shrink-0">Latest</span>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 dark:text-[#A0A0A0]">No tracking updates yet.</p>
      </div>

      <!-- FOOTER -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        <Link href="/" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-[#A0A0A0] hover:text-gray-700 dark:hover:text-[#c9d1d9] border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] hover:bg-gray-50 dark:hover:bg-[#21262d] px-4 py-2 rounded-lg transition no-underline">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to Shopping
        </Link>
        <Link v-if="$page.props.auth.user" href="/orders" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-[#A0A0A0] hover:text-gray-700 dark:hover:text-[#c9d1d9] border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] hover:bg-gray-50 dark:hover:bg-[#21262d] px-4 py-2 rounded-lg transition no-underline">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          My Orders
        </Link>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div
      v-else-if="!error"
      class="mt-6 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-8 sm:p-10"
    >
      <div class="grid sm:grid-cols-3 gap-6 text-center">
        <div>
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Place your order</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">You'll get a unique tracking code instantly.</p>
        </div>
        <div>
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Follow its journey</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">Track status updates in real time.</p>
        </div>
        <div>
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Get it delivered</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">Receive your order right at your door.</p>
        </div>
      </div>
    </div>

  </div>

</div>
</template>
