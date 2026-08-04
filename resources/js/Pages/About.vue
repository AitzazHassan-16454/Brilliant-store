<script setup>
import { Head, Link } from "@inertiajs/vue3"
import AppFooter from "./components/AppFooter.vue"
import { motion } from "motion-v"
import { motionPresets as m, staggerContainer } from "../lib/motion.js"

const values = [
  {
    title: "Craftsmanship",
    text: "We believe in products made to be enjoyed for years, not seasons.",
    icon: "M12 3l2.472 5.008L20 9.36l-4 3.9.944 5.5L12 16l-4.944 2.66L8 13.26l-4-3.9 5.528-1.352L12 3z",
  },
  {
    title: "Honesty",
    text: "Clear pricing, real descriptions, and no surprises at checkout.",
    icon: "M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    title: "Customer First",
    text: "Your experience matters more than any single sale.",
    icon: "M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z",
  },
]

const reasons = [
  {
    title: "Premium Quality",
    description: "Every product is carefully selected and crafted from premium materials, so it looks and lasts as good as it should.",
    icon: "M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z",
  },
  {
    title: "Fast & Secure Shipping",
    description: "Orders are packed with care and shipped quickly, with tracking available at every step of the journey.",
    icon: "M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12",
  },
  {
    title: "Easy Returns",
    description: "Not quite right? Our simple returns process makes it easy to exchange or return your purchase.",
    icon: "M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3",
  },
  {
    title: "Secure Payments",
    description: "Check out with confidence — payments are processed safely and your details are always protected.",
    icon: "M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z",
  },
  {
    title: "Real Customer Care",
    description: "Our support team is always ready to help — from choosing the right product to after-sales assistance.",
    icon: "M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z",
  },
  {
    title: "Order Tracking",
    description: "Every order gets its own tracking code so you can follow it from our shelves all the way to your door.",
    icon: "M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99",
  },
]
</script>

<template>
<Head>
  <title>About Us — Brilliant</title>
  <meta name="description" content="Learn about Brilliant — who we are, our story, our mission, our values, and why customers choose us." />
