<script setup>
import { router, Head } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { debounce } from "lodash";
import Sidebar from "../components/Sidebar.vue";
import Modal from "../../Components/Modal.vue";

defineOptions({ layout: false });

const props = defineProps({
  orders: Array,
});

const deleteId = ref(null);
const confirmStatus = ref(null);
const expandedOrder = ref(null);
const viewingSlipOrder = ref(null);
const removingSlipId = ref(null);
const searchQuery = ref("");
const activeFilter = ref("all");
const debouncedSearch = ref("");

const updateSearch = debounce((val) => {
  debouncedSearch.value = val;
}, 300);

watch(searchQuery, (val) => updateSearch(val));

const filters = [
  { key: "all", label: "All" },
  { key: "pending", label: "Pending" },
  { key: "confirmed", label: "Confirmed" },
  { key: "delivered", label: "Delivered" },
];

const stats = computed(() => {
  const pending = props.orders.filter((o) => o.status === "pending").length;
  const confirmed = props.orders.filter((o) => o.status === "confirmed").length;
  const delivered = props.orders.filter((o) => o.status === "delivered").length;
  const revenue = props.orders.reduce((s, o) => s + parseFloat(o.total || 0), 0);
  return { total: props.orders.length, pending, confirmed, delivered, revenue: revenue.toFixed(2) };
});

const filteredOrders = computed(() => {
  let result = [...props.orders];
  if (activeFilter.value !== "all") result = result.filter((o) => o.status === activeFilter.value);
  if (debouncedSearch.value.trim()) {
    const q = debouncedSearch.value.toLowerCase().trim();
    result = result.filter((o) =>
      String(o.id).includes(q) ||
      o.user?.name?.toLowerCase().includes(q) ||
      o.items?.some((item) => item.product?.name?.toLowerCase().includes(q))
    );
  }
  return result;
});

const toggleTimeline = (orderId) => {
  expandedOrder.value = expandedOrder.value === orderId ? null : orderId;
};

const requestStatusChange = (id, status) => {
  confirmStatus.value = { id, status };
};

const changeStatus = () => {
  router.post(`/orders/${confirmStatus.value.id}/status`, { status: confirmStatus.value.status }, {
    preserveScroll: true,
    onFinish: () => (confirmStatus.value = null),
  });
};

const confirmDelete = (id) => {
  deleteId.value = id;
};

const destroy = () => {
  router.delete(`/orders/${deleteId.value}`, {
    preserveScroll: true,
    onFinish: () => (deleteId.value = null),
  });
};

const viewSlip = (order) => {
  viewingSlipOrder.value = order;
};

const confirmRemoveSlip = (id) => {
  removingSlipId.value = id;
};

const removeSlip = () => {
  if (!removingSlipId.value) return;
  router.delete(`/orders/${removingSlipId.value}/payment-slip`, {
    preserveScroll: true,
    onFinish: () => (removingSlipId.value = null),
  });
};

const statusBadge = (s) => ({
  pending: "bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-500/20 dark:bg-[#d29922]/10 dark:text-[#d29922] dark:ring-[#d29922]/30",
  confirmed: "bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-500/20 dark:bg-[#58a6ff]/10 dark:text-[#58a6ff] dark:ring-[#58a6ff]/30",
  delivered: "bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-500/20 dark:bg-[#3fb950]/10 dark:text-[#3fb950] dark:ring-[#3fb950]/30",
}[s] || "bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20 dark:bg-[#2A2A2A] dark:text-[#A0A0A0] dark:ring-[#30363d]");

const statusDot = (s) => ({
  pending: "bg-amber-500",
  confirmed: "bg-blue-500",
  delivered: "bg-emerald-500",
}[s] || "bg-gray-400");

const formatTime = (date) => {
  return new Date(date).toLocaleString("en-US", {
    month: "short", day: "numeric", year: "numeric", hour: "numeric", minute: "2-digit",
  });
};

