<script setup>
import { router, usePage, Head, Link, useForm } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import { useAuthModal } from "../composables/useAuthModal.js"
import { useNotification } from "../composables/useNotification.js"
import { motion, AnimatePresence } from "motion-v"

const props = defineProps({
  product: { type: Object, required: true },
  cartProductIds: { type: Array, default: () => [] },
  wishlistProductIds: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  userReview: { type: Object, default: null },
})

const { openLogin } = useAuthModal()
const { success, info } = useNotification()
const page = usePage()

const authUser = computed(() => page.props.auth?.user)
const isInCart = computed(() => props.cartProductIds?.includes(props.product.id) || false)
const isInWishlist = computed(() => props.wishlistProductIds?.includes(props.product.id) || false)

const averageRating = computed(() => {
  if (!props.reviews?.length) return null
  const sum = props.reviews.reduce((acc, r) => acc + r.rating, 0)
  return (sum / props.reviews.length).toFixed(1)
})

const ratingCounts = computed(() => {
  const counts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
  if (!props.reviews?.length) return counts
  props.reviews.forEach((r) => {
    if (counts[r.rating] !== undefined) counts[r.rating]++
  })
  return counts
})

// Accordion states
const activeAccordion = ref("details") // 'details' | 'shipping' | 'authenticity'

const toggleAccordion = (name) => {
  activeAccordion.value = activeAccordion.value === name ? null : name
}

// Review form state
const showReviewForm = ref(false)
const editingReview = ref(null)

const reviewForm = useForm({
  product_id: props.product.id,
  rating: 5,
  comment: "",
})

const editForm = useForm({
  rating: 5,
  comment: "",
})

function submitReview() {
  reviewForm.post("/reviews", {
    preserveScroll: true,
    onSuccess: () => {
      reviewForm.reset("rating", "comment")
      reviewForm.rating = 5
      showReviewForm.value = false
      success({
        title: "Review Published",
        message: "Thank you for sharing your experience! Your review is now live.",
      })
    },
  })
}

function startEditing(review) {
  editingReview.value = review.id
  editForm.rating = review.rating
  editForm.comment = review.comment || ""
}

function cancelEditing() {
  editingReview.value = null
}

function saveEdit(review) {
  editForm.put(`/reviews/${review.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      editingReview.value = null
      success({
        title: "Review Updated",
        message: "Your review changes have been saved.",
      })
    },
  })
}

function deleteReview(review) {
  if (!confirm("Are you sure you want to remove your review?")) return
  router.delete(`/reviews/${review.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      info({
        title: "Review Removed",
        message: "Your review has been removed.",
      })
    },
  })
}

function setRating(form, value) {
  form.rating = value
}

const addToCart = () => {
  if (!authUser.value) {
    openLogin()
    return
  }

  router.post("/cart", {
    product_id: props.product.id,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      success({
        title: "Added to Bag",
        message: `${props.product.name} is now in your shopping bag.`,
        action: {
          label: "View Bag",
          onClick: () => router.visit("/cart"),
        },
      })
    },
  })
}

const toggleWishlist = () => {
  if (!authUser.value) {
    openLogin()
    return
  }

  const wasInWishlist = isInWishlist.value
  router.post("/wishlist", {
    product_id: props.product.id,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      if (wasInWishlist) {
        info({
          title: "Removed from Wishlist",
          message: `${props.product.name} was removed from your favorites.`,
        })
      } else {
        success({
          title: "Saved to Wishlist",
          message: `${props.product.name} was added to your favorites.`,
        })
      }
    },
  })
}

const imgLoaded = ref(false)
const imgError = ref(false)

function onImgLoad() {
  imgLoaded.value = true
}

function onImgError() {
  imgError.value = true
  imgLoaded.value = true
}

const scrollToReviews = () => {
  const el = document.getElementById("reviews-section")
  if (el) {
    el.scrollIntoView({ behavior: "smooth" })
  }
}
</script>

