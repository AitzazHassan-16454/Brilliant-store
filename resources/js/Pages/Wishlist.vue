<script setup>
import { ref, computed } from "vue"
import { router, usePage, Head, Link } from "@inertiajs/vue3"
import { motion } from "motion-v"
import { motionPresets as m, staggerContainer, staggerItem } from "../lib/motion.js"
import { useAuthModal } from "../composables/useAuthModal.js"
import { useNotification } from "../composables/useNotification.js"

const props = defineProps({
  wishlistItems: Array,
  cartProductIds: Array,
})

const page = usePage()
const { openLogin } = useAuthModal()
const { success } = useNotification()

const loadedImages = ref({})
const addingAll = ref(false)

const isInCart = (product) => props.cartProductIds?.includes(product.id)

const formatPrice = (val) => {
  const num = Number(val)
  return isNaN(num) ? "$0.00" : `$${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

const removeFromWishlist = (item) => {
  router.delete(`/wishlist/${item.id}`, {
    preserveScroll: true,
  })
}

const addToCart = (product) => {
  if (!page.props.auth.user) {
    openLogin()
    return
  }
  router.post("/cart", { product_id: product.id }, {
    preserveScroll: true,
    onSuccess: () => success("Added to bag"),
  })
}

const availableItems = computed(() => {
  return (props.wishlistItems || []).filter(item => item.product?.stock > 0 && !isInCart(item.product))
})

const addAllAvailableToCart = async () => {
  if (!availableItems.value.length || addingAll.value) return
  if (!page.props.auth.user) {
    openLogin()
    return
  }

  addingAll.value = true
  for (const item of availableItems.value) {
    await new Promise((resolve) => {
      router.post("/cart", { product_id: item.product.id }, {
        preserveScroll: true,
        onFinish: () => resolve(),
      })
    })
  }
  addingAll.value = false
  success(`Added ${availableItems.value.length} pieces to your bag`)
}

const gridStagger = staggerContainer(0.06, 0.04)
const gridItem = staggerItem
</script>

<template>
<Head>
  <title>Curated Wishlist — Brilliant</title>
  <meta name="description" content="Review your curated acquisitions and saved masterworks." />
</Head>

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5] selection:bg-[#D4AF37]/30 selection:text-[#1A1A1A] dark:selection:text-white" style="font-family:'Instrument Sans',Inter,system-ui,sans-serif;">

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">

    <!-- ══════════════════════════════
         PAGE HEADER & BREADCRUMB
    ══════════════════════════════ -->
    <motion.div
      :initial="{ opacity: 0, y: 15 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ duration: 0.5, ease: m.easeOutExpo }"
      class="mb-8 sm:mb-12 pb-6 border-b border-gray-200/80 dark:border-[#D4AF37]/15 flex flex-col sm:flex-row sm:items-end justify-between gap-4"
    >
      <div>
        <div class="flex items-center gap-2 text-xs text-[#6B6B6B] dark:text-[#A0A0A0] mb-2 font-medium">
          <Link href="/" class="hover:text-[#D4AF37] transition-colors no-underline text-inherit">Home</Link>
          <span>/</span>
          <span class="text-[#D4AF37] font-semibold">Wishlist</span>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#D4AF37]/15 text-[#D4AF37]">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
              Curated Wishlist
            </h1>
            <p class="text-xs sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0] mt-0.5">
              {{ wishlistItems?.length || 0 }} {{ (wishlistItems?.length || 0) === 1 ? 'piece' : 'pieces' }} saved in your personal archive
            </p>
          </div>
        </div>
      </div>

      <!-- Action buttons -->
      <div v-if="wishlistItems?.length" class="flex items-center gap-3">
        <button
          v-if="availableItems.length > 0"
          type="button"
          @click="addAllAvailableToCart"
          :disabled="addingAll"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#D4AF37] text-white dark:text-[#0A0A0A] font-semibold text-xs sm:text-sm hover:bg-[#C9A032] shadow-md shadow-[#D4AF37]/20 transition-all duration-300 disabled:opacity-50 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.188-.578-1.119-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>
          <span>{{ addingAll ? 'Transferring...' : `Move All to Bag (${availableItems.length})` }}</span>
        </button>

        <Link
          href="/all-products"
          class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-[#D4AF37]/30 bg-white/70 dark:bg-[#141414]/70 text-xs sm:text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] hover:border-[#D4AF37] transition-all no-underline"
        >
          <span>Continue Browsing</span>
        </Link>
      </div>
    </motion.div>

    <!-- ══════════════════════════════
         WISHLIST ITEMS GRID
    ══════════════════════════════ -->
    <motion.div
      v-if="wishlistItems?.length > 0"
      :variants="gridStagger"
      initial="hidden"
      animate="visible"
      class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6"
    >
      <motion.div
        v-for="item in wishlistItems"
        :key="item.id"
        :variants="gridItem"
        class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#141414] shadow-sm hover:shadow-xl hover:border-[#D4AF37]/60 transition-all duration-300"
      >
        <!-- Product Image Container -->
        <Link :href="`/products/${item.product?.uid}`" class="relative block aspect-square overflow-hidden bg-neutral-100 dark:bg-neutral-900 no-underline">
          <div
            v-if="!loadedImages[item.id]"
            class="shimmer absolute inset-0"
          />

          <img
            :src="item.product?.image ? `/storage/${item.product.image}` : '/placeholder.png'"
            :alt="item.product?.name"
            loading="lazy"
            :class="[
              'h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105',
              loadedImages[item.id] ? 'opacity-100' : 'opacity-0'
            ]"
            @load="loadedImages[item.id] = true"
          />

          <!-- Gradient overlay on hover -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/80 backdrop-blur-md text-[11px] font-semibold text-white">
              Quick Inspect
            </span>
          </div>

          <!-- Stock Pill Badge -->
          <div class="absolute top-2.5 left-2.5 z-10">
            <span
              :class="[
                'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider backdrop-blur-md border',
                item.product?.stock > 0
                  ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border-emerald-500/30'
                  : 'bg-rose-500/15 text-rose-700 dark:text-rose-400 border-rose-500/30'
              ]"
            >
              {{ item.product?.stock > 0 ? 'In Stock' : 'Sold Out' }}
            </span>
          </div>

          <!-- Remove from Wishlist Button -->
          <button
            type="button"
            @click.prevent.stop="removeFromWishlist(item)"
            aria-label="Remove from wishlist"
            class="absolute top-2.5 right-2.5 z-10 flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-full bg-white/90 dark:bg-[#1A1A1A]/90 backdrop-blur-md border border-gray-200 dark:border-white/10 text-rose-500 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all duration-200 shadow-sm cursor-pointer"
          >
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
          </button>
        </Link>

        <!-- Card Body -->
        <div class="flex flex-1 flex-col p-3.5 sm:p-4">
          <!-- Category -->
          <div v-if="item.product?.category" class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.16em] text-[#D4AF37] mb-1 truncate">
            {{ item.product.category.name }}
          </div>

          <!-- Product Title -->
          <Link
            :href="`/products/${item.product?.uid}`"
            class="font-bold text-xs sm:text-sm text-[#1A1A1A] dark:text-[#F5F5F5] line-clamp-2 hover:text-[#D4AF37] transition-colors leading-snug mb-3 no-underline"
          >
            {{ item.product?.name }}
          </Link>

          <!-- Price & Add to Bag (Matching ProductCard.vue icon-only style) -->
          <div class="mt-auto flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
            <div class="min-w-0">
              <span class="text-sm sm:text-base font-extrabold text-[#D4AF37]">
                {{ formatPrice(item.product?.price) }}
              </span>
            </div>

            <!-- Icon-only Add to Cart Button -->
            <button
              type="button"
              @click="addToCart(item.product)"
              :disabled="item.product?.stock === 0 || isInCart(item.product)"
              :title="item.product?.stock === 0 ? 'Sold Out' : isInCart(item.product) ? 'In Bag' : 'Add to Bag'"
              :aria-label="item.product?.stock === 0 ? 'Sold Out' : isInCart(item.product) ? 'In Bag' : 'Add to Bag'"
              :class="[
                'flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl shadow-sm transition-all duration-300 cursor-pointer',
                item.product?.stock === 0
                  ? 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400 cursor-not-allowed opacity-60'
                  : isInCart(item.product)
                    ? 'bg-emerald-600 text-white shadow-emerald-600/20'
                    : 'bg-[#D4AF37] text-white hover:bg-[#C9A032] shadow-[#D4AF37]/20 hover:scale-105 active:scale-95'
              ]"
            >
              <!-- Normal Bag Icon -->
              <svg v-if="!isInCart(item.product) && item.product?.stock > 0" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.188-.578-1.119-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
              </svg>
              <!-- Added Checkmark -->
              <svg v-else-if="isInCart(item.product)" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
              <!-- Sold Out Disabled Icon -->
              <svg v-else class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            </button>
          </div>
        </div>

      </motion.div>
    </motion.div>

    <!-- ══════════════════════════════
         EMPTY STATE
    ══════════════════════════════ -->
    <div v-else class="text-center py-20 sm:py-28 flex flex-col items-center gap-4 max-w-md mx-auto">
      <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10 text-[#D4AF37] border border-[#D4AF37]/20 shadow-md">
        <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24">
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
      </div>

      <div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-[#1A1A1A] dark:text-[#F5F5F5]">
          Your Personal Archive is Empty
        </h2>
        <p class="text-xs sm:text-sm mt-1.5 text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
          Save pieces you admire while exploring the catalog to compare details and review anytime.
        </p>
      </div>

      <Link
        href="/all-products"
        class="mt-3 inline-flex items-center gap-2 text-xs sm:text-sm font-semibold px-6 py-3 rounded-xl text-white bg-[#D4AF37] hover:bg-[#C9A032] shadow-lg shadow-[#D4AF37]/25 transition-all duration-300 no-underline"
      >
        <span>Explore Catalog Vault</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
        </svg>
      </Link>
    </div>

  </div>

</div>
</template>
