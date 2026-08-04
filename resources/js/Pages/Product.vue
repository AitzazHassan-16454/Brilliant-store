<script setup>
import { router, usePage, Head, Link, useForm } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import { useAuthModal } from "../composables/useAuthModal.js"
import { useNotification } from "../composables/useNotification.js"
import { motion, AnimatePresence } from "motion-v"
import { motionPresets as m, staggerContainer, staggerItem } from "../lib/motion.js"

const props = defineProps({
  product: Object,
  cartProductIds: Array,
  wishlistProductIds: Array,
  reviews: Array,
  userReview: Object,
})

const { openLogin } = useAuthModal()
const { success } = useNotification()

const isInCart = props.cartProductIds.includes(props.product.id)
const isInWishlist = props.wishlistProductIds.includes(props.product.id)

const page = usePage()
const authUser = computed(() => page.props.auth.user)

const averageRating = computed(() => {
  if (!props.reviews.length) return null
  const sum = props.reviews.reduce((acc, r) => acc + r.rating, 0)
  return (sum / props.reviews.length).toFixed(1)
})

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
    },
  })
}

function deleteReview(review) {
  if (!confirm("Delete your review?")) return
  router.delete(`/reviews/${review.id}`, {
    preserveScroll: true,
  })
}

function setRating(form, value) {
  form.rating = value
}

const addToCart = () => {
  if (!usePage().props.auth.user) {
    openLogin()
    return
  }

  router.post("/cart", {
    product_id: props.product.id
  }, {
    preserveScroll: true,
    onSuccess: () => success("Added to cart")
  })
}

