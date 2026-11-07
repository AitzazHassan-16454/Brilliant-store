<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue"
import { router, Link, Head, usePage } from "@inertiajs/vue3"
import ProductCard from "./ProductCard.vue"
import { useAuthModal } from "../composables/useAuthModal.js"
import { useNotification } from "../composables/useNotification.js"

const props = defineProps({
  products: { type: Object, default: () => ({ data: [], total: 0, last_page: 1, current_page: 1, from: 0, to: 0, per_page: 12 }) },
  categories: { type: Array, default: () => [] },
  category: { type: Object, default: null },
  filters: { type: Object, default: () => ({}) },
  subcategories: { type: Array, default: () => [] },
  cartProductIds: { type: Array, default: () => [] },
  wishlistProductIds: { type: Array, default: () => [] },
})

const page = usePage()
const { openLogin } = useAuthModal()
const { success, info } = useNotification()

const isInCart = (product) => props.cartProductIds?.includes(product.id) || false
const isInWishlist = (product) => props.wishlistProductIds?.includes(product.id) || false

const addToCart = (product) => {
  if (!page.props.auth?.user) {
    openLogin()
    return
  }
  router.post("/cart", { product_id: product.id }, {
    preserveScroll: true,
    onSuccess: () => {
      success({
        title: "Added to Bag",
        message: `${product.name} is now in your shopping bag.`,
        action: {
          label: "View Bag",
          onClick: () => router.visit("/cart"),
        },
      })
    },
  })
}

const toggleWishlist = (product) => {
  if (!page.props.auth?.user) {
    openLogin()
    return
  }
  const wasInWishlist = isInWishlist(product)
  router.post("/wishlist", { product_id: product.id }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      if (wasInWishlist) {
        info({
          title: "Removed from Wishlist",
          message: `${product.name} was removed from your favorites.`,
        })
      } else {
        success({
          title: "Saved to Wishlist",
          message: `${product.name} was added to your favorites.`,
        })
      }
    },
  })
}

// Active category slug detection
const activeCategorySlug = computed(() => {
  if (props.filters?.category) {
    return String(props.filters.category)
  }
  if (props.category?.uid) {
    return String(props.category.uid)
  }
  return "all"
})

// Active subcategory slug/id detection
const activeSubcategorySlug = computed(() => {
  if (props.filters?.subcategory) {
    return String(props.filters.subcategory)
  }
  if (props.filters?.subcategory_id) {
    return String(props.filters.subcategory_id)
  }
  return "all"
})

// Current Category Model (if matching any category)
const currentCategoryModel = computed(() => {
  if (props.category) return props.category
  if (!activeCategorySlug.value || activeCategorySlug.value === "all") return null

  const slug = activeCategorySlug.value.toLowerCase()
  return props.categories.find(c =>
    String(c.uid).toLowerCase() === slug ||
    String(c.id) === slug ||
    (c.name && c.name.toLowerCase().replace(/\s+/g, "-") === slug) ||
    (c.name && c.name.toLowerCase() === slug)
  ) || null
})

// Section Header Title & Subtitle
const headerWords = computed(() => {
  if (currentCategoryModel.value?.name) {
    return currentCategoryModel.value.name.split(/\s+/).filter(Boolean)
  }
  return ["Shop", "Collection"]
})

const headerSubtitle = computed(() => {
  if (currentCategoryModel.value?.description) {
    return currentCategoryModel.value.description
  }
  return "Browse our curated catalog of modern essentials — filter by category, price, and more."
})

// Sort handling
const sortOptions = [
  { value: "featured", label: "All" },
  { value: "price-low", label: "Price: Low to High" },
  { value: "price-high", label: "Price: High to Low" },
  { value: "newest", label: "Newest" },
]

const currentSort = computed(() => {
  const s = props.filters?.sortBy || props.filters?.sort
  if (s === "low-to-high") return "price-low"
  if (s === "high-to-low") return "price-high"
  return s || "featured"
})

// Customizable pagination per-page options
const perPageOptions = [12, 24, 48, 96]
const currentPerPage = computed(() => Number(props.filters?.per_page || props.products?.per_page || 12))

// Jump to page input
const jumpPageInput = ref(props.products?.current_page || 1)
watch(() => props.products?.current_page, (newVal) => {
  jumpPageInput.value = newVal || 1
})

