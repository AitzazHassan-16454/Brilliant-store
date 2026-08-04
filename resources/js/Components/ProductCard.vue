<script setup>
import { ref } from "vue"
import { Link } from "@inertiajs/vue3"
import { motion } from "motion-v"
import { motionPresets as m } from "../lib/motion.js"

const props = defineProps({
  product: { type: Object, required: true },
  isInWishlist: { type: Boolean, default: false },
  isInCart: { type: Boolean, default: false },
})

const emit = defineEmits(["add-to-cart", "toggle-wishlist"])

const imgLoaded = ref(false)
</script>

<template>
  <motion.div
    :whileHover="{ y: -6, boxShadow: '0 8px 30px rgba(0,0,0,0.12)', transition: m.spring.gentle }"
    class="group relative flex h-full flex-col overflow-hidden rounded-2xl md:rounded-lg border border-border bg-card shadow-luxury transition-colors hover:border-[#D4AF37]/50">

    <!-- Image -->
    <Link :href="`/products/${product.uid}`" class="relative block aspect-square overflow-hidden bg-gradient-to-br from-[#D4AF37]/25 via-[#D4AF37]/10 to-[#D4AF37]/30 dark:from-[#D4AF37]/20 dark:via-[#D4AF37]/5 dark:to-[#D4AF37]/25">
      <div v-if="!product.image || !imgLoaded" class="absolute inset-0 flex items-center justify-center p-4 text-center">
        <span class="text-sm font-semibold text-[#D4AF37]/80 dark:text-[#D4AF37]/70 leading-tight">{{ product.name }}</span>
      </div>
      <img v-if="product.image"
        :src="`/storage/${product.image}`"
        :alt="product.name"
        loading="lazy"
        class="relative h-full w-full object-cover transition-luxury duration-500 group-hover:scale-[1.06]"
        @load="imgLoaded = true"
        @error="imgLoaded = false" />

      <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-luxury" />

      <!-- Quick view overlay -->
      <div class="absolute inset-x-0 bottom-0 flex justify-center pb-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <span class="text-white text-xs font-semibold px-4 py-1.5 rounded-full bg-black/85">Quick View</span>
      </div>

      <!-- Wishlist -->
      <motion.button
        @click.prevent="emit('toggle-wishlist')"
        :whileHover="{ scale: 1.12 }"
        :whileTap="{ scale: 0.88 }"
        :class="[
          'absolute top-2.5 right-2.5 md:top-3 md:right-3 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-border bg-card/95 backdrop-blur-sm shadow-sm transition-luxury',
          isInWishlist
            ? 'hover:bg-red-500 hover:text-white'
            : 'hover:bg-[#D4AF37]',
          isInWishlist
            ? 'translate-y-0 opacity-100'
            : 'translate-y-0 opacity-100 md:translate-y-2 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100'
        ]">
        <svg
          :class="['w-4 h-4', isInWishlist ? 'fill-red-500 text-red-500' : 'text-foreground']"
          :fill="isInWishlist ? 'currentColor' : 'none'"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
      </motion.button>
    </Link>

    <!-- Card body -->
    <div class="flex flex-1 flex-col p-3 md:p-4">

      <!-- Category (mobile) -->
      <div class="mb-1.5 md:mb-2">
        <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground md:text-[#D4AF37]">
          {{ product.category?.name }}
        </span>
      </div>

      <!-- Title -->
      <Link :href="`/products/${product.uid}`"
        class="mb-1.5 md:mb-2 line-clamp-2 min-h-[2.5rem] md:min-h-0 text-sm leading-5 md:text-base md:leading-normal font-semibold text-foreground no-underline transition-colors group-hover:text-[#D4AF37]">
        {{ product.name }}
      </Link>

      <!-- Stock badge -->
      <div class="mb-2 md:mb-3">
        <span
          :class="[
            'text-[10px] font-bold uppercase tracking-wider w-fit',
            product.stock > 0
              ? 'text-green-600 dark:text-green-400'
              : 'text-red-500 dark:text-red-400'
          ]"
        >
          {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
        </span>
      </div>

      <!-- Stars (mobile) -->
      <div v-if="product.reviews_count > 0" class="mb-2 flex items-center gap-1 md:hidden">
        <svg class="w-3.5 h-3.5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        <span class="text-xs font-medium text-foreground">{{ Number(product.reviews_avg_rating || 0).toFixed(1) }}</span>
        <span class="text-xs text-muted-foreground">({{ product.reviews_count }})</span>
      </div>

      <!-- Stars (desktop) -->
      <div v-if="product.reviews_count > 0" class="mb-3 hidden items-center space-x-1 md:flex">
        <svg v-for="i in 5" :key="i"
          :class="[
            'w-4 h-4',
            i <= Math.round(Number(product.reviews_avg_rating || 0))
              ? 'fill-[#D4AF37] text-[#D4AF37]'
              : 'fill-current text-muted dark:text-muted-foreground/30'
          ]"
          viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        <span class="text-xs text-muted-foreground">({{ Number(product.reviews_avg_rating || 0).toFixed(1) }})</span>
      </div>

      <!-- No reviews yet -->
      <p v-if="!product.reviews_count" class="mb-2 md:mb-3 text-xs text-muted-foreground">
        No reviews yet
      </p>

      <!-- Mobile: Add to cart row -->
      <div class="mt-auto md:hidden">
        <div class="mb-2.5 flex flex-wrap items-baseline gap-x-1.5">
          <span class="text-base font-bold text-[#D4AF37]">${{ Number(product.price).toFixed(2) }}</span>
          <span v-if="product.compare_price" class="text-xs text-muted-foreground line-through">${{ Number(product.compare_price).toFixed(2) }}</span>
        </div>
        <motion.button
          @click="emit('add-to-cart')"
          :disabled="product.stock === 0 || isInCart"
          :whileTap="{ scale: 0.96 }"
          :class="[
            'flex h-10 w-full items-center justify-center gap-1.5 rounded-full text-sm font-semibold shadow-sm transition-luxury',
            product.stock === 0 || isInCart
              ? 'bg-muted text-muted-foreground cursor-not-allowed'
              : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E]'
          ]">
          <svg v-if="!isInCart && product.stock > 0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <svg v-else-if="isInCart" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M20 6 9 17l-5-5"/>
          </svg>
          {{ product.stock === 0 ? 'Sold Out' : isInCart ? 'Added' : 'Add to Cart' }}
        </motion.button>
      </div>

      <!-- Desktop: Price + Cart icon -->
      <div class="mt-auto hidden items-center justify-between md:flex">
        <span class="text-2xl font-bold text-foreground">${{ Number(product.price).toFixed(2) }}</span>
        <motion.button
          @click="emit('add-to-cart')"
          :disabled="product.stock === 0 || isInCart"
          :whileTap="{ scale: 0.9 }"
          :class="[
            'flex h-10 w-10 items-center justify-center rounded-full shadow-sm transition-luxury',
            product.stock === 0 || isInCart
              ? 'bg-muted text-muted-foreground cursor-not-allowed'
              : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E] hover:shadow-md'
          ]">
          <svg v-if="!isInCart && product.stock > 0" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          <svg v-else-if="isInCart" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M20 6 9 17l-5-5"/>
          </svg>
        </motion.button>
      </div>

    </div>
  </motion.div>
</template>


