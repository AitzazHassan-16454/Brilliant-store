<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  initialImages: {
    type: Array,
    default: () => [],
  },
  error: {
    type: String,
    default: '',
  },
  max: {
    type: Number,
    default: 10,
  },
})

const emit = defineEmits(['update:modelValue', 'update:existingImages', 'change'])

const fileInputRef = ref(null)
const scrollContainerRef = ref(null)
const activeIndex = ref(0)
const isDragging = ref(false)

// Existing images from database (relative path or URL)
const existingImages = ref([...(props.initialImages || [])])

// Newly staged local files with previews
const newFiles = ref([])

// Helper to format bytes
const formatSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / 1048576).toFixed(1) + ' MB'
}

// Watch initialImages prop in case it updates asynchronously
watch(
  () => props.initialImages,
  (newVal) => {
    if (Array.isArray(newVal)) {
      existingImages.value = [...newVal.filter(Boolean)]
    }
  },
  { deep: true }
)

// Combined list of images for rendering
const combinedList = computed(() => {
  const list = []

  // Existing images
  existingImages.value.forEach((path, idx) => {
    if (!path) return
    const isFullUrl = path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')
    const src = isFullUrl ? path : `/storage/${path}`
    const filename = path.split('/').pop() || `Image ${idx + 1}`

    list.push({
      key: `existing-${path}-${idx}`,
      isExisting: true,
      src,
      rawPath: path,
      name: filename,
      size: '',
    })
  })

  // Newly selected files
  newFiles.value.forEach((item, idx) => {
    list.push({
      key: `new-${item.id}`,
      isExisting: false,
      src: item.previewUrl,
      file: item.file,
      name: item.file.name,
      size: formatSize(item.file.size),
    })
  })

  return list
})

// Emit changes to parent
const emitChanges = () => {
  const rawFiles = newFiles.value.map((f) => f.file)
  emit('update:modelValue', rawFiles)
  emit('update:existingImages', [...existingImages.value])
  emit('change', {
    newFiles: rawFiles,
    existingImages: [...existingImages.value],
  })
}

