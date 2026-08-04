<script setup>
import { ref } from "vue"
import { Head } from "@inertiajs/vue3"
import AppFooter from "./components/AppFooter.vue"
import { AnimatePresence, motion } from "motion-v"
import { motionPresets as m } from "../lib/motion.js"

const props = defineProps({
  faqs: Array,
})

const openIndex = ref(0)

const toggle = (index) => {
  openIndex.value = openIndex.value === index ? null : index
}

const answerVariants = {
  hidden: { opacity: 0, height: 0 },
  visible: { opacity: 1, height: "auto", transition: { duration: 0.32, ease: m.easeOutExpo } },
  exit: { opacity: 0, height: 0, transition: { duration: 0.22, ease: "easeIn" } },
}
</script>

<template>
  <Head title="FAQ" />

  <div class="min-h-screen py-8 sm:py-12">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

      <!-- Section Header -->
      <motion.div
        :variants="m.staggerContainer(0.12, 0.1)"
        initial="hidden"
        animate="visible"
        class="text-center mb-10"
      >
        <div :variants="m.itemFadeUp(0)" class="inline-flex items-center gap-2 rounded-full border border-[#D4AF37]/30 bg-[#D4AF37]/10 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#D4AF37] dark:text-[#D4AF37] mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
          </svg>
          FAQ
        </div>
        <h1 :variants="m.itemFadeUp(1)" class="text-3xl sm:text-4xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Frequently Asked Questions</h1>
        <p :variants="m.itemFadeUp(2)" class="mt-2 text-base text-gray-500 dark:text-[#A0A0A0] max-w-xl mx-auto">
          Everything you need to know about ordering, custom pieces, shipping, and more.
        </p>
      </motion.div>

      <!-- Accordion -->
      <div v-if="faqs.length === 0" class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] p-10 text-center">
        <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">No FAQs available right now. Check back soon!</p>
      </div>

      <motion.div
        v-else
        :variants="m.staggerContainer(0.08, 0.05)"
        initial="hidden"
        whileInView="visible"
        :viewport="m.viewport.once"
        class="space-y-3"
      >
        <motion.div
          v-for="(faq, index) in faqs"
          :key="faq.id"
          :variants="m.itemFadeUp(index * 0.05)"
          class="overflow-hidden rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-sm transition-all duration-300"
          :class="openIndex === index ? 'border-[#D4AF37]/40 dark:border-[#D4AF37]/40' : ''"
        >
          <button
            @click="toggle(index)"
            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left cursor-pointer"
          >
            <span class="text-sm sm:text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ faq.question }}</span>
            <motion.span
              :animate="{ rotate: openIndex === index ? 180 : 0 }"
              :transition="{ duration: 0.3, ease: m.easeOutExpo }"
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#D4AF37]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
              </svg>
            </motion.span>
          </button>

          <AnimatePresence initial="false">
            <motion.div
              v-if="openIndex === index"
              :variants="answerVariants"
              initial="hidden"
              animate="visible"
              exit="exit"
              class="px-6 pb-6 overflow-hidden"
            >
              <p class="text-sm sm:text-base leading-7 text-gray-500 dark:text-[#A0A0A0]">{{ faq.answer }}</p>
            </motion.div>
          </AnimatePresence>
        </motion.div>
      </motion.div>

      <!-- Contact Card -->
      <motion.div
        :initial="{ opacity: 0, y: 24 }"
        whileInView="{ opacity: 1, y: 0 }"
        :viewport="m.viewport.once"
        :transition="{ duration: 0.5, ease: m.easeOutExpo }"
        class="mt-10 rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] p-8 text-center"
      >
        <h2 class="text-xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Still have questions?</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-[#A0A0A0] max-w-md mx-auto">
          Can't find what you're looking for? Reach out and we'll get back to you as soon as possible.
        </p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
          <a href="/custom-order"
            class="inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-semibold text-white bg-[#D4AF37] hover:bg-[#B8960F] transition-all active:scale-95">
            Place a Custom Order
          </a>
        </div>
      </motion.div>

    </div>
  </div>

  <AppFooter />
</template>
