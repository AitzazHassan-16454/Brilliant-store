<script setup>
import { router, Head, Link } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";

defineOptions({ layout: false });

defineProps({
  reviews: Object,
});

const changeStatus = (id, action) => {
  router.post(`/reviews/${id}/${action}`, {}, {
    preserveScroll: true,
  });
};

const deleteReview = (id) => {
  if (confirm("Are you sure you want to delete this review?")) {
    router.delete(`/reviews/${id}/admin`, {
      preserveScroll: true,
    });
  }
};

const statusColor = (status) => {
  return {
    pending: "bg-yellow-100 dark:bg-[#d29922]/10 text-yellow-700 dark:text-[#d29922]",
    approved: "bg-green-100 dark:bg-[#3fb950]/10 text-green-700 dark:text-[#3fb950]",
    rejected: "bg-red-100 dark:bg-[#f85149]/10 text-red-700 dark:text-[#f85149]",
  }[status] || "bg-gray-100 dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5]";
};
</script>

<template>
  <Head title="Reviews" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">

    <Sidebar />

    <main class="flex-1 p-6 lg:p-10 overflow-x-hidden">

      <!-- HEADER -->
      <div class="flex items-center justify-between mb-6 animate-fade-in-up-sm">
        <div>
          <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Reviews Management</h1>
          <p class="text-gray-500 dark:text-[#A0A0A0] text-sm">Approve or reject customer product reviews</p>
        </div>
      </div>

      <!-- EMPTY -->
      <div v-if="reviews.data.length === 0"
        class="bg-white dark:bg-[#1A1A1A] p-10 rounded-2xl text-center text-gray-500 dark:text-[#A0A0A0] shadow-sm border border-gray-200 dark:border-[#D4AF37]/20">
        No reviews found
      </div>

      <!-- REVIEWS TABLE -->
      <div v-else class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-[#D4AF37]/20 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">

            <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-[10px]">
              <tr>
                <th class="px-4 py-3 font-semibold">User</th>
                <th class="px-4 py-3 font-semibold">Product</th>
                <th class="px-4 py-3 font-semibold">Rating</th>
                <th class="px-4 py-3 font-semibold">Comment</th>
                <th class="px-4 py-3 font-semibold">Status</th>
                <th class="px-4 py-3 font-semibold">Date</th>
                <th class="px-4 py-3 font-semibold text-right">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-50 dark:divide-[#21262d]">

              <tr
                v-for="review in reviews.data"
                :key="review.id"
                class="hover:bg-[#FAF7F2] dark:hover:bg-[#30363d]/50 transition-colors"
              >
                <td class="px-4 py-3">
                  <Link :href="`/reviews/${review.id}`" class="flex items-center gap-2 no-underline hover:opacity-70 transition">
                    <img
                      v-if="review.user?.avatar"
                      :src="`/storage/${review.user.avatar}`"
                      class="w-7 h-7 rounded-full object-cover shrink-0"
                    />
                    <div v-else class="w-7 h-7 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-full flex items-center justify-center text-[10px] font-semibold text-gray-600 dark:text-[#A0A0A0] shrink-0">
                      {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <span class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">{{ review.user?.name || 'Unknown' }}</span>
                  </Link>
                </td>
                <td class="px-4 py-3 font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">
                  {{ review.product?.name || 'Deleted' }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-0.5">
                    <svg
                      v-for="i in 5"
                      :key="i"
                      class="w-3 h-3"
                      :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200 dark:text-[#A0A0A0]'"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </div>
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-[#A0A0A0] max-w-[200px] truncate">
                  {{ review.comment || '—' }}
                </td>
                <td class="px-4 py-3">
                  <span
                    :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize', statusColor(review.status)]"
                  >
                    {{ review.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-400 dark:text-[#A0A0A0] whitespace-nowrap">
                  {{ new Date(review.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-1.5">

                    <button
                      v-if="review.status !== 'approved'"
                      @click="changeStatus(review.id, 'approve')"
                      class="px-2 py-1 rounded-lg bg-green-500 hover:bg-green-600 text-white text-[10px] font-semibold transition-all active:scale-95 cursor-pointer"
                    >
                      Approve
                    </button>

                    <button
                      v-if="review.status !== 'rejected'"
                      @click="changeStatus(review.id, 'reject')"
                      class="px-2 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-[10px] font-semibold transition-all active:scale-95 cursor-pointer"
                    >
                      Reject
                    </button>

                    <button
                      @click="deleteReview(review.id)"
                      class="px-2 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[10px] font-semibold transition-all active:scale-95 cursor-pointer"
                    >
                      Delete
                    </button>

                  </div>
                </td>
              </tr>

            </tbody>

          </table>
        </div>

        <!-- PAGINATION -->
        <div v-if="reviews.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between text-xs text-gray-500 dark:text-[#A0A0A0]">
          <span>
            Showing {{ reviews.from }} to {{ reviews.to }} of {{ reviews.total }} reviews
          </span>
          <div class="flex gap-1">
            <button
              v-for="page in reviews.last_page"
              :key="page"
              @click="router.get(`/reviews?page=${page}`, {}, { preserveScroll: true })"
              :class="[
                'px-2.5 py-1 rounded-lg text-[10px] font-semibold transition-all cursor-pointer',
                page === reviews.current_page
                  ? 'bg-[#D4AF37] text-white'
                  : 'bg-gray-100 dark:bg-[#1A1A1A] text-gray-600 dark:text-[#A0A0A0] hover:bg-gray-200 dark:hover:bg-[#30363d]'
              ]"
            >
              {{ page }}
            </button>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>
