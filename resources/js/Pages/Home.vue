<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import { router, usePage, Link, Head } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"
import { useAuthModal } from "../composables/useAuthModal.js"
import AppFooter from "./components/AppFooter.vue"
import ProductCard from "../Components/ProductCard.vue"
import { motion } from "motion-v"
import { motionPresets as m, staggerContainer, staggerItem, itemFadeUp } from "../lib/motion.js"

const heroStagger = staggerContainer(0.12, 0.15)
const heroItem = staggerItem

const page = usePage()
const site = computed(() => page.props.site ?? {})
const siteName = computed(() => site.value.site_name || "GharBazaar")
const siteTagline = computed(() => site.value.tagline || "Shop Smart • Live Better")
const { success } = useNotification()
const { openLogin } = useAuthModal()

const props = defineProps({
  products: Object,
  categories: Array,
  cartProductIds: Array,
  wishlistProductIds: Array,
  latestProduct: Object,
  stats: Object,
  trendingProducts: Array,
})

const isInCart = (product) => props.cartProductIds?.includes(product?.id)
const isInWishlist = (product) => props.wishlistProductIds?.includes(product?.id)

const spotlightProduct = computed(() => {
  // Prefer a curated trending product that has a real image and name
  const validTrending = props.trendingProducts?.find(
    (p) => p && p.image && p.name && !p.name.toLowerCase().includes('iure sit')
  )
  if (validTrending) return validTrending

  // If latestProduct has an image and clean name
  if (props.latestProduct?.image && !props.latestProduct?.name?.toLowerCase().includes('iure sit')) {
    return props.latestProduct
  }

  // Fallback to first catalog product with image
  const firstWithImage = props.products?.data?.find((p) => p && p.image)
  if (firstWithImage) return firstWithImage

  return props.trendingProducts?.[0] || props.latestProduct || null
})

const comparePrice = (price) => {
  const num = Number(price)
  if (isNaN(num) || num <= 0) return null
  return num * 1.25
}

