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
const siteName = computed(() => site.value.site_name || "Brilliant")
const siteTagline = computed(() => site.value.tagline || "Premium Store")
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
  const elCenter = el.getBoundingClientRect().left + el.clientWidth / 2
  let closestIdx = 0
  let minDiff = Infinity
  Array.from(el.children).forEach((child, idx) => {
    const rect = child.getBoundingClientRect()
    const childCenter = rect.left + rect.width / 2
    const diff = Math.abs(childCenter - elCenter)
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
    const elRect = el.getBoundingClientRect()
    const childRect = child.getBoundingClientRect()
    const targetLeft = (childRect.left - elRect.left + el.scrollLeft) - (el.clientWidth - child.offsetWidth) / 2
    el.scrollTo({ left: Math.max(0, targetLeft), behavior: "smooth" })
    activeCategoryIndex.value = idx
  }
}

// ── Mobile Slider Navigation: Reviews ─────────────────
const testimonials = [
  {
    quote: "The hand-finishing, weight, and precision exceeded expectations. The presentation box alone signals that you are holding an heirloom.",
    author: "Sarah J.",
    city: "London, UK",
  },
  {
    quote: "Commissioning a bespoke creation was a seamless atelier experience. The artisans accommodated every custom detail with genuine care.",
    author: "Michael C.",
    city: "Geneva, Switzerland",
  },
  {
    quote: "The design direction feels cohesive and distinct. The fast dispatch and insured white-glove arrival made it an effortless experience.",
    author: "Emma W.",
    city: "New York, USA",
  },
]

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
  const elCenter = el.getBoundingClientRect().left + el.clientWidth / 2
  let closestIdx = 0
  let minDiff = Infinity
  Array.from(el.children).forEach((child, idx) => {
    const rect = child.getBoundingClientRect()
    const childCenter = rect.left + rect.width / 2
    const diff = Math.abs(childCenter - elCenter)
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
    const elRect = el.getBoundingClientRect()
    const childRect = child.getBoundingClientRect()
    const targetLeft = (childRect.left - elRect.left + el.scrollLeft) - (el.clientWidth - child.offsetWidth) / 2
    el.scrollTo({ left: Math.max(0, targetLeft), behavior: "smooth" })
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
    <div class="hero-noise" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-16 pb-16 sm:pb-20 lg:pb-24 relative z-10">
      <div class="grid items-center gap-10 md:gap-16 lg:grid-cols-2 lg:gap-20">

        <!-- Left: Content & Narrative -->
        <motion.div
          :variants="heroStagger"
          initial="hidden"
          animate="visible"
          class="space-y-4 md:space-y-5 lg:space-y-6"
        >
          <div class="space-y-3 md:space-y-4">
            <!-- Badge -->
            <motion.div :variants="heroItem" class="hero-badge">
              <span class="hero-badge-dot">
                <span class="hero-badge-dot-ring"></span>
                <span class="hero-badge-dot-core"></span>
              </span>
              Haute Horology & Fine Art
            </motion.div>

            <!-- Heading -->
            <motion.h1
              :variants="heroItem"
              class="text-3xl font-extrabold leading-tight sm:text-4xl lg:text-5xl xl:text-6xl tracking-tight"
            >
              <span class="text-[#1A1A1A] dark:text-[#F5F5F5]">Where Precision Meets</span>
              <span class="block mt-1 text-gold-gradient">Elegance</span>
            </motion.h1>

            <!-- Subtitle -->
            <motion.p
              :variants="heroItem"
              class="max-w-lg text-sm leading-relaxed sm:text-base lg:text-lg text-[#6B6B6B] dark:text-[#A0A0A0]"
            >
              Discover exclusive luxury products crafted with passion, precision, and excellence. Every collection is carefully curated to feel timeless.
            </motion.p>
          </div>

          <!-- CTA Buttons -->
          <motion.div :variants="heroItem" class="flex flex-col gap-3 sm:flex-row sm:gap-4 pt-1">
            <Link href="/all-products" class="btn-premium group no-underline inline-flex items-center justify-center gap-2">
              <span>Explore Collection</span>
              <svg class="btn-arrow h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
            <Link href="/custom-order" class="btn-glass group no-underline inline-flex items-center justify-center gap-2">
              <span>Custom Order</span>
              <svg class="btn-arrow h-4 w-4 transition-transform duration-300 group-hover:rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
          </motion.div>

          <!-- Stats Cards -->
          <motion.div :variants="heroItem" class="hidden md:grid md:grid-cols-3 gap-4 pt-2">
            <motion.div
              v-for="(stat, i) in [
                { value: (stats?.products || 50) + '+', label: 'Products' },
                { value: (stats?.categories || 10) + '+', label: 'Categories' },
                { value: (stats?.orders || 100) + '+', label: 'Orders' },
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

        <!-- Right: Glowing Brand Crest Emblem & Masterpiece Card -->
        <motion.div
          :initial="{ opacity: 0, scale: 0.9, y: 24 }"
          :animate="{ opacity: 1, scale: 1, y: 0 }"
          :transition="{ duration: 0.8, ease: m.easeOutExpo, delay: 0.25 }"
          class="relative flex justify-center lg:justify-end"
        >
          <div class="hero-image-group relative max-w-full overflow-hidden sm:overflow-visible">

            <!-- Breathing glow aura -->
            <div class="hero-glow"></div>

            <!-- Brand emblem card -->
            <div class="hero-emblem">
              <div class="hero-emblem-card">
                <div class="relative">

                  <!-- Diamond icon -->
                  <svg class="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 text-[#D4AF37] mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M6 3h12l4 6-10 13L2 9z"/>
                    <path d="M2 9h20"/>
                    <path d="M12 22L6 9"/>
                    <path d="M12 22l6-13"/>
                  </svg>

                  <!-- Twinkling sparkles -->
                  <div class="hero-sparkle" style="top:-10px; right:-12px; animation-delay:1.2s;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
                    </svg>
                  </div>
                  <div class="hero-sparkle" style="bottom:-6px; left:-14px; animation-delay:2.1s;">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
                    </svg>
                  </div>
                  <div class="hero-sparkle" style="top: calc(50% - 4px); right: -22px; animation-delay:2.8s;">
                    <span class="block w-2 h-2 bg-[#D4AF37] rounded-full"></span>
                  </div>
                  <div class="hero-sparkle" style="top: calc(50% - 4px); left: -22px; animation-delay:2.8s;">
                    <span class="block w-2 h-2 bg-[#D4AF37] rounded-full"></span>
                  </div>
                </div>

                <!-- Wordmark -->
                <div class="mt-5 text-center">
                  <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ siteName }}</h2>
                  <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="w-8 h-px bg-[#D4AF37]"></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#D4AF37]">{{ siteTagline }}</span>
                    <span class="w-8 h-px bg-[#D4AF37]"></span>
                  </div>
                </div>

                <!-- Latest Creation Direct Link -->
                <div v-if="latestProduct" class="mt-5 pt-4 border-t border-[#D4AF37]/20 text-center">
                  <Link
                    :href="`/products/${latestProduct.uid}`"
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#D4AF37]/15 border border-[#D4AF37]/30 text-[11px] font-semibold text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0A0A0A] transition-all duration-300 no-underline"
                  >
                    <span>Latest: {{ latestProduct.name }}</span>
                    <span class="font-bold">({{ formatPrice(latestProduct.price) }})</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                  </Link>
                </div>

                <!-- Gradient border -->
                <div class="absolute -inset-px rounded-[28px] border border-transparent bg-gradient-to-br from-[#D4AF37]/40 via-transparent to-[#D4AF37]/40 pointer-events-none"></div>
              </div>
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
  <section id="categories" class="py-14 sm:py-24 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-b border-gray-200/60 dark:border-[#D4AF37]/15 overflow-hidden">
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
          :class="[
            'gap-5 sm:gap-8 scroll-smooth pb-4 sm:pb-0',
            categories.length === 1
              ? 'flex justify-center max-w-md mx-auto px-4 sm:px-0'
              : categories.length === 2
                ? 'flex sm:grid sm:grid-cols-2 justify-center max-w-3xl mx-auto overflow-x-auto sm:overflow-visible snap-x snap-mandatory -mx-4 px-4 sm:mx-auto sm:px-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden'
                : 'flex sm:grid sm:grid-cols-2 lg:grid-cols-3 overflow-x-auto sm:overflow-visible snap-x snap-mandatory -mx-4 px-4 pb-4 sm:mx-0 sm:px-0 sm:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden'
          ]"
        >
          <div
            v-for="cat in categories"
            :key="cat.id"
            :class="[
              'group relative overflow-hidden rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#141414] shadow-sm hover:shadow-xl hover:border-[#D4AF37]/60 transition-all duration-500 shrink-0 snap-center',
              categories.length === 1
                ? 'w-full max-w-[380px]'
                : 'w-[78vw] max-w-[300px] sm:w-auto sm:max-w-none'
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
                  <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white group-hover:text-[#D4AF37] transition-colors">
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

        <!-- Slider Pagination Indicator for Mobile -->
        <div v-if="categories?.length > 1" class="flex sm:hidden items-center justify-center gap-1.5 mt-4">
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
            class="group inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-[#D4AF37]/30 bg-white/80 dark:bg-[#141414]/80 px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] backdrop-blur-sm transition-all duration-300 hover:border-[#D4AF37] hover:bg-[#D4AF37]/10 hover:text-[#D4AF37] dark:hover:text-[#D4AF37] shadow-sm no-underline"
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
        class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"
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
  <section class="py-14 sm:py-24 bg-white/60 dark:bg-[#111111]/60 border-b border-gray-200/60 dark:border-[#D4AF37]/15">
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
        <div class="group relative rounded-2xl border border-gray-200/70 dark:border-white/5 bg-white dark:bg-[#161616] p-6 sm:p-7 shadow-sm hover:border-[#D4AF37]/40 hover:shadow-lg transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 text-[#D4AF37] group-hover:bg-[#D4AF37] group-hover:text-white transition-colors duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
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
        <div class="group relative rounded-2xl border border-gray-200/70 dark:border-white/5 bg-white dark:bg-[#161616] p-6 sm:p-7 shadow-sm hover:border-[#D4AF37]/40 hover:shadow-lg transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 text-[#D4AF37] group-hover:bg-[#D4AF37] group-hover:text-white transition-colors duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
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
        <div class="group relative rounded-2xl border border-gray-200/70 dark:border-white/5 bg-white dark:bg-[#161616] p-6 sm:p-7 shadow-sm hover:border-[#D4AF37]/40 hover:shadow-lg transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 text-[#D4AF37] group-hover:bg-[#D4AF37] group-hover:text-white transition-colors duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
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
        <div class="group relative rounded-2xl border border-gray-200/70 dark:border-white/5 bg-white dark:bg-[#161616] p-6 sm:p-7 shadow-sm hover:border-[#D4AF37]/40 hover:shadow-lg transition-all duration-300">
          <div class="mb-5 flex h-13 w-13 items-center justify-center rounded-xl bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 text-[#D4AF37] group-hover:bg-[#D4AF37] group-hover:text-white transition-colors duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
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
  <section class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-b border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl overflow-hidden">
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
          class="flex md:grid md:grid-cols-3 gap-5 md:gap-6 overflow-x-auto md:overflow-visible snap-x snap-mandatory -mx-4 px-4 pb-4 md:mx-0 md:px-0 md:pb-0 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden scroll-smooth"
        >
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.author"
            class="rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white/90 dark:bg-[#1A1A1A]/90 backdrop-blur-sm p-6 sm:p-7 shadow-md hover:border-[#D4AF37]/50 hover:shadow-lg transition-all duration-300 flex flex-col justify-between w-[82vw] max-w-[320px] shrink-0 snap-center md:w-auto md:max-w-none"
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

        <!-- Slider Pagination Indicator for Mobile -->
        <div class="flex md:hidden items-center justify-center gap-1.5 mt-4">
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
      </div>

    </div>
  </section>

  <!-- ══════════════════════════════════════════════════════
       BESPOKE COMMISSIONS / CUSTOM INVITATION BANNER
  ══════════════════════════════════════════════════════ -->
  <section id="custom-order" class="py-16 sm:py-24 bg-[#FAF7F2] dark:bg-[#0A0A0A]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <motion.div
        :variants="m.scaleIn"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="relative overflow-hidden rounded-3xl border border-[#D4AF37]/35 bg-gradient-to-br from-[#1C1814] via-[#121110] to-[#0A0A0A] p-8 sm:p-14 text-center text-white shadow-2xl shadow-black/40"
      >
        <!-- Ambient Gold Flare -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#D4AF37]/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-[#D4AF37]/30 bg-[#D4AF37]/10 backdrop-blur-md mb-5">
            <span class="w-1.5 h-1.5 rounded-full bg-[#D4AF37]"></span>
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.2em] text-[#D4AF37]">
              Atelier Commissions
            </span>
          </div>

          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white mb-4">
            Desire a One-of-a-Kind Creation?
          </h2>

          <p class="text-sm sm:text-base text-white/75 leading-relaxed mb-8 max-w-xl mx-auto">
            Collaborate directly with our master craftsmen to create a bespoke timepiece, sculpted artwork, or customized collectible tailored to your exact aesthetic.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 sm:gap-4">
            <Link
              href="/custom-order"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-[#D4AF37] text-[#0A0A0A] font-bold text-sm sm:text-base hover:bg-[#C9A032] shadow-xl shadow-[#D4AF37]/25 transition-all duration-300 no-underline"
            >
              <span>Begin Bespoke Inquiry</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </Link>

            <Link
              href="/all-products"
              class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl border border-white/20 bg-white/5 text-white font-semibold text-sm sm:text-base backdrop-blur-md hover:border-[#D4AF37] hover:bg-white/10 transition-all duration-300 no-underline"
            >
              <span>Browse Catalog Vault</span>
            </Link>
          </div>
        </div>
      </motion.div>
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
    radial-gradient(ellipse 80% 70% at 78% 32%, color-mix(in srgb, var(--gold) 20%, transparent), transparent 62%),
    radial-gradient(ellipse 55% 55% at 8% 88%, color-mix(in srgb, var(--gold) 10%, transparent), transparent 65%),
    linear-gradient(135deg, #FAF7F2 0%, #F5F1E8 45%, #FFFFFF 70%, #FAF7F2 100%);
}

.dark .hero-spotlight-bg {
  background:
    radial-gradient(ellipse 80% 70% at 78% 30%, color-mix(in srgb, var(--gold) 16%, transparent), transparent 62%),
    radial-gradient(ellipse 55% 55% at 12% 86%, color-mix(in srgb, var(--gold) 8%, transparent), transparent 65%),
    linear-gradient(135deg, #0A0A0A 0%, #141414 45%, #1A1A1A 70%, #0A0A0A 100%);
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.25; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.05); }
}

.hero-glow {
  position: absolute;
  width: 280px;
  height: 280px;
  border-radius: 9999px;
  background: radial-gradient(circle, color-mix(in srgb, var(--gold) 28%, transparent), transparent 65%);
  filter: blur(30px);
  animation: glow-pulse 6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
  pointer-events: none;
}

.hero-emblem {
  position: relative;
  animation: emblem-float 6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes emblem-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}

.hero-emblem-card {
  position: relative;
  background: #141414;
  border: 1px solid color-mix(in srgb, var(--gold) 35%, transparent);
  border-radius: 28px;
  padding: 1.75rem 1.5rem;
  max-width: 100%;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  animation: emblem-breathe 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@media (min-width: 640px) {
  .hero-emblem-card {
    padding: 2.5rem 2.75rem;
  }
}

@keyframes emblem-breathe {
  0%, 100% { box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 0 30px color-mix(in srgb, var(--gold) 8%, transparent); }
  50% { box-shadow: 0 28px 70px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 0 50px color-mix(in srgb, var(--gold) 22%, transparent); }
}

.hero-sparkle {
  position: absolute;
  color: var(--gold);
  animation: sparkle-twinkle 3s ease-in-out infinite;
}

@keyframes sparkle-twinkle {
  0%, 100% { opacity: 0.2; transform: scale(0.6); }
  50% { opacity: 1; transform: scale(1); }
}

@media (prefers-reduced-motion: reduce) {
  .hero-glow,
  .hero-emblem,
  .hero-emblem-card,
  .hero-sparkle {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    opacity: 1 !important;
  }
}
</style>