const timeAgo = (date) => {
  const s = Math.floor((new Date() - new Date(date)) / 1000);
  if (s < 60) return "just now";
  const m = Math.floor(s / 60);
  if (m < 60) return `${m}m ago`;
  const h = Math.floor(m / 60);
  if (h < 24) return `${h}h ago`;
  const d = Math.floor(h / 24);
  if (d < 7) return `${d}d ago`;
  return formatTime(date);
};

const nextStatuses = (current) => ({
  pending: ["confirmed", "delivered"],
  confirmed: ["delivered"],
  delivered: [],
}[current] || []);
</script>

<template>
  <Head title="Orders" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <Sidebar />

    <main class="flex-1 min-w-0 overflow-y-auto">

      <!-- HEADER -->
      <div class="static lg:sticky lg:top-0 z-20 bg-white/90 dark:bg-[#0A0A0A]/90 backdrop-blur-md border-b border-gray-200 dark:border-[#D4AF37]/20">
        <div class="min-h-16 px-4 sm:px-6 lg:px-8 py-3.5 sm:py-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-0">
          <div>
            <h1 class="text-lg sm:text-xl font-semibold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Orders Management</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">Manage customer orders, track shipments, and review payment slips</p>
          </div>
          <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0]">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            <span class="font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ stats.total }}</span> total orders
          </div>
        </div>
      </div>

      <div class="p-3.5 sm:p-6 lg:p-8 space-y-5 sm:space-y-6">

        <!-- STATS ROW (Responsive 2 cols on mobile, 4 on desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
          <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-3.5 sm:p-5 border border-gray-200 dark:border-[#D4AF37]/20 shadow-xs">
            <div class="flex items-center justify-between">
              <p class="text-xs sm:text-sm font-medium text-gray-400 dark:text-[#A0A0A0]">Total</p>
              <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] flex items-center justify-center text-gray-400 dark:text-[#A0A0A0]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
              </div>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mt-2 sm:mt-3">{{ stats.total }}</p>
          </div>
          <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-3.5 sm:p-5 border border-gray-200 dark:border-[#D4AF37]/20 shadow-xs">
            <div class="flex items-center justify-between">
              <p class="text-xs sm:text-sm font-medium text-gray-400 dark:text-[#A0A0A0]">Pending</p>
              <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-[#d29922]/10 flex items-center justify-center text-amber-500 dark:text-[#d29922]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-amber-500 dark:text-[#d29922] mt-2 sm:mt-3">{{ stats.pending }}</p>
          </div>
          <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-3.5 sm:p-5 border border-gray-200 dark:border-[#D4AF37]/20 shadow-xs">
            <div class="flex items-center justify-between">
              <p class="text-xs sm:text-sm font-medium text-gray-400 dark:text-[#A0A0A0]">Confirmed</p>
              <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-[#58a6ff]/10 flex items-center justify-center text-blue-500 dark:text-[#58a6ff]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-blue-500 dark:text-[#58a6ff] mt-2 sm:mt-3">{{ stats.confirmed }}</p>
          </div>
          <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl p-3.5 sm:p-5 border border-gray-200 dark:border-[#D4AF37]/20 shadow-xs">
            <div class="flex items-center justify-between">
              <p class="text-xs sm:text-sm font-medium text-gray-400 dark:text-[#A0A0A0]">Delivered</p>
              <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-[#3fb950]/10 flex items-center justify-center text-emerald-500 dark:text-[#3fb950]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-emerald-500 dark:text-[#3fb950] mt-2 sm:mt-3">{{ stats.delivered }}</p>
          </div>
        </div>

        <!-- FILTERS + SEARCH -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex flex-wrap gap-1.5">
            <button
              v-for="f in filters" :key="f.key"
              @click="activeFilter = f.key"
              :class="[
                'px-3 sm:px-3.5 py-1.5 rounded-xl text-xs font-semibold cursor-pointer transition',
                activeFilter === f.key
                  ? 'bg-[#1A1A1A] dark:bg-[#D4AF37] text-white dark:text-[#1A1A1A] shadow-xs'
                  : 'bg-white dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] hover:bg-gray-50 dark:hover:bg-[#21262d] hover:text-gray-700 dark:hover:text-[#c9d1d9] border border-gray-200 dark:border-[#D4AF37]/20'
              ]"
            >
              {{ f.label }}
              <span v-if="f.key === 'all' || stats[f.key]" class="ml-1 text-[10px] opacity-75">({{ f.key === 'all' ? stats.total : stats[f.key] }})</span>
            </button>
          </div>
          <div class="relative w-full sm:w-64">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search orders, code, name..."
              class="w-full pl-9 pr-4 py-2 rounded-xl text-xs sm:text-sm border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-[#8b949e] outline-none focus:border-gray-400 dark:focus:border-gray-500"
            />
          </div>
        </div>

        <!-- EMPTY -->
        <div v-if="orders.length === 0"
          class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-16 text-center flex flex-col items-center gap-4">
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-gray-50 dark:bg-[#2A2A2A]">
            <svg class="w-8 h-8 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
          </div>
          <div>
            <h2 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">No orders yet</h2>
            <p class="text-sm text-gray-500 dark:text-[#A0A0A0] mt-1">Customer orders will appear here</p>
          </div>
        </div>

        <div v-else-if="filteredOrders.length === 0"
          class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-12 text-center">
          <p class="text-sm text-gray-400 dark:text-[#A0A0A0]">No orders match your search.</p>
        </div>

        <!-- ORDERS LIST -->
        <div v-else class="space-y-3">

          <div
            v-for="order in filteredOrders" :key="order.id"
            class="bg-white dark:bg-[#1A1A1A] rounded-xl border border-gray-100 dark:border-[#D4AF37]/20 divide-y divide-gray-50 dark:divide-[#21262d]"
          >

            <!-- ROW 1: Customer + Status + Total -->
            <div class="p-3.5 sm:px-5 sm:py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gray-50 dark:bg-[#2A2A2A] flex items-center justify-center text-xs font-bold text-gray-500 dark:text-[#A0A0A0] shrink-0">
                  #{{ String(order.id).slice(-3) }}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ order.user?.name || "Guest" }}</p>
                    <span class="text-xs text-gray-400 dark:text-[#A0A0A0]" :title="formatTime(order.created_at)">&bull; {{ timeAgo(order.created_at) }}</span>
                  </div>
                  <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <p v-if="order.tracking_code" class="m-0">
                      <a
                        :href="`/track-order?code=${order.tracking_code}`"
                        target="_blank"
                        class="inline-flex items-center gap-1 font-mono text-[10px] font-semibold tracking-wide text-[#B8960F] dark:text-[#D4AF37] bg-[#D4AF37]/10 border border-[#D4AF37]/25 rounded px-1.5 py-0.5 hover:bg-[#D4AF37]/20 no-underline"
                        :title="`Open tracking for ${order.tracking_code}`"
                      >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        {{ order.tracking_code }}
                      </a>
                    </p>
                    <button
                      v-if="order.payment_slip_url"
                      type="button"
                      @click="viewSlip(order)"
                      class="inline-flex items-center gap-1 font-mono text-[10px] font-bold tracking-wide text-[#B8960F] dark:text-[#D4AF37] bg-[#D4AF37]/15 border border-[#D4AF37]/35 rounded px-2 py-0.5 hover:bg-[#D4AF37]/25 transition cursor-pointer"
                      title="View attached payment receipt"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.373L8.557 18.315a1.5 1.5 0 11-2.121-2.121L15.75 6.879"/></svg>
                      Slip Attached
                    </button>
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0 pl-12 sm:pl-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-gray-100 dark:border-white/5">
                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold leading-5" :class="statusBadge(order.status)">
                  {{ order.status }}
                </span>
                <span class="text-base sm:text-sm font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">${{ parseFloat(order.total).toFixed(2) }}</span>
              </div>
            </div>

            <!-- ROW 2: Items -->
            <div class="px-5 py-2.5">
              <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between py-1.5 text-sm">
                <div class="flex items-center gap-2.5 min-w-0">
                  <span class="text-gray-600 dark:text-[#A0A0A0] truncate">{{ item.product?.name ||"Product" }}</span>
                  <span class="text-gray-400 dark:text-[#A0A0A0] shrink-0">&times;{{ item.quantity }}</span>
                </div>
                <span class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] shrink-0 ml-3">${{ (item.price * item.quantity).toFixed(2) }}</span>
              </div>
            </div>

            <!-- ROW 3: Shipping (if any) -->
            <div v-if="order.shipping_name" class="px-5 py-2.5">
              <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-gray-400 dark:text-[#A0A0A0]">
                <span v-if="order.shipping_name" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  {{ order.shipping_name }}
                </span>
                <span v-if="order.shipping_email" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  {{ order.shipping_email }}
                </span>
                <span v-if="order.shipping_phone" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  {{ order.shipping_phone }}
                </span>
                <span v-if="order.shipping_address" class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  {{ order.shipping_address }}{{ order.shipping_city ? ", " + order.shipping_city : "" }}{{ order.shipping_postal_code ? " " + order.shipping_postal_code : "" }}
                </span>
              </div>
              <p v-if="order.notes" class="mt-1.5 text-xs text-gray-400 dark:text-[#A0A0A0]">
                <span class="font-medium">Note:</span> {{ order.notes }}
              </p>
            </div>

            <!-- ROW 4: Actions -->
            <div class="p-3.5 sm:px-5 sm:py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <button
                @click="toggleTimeline(order.id)"
                class="text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] hover:text-gray-700 dark:hover:text-[#c9d1d9] cursor-pointer flex items-center gap-1.5 self-start"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ expandedOrder === order.id ? "Hide" : "View" }} History
              </button>

              <div class="flex items-center gap-1.5 flex-wrap">
                <button
                  v-if="order.payment_slip_url"
                  type="button"
                  @click="viewSlip(order)"
                  class="px-2.5 py-1.5 rounded-lg text-xs font-semibold cursor-pointer border border-[#D4AF37]/35 bg-[#D4AF37]/10 text-[#B8960F] dark:text-[#D4AF37] hover:bg-[#D4AF37]/20 flex items-center gap-1"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.373L8.557 18.315a1.5 1.5 0 11-2.121-2.121L15.75 6.879"/></svg>
                  Review Slip
                </button>
                <template v-if="nextStatuses(order.status).length">
                  <button
                    v-for="ns in nextStatuses(order.status)" :key="ns"
                    @click="requestStatusChange(order.id, ns)"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer transition"
                    :class="{
                      'bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-[#d29922]/10 dark:text-[#d29922] dark:hover:bg-[#d29922]/20': ns === 'pending',
                      'bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-[#58a6ff]/10 dark:text-[#58a6ff] dark:hover:bg-[#58a6ff]/20': ns === 'confirmed',
                      'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-[#3fb950]/10 dark:text-[#3fb950] dark:hover:bg-[#3fb950]/20': ns === 'delivered',
                    }"
                  >
                    {{ ns === 'confirmed' ? 'Confirm' : ns.charAt(0).toUpperCase() + ns.slice(1) }}
                  </button>
                </template>
                <button
                  @click="confirmDelete(order.id)"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold cursor-pointer bg-red-50 text-red-500 hover:bg-red-100 dark:bg-[#f85149]/10 dark:text-[#f85149] dark:hover:bg-[#f85149]/20 transition"
                >
                  Delete
                </button>
              </div>
            </div>

            <!-- TIMELINE -->
            <div v-if="expandedOrder === order.id" class="border-t border-gray-50 dark:border-[#21262d]">
              <div v-if="order.statuses?.length" class="px-5 py-4">
                <div class="relative ml-2.5">
                  <div class="absolute left-0 top-1 bottom-1 w-px bg-gray-100 dark:bg-[#30363d]"></div>
                  <div v-for="s in order.statuses" :key="s.id" class="relative flex items-start gap-3 pb-4 last:pb-0">
                    <div :class="['w-2.5 h-2.5 rounded-full border-2 border-white dark:border-[#0d1117] shrink-0 mt-0.5 z-10', statusDot(s.status)]"></div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold capitalize text-[#1A1A1A] dark:text-[#F5F5F5]">{{ s.status }}</span>
                        <span class="text-[10px] text-gray-400 dark:text-[#A0A0A0]">{{ formatTime(s.created_at) }}</span>
                      </div>
                      <p v-if="s.note" class="text-[11px] text-gray-500 dark:text-[#A0A0A0] mt-0.5">{{ s.note }}</p>
                      <p v-if="s.user" class="text-[10px] text-gray-400 dark:text-[#A0A0A0] mt-0.5">by {{ s.user.name }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="px-5 py-6 text-center text-sm text-gray-400 dark:text-[#A0A0A0]">No tracking history yet</div>
            </div>

          </div>

        </div>

      </div>

    </main>

    <Modal :show="!!confirmStatus" @close="confirmStatus = null">
      <template #icon>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center"
          :class="{
            'bg-amber-500/10': confirmStatus?.status === 'pending',
            'bg-blue-500/10': confirmStatus?.status === 'confirmed',
            'bg-emerald-500/10': confirmStatus?.status === 'delivered',
          }">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            :class="{
              'text-amber-500': confirmStatus?.status === 'pending',
              'text-blue-500': confirmStatus?.status === 'confirmed',
              'text-emerald-500': confirmStatus?.status === 'delivered',
            }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </template>
      <template #title>
        <h2 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5] capitalize">Mark as {{ confirmStatus?.status }}?</h2>
      </template>
      <template #description>
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Are you sure you want to change this order's status to <strong class="capitalize text-[#1A1A1A] dark:text-[#F5F5F5]">{{ confirmStatus?.status }}</strong>?</p>
      </template>
      <template #actions>
        <button @click="confirmStatus = null"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-700 dark:text-[#F5F5F5] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]">
          Cancel
        </button>
        <button @click="changeStatus"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white cursor-pointer"
          :class="{
            'bg-amber-500 hover:bg-amber-600': confirmStatus?.status === 'pending',
            'bg-blue-500 hover:bg-blue-600': confirmStatus?.status === 'confirmed',
            'bg-emerald-500 hover:bg-emerald-600': confirmStatus?.status === 'delivered',
          }">
          Confirm
        </button>
      </template>
    </Modal>

    <Modal :show="!!deleteId" @close="deleteId = null">
      <template #icon>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-600/10">
          <svg class="w-6 h-6" fill="none" stroke="#DC2626" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/></svg>
        </div>
      </template>
      <template #title>
        <h2 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">Delete this order?</h2>
      </template>
      <template #description>
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">This action cannot be undone.</p>
      </template>
      <template #actions>
        <button @click="deleteId = null"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-700 dark:text-[#F5F5F5] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]">
          Cancel
        </button>
        <button @click="destroy"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white cursor-pointer bg-red-600 hover:bg-red-700">
          Delete
        </button>
      </template>
    </Modal>

    <!-- VIEW PAYMENT SLIP MODAL -->
    <Modal :show="!!viewingSlipOrder" @close="viewingSlipOrder = null" max-width="lg">
      <template #icon>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-[#D4AF37]/15 text-[#D4AF37]">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.373L8.557 18.315a1.5 1.5 0 11-2.121-2.121L15.75 6.879"/></svg>
        </div>
      </template>
      <template #title>
        <h2 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">
          Payment Slip &bull; Order #{{ viewingSlipOrder?.id }}
        </h2>
      </template>
      <template #description>
        <p class="text-xs text-gray-500 dark:text-[#A0A0A0]">
          Tracking: {{ viewingSlipOrder?.tracking_code }} &bull; Customer: {{ viewingSlipOrder?.user?.name || viewingSlipOrder?.shipping_name || 'Guest' }} &bull; Total: ${{ viewingSlipOrder?.total }}
        </p>
      </template>

      <div class="mt-4">
        <div
          v-if="viewingSlipOrder?.payment_slip_url?.match(/\.(jpg|jpeg|png|webp)$/i)"
          class="rounded-xl overflow-hidden border border-gray-200 dark:border-[#D4AF37]/20 bg-black/5 dark:bg-black/40 flex items-center justify-center max-h-[55vh]"
        >
          <img
            :src="viewingSlipOrder.payment_slip_url"
            alt="Payment Receipt"
            class="max-h-[55vh] w-auto object-contain cursor-pointer"
            @click="window.open(viewingSlipOrder.payment_slip_url, '_blank')"
          />
        </div>
        <div
          v-else
          class="p-8 text-center rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-gray-50 dark:bg-[#0A0A0A]"
        >
          <svg class="w-12 h-12 text-[#D4AF37] mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">PDF Slip Attached</p>
        </div>

        <div class="mt-3 flex items-center justify-between text-xs text-gray-400 dark:text-[#A0A0A0]">
          <span>Uploaded on {{ formatTime(viewingSlipOrder?.payment_slip_uploaded_at) }}</span>
          <a :href="viewingSlipOrder?.payment_slip_url" target="_blank" class="font-semibold text-[#D4AF37] hover:underline flex items-center gap-1">
            Open in new tab &rarr;
          </a>
        </div>
      </div>

      <template #actions>
        <button
          type="button"
          @click="confirmRemoveSlip(viewingSlipOrder.id); viewingSlipOrder = null"
          class="px-3.5 py-2.5 rounded-xl text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition cursor-pointer border border-red-200 dark:border-red-500/20 bg-transparent"
        >
          Remove Slip
        </button>
        <button
          v-if="viewingSlipOrder?.status === 'pending'"
          type="button"
          @click="requestStatusChange(viewingSlipOrder.id, 'confirmed'); viewingSlipOrder = null"
          class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-[#D4AF37] hover:brightness-105 shadow-sm shadow-[#D4AF37]/30 cursor-pointer border-none"
        >
          Approve & Mark Confirmed
        </button>
        <button
          type="button"
          @click="viewingSlipOrder = null"
          class="px-4 py-2.5 rounded-xl text-xs font-semibold border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-700 dark:text-[#F5F5F5] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] cursor-pointer"
        >
          Close
        </button>
      </template>
    </Modal>

    <!-- CONFIRM REMOVE SLIP MODAL -->
    <Modal :show="!!removingSlipId" @close="removingSlipId = null">
      <template #icon>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-600/10">
          <svg class="w-6 h-6" fill="none" stroke="#DC2626" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/></svg>
        </div>
      </template>
      <template #title>
        <h2 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">Remove Payment Slip?</h2>
      </template>
      <template #description>
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">
          Are you sure you want to remove this uploaded receipt? The customer will be able to upload a replacement.
        </p>
      </template>
      <template #actions>
        <button
          @click="removingSlipId = null"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-700 dark:text-[#F5F5F5] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]"
        >
          Cancel
        </button>
        <button
          @click="removeSlip"
          class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white cursor-pointer bg-red-600 hover:bg-red-700"
        >
          Remove
        </button>
      </template>
    </Modal>

  </div>
</template>
