<script setup>
import { ref, watch, computed } from "vue"
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
  products: Object,
  categories: Array,
  filters: Object,
  cartProductIds: Array,
  wishlistProductIds: Array,
})

const isInCart = (product) => props.cartProductIds?.includes(product.id) || false
const isInWishlist = (product) => props.wishlistProductIds?.includes(product.id) || false

const addToCart = (product) => {
  if (!page.props.auth?.user) {
    openLogin()
    return
  }
  router.post("/cart", { product_id: product.id }, {
    preserveScroll: true,
    onSuccess: () => success("Added to cart"),
  })
}

const toggleWishlist = (product) => {
  if (!page.props.auth?.user) {
    openLogin()
    return
  }
  router.post("/wishlist", { product_id: product.id }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const search = ref(props.filters?.search || "")
const selectedCategory = ref(props.filters?.category || "")
const minPrice = ref(props.filters?.min_price || "")
const maxPrice = ref(props.filters?.max_price || "")
const sortBy = ref(props.filters?.sortBy || "")
const perPage = ref(Number(props.filters?.per_page || props.products?.per_page || 12))

const hasActiveFilters = computed(() => {
  return Boolean(
    search.value ||
    (selectedCategory.value && selectedCategory.value !== "All") ||
    minPrice.value ||
    maxPrice.value ||
    sortBy.value ||
    (perPage.value && perPage.value !== 12)
  )
})

const applyFilters = () => {
  router.get("/all-products", {
    search: search.value || undefined,
    category: (selectedCategory.value && selectedCategory.value !== "All") ? selectedCategory.value : undefined,
    min_price: minPrice.value || undefined,
    max_price: maxPrice.value || undefined,
    sortBy: sortBy.value || undefined,
    per_page: perPage.value || undefined,
  }, { preserveState: true, replace: true })
}

const applyPriceFilter = () => {
  applyFilters()
}

const setCategory = (catUid) => {
  selectedCategory.value = selectedCategory.value === catUid ? "" : catUid
  applyFilters()
}

const clearFilters = () => {
  search.value = ""
  selectedCategory.value = ""
  minPrice.value = ""
  maxPrice.value = ""
  sortBy.value = ""
  perPage.value = 12
  applyFilters()
}

watch(search, debounce(applyFilters, 400))
watch(sortBy, applyFilters)
watch(selectedCategory, applyFilters)
watch(perPage, applyFilters)
</script>

<template>
  <div>
    <Head title="All Products - Luxury Collection" />

    <!-- PAGE HEADER -->
    <motion.div
      initial="hidden"
      animate="visible"
      :variants="{
        hidden: {},
        visible: { transition: { staggerChildren: 0.15 } },
      }"
      class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 pb-6"
    >
      <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-10">

        <!-- Animated Luxury Emblem -->
        <motion.div
          :variants="{
            hidden: { opacity: 0, scale: 0.5, rotate: -10 },
            visible: { opacity: 1, scale: 1, rotate: 0, transition: { type: 'spring', stiffness: 200, damping: 15 } },
          }"
          class="relative shrink-0"
        >
          <!-- Ambient Glow -->
          <div class="absolute inset-0 blur-3xl bg-[#D4AF37]/50 rounded-full scale-150 animate-logo-glow"></div>
          <!-- Pulse Rings -->
          <div class="absolute inset-[-12px] rounded-full border border-gray-200/60 dark:border-[#D4AF37]/20 animate-logo-pulse-ring"></div>
          <div class="absolute inset-[-24px] rounded-full border border-gray-100/40 dark:border-[#D4AF37]/10 animate-logo-pulse-ring" style="animation-delay:1s;"></div>
          <!-- Orbiting Dots -->
          <div class="absolute inset-[-16px]" style="animation:logo-orbit 12s linear infinite;">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#D4AF37] rounded-full"></div>
          </div>
          <div class="absolute inset-[-16px]" style="animation:logo-orbit-reverse 16s linear infinite;">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-[#D4AF37] rounded-full"></div>
          </div>
          <!-- Icon Card -->
          <div class="relative w-24 h-24 sm:w-28 sm:h-28 bg-white dark:bg-[#1A1A1A] rounded-3xl border border-gray-100 dark:border-[#D4AF37]/20 shadow-xl flex items-center justify-center animate-logo-float">
            <svg class="w-10 h-10 sm:w-12 sm:h-12 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M6 3h12l4 6-10 13L2 9z"/>
              <path d="M2 9h20"/>
              <path d="M12 22L6 9"/>
              <path d="M12 22l6-13"/>
            </svg>
          </div>
        </motion.div>

        <!-- Title & Stats -->
        <motion.div
          :variants="{
            hidden: { opacity: 0, x: 30 },
            visible: { opacity: 1, x: 0, transition: { duration: 0.5 } },
          }"
          class="text-center sm:text-left"
        >
          <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest text-[#D4AF37] bg-[#D4AF37]/10 px-3 py-1 rounded-full mb-3">
            Full Catalog
          </span>
          <h1 class="text-3xl sm:text-4xl font-extrabold text-[#1A1A1A] dark:text-[#F5F5F5] tracking-tight mb-1">
            All Products
          </h1>
          <p class="text-sm text-gray-500 dark:text-[#A0A0A0] max-w-lg">
            {{ products.total }} luxury piece{{ products.total !== 1 ? 's' : '' }} crafted with precision and available in the collection.
          </p>
        </motion.div>
      </div>
    </motion.div>

    <!-- BREADCRUMB -->
    <motion.div
      :initial="{ opacity: 0, y: 2 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ delay: 0.25, duration: 0.4 }"
      class="max-w-7xl mx-auto px-4 sm:px-6 pb-6"
    >
      <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-[#A0A0A0]">
        <Link href="/" class="hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] transition-colors no-underline text-gray-500 dark:text-[#A0A0A0]">Home</Link>
        <span class="dark:text-[#A0A0A0]">/</span>
        <span class="text-[#1A1A1A] dark:text-[#F5F5F5] font-semibold">All Products</span>
      </nav>
    </motion.div>

    <!-- CATEGORY PILLS BAR -->
    <motion.div
      v-if="categories?.length"
      :initial="{ opacity: 0, y: 4 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ delay: 0.3, duration: 0.4 }"
      class="max-w-7xl mx-auto px-4 sm:px-6 pb-6"
    >
      <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        <button
          type="button"
          @click="setCategory('')"
          class="shrink-0 px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer border"
          :class="!selectedCategory || selectedCategory === 'All'
            ? 'bg-[#D4AF37] text-white border-[#D4AF37] shadow-sm shadow-[#D4AF37]/20'
            : 'bg-white dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] border-gray-200 dark:border-[#D4AF37]/20 hover:border-[#D4AF37]/50'"
        >
          All Categories
        </button>
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          @click="setCategory(cat.uid)"
          class="shrink-0 px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer border flex items-center gap-1.5"
          :class="selectedCategory === cat.uid
            ? 'bg-[#D4AF37] text-white border-[#D4AF37] shadow-sm shadow-[#D4AF37]/20'
            : 'bg-white dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] border-gray-200 dark:border-[#D4AF37]/20 hover:border-[#D4AF37]/50'"
        >
          <span>{{ cat.name }}</span>
          <span class="text-[10px] opacity-75">({{ cat.products_count }})</span>
        </button>
      </div>
    </motion.div>

    <!-- FILTER BAR -->
    <motion.div
      :initial="{ opacity: 0, y: 6 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ delay: 0.35, duration: 0.5 }"
      class="max-w-7xl mx-auto px-4 sm:px-6 pb-8"
    >
      <div class="bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-2xl border border-gray-100 dark:border-[#D4AF37]/20 p-4 sm:p-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

          <!-- Search -->
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Search products..."
              class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-[#111111] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-[#D4AF37] transition-all"
            />
          </div>

          <!-- Min Price -->
          <div>
            <input
              v-model="minPrice"
              type="number"
              min="0"
              step="0.01"
              placeholder="Min price"
              @change="applyPriceFilter"
              class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-[#D4AF37] transition-all"
            />
          </div>

          <!-- Max Price -->
          <div>
            <input
              v-model="maxPrice"
              type="number"
              min="0"
              step="0.01"
              placeholder="Max price"
              @change="applyPriceFilter"
              class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-[#D4AF37] transition-all"
            />
          </div>

          <!-- Sort -->
          <div>
            <select
              v-model="sortBy"
              class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-sm text-[#1A1A1A] dark:text-[#F5F5F5] focus:outline-none focus:border-[#D4AF37] transition-all cursor-pointer"
            >
              <option value="">Sort: Featured</option>
              <option value="low-to-high">Price: Low to High</option>
              <option value="high-to-low">Price: High to Low</option>
            </select>
          </div>

        </div>

        <!-- Filter info & Clear action -->
        <div v-if="hasActiveFilters" class="mt-4 pt-3 border-t border-gray-200/60 dark:border-[#D4AF37]/15 flex items-center justify-between text-xs text-gray-500 dark:text-[#A0A0A0]">
          <span>Filtering active products</span>
          <button
            type="button"
            @click="clearFilters"
            class="text-[#D4AF37] hover:underline font-semibold cursor-pointer border-none bg-transparent"
          >
            Clear all filters
          </button>
        </div>
      </div>
    </motion.div>

    <!-- PRODUCTS GRID -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-16">
      <div v-if="products.data?.length">
        <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4">
          <ProductCard
            v-for="product in products.data"
            :key="product.id"
            :product="product"
            :is-in-wishlist="isInWishlist(product)"
            :is-in-cart="isInCart(product)"
            @add-to-cart="addToCart(product)"
            @toggle-wishlist="toggleWishlist(product)"
          />
        </div>

        <Pagination
          :links="products.links"
          :meta="products"
          :per-page="perPage"
          :per-page-options="[8, 12, 24, 48, 96]"
          item-label="products"
          class="mt-10"
        />
      </div>

      <!-- EMPTY STATE -->
      <motion.div
        v-else
        :initial="{ opacity: 0, scale: 0.95 }"
        :animate="{ opacity: 1, scale: 1 }"
        class="text-center py-16 px-4 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-3xl border border-gray-100 dark:border-[#D4AF37]/15 max-w-xl mx-auto shadow-sm"
      >
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10 mx-auto mb-4">
          <svg class="w-8 h-8 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-1">No products found</h3>
        <p class="text-gray-500 dark:text-[#A0A0A0] text-sm mb-6">
          Try adjusting your search query, price range, or category filter to find what you are looking for.
        </p>
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="clearFilters"
          class="inline-flex items-center gap-2 bg-[#D4AF37] hover:bg-[#B8960F] text-white text-sm font-bold px-6 py-3 rounded-xl transition-all cursor-pointer border-none shadow-md shadow-[#D4AF37]/20"
        >
          Reset Filters
        </button>
        <Link
          v-else
          href="/"
          class="inline-flex items-center gap-2 bg-[#D4AF37] hover:bg-[#B8960F] text-white text-sm font-bold px-6 py-3 rounded-xl transition-all no-underline shadow-md shadow-[#D4AF37]/20"
        >
          Return to Home
        </Link>
      </motion.div>
    </div>
  </div>
</template>
