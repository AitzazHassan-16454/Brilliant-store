<script setup>
import { router, Head } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";

defineOptions({ layout: false });

const props = defineProps({
  review: Object,
});

const statusColor = (status) => {
  return {
    pending: "bg-yellow-100 text-yellow-700 dark:bg-[#d29922]/10 dark:text-[#d29922]",
    approved: "bg-green-100 text-green-700 dark:bg-[#3fb950]/10 dark:text-[#3fb950]",
    rejected: "bg-red-100 text-red-700 dark:bg-[#f85149]/10 dark:text-[#f85149]",
  }[status] || "bg-gray-100 text-gray-700 dark:bg-[#1A1A1A] dark:text-[#F5F5F5]";
};

const changeStatus = (action) => {
  router.post(`/reviews/${props.review.id}/${action}`, {}, {
    preserveScroll: true,
  });
};

const deleteReview = () => {
  if (confirm("Are you sure you want to delete this review?")) {
    router.delete(`/reviews/${props.review.id}/admin`, {
      preserveScroll: true,
    });
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>

<template>
  <Head :title="`Review #${review.id}`" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <Sidebar />

    <main class="flex-1 p-6 lg:p-10 overflow-x-hidden">

      <!-- HEADER -->
      <div class="mb-6 flex items-center gap-3">
        <a href="/reviews"
          class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-[#D4AF37]/20 hover:bg-gray-50 dark:hover:bg-[#30363d]/50 cursor-pointer">
          <svg class="w-4 h-4 text-gray-500 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </a>
        <div>
          <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Review #{{ review.id }}</h1>
          <p class="text-gray-500 dark:text-[#A0A0A0] text-sm">Full review details and status management</p>
        </div>
        <div class="ml-auto">
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Review Details -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Review Card -->
          <div class="bg-white dark:bg-[#1A1A1A] rounded-lg border border-gray-100 dark:border-[#D4AF37]/20 shadow-sm p-6">

            <!-- User + Status -->
            <div class="flex items-center justify-between mb-5">
              <div class="flex items-center gap-3">
                <img
                  v-if="review.user?.avatar"
                  :src="`/storage/${review.user.avatar}`"
                  class="w-10 h-10 rounded-full object-cover shrink-0"
                />
                <div v-else class="w-10 h-10 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-full flex items-center justify-center text-sm font-semibold text-gray-600 dark:text-[#F5F5F5] shrink-0">
                  {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ review.user?.name || 'Unknown' }}</p>
                  <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">{{ review.user?.email || '' }}</p>
                </div>
              </div>
              <span
                :class="['px-2.5 py-1 rounded-full text-xs font-semibold capitalize', statusColor(review.status)]"
              >
                {{ review.status }}
              </span>
            </div>

            <!-- Product -->
            <div class="mb-5 p-3 rounded-lg bg-[#FAF7F2] dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20">
              <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0] uppercase font-semibold tracking-wider mb-1">Product</p>
              <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ review.product?.name || 'Deleted product' }}</p>
            </div>

            <!-- Rating -->
            <div class="mb-5">
              <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0] uppercase font-semibold tracking-wider mb-2">Rating</p>
              <div class="flex items-center gap-1">
                <svg
                  v-for="i in 5"
                  :key="i"
                  class="w-5 h-5"
                  :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200 dark:text-[#A0A0A0]'"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] ml-1">{{ review.rating }}/5</span>
              </div>
            </div>

            <!-- Comment -->
            <div>
              <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0] uppercase font-semibold tracking-wider mb-2">Comment</p>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 text-sm text-gray-700 dark:text-[#F5F5F5] leading-relaxed whitespace-pre-wrap">
                {{ review.comment || 'No comment provided.' }}
              </div>
            </div>

          </div>

        </div>

        <!-- RIGHT: Sidebar Info + Actions -->
        <div class="space-y-5">

          <!-- Details -->
          <div class="bg-white dark:bg-[#1A1A1A] rounded-lg border border-gray-100 dark:border-[#D4AF37]/20 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 dark:text-[#A0A0A0] uppercase tracking-wider mb-4">Details</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-[#A0A0A0]">Review ID</span>
                <span class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">#{{ review.id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-[#A0A0A0]">Status</span>
                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize', statusColor(review.status)]">
                  {{ review.status }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-[#A0A0A0]">Rating</span>
                <span class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">{{ review.rating }}/5</span>
              </div>
              <div class="h-px bg-gray-100 dark:bg-[#1A1A1A]"></div>
              <div>
                <span class="text-gray-500 dark:text-[#A0A0A0] text-xs">Submitted</span>
                <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5] text-xs mt-0.5">{{ formatDate(review.created_at) }}</p>
              </div>
              <div>
                <span class="text-gray-500 dark:text-[#A0A0A0] text-xs">Last Updated</span>
                <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5] text-xs mt-0.5">{{ formatDate(review.updated_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white dark:bg-[#1A1A1A] rounded-lg border border-gray-100 dark:border-[#D4AF37]/20 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 dark:text-[#A0A0A0] uppercase tracking-wider mb-4">Actions</h3>
            <div class="space-y-2">

              <button
                v-if="review.status !== 'approved'"
                @click="changeStatus('approve')"
                class="w-full px-4 py-2.5 rounded-lg bg-green-500 hover:bg-green-600 text-white text-xs font-semibold cursor-pointer flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Approve Review
              </button>

              <button
                v-if="review.status !== 'rejected'"
                @click="changeStatus('reject')"
                class="w-full px-4 py-2.5 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold cursor-pointer flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject Review
              </button>

              <div class="h-px bg-gray-100 dark:bg-[#1A1A1A] my-1"></div>

              <button
                @click="deleteReview"
                class="w-full px-4 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-semibold cursor-pointer flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/></svg>
                Delete Review
              </button>

            </div>
          </div>

          <!-- Back Link -->
          <a href="/reviews"
            class="block text-center text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] py-2">
            &larr; Back to Reviews
          </a>

        </div>

      </div>

    </main>
  </div>
</template>
