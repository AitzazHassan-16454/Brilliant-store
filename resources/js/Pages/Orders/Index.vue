<script setup>
import { Link, Head, router, usePage } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { debounce } from "lodash";
import Payment from "../components/Payment.vue";
import Modal from "../../Components/Modal.vue";

const props = defineProps({
  orders: Array,
});

const page = usePage();
const expandedOrder = ref(null);
const expandedItems = ref(new Set());
const deleteId = ref(null);
const activeFilter = ref("all");
const searchQuery = ref("");
const debouncedSearch = ref("");
const sortBy = ref("newest");

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

const sortOptions = [
  { key: "newest", label: "Newest First" },
  { key: "oldest", label: "Oldest First" },
  { key: "highest", label: "Highest Total" },
  { key: "lowest", label: "Lowest Total" },
];

const stats = computed(() => {
  const totalSpent = props.orders.reduce((sum, o) => sum + parseFloat(o.total), 0);
  const pendingCount = props.orders.filter((o) => o.status === "pending").length;
  return {
    totalOrders: props.orders.length,
    totalSpent: totalSpent.toFixed(2),
    pendingCount,
  };
});

const filteredOrders = computed(() => {
  let result = [...props.orders];
  if (activeFilter.value !== "all") {
    result = result.filter((o) => o.status === activeFilter.value);
  }
  if (debouncedSearch.value.trim()) {
    const q = debouncedSearch.value.toLowerCase().trim();
    result = result.filter((o) => {
      if (String(o.id).includes(q)) return true;
      if (o.items?.some((item) => item.product?.name?.toLowerCase().includes(q))) return true;
      return false;
    });
  }
  switch (sortBy.value) {
    case "oldest": result.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)); break;
    case "highest": result.sort((a, b) => parseFloat(b.total) - parseFloat(a.total)); break;
    case "lowest": result.sort((a, b) => parseFloat(a.total) - parseFloat(b.total)); break;
    default: result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
  return result;
});

const toggleTimeline = (orderId) => {
  expandedOrder.value = expandedOrder.value === orderId ? null : orderId;
};

const toggleItems = (orderId) => {
  const s = new Set(expandedItems.value);
  s.has(orderId) ? s.delete(orderId) : s.add(orderId);
  expandedItems.value = s;
};

const whatsappNumber = "03414425591";

const payViaWhatsApp = (order) => {
  let message = `*Order #${order.id}*\n\n`;
  order.items.forEach((item) => {
    message += `\u2022 ${item.product.name} x ${item.quantity} = $${item.price * item.quantity}\n`;
  });
  message += `\n*Total:* $${order.total}\n`;
  message += `\nPlease confirm my order. Thank you!`;
  window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, "_blank");
};

const reorder = (order) => {
  router.post(`/orders/${order.id}/reorder`, {}, { preserveScroll: true });
};

const confirmDelete = (id) => {
  deleteId.value = id;
};

const destroyOrder = () => {
  router.delete(`/orders/${deleteId.value}`, {
    preserveScroll: true,
    onFinish: () => (deleteId.value = null),
  });
};