// Filtered categories and subcategories: show only when there is > 0 active products
const activeCategories = computed(() => {
  return props.categories.filter(c => c.products_count === undefined || Number(c.products_count) > 0)
})

const visibleSubcategories = computed(() => {
  return (props.subcategories || []).filter(sub => sub.products_count === undefined || Number(sub.products_count) > 0)
})

// Category lists for pills and sidebar
const categoryChips = computed(() => [
  { value: "all", label: "All" },
  ...activeCategories.value.map(c => ({
    value: c.uid || String(c.id),
    label: c.name || c.label,
  }))
])

const sidebarCategories = computed(() => [
  { value: "all", label: "All Categories" },
  ...activeCategories.value.map(c => ({
    value: c.uid || String(c.id),
    label: c.name || c.label,
  }))
])

// Price range slider & radio presets (0 to 500)
const minPriceLimit = 0
const maxPriceLimit = 500
const priceSliderValue = ref(props.filters?.max_price !== undefined && props.filters?.max_price !== null && props.filters?.max_price !== "" ? Number(props.filters.max_price) : 500)

watch(() => props.filters?.max_price, (newVal) => {
  if (newVal !== undefined && newVal !== null && newVal !== "") {
    priceSliderValue.value = Number(newVal)
  } else {
    priceSliderValue.value = maxPriceLimit
  }
}, { immediate: true })

const priceProgressPercent = computed(() => {
  const val = Math.min(Math.max(priceSliderValue.value, minPriceLimit), maxPriceLimit)
  return ((val - minPriceLimit) / (maxPriceLimit - minPriceLimit)) * 100
})

const priceRadioPresets = [
  { id: "all", label: "All Prices", min: null, max: null },
  { id: "0-50", label: "$0 - $50", min: 0, max: 50 },
  { id: "50-100", label: "$50 - $100", min: 50, max: 100 },
  { id: "100-250", label: "$100 - $250", min: 100, max: 250 },
  { id: "250-500", label: "$250 - $500", min: 250, max: 500 },
]

const selectedPriceRadio = computed(() => {
  const minRaw = props.filters?.min_price
  const maxRaw = props.filters?.max_price
  const hasMin = minRaw !== undefined && minRaw !== null && minRaw !== ""
  const hasMax = maxRaw !== undefined && maxRaw !== null && maxRaw !== ""

  if (!hasMin && !hasMax) return "all"

  const min = hasMin ? Number(minRaw) : null
  const max = hasMax ? Number(maxRaw) : null

  if ((min === 0 || min === null) && max === 50) return "0-50"
  if (min === 50 && max === 100) return "50-100"
  if (min === 100 && max === 250) return "100-250"
  if (min === 250 && (max === 500 || max === null)) return "250-500"
  return "custom"
})

const selectPriceRadio = (preset) => {
  if (preset.id === "all") {
    priceSliderValue.value = maxPriceLimit
    navigateWithFilters({ min_price: null, max_price: null })
  } else {
    priceSliderValue.value = preset.max ?? maxPriceLimit
    navigateWithFilters({ min_price: preset.min, max_price: preset.max })
  }
}

// Mobile bottom sheet state
const mobileSheet = ref(null) // 'filter' | 'sort' | null

const openMobileSheet = (type) => {
  mobileSheet.value = type
}

const closeMobileSheet = () => {
  mobileSheet.value = null
}

watch(mobileSheet, (val) => {
  if (typeof document !== "undefined") {
    document.body.style.overflow = val ? "hidden" : ""
  }
})

onBeforeUnmount(() => {
  if (typeof document !== "undefined") {
    document.body.style.overflow = ""
  }
})

// Filter count badge on mobile
const activeFilterCount = computed(() => {
  let count = 0
  if (activeCategorySlug.value !== "all") count += 1
  if (activeSubcategorySlug.value !== "all") count += 1
  if (props.filters?.min_price || props.filters?.max_price) count += 1
  return count
})