</Head>

  <div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]" style="font-family:'Inter',system-ui,sans-serif;">

    <!-- HERO -->
    <section class="relative overflow-hidden border-b border-gray-200 dark:border-[#D4AF37]/20">
      <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at top right,rgba(212,175,55,0.12),transparent 55%),radial-gradient(ellipse at bottom left,rgba(212,175,55,0.06),transparent 55%);"></div>
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center relative">
        <motion.div
          :variants="m.staggerContainer(0.12, 0.15)"
          initial="hidden"
          animate="visible"
        >
          <span :variants="m.itemFadeUp(0)" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-widest border border-[#D4AF37]/30 bg-[#D4AF37]/10 text-[#B8960F] dark:text-[#D4AF37]">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 3h12l4 6-10 13L2 9z"/><path d="M2 9h20"/><path d="M12 22L6 9"/><path d="M12 22l6-13"/></svg>
            About Us
          </span>
          <h1 :variants="m.itemFadeUp(1)" class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">
            About <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#D4AF37] via-[#B8960F] to-[#D4AF37]">Brilliant</span>
          </h1>
          <p :variants="m.itemFadeUp(2)" class="mt-5 max-w-2xl mx-auto text-base sm:text-lg leading-relaxed text-gray-500 dark:text-[#A0A0A0]">
            A premium store built on a simple idea: everyday products made exceptionally well.
            Here's who we are, what drives us, and why customers choose us.
          </p>
          <div :variants="m.itemFadeUp(3)" class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Link href="/" class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30">
              Browse the Store
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </Link>
            <Link href="/track-order" class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline border border-black/10 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A]/60 text-gray-900 dark:text-[#F5F5F5]">
              Track an Order
            </Link>
          </div>
        </motion.div>
      </div>
    </section>

    <!-- WHO WE ARE -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
      <div class="grid gap-8 sm:gap-10 lg:grid-cols-2 items-center">
        <motion.div
          :variants="m.fadeRight"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
        >
          <p class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">Who We Are</p>
          <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">Premium products, thoughtfully delivered</h2>
          <div class="mt-5 space-y-4 text-sm sm:text-base leading-relaxed text-gray-500 dark:text-[#A0A0A0]">
            <p>
              Brilliant is a premium online store that brings together carefully curated everyday essentials
              and made-to-order pieces. We focus on quality, honest pricing, and a shopping experience that
              feels personal from start to finish.
            </p>
            <p>
              From browsing the catalog to following your package to the door with a tracking code, everything
              at Brilliant is designed with the customer in mind.
            </p>
          </div>
        </motion.div>
        <motion.div
          :variants="m.staggerContainer(0.08, 0.1)"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
          class="grid grid-cols-2 gap-4 sm:gap-5"
        >
          <motion.div :variants="m.itemScaleIn(0)" class="rounded-2xl p-6 text-center border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
            <p class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-[#D4AF37] to-[#B8960F]">100%</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Curated Products</p>
          </motion.div>
          <motion.div :variants="m.itemScaleIn(1)" class="rounded-2xl p-6 text-center border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
            <p class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-[#D4AF37] to-[#B8960F]">24/7</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Customer Support</p>
          </motion.div>
          <motion.div :variants="m.itemScaleIn(2)" class="rounded-2xl p-6 text-center border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
            <p class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-[#D4AF37] to-[#B8960F]">Tracked</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Every Order</p>
          </motion.div>
          <motion.div :variants="m.itemScaleIn(3)" class="rounded-2xl p-6 text-center border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl">
            <p class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-br from-[#D4AF37] to-[#B8960F]">Easy</p>
            <p class="mt-2 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-[#A0A0A0]">Returns &amp; Exchanges</p>
          </motion.div>
        </motion.div>
      </div>
    </section>

    <!-- OUR STORY -->
    <section class="bg-white/60 dark:bg-[#1A1A1A]/40 border-y border-gray-200 dark:border-[#D4AF37]/20">
      <motion.div
        :variants="m.staggerContainer(0.12, 0.05)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20"
      >
        <div :variants="m.itemFadeUp(0)" class="text-center mb-8">
          <p class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">Our Story</p>
          <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">How Brilliant came to be</h2>
        </div>
        <div :variants="m.itemFadeUp(1)" class="space-y-4 text-sm sm:text-base leading-relaxed text-gray-500 dark:text-[#A0A0A0] text-center max-w-2xl mx-auto">
          <p>
            Brilliant started with a frustration with disposable goods — products that look great for a week
            and then fall apart. So we set out to build a store where quality comes first: curated collections,
            honest materials, and finishes that age beautifully.
          </p>
          <p>
            Today, Brilliant brings together premium everyday essentials and made-to-order pieces, backed by
            dependable shipping and support that treats every customer like a person, not a number.
          </p>
        </div>
      </motion.div>
    </section>

    <!-- OUR MISSION -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
      <motion.div
        :variants="m.scaleIn"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="relative overflow-hidden rounded-3xl p-8 sm:p-14 text-center border border-[#D4AF37]/30 bg-gradient-to-br from-[#0A0A0A] to-[#1A1A1A]"
      >
        <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(circle at center,rgba(212,175,55,0.18),transparent 60%);"></div>
        <div class="relative">
          <p class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">Our Mission</p>
          <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Make every purchase feel Brilliant</h2>
          <p class="mt-4 max-w-2xl mx-auto text-sm sm:text-base leading-relaxed text-white/60">
            We're on a mission to deliver premium quality products with a shopping experience that is simple,
            honest, and enjoyable — so every order feels like a gift, even when it's for you.
          </p>
        </div>
      </motion.div>
    </section>

    <!-- OUR VALUES -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-14 sm:pb-20">
      <div class="text-center mb-10">
        <p class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">Our Values</p>
        <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">What we stand for</h2>
      </div>

      <motion.div
        :variants="m.staggerContainer(0.1, 0.1)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="grid grid-cols-1 sm:grid-cols-3 gap-5"
      >
        <motion.div
          v-for="(value, index) in values" :key="value.title"
          :variants="m.itemScaleIn(index)"
          :whileHover="{ y: -6, transition: m.spring.gentle }"
          class="rounded-2xl p-6 text-center border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A]/60 shadow-xl transition-all duration-300"
        >
          <div class="w-11 h-11 rounded-xl flex items-center justify-center mx-auto mb-4 bg-[#D4AF37]/10">
            <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="value.icon"/>
            </svg>
          </div>
          <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">{{ value.title }}</p>
          <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">{{ value.text }}</p>
        </motion.div>
      </motion.div>
    </section>

    <!-- WHY CHOOSE US -->
    <section class="bg-white/60 dark:bg-[#1A1A1A]/40 border-t border-gray-200 dark:border-[#D4AF37]/20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
        <div class="text-center mb-10">
          <p class="text-xs font-bold uppercase tracking-widest text-[#D4AF37]">Why Choose Us</p>
          <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">The Brilliant difference</h2>
        </div>

        <motion.div
          :variants="m.staggerContainer(0.07, 0.05)"
          initial="hidden"
          whileInView="visible"
          :viewport="m.viewport.once"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <motion.div
            v-for="(reason, index) in reasons" :key="reason.title"
            :variants="m.itemFadeUp(index * 0.05)"
            :whileHover="{ y: -6, boxShadow: '0 16px 40px rgba(212,175,55,0.15)', transition: m.spring.gentle }"
            class="group rounded-2xl p-6 border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] shadow-xl transition-all duration-300"
          >
            <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-[#D4AF37]/10 transition group-hover:bg-[#D4AF37]">
              <svg class="w-5 h-5 text-[#D4AF37] transition group-hover:text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" :d="reason.icon"/>
              </svg>
            </div>
            <p class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">{{ reason.title }}</p>
            <p class="text-sm mt-2 leading-relaxed text-gray-500 dark:text-[#A0A0A0]">{{ reason.description }}</p>
          </motion.div>
        </motion.div>
      </div>
    </section>

    <!-- CTA -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
      <div class="text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">Experience Brilliant for yourself</h2>
        <p class="mt-3 max-w-xl mx-auto text-sm sm:text-base text-gray-500 dark:text-[#A0A0A0]">
          Browse the collection, place an order, and enjoy premium quality delivered to your door.
        </p>
        <Link href="/" class="mt-7 inline-flex items-center gap-2 text-sm font-semibold px-7 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline text-[#0A0A0A] bg-gradient-to-br from-[#D4AF37] via-[#D4AF37] to-[#B8960F] shadow-lg shadow-[#D4AF37]/30">
          Start Shopping
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </Link>
      </div>
    </section>

    <AppFooter />

  </div>
</template>
