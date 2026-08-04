<script setup>
import { ref } from "vue"
import { router, usePage, Link, Head } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"
import { useAuthModal } from "../composables/useAuthModal.js"
import AppFooter from "./components/AppFooter.vue"
import ProductCard from "../Components/ProductCard.vue"
import { motion } from "motion-v"
import { motionPresets as m, staggerContainer, staggerItem, itemFadeUp, GESTURE } from "../lib/motion.js"

const heroStagger = staggerContainer(0.12, 0.15)
const heroItem = staggerItem

const page = usePage()
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
  router.post("/wishlist", { product_id: product.id }, { preserveScroll: true })
}
</script>

<template>
<Head>
  <title>Brilliant — Premium Handmade Art</title>
  <meta name="description" content="Discover exclusive luxury products crafted with passion and precision." />
</Head>

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]" style="font-family:'Inter',system-ui,sans-serif;">

  <!-- ══════════════════════════════
       HERO SECTION
  ══════════════════════════════ -->
  <section class="relative overflow-hidden hero-premium-bg hero-gradient-animated">

    <div class="absolute w-96 h-96 rounded-2xl top-[-10%] right-[-5%] hero-glow-orb" style="background:rgba(212,175,55,0.12); filter:blur(80px); animation:glow-pulse 6s cubic-bezier(0.4,0,0.2,1) infinite;"></div>
    <div class="absolute w-80 h-80 rounded-2xl bottom-[-8%] left-[-8%] hero-glow-orb" style="background:rgba(212,175,55,0.08); filter:blur(80px); animation:glow-pulse 6s cubic-bezier(0.4,0,0.2,1) infinite 2.5s;"></div>
    <div class="absolute w-64 h-64 rounded-2xl top-[40%] left-[-4%] hero-glow-orb" style="background:rgba(212,175,55,0.06); filter:blur(80px); animation:glow-pulse 6s cubic-bezier(0.4,0,0.2,1) infinite 4.5s;"></div>

    <div class="hero-rays"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-16 pb-16 sm:pb-20 lg:pb-24 relative z-10">
      <div class="grid items-center gap-10 md:gap-16 lg:grid-cols-2 lg:gap-20">

        <!-- Left: Content -->
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
              Premium Handmade Art
            </motion.div>

            <!-- Heading -->
            <motion.h1
              :variants="heroItem"
              class="text-2xl font-bold leading-tight sm:text-3xl lg:text-4xl xl:text-5xl"
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
          <motion.div :variants="heroItem" class="flex flex-col gap-3 sm:flex-row sm:gap-4">
            <a href="#categories" class="btn-premium group">
              Explore Collection
              <svg class="btn-arrow h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <Link href="/custom-order" class="btn-glass group">
              Custom Order
              <svg class="btn-arrow h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
          </motion.div>

          <!-- Stats -->
          <motion.div :variants="heroItem" class="hidden md:grid md:grid-cols-3 gap-4 pt-2">
            <motion.div
              v-for="(stat, i) in [
                { value: stats.products + '+', label: 'Products' },
                { value: stats.categories + '+', label: 'Categories' },
                { value: stats.orders + '+', label: 'Orders' },
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

        <!-- Right: Hero Image -->
        <motion.div
          :initial="{ opacity: 0, scale: 0.9, y: 24 }"
          :animate="{ opacity: 1, scale: 1, y: 0 }"
          :transition="{ duration: 0.8, ease: m.easeOutExpo, delay: 0.25 }"
          class="relative flex justify-center lg:justify-end"
        >
          <div class="hero-image-group">

            <!-- Breathing glow -->
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
                  <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Brilliant</h2>
                  <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="w-8 h-px bg-[#D4AF37]"></span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#D4AF37]">Premium Store</span>
                    <span class="w-8 h-px bg-[#D4AF37]"></span>
                  </div>
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

  <!-- ══════════════════════════════
       BROWSE BY CATEGORY
  ══════════════════════════════ -->
  <section id="categories" class="py-12 sm:py-20 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <motion.div
        :variants="staggerContainer(0.15)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="text-center mb-8 sm:mb-12"
      >
        <motion.h2 :variants="staggerItem" class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Browse by Category</motion.h2>
        <motion.p :variants="staggerItem" class="mt-3 text-base sm:text-lg text-[#6B6B6B] dark:text-[#A0A0A0]">Explore featured collections curated from the current catalog.</motion.p>
      </motion.div>

      <motion.div
        v-if="categories.length"
        :variants="staggerContainer(0.08)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.onceLight"
        class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
      >
        <motion.div
          v-for="cat in categories" :key="cat.id"
          :variants="staggerItem"
          :whileHover="{ y: -8, transition: m.spring.gentle }"
          class="group relative overflow-hidden rounded-2xl no-underline border border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-md shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10 hover:shadow-xl hover:shadow-[#D4AF37]/15 dark:hover:shadow-[#D4AF37]/20"
        >
          <Link :href="`/categories/${cat.uid}`" class="block no-underline">

          <div v-if="cat.image" class="aspect-[4/3] relative overflow-hidden">
            <img :src="`/storage/${cat.image}`" :alt="cat.name" loading="lazy"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-black/20" />
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <span class="text-lg font-bold text-white">{{ cat.name }}</span>
              <p v-if="cat.description" class="text-sm text-white/80 mt-1 line-clamp-2">{{ cat.description }}</p>
            </div>
          </div>
          <div v-else class="aspect-[4/3] relative overflow-hidden bg-gradient-to-br from-[#D4AF37]/20 via-[#FAF7F2] to-[#D4AF37]/10 dark:from-[#D4AF37]/15 dark:via-[#1A1A1A] dark:to-[#D4AF37]/5">
            <div class="absolute inset-0 opacity-60 dark:opacity-70 category-rays">
            </div>
            <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
              <span class="text-xl font-bold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ cat.name }}</span>
              <p v-if="cat.description" class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0] mt-2 line-clamp-2 max-w-xs">{{ cat.description }}</p>
            </div>
          </div>

          <div class="p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-medium text-[#6B6B6B]/70 dark:text-[#A0A0A0]/70">
                {{ cat.products_count }} {{ cat.products_count === 1 ? 'item' : 'items' }}
              </span>
              <span class="inline-flex items-center text-sm font-semibold text-[#D4AF37]">
                Explore
                <svg class="w-4 h-4 ml-1.5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </span>
            </div>
          </div>
        </Link>
      </motion.div>
    </motion.div>

    <div v-else class="rounded-2xl p-8 text-center border border-gray-200 dark:border-[#D4AF37]/15 bg-white dark:bg-[#1A1A1A] text-[#6B6B6B] dark:text-[#A0A0A0]">
      Categories will appear here once featured collections are active.
    </div>
  </div>