// Handle file addition
const addFiles = (fileList) => {
  if (!fileList || !fileList.length) return

  const remainingSlots = props.max - (existingImages.value.length + newFiles.value.length)
  if (remainingSlots <= 0) return

  const validFiles = Array.from(fileList)
    .filter((f) => f.type.startsWith('image/'))
    .slice(0, remainingSlots)

  validFiles.forEach((file) => {
    const previewUrl = URL.createObjectURL(file)
    newFiles.value.push({
      id: Math.random().toString(36).substring(2, 9),
      file,
      previewUrl,
    })
  })

  emitChanges()

  // Reset file input
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const onFileInputChange = (e) => {
  addFiles(e.target.files)
}

const onDrop = (e) => {
  isDragging.value = false
  if (e.dataTransfer && e.dataTransfer.files) {
    addFiles(e.dataTransfer.files)
  }
}

// Remove an item
const removeItem = (index) => {
  const item = combinedList.value[index]
  if (!item) return

  if (item.isExisting) {
    existingImages.value = existingImages.value.filter((p) => p !== item.rawPath)
  } else {
    const fIdx = newFiles.value.findIndex((f) => f.file === item.file)
    if (fIdx > -1) {
      URL.revokeObjectURL(newFiles.value[fIdx].previewUrl)
      newFiles.value.splice(fIdx, 1)
    }
  }

  emitChanges()

  if (activeIndex.value >= combinedList.value.length) {
    activeIndex.value = Math.max(0, combinedList.value.length - 1)
  }
}

// Set an image as primary (move to index 0)
const makePrimary = (index) => {
  if (index === 0) return
  const item = combinedList.value[index]
  if (!item) return

  if (item.isExisting) {
    const currentPath = item.rawPath
    existingImages.value = [currentPath, ...existingImages.value.filter((p) => p !== currentPath)]
  } else {
    const fIdx = newFiles.value.findIndex((f) => f.file === item.file)
    if (fIdx > -1) {
      const target = newFiles.value.splice(fIdx, 1)[0]
      // Also bring newFiles to top, or prepend to combined
      newFiles.value.unshift(target)
      // If there were existing images, move newFiles before existing
      // We prioritize the primary file
    }
  }

  emitChanges()
  scrollTo(0)
}

// ── Mobile Carousel Scroll Navigation ────────────────────────
const onScroll = (e) => {
  const el = e.target
  if (!el || !el.children.length) return
  const scrollLeft = el.scrollLeft
  let closestIdx = 0
  let minDiff = Infinity

  Array.from(el.children).forEach((child, idx) => {
    const diff = Math.abs(child.offsetLeft - el.offsetLeft - 16 - scrollLeft)
    if (diff < minDiff) {
      minDiff = diff
      closestIdx = idx
    }
  })

  activeIndex.value = closestIdx
}

const scrollTo = (idx) => {
  const el = scrollContainerRef.value
  if (!el) return
  const child = el.children[idx]
  if (child) {
    if (idx === 0) {
      el.scrollTo({ left: 0, behavior: 'smooth' })
    } else {
      const left = child.offsetLeft - el.offsetLeft - 16
      el.scrollTo({ left: Math.max(0, left), behavior: 'smooth' })
    }
  }
  activeIndex.value = idx
}

// Clean up object URLs on unmount
onUnmounted(() => {
  newFiles.value.forEach((item) => {
    if (item.previewUrl) {
      URL.revokeObjectURL(item.previewUrl)
    }
  })
})
</script>

<template>
  <div class="product-image-uploader space-y-3.5">
    <!-- Header / Label Row -->
    <div class="flex items-center justify-between">
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5]">
          Product Images <span class="text-red-500">*</span>
        </label>
        <p class="text-xs text-gray-500 dark:text-[#A0A0A0] mt-0.5">
          First photo serves as the primary cover. Upload up to {{ max }} high-resolution images.
        </p>
      </div>

      <span
        v-if="combinedList.length"
        class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#D4AF37]/10 text-[#B8960F] dark:text-[#D4AF37] border border-[#D4AF37]/20"
      >
        {{ combinedList.length }} / {{ max }} {{ combinedList.length === 1 ? 'Photo' : 'Photos' }}
      </span>
    </div>

    <!-- Hidden Native File Input -->
    <input
      ref="fileInputRef"
      type="file"
      multiple
      accept="image/png,image/jpeg,image/webp,image/avif"
      class="hidden"
      @change="onFileInputChange"
    />

    <!-- Upload Dropzone (visible if below max) -->
    <div
      v-if="combinedList.length < max"
      @click="fileInputRef?.click()"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      :class="[
        'relative border-2 border-dashed rounded-2xl p-5 sm:p-7 text-center cursor-pointer transition-all duration-300',
        isDragging
          ? 'border-[#D4AF37] bg-[#D4AF37]/10 scale-[1.01]'
          : 'border-gray-300 dark:border-[#D4AF37]/30 hover:border-[#D4AF37] dark:hover:border-[#D4AF37] bg-[#FAF7F2]/60 dark:bg-[#141414]/60 hover:bg-[#FAF7F2] dark:hover:bg-[#1A1A1A]',
      ]"
    >
      <div class="flex flex-col items-center justify-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-[#D4AF37]/15 text-[#D4AF37] flex items-center justify-center transition-transform group-hover:scale-110 shadow-xs">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
          </svg>
        </div>

        <div>
          <span class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
            Click to upload
          </span>
          <span class="text-sm text-gray-500 dark:text-[#A0A0A0]"> or drag & drop product photos</span>
        </div>

        <p class="text-[11px] text-gray-400 dark:text-neutral-500">
          PNG, JPG, WEBP or AVIF up to 5MB each (multiple selection supported)
        </p>
      </div>
    </div>

    <!-- Error Display -->
    <p v-if="error" class="text-red-500 text-xs mt-1.5">{{ error }}</p>

    <!-- PREVIEWS CONTAINER (When images exist) -->
    <div v-if="combinedList.length" class="space-y-3 pt-1">

      <!-- 1. MOBILE CAROUSEL (Small Devices: < sm) - Matches Category Section Layout -->
      <div class="block sm:hidden">
        <div
          ref="scrollContainerRef"
          @scroll.passive="onScroll"
          class="flex overflow-x-auto snap-x snap-mandatory scroll-smooth scroll-px-4 -mx-4 px-4 pb-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden gap-3.5"
        >
          <div
            v-for="(item, idx) in combinedList"
            :key="item.key"
            class="group relative overflow-hidden rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#141414] shadow-sm shrink-0 snap-start w-[78vw] max-w-[290px] aspect-[4/3]"
          >
            <!-- Image Frame -->
            <img
              :src="item.src"
              :alt="item.name"
              class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
            />

            <!-- Top Badges -->
            <div class="absolute top-3 left-3 z-10 flex items-center gap-1.5">
              <span
                v-if="idx === 0"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#D4AF37] text-neutral-950 font-bold text-[10px] shadow-md shadow-[#D4AF37]/30 uppercase tracking-wider"
              >
                ★ Primary Cover
              </span>
              <button
                v-else
                type="button"
                @click="makePrimary(idx)"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/70 hover:bg-[#D4AF37] hover:text-neutral-950 text-white text-[10px] font-semibold backdrop-blur-md border border-white/20 transition cursor-pointer"
              >
                Set Primary
              </button>
            </div>

            <div class="absolute top-3 right-3 z-10">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-[10px] font-semibold text-white/90">
                {{ idx + 1 }} / {{ combinedList.length }}
              </span>
            </div>

            <!-- Bottom Gradient Overlay & Remove Control -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent flex flex-col justify-end p-3.5 text-white pointer-events-none">
              <div class="flex items-center justify-between gap-2 pointer-events-auto">
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-semibold text-white truncate">{{ item.name }}</p>
                  <p v-if="item.size" class="text-[10px] text-white/70">{{ item.size }}</p>
                </div>

                <button
                  type="button"
                  @click="removeItem(idx)"
                  class="w-7 h-7 rounded-full bg-red-600/90 hover:bg-red-600 text-white flex items-center justify-center shrink-0 shadow-md cursor-pointer transition"
                  title="Remove photo"
                  aria-label="Remove photo"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Carousel Navigation Controls & Dots (Category Style) -->
        <div v-if="combinedList.length > 1" class="flex items-center justify-center gap-3 mt-3">
          <button
            type="button"
            @click="scrollTo(Math.max(0, activeIndex - 1))"
            :disabled="activeIndex === 0"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Previous photo"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
          </button>

          <div class="flex items-center gap-1.5">
            <button
              v-for="(item, idx) in combinedList"
              :key="'dot-' + item.key"
              type="button"
              @click="scrollTo(idx)"
              :aria-label="`Slide to photo ${idx + 1}`"
              :class="[
                'h-1.5 rounded-full transition-all duration-300 cursor-pointer',
                activeIndex === idx ? 'w-6 bg-[#D4AF37]' : 'w-1.5 bg-neutral-300 dark:bg-neutral-700',
              ]"
            />
          </div>

          <button
            type="button"
            @click="scrollTo(Math.min(combinedList.length - 1, activeIndex + 1))"
            :disabled="activeIndex === combinedList.length - 1"
            class="w-7 h-7 rounded-full flex items-center justify-center border border-gray-300 dark:border-[#D4AF37]/30 text-[#1A1A1A] dark:text-[#F5F5F5] disabled:opacity-25 disabled:cursor-not-allowed bg-white/80 dark:bg-[#1A1A1A]/80 cursor-pointer shadow-xs transition"
            aria-label="Next photo"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>
      </div>

      <!-- 2. TABLET & DESKTOP GRID (>= sm) -->
      <div class="hidden sm:grid sm:grid-cols-3 md:grid-cols-4 gap-4">
        <div
          v-for="(item, idx) in combinedList"
          :key="'desktop-' + item.key"
          class="group relative overflow-hidden rounded-2xl border border-gray-200/80 dark:border-[#D4AF37]/20 bg-white dark:bg-[#141414] shadow-sm hover:shadow-lg transition-all duration-300 aspect-[4/3]"
        >
          <img
            :src="item.src"
            :alt="item.name"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />

          <!-- Top Badge: Primary or Set Primary -->
          <div class="absolute top-2.5 left-2.5 z-10 flex items-center gap-1.5">
            <span
              v-if="idx === 0"
              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#D4AF37] text-neutral-950 font-bold text-[10px] shadow-md uppercase tracking-wider"
            >
              ★ Cover
            </span>
            <button
              v-else
              type="button"
              @click="makePrimary(idx)"
              class="opacity-0 group-hover:opacity-100 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/70 hover:bg-[#D4AF37] hover:text-neutral-950 text-white text-[10px] font-semibold backdrop-blur-md border border-white/20 transition cursor-pointer"
            >
              Set Primary
            </button>
          </div>

          <!-- Remove Button -->
          <button
            type="button"
            @click="removeItem(idx)"
            class="absolute top-2.5 right-2.5 z-10 w-7 h-7 rounded-full bg-red-600/90 hover:bg-red-600 text-white flex items-center justify-center opacity-80 hover:opacity-100 group-hover:opacity-100 shadow-md cursor-pointer transition"
            title="Remove photo"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Bottom Info -->
          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2.5 text-white">
            <p class="text-xs font-semibold truncate">{{ item.name }}</p>
            <p v-if="item.size" class="text-[10px] text-white/70">{{ item.size }}</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