const formatPrice = (val) => {
  const num = Number(val)
  return isNaN(num) ? "$0.00" : `$${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const addToCart = (product) => {
  if (!product) return
  if (!page.props.auth.user) { openLogin(); return }
  router.post("/cart", { product_id: product.id }, {
    preserveScroll: true,
    onSuccess: () => success("Added to cart"),
  })
}

const toggleWishlist = (product) => {
  if (!product) return
  if (!page.props.auth.user) { openLogin(); return }
  router.post("/wishlist", { product_id: product.id }, { preserveScroll: true })
}

// ── Mobile Slider Navigation: Category ────────────────
const activeCategoryIndex = ref(0)
const categoryScrollRef = ref(null)
const isCategoryPaused = ref(false)
let categoryTimer = null
let categoryResumeTimeout = null

const pauseCategory = () => {
  isCategoryPaused.value = true
  if (categoryResumeTimeout) clearTimeout(categoryResumeTimeout)
}

const resumeCategory = () => {
  if (categoryResumeTimeout) clearTimeout(categoryResumeTimeout)
  categoryResumeTimeout = setTimeout(() => {
    isCategoryPaused.value = false
  }, 2500)
}

const onCategoryScroll = (e) => {
  const el = e.target
  if (!el || !el.children.length) return
  const scrollLeft = el.scrollLeft
  let closestIdx = 0
  let minDiff = Infinity
  Array.from(el.children).forEach((child, idx) => {
    const targetLeft = idx === 0 ? 0 : child.offsetLeft - (parseFloat(getComputedStyle(el).paddingLeft) || 16)
    const diff = Math.abs(targetLeft - scrollLeft)
    if (diff < minDiff) {
      minDiff = diff
      closestIdx = idx
    }
  })
  activeCategoryIndex.value = closestIdx
}

const scrollCategoryTo = (idx) => {
  const el = categoryScrollRef.value
  if (!el) return
  const child = el.children[idx]
  if (child) {
    if (idx === 0) {
      el.scrollTo({ left: 0, behavior: "smooth" })
    } else {
      const paddingLeft = parseFloat(getComputedStyle(el).paddingLeft) || 16
      const targetLeft = child.offsetLeft - paddingLeft
      el.scrollTo({ left: Math.max(0, targetLeft), behavior: "smooth" })
    }
    activeCategoryIndex.value = idx
  }
}

// ── Mobile Slider Navigation: Reviews ─────────────────
const testimonials = ref([])

const activeReviewIndex = ref(0)
const reviewScrollRef = ref(null)
const isReviewPaused = ref(false)
let reviewTimer = null
let reviewResumeTimeout = null

const pauseReview = () => {
  isReviewPaused.value = true
  if (reviewResumeTimeout) clearTimeout(reviewResumeTimeout)
}

const resumeReview = () => {
  if (reviewResumeTimeout) clearTimeout(reviewResumeTimeout)
  reviewResumeTimeout = setTimeout(() => {
    isReviewPaused.value = false
  }, 2500)
}

const onReviewScroll = (e) => {
  const el = e.target
  if (!el || !el.children.length) return
  const scrollLeft = el.scrollLeft
  let closestIdx = 0
  let minDiff = Infinity
  Array.from(el.children).forEach((child, idx) => {
    const targetLeft = idx === 0 ? 0 : child.offsetLeft - (parseFloat(getComputedStyle(el).paddingLeft) || 16)
    const diff = Math.abs(targetLeft - scrollLeft)
    if (diff < minDiff) {
      minDiff = diff
      closestIdx = idx
    }
  })
  activeReviewIndex.value = closestIdx
}

const scrollReviewTo = (idx) => {
  const el = reviewScrollRef.value
  if (!el) return
  const child = el.children[idx]
  if (child) {
    if (idx === 0) {
      el.scrollTo({ left: 0, behavior: "smooth" })
    } else {
      const paddingLeft = parseFloat(getComputedStyle(el).paddingLeft) || 16
      const targetLeft = child.offsetLeft - paddingLeft
      el.scrollTo({ left: Math.max(0, targetLeft), behavior: "smooth" })
    }
    activeReviewIndex.value = idx
  }
}

// ── Auto-slide Timer (every 2.5 - 3 seconds) ──────────
onMounted(() => {
  // Category slider auto-advance every 3s on small devices
  categoryTimer = setInterval(() => {
    if (!isCategoryPaused.value) {
      const el = categoryScrollRef.value
      if (el && el.scrollWidth > el.clientWidth) {
        const count = props.categories?.length || 0
        if (count > 1) {
          const nextIdx = (activeCategoryIndex.value + 1) % count
          scrollCategoryTo(nextIdx)
        }
      }
    }
  }, 3000)

  // Staggered Reviews slider auto-advance every 3s
  setTimeout(() => {
    reviewTimer = setInterval(() => {
      if (!isReviewPaused.value) {
        const el = reviewScrollRef.value
        if (el && el.scrollWidth > el.clientWidth) {
          const count = testimonials.length
          if (count > 1) {
            const nextIdx = (activeReviewIndex.value + 1) % count
            scrollReviewTo(nextIdx)
          }
        }
      }
    }, 3000)
  }, 1500)
})

onUnmounted(() => {
  if (categoryTimer) clearInterval(categoryTimer)
  if (reviewTimer) clearInterval(reviewTimer)
  if (categoryResumeTimeout) clearTimeout(categoryResumeTimeout)
  if (reviewResumeTimeout) clearTimeout(reviewResumeTimeout)
})
</script>

<template>
<Head>
  <title>{{ siteName }} — {{ siteTagline }}</title>
  <meta name="description" content="Discover exclusive luxury products crafted with passion, precision, and excellence." />
</Head>

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5] selection:bg-[#D4AF37]/30 selection:text-[#1A1A1A] dark:selection:text-white" style="font-family:'Instrument Sans',Inter,system-ui,sans-serif;">

  <!-- ══════════════════════════════════════════════════════
       HERO SECTION: ATMOSPHERIC BRAND CREST & EDITORIAL INTRO
  ══════════════════════════════════════════════════════ -->
  <section class="relative overflow-hidden hero-premium-bg hero-spotlight-bg border-b border-[#D4AF37]/15">
    <!-- Generated Store Hero Background Image with Atmospheric Overlays -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
      <img
        :src="'/images/hero-bg.jpg'"
        alt=""
        class="w-full h-full object-cover object-center scale-105 opacity-35 dark:opacity-25 transition-transform duration-1000"
      />
      <!-- Ambient dual-gradient overlays for readable typography in light & dark mode -->
      <div class="absolute inset-0 bg-gradient-to-r from-[#FAF7F2] via-[#FAF7F2]/85 to-transparent dark:from-[#0A0A0A] dark:via-[#0A0A0A]/90 dark:to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-[#FAF7F2] via-transparent to-[#FAF7F2]/60 dark:from-[#0A0A0A] dark:via-transparent dark:to-[#0A0A0A]/70"></div>
    </div>

    <div class="hero-noise" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-16 pb-16 sm:pb-20 lg:pb-24 relative z-10">
      <div :class="spotlightProduct ? 'grid items-center gap-10 md:gap-16 lg:grid-cols-2 lg:gap-20' : 'max-w-3xl mx-auto text-center flex flex-col items-center'" class="relative">

        <!-- Left: Content & Narrative -->
        <motion.div
          :variants="heroStagger"
          initial="hidden"
          animate="visible"
          :class="spotlightProduct ? 'space-y-4 md:space-y-5 lg:space-y-6' : 'space-y-4 md:space-y-5 lg:space-y-6 flex flex-col items-center text-center'"
        >
          <div class="space-y-3 md:space-y-4">
            <!-- Badge -->
            <motion.div :variants="heroItem" class="hero-badge">
              <span class="hero-badge-dot">
                <span class="hero-badge-dot-ring"></span>
                <span class="hero-badge-dot-core"></span>
              </span>
              Curated Collections • Smart Living
            </motion.div>

            <!-- Heading -->
            <motion.h1
              :variants="heroItem"
              class="text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl xl:text-6xl tracking-tight"
            >
              <span class="block text-[#1A1A1A] dark:text-[#F5F5F5]">Where Precision</span>
              <span class="inline-block mt-1 text-gold-gradient whitespace-nowrap">Meets Elegance</span>
            </motion.h1>

            <!-- Subtitle -->
            <motion.p
              :variants="heroItem"
              class="max-w-lg text-sm leading-relaxed sm:text-base lg:text-lg text-[#6B6B6B] dark:text-[#A0A0A0]"
            >
              Discover curated lifestyle pieces and premium essentials crafted with passion, precision, and excellence. Shop smart, live better with GharBazaar.
            </motion.p>
          </div>

          <!-- CTA Buttons -->
          <motion.div :variants="heroItem" class="flex flex-col gap-3 sm:flex-row sm:gap-4 pt-1">
            <Link href="/all-products" class="btn-premium group no-underline inline-flex items-center justify-center gap-2">
              <span>Explore Collection</span>
              <svg class="btn-arrow h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
            <Link href="/about" class="btn-glass group no-underline inline-flex items-center justify-center gap-2">
              <span>About Atelier</span>
              <svg class="btn-arrow h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
          </motion.div>

          <!-- Stats Cards -->
          <motion.div :variants="heroItem" class="grid grid-cols-3 gap-2 sm:gap-4 pt-2">
            <motion.div
              v-for="(stat, i) in [
                { value: stats?.products ? `${stats.products}+` : '0', label: 'Products' },
                { value: stats?.categories ? `${stats.categories}+` : '0', label: 'Categories' },
                { value: stats?.orders ? `${stats.orders}+` : '0', label: 'Orders' },
              ]"
              :key="stat.label"
              :variants="itemFadeUp(i * 0.1)"
              initial="hidden"
              animate="visible"
              class="hero-stat-card"
            >
              <div class="hero-stat-value">{{ stat.value }}</div>
              <div class="hero-stat-label">{{ stat.label }}</div>
            </motion.div>
          </motion.div>
        </motion.div>

        <!-- Right: Modern Spotlight Product Showcase Card -->
        <motion.div
          v-if="spotlightProduct"
          :initial="{ opacity: 0, scale: 0.94, y: 20 }"
          :animate="{ opacity: 1, scale: 1, y: 0 }"
          :transition="{ duration: 0.7, ease: m.easeOutExpo, delay: 0.2 }"
          class="relative flex justify-center lg:justify-end w-full"
        >
          <div class="relative w-full max-w-[360px] sm:max-w-[400px] lg:max-w-[420px]">

            <!-- Subtle ambient back shadow -->
            <div class="absolute -inset-1 rounded-[32px] bg-black/5 dark:bg-white/5 blur-lg pointer-events-none"></div>

            <!-- Spotlight Card -->
            <div class="relative rounded-3xl border border-gray-200/90 dark:border-white/10 bg-white/95 dark:bg-[#141414]/95 backdrop-blur-xl p-4 sm:p-5 shadow-2xl shadow-black/8 dark:shadow-black/50 transition-all duration-300">

              <!-- Top Status Pill & Category -->
              <div class="flex items-center justify-between gap-2 mb-3">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/70 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Curated Spotlight
                </div>
                <span v-if="spotlightProduct.category?.name" class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-white/5 px-2.5 py-1 rounded-full">
                  {{ spotlightProduct.category.name }}
                </span>
              </div>

              <!-- Product Image Frame -->
              <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-[#FAF7F2] dark:bg-[#1F1F1F] border border-gray-100 dark:border-white/5 group/showcase">
                <img
                  v-if="spotlightProduct.image"
                  :src="`/storage/${spotlightProduct.image}`"
                  :alt="spotlightProduct.name"
                  class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover/showcase:scale-105"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-[#D4AF37]">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                  </svg>
                </div>

                <!-- Floating Wishlist Button -->
                <button
                  type="button"
                  @click.prevent="toggleWishlist(spotlightProduct)"
                  :aria-label="isInWishlist(spotlightProduct) ? 'Remove from wishlist' : 'Add to wishlist'"
                  class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/90 dark:bg-[#1A1A1A]/90 backdrop-blur-md shadow-md border border-gray-200/70 dark:border-white/10 flex items-center justify-center transition-transform active:scale-90 hover:scale-110"
                >
                  <svg
                    class="w-4 h-4 transition-colors"
                    :class="isInWishlist(spotlightProduct) ? 'fill-red-500 text-red-500' : 'text-gray-600 dark:text-gray-300'"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                </button>

                <!-- Rating Pill Overlay -->
                <div class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/75 backdrop-blur-md text-white text-[11px] font-semibold">
                  <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                  <span>4.9</span>
                  <span class="text-white/60">·</span>
                  <span class="text-white/80 font-normal">Verified Quality</span>
                </div>
              </div>

              <!-- Product Info -->
              <div class="mt-3.5">
                <Link :href="`/products/${spotlightProduct.uid}`" class="block no-underline group/title">
                  <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white tracking-tight line-clamp-1 group-hover/title:text-[#D4AF37] transition-colors">
                    {{ spotlightProduct.name }}
                  </h3>
                </Link>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                  Handcrafted with precision • Limited GharBazaar edition
                </p>

                <!-- Pricing Lockup -->
                <div class="mt-3 flex items-baseline gap-2.5">
                  <span class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">
                    {{ formatPrice(spotlightProduct.price) }}
                  </span>
                  <span
                    v-if="spotlightProduct.compare_price && Number(spotlightProduct.compare_price) > Number(spotlightProduct.price)"
                    class="text-xs sm:text-sm text-gray-400 line-through"
                  >
                    {{ formatPrice(spotlightProduct.compare_price) }}
                  </span>
                  <span
                    v-else-if="comparePrice(spotlightProduct.price)"
                    class="text-xs sm:text-sm text-gray-400 line-through"
                  >
                    {{ formatPrice(comparePrice(spotlightProduct.price)) }}
                  </span>
                  <span class="ml-auto inline-flex items-center text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/40">
                    Save {{ spotlightProduct.compare_price && Number(spotlightProduct.compare_price) > Number(spotlightProduct.price) ? Math.round(((Number(spotlightProduct.compare_price) - Number(spotlightProduct.price)) / Number(spotlightProduct.compare_price)) * 100) : 20 }}%
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-4 flex items-center gap-2.5 pt-3 border-t border-gray-100 dark:border-white/5">
                <Link
                  :href="`/products/${spotlightProduct.uid}`"
                  class="flex-1 h-11 px-4 rounded-xl bg-[#D4AF37] hover:bg-[#B8941E] text-[#0A0A0A] text-xs sm:text-sm font-bold flex items-center justify-center gap-2 hover:brightness-105 transition-all no-underline shadow-md shadow-[#D4AF37]/20"
                >
                  <span>View Product</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <button
                  type="button"
                  @click="addToCart(spotlightProduct)"
                  :aria-label="isInCart(spotlightProduct) ? 'In Cart' : 'Add to cart'"
                  class="h-11 px-3.5 rounded-xl border border-gray-200 dark:border-white/10 hover:border-[#D4AF37] dark:hover:border-[#D4AF37] text-gray-700 dark:text-gray-200 hover:text-[#D4AF37] dark:hover:text-[#D4AF37] bg-gray-50/50 dark:bg-white/5 flex items-center justify-center gap-1.5 transition-all text-xs font-semibold cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                  </svg>
                  <span class="hidden sm:inline">{{ isInCart(spotlightProduct) ? 'In Cart' : 'Add' }}</span>
                </button>
              </div>

              <!-- Trust Guarantee Footer -->
              <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-white/5 grid grid-cols-2 gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>100% Authentic</span>
                </div>
                <div class="flex items-center gap-1.5 justify-end">
                  <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.5A2.25 2.25 0 0012 2.25H4.5A2.25 2.25 0 002.25 4.5v10.5"/></svg>
                  <span>Free Shipping</span>
                </div>
              </div>

            </div>

            <!-- Floating Micro-Badge Top-Left -->
            <div class="hidden sm:flex absolute -top-3.5 -left-4 items-center gap-2 px-3 py-1.5 rounded-full bg-white dark:bg-[#1A1A1A] border border-gray-200/80 dark:border-white/10 shadow-lg text-[11px] font-semibold text-gray-800 dark:text-gray-200 pointer-events-none">
              <span class="w-2 h-2 rounded-full bg-amber-400"></span>
              <span>⚡ Fast Dispatch</span>
            </div>

            <!-- Floating Micro-Badge Bottom-Right -->
            <div class="hidden sm:flex absolute -bottom-3.5 -right-4 items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-[#1A1A1A] border border-gray-200/80 dark:border-white/10 shadow-lg text-[11px] font-semibold text-gray-800 dark:text-gray-200 pointer-events-none">
              <span class="text-emerald-500 font-bold">✓</span>
              <span>30-Day Easy Returns</span>
            </div>

          </div>
        </motion.div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════════════════════════════
       BROWSE BY CATEGORY / CURATED DISCIPLINES
       (Slider on small devices, Grid on sm+)
  ══════════════════════════════════════════════════════ -->
  <section id="categories" class="py-14 sm:py-24 hero-gradient-static border-b border-gray-200/60 dark:border-[#D4AF37]/15 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Section Title Lockup -->
      <motion.div
        :variants="staggerContainer(0.12)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="text-center max-w-2xl mx-auto mb-8 sm:mb-16"
      >
        <motion.div :variants="staggerItem" class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#D4AF37] mb-2">
          Curated Disciplines
        </motion.div>
        <motion.h2 :variants="staggerItem" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
          Explore By Collection
        </motion.h2>
        <motion.p :variants="staggerItem" class="mt-3 text-sm sm:text-base text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
          Each collection represents a distinct discipline of design, noble materials, and artisanal execution.
        </motion.p>
      </motion.div>

      <!-- Category Slider Container (Mobile scroll-snap / Desktop Grid) -->
      <div v-if="categories?.length">
        <div
          ref="categoryScrollRef"
          @scroll.passive="onCategoryScroll"
          @touchstart.passive="pauseCategory"
          @touchend.passive="resumeCategory"
          @mouseenter="pauseCategory"
          @mouseleave="resumeCategory"
          @wheel.passive="pauseCategory(); resumeCategory()"
          :class="[
            'gap-5 sm:gap-8 scroll-smooth pb-4 sm:pb-0',
            categories.length === 1
              ? 'flex justify-center max-w-md mx-auto px-4 sm:px-0'
              : categories.length === 2
                ? 'flex sm:grid sm:grid-cols-2 sm:justify-center max-w-3xl mx-auto overflow-x-auto sm:overflow-visible snap-x snap-mandatory scroll-px-4 -mx-4 px-4 sm:mx-auto sm:px-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden'
                : 'flex sm:grid sm:grid-cols-2 lg:grid-cols-3 overflow-x-auto sm:overflow-visible snap-x snap-mandatory scroll-px-4 -mx-4 px-4 pb-4 sm:mx-0 sm:px-0 sm:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden'
          ]"
        >
          <div
            v-for="cat in categories"
            :key="cat.id"
            :class="[
              'group relative overflow-hidden rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#141414] shadow-sm hover:shadow-xl hover:border-[#D4AF37]/60 transition-all duration-500 shrink-0 snap-start',
              categories.length === 1
                ? 'w-full max-w-[380px]'
                : 'w-[80vw] max-w-[310px] sm:w-auto sm:max-w-none'
            ]"
          >
            <Link :href="`/categories/${cat.uid}`" class="block no-underline">
              <!-- Visual Container -->
              <div class="aspect-[16/10] relative overflow-hidden bg-neutral-100 dark:bg-neutral-900">
                <img
                  v-if="cat.image"
                  :src="`/storage/${cat.image}`"
                  :alt="cat.name"
                  loading="lazy"
                  class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                />
                <!-- Monogram Fallback -->
                <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[#1A1A1A] via-[#2B2413] to-[#0A0A0A]">
                  <span class="text-7xl font-black text-[#D4AF37]/20 select-none group-hover:scale-110 transition-transform duration-500">
                    {{ cat.name?.charAt(0)?.toUpperCase() }}
                  </span>
                </div>

                <!-- Top Pill Badge -->
                <div class="absolute top-3.5 right-3.5 z-10">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-[11px] font-semibold text-white/90">
                    {{ cat.products_count }} {{ cat.products_count === 1 ? 'Piece' : 'Pieces' }}
                  </span>
                </div>

                <!-- Bottom Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent flex flex-col justify-end p-5 sm:p-6 text-white">
                  <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white group-hover:text-[#FFE58F] transition-colors">
                    {{ cat.name }}
                  </h3>
                  <p v-if="cat.description" class="text-xs sm:text-sm text-white/75 mt-1.5 line-clamp-2 leading-relaxed">
                    {{ cat.description }}
                  </p>

                  <div class="mt-3.5 inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#D4AF37]">
                    <span>Discover Collection</span>
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                  </div>
                </div>
              </div>
            </Link>
          </div>
        </div>

        <!-- Slider Pagination Indicator & Controls for Mobile -->
        <div v-if="categories?.length > 1" class="flex sm:hidden items-center justify-center gap-3 mt-4">
          <button
            type="button"
            @click="scrollCategoryTo(Math.max(0, activeCategoryIndex - 1))"
            :disabled="activeCategoryIndex === 0"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Previous category"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
          </button>

          <div class="flex items-center gap-1.5">
            <button
              v-for="(cat, idx) in categories"
              :key="'cat-dot-' + cat.id"
              type="button"
              @click="scrollCategoryTo(idx)"
              :aria-label="`Slide to ${cat.name}`"
              :class="[
                'h-1.5 rounded-full transition-all duration-300 cursor-pointer',
                activeCategoryIndex === idx ? 'w-6 bg-[#D4AF37]' : 'w-1.5 bg-neutral-300 dark:bg-neutral-700'
              ]"
            />
          </div>

          <button
            type="button"
            @click="scrollCategoryTo(Math.min(categories.length - 1, activeCategoryIndex + 1))"
            :disabled="activeCategoryIndex === categories.length - 1"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Next category"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="rounded-2xl p-12 text-center border border-dashed border-gray-300 dark:border-[#D4AF37]/20 bg-white/50 dark:bg-[#141414]/50">
        <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Curated disciplines will be published shortly.</p>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════════════════════════════════
       CURATED SELECTION / TRENDING ACQUISITIONS
       (Background matches AppFooter: hero-gradient-static)
  ══════════════════════════════════════════════════════ -->
  <section id="products" class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-b border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Header Row with Quick Link -->
      <motion.div
        :variants="staggerContainer(0.12)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="mb-10 sm:mb-14 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end"
      >
        <div>
          <motion.div :variants="staggerItem" class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#D4AF37] mb-2">
            Curated Selection
          </motion.div>
          <motion.h2 :variants="staggerItem" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
            Trending Acquisitions
          </motion.h2>
          <motion.p :variants="staggerItem" class="mt-2 text-sm sm:text-base text-[#6B6B6B] dark:text-[#A0A0A0]">
            The most sought-after pieces from the current atelier archive.
          </motion.p>
        </div>

        <motion.div :variants="staggerItem">
          <Link
            href="/all-products"
            class="group inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-[#D4AF37]/30 bg-white/90 dark:bg-[#141414]/90 px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] backdrop-blur-sm transition-all duration-300 hover:border-[#D4AF37] hover:bg-[#D4AF37]/10 hover:text-[#B8941E] dark:hover:text-[#D4AF37] shadow-sm no-underline"
          >
            <span>View Complete Vault</span>
            <svg class="h-4 w-4 text-[#D4AF37] transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
          </Link>
        </motion.div>
      </motion.div>

      <!-- Products Grid -->
      <motion.div
        v-if="trendingProducts?.length"
        :variants="staggerContainer(0.06)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.onceLight"
        class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6"
      >
        <motion.div
          v-for="product in trendingProducts"
          :key="product.id"
          :variants="staggerItem"
        >
          <ProductCard
            :product="product"
            :is-in-wishlist="isInWishlist(product)"
            :is-in-cart="isInCart(product)"
            @add-to-cart="addToCart(product)"
            @toggle-wishlist="toggleWishlist(product)"
          />
        </motion.div>
      </motion.div>

      <!-- Empty State -->
      <div v-else class="rounded-2xl p-12 text-center border border-dashed border-gray-300 dark:border-[#D4AF37]/20 bg-white/50 dark:bg-[#141414]/50">
        <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Featured pieces will appear here once ready for acquisition.</p>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════════════════════════════════
       PILLARS OF ARTISTRY & HERITAGE
  ══════════════════════════════════════════════════════ -->
  <section class="py-14 sm:py-24 hero-gradient-static border-b border-gray-200/60 dark:border-[#D4AF37]/15">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Small Section Kicker -->
      <div class="text-center max-w-xl mx-auto mb-12 sm:mb-16">
        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#D4AF37] mb-2">
          The Atelier Standard
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
          Pillars of Exceptional Craftsmanship
        </h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">

        <!-- Pillar 1 -->
        <div class="group relative rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-[#161616]/90 backdrop-blur-xs p-6 sm:p-7 shadow-xs hover:border-[#D4AF37]/60 hover:shadow-xl hover:shadow-[#D4AF37]/5 hover:-translate-y-1 transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-2xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 border border-[#D4AF37]/25 text-[#B8941E] dark:text-[#D4AF37] group-hover:border-[#D4AF37] group-hover:bg-[#D4AF37]/20 dark:group-hover:bg-[#D4AF37]/25 group-hover:text-[#9A7B1C] dark:group-hover:text-[#FFE082] group-hover:shadow-[0_4px_16px_rgba(212,175,55,0.25)] group-hover:scale-105 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-2">
            Master Craftsmanship
          </h3>
          <p class="text-xs sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
            Every creation is sculpted, assembled, and finished by master artisans with micrometer precision.
          </p>
        </div>

        <!-- Pillar 2 -->
        <div class="group relative rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-[#161616]/90 backdrop-blur-xs p-6 sm:p-7 shadow-xs hover:border-[#D4AF37]/60 hover:shadow-xl hover:shadow-[#D4AF37]/5 hover:-translate-y-1 transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-2xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 border border-[#D4AF37]/25 text-[#B8941E] dark:text-[#D4AF37] group-hover:border-[#D4AF37] group-hover:bg-[#D4AF37]/20 dark:group-hover:bg-[#D4AF37]/25 group-hover:text-[#9A7B1C] dark:group-hover:text-[#FFE082] group-hover:shadow-[0_4px_16px_rgba(212,175,55,0.25)] group-hover:scale-105 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-2">
            Certified Authenticity
          </h3>
          <p class="text-xs sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
            Each individual piece is cataloged with a unique serial identification and atelier certificate.
          </p>
        </div>

        <!-- Pillar 3 -->
        <div class="group relative rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-[#161616]/90 backdrop-blur-xs p-6 sm:p-7 shadow-xs hover:border-[#D4AF37]/60 hover:shadow-xl hover:shadow-[#D4AF37]/5 hover:-translate-y-1 transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-2xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 border border-[#D4AF37]/25 text-[#B8941E] dark:text-[#D4AF37] group-hover:border-[#D4AF37] group-hover:bg-[#D4AF37]/20 dark:group-hover:bg-[#D4AF37]/25 group-hover:text-[#9A7B1C] dark:group-hover:text-[#FFE082] group-hover:shadow-[0_4px_16px_rgba(212,175,55,0.25)] group-hover:scale-105 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-2">
            White-Glove Presentation
          </h3>
          <p class="text-xs sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
            Delivered in a handcrafted presentation case, velvet travel pouch, and wax-sealed provenance.
          </p>
        </div>

        <!-- Pillar 4 -->
        <div class="group relative rounded-2xl border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-[#161616]/90 backdrop-blur-xs p-6 sm:p-7 shadow-xs hover:border-[#D4AF37]/60 hover:shadow-xl hover:shadow-[#D4AF37]/5 hover:-translate-y-1 transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-2xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 border border-[#D4AF37]/25 text-[#B8941E] dark:text-[#D4AF37] group-hover:border-[#D4AF37] group-hover:bg-[#D4AF37]/20 dark:group-hover:bg-[#D4AF37]/25 group-hover:text-[#9A7B1C] dark:group-hover:text-[#FFE082] group-hover:shadow-[0_4px_16px_rgba(212,175,55,0.25)] group-hover:scale-105 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.125A1.125 1.125 0 0013.125 3H4.875A1.125 1.125 0 003.75 4.125v10.125c0 .621.504 1.125 1.125 1.125h9.75" />
            </svg>
          </div>
          <h3 class="text-base font-bold text-[#1A1A1A] dark:text-[#F5F5F5] mb-2">
            Insured Express Courier
          </h3>
          <p class="text-xs sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
            Directly dispatched with comprehensive transit insurance, real-time tracking, and adult signature.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════════════════════════════
       VERIFIED EXPERIENCES (COLLECTOR ACCLAIM)
       (Slider on small devices, Grid on md+)
  ══════════════════════════════════════════════════════ -->
  <section v-if="testimonials && testimonials.length" class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-b border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Header -->
      <motion.div
        :variants="staggerContainer(0.12)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="text-center max-w-2xl mx-auto mb-8 sm:mb-16"
      >
        <motion.div :variants="staggerItem" class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#D4AF37] mb-2">
          Collector Acclaim
        </motion.div>
        <motion.h2 :variants="staggerItem" class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
          Verified Experiences
        </motion.h2>
        <motion.p :variants="staggerItem" class="mt-2 text-sm sm:text-base text-[#6B6B6B] dark:text-[#A0A0A0]">
          Reflections from connoisseurs who have welcomed our creations into their permanent collections.
        </motion.p>
      </motion.div>

      <!-- Review Slider Container (Mobile scroll-snap / Desktop Grid) -->
      <div>
        <div
          ref="reviewScrollRef"
          @scroll.passive="onReviewScroll"
          @touchstart.passive="pauseReview"
          @touchend.passive="resumeReview"
          @mouseenter="pauseReview"
          @mouseleave="resumeReview"
          @wheel.passive="pauseReview(); resumeReview()"
          class="flex md:grid md:grid-cols-3 gap-5 md:gap-6 overflow-x-auto md:overflow-visible snap-x snap-mandatory scroll-px-4 -mx-4 px-4 pb-4 md:mx-0 md:px-0 md:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden scroll-smooth"
        >
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.author"
            class="rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white/90 dark:bg-[#1A1A1A]/90 backdrop-blur-sm p-6 sm:p-7 shadow-md hover:border-[#D4AF37]/50 hover:shadow-lg transition-all duration-300 flex flex-col justify-between w-[80vw] max-w-[310px] shrink-0 snap-start md:w-auto md:max-w-none"
          >
            <div>
              <div class="mb-4 flex items-center space-x-1">
                <svg v-for="i in 5" :key="i" class="w-4 h-4 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
                  <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                </svg>
              </div>
              <p class="text-sm sm:text-base italic text-[#1A1A1A] dark:text-[#EDEDED] leading-relaxed mb-6">
                "{{ testimonial.quote }}"
              </p>
            </div>
            <div class="border-t border-gray-100 dark:border-white/10 pt-4 flex items-center justify-between">
              <div>
                <div class="font-bold text-sm text-[#1A1A1A] dark:text-[#F5F5F5]">{{ testimonial.author }}</div>
                <div class="text-xs text-[#6B6B6B] dark:text-[#A0A0A0]">{{ testimonial.city }}</div>
              </div>
              <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Verified Buyer
              </span>
            </div>
          </div>
        </div>

        <!-- Slider Pagination Indicator & Controls for Mobile -->
        <div class="flex md:hidden items-center justify-center gap-3 mt-4">
          <button
            type="button"
            @click="scrollReviewTo(Math.max(0, activeReviewIndex - 1))"
            :disabled="activeReviewIndex === 0"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Previous review"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
          </button>

          <div class="flex items-center gap-1.5">
            <button
              v-for="(_, idx) in testimonials"
              :key="'review-dot-' + idx"
              type="button"
              @click="scrollReviewTo(idx)"
              :aria-label="`Slide to review ${idx + 1}`"
              :class="[
                'h-1.5 rounded-full transition-all duration-300 cursor-pointer',
                activeReviewIndex === idx ? 'w-6 bg-[#D4AF37]' : 'w-1.5 bg-neutral-300 dark:bg-neutral-700'
              ]"
            />
          </div>

          <button
            type="button"
            @click="scrollReviewTo(Math.min(testimonials.length - 1, activeReviewIndex + 1))"
            :disabled="activeReviewIndex === testimonials.length - 1"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Next review"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
          </button>
        </div>
      </div>

    </div>
  </section>

  <!-- Global Footer -->
  <AppFooter />

</div>
</template>

<style>
html {
  scroll-behavior: smooth;
  scroll-padding-top: 5rem;
}

.dark .elegance-text {
  background: linear-gradient(135deg, var(--gold), var(--gold-light)) !important;
  -webkit-background-clip: text !important;
  background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
}
</style>

<style scoped>
.hero-spotlight-bg {
  background:
    radial-gradient(ellipse 80% 70% at 78% 32%, rgba(212, 175, 55, 0.08), transparent 65%),
    radial-gradient(ellipse 55% 55% at 8% 88%, rgba(212, 175, 55, 0.05), transparent 65%),
    linear-gradient(135deg, #FAF7F2 0%, #F8F5EE 50%, #FAF7F2 100%);
}

.dark .hero-spotlight-bg {
  background:
    radial-gradient(ellipse 80% 70% at 78% 30%, rgba(212, 175, 55, 0.12), transparent 65%),
    radial-gradient(ellipse 55% 55% at 12% 86%, rgba(212, 175, 55, 0.06), transparent 65%),
    linear-gradient(135deg, #0A0A0A 0%, #121212 50%, #0A0A0A 100%);
}
</style>
