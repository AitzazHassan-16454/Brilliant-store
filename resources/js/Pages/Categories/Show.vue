<script setup>
import { ref, watch } from "vue"
import Pagination from "../components/Pagination.vue"
import { router, Link, Head, usePage } from "@inertiajs/vue3"
import { useAuthModal } from "../../composables/useAuthModal.js"
import { useNotification } from "../../composables/useNotification.js"
import { debounce } from "lodash"
import { motion } from "motion-v"
import ProductCard from "../../Components/ProductCard.vue"

const page = usePage()
const { openLogin } = useAuthModal()
const { success } = useNotification()

const props = defineProps({
  category: Object,
  products: Object,
  filters: Object,
  subcategories: Array,
  cartProductIds: Array,
  wishlistProductIds: Array,
})

const isInCart = (product) => props.cartProductIds.includes(product.id)
const isInWishlist = (product) => props.wishlistProductIds.includes(product.id)

const addToCart = (product) => {
  if (!page.props.auth.user) { openLogin(); return }
  router.post("/cart", { product_id: product.id }, {
    preserveScroll: true,
    onSuccess: () => success("Added to cart"),
  })
}

const toggleWishlist = (product) => {
  if (!page.props.auth.user) { openLogin(); return }
  router.post("/wishlist", { product_id: product.id }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const search = ref(props.filters?.search || "")
const minPrice = ref(props.filters?.min_price || "")
const maxPrice = ref(props.filters?.max_price || "")
const sortBy = ref(props.filters?.sortBy || "")
const selectedSubcategory = ref(props.filters?.subcategory_id || "")

const applyFilters = () => {
  router.get(`/categories/${props.category.uid}`, {
    search: search.value,
    min_price: minPrice.value,
    max_price: maxPrice.value,
    sortBy: sortBy.value,
    subcategory_id: selectedSubcategory.value,
  }, { preserveState: true, replace: true })
}

const applyPriceFilter = () => {
  applyFilters()
}

const filterBySubcategory = (subcategoryId) => {
  selectedSubcategory.value = selectedSubcategory.value == subcategoryId ? "" : subcategoryId
  applyFilters()
}

watch(search, debounce(applyFilters, 400))
watch(sortBy, applyFilters)

const categoryIcons = {
  fashion:     "M20.38 3.46L16 2 12 5.69 8 2l-4.38 1.46a2 2 0 00-1.34 1.89v13.3a2 2 0 002.66 1.89L8 17l4 3.69L16 17l4.38 1.46a2 2 0 002.66-1.89V5.35a2 2 0 00-1.34-1.89z",
  electronic:  "M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  electronics: "M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  clothing:    "M20.38 3.46L16 2 12 5.69 8 2l-4.38 1.46a2 2 0 00-1.34 1.89v13.3a2 2 0 002.66 1.89L8 17l4 3.69L16 17l4.38 1.46a2 2 0 002.66-1.89V5.35a2 2 0 00-1.34-1.89z",
  home:        "M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10",
  sports:      "M12 2a10 10 0 100 20 10 10 0 000-20z M12 2a14.5 14.5 0 000 20 14.5 14.5 0 000-20 M2 12h20",
  beauty:      "M12 2L2 7l10 5 10-5-10-5z M2 17l10 5 10-5 M2 12l10 5 10-5",
  books:       "M4 19.5A2.5 2.5 0 016.5 17H20 M4 19.5V4a2 2 0 012-2h14v14H6.5A2.5 2.5 0 004 18.5z",
  toys:        "M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.77 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z",
  food:        "M18 8h1a4 4 0 010 8h-1 M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z M6 1v3 M10 1v3 M14 1v3",
}
const defaultIcon = "M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z M3 6h18 M16 10a4 4 0 01-8 0"
const getCategoryIcon = (name) => categoryIcons[name?.toLowerCase()] || defaultIcon
</script>

<template>
<Head :title="category.name" />

  <!-- CATEGORY HEADER -->
  <motion.div
    initial="hidden"
    animate="visible"
    :variants="{
      hidden: {},
      visible: { transition: { staggerChildren: 0.15 } },
    }"
    class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 pb-6"
  >
    <div class="flex flex-col sm:flex-row items-center gap-8 sm:gap-10">

      <!-- Animated Icon -->
      <motion.div
        :variants="{
          hidden: { opacity: 0, scale: 0.5, rotate: -10 },
          visible: { opacity: 1, scale: 1, rotate: 0, transition: { type: 'spring', stiffness: 200, damping: 15 } },
        }"
        class="relative shrink-0"
      >
        <!-- Glow -->
        <div class="absolute inset-0 blur-3xl bg-[#D4AF37]/50 rounded-full scale-150 animate-logo-glow"></div>
        <!-- Pulse rings -->
        <div class="absolute inset-[-12px] rounded-full border border-gray-200/60 animate-logo-pulse-ring"></div>
        <div class="absolute inset-[-24px] rounded-full border border-gray-100/40 animate-logo-pulse-ring" style="animation-delay:1s;"></div>
        <!-- Orbit dots -->
        <div class="absolute inset-[-16px]" style="animation:logo-orbit 12s linear infinite;">
          <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#D4AF37] rounded-full"></div>
        </div>
        <div class="absolute inset-[-16px]" style="animation:logo-orbit-reverse 16s linear infinite;">
          <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-[#D4AF37] rounded-full"></div>
        </div>
        <!-- Main icon card -->
        <div class="relative w-28 h-28 bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-100 dark:border-[#D4AF37]/20 shadow-xl flex items-center justify-center animate-logo-float">
          <svg class="w-12 h-12 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path :d="getCategoryIcon(category.name)" />
          </svg>
        </div>
      </motion.div>

      <!-- Text -->
      <motion.div
        :variants="{
          hidden: { opacity: 0, x: 30 },
          visible: { opacity: 1, x: 0, transition: { duration: 0.5 } },
        }"
        class="text-center sm:text-left"
      >
        <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest text-[#D4AF37] bg-[#D4AF37]/10 px-3 py-1 rounded-full mb-3">
          Category
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1A1A1A] dark:text-[#F5F5F5] tracking-tight mb-1">
          {{ category.name }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0] max-w-md">
          {{ products.total }} product{{ products.total !== 1 ? 's' : '' }} available in this category
        </p>
      </motion.div>
    </div>
  </motion.div>

  <!-- BREADCRUMB + OTHER CATEGORIES -->
  <motion.div
    :initial="{ opacity: 0, y: 2 }"
    :animate="{ opacity: 1, y: 0 }"
    :transition="{ delay: 0.3, duration: 0.4 }"
    class="max-w-7xl mx-auto px-4 sm:px-6 pb-8"
  >
    <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-[#A0A0A0]">
        <Link href="/" class="hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] transition-colors no-underline text-gray-500 dark:text-[#A0A0A0]">Home</Link>
        <span class="dark:text-[#A0A0A0]">/</span>
        <span class="text-[#1A1A1A] dark:text-[#F5F5F5] font-semibold">{{ category.name }}</span>
    </nav>
  </motion.div>

  <!-- FILTERS -->
  <motion.div
    :initial="{ opacity: 0, y: 2 }"
    :animate="{ opacity: 1, y: 0 }"
    :transition="{ delay: 0.35, duration: 0.5 }"
    class="max-w-7xl mx-auto px-4 sm:px-6 pb-8"
  >
    <div class="bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-[#D4AF37]/20 p-4 sm:p-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Search -->
        <div class="relative">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input v-model="search" type="text" placeholder="Search products..."
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-gray-400 dark:focus:border-slate-500 transition-all" />
        </div>

        <!-- Min Price -->
        <div>
          <input v-model="minPrice" type="number" min="0" step="0.01" placeholder="Min price"
            @change="applyPriceFilter"
            class="w-full px-4 py-2.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-gray-400 dark:focus:border-slate-500 transition-all" />
        </div>

        <!-- Max Price -->
        <div>
          <input v-model="maxPrice" type="number" min="0" step="0.01" placeholder="Max price"
            @change="applyPriceFilter"
            class="w-full px-4 py-2.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-gray-400 dark:focus:border-slate-500 transition-all" />
        </div>

        <!-- Sort -->
        <div>
          <select v-model="sortBy"
            class="w-full px-4 py-2.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] focus:outline-none focus:border-gray-400 dark:focus:border-slate-500 transition-all appearance-none cursor-pointer">
            <option value="">Sort by: Latest</option>
            <option value="low-to-high">Price: Low to High</option>
            <option value="high-to-low">Price: High to Low</option>
          </select>
        </div>

      </div>
    </div>
  </motion.div>

  <!-- MAIN CONTENT WITH SIDEBAR -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-16">
    <div class="flex flex-col lg:flex-row gap-8">

      <!-- SIDEBAR: Subcategories -->
      <motion.aside
        :initial="{ opacity: 0, x: -30 }"
        :animate="{ opacity: 1, x: 0 }"
        :transition="{ delay: 0.4, duration: 0.5 }"
        v-if="subcategories?.length" class="lg:w-64 shrink-0"
      >
        <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-[#D4AF37]/20 p-5 sticky top-24">
          <h4 class="text-sm font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-4 uppercase tracking-wider">Subcategories</h4>
          <nav class="flex flex-col gap-1">
            <motion.button
              :whileHover="{ x: 3 }"
              :whileTap="{ scale: 0.97 }"
              @click="filterBySubcategory('')"
              :class="[
                'text-left px-3 py-2 rounded-xl text-sm font-medium transition-all cursor-pointer',
                !selectedSubcategory
                  ? 'border border-gray-400 text-[#D4AF37] dark:text-[#D4AF37]'
                  : 'text-gray-600 dark:text-[#A0A0A0] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] border border-transparent'
              ]">
              All {{ category.name }}
            </motion.button>
            <motion.button
              v-for="sub in subcategories" :key="sub.id"
              :whileHover="{ x: 3 }"
              :whileTap="{ scale: 0.97 }"
              @click="filterBySubcategory(sub.id)"
              :class="[
                'text-left px-3 py-2 rounded-xl text-sm font-medium transition-all cursor-pointer flex items-center justify-between',
                selectedSubcategory == sub.id
                  ? 'border border-gray-400 text-[#D4AF37] dark:text-[#D4AF37]'
                  : 'text-gray-600 dark:text-[#A0A0A0] hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] border border-transparent'
              ]">
              <span>{{ sub.name }}</span>
              <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">{{ sub.products_count }}</span>
            </motion.button>
          </nav>
        </div>
      </motion.aside>

      <!-- PRODUCTS GRID -->
      <div class="flex-1 min-w-0">

        <div class="flex items-center justify-between mb-5">
          <h3 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">
            {{ category.name }}
            <span class="font-normal text-gray-400 dark:text-[#A0A0A0] text-sm ml-1">· {{ products.total }} items</span>
          </h3>
          <Link href="/" class="text-sm font-semibold text-[#D4AF37] dark:text-[#D4AF37] hover:text-[#D4AF37] dark:hover:text-[#D4AF37] no-underline transition-colors">
            &larr; Back to Home
          </Link>
        </div>

        <div v-if="products.data.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <motion.div
            v-for="(product, index) in products.data"
            :key="product.id"
            initial="hidden"
            animate="visible"
            :variants="{
              hidden: { opacity: 0, y: 2 },
              visible: { opacity: 1, y: 0, transition: { duration: 0.4, delay: Math.min(index * 0.06, 0.4), ease: [0.22, 1, 0.36, 1] } },
            }"
            :whileHover="{ y: -1 }"
          >
            <ProductCard
              :product="product"
              :is-in-wishlist="isInWishlist(product)"
              :is-in-cart="isInCart(product)"
              @add-to-cart="addToCart(product)"
              @toggle-wishlist="toggleWishlist(product)" />
          </motion.div>
        </div>

        <!-- Empty state -->
        <motion.div
          :initial="{ opacity: 0, scale: 0.95 }"
          :animate="{ opacity: 1, scale: 1 }"
          v-else class="text-center py-20 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-[#D4AF37]/20"
        >
          <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10 mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
            </svg>
          </div>
          <p class="text-gray-500 dark:text-[#A0A0A0] text-sm mb-4">No products in this category yet.</p>
          <Link href="/" class="inline-flex items-center gap-2 bg-[#D4AF37] dark:bg-[#D4AF37] hover:bg-[#B8960F] dark:hover:bg-[#C9A032] text-white text-sm font-bold px-6 py-3 rounded-xl transition-all no-underline active:scale-95">
            Browse All Products
          </Link>
        </motion.div>

        <!-- Pagination -->
        <Pagination v-if="products.data.length" :links="products.links" />

      </div>

    </div>
  </div>

</template>