const printInvoice = (order) => {
  const w = window.open("", "_blank");
  const itemsHtml = order.items.map((item) => `
    <tr>
      <td style="padding:8px 0;border-bottom:1px solid #eee;">${item.product?.name ?? "Product"}</td>
      <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:center;">${item.quantity}</td>
      <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;">$${parseFloat(item.price).toFixed(2)}</td>
      <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;font-weight:600;">$${(item.price * item.quantity).toFixed(2)}</td>
    </tr>
  `).join("");
  w.document.write(`
    <!DOCTYPE html>
    <html><head><title>Invoice #${order.id}</title>
    <style>
      body{font-family:'Segoe UI',system-ui,sans-serif;color:#1a1a1a;max-width:700px;margin:0 auto;padding:40px 20px;}
      h1{font-size:22px;margin:0 0 4px;}
      .meta{color:#666;font-size:13px;margin-bottom:24px;}
      table{width:100%;border-collapse:collapse;}
      th{text-align:left;padding:8px 0;border-bottom:2px solid #1a1a1a;font-size:12px;text-transform:uppercase;color:#666;}
      th:nth-child(2){text-align:center;}
      th:nth-child(3),th:nth-child(4){text-align:right;}
      .total-row td{border-top:2px solid #1a1a1a;font-size:16px;font-weight:700;padding-top:12px;}
      .shipping{margin-top:24px;padding:16px;background:#f9fafb;border-radius:8px;font-size:13px;}
      .shipping h3{margin:0 0 8px;font-size:12px;text-transform:uppercase;color:#666;}
      .footer{margin-top:32px;text-align:center;color:#999;font-size:12px;}
    </style></head><body>
      <h1>Invoice #${order.id}</h1>
      <div class="meta">Date: ${new Date(order.created_at).toLocaleString()} &nbsp;|&nbsp; Status: ${order.status}</div>
      <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          ${itemsHtml}
          <tr class="total-row"><td colspan="3">Total</td><td style="text-align:right;">$${parseFloat(order.total).toFixed(2)}</td></tr>
        </tbody>
      </table>
      ${order.shipping_name ? `
      <div class="shipping">
        <h3>Shipping Details</h3>
        <p style="margin:2px 0;"><strong>${order.shipping_name}</strong></p>
        ${order.shipping_email ? `<p style="margin:2px 0;color:#666;">${order.shipping_email}</p>` : ""}
        ${order.shipping_phone ? `<p style="margin:2px 0;color:#666;">${order.shipping_phone}</p>` : ""}
        ${order.shipping_address ? `<p style="margin:2px 0;color:#666;">${order.shipping_address}${order.shipping_city ? ", " + order.shipping_city : ""}${order.shipping_postal_code ? " " + order.shipping_postal_code : ""}</p>` : ""}
      </div>` : ""}
      <div class="footer">Thank you for your purchase!</div>
    </body></html>
  `);
  w.document.close();
  setTimeout(() => w.print(), 300);
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

const timeAgo = (date) => {
  const seconds = Math.floor((new Date() - new Date(date)) / 1000);
  if (seconds < 60) return "just now";
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days}d ago`;
  if (days < 30) return `${Math.floor(days / 7)}w ago`;
  return formatTime(date);
};
</script>

<template>
<Head title="My Orders" />

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">My Orders</h1>
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">Track your purchases and order history</p>
      </div>
      <Link
        href="/"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 dark:text-[#A0A0A0] hover:text-gray-700 dark:hover:text-[#c9d1d9] border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] hover:bg-gray-50 dark:hover:bg-[#21262d] px-4 py-2 rounded-lg transition active:scale-95 no-underline"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Shopping
      </Link>
    </div>

    <!-- FLASH -->
    <Transition name="modal">
      <div v-if="$page.props.flash?.success"
        class="mb-5 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2"
        style="background:rgba(13,148,136,0.08); border:1px solid rgba(13,148,136,0.2); color:#0D9488;">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ $page.props.flash.success }}
      </div>
    </Transition>

    <!-- STATS -->
    <div v-if="orders.length > 0" class="grid grid-cols-3 gap-4 mb-8">
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Total Orders</p>
        <p class="text-2xl font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-1">{{ stats.totalOrders }}</p>
      </div>
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Total Spent</p>
        <p class="text-2xl font-medium mt-1 text-emerald-600 dark:text-[#3fb950]">${{ stats.totalSpent }}</p>
      </div>
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-4">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Pending</p>
        <p class="text-2xl font-medium mt-1 text-amber-600 dark:text-[#d29922]">{{ stats.pendingCount }}</p>
      </div>
    </div>

    <Payment />

    <!-- EMPTY -->
    <div
      v-if="orders.length === 0"
      class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-16 text-center flex flex-col items-center gap-4"
    >
      <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10">
        <svg class="w-10 h-10" fill="none" stroke="#0D9488" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-[#F5F5F5]">No Orders Yet</h2>
        <p class="text-sm mt-1 text-gray-500 dark:text-[#A0A0A0] max-w-xs mx-auto">Once you place an order, it will appear here with full details.</p>
      </div>
      <Link
        href="/"
        class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline mt-2 text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30"
      >
        Start Shopping
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </Link>
    </div>

    <!-- FILTERS, SEARCH & SORT -->
    <div v-if="orders.length > 0" class="mb-6 space-y-4">

      <div class="flex flex-wrap gap-2">
        <button
          v-for="f in filters" :key="f.key"
          @click="activeFilter = f.key"
          :class="[
            'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer border',
            activeFilter === f.key
              ? 'bg-[#1A1A1A] dark:bg-[#D4AF37] text-white border-[#1A1A1A] dark:border-[#D4AF37]'
              : 'bg-white dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] border-gray-200 dark:border-[#D4AF37]/20 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-[#c9d1d9]'
          ]"
        >
          {{ f.label }}
        </button>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by order ID or product name..."
            class="w-full pl-9 pr-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-[#8b949e] outline-none transition focus:border-gray-400 dark:focus:border-gray-500"
          />
        </div>
        <select
          v-model="sortBy"
          class="px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] outline-none transition focus:border-gray-400 dark:focus:border-gray-500 cursor-pointer"
        >
          <option v-for="s in sortOptions" :key="s.key" :value="s.key">{{ s.label }}</option>
        </select>
      </div>
    </div>

    <!-- NO RESULTS -->
    <div
      v-if="orders.length > 0 && filteredOrders.length === 0"
      class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-10 text-center"
    >
      <p class="text-sm text-gray-400 dark:text-[#A0A0A0]">No orders match your search.</p>
    </div>

    <!-- ORDERS -->
    <div v-else class="space-y-4">

      <div
        v-for="order in filteredOrders" :key="order.id"
        class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-xl overflow-hidden transition hover:shadow-sm"
      >

        <!-- ORDER HEADER -->
        <div class="px-5 py-3.5 border-b border-gray-50 dark:border-[#D4AF37]/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-[#1A1A1A] dark:bg-[#2A2A2A] flex items-center justify-center text-xs font-bold text-white shrink-0">
              #{{ String(order.id).slice(-3) }}
            </div>
            <div>
              <p class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Order Placed</p>
              <p class="text-xs text-gray-400 dark:text-[#A0A0A0]" :title="formatTime(order.created_at)">{{ timeAgo(order.created_at) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold uppercase tracking-wide leading-none" :class="statusColor(order.status)">
              {{ order.status }}
            </span>
            <span class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">${{ order.total }}</span>
          </div>
        </div>

        <!-- ORDER ITEMS -->
        <div class="px-5 py-3 space-y-2">
          <div
            v-for="item in (expandedItems.has(order.id) ? order.items : order.items.slice(0, 1))" :key="item.id"
            class="flex items-center justify-between"
          >
            <div class="flex items-center gap-3 min-w-0">
              <img
                :src="`/storage/${item.product.image}`"
                class="w-10 h-10 rounded-lg object-cover border border-gray-100 dark:border-[#D4AF37]/20 shrink-0"
              />
              <div class="min-w-0">
                <p class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ item.product.name }}</p>
                <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Qty: {{ item.quantity }} &times; ${{ item.price }}</p>
              </div>
            </div>
            <span class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] shrink-0 ml-3">${{ (item.price * item.quantity).toFixed(2) }}</span>
          </div>

          <button
            v-if="order.items.length > 1"
            @click="toggleItems(order.id)"
            class="text-xs font-medium text-gray-400 dark:text-[#A0A0A0] hover:text-gray-600 dark:hover:text-[#c9d1d9] transition cursor-pointer flex items-center gap-1 pt-1"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path v-if="!expandedItems.has(order.id)" stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
            </svg>
            {{ expandedItems.has(order.id) ? 'Show less' : `+${order.items.length - 1} more item${order.items.length - 1 > 1 ? 's' : ''}` }}
          </button>
        </div>

        <!-- SHIPPING DETAILS -->
        <div v-if="order.shipping_name" class="px-5 pb-2">
          <div class="flex flex-wrap gap-4 text-xs text-gray-400 dark:text-[#A0A0A0] bg-gray-50 dark:bg-[#2A2A2A]/50 rounded-lg px-4 py-3">
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
          <div v-if="order.notes" class="mt-2 text-xs text-gray-400 dark:text-[#A0A0A0] bg-gray-50 dark:bg-[#2A2A2A]/50 rounded-lg px-4 py-2.5">
            <span class="font-medium">Notes:</span> {{ order.notes }}
          </div>
        </div>

        <!-- WHATSAPP PAYMENT BOX -->
        <div
          v-if="order.status === 'pending'"
          class="mx-5 mb-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-800/30 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Complete your payment</p>
              <p class="text-xs text-gray-500 dark:text-[#A0A0A0]">Total: ${{ order.total }}</p>
            </div>
          </div>
          <button
            @click="payViaWhatsApp(order)"
            class="bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition shrink-0 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Pay via WhatsApp
          </button>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="px-5 py-3 border-t border-gray-50 dark:border-[#D4AF37]/20 flex flex-wrap items-center justify-between gap-2">
          <button
            @click="toggleTimeline(order.id)"
            class="text-xs font-medium text-gray-400 dark:text-[#A0A0A0] hover:text-gray-600 dark:hover:text-[#c9d1d9] transition cursor-pointer flex items-center gap-1.5"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ expandedOrder === order.id ? "Hide" : "View" }} Tracking
          </button>
          <div class="flex items-center gap-1.5">
            <button @click="printInvoice(order)" class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-[#2A2A2A] dark:text-[#A0A0A0] dark:hover:bg-[#30363d] flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              Invoice
            </button>
            <button @click="reorder(order)" class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-[#2A2A2A] dark:text-[#A0A0A0] dark:hover:bg-[#30363d] flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              Reorder
            </button>
            <button @click="confirmDelete(order.id)" class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition cursor-pointer bg-red-100 text-red-600 hover:bg-red-200 dark:bg-[#f85149]/10 dark:text-[#f85149] dark:hover:bg-[#f85149]/20 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 0V4a1 1 0 011-1h2a1 1 0 011 1v2"/></svg>
              Delete
            </button>
          </div>
        </div>

        <!-- TIMELINE -->
        <div v-if="expandedOrder === order.id" class="border-t border-gray-50 dark:border-[#D4AF37]/20">
          <div v-if="order.statuses?.length" class="px-5 py-4">
            <div class="relative ml-2.5">
              <div class="absolute left-0 top-1 bottom-1 w-px bg-gray-200 dark:bg-[#30363d]"></div>
              <div
                v-for="s in order.statuses" :key="s.id"
                class="relative flex items-start gap-3 pb-4 last:pb-0"
              >
                <div :class="['w-2.5 h-2.5 rounded-full border-2 border-white dark:border-[#0d1117] shrink-0 mt-1 z-10', statusDot(s.status)]"></div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-semibold capitalize text-[#1A1A1A] dark:text-[#F5F5F5]">{{ s.status }}</span>
                    <span class="text-[10px] text-gray-400 dark:text-[#A0A0A0]">{{ formatTime(s.created_at) }}</span>
                  </div>
                  <p v-if="s.note" class="text-[11px] text-gray-500 dark:text-[#A0A0A0] mt-0.5">{{ s.note }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-6 text-center text-sm text-gray-400 dark:text-[#A0A0A0]">
            No tracking history yet
          </div>
        </div>

      </div>

    </div>

  </div>

</div>

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
      class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-700 dark:text-[#F5F5F5] hover:bg-gray-50 dark:hover:bg-[#21262d]">
      Cancel
    </button>
    <button @click="destroyOrder"
      class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white cursor-pointer bg-red-600 hover:bg-red-700">
      Delete
    </button>
  </template>
</Modal>
</template>