const toggleWishlist = () => {
  if (!usePage().props.auth.user) {
    openLogin()
    return
  }

  router.post("/wishlist", {
    product_id: props.product.id
  }, {
    preserveScroll: true,
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

const productStagger = staggerContainer(0.1, 0.1)
const detailItems = staggerItem
</script>

<template>
<Head :title="product.name" />

<div
  class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]"
  style="font-family:'Inter',system-ui,sans-serif;"
>

  <!-- HEADER -->
  <motion.div
    :initial="{ y: -56, opacity: 0 }"
    :animate="{ y: 0, opacity: 1 }"
    :transition="{ duration: 0.5, ease: m.easeOutExpo }"
    class="sticky top-0 z-50 border-b border-border bg-card/90 backdrop-blur-md"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">

      <Link
        :href="`/categories/${product.category.uid}`"
        class="flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-luxury no-underline active:scale-95"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
      </Link>

      <Link
        href="/cart"
        class="relative flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-foreground border border-border bg-card hover:bg-muted transition-luxury no-underline active:scale-95"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        Cart

        <span
          v-if="$page.props.cartCount"
          class="w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-bold"
        >
          {{ $page.props.cartCount }}
        </span>
      </Link>

    </div>
  </motion.div>

  <!-- PRODUCT SECTION -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 md:py-10">

    <motion.div
      :variants="productStagger"
      initial="hidden"
      animate="visible"
      class="border border-border bg-card rounded-3xl overflow-hidden shadow-luxury"
    >

      <div class="grid lg:grid-cols-2 gap-0">

        <!-- IMAGE SECTION -->
        <motion.div
          :variants="m.scaleIn"
          class="relative bg-[#FAF7F2] dark:bg-[#111] border-b lg:border-b-0 lg:border-r border-border p-8 sm:p-12 flex items-center justify-center"
        >

          <div class="relative w-full max-w-lg">

            <div class="absolute inset-0 rounded-2xl ring-1 ring-[#D4AF37]/20 shadow-[0_0_40px_rgba(212,175,55,0.08)]" />

            <span
              v-if="product.category?.name"
              class="absolute top-3 left-3 z-10 bg-[#D4AF37] text-[#0A0A0A] text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full"
            >
              {{ product.category.name }}
            </span>

            <button
              @click="toggleWishlist"
              :class="[
                'absolute top-3 right-3 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-border bg-card/95 backdrop-blur-sm shadow-sm transition-luxury hover:bg-[#D4AF37] active:scale-90',
                isInWishlist
                  ? 'translate-y-0 opacity-100'
                  : 'translate-y-0 opacity-100 md:translate-y-2 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100'
              ]"
            >
              <svg
                :class="['w-4 h-4', isInWishlist ? 'fill-red-500 text-red-500' : 'text-muted-foreground']"
                :fill="isInWishlist ? 'currentColor' : 'none'"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
            </button>

            <div v-if="!imgLoaded" class="shimmer absolute inset-0 z-10 rounded-2xl" />

            <img
              v-if="product.image && !imgError"
              :src="`/storage/${product.image}`"
              :alt="product.name"
              class="relative z-0 w-full h-[340px] sm:h-[440px] object-contain"
              loading="lazy"
              @load="onImgLoad"
              @error="onImgError"
            />

            <div
              v-else
              class="relative z-0 w-full h-[340px] sm:h-[440px] flex items-center justify-center bg-[#FAF7F2] dark:bg-[#111] rounded-2xl"
            >
              <svg class="w-20 h-20 text-muted-foreground/30" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
            </div>

          </div>

        </motion.div>

        <!-- DETAILS SECTION -->
        <motion.div
          :variants="m.fadeRight"
          class="p-6 sm:p-8 lg:p-10 flex flex-col justify-between"
        >

          <div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-foreground leading-tight">
              {{ product.name }}
            </h1>

            <div v-if="averageRating" class="flex items-center gap-2 mt-4">
              <div class="flex gap-0.5">
                <template v-for="i in 5" :key="i">
                  <svg
                    class="w-4 h-4"
                    :class="i <= Math.round(averageRating) ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-current text-muted dark:text-muted-foreground/30'"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </template>
              </div>
              <span class="text-sm font-semibold text-foreground">{{ averageRating }}</span>
              <span class="text-sm text-muted-foreground">({{ reviews.length }} {{ reviews.length === 1 ? 'review' : 'reviews' }})</span>
            </div>

            <div class="mt-6 flex items-end gap-4">
              <span class="text-4xl sm:text-5xl font-extrabold text-[#D4AF37]">
                ${{ product.price }}
              </span>

              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider',
                  product.stock > 0
                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                    : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                ]"
              >
                {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>

            <div class="mt-8 pt-8 border-t border-border">
              <p class="text-muted-foreground leading-relaxed text-sm sm:text-base">
                {{
                  product.description ||
                  "No description available for this product."
                }}
              </p>
            </div>

          </div>

          <div class="mt-8 space-y-4">
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                @click="addToCart"
                :disabled="isInCart || product.stock === 0"
                :class="[
                  'flex-1 px-6 py-3.5 rounded-xl text-sm font-bold transition-luxury active:scale-[0.98]',
                  isInCart || product.stock === 0
                    ? 'bg-muted text-muted-foreground cursor-not-allowed'
                    : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E] shadow-sm hover:shadow-md'
                ]"
              >
                <span v-if="product.stock === 0">Out of Stock</span>
                <span v-else-if="isInCart" class="flex items-center justify-center gap-1.5">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                  Added
                </span>
                <span v-else class="flex items-center justify-center gap-1.5">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                  </svg>
                  Add to Cart
                </span>
              </button>

              <button
                @click="toggleWishlist"
                :class="[
                  'flex-1 sm:flex-none px-6 py-3.5 rounded-xl text-sm font-bold border transition-luxury active:scale-[0.98]',
                  isInWishlist
                    ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30'
                    : 'border-border bg-card text-foreground hover:bg-muted'
                ]"
              >
                <span class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4" :class="isInWishlist ? 'fill-red-500 text-red-500' : 'text-muted-foreground'" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                  {{ isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}
                </span>
              </button>
            </div>

            <Link
              href="/cart"
              class="inline-flex items-center gap-1 text-sm font-semibold text-[#D4AF37] hover:text-[#B8941E] transition-luxury no-underline active:scale-95"
            >
              Go to Cart
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
              </svg>
            </Link>
          </div>

        </motion.div>

      </div>

    </motion.div>

    <!-- REVIEWS SECTION -->
    <div class="mt-10 md:mt-14">

      <!-- REVIEWS HEADER -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-[#D4AF37]/10 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-extrabold text-foreground tracking-tight">Customer Reviews</h2>
            <p class="text-sm text-muted-foreground">{{ reviews.length }} {{ reviews.length === 1 ? 'review' : 'reviews' }}</p>
          </div>
        </div>

        <button
          v-if="authUser && !userReview && !showReviewForm"
          @click="showReviewForm = true"
          class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8941E] text-[#0A0A0A] text-sm font-bold rounded-xl transition-luxury active:scale-95 cursor-pointer"
        >
          Write a Review
        </button>
      </div>

      <!-- REVIEW FORM -->
      <AnimatePresence>
        <motion.div
          v-if="showReviewForm && !userReview"
          :initial="{ opacity: 0, height: 0, y: -12 }"
          :animate="{ opacity: 1, height: 'auto', y: 0 }"
          :exit="{ opacity: 0, height: 0, y: -12 }"
          :transition="{ duration: 0.3, ease: m.easeOutExpo }"
          class="border border-border bg-card rounded-2xl p-6 mb-8 shadow-luxury overflow-hidden"
        >
        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground mb-4">Your Review</h3>

        <form @submit.prevent="submitReview">
          <div class="flex items-center gap-1 mb-4">
            <button
              v-for="i in 5"
              :key="i"
              type="button"
              @click="setRating(reviewForm, i)"
              class="cursor-pointer transition-transform active:scale-110"
            >
              <svg
                class="w-7 h-7 transition-colors"
                :class="i <= reviewForm.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-current text-muted dark:text-muted-foreground/30 hover:text-[#D4AF37]/50'"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </button>
            <span class="text-sm font-semibold text-muted-foreground ml-2">{{ reviewForm.rating }}/5</span>
          </div>

          <textarea
            v-model="reviewForm.comment"
            placeholder="Share your thoughts about this product (optional)"
            rows="3"
            maxlength="1000"
            class="w-full px-4 py-3 border border-border rounded-xl text-sm text-foreground placeholder-muted-foreground bg-card focus:outline-none focus:border-[#D4AF37] resize-none transition-luxury"
          ></textarea>

          <div class="flex items-center gap-3 mt-4">
            <button
              type="submit"
              :disabled="reviewForm.processing"
              class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8941E] text-[#0A0A0A] text-sm font-bold rounded-xl transition-luxury active:scale-95 disabled:opacity-50 cursor-pointer"
            >
              {{ reviewForm.processing ? 'Submitting...' : 'Submit Review' }}
            </button>
            <button
              type="button"
              @click="showReviewForm = false"
              class="px-5 py-2.5 border border-border hover:bg-muted text-foreground text-sm font-semibold rounded-xl transition-luxury active:scale-95 cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </form>
      </motion.div>
      </AnimatePresence>

      <!-- LOGIN PROMPT -->
      <div
        v-if="!authUser && reviews.length === 0"
        class="border border-border bg-card rounded-2xl p-8 mb-8 text-center shadow-luxury"
      >
        <div class="w-16 h-16 bg-[#D4AF37]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
        </div>
        <p class="text-muted-foreground text-sm mb-3">Be the first to review this product</p>
        <button
          @click="openLogin()"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8941E] text-[#0A0A0A] text-sm font-bold rounded-xl transition-luxury active:scale-95 cursor-pointer"
        >
          Login to Review
        </button>
      </div>

      <!-- REVIEW LIST -->
      <motion.div
        v-if="reviews.length > 0"
        :variants="m.staggerContainer(0.08, 0.05)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="space-y-4"
      >
        <motion.div
          v-for="review in reviews"
          :key="review.id"
          :variants="m.itemFadeUp()"
          class="border border-border bg-card rounded-2xl p-6 shadow-luxury"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-[#D4AF37]/10 rounded-full flex items-center justify-center text-sm font-bold text-[#D4AF37]">
                {{ review.user?.name?.charAt(0)?.toUpperCase() || '?' }}
              </div>
              <div>
                <p class="text-sm font-semibold text-foreground">{{ review.user?.name || 'Anonymous' }}</p>
                <div class="flex items-center gap-0.5 mt-0.5">
                  <svg
                    v-for="i in 5"
                    :key="i"
                    class="w-3.5 h-3.5"
                    :class="i <= review.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-current text-muted dark:text-muted-foreground/30'"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                </div>
              </div>
            </div>

            <div v-if="authUser && authUser.id === review.user_id" class="flex items-center gap-2 shrink-0">
              <button
                v-if="editingReview !== review.id"
                @click="startEditing(review)"
                class="text-xs font-semibold text-muted-foreground hover:text-foreground transition-luxury cursor-pointer"
              >
                Edit
              </button>
              <button
                @click="deleteReview(review)"
                class="text-xs font-semibold text-muted-foreground hover:text-red-500 transition-luxury cursor-pointer"
              >
                Delete
              </button>
            </div>
          </div>

          <!-- EDIT FORM -->
          <div v-if="editingReview === review.id" class="mt-4 pt-4 border-t border-border">
            <div class="flex items-center gap-1 mb-3">
              <button
                v-for="i in 5"
                :key="i"
                type="button"
                @click="setRating(editForm, i)"
                class="cursor-pointer transition-transform active:scale-110"
              >
                <svg
                  class="w-6 h-6 transition-colors"
                  :class="i <= editForm.rating ? 'fill-[#D4AF37] text-[#D4AF37]' : 'fill-current text-muted dark:text-muted-foreground/30 hover:text-[#D4AF37]/50'"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </button>
            </div>

            <textarea
              v-model="editForm.comment"
              rows="2"
              maxlength="1000"
              class="w-full px-4 py-3 border border-border rounded-xl text-sm text-foreground placeholder-muted-foreground bg-card focus:outline-none focus:border-[#D4AF37] resize-none transition-luxury"
            ></textarea>

            <div class="flex items-center gap-3 mt-3">
              <button
                @click="saveEdit(review)"
                :disabled="editForm.processing"
                class="px-4 py-2 bg-[#D4AF37] hover:bg-[#B8941E] text-[#0A0A0A] text-xs font-bold rounded-lg transition-luxury active:scale-95 disabled:opacity-50 cursor-pointer"
              >
                {{ editForm.processing ? 'Saving...' : 'Save' }}
              </button>
              <button
                @click="cancelEditing"
                class="px-4 py-2 border border-border hover:bg-muted text-muted-foreground text-xs font-semibold rounded-lg transition-luxury active:scale-95 cursor-pointer"
              >
                Cancel
              </button>
            </div>
          </div>

          <p v-else-if="review.comment" class="text-sm text-muted-foreground mt-3 leading-relaxed">
            {{ review.comment }}
          </p>

          <div class="flex items-center gap-1.5 mt-3">
            <svg class="w-3.5 h-3.5 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-xs text-muted-foreground font-medium">
              {{ new Date(review.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
            </p>
          </div>
        </motion.div>
      </motion.div>

      <!-- EMPTY STATE -->
      <div
        v-else-if="authUser"
        class="border border-border bg-card rounded-2xl p-10 text-center shadow-luxury"
      >
        <div class="w-16 h-16 bg-[#D4AF37]/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
          </svg>
        </div>
        <p class="text-lg font-bold text-foreground">No reviews yet</p>
        <p class="text-sm text-muted-foreground mt-1">Be the first to share your experience</p>
      </div>

    </div>

  </div>

  <!-- MOBILE STICKY BOTTOM BAR -->
  <motion.div
    :initial="{ y: 80, opacity: 0 }"
    :animate="{ y: 0, opacity: 1 }"
    :transition="{ delay: 0.3, type: 'spring', stiffness: 320, damping: 28 }"
    class="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t border-border bg-card/95 backdrop-blur-md px-4 py-3 flex items-center justify-between gap-4 shadow-luxury"
  >
    <div class="flex flex-col">
      <span class="text-2xl font-extrabold text-[#D4AF37]">${{ product.price }}</span>
      <span
        :class="[
          'text-[10px] font-bold uppercase tracking-wider',
          product.stock > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'
        ]"
      >
        {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
      </span>
    </div>

    <button
      @click="addToCart"
      :disabled="isInCart || product.stock === 0"
      :class="[
        'flex-1 max-w-[200px] px-6 py-3 rounded-xl text-sm font-bold transition-luxury active:scale-[0.98]',
        isInCart || product.stock === 0
          ? 'bg-muted text-muted-foreground cursor-not-allowed'
          : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E]'
      ]"
    >
      <span v-if="product.stock === 0">Out of Stock</span>
      <span v-else-if="isInCart">Added</span>
      <span v-else>Add to Cart</span>
    </button>
  </motion.div>

  <!-- Spacer for mobile bottom bar -->
  <div class="md:hidden h-20" />

</div>
</template>