<template>
  <div class="min-h-screen bg-[var(--background)] text-neutral-900 dark:text-neutral-100 pb-24 selection:bg-[#D4AF37]/30">
    <Head :title="`${product.name} — Luxury Collection`" />

    <!-- TOP NAVIGATION BAR -->
    <div class="border-b border-neutral-200/80 dark:border-white/10 bg-white/80 dark:bg-[#1A1A1A]/80 backdrop-blur-md sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">

        <!-- Breadcrumbs & Back -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-neutral-500 dark:text-neutral-400 overflow-hidden text-ellipsis whitespace-nowrap">
          <Link href="/" class="hover:text-[#D4AF37] transition-colors no-underline">Home</Link>
          <span class="text-neutral-300 dark:text-neutral-600">/</span>
          <Link
            v-if="product.category"
            :href="`/categories/${product.category.uid}`"
            class="hover:text-[#D4AF37] transition-colors no-underline"
          >
            {{ product.category.name }}
          </Link>
          <span v-if="product.category" class="text-neutral-300 dark:text-neutral-600">/</span>
          <span class="text-neutral-900 dark:text-white font-bold truncate max-w-[140px] sm:max-w-xs">{{ product.name }}</span>
        </nav>

        <!-- Right Quick Actions: Wishlist + Cart -->
        <div class="flex items-center gap-3">
          <button
            @click="toggleWishlist"
            :class="[
              'w-9 h-9 rounded-xl flex items-center justify-center border transition-all cursor-pointer',
              isInWishlist
                ? 'border-red-300 bg-red-50 dark:bg-red-950/30 text-red-500 shadow-xs'
                : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'
            ]"
            :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"
          >
            <svg class="w-4 h-4" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
            </svg>
          </button>

          <Link
            href="/cart"
            class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-neutral-900 dark:text-white border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 hover:border-[#D4AF37]/50 transition-all no-underline"
          >
            <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
            <span class="hidden sm:inline">Cart</span>
            <span
              v-if="$page.props.cartCount"
              class="w-5 h-5 rounded-full bg-[#D4AF37] text-neutral-950 text-[10px] flex items-center justify-center font-bold"
            >
              {{ $page.props.cartCount }}
            </span>
          </Link>
        </div>

      </div>
    </div>

    <!-- MAIN PRODUCT SHOWCASE CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 sm:pt-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-start">

        <!-- LEFT COLUMN: PRODUCT GALLERY (STICKY) -->
        <div class="lg:col-span-7 lg:sticky lg:top-24">
          <motion.div
            initial="{ opacity: 0, scale: 0.98 }"
            animate="{ opacity: 1, scale: 1 }"
            :transition="{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }"
            class="relative rounded-3xl overflow-hidden border border-neutral-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-xl group"
          >
            <!-- Ambient Backdrop Glow -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-[#D4AF37]/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-[#D4AF37]/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Corner Badges -->
            <div class="absolute top-4 left-4 z-20 flex flex-col gap-2">
              <span
                v-if="product.category"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-white/90 dark:bg-[#1A1A1A]/90 text-[#D4AF37] border border-[#D4AF37]/30 backdrop-blur-md shadow-xs"
              >
                {{ product.category.name }}
              </span>
              <span
                v-if="product.is_trending"
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-[#D4AF37] text-neutral-950 shadow-md shadow-[#D4AF37]/20"
              >
                ✦ Trending Piece
              </span>
            </div>

            <!-- Top Right Wishlist Button -->
            <button
              @click="toggleWishlist"
              :class="[
                'absolute top-4 right-4 z-20 w-10 h-10 rounded-full flex items-center justify-center border shadow-md backdrop-blur-md transition-all cursor-pointer',
                isInWishlist
                  ? 'bg-red-50 dark:bg-red-950/80 border-red-300 text-red-500 scale-105'
                  : 'bg-white/90 dark:bg-[#1A1A1A]/90 border-neutral-200 dark:border-white/10 text-neutral-600 dark:text-neutral-300 hover:border-[#D4AF37] hover:text-[#D4AF37]'
              ]"
            >
              <svg class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
              </svg>
            </button>

            <!-- Main Product Image Frame -->
            <div class="relative w-full aspect-square sm:aspect-4/3 flex items-center justify-center p-6 sm:p-12 overflow-hidden bg-radial from-neutral-50/50 to-neutral-100/50 dark:from-neutral-900/40 dark:to-neutral-950/80">
              <img
                v-if="product.image && !imgError"
                :src="`/storage/${product.image}`"
                :alt="product.name"
                class="relative z-10 w-full h-full object-contain transition-transform duration-700 ease-out group-hover:scale-105"
                @load="onImgLoad"
                @error="onImgError"
              />
              <div
                v-else
                class="relative z-10 flex flex-col items-center justify-center text-center p-8"
              >
                <div class="w-24 h-24 rounded-3xl bg-[#D4AF37]/10 flex items-center justify-center mb-4 border border-[#D4AF37]/20">
                  <svg class="w-12 h-12 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                  </svg>
                </div>
                <h3 class="text-base font-bold text-neutral-800 dark:text-neutral-200">{{ product.name }}</h3>
                <p class="text-xs text-neutral-400 mt-1">Authentic Luxury Piece</p>
              </div>
            </div>

            <!-- Bottom Gallery Ribbon Highlights -->
            <div class="grid grid-cols-3 border-t border-neutral-100 dark:border-neutral-800 divide-x divide-neutral-100 dark:divide-neutral-800 bg-neutral-50/50 dark:bg-neutral-900/30 text-center py-3 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
              <div class="flex items-center justify-center gap-1.5 px-2">
                <span class="text-[#D4AF37]">✦</span>
                <span>Original Guaranteed</span>
              </div>
              <div class="flex items-center justify-center gap-1.5 px-2">
                <span class="text-[#D4AF37]">✦</span>
                <span>Insured Delivery</span>
              </div>
              <div class="flex items-center justify-center gap-1.5 px-2">
                <span class="text-[#D4AF37]">✦</span>
                <span>Bespoke Care</span>
              </div>
            </div>

          </motion.div>
        </div>

        <!-- RIGHT COLUMN: PURCHASE & DETAILS PANEL -->
        <div class="lg:col-span-5 flex flex-col space-y-6">

          <!-- Category and Subcategory header -->
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">
                {{ product.category?.name || 'Exclusive' }}
              </span>
              <span v-if="product.subcategory" class="text-neutral-400">•</span>
              <span v-if="product.subcategory" class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ product.subcategory.name }}
              </span>
            </div>

            <!-- Product Title -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-neutral-900 dark:text-white leading-tight">
              {{ product.name }}
            </h1>
          </div>

          <!-- Rating & Reviews link -->
          <div class="flex items-center gap-3 pt-1">
            <div v-if="averageRating" class="flex items-center gap-2">
              <div class="flex items-center gap-0.5">
                <svg
                  v-for="i in 5"
                  :key="i"
                  class="w-4 h-4"
                  :class="i <= Math.round(Number(averageRating)) ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-neutral-200 dark:fill-neutral-800 text-neutral-200 dark:text-neutral-800'"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </div>
              <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ averageRating }}</span>
              <button
                @click="scrollToReviews"
                class="text-xs text-[#D4AF37] hover:underline font-semibold cursor-pointer"
              >
                ({{ reviews.length }} {{ reviews.length === 1 ? 'review' : 'reviews' }})
              </button>
            </div>
            <div v-else class="flex items-center gap-2 text-xs text-neutral-400">
              <span>✦ First to discover this piece</span>
            </div>
          </div>

          <!-- Price & Stock Status Box -->
          <div class="p-5 rounded-2xl border border-neutral-200/80 dark:border-white/10 bg-white/60 dark:bg-[#1A1A1A]/60 backdrop-blur-md">
            <div class="flex items-baseline justify-between gap-4">
              <div class="flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-extrabold text-[#D4AF37]">
                  ${{ Number(product.price).toFixed(2) }}
                </span>
                <span class="text-xs text-neutral-400 uppercase tracking-wider font-semibold">USD</span>
              </div>

              <!-- Stock Pill -->
              <span
                :class="[
                  'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider',
                  product.stock > 0
                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40'
                    : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40'
                ]"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="product.stock > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                {{ product.stock > 0 ? `In Stock (${product.stock})` : 'Sold Out' }}
              </span>
            </div>

            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
              Taxes calculated at checkout. Free shipping on qualifying orders.
            </p>
          </div>

          <!-- Primary Purchase Actions -->
          <div class="space-y-3 pt-2">
            <div class="flex items-center gap-3">
              <!-- Add to Cart Main Button -->
              <button
                @click="addToCart"
                :disabled="isInCart || product.stock === 0"
                :class="[
                  'flex-1 flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl text-sm font-bold tracking-wide transition-all shadow-md cursor-pointer',
                  isInCart
                    ? 'bg-emerald-600 text-white shadow-emerald-600/25'
                    : product.stock === 0
                      ? 'bg-neutral-200 dark:bg-neutral-800 text-neutral-400 cursor-not-allowed shadow-none'
                      : 'bg-[#D4AF37] hover:bg-[#B8941E] text-neutral-950 shadow-[#D4AF37]/25 hover:shadow-lg hover:shadow-[#D4AF37]/30'
                ]"
              >
                <span v-if="product.stock === 0">Currently Sold Out</span>
                <span v-else-if="isInCart" class="inline-flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                  </svg>
                  Added to Cart
                </span>
                <span v-else class="inline-flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                  </svg>
                  Add to Cart
                </span>
              </button>

              <!-- Secondary Wishlist Toggle Button -->
              <button
                @click="toggleWishlist"
                :class="[
                  'w-14 h-14 rounded-2xl flex items-center justify-center border transition-all cursor-pointer shrink-0',
                  isInWishlist
                    ? 'border-red-300 bg-red-50 dark:bg-red-950/30 text-red-500 shadow-xs'
                    : 'border-neutral-200 dark:border-neutral-800 bg-white dark:bg-[#1A1A1A] text-neutral-600 dark:text-neutral-400 hover:border-[#D4AF37] hover:text-[#D4AF37]'
                ]"
                :title="isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'"
              >
                <svg class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                </svg>
              </button>
            </div>

            <!-- View Cart Quick Link -->
            <div class="flex items-center justify-between text-xs px-1 text-neutral-500 dark:text-neutral-400">
              <span>SKU: {{ product.uid }}</span>
              <Link href="/cart" class="text-[#D4AF37] hover:underline font-bold inline-flex items-center gap-1 no-underline">
                View Shopping Bag &rarr;
              </Link>
            </div>
          </div>

          <!-- ACCORDIONS: DETAILS, SHIPPING, AUTHENTICITY -->
          <div class="pt-4 border-t border-neutral-200/80 dark:border-white/10 space-y-3">

            <!-- Accordion 1: Description & Details -->
            <div class="border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden bg-white/70 dark:bg-[#1A1A1A]/70">
              <button
                @click="toggleAccordion('details')"
                class="w-full px-5 py-4 flex items-center justify-between text-left text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 cursor-pointer"
              >
                <span>Product Description & Story</span>
                <svg
                  class="w-4 h-4 text-[#D4AF37] transition-transform duration-300"
                  :class="{ 'rotate-180': activeAccordion === 'details' }"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="m6 9 6 6 6-6"/>
                </svg>
              </button>
              <div v-if="activeAccordion === 'details'" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80">
                <p>
                  {{ product.description || "Every facet of this piece reflects unmatched commitment to premium craftsmanship, balancing timeless heritage with modern aesthetics." }}
                </p>
                <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-neutral-100 dark:border-neutral-800 text-xs">
                  <div>
                    <span class="text-neutral-400 block">Category</span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ product.category?.name || 'General' }}</span>
                  </div>
                  <div>
                    <span class="text-neutral-400 block">Status</span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ product.stock > 0 ? 'Ready to Ship' : 'Backorder' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Accordion 2: Complimentary Shipping & Returns -->
            <div class="border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden bg-white/70 dark:bg-[#1A1A1A]/70">
              <button
                @click="toggleAccordion('shipping')"
                class="w-full px-5 py-4 flex items-center justify-between text-left text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 cursor-pointer"
              >
                <span>Complimentary Delivery & Packaging</span>
                <svg
                  class="w-4 h-4 text-[#D4AF37] transition-transform duration-300"
                  :class="{ 'rotate-180': activeAccordion === 'shipping' }"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="m6 9 6 6 6-6"/>
                </svg>
              </button>
              <div v-if="activeAccordion === 'shipping'" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 space-y-2">
                <p>
                  • <strong>Express Dispatch:</strong> Orders placed before 2 PM are prepared and dispatched within 24–48 hours.
                </p>
                <p>
                  • <strong>Signature Packaging:</strong> Each order arrives encased in our signature luxury presentation box, protected against courier transit.
                </p>
                <p>
                  • <strong>30-Day Returns:</strong> If you are not completely enchanted by your selection, enjoy seamless returns within 30 days of arrival.
                </p>
              </div>
            </div>

            <!-- Accordion 3: Authenticity & Craft Guarantee -->
            <div class="border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden bg-white/70 dark:bg-[#1A1A1A]/70">
              <button
                @click="toggleAccordion('authenticity')"
                class="w-full px-5 py-4 flex items-center justify-between text-left text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 cursor-pointer"
              >
                <span>Authenticity & Warranty</span>
                <svg
                  class="w-4 h-4 text-[#D4AF37] transition-transform duration-300"
                  :class="{ 'rotate-180': activeAccordion === 'authenticity' }"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="m6 9 6 6 6-6"/>
                </svg>
              </button>
              <div v-if="activeAccordion === 'authenticity'" class="px-5 pb-5 pt-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed border-t border-neutral-100 dark:border-neutral-800/80 space-y-2">
                <p>
                  Every piece in our catalog is rigorously verified by master curators. We guarantee authentic provenance and provide a certificate of authenticity with every order.
                </p>
              </div>
            </div>

          </div>

        </div>

      </div>

      <!-- CUSTOMER REVIEWS SECTION -->
      <div id="reviews-section" class="mt-20 pt-12 border-t border-neutral-200/80 dark:border-white/10">

        <!-- Reviews Section Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10">
          <div>
            <span class="text-xs font-bold uppercase tracking-widest text-[#D4AF37] block mb-1">
              Client Feedback
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 dark:text-white tracking-tight">
              Customer Reviews
            </h2>
          </div>

          <button
            v-if="authUser && !userReview && !showReviewForm"
            @click="showReviewForm = true"
            class="px-6 py-3 bg-[#D4AF37] hover:bg-[#B8941E] text-neutral-950 text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm cursor-pointer"
          >
            Write a Review
          </button>
        </div>

        <!-- Rating Overview Card & Distribution -->
        <div v-if="reviews.length > 0" class="grid grid-cols-1 md:grid-cols-12 gap-6 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-[#D4AF37]/20 shadow-sm mb-10">

          <!-- Left Score Summary -->
          <div class="md:col-span-4 flex flex-col items-center justify-center text-center p-4 border-b md:border-b-0 md:border-r border-neutral-100 dark:border-neutral-800">
            <span class="text-5xl sm:text-6xl font-extrabold text-[#D4AF37]">{{ averageRating }}</span>
            <div class="flex items-center gap-1 my-2">
              <svg
                v-for="i in 5"
                :key="i"
                class="w-5 h-5"
                :class="i <= Math.round(Number(averageRating)) ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-neutral-200 dark:fill-neutral-800 text-neutral-200 dark:text-neutral-800'"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
              Based on {{ reviews.length }} verified {{ reviews.length === 1 ? 'review' : 'reviews' }}
            </p>
          </div>

          <!-- Right Rating Bars Breakdown -->
          <div class="md:col-span-8 flex flex-col justify-center space-y-2 p-2 sm:p-4">
            <div
              v-for="star in [5, 4, 3, 2, 1]"
              :key="star"
              class="flex items-center gap-3 text-xs"
            >
              <span class="w-8 font-bold text-neutral-600 dark:text-neutral-400">{{ star }} ★</span>
              <div class="flex-1 h-2 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
                <div
                  class="h-full bg-[#D4AF37] rounded-full transition-all duration-500"
                  :style="{ width: `${reviews.length ? (ratingCounts[star] / reviews.length) * 100 : 0}%` }"
                ></div>
              </div>
              <span class="w-8 text-right text-neutral-400">{{ ratingCounts[star] }}</span>
            </div>
          </div>

        </div>

        <!-- REVIEW FORM (IF ACTIVE) -->
        <AnimatePresence>
          <motion.div
            v-if="showReviewForm && !userReview"
            initial="{ opacity: 0, y: -10 }"
            animate="{ opacity: 1, y: 0 }"
            exit="{ opacity: 0, y: -10 }"
            class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-[#D4AF37]/30 shadow-lg mb-10"
          >
            <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-2">Write Your Experience</h3>
            <p class="text-xs text-neutral-500 mb-6">Select your rating and tell other clients what you loved about this piece.</p>

            <form @submit.prevent="submitReview" class="space-y-5">
              <!-- Rating Selector -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-2">Your Rating</label>
                <div class="flex items-center gap-1.5">
                  <button
                    v-for="i in 5"
                    :key="i"
                    type="button"
                    @click="setRating(reviewForm, i)"
                    class="p-1 cursor-pointer transition-transform hover:scale-110"
                  >
                    <svg
                      class="w-7 h-7"
                      :class="i <= reviewForm.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-neutral-200 dark:fill-neutral-800 text-neutral-200 dark:text-neutral-800'"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </button>
                  <span class="text-xs font-bold text-[#D4AF37] ml-2">{{ reviewForm.rating }} of 5 Stars</span>
                </div>
              </div>

              <!-- Comment Textarea -->
              <div class="pro-form-group">
                <label class="pro-label">
                  <span>Your Review (Optional)</span>
                </label>
                <textarea
                  v-model="reviewForm.comment"
                  rows="3"
                  placeholder="Share details of the craftsmanship, quality, and your impression..."
                  class="pro-textarea"
                ></textarea>
              </div>

              <!-- Form Buttons -->
              <div class="flex items-center gap-3 pt-1">
                <button
                  type="submit"
                  :disabled="reviewForm.processing"
                  class="pro-btn-primary w-auto px-6 py-2.5 text-xs"
                >
                  <svg v-if="reviewForm.processing" class="w-3.5 h-3.5 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span>{{ reviewForm.processing ? 'Publishing...' : 'Publish Review' }}</span>
                </button>
                <button
                  type="button"
                  @click="showReviewForm = false"
                  class="px-5 py-2.5 border border-border text-foreground font-semibold text-xs rounded-xl hover:bg-muted transition-all cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </form>
          </motion.div>
        </AnimatePresence>

        <!-- Unauthenticated Prompt -->
        <div
          v-if="!authUser && reviews.length === 0"
          class="p-10 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-white/10 text-center shadow-sm mb-10 max-w-xl mx-auto"
        >
          <div class="w-16 h-16 rounded-full bg-[#D4AF37]/10 text-[#D4AF37] flex items-center justify-center mx-auto mb-4 border border-[#D4AF37]/20">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
            </svg>
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">Be the First to Review</h3>
          <p class="text-xs text-neutral-500 mb-5">Sign in to leave your feedback and assist other collectors.</p>
          <button
            @click="openLogin()"
            class="px-6 py-2.5 bg-[#D4AF37] hover:bg-[#B8941E] text-neutral-950 font-bold text-xs rounded-xl transition-all cursor-pointer"
          >
            Sign in to Review
          </button>
        </div>

        <!-- REVIEWS LIST -->
        <div v-if="reviews.length > 0" class="space-y-4">
          <div
            v-for="review in reviews"
            :key="review.id"
            class="p-6 rounded-2xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-white/10 shadow-sm"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#D4AF37]/15 text-[#D4AF37] font-bold text-sm flex items-center justify-center border border-[#D4AF37]/30">
                  {{ review.user?.name?.charAt(0)?.toUpperCase() || 'C' }}
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-neutral-900 dark:text-white">{{ review.user?.name || 'Verified Collector' }}</span>
                    <span class="text-[10px] uppercase tracking-wider font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">
                      Verified
                    </span>
                  </div>
                  <div class="flex items-center gap-0.5 mt-1">
                    <svg
                      v-for="i in 5"
                      :key="i"
                      class="w-3.5 h-3.5"
                      :class="i <= review.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-neutral-200 dark:fill-neutral-800 text-neutral-200 dark:text-neutral-800'"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Owner Edit & Delete Buttons -->
              <div v-if="authUser && authUser.id === review.user_id" class="flex items-center gap-2 text-xs font-semibold">
                <button
                  v-if="editingReview !== review.id"
                  @click="startEditing(review)"
                  class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="deleteReview(review)"
                  class="text-neutral-400 hover:text-red-500 cursor-pointer"
                >
                  Delete
                </button>
              </div>
            </div>

            <!-- Inline Edit Form -->
            <div v-if="editingReview === review.id" class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
              <div class="flex items-center gap-1 mb-3">
                <button
                  v-for="i in 5"
                  :key="i"
                  type="button"
                  @click="setRating(editForm, i)"
                  class="p-0.5 cursor-pointer"
                >
                  <svg
                    class="w-5 h-5"
                    :class="i <= editForm.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-neutral-200 dark:fill-neutral-800 text-neutral-200 dark:text-neutral-800'"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </button>
              </div>
              <textarea
                v-model="editForm.comment"
                rows="2"
                class="w-full px-3 py-2 bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg text-xs"
              ></textarea>
              <div class="flex items-center gap-2 mt-2">
                <button
                  @click="saveEdit(review)"
                  :disabled="editForm.processing"
                  class="px-4 py-1.5 bg-[#D4AF37] text-neutral-950 font-bold text-xs rounded-lg cursor-pointer"
                >
                  Save
                </button>
                <button
                  @click="cancelEditing"
                  class="px-4 py-1.5 border border-neutral-200 dark:border-neutral-800 text-xs rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
              </div>
            </div>

            <!-- Review Comment Content -->
            <p v-else-if="review.comment" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 mt-3 leading-relaxed">
              {{ review.comment }}
            </p>

            <div class="mt-3 text-[11px] text-neutral-400">
              {{ new Date(review.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
            </div>
          </div>
        </div>

        <!-- Empty reviews state -->
        <div
          v-else-if="authUser"
          class="p-12 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-white/10 text-center shadow-sm"
        >
          <div class="w-16 h-16 rounded-full bg-[#D4AF37]/10 text-[#D4AF37] flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
            </svg>
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">No Reviews Yet</h3>
          <p class="text-xs text-neutral-500">Be the first to share your thoughts on this creation.</p>
        </div>

      </div>

    </div>

    <!-- MOBILE STICKY BOTTOM PURCHASE BAR -->
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-[#1A1A1A]/95 backdrop-blur-md border-t border-neutral-200/80 dark:border-white/10 px-4 py-3 flex items-center justify-between gap-3 shadow-2xl">
      <div class="flex flex-col">
        <span class="text-xl font-extrabold text-[#D4AF37]">${{ Number(product.price).toFixed(2) }}</span>
        <span
          :class="[
            'text-[10px] font-bold uppercase tracking-wider',
            product.stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'
          ]"
        >
          {{ product.stock > 0 ? 'In Stock' : 'Sold Out' }}
        </span>
      </div>

      <button
        @click="addToCart"
        :disabled="isInCart || product.stock === 0"
        :class="[
          'flex-1 max-w-[200px] py-3 rounded-xl text-xs font-bold tracking-wide transition-all shadow-sm cursor-pointer',
          isInCart
            ? 'bg-emerald-600 text-white'
            : product.stock === 0
              ? 'bg-neutral-200 dark:bg-neutral-800 text-neutral-400 cursor-not-allowed'
              : 'bg-[#D4AF37] text-neutral-950 font-extrabold'
        ]"
      >
        <span v-if="product.stock === 0">Sold Out</span>
        <span v-else-if="isInCart">Added to Cart ✓</span>
        <span v-else>Add to Cart</span>
      </button>
    </div>

  </div>
</template>
