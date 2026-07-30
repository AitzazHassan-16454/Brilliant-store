<script setup>
import { Head, Link } from "@inertiajs/vue3"
import Sidebar from "../components/Sidebar.vue"

defineOptions({ layout: false })

defineProps({
  customOrders: Array,
})

const statusColor = (status) => ({
  pending: "bg-yellow-100 text-yellow-700 dark:bg-[#d29922]/10 dark:text-[#d29922]",
  approved: "bg-blue-100 text-blue-700 dark:bg-[#58a6ff]/10 dark:text-[#58a6ff]",
  in_progress: "bg-purple-100 text-purple-700 dark:bg-[#bc8cff]/10 dark:text-[#bc8cff]",
  completed: "bg-green-100 text-green-700 dark:bg-[#3fb950]/10 dark:text-[#3fb950]",
  declined: "bg-red-100 text-red-700 dark:bg-[#f85149]/10 dark:text-[#f85149]",
}[status] || "bg-gray-100 text-gray-700 dark:bg-[#2A2A2A] dark:text-[#A0A0A0]")

const formatDate = (d) => new Date(d).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
</script>

<template>
  <Head title="Custom Orders" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">
    <Sidebar />

    <main class="flex-1 p-6 lg:p-10">
      <div class="mb-8 animate-fade-in-up-sm">
        <h1 class="text-3xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Custom Orders</h1>
        <p class="text-gray-500 dark:text-[#A0A0A0]">Customer custom order requests</p>
      </div>

      <div v-if="customOrders.length === 0"
        class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-16 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10 mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
          </svg>
        </div>
        <p class="text-gray-500 dark:text-[#A0A0A0] text-sm">No custom order requests yet.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in customOrders" :key="order.id"
          class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ order.name }}</h3>
                <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">({{ order.email }})</span>
              </div>
              <p class="text-xs text-gray-500 dark:text-[#A0A0A0] mt-0.5">{{ order.phone }} &middot; {{ formatDate(order.created_at) }}</p>
            </div>
            <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold', statusColor(order.status)]">
              {{ order.status.replace("_", " ") }}
            </span>
          </div>
          <p class="text-sm text-[#1A1A1A] dark:text-[#F5F5F5] mt-3 whitespace-pre-wrap">{{ order.description }}</p>
          <div v-if="order.reference_image" class="mt-3">
            <img :src="'/storage/' + order.reference_image" class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-[#D4AF37]/20" />
          </div>
          <div v-if="order.budget_min || order.budget_max" class="mt-3 flex items-center gap-4 text-xs text-gray-500 dark:text-[#A0A0A0]">
            <span v-if="order.budget_min">Budget: ${{ order.budget_min }} &ndash; ${{ order.budget_max || "—" }}</span>
            <span v-if="order.desired_date">By {{ formatDate(order.desired_date) }}</span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
