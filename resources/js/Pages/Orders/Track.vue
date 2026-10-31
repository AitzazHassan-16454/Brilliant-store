<script setup>
import { Link, Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { motion } from "motion-v";
import { motionPresets as m } from "../../lib/motion.js";

const props = defineProps({
  order: Object,
  error: String,
  code: String,
});

const searchCode = ref(props.code || "");
const searching = ref(false);
const copied = ref(false);
const copiedBankField = ref("");

const fileInput = ref(null);
const filePreview = ref(null);
const isPdf = ref(false);
const showUploadSection = ref(false);

const slipForm = useForm({
  payment_slip: null,
});

const copyBankDetail = async (text, fieldName) => {
  try {
    await navigator.clipboard.writeText(text);
    copiedBankField.value = fieldName;
    setTimeout(() => (copiedBankField.value = ""), 1500);
  } catch {}
};

const onFileSelected = (e) => {
  const file = e.target.files?.[0];
  if (!file) return;
  slipForm.payment_slip = file;
  slipForm.clearErrors("payment_slip");
  isPdf.value = file.type === "application/pdf" || file.name.toLowerCase().endsWith(".pdf");

  if (!isPdf.value && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = (ev) => {
      filePreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    filePreview.value = null;
  }
};

const cancelSelectedFile = () => {
  slipForm.payment_slip = null;
  filePreview.value = null;
  isPdf.value = false;
  if (fileInput.value) fileInput.value.value = "";
};

const uploadSlip = () => {
  if (!slipForm.payment_slip || !props.order?.tracking_code) return;
  slipForm.post(`/track-order/${props.order.tracking_code}/payment-slip`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      cancelSelectedFile();
      showUploadSection.value = false;
    },
  });
};

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
    <motion.div
      :variants="m.staggerContainer(0.12, 0.1)"
      initial="hidden"
      animate="visible"
      class="text-center mb-8"
    >
      <motion.div :variants="m.itemScaleIn(0)" class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-[#D4AF37]/10 mb-4">
        <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
        </svg>
      </motion.div>
      <h1 :variants="m.itemFadeUp(1)" class="text-2xl sm:text-3xl font-semibold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Track Your Order</h1>
      <p :variants="m.itemFadeUp(2)" class="text-sm text-gray-500 dark:text-[#A0A0A0] mt-2 max-w-md mx-auto">
        Enter the tracking code you received after placing your order to see its live status.
      </p>
    </motion.div>

    <!-- SEARCH -->
    <motion.div
      :initial="{ opacity: 0, y: 24 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ delay: 0.25, duration: 0.5, ease: m.easeOutExpo }"
      class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5 sm:p-6 shadow-sm"
    >
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
    </motion.div>

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
    <motion.div
      v-if="order"
      :initial="{ opacity: 0, y: 24 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ duration: 0.5, ease: m.easeOutExpo }"
      class="mt-6 space-y-4"
    >

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

      <!-- PAYMENT SLIP / BANK TRANSFER SECTION -->
      <div
        v-if="order.payment_method === 'bank_transfer' || order.payment_slip_url"
        class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5 sm:p-6"
      >
        <div class="flex items-center justify-between gap-3 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-[#D4AF37]/15 flex items-center justify-center text-[#D4AF37] shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm6 3.75h.008v.008H21v-.008zm0-3h.008v.008H21v-.008zm0-3h.008v.008H21v-.008z"/></svg>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Bank Transfer Payment Verification</h2>
              <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Complete payment and attach your receipt to confirm your order.</p>
            </div>
          </div>

          <span
            v-if="order.payment_slip_url"
            class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider shrink-0"
            :class="order.status === 'pending'
              ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400'
              : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400'"
          >
            {{ order.status === 'pending' ? 'Slip Under Review' : 'Payment Verified' }}
          </span>
        </div>

        <!-- BANK ACCOUNT INFO CARD -->
        <div v-if="!order.payment_slip_url || showUploadSection" class="p-4 rounded-xl bg-gray-50 dark:bg-[#0A0A0A] border border-gray-100 dark:border-[#D4AF37]/15 mb-4">
          <p class="text-xs font-bold uppercase tracking-wider text-[#D4AF37] mb-2">Store Bank Account Details</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-[#1A1A1A] border border-gray-200/60 dark:border-white/5">
              <span class="text-gray-400 dark:text-[#A0A0A0]">Bank:</span>
              <span class="font-semibold text-[#1A1A1A] dark:text-white">Meezan Bank</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-[#1A1A1A] border border-gray-200/60 dark:border-white/5">
              <span class="text-gray-400 dark:text-[#A0A0A0]">Account Title:</span>
              <span class="font-semibold text-[#1A1A1A] dark:text-white">Brilliant Store</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-[#1A1A1A] border border-gray-200/60 dark:border-white/5">
              <span class="text-gray-400 dark:text-[#A0A0A0]">Account No:</span>
              <div class="flex items-center gap-1.5">
                <span class="font-mono font-semibold text-[#1A1A1A] dark:text-white">01020103456789</span>
                <button
                  type="button"
                  @click="copyBankDetail('01020103456789', 'acc')"
                  class="text-[10px] text-[#D4AF37] hover:underline cursor-pointer bg-transparent border-none p-0"
                >
                  {{ copiedBankField === 'acc' ? 'Copied' : 'Copy' }}
                </button>
              </div>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-[#1A1A1A] border border-gray-200/60 dark:border-white/5">
              <span class="text-gray-400 dark:text-[#A0A0A0]">IBAN:</span>
              <div class="flex items-center gap-1.5">
                <span class="font-mono font-semibold text-[#1A1A1A] dark:text-white">PK36MEZN01020103456789</span>
                <button
                  type="button"
                  @click="copyBankDetail('PK36MEZN01020103456789', 'iban')"
                  class="text-[10px] text-[#D4AF37] hover:underline cursor-pointer bg-transparent border-none p-0"
                >
                  {{ copiedBankField === 'iban' ? 'Copied' : 'Copy' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- UPLOADED SLIP PREVIEW -->
        <div v-if="order.payment_slip_url && !showUploadSection" class="p-4 rounded-xl border border-gray-100 dark:border-[#D4AF37]/15 bg-gray-50/50 dark:bg-[#0A0A0A]/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg overflow-hidden bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 border border-gray-200 dark:border-[#D4AF37]/20">
              <img
                v-if="order.payment_slip_url.match(/\.(jpg|jpeg|png|webp)$/i)"
                :src="order.payment_slip_url"
                alt="Payment Slip"
                class="w-full h-full object-cover cursor-pointer hover:scale-105 transition"
                @click="window.open(order.payment_slip_url, '_blank')"
              />
              <svg v-else class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Payment receipt uploaded</p>
              <p v-if="order.payment_slip_uploaded_at" class="text-[11px] text-gray-400 dark:text-[#A0A0A0] mt-0.5">
                Uploaded on {{ formatTime(order.payment_slip_uploaded_at) }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <a
              :href="order.payment_slip_url"
              target="_blank"
              class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 dark:border-[#D4AF37]/20 text-[#1A1A1A] dark:text-[#F5F5F5] hover:bg-gray-100 dark:hover:bg-[#21262d] transition no-underline"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
              View Document
            </a>
            <button
              v-if="order.status === 'pending'"
              type="button"
              @click="showUploadSection = true"
              class="flex-1 sm:flex-initial px-3 py-1.5 rounded-lg text-xs font-semibold border border-[#D4AF37]/30 text-[#D4AF37] hover:bg-[#D4AF37]/10 transition cursor-pointer bg-transparent"
            >
              Re-upload Slip
            </button>
          </div>
        </div>

        <!-- UPLOAD DROPZONE / FORM -->
        <div v-if="!order.payment_slip_url || showUploadSection" class="mt-2">
          <form @submit.prevent="uploadSlip" class="space-y-3">
            <div
              class="border-2 border-dashed rounded-xl p-5 text-center transition cursor-pointer"
              :class="filePreview || slipForm.payment_slip
                ? 'border-[#D4AF37] bg-[#D4AF37]/5'
                : 'border-gray-200 dark:border-[#D4AF37]/20 hover:border-[#D4AF37]/50 bg-white dark:bg-[#0A0A0A]'"
              @click="fileInput?.click()"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/png,image/webp,application/pdf"
                class="hidden"
                @change="onFileSelected"
              />

              <!-- FILE SELECTED PREVIEW -->
              <div v-if="filePreview || slipForm.payment_slip" class="flex flex-col items-center">
                <img
                  v-if="filePreview"
                  :src="filePreview"
                  class="max-h-36 rounded-lg shadow-sm border border-gray-200 dark:border-white/10 mb-2 object-contain"
                />
                <div v-else class="w-12 h-12 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center mb-2">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <p class="text-xs font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] truncate max-w-xs">
                  {{ slipForm.payment_slip?.name }}
                </p>
                <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0] mt-0.5">
                  {{ (slipForm.payment_slip?.size / (1024 * 1024)).toFixed(2) }} MB
                </p>
                <button
                  type="button"
                  @click.stop="cancelSelectedFile"
                  class="mt-2 text-xs font-semibold text-red-500 hover:underline cursor-pointer bg-transparent border-none p-0"
                >
                  Choose different file
                </button>
              </div>

              <!-- EMPTY DROPZONE -->
              <div v-else class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-xl bg-[#D4AF37]/10 flex items-center justify-center text-[#D4AF37] mb-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                </div>
                <p class="text-xs font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                  Click or drag and drop your payment receipt
                </p>
                <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0] mt-1">
                  Supports JPG, PNG, WEBP, or PDF (up to 5MB)
                </p>
              </div>
            </div>

            <p v-if="slipForm.errors.payment_slip" class="text-xs font-medium text-red-600">
              {{ slipForm.errors.payment_slip }}
            </p>

            <div class="flex items-center gap-2">
              <button
                type="submit"
                :disabled="!slipForm.payment_slip || slipForm.processing"
                class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-md shadow-[#D4AF37]/20 hover:brightness-105"
              >
                <svg v-if="slipForm.processing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                {{ slipForm.processing ? 'Uploading receipt...' : 'Upload Payment Slip' }}
              </button>

              <button
                v-if="order.payment_slip_url && showUploadSection"
                type="button"
                @click="showUploadSection = false; cancelSelectedFile()"
                class="px-4 py-2.5 rounded-xl text-xs font-semibold border border-gray-200 dark:border-[#D4AF37]/20 text-gray-600 dark:text-[#A0A0A0] hover:bg-gray-50 dark:hover:bg-[#21262d] transition cursor-pointer"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>

      </div>

      <!-- TIMELINE -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5 sm:p-6">
        <div class="flex items-center gap-2 mb-5">
          <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h2 class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Order Progress</h2>
        </div>

        <motion.div
          v-if="order.statuses?.length"
          :variants="m.staggerContainer(0.1, 0.05)"
          initial="hidden"
          animate="visible"
          class="relative ml-2.5"
        >
          <div class="absolute left-0 top-1 bottom-1 w-px bg-gray-200 dark:bg-[#30363d]"></div>
          <motion.div v-for="(s, idx) in order.statuses" :key="s.id" :variants="m.itemFadeUp(0)" class="relative flex items-start gap-3 pb-5 last:pb-0">
            <div :class="['w-2.5 h-2.5 rounded-full border-2 border-white dark:border-[#0A0A0A] shrink-0 mt-1 z-10', statusDot(s.status)]"></div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-semibold capitalize text-[#1A1A1A] dark:text-[#F5F5F5]">{{ s.status }}</span>
                <span class="text-[10px] text-gray-400 dark:text-[#A0A0A0]">{{ formatTime(s.created_at) }}</span>
              </div>
              <p v-if="s.note" class="text-xs text-gray-500 dark:text-[#A0A0A0] mt-0.5">{{ s.note }}</p>
            </div>
            <span v-if="idx === 0" class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wide bg-[#D4AF37]/10 text-[#B8960F] dark:text-[#D4AF37] shrink-0">Latest</span>
          </motion.div>
        </motion.div>
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
    </motion.div>

    <!-- EMPTY STATE -->
      <motion.div
        v-else-if="!error"
        :variants="m.staggerContainer(0.15, 0.05)"
        initial="hidden"
        animate="visible"
        class="mt-6 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-8 sm:p-10"
      >
      <div class="grid sm:grid-cols-3 gap-6 text-center">
        <motion.div :variants="m.itemFadeUp(0)">
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Place your order</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">You'll get a unique tracking code instantly.</p>
        </motion.div>
        <motion.div :variants="m.itemFadeUp(1)">
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Follow its journey</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">Track status updates in real time.</p>
        </motion.div>
        <motion.div :variants="m.itemFadeUp(2)">
          <div class="w-11 h-11 mx-auto rounded-xl bg-[#D4AF37]/10 flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-[#B8960F] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Get it delivered</p>
          <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-1">Receive your order right at your door.</p>
        </motion.div>
      </div>
      </motion.div>

  </div>

</div>
</template>
