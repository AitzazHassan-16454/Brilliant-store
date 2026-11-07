<script setup>
import { router, Head, Link } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";
import Pagination from "../components/Pagination.vue";

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

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <Sidebar />

    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Reviews Management</h1>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">Approve, moderate, or reject customer product reviews</p>
        </div>
      </div>

      <!-- EMPTY -->
      <div v-if="reviews.data.length === 0"
        class="bg-white dark:bg-[#1A1A1A] p-12 rounded-2xl text-center text-gray-500 dark:text-[#A0A0A0] shadow-xs border border-gray-200 dark:border-[#D4AF37]/20">
        No reviews found
      </div>

      <!-- REVIEWS TABLE -->
      <div v-else class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 shadow-xs overflow-hidden">

        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
          <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">All Customer Reviews</h3>
          <span class="text-xs text-gray-400 dark:text-[#A0A0A0] lg:hidden">
            Scroll horizontally &rarr;
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left min-w-[720px]">

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
                class="hover:bg-[#FAF7F2] dark:hover:bg-[#30363d]/50"
              >
                <td class="px-4 py-3">
                  <Link :href="`/reviews/${review.id}`" class="flex items-center gap-2 no-underline hover:opacity-70">
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
                      class="px-2 py-1 rounded-lg bg-green-500 hover:bg-green-600 text-white text-[10px] font-semibold cursor-pointer transition"
                    >
                      Approve
                    </button>

                    <button
                      v-if="review.status !== 'rejected'"
                      @click="changeStatus(review.id, 'reject')"
                      class="px-2 py-1 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-[10px] font-semibold cursor-pointer transition"
                    >
                      Reject
                    </button>

                    <button
                      @click="deleteReview(review.id)"
                      class="px-2 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[10px] font-semibold cursor-pointer transition"
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
        <div v-if="reviews.data?.length" class="px-4 py-3 border-t border-gray-100 dark:border-[#D4AF37]/20">
          <Pagination :links="reviews.links" :meta="reviews" :per-page-options="[10, 15, 25, 50, 100]" item-label="reviews" />
        </div>

      </div>

    </main>
  </div>
</template>
