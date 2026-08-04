<script setup>
import { computed } from "vue"
import { Link } from "@inertiajs/vue3"
import { motion } from "motion-v"
import { motionPresets as m } from "../lib/motion.js"

const props = defineProps({
  category: { type: Object, required: true },
})

const gradients = [
  'from-[#D4AF37]/20 to-[#0A0A0A]/80',
  'from-[#D4AF37]/15 to-[#1A1A1A]/80',
  'from-amber-600/20 to-[#0A0A0A]/80',
  'from-yellow-500/15 to-[#1A1A1A]/80',
  'from-[#D4AF37]/25 to-[#0A0A0A]/70',
  'from-amber-500/20 to-[#1A1A1A]/75',
]

const gradient = computed(() => gradients[props.category.id % gradients.length])

const initials = computed(() => props.category.name.charAt(0).toUpperCase())

const imageUrl = computed(() => {
  if (!props.category.image) return null
  if (props.category.image.startsWith('http')) return props.category.image
  return `/storage/${props.category.image}`
})
</script>

<template>
  <motion.div
    :whileHover="{ y: -6, boxShadow: '0 8px 30px rgba(0,0,0,0.12)', transition: m.spring.gentle }"
    class="h-full"
  >
    <Link :href="`/categories/${category.uid}`"
      class="group relative block h-full overflow-hidden rounded-2xl border border-border bg-card shadow-luxury transition-colors hover:border-[#D4AF37]/50 no-underline">

    <div class="aspect-[4/3] relative overflow-hidden">
      <!-- Image background -->
      <img v-if="imageUrl" :src="imageUrl" :alt="category.name"
        class="absolute inset-0 w-full h-full object-cover transition-luxury group-hover:scale-105" />

      <!-- Gradient overlay (always on top of image or as fallback) -->
      <div class="absolute inset-0 bg-gradient-to-br"
        :class="[imageUrl ? 'from-black/40 to-black/70' : gradient]">

        <!-- Large initial letter (only shown when no image) -->
        <span v-if="!imageUrl" class="absolute inset-0 flex items-center justify-center text-7xl sm:text-8xl font-black text-white/10 select-none">{{ initials }}</span>

        <!-- Subtle gold pattern overlay -->
        <div class="absolute inset-0 opacity-[0.04]"
          style="background-image: repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(212,175,55,1) 20px, rgba(212,175,55,1) 21px);" />
      </div>

      <!-- Gold accent line -->
      <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-[#D4AF37]/60 to-transparent opacity-0 group-hover:opacity-100 transition-luxury z-10" />
    </div>

    <!-- Content overlay -->
    <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5 bg-gradient-to-t from-black/85 via-black/50 to-transparent">
      <h3 class="text-base sm:text-lg font-bold text-white mb-0.5 transition-luxury group-hover:-translate-y-0.5">
        {{ category.name }}
      </h3>
      <p v-if="category.description" class="text-xs sm:text-sm text-white/70 line-clamp-2 mb-2 leading-relaxed transition-luxury group-hover:-translate-y-0.5">
        {{ category.description }}
      </p>
      <div class="flex items-center justify-between gap-2">
        <span class="text-[11px] sm:text-xs text-white/50">{{ category.products_count || 0 }} {{ (category.products_count || 0) === 1 ? 'item' : 'items' }}</span>
        <div class="flex items-center gap-1 text-xs sm:text-sm text-[#D4AF37] font-medium transition-luxury group-hover:translate-x-1">
          <span>Explore</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </div>
      </div>
    </div>

  </Link>
  </motion.div>
</template>