<script setup>
import { ref } from "vue"
import { router, usePage, Link, Head } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"
import { useAuthModal } from "../composables/useAuthModal.js"
import AppFooter from "./components/AppFooter.vue"
import ProductCard from "../Components/ProductCard.vue"

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

    <div class="absolute w-96 h-96 rounded-full top-[-10%] right-[-5%] hero-glow-orb" style="background:rgba(212,175,55,0.12); filter:blur(80px); animation:glow-pulse 6s ease-in-out infinite;"></div>
    <div class="absolute w-80 h-80 rounded-full bottom-[-8%] left-[-8%] hero-glow-orb" style="background:rgba(212,175,55,0.08); filter:blur(80px); animation:glow-pulse 6s ease-in-out infinite 2.5s;"></div>
    <div class="absolute w-64 h-64 rounded-full top-[40%] left-[-4%] hero-glow-orb" style="background:rgba(212,175,55,0.06); filter:blur(80px); animation:glow-pulse 6s ease-in-out infinite 4s;"></div>

    <div class="hero-rays"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-16 pb-16 sm:pb-20 lg:pb-24 relative z-10">
      <div class="grid items-center gap-10 md:gap-16 lg:grid-cols-2 lg:gap-20">

        <!-- Left: Content -->
        <div class="space-y-4 md:space-y-5 lg:space-y-6">

          <div class="space-y-3 md:space-y-4">

            <!-- Badge -->
            <div class="hero-badge">
              <span class="hero-badge-dot">
                <span class="hero-badge-dot-ring"></span>
                <span class="hero-badge-dot-core"></span>
              </span>
              Premium Handmade Art
            </div>

            <!-- Heading -->
            <h1 class="text-lg font-bold leading-tight sm:text-xl lg:text-2xl xl:text-3xl">
              <span class="text-[#1A1A1A] dark:text-[#F5F5F5]">Where Precision Meets</span>
              <span class="block mt-0.5 text-gold-gradient">Elegance</span>
            </h1>

            <!-- Subtitle -->
            <p class="max-w-lg text-xs leading-relaxed sm:text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">
              Discover exclusive luxury products crafted with passion, precision, and excellence. Every collection is carefully curated to feel timeless.
            </p>
          </div>

          <!-- CTA Buttons -->
          <div class="flex flex-col gap-3 sm:flex-row sm:gap-4">
            <a href="#categories" class="btn-premium group">
              Explore Collection
              <svg class="btn-arrow h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <Link href="/custom-order" class="btn-glass group">
              Custom Order
              <svg class="btn-arrow h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
          </div>

          <!-- Stats -->
          <div class="hidden md:grid md:grid-cols-3 gap-4 pt-2">
            <div class="hero-stat-card">
              <div class="hero-stat-value">{{ stats.products }}+</div>
              <div class="hero-stat-label">Products</div>
            </div>
            <div class="hero-stat-card">
              <div class="hero-stat-value">{{ stats.categories }}+</div>
              <div class="hero-stat-label">Categories</div>
            </div>
            <div class="hero-stat-card">
              <div class="hero-stat-value">{{ stats.orders }}+</div>
              <div class="hero-stat-label">Orders</div>
            </div>
          </div>
        </div>

        <!-- Right: Hero Image -->
        <div class="relative flex justify-center lg:justify-end">
          <div class="hero-image-group">

            <!-- Glow backdrop -->
            <div class="hero-glow-backdrop"></div>

            <!-- Orbit dots -->
            <div class="hero-orbit">
              <div class="hero-orbit-dot hero-orbit-dot-1"></div>
              <div class="hero-orbit-dot hero-orbit-dot-2"></div>
              <div class="hero-orbit-dot hero-orbit-dot-3"></div>
              <div class="hero-orbit-dot hero-orbit-dot-4"></div>
            </div>

            <div class="hero-image-wrapper">
              <!-- Ring wheel -->
              <div class="hero-ring-wheel"></div>

              <!-- Diamond icon -->
              <div class="hero-image-ring">
                <div class="hero-ring-border"></div>
                <div class="relative w-full h-full flex items-center justify-center">
                  <div class="relative w-40 h-40 sm:w-52 sm:h-52 lg:w-64 lg:h-64 z-10 flex items-center justify-center">
                    <!-- Outer ring -->
                    <div class="absolute inset-0 rounded-full border-2 border-dashed hero-ring-outer" style="border-color:rgba(212,175,55,0.2); animation:spin 25s linear infinite;"></div>

                    <!-- Main shape -->
                    <div class="relative w-28 h-28 sm:w-36 sm:h-36 lg:w-44 lg:h-44 rounded-2xl flex items-center justify-center hero-diamond-box"
                      style="background:linear-gradient(135deg,#D4AF37,#B8941E); box-shadow:0 12px 40px rgba(212,175,55,0.3),0 2px 8px rgba(0,0,0,0.06); transform:rotate(45deg); animation:box-pulse 4s ease-in-out infinite;">

                      <!-- Shine overlay -->
                      <div class="absolute inset-0 rounded-2xl overflow-hidden">
                        <div class="absolute inset-0 hero-shine"></div>
                      </div>

                      <!-- Diamond icon -->
                      <svg class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 -rotate-45 transition-transform duration-500 hover:scale-110" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M6 3h12l4 6-10 13L2 9z"/>
                        <path d="M2 9h20"/>
                        <path d="M12 22L6 9"/>
                        <path d="M12 22l6-13"/>
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="hero-image-overlay"></div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════
       BROWSE BY CATEGORY
  ══════════════════════════════ -->
  <section id="categories" class="py-12 sm:py-20 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="text-center mb-8 sm:mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Browse by Category</h2>
        <p class="mt-3 text-base sm:text-lg text-[#6B6B6B] dark:text-[#A0A0A0]">Explore featured collections curated from the current catalog.</p>
      </div>

      <div v-if="categories.length" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <Link v-for="cat in categories" :key="cat.id" :href="`/categories/${cat.uid}`"
          class="group relative overflow-hidden rounded-2xl transition-all duration-500 hover:-translate-y-2 no-underline border border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-md shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10 hover:shadow-xl hover:shadow-[#D4AF37]/15 dark:hover:shadow-[#D4AF37]/20">

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
      </div>

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

      <div class="mb-8 sm:mb-12 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
          <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Trending Products</h2>
          <p class="mt-2 text-base text-[#6B6B6B] dark:text-[#A0A0A0]">A quick look at featured pieces from the live collection.</p>
        </div>
      </div>

      <div v-if="trendingProducts?.length" class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
        <ProductCard
          v-for="product in trendingProducts"
          :key="product.id"
          :product="product"
          :is-in-wishlist="isInWishlist(product)"
          :is-in-cart="isInCart(product)"
          @add-to-cart="addToCart(product)"
          @toggle-wishlist="toggleWishlist(product)" />
      </div>

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

        <div class="text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Handcrafted</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Every piece is created with careful finishing and a human touch.</p>
        </div>

        <div class="text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 15l-2 5-6-2 2-6-5-2 5-2-2-6 6 2 2-5 2 5 6-2-2 6 5 2-5 2 2 6-6-2-2 5z"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Premium Quality</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Materials and detailing stay aligned with a polished final result.</p>
        </div>

        <div class="text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Made with Care</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Each item is shaped with attention to detail, balance, and precision.</p>
        </div>

        <div class="text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#D4AF37]/10 dark:bg-[#D4AF37]/15">
            <svg class="w-7 h-7" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/><circle cx="12" cy="12" r="4"/></svg>
          </div>
          <h3 class="mb-2 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Curated Collection</h3>
          <p class="text-sm text-[#6B6B6B] dark:text-[#A0A0A0]">Featured works reflect the active products and categories in your catalog.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- ══════════════════════════════
       TESTIMONIALS
  ══════════════════════════════ -->
  <section class="py-12 sm:py-20 hero-gradient-static dark:hero-gradient-static border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="mb-10 sm:mb-12 text-center">
        <h2 class="mb-3 text-3xl font-bold leading-tight text-foreground sm:mb-4 sm:text-4xl">
          <span class="inline-flex flex-wrap items-baseline gap-x-[0.24em] gap-y-1 justify-center">
            <span>Client</span>
            <span class="text-gold-gradient">Testimonials</span>
          </span>
        </h2>
        <p class="max-w-2xl text-muted-foreground leading-7 sm:text-lg mx-auto">Recent feedback from collectors and watch enthusiasts.</p>
      </div>

      <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover">
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"The finishing, accuracy, and presentation were even better in person. It instantly became my daily wear."</p>
          <p class="font-semibold text-foreground">Sarah Johnson</p>
        </div>

        <div class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover">
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"A premium experience. The craftsmanship and packaging worked perfectly for a luxury gift."</p>
          <p class="font-semibold text-foreground">Michael Chen</p>
        </div>

        <div class="rounded-xl border border-border bg-[#F5F1E8] dark:bg-[#1A1A1A] p-6 shadow-luxury transition-luxury hover:shadow-luxury-hover">
          <div class="mb-4 flex items-center space-x-1">
            <svg v-for="i in 5" :key="i" class="w-5 h-5 fill-[#D4AF37] text-[#D4AF37]" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <p class="mb-4 italic text-muted-foreground">"The design direction feels thoughtful and consistent. Every collection looks professionally curated."</p>
          <p class="font-semibold text-foreground">Emma Williams</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ══════════════════════════════
       CTA SECTION
  ══════════════════════════════ -->
  <section id="custom-order" class="py-12 sm:py-20 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-t border-gray-200/50 dark:border-[#D4AF37]/10 shadow-xl">
    <div class="max-w-4xl mx-auto px-4 text-center sm:px-6 lg:px-8">
      <div class="rounded-2xl p-8 sm:p-12 border border-gray-200 dark:border-[#D4AF37]/20 bg-gradient-to-br from-[#D4AF37]/10 to-slate-900/5 dark:from-[#D4AF37]/10 dark:to-[#1A1A1A]/50 shadow-lg shadow-[#D4AF37]/10 dark:shadow-[#D4AF37]/20 hover:shadow-xl hover:shadow-[#D4AF37]/20 dark:hover:shadow-[#D4AF37]/25 transition-shadow duration-300">

        <h2 class="mb-4 text-3xl font-bold sm:text-4xl text-[#1A1A1A] dark:text-[#F5F5F5]">Ready to Commission Your Custom Order?</h2>
        <p class="mx-auto mb-8 max-w-2xl text-lg text-[#6B6B6B] dark:text-[#A0A0A0]">Share your idea with us and we will help shape the right piece for your space, gift, or collection.</p>

        <div class="flex flex-col justify-center gap-4 sm:flex-row">
          <Link href="/custom-order"
            class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-base font-medium transition-all duration-300 sm:px-8 sm:py-4 sm:text-lg no-underline bg-[#D4AF37] dark:bg-[#D4AF37] text-white dark:text-[#0A0A0A] hover:bg-[#C9A032] dark:hover:bg-[#C9A032] shadow-lg shadow-[#D4AF37]/20 dark:shadow-[#D4AF37]/30 hover:shadow-xl hover:shadow-[#D4AF37]/30 dark:hover:shadow-[#D4AF37]/40">
            Start Custom Order
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </Link>
          <Link href="/cart"
            class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-base font-medium transition-all duration-300 sm:px-8 sm:py-4 sm:text-lg no-underline border border-gray-200 dark:border-[#D4AF37]/25 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm shadow-[#D4AF37]/5 dark:shadow-[#D4AF37]/10 hover:shadow-md hover:shadow-[#D4AF37]/10 dark:hover:shadow-[#D4AF37]/20">
            Contact Us
          </Link>
        </div>
      </div>
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

.dark .hero-diamond-box {
  background: linear-gradient(135deg, #D4AF37, #B8941E) !important;
  box-shadow: 0 12px 40px rgba(212, 175, 55, 0.4), 0 2px 8px rgba(0, 0, 0, 0.15) !important;
}

.dark .hero-ring-outer {
  border-color: rgba(212, 175, 55, 0.3) !important;
}

.dark .hero-gradient-animated {
  background: linear-gradient(135deg, #0A0A0A, #1A1A1A, #111, #1A1A1A, #0A0A0A) !important;
  background-size: 200% 200% !important;
  animation: gradient-shift 12s ease-in-out infinite !important;
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

.dark .hero-glow-backdrop {
  background: radial-gradient(circle, rgba(212, 175, 55, 0.25) 0%, rgba(212, 175, 55, 0.08) 40%, transparent 70%) !important;
}

.dark .hero-ring-border {
  border-color: rgba(212, 175, 55, 0.35) !important;
  box-shadow: 0 0 15px rgba(212, 175, 55, 0.1), inset 0 0 15px rgba(212, 175, 55, 0.05) !important;
}

.dark .hero-image-group:hover .hero-ring-border {
  border-color: rgba(212, 175, 55, 0.65) !important;
  box-shadow: 0 0 30px rgba(212, 175, 55, 0.25), inset 0 0 25px rgba(212, 175, 55, 0.1) !important;
}

.dark .hero-ring-wheel {
  border-color: rgba(212, 175, 55, 0.2) !important;
  box-shadow: 0 0 8px rgba(212, 175, 55, 0.04), inset 0 0 8px rgba(212, 175, 55, 0.04) !important;
}

.dark .hero-ring-wheel:before,
.dark .hero-ring-wheel:after {
  background: #D4AF37 !important;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.6), 0 0 24px rgba(212, 175, 55, 0.2) !important;
}

.dark .hero-image-group:hover .hero-ring-wheel {
  border-color: rgba(212, 175, 55, 0.55) !important;
  box-shadow: 0 0 25px rgba(212, 175, 55, 0.15), inset 0 0 25px rgba(212, 175, 55, 0.1) !important;
}

.dark .hero-image-group:hover .hero-ring-wheel:before,
.dark .hero-image-group:hover .hero-ring-wheel:after {
  box-shadow: 0 0 18px rgba(212, 175, 55, 0.8), 0 0 35px rgba(212, 175, 55, 0.3) !important;
}

.dark .hero-orbit-dot {
  background: #D4AF37 !important;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.7), 0 0 25px rgba(212, 175, 55, 0.3) !important;
}

.dark .hero-image-group:hover .hero-orbit-dot {
  box-shadow: 0 0 18px rgba(212, 175, 55, 0.9), 0 0 35px rgba(212, 175, 55, 0.4) !important;
}

.dark .hero-image-ring:after {
  background: linear-gradient(105deg, transparent 40%, rgba(212, 175, 55, 0.06) 45%, rgba(212, 175, 55, 0.1) 50%, rgba(212, 175, 55, 0.06) 55%, transparent 60%) !important;
}

.dark .hero-image-overlay {
  background: linear-gradient(to top, rgba(0, 0, 0, 0.4) 0%, transparent 50%) !important;
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
  animation: gradient-shift 20s ease-in-out infinite;
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.2; transform: scale(1); }
  50% { opacity: 0.45; transform: scale(1.06); }
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
  animation: rays-sweep 25s linear infinite;
  pointer-events: none;
}

@keyframes spin {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

@keyframes box-pulse {
  0%, 100% { transform: rotate(45deg) scale(1); box-shadow:0 12px 40px rgba(212,175,55,0.3),0 2px 8px rgba(0,0,0,0.06); }
  50% { transform: rotate(45deg) scale(1.03); box-shadow:0 16px 50px rgba(212,175,55,0.35),0 4px 12px rgba(0,0,0,0.08); }
}

@keyframes shine-sweep {
  0%, 100% { background-position: 200% center; }
  50% { background-position: -200% center; }
}

.hero-shine {
  background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.2) 45%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.2) 55%, transparent 60%);
  background-size: 250% 100%;
  animation: shine-sweep 6s ease-in-out infinite;
}

@keyframes hero-float {
  0%, 100% { transform: translateY(0); }
  25% { transform: translateY(-6px); }
  50% { transform: translateY(-2px); }
  75% { transform: translateY(3px); }
}

.hero-image-wrapper {
  animation: hero-float 12s ease-in-out infinite;
  will-change: transform;
}

.hero-image-group {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-glow-backdrop {
  position: absolute;
  width: 90%;
  height: 90%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.25) 0%, rgba(212, 175, 55, 0.08) 40%, transparent 70%);
  animation: glow-pulse 5s ease-in-out infinite;
  pointer-events: none;
  transition: opacity 0.6s ease;
}

.hero-image-group:hover .hero-glow-backdrop {
  opacity: 1.4;
}

.hero-image-ring {
  position: relative;
  width: 100%;
  max-width: 320px;
  min-width: 320px;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  overflow: hidden;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-image-group:hover .hero-image-ring img {
  transform: scale(1.08);
}

.hero-ring-border {
  position: absolute;
  top: -6px; right: -6px; bottom: -6px; left: -6px;
  border-radius: 50%;
  border: 1.5px solid rgba(212, 175, 55, 0.35);
  box-shadow: 0 0 15px rgba(212, 175, 55, 0.1), inset 0 0 15px rgba(212, 175, 55, 0.05);
  pointer-events: none;
  transition: border-color 0.5s ease, box-shadow 0.5s ease;
  z-index: 3;
}

.hero-image-group:hover .hero-ring-border {
  border-color: rgba(212, 175, 55, 0.65);
  box-shadow: 0 0 30px rgba(212, 175, 55, 0.25), inset 0 0 25px rgba(212, 175, 55, 0.1);
}

@keyframes spin-wheel {
  0% { transform: translate(-50%, -50%) rotate(0); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

.hero-ring-wheel {
  position: absolute;
  top: 50%;
  left: 50%;
  width: calc(100% + 100px);
  height: calc(100% + 100px);
  transform: translate(-50%, -50%);
  border-radius: 50%;
  border: 1.5px solid rgba(212, 175, 55, 0.2);
  animation: spin-wheel 15s linear infinite;
  pointer-events: none;
  z-index: 3;
  transition: border-color 0.5s ease, width 0.5s ease, height 0.5s ease;
  box-shadow: 0 0 8px rgba(212, 175, 55, 0.04), inset 0 0 8px rgba(212, 175, 55, 0.04);
}

.hero-ring-wheel:before,
.hero-ring-wheel:after {
  content: "";
  position: absolute;
  width: 10px;
  height: 10px;
  margin-left: -5px;
  margin-top: -5px;
  border-radius: 50%;
  background: #D4AF37;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.6), 0 0 24px rgba(212, 175, 55, 0.2);
  transition: box-shadow 0.5s ease, transform 0.5s ease;
}

.hero-ring-wheel:before { top: 50%; left: 0; }
.hero-ring-wheel:after { top: 50%; left: 100%; }

.hero-image-group:hover .hero-ring-wheel {
  border-color: rgba(212, 175, 55, 0.55);
  width: calc(100% + 110px);
  height: calc(100% + 110px);
  box-shadow: 0 0 25px rgba(212, 175, 55, 0.15), inset 0 0 25px rgba(212, 175, 55, 0.1);
}

.hero-image-group:hover .hero-ring-wheel:before,
.hero-image-group:hover .hero-ring-wheel:after {
  box-shadow: 0 0 18px rgba(212, 175, 55, 0.8), 0 0 35px rgba(212, 175, 55, 0.3);
  transform: scale(1.2);
}

.hero-image-overlay {
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  border-radius: 50%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.2) 0%, transparent 50%);
  pointer-events: none;
  z-index: 2;
}

.hero-orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  z-index: 4;
  pointer-events: none;
}

@keyframes hero-orbit-1 {
  0% { transform: rotate(0deg) translateX(150px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(150px) rotate(-360deg); }
}

@keyframes hero-orbit-2 {
  0% { transform: rotate(0deg) translateX(165px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(165px) rotate(-360deg); }
}

@keyframes hero-orbit-3 {
  0% { transform: rotate(0deg) translateX(140px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(140px) rotate(-360deg); }
}

@keyframes hero-orbit-4 {
  0% { transform: rotate(0deg) translateX(160px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(160px) rotate(-360deg); }
}

.hero-orbit-dot {
  position: absolute;
  width: 10px;
  height: 10px;
  margin: -5px 0 0 -5px;
  border-radius: 50%;
  background: #D4AF37;
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.7), 0 0 25px rgba(212, 175, 55, 0.3);
  transition: box-shadow 0.5s ease, transform 0.5s ease;
  will-change: transform;
}

.hero-image-group:hover .hero-orbit-dot {
  box-shadow: 0 0 18px rgba(212, 175, 55, 0.9), 0 0 35px rgba(212, 175, 55, 0.4);
}

.hero-orbit-dot-1 { animation: hero-orbit-1 5s linear infinite; }
.hero-image-group:hover .hero-orbit-dot-1 { animation-duration: 3s; }
.hero-orbit-dot-2 { animation: hero-orbit-2 7s linear infinite reverse; }
.hero-image-group:hover .hero-orbit-dot-2 { animation-duration: 4.5s; }
.hero-orbit-dot-3 { animation: hero-orbit-3 4.5s linear infinite; }
.hero-image-group:hover .hero-orbit-dot-3 { animation-duration: 2.8s; }
.hero-orbit-dot-4 { animation: hero-orbit-4 8.5s linear infinite reverse; }
.hero-image-group:hover .hero-orbit-dot-4 { animation-duration: 5.5s; }

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

.hero-image-ring:after {
  content: "";
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  border-radius: 50%;
  background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.15) 45%, rgba(255, 255, 255, 0.2) 50%, rgba(255, 255, 255, 0.15) 55%, transparent 60%);
  animation: img-shine 8s ease-in-out infinite;
  animation-delay: 2s;
  pointer-events: none;
  z-index: 2;
  mix-blend-mode: overlay;
}

@keyframes img-shine {
  0% { transform: translate(-100%) rotate(20deg); }
  30% { transform: translate(100%) rotate(20deg); }
  100% { transform: translate(100%) rotate(20deg); }
}

@media (max-width: 1023px) {
  .hero-image-ring {
    max-width: 280px;
    min-width: 280px;
  }
  @keyframes hero-orbit-1 { 0% { transform: rotate(0deg) translateX(130px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(130px) rotate(-360deg); } }
  @keyframes hero-orbit-2 { 0% { transform: rotate(0deg) translateX(145px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(145px) rotate(-360deg); } }
  @keyframes hero-orbit-3 { 0% { transform: rotate(0deg) translateX(120px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(120px) rotate(-360deg); } }
  @keyframes hero-orbit-4 { 0% { transform: rotate(0deg) translateX(140px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(140px) rotate(-360deg); } }
}

@media (max-width: 639px) {
  .hero-image-ring {
    max-width: 240px;
    min-width: 240px;
  }
  @keyframes hero-orbit-1 { 0% { transform: rotate(0deg) translateX(110px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(110px) rotate(-360deg); } }
  @keyframes hero-orbit-2 { 0% { transform: rotate(0deg) translateX(125px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(125px) rotate(-360deg); } }
  @keyframes hero-orbit-3 { 0% { transform: rotate(0deg) translateX(100px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(100px) rotate(-360deg); } }
  @keyframes hero-orbit-4 { 0% { transform: rotate(0deg) translateX(118px) rotate(0deg); } 100% { transform: rotate(360deg) translateX(118px) rotate(-360deg); } }
}

@media (prefers-reduced-motion: reduce) {
  .hero-gradient-animated,
  .hero-glow-orb,
  .hero-rays,
  .hero-image-wrapper,
  .hero-glow-backdrop,
  .hero-orbit-dot,
  .hero-ring-wheel,
  .hero-image-ring:after {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    opacity: 1 !important;
  }
  .hero-stat-card,
  .hero-image-group:hover .hero-ring-border,
  .hero-image-group:hover .hero-glow-backdrop,
  .hero-image-group:hover .hero-image-ring img {
    transform: none !important;
    box-shadow: none !important;
    border-color: inherit !important;
    opacity: 1 !important;
  }
}
</style>