</section>

  <!-- ══════════════════════════════
       TRENDING PRODUCTS
  ══════════════════════════════ -->
  <section id="products" class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <motion.div
        :variants="staggerContainer(0.15)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="mb-8 sm:mb-12 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
      >
        <div>
          <motion.h2 :variants="staggerItem" class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Trending Products</motion.h2>
          <motion.p :variants="staggerItem" class="mt-2 text-base text-[#6B6B6B] dark:text-[#A0A0A0]">A quick look at featured pieces from the live collection.</motion.p>
        </div>
      </motion.div>

      <motion.div
        v-if="trendingProducts?.length"
        :variants="staggerContainer(0.08)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.onceLight"
        class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"
      >
        <motion.div v-for="product in trendingProducts" :key="product.id" :variants="staggerItem">
          <ProductCard
            :product="product"
            :is-in-wishlist="isInWishlist(product)"
            :is-in-cart="isInCart(product)"
            @add-to-cart="addToCart(product)"
            @toggle-wishlist="toggleWishlist(product)" />
        </motion.div>
      </motion.div>

      <div v-else class="rounded-2xl p-8 text-center border border-gray-200 dark:border-[#D4AF37]/15 bg-white dark:bg-[#1A1A1A] text-[#6B6B6B] dark:text-[#A0A0A0]">
        Featured products will appear here once they are available.
      </div>
    </div>
  </section>

  <!-- ══════════════════════════════
       FEATURES
  ══════════════════════════════ -->
  <section class="py-12 sm:py-20 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 gap-6 sm:gap-8 lg:grid-cols-4">

        <motion.div
          :whileHover="{ y: -4, transition: m.spring.gentle }"
          class="text-center"
        >
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 transition-all duration-300 group-hover:bg-[#D4AF37]">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Handcrafted</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Every piece is created with careful finishing and a human touch.</p>
        </motion.div>

        <motion.div
          :whileHover="{ y: -4, transition: m.spring.gentle }"
          class="text-center"
        >
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 transition-all duration-300 group-hover:bg-[#D4AF37]">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 15l-2 5-6-2 2-6-5-2 5-2-2-6 6 2 2-5 2 5 6-2-2 6 5 2-5 2 2 6-6-2-2 5z"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Premium Quality</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Materials and detailing stay aligned with a polished final result.</p>
        </motion.div>

        <motion.div
          :whileHover="{ y: -4, transition: m.spring.gentle }"
          class="text-center"
        >
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 transition-all duration-300 group-hover:bg-[#D4AF37]">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Made with Care</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Each item is shaped with attention to detail, balance, and precision.</p>
        </motion.div>

        <motion.div
          :whileHover="{ y: -4, transition: m.spring.gentle }"
          class="text-center"
        >
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15 transition-all duration-300 group-hover:bg-[#D4AF37]">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/><circle cx="12" cy="12" r="4"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Curated Collection</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Featured works reflect the active products and categories in your catalog.</p>
        </motion.div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════
       TESTIMONIALS
  ══════════════════════════════ -->
  <section class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <motion.div
        :variants="staggerContainer(0.15)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="mb-10 sm:mb-12 text-center"
      >
        <motion.h2 :variants="staggerItem" class="mb-3 text-3xl font-bold leading-tight text-foreground sm:mb-4 sm:text-4xl">
          <span class="inline-flex flex-wrap items-baseline gap-x-[0.24em] gap-y-1 justify-center">
            <span>Client</span>
            <span class="text-gold-gradient">Testimonials</span>
          </span>
        </motion.h2>
        <motion.p :variants="staggerItem" class="max-w-2xl text-muted-foreground leading-7 sm:text-lg mx-auto">Recent feedback from collectors and watch enthusiasts.</motion.p>
      </motion.div>

      <motion.div
        :variants="staggerContainer(0.15)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.onceLight"
        class="grid gap-6 md:grid-cols-3"
      >
        <motion.div
          :variants="staggerItem"
          :whileHover="{ y: -6, transition: m.spring.gentle }"
          class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover"
        >
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"The finishing, accuracy, and presentation were even better in person. It instantly became my daily wear."</p>
          <p class="font-semibold text-foreground">Sarah Johnson</p>
        </motion.div>

        <motion.div
          :variants="staggerItem"
          :whileHover="{ y: -6, transition: m.spring.gentle }"
          class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover"
        >
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"A premium experience. The craftsmanship and packaging worked perfectly for a luxury gift."</p>
          <p class="font-semibold text-foreground">Michael Chen</p>
        </motion.div>

        <motion.div
          :variants="staggerItem"
          :whileHover="{ y: -6, transition: m.spring.gentle }"
          class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover"
        >
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"The design direction feels thoughtful and consistent. Every collection looks professionally curated."</p>
          <p class="font-semibold text-foreground">Emma Williams</p>
        </motion.div>
      </motion.div>
    </div>
  </section>

  <!-- ══════════════════════════════
       CTA SECTION
  ══════════════════════════════ -->
  <section id="custom-order" class="py-12 sm:py-20 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-4xl mx-auto px-4 text-center sm:px-6 lg:px-8">
      <motion.div
        :variants="m.scaleIn"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="rounded-2xl p-8 sm:p-12 border border-gray-200 dark:border-[#D4AF37]/20 bg-gradient-to-br from-[#D4AF37]/10 to-slate-900/5 dark:from-[#D4AF37]/10 dark:to-[#1A1A1A]/50 shadow-lg shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/20 hover:shadow-xl hover:shadow-[#D4AF37]/20 dark:hover:shadow-[#D4AF37]/25 transition-shadow duration-300"
      >

        <motion.h2
          :variants="m.fadeUp"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
          class="mb-4 text-3xl font-bold sm:text-4xl text-[#1A1A1A] dark:text-[#F5F5F5]"
        >Ready to Commission Your Custom Order?</motion.h2>
        <motion.p
          :variants="m.fadeUp"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
          :transition="{ duration: m.duration.base, ease: m.easeOutExpo, delay: 0.1 }"
          class="mx-auto mb-8 max-w-2xl text-lg text-[#6B6B6B] dark:text-[#A0A0A0]"
        >Share your idea with us and we will help shape the right piece for your space, gift, or collection.</motion.p>

        <motion.div
          :variants="m.fadeUp"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
          :transition="{ duration: m.duration.base, ease: m.easeOutExpo, delay: 0.2 }"
          class="flex flex-col justify-center gap-4 sm:flex-row"
        >
          <Link href="/custom-order"
            class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-base font-medium transition-all duration-300 sm:px-8 sm:py-4 sm:text-lg no-underline bg-[#D4AF37] dark:bg-[#D4AF37] text-white dark:text-[#0A0A0A] hover:bg-[#C9A032] dark:hover:bg-[#C9A032] shadow-lg shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/30 hover:shadow-xl hover:shadow-[#D4AF37]/30 dark:hover:shadow-[#D4AF37]/40">
            Start Custom Order
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </Link>
          <Link href="/cart"
            class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-base font-medium transition-all duration-300 sm:px-8 sm:py-4 sm:text-lg no-underline border border-gray-200 dark:border-[#D4AF37]/25 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10 hover:shadow-md hover:shadow-[#D4AF37]/10 dark:hover:shadow-[#D4AF37]/20">
            Contact Us
          </Link>
        </motion.div>
      </motion.div>
    </div>
  </section>

  <AppFooter />

</div>
</template>

<style>
html {
  scroll-behavior: smooth;
  scroll-padding-top: 5rem;
}

.dark .elegance-text {
  background: linear-gradient(135deg, #D4AF37, #F4E5C2) !important;
  -webkit-background-clip: text !important;
  background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
}

.dark .hero-glow-orb {
  background: rgba(212, 175, 55, 0.15) !important;
}

.dark .hero-rays {
  background: repeating-linear-gradient(55deg, transparent, transparent 60px, rgba(212, 175, 55, 0.06) 60px, rgba(212, 175, 55, 0.06) 62px) !important;
}

.dark .hero-gradient-animated {
  background: linear-gradient(135deg, #0A0A0A, #1A1A1A, #111, #1A1A1A, #0A0A0A) !important;
  background-size: 200% 200% !important;
  animation: gradient-shift 20s cubic-bezier(0.4, 0, 0.2, 1) infinite !important;
}

.dark .hero-stat-card {
  background: rgba(26, 26, 26, 0.6) !important;
  border-color: rgba(212, 175, 55, 0.15) !important;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2) !important;
}

.dark .hero-stat-card:hover {
  box-shadow: 0 12px 40px rgba(212, 175, 55, 0.1) !important;
  border-color: rgba(212, 175, 55, 0.3) !important;
}

.category-rays {
  background-image: repeating-linear-gradient(45deg, transparent, transparent 25px, rgba(212, 175, 55, 0.12) 25px, rgba(212, 175, 55, 0.12) 26px);
}

.dark .category-rays {
  background-image: repeating-linear-gradient(45deg, transparent, transparent 25px, rgba(212, 175, 55, 0.25) 25px, rgba(212, 175, 55, 0.25) 26px) !important;
}
</style>

<style scoped>
@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.hero-gradient-animated {
  background: linear-gradient(135deg, #FAF7F2, #F5F1E8, #FFF, #F5F1E8, #FAF7F2);
  background-size: 200% 200%;
  animation: gradient-shift 30s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.25; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.05); }
}

.hero-glow-orb {
  pointer-events: none;
  will-change: transform, opacity;
}

@keyframes rays-sweep {
  0% { transform: translate(-30%) translateY(-20%) rotate(0); }
  100% { transform: translate(30%) translateY(20%) rotate(3deg); }
}

.hero-rays {
  position: absolute;
  top: -50%;
  right: -50%;
  bottom: -50%;
  left: -50%;
  background: repeating-linear-gradient(55deg, transparent, transparent 60px, rgba(212, 175, 55, 0.03) 60px, rgba(212, 175, 55, 0.03) 62px);
  animation: rays-sweep 40s linear infinite;
  pointer-events: none;
}

.hero-glow {
  position: absolute;
  width: 280px;
  height: 280px;
  border-radius: 9999px;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.28), transparent 65%);
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
  background: #1a1f36;
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-radius: 28px;
  padding: 2.5rem 2.75rem;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  animation: emblem-breathe 5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes emblem-breathe {
  0%, 100% { box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 0 30px rgba(212, 175, 55, 0.08); }
  50% { box-shadow: 0 28px 70px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 0 50px rgba(212, 175, 55, 0.22); }
}

.hero-sparkle {
  position: absolute;
  color: #D4AF37;
  animation: sparkle-twinkle 3s ease-in-out infinite;
}

@keyframes sparkle-twinkle {
  0%, 100% { opacity: 0.2; transform: scale(0.6); }
  50% { opacity: 1; transform: scale(1); }
}

.hero-stat-card {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  padding: 1.25rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
  transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.4s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.3s ease;
  will-change: transform;
}

.hero-stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
  border-color: rgba(212, 175, 55, 0.3);
}

.hero-stat-value {
  font-size: 1.875rem;
  font-weight: 700;
  line-height: 1.2;
  color: #1A1A1A;
}

.dark .hero-stat-value {
  color: #F5F5F5;
}

.hero-stat-label {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6B6B6B;
  font-weight: 400;
}

.dark .hero-stat-label {
  color: #A0A0A0;
}

@media (prefers-reduced-motion: reduce) {
  .hero-gradient-animated,
  .hero-glow-orb,
  .hero-rays,
  .hero-glow,
  .hero-emblem,
  .hero-emblem-card,
  .hero-sparkle {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    opacity: 1 !important;
  }
  .hero-stat-card {
    transform: none !important;
    box-shadow: none !important;
    border-color: inherit !important;
    opacity: 1 !important;
  }
}
</style>