// Navigation dispatcher
const navigateWithFilters = (updates = {}) => {
  const currentParams = new URLSearchParams(window.location.search)

  for (const [key, val] of Object.entries(updates)) {
    if (val === undefined || val === null || val === "" || val === "all") {
      currentParams.delete(key)
    } else {
      currentParams.set(key, String(val))
    }
  }

  if (!("page" in updates)) {
    currentParams.delete("page")
  }

  let basePath = window.location.pathname
  if (basePath.startsWith("/categories") && ("category" in updates)) {
    basePath = "/shop"
  }

  const query = currentParams.toString()
  const destination = query ? `${basePath}?${query}` : basePath
  router.get(destination, {}, { preserveScroll: true })
}

const setCategory = (catValue) => {
  navigateWithFilters({ category: catValue, subcategory: null, subcategory_id: null })
}

const setSubcategory = (subValue) => {
  navigateWithFilters({ subcategory: subValue, subcategory_id: null })
}

const setSort = (sortValue) => {
  navigateWithFilters({ sort: sortValue })
}

const setPerPage = (count) => {
  navigateWithFilters({ per_page: count, page: 1 })
}

const selectSortMobile = (sortValue) => {
  closeMobileSheet()
  setSort(sortValue)
}

const onPriceSliderChange = () => {
  navigateWithFilters({ min_price: null, max_price: priceSliderValue.value })
}

const applyPriceFilterMobile = () => {
  closeMobileSheet()
  navigateWithFilters({ min_price: null, max_price: priceSliderValue.value })
}

const clearAllFilters = () => {
  closeMobileSheet()
  priceSliderValue.value = maxPriceLimit
  let basePath = window.location.pathname
  if (basePath.startsWith("/categories")) {
    basePath = "/shop"
  }
  router.get(basePath, {}, { preserveScroll: true })
}

const goToPage = (pageNumber) => {
  if (pageNumber < 1 || pageNumber > (props.products?.last_page || 1)) return
  navigateWithFilters({ page: pageNumber })
}

const onJumpPageSubmit = () => {
  const target = Math.max(1, Math.min(Number(jumpPageInput.value) || 1, props.products?.last_page || 1))
  goToPage(target)
}

// Pagination numbers
const paginationPages = computed(() => {
  const current = props.products?.current_page || 1
  const last = props.products?.last_page || 1
  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }
  if (current <= 4) {
    return [1, 2, 3, 4, 5, "...", last]
  }
  if (current >= last - 3) {
    return [1, "...", last - 4, last - 3, last - 2, last - 1, last]
  }
  return [1, "...", current - 1, current, current + 1, "...", last]
})
</script>

