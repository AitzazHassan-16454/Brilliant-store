<script setup>
import { ref } from "vue"
import { router, usePage, Head, Link } from "@inertiajs/vue3"
import { useInView } from "../composables/useInView.js"
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

const isInCart = (product) => props.cartProductIds.includes(product.id)

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
    onSuccess: () => success("Added to cart"),
  })
}

const { target: contentRef, isInView: contentVisible } = useInView()
</script>

<template>
<Head>
  <title>My Wishlist — Brilliant</title>
  <meta name="description" content="Your saved favorite products." />
</Head>

  <div ref="contentRef" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">

    <div
      :class="['transition-all duration-500', contentVisible ? 'animate-fade-in-up' : 'opacity-0']"
    >

      <!-- TITLE -->
      <div class="flex items-center gap-3 mb-8 sm:mb-10">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#D4AF37]/10">
          <svg class="w-5 h-5" fill="none" stroke="#D4AF37" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-foreground tracking-tight">My Wishlist</h1>
          <p class="text-sm text-muted-foreground">
            {{ wishlistItems.length }} {{ wishlistItems.length === 1 ? 'item' : 'items' }} saved
          </p>
        </div>
      </div>

      <!-- ITEMS GRID -->
      <div v-if="wishlistItems.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <div
          v-for="(item, index) in wishlistItems"
          :key="item.id"
          class="group relative flex h-full flex-col overflow-hidden rounded-2xl md:rounded-lg border border-border bg-card shadow-luxury transition-luxury hover:border-[#D4AF37]/50 hover:shadow-luxury-hover"
          :style="{ animationDelay: `${Math.min(index * 0.04, 0.4)}s`, animationFillMode: 'both' }"
        >

          <!-- Image -->
          <Link :href="`/products/${item.product.uid}`" class="relative block aspect-square overflow-hidden">
            <div v-if="!loadedImages[item.id]"
              class="shimmer absolute inset-0" />

            <img
              :src="`/storage/${item.product.image}`"
              :alt="item.product.name"
              loading="lazy"
              :class="[
                'h-full w-full object-cover transition-luxury duration-500 group-hover:scale-[1.06]',
                loadedImages[item.id] ? 'opacity-100' : 'opacity-0'
              ]"
              @load="loadedImages[item.id] = true" />

            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-luxury" />

            <!-- Remove from wishlist -->
            <button
              @click.prevent="removeFromWishlist(item)"
              :class="[
                'absolute top-2.5 right-2.5 md:top-3 md:right-3 z-10 flex h-9 w-9 items-center justify-center rounded-full border border-border bg-card/95 backdrop-blur-sm shadow-sm transition-luxury hover:bg-red-500 hover:text-white active:scale-90',
                'translate-y-0 opacity-100 md:translate-y-2 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100'
              ]">
              <svg class="w-4 h-4 fill-red-500 text-red-500" stroke="none" viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
            </button>

            <!-- Quick view overlay -->
            <div class="absolute inset-x-0 bottom-0 flex justify-center pb-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <span class="text-white text-xs font-semibold px-4 py-1.5 rounded-full bg-black/85">Quick View</span>
            </div>
          </Link>

          <!-- Card body -->
          <div class="flex flex-1 flex-col p-3 md:p-4">

            <!-- Category (desktop) -->
            <div class="mb-1.5 md:mb-2">
              <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground md:text-[#D4AF37]">
                {{ item.product.category?.name }}
              </span>
            </div>

            <!-- Title -->
            <Link :href="`/products/${item.product.uid}`"
              class="mb-1.5 md:mb-2 line-clamp-2 min-h-[2.5rem] md:min-h-0 text-sm leading-5 md:text-base md:leading-normal font-semibold text-foreground no-underline transition-colors group-hover:text-[#D4AF37]">
              {{ item.product.name }}
            </Link>

            <!-- Stock badge -->
            <div class="mb-2 md:mb-3">
              <span
                :class="[
                  'text-[10px] font-bold uppercase tracking-wider w-fit',
                  item.product.stock > 0
                    ? 'text-green-600 dark:text-green-400'
                    : 'text-red-500 dark:text-red-400'
                ]"
              >
                {{ item.product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>

            <!-- Mobile: Add to cart row -->
            <div class="mt-auto md:hidden">
              <div class="mb-2.5 flex flex-wrap items-baseline gap-x-1.5">
                <span class="text-base font-bold text-[#D4AF37]">${{ Number(item.product.price).toFixed(2) }}</span>
              </div>
              <button
                @click="addToCart(item.product)"
                :disabled="item.product.stock === 0 || isInCart(item.product)"
                :class="[
                  'flex h-10 w-full items-center justify-center gap-1.5 rounded-full text-sm font-semibold shadow-sm transition-luxury active:scale-[0.98]',
                  item.product.stock === 0 || isInCart(item.product)
                    ? 'bg-muted text-muted-foreground cursor-not-allowed'
                    : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E]'
                ]">
                <svg v-if="!isInCart(item.product)" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path d="M20 6 9 17l-5-5"/>
                </svg>
                {{ item.product.stock === 0 ? 'Sold Out' : isInCart(item.product) ? 'Added' : 'Add to Cart' }}
              </button>
            </div>

            <!-- Desktop: Price + Cart -->
            <div class="mt-auto hidden items-center justify-between md:flex">
              <span class="text-2xl font-bold text-foreground">${{ Number(item.product.price).toFixed(2) }}</span>
              <button
                @click="addToCart(item.product)"
                :disabled="item.product.stock === 0 || isInCart(item.product)"
                :class="[
                  'flex h-10 w-10 items-center justify-center rounded-full shadow-sm transition-luxury',
                  item.product.stock === 0 || isInCart(item.product)
                    ? 'bg-muted text-muted-foreground cursor-not-allowed'
                    : 'bg-[#D4AF37] text-[#0A0A0A] hover:bg-[#B8941E] hover:shadow-md'
                ]">
                <svg v-if="!isInCart(item.product)" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path d="M20 6 9 17l-5-5"/>
                </svg>
              </button>
            </div>

          </div>

        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-20 sm:py-28 flex flex-col items-center gap-4">
        <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10">
          <svg class="w-10 h-10" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </div>
        <div>
          <p class="text-lg font-bold text-foreground">Your wishlist is empty</p>
          <p class="text-sm mt-1 text-muted-foreground">Browse products and save your favorites</p>
        </div>
        <Link href="/"
          class="mt-4 inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30">
          Browse Products
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </Link>
      </div>

    </div>

  </div>
</template>