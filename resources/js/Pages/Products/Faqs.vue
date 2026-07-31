<script setup>
import { Head } from "@inertiajs/vue3"
import Sidebar from "../components/Sidebar.vue"

defineOptions({ layout: false })

defineProps({
  faqs: Array,
})
</script>

<template>
  <Head title="FAQs" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">
    <Sidebar />

    <main class="flex-1 p-6 lg:p-10">
      <div class="mb-8 animate-fade-in-up-sm">
        <h1 class="text-3xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">FAQs</h1>
        <p class="text-gray-500 dark:text-[#A0A0A0]">Frequently asked questions shown to customers</p>
      </div>

      <div v-if="faqs.length === 0"
        class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-16 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10 mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
          </svg>
        </div>
        <p class="text-gray-500 dark:text-[#A0A0A0] text-sm">No FAQs yet.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="faq in faqs" :key="faq.id"
          class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ faq.question }}</h3>
                <span :class="[
                  'px-2.5 py-1 rounded-full text-xs font-semibold',
                  faq.is_active
                    ? 'bg-green-100 text-green-700 dark:bg-[#3fb950]/10 dark:text-[#3fb950]'
                    : 'bg-gray-100 text-gray-700 dark:bg-[#2A2A2A] dark:text-[#A0A0A0]'
                ]">
                  {{ faq.is_active ? "Active" : "Hidden" }}
                </span>
              </div>
              <p class="text-sm text-[#1A1A1A] dark:text-[#F5F5F5] mt-3 whitespace-pre-wrap">{{ faq.answer }}</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