<template>
  <div class="min-h-screen bg-background py-6 pb-24 sm:py-12 md:pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Section Header -->
      <div class="mb-6 sm:mb-8">
        <div class="mb-8 sm:mb-10">
          <h2 class="mb-3 text-3xl font-bold leading-tight text-foreground sm:mb-4 sm:text-4xl">
            <span class="inline-flex flex-wrap items-baseline gap-x-[0.24em] gap-y-1 justify-start">
              <span
                v-for="(word, i) in headerWords"
                :key="`${word}-${i}`"
                :class="{ 'text-gold-gradient': i === headerWords.length - 1 }"
              >
                {{ word }}
              </span>
            </span>
          </h2>
          <p class="max-w-2xl text-muted-foreground leading-7 sm:text-lg">
            {{ headerSubtitle }}
          </p>
        </div>
      </div>

      <!-- Mobile Category Chips (Horizontal Scroll) -->
      <div class="relative mb-5 md:hidden">
        <div class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-r from-background to-transparent" />
        <div class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-l from-background to-transparent" />
        <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-6 pb-1">
          <button
            v-for="cat in categoryChips"
            :key="cat.value"
            type="button"
            @click="setCategory(cat.value)"
            :class="[
              'shrink-0 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-[13px] font-medium transition-colors cursor-pointer',
              activeCategorySlug === cat.value
                ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A] shadow-sm'
                : 'shop-chip border-border'
            ]"
          >
            {{ cat.label }}
          </button>
        </div>
      </div>

      <!-- Main Layout: Fixed/Sticky Desktop Sidebars + Products Grid -->
      <div class="flex flex-col md:flex-row gap-8 items-start relative">

        <!-- Desktop Fixed / Sticky Sidebar (stays locked at top while products scroll) -->
        <aside class="hidden md:block w-64 shrink-0 sticky top-24 self-start space-y-6">

          <!-- 1. Main Category & Filters Card -->
          <div class="bg-card rounded-xl p-6 border border-border shadow-luxury space-y-6">

            <!-- Category Filter -->
            <div>
              <h3 class="font-semibold text-foreground mb-4">Category</h3>
              <div class="space-y-2">
                <label
                  v-for="cat in sidebarCategories"
                  :key="cat.value"
                  class="flex items-center space-x-3 cursor-pointer group"
                >
                  <input
                    type="radio"
                    name="category"
                    :value="cat.value"
                    :checked="activeCategorySlug === cat.value"
                    @change="setCategory(cat.value)"
                    class="h-4 w-4 border-border text-[#D4AF37] focus:ring-[#D4AF37]"
                  />
                  <span class="text-sm text-muted-foreground group-hover:text-[#D4AF37] transition-colors">
                    {{ cat.label }}
                  </span>
                </label>
              </div>
            </div>

            <!-- Price Range Filter -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-foreground">Price Range</h3>
                <button
                  v-if="selectedPriceRadio !== 'all'"
                  type="button"
                  @click="selectPriceRadio(priceRadioPresets[0])"
                  class="text-xs font-semibold text-[#D4AF37] hover:underline cursor-pointer"
                >
                  Reset
                </button>
              </div>

              <!-- Radio presets starting from 0 to 500 -->
              <div class="space-y-2 mb-4">
                <label
                  v-for="preset in priceRadioPresets"
                  :key="preset.id"
                  class="flex items-center space-x-3 cursor-pointer group"
                >
                  <input
                    type="radio"
                    name="price_range"
                    :value="preset.id"
                    :checked="selectedPriceRadio === preset.id"
                    @change="selectPriceRadio(preset)"
                    class="h-4 w-4 border-border text-[#D4AF37] focus:ring-[#D4AF37]"
                  />
                  <span class="text-sm text-muted-foreground group-hover:text-[#D4AF37] transition-colors">
                    {{ preset.label }}
                  </span>
                </label>
              </div>

              <!-- Price Slider ($0 to $500) -->
              <div class="pt-3 border-t border-border/70 space-y-3">
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>Custom Max</span>
                  <span class="font-semibold text-[#D4AF37]">${{ priceSliderValue }}</span>
                </div>
                <input
                  type="range"
                  :min="minPriceLimit"
                  :max="maxPriceLimit"
                  step="10"
                  :value="priceSliderValue"
                  @input="priceSliderValue = Number($event.target.value)"
                  @change="onPriceSliderChange"
                  :style="{ '--range-progress': `${priceProgressPercent}%` }"
                  class="public-range-slider h-2 w-full cursor-pointer appearance-none"
                />
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <span>${{ minPriceLimit }}</span>
                  <span>${{ maxPriceLimit }}</span>
                </div>
              </div>
            </div>

            <!-- Clear Filters -->
            <div class="pt-4 border-t border-border">
              <button
                type="button"
                @click="clearAllFilters"
                class="w-full px-4 py-2 text-sm text-muted-foreground transition-colors hover:text-foreground cursor-pointer"
              >
                Clear Filters
              </button>
            </div>

          </div>

          <!-- 2. Subcategory Sidebar Card (Placed below main category card, shown only when subcategories with products exist) -->
          <div v-if="visibleSubcategories && visibleSubcategories.length > 0" class="bg-card rounded-xl p-6 border border-border shadow-luxury space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-foreground">Subcategories</h3>
              <button
                v-if="activeSubcategorySlug !== 'all'"
                type="button"
                @click="setSubcategory('all')"
                class="text-xs font-semibold text-[#D4AF37] hover:underline cursor-pointer"
              >
                Reset
              </button>
            </div>

            <!-- When subcategories are available -->
            <div class="space-y-2">
              <!-- All Subcategories -->
              <label class="flex items-center space-x-3 cursor-pointer group">
                <input
                  type="radio"
                  name="subcategory"
                  value="all"
                  :checked="activeSubcategorySlug === 'all'"
                  @change="setSubcategory('all')"
                  class="h-4 w-4 border-border text-[#D4AF37] focus:ring-[#D4AF37]"
                />
                <span class="text-sm text-muted-foreground group-hover:text-[#D4AF37] transition-colors">
                  All Subcategories
                </span>
              </label>

              <!-- Subcategory items -->
              <label
                v-for="sub in visibleSubcategories"
                :key="sub.id"
                class="flex items-center justify-between space-x-3 cursor-pointer group py-0.5"
              >
                <div class="flex items-center space-x-3 min-w-0">
                  <input
                    type="radio"
                    name="subcategory"
                    :value="sub.uid || String(sub.id)"
                    :checked="activeSubcategorySlug === (sub.uid || String(sub.id))"
                    @change="setSubcategory(sub.uid || String(sub.id))"
                    class="h-4 w-4 border-border text-[#D4AF37] focus:ring-[#D4AF37]"
                  />
                  <span class="text-sm text-muted-foreground group-hover:text-[#D4AF37] transition-colors truncate">
                    {{ sub.name }}
                  </span>
                </div>
                <span
                  v-if="sub.products_count !== undefined"
                  class="text-xs px-2 py-0.5 rounded-full bg-muted text-muted-foreground group-hover:text-foreground shrink-0 font-medium"
                >
                  {{ sub.products_count }}
                </span>
              </label>
            </div>
          </div>

        </aside>

        <!-- Product Listing Content (Scrolls while sidebar remains fixed) -->
        <div class="flex-1 min-w-0 w-full">

          <!-- Results count, Per-page & Sort toolbar -->
          <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-muted-foreground md:text-base">
              Showing <span class="font-medium text-foreground">{{ products.total || 0 }}</span> results
            </p>

            <div class="flex items-center gap-3 ml-auto">
              <!-- Customizable Items Per Page Selector (Desktop) -->
              <div class="hidden sm:flex items-center gap-2 text-xs sm:text-sm text-muted-foreground">
                <span>Per page:</span>
                <div class="inline-flex rounded-lg border border-border bg-card p-0.5 shadow-xs">
                  <button
                    v-for="count in perPageOptions"
                    :key="count"
                    type="button"
                    @click="setPerPage(count)"
                    :class="[
                      'px-2.5 py-1 text-xs font-semibold rounded-md transition-colors cursor-pointer',
                      currentPerPage === count
                        ? 'bg-[#D4AF37] text-[#0A0A0A] shadow-xs'
                        : 'text-muted-foreground hover:text-foreground'
                    ]"
                  >
                    {{ count }}
                  </button>
                </div>
              </div>

              <!-- Sort Dropdown -->
              <div class="relative hidden md:block">
                <select
                  :value="currentSort"
                  @change="setSort($event.target.value)"
                  class="cursor-pointer appearance-none rounded-lg border border-border bg-white px-4 py-2 pr-10 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#D4AF37] dark:bg-slate-950 dark:text-slate-100"
                >
                  <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Products Grid -->
          <div
            v-if="products.data && products.data.length > 0"
            class="grid grid-cols-2 gap-3 md:gap-6 lg:grid-cols-3"
          >
            <ProductCard
              v-for="item in products.data"
              :key="item.id"
              :product="item"
              :is-in-wishlist="isInWishlist(item)"
              :is-in-cart="isInCart(item)"
              @add-to-cart="addToCart(item)"
              @toggle-wishlist="toggleWishlist(item)"
            />
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 bg-card rounded-2xl border border-border p-8">
            <div class="mx-auto w-12 h-12 rounded-full bg-muted flex items-center justify-center text-muted-foreground mb-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="21" x2="14" y1="4" y2="4" stroke-linecap="round" />
                <line x1="10" x2="3" y1="4" y2="4" stroke-linecap="round" />
                <line x1="21" x2="12" y1="12" y2="12" stroke-linecap="round" />
                <line x1="8" x2="3" y1="12" y2="12" stroke-linecap="round" />
                <line x1="21" x2="16" y1="20" y2="20" stroke-linecap="round" />
                <line x1="12" x2="3" y1="20" y2="20" stroke-linecap="round" />
                <line x1="14" x2="14" y1="2" y2="6" stroke-linecap="round" />
                <line x1="8" x2="8" y1="10" y2="14" stroke-linecap="round" />
                <line x1="16" x2="16" y1="18" y2="22" stroke-linecap="round" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-foreground mb-2">No products found</h3>
            <p class="text-sm text-muted-foreground max-w-md mx-auto mb-6">
              We couldn't find any products matching your current filters. Try resetting the filters or selecting a different category or subcategory.
            </p>
            <button
              type="button"
              @click="clearAllFilters"
              class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-[#D4AF37] text-sm font-semibold text-[#0A0A0A] hover:bg-[#B8941E] transition-all cursor-pointer"
            >
              Clear All Filters
            </button>
          </div>

          <!-- Customizable Pagination Footer -->
          <div v-if="products.total > 0" class="mt-10 md:mt-12 pt-6 border-t border-border flex flex-col md:flex-row items-center justify-between gap-4">

            <!-- Summary text -->
            <div class="text-xs sm:text-sm text-muted-foreground">
              Showing <span class="font-semibold text-foreground">{{ products.from || 0 }}</span>
              to <span class="font-semibold text-foreground">{{ products.to || 0 }}</span>
              of <span class="font-semibold text-foreground">{{ products.total || 0 }}</span> products
            </div>

            <!-- Pagination Nav Controls -->
            <div v-if="(products.last_page || 1) > 1" class="flex flex-wrap items-center justify-center gap-2">
              <nav class="inline-flex items-center gap-1.5 rounded-full border border-border bg-card p-1.5 shadow-sm" aria-label="Pagination">
                <!-- First Page -->
                <button
                  type="button"
                  :disabled="products.current_page <= 1"
                  @click="goToPage(1)"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-foreground disabled:opacity-30 disabled:cursor-not-allowed hover:bg-muted transition-colors cursor-pointer"
                  title="First Page"
                  aria-label="First Page"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                  </svg>
                </button>

                <!-- Previous Page -->
                <button
                  type="button"
                  :disabled="products.current_page <= 1"
                  @click="goToPage(products.current_page - 1)"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-foreground disabled:opacity-30 disabled:cursor-not-allowed hover:bg-muted transition-colors cursor-pointer"
                  title="Previous Page"
                  aria-label="Previous Page"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/>
                  </svg>
                </button>

                <!-- Page numbers -->
                <button
                  v-for="(p, idx) in paginationPages"
                  :key="`page-${idx}`"
                  type="button"
                  @click="typeof p === 'number' && goToPage(p)"
                  :disabled="p === '...'"
                  :class="[
                    'flex h-9 min-w-[36px] px-2 items-center justify-center rounded-full text-xs font-semibold transition-colors',
                    p === products.current_page
                      ? 'bg-[#D4AF37] text-[#0A0A0A]'
                      : p === '...'
                        ? 'text-muted-foreground cursor-default'
                        : 'text-foreground hover:bg-muted cursor-pointer'
                  ]"
                >
                  {{ p }}
                </button>

                <!-- Next Page -->
                <button
                  type="button"
                  :disabled="products.current_page >= products.last_page"
                  @click="goToPage(products.current_page + 1)"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-foreground disabled:opacity-30 disabled:cursor-not-allowed hover:bg-muted transition-colors cursor-pointer"
                  title="Next Page"
                  aria-label="Next Page"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                  </svg>
                </button>

                <!-- Last Page -->
                <button
                  type="button"
                  :disabled="products.current_page >= products.last_page"
                  @click="goToPage(products.last_page)"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-foreground disabled:opacity-30 disabled:cursor-not-allowed hover:bg-muted transition-colors cursor-pointer"
                  title="Last Page"
                  aria-label="Last Page"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                  </svg>
                </button>
              </nav>

              <!-- Jump to page input -->
              <form @submit.prevent="onJumpPageSubmit" class="hidden sm:flex items-center gap-1.5 ml-2">
                <span class="text-xs text-muted-foreground">Go to:</span>
                <input
                  type="number"
                  min="1"
                  :max="products.last_page || 1"
                  v-model="jumpPageInput"
                  class="w-12 h-8 rounded-lg border border-border bg-card px-1 text-center text-xs font-semibold text-foreground focus:outline-none focus:ring-1 focus:ring-[#D4AF37]"
                />
                <button
                  type="submit"
                  class="h-8 px-2.5 rounded-lg border border-border bg-card text-xs font-semibold text-foreground hover:bg-muted transition-colors cursor-pointer"
                >
                  Go
                </button>
              </form>
            </div>

            <!-- Per-page selector on mobile -->
            <div class="sm:hidden flex items-center gap-2 text-xs text-muted-foreground">
              <span>Per page:</span>
              <div class="inline-flex rounded-lg border border-border bg-card p-0.5">
                <button
                  v-for="count in perPageOptions"
                  :key="count"
                  type="button"
                  @click="setPerPage(count)"
                  :class="[
                    'px-2 py-1 text-xs font-semibold rounded-md transition-colors',
                    currentPerPage === count
                      ? 'bg-[#D4AF37] text-[#0A0A0A]'
                      : 'text-muted-foreground'
                  ]"
                >
                  {{ count }}
                </button>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- Mobile Bottom Toolbar -->
    <div class="mobile-shop-toolbar fixed inset-x-0 bottom-0 z-40 border-t border-border md:hidden">
      <div class="grid grid-cols-2 divide-x divide-border">
        <button
          type="button"
          @click="openMobileSheet('filter')"
          class="flex min-h-[52px] items-center justify-center gap-2 text-sm font-semibold text-foreground active:bg-muted cursor-pointer"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="21" x2="14" y1="4" y2="4" stroke-linecap="round" />
            <line x1="10" x2="3" y1="4" y2="4" stroke-linecap="round" />
            <line x1="21" x2="12" y1="12" y2="12" stroke-linecap="round" />
            <line x1="8" x2="3" y1="12" y2="12" stroke-linecap="round" />
            <line x1="21" x2="16" y1="20" y2="20" stroke-linecap="round" />
            <line x1="12" x2="3" y1="20" y2="20" stroke-linecap="round" />
            <line x1="14" x2="14" y1="2" y2="6" stroke-linecap="round" />
            <line x1="8" x2="8" y1="10" y2="14" stroke-linecap="round" />
            <line x1="16" x2="16" y1="18" y2="22" stroke-linecap="round" />
          </svg>
          Filter
          <span v-if="activeFilterCount" class="flex h-5 w-5 items-center justify-center rounded-full bg-[#D4AF37] text-[11px] font-bold text-[#0A0A0A]">
            {{ activeFilterCount }}
          </span>
        </button>
        <button
          type="button"
          @click="openMobileSheet('sort')"
          class="flex min-h-[52px] items-center justify-center gap-2 text-sm font-semibold text-foreground active:bg-muted cursor-pointer"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 16 4 4 4-4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20V4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4h10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 8h7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 12h4" />
          </svg>
          Sort
        </button>
      </div>
    </div>

    <!-- Mobile Bottom Sheet / Modal (Teleported to body) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="mobileSheet" class="mobile-sheet-overlay fixed inset-0 z-50 md:hidden" @click="closeMobileSheet" />
      </Transition>

      <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        enter-from-class="translate-y-full"
        enter-to-class="translate-y-0"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-from-class="translate-y-0"
        leave-to-class="translate-y-full"
      >
        <div v-if="mobileSheet" class="mobile-sheet fixed inset-x-0 bottom-0 z-50 rounded-t-3xl border-t border-border md:hidden">
          <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-muted-foreground/30" />
          <div class="flex items-center justify-between px-5 pt-4 pb-2">
            <h3 class="text-base font-semibold text-foreground">
              {{ mobileSheet === 'filter' ? 'Filters' : 'Sort by' }}
            </h3>
            <button
              type="button"
              @click="closeMobileSheet"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-foreground active:scale-95 cursor-pointer"
              aria-label="Close"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Sort Sheet Content -->
          <div v-if="mobileSheet === 'sort'" class="px-3 pb-6">
            <button
              v-for="opt in sortOptions"
              :key="opt.value"
              type="button"
              @click="selectSortMobile(opt.value)"
              :class="[
                'flex min-h-[48px] w-full items-center justify-between rounded-xl px-4 text-left text-sm transition-colors cursor-pointer',
                currentSort === opt.value
                  ? 'bg-[#D4AF37]/10 font-semibold text-[#D4AF37]'
                  : 'text-foreground active:bg-muted'
              ]"
            >
              <span>{{ opt.label }}</span>
              <svg v-if="currentSort === opt.value" class="h-4 w-4 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
              </svg>
            </button>
          </div>

          <!-- Filter Sheet Content -->
          <div v-else-if="mobileSheet === 'filter'" class="max-h-[70vh] overflow-y-auto px-5 pb-6 space-y-6">
            <!-- Category -->
            <div>
              <h4 class="mb-3 text-sm font-semibold text-foreground">Category</h4>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="cat in sidebarCategories"
                  :key="cat.value"
                  type="button"
                  @click="closeMobileSheet(); setCategory(cat.value)"
                  :class="[
                    'rounded-full border px-4 py-2 text-[13px] font-medium transition-colors cursor-pointer',
                    activeCategorySlug === cat.value
                      ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A]'
                      : 'border-border bg-background text-muted-foreground'
                  ]"
                >
                  {{ cat.label }}
                </button>
              </div>
            </div>

            <!-- Subcategories (if available) -->
            <div v-if="visibleSubcategories && visibleSubcategories.length > 0">
              <h4 class="mb-3 text-sm font-semibold text-foreground">Subcategories</h4>
              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  @click="closeMobileSheet(); setSubcategory('all')"
                  :class="[
                    'rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors cursor-pointer',
                    activeSubcategorySlug === 'all'
                      ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A]'
                      : 'border-border bg-background text-muted-foreground'
                  ]"
                >
                  All Subcategories
                </button>
                <button
                  v-for="sub in visibleSubcategories"
                  :key="sub.id"
                  type="button"
                  @click="closeMobileSheet(); setSubcategory(sub.uid || String(sub.id))"
                  :class="[
                    'rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors cursor-pointer',
                    activeSubcategorySlug === (sub.uid || String(sub.id))
                      ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A]'
                      : 'border-border bg-background text-muted-foreground'
                  ]"
                >
                  {{ sub.name }}
                  <span v-if="sub.products_count !== undefined" class="ml-1 opacity-70">({{ sub.products_count }})</span>
                </button>
              </div>
            </div>

            <!-- Price Range -->
            <div>
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-foreground">Price Range</h4>
                <span class="text-xs font-semibold text-[#D4AF37]">${{ priceSliderValue }}</span>
              </div>
              <div class="flex flex-wrap gap-2 mb-4">
                <button
                  v-for="preset in priceRadioPresets"
                  :key="preset.id"
                  type="button"
                  @click="closeMobileSheet(); selectPriceRadio(preset)"
                  :class="[
                    'rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors cursor-pointer',
                    selectedPriceRadio === preset.id
                      ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A]'
                      : 'border-border bg-background text-muted-foreground'
                  ]"
                >
                  {{ preset.label }}
                </button>
              </div>
              <input
                type="range"
                :min="minPriceLimit"
                :max="maxPriceLimit"
                step="10"
                :value="priceSliderValue"
                @input="priceSliderValue = Number($event.target.value)"
                :style="{ '--range-progress': `${priceProgressPercent}%` }"
                class="public-range-slider h-2 w-full cursor-pointer appearance-none"
              />
              <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                <span>${{ minPriceLimit }}</span>
                <span>${{ maxPriceLimit }}</span>
              </div>
            </div>

            <!-- Items Per Page (Mobile) -->
            <div>
              <h4 class="mb-3 text-sm font-semibold text-foreground">Items Per Page</h4>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="count in perPageOptions"
                  :key="count"
                  type="button"
                  @click="closeMobileSheet(); setPerPage(count)"
                  :class="[
                    'rounded-full border px-4 py-1.5 text-xs font-medium transition-colors cursor-pointer',
                    currentPerPage === count
                      ? 'border-[#D4AF37] bg-[#D4AF37] text-[#0A0A0A]'
                      : 'border-border bg-background text-muted-foreground'
                  ]"
                >
                  {{ count }} items
                </button>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
              <button
                type="button"
                @click="clearAllFilters"
                class="flex h-11 flex-1 items-center justify-center rounded-full border border-border text-sm font-medium text-muted-foreground active:bg-muted cursor-pointer"
              >
                Clear All
              </button>
              <button
                type="button"
                @click="applyPriceFilterMobile"
                class="flex h-11 flex-1 items-center justify-center rounded-full bg-[#D4AF37] text-sm font-semibold text-[#0A0A0A] shadow-sm active:scale-[0.98] cursor-pointer"
              >
                Apply
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
