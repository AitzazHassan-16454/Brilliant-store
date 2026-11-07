<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue"

const props = defineProps({
  toast: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(["dismiss"])

const isPaused = ref(false)
const progress = ref(100)
let animationFrame = null
let startTime = null
let elapsedBeforePause = 0
let pauseStartTime = null

const duration = props.toast.duration !== undefined ? props.toast.duration : 4000

const defaultMessageForType = (type) => {
  switch (type) {
    case "error":
      return "An error occurred"
    case "warning":
      return "Attention required"
    case "info":
      return "Notice"
    case "success":
    default:
      return "Action completed successfully"
  }
}

const updateTimer = (timestamp) => {
  if (!startTime) startTime = timestamp

  if (!isPaused.value) {
    if (pauseStartTime) {
      elapsedBeforePause += timestamp - pauseStartTime
      pauseStartTime = null
    }
    const totalElapsed = timestamp - startTime - elapsedBeforePause
    const remaining = Math.max(0, duration - totalElapsed)
    progress.value = (remaining / duration) * 100

    if (remaining <= 0) {
      emit("dismiss", props.toast.id)
      return
    }
  } else {
    if (!pauseStartTime) {
      pauseStartTime = timestamp
    }
  }

  animationFrame = requestAnimationFrame(updateTimer)
}

onMounted(() => {
  if (duration > 0 && duration !== Infinity) {
    animationFrame = requestAnimationFrame(updateTimer)
  }
})

onBeforeUnmount(() => {
  if (animationFrame) {
    cancelAnimationFrame(animationFrame)
  }
})

const onMouseEnter = () => {
  isPaused.value = true
}

const onMouseLeave = () => {
  isPaused.value = false
}

const onActionClick = () => {
  if (props.toast.action?.onClick) {
    props.toast.action.onClick()
  }
  emit("dismiss", props.toast.id)
}
</script>

<template>
  <div
    class="pointer-events-auto relative w-full overflow-hidden rounded-2xl border bg-white/95 p-4 shadow-2xl backdrop-blur-xl transition-all duration-300 dark:bg-[#1A1A1A]/95"
    :class="[
      toast.type === 'success'
        ? 'border-[#D4AF37]/35 shadow-[0_12px_40px_-10px_rgba(212,175,55,0.25)]'
        : toast.type === 'error'
          ? 'border-red-500/35 shadow-[0_12px_40px_-10px_rgba(239,68,68,0.25)]'
          : toast.type === 'warning'
            ? 'border-amber-500/35 shadow-[0_12px_40px_-10px_rgba(245,158,11,0.22)]'
            : 'border-sky-500/35 shadow-[0_12px_40px_-10px_rgba(14,165,233,0.22)]'
    ]"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    role="alert"
  >
    <div class="flex items-start gap-3.5">
      <!-- Icon with glowing ring badge -->
      <div
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-transform"
        :class="[
          toast.type === 'success'
            ? 'bg-[#D4AF37]/15 text-[#B8960F] dark:text-[#D4AF37] ring-1 ring-[#D4AF37]/35'
            : toast.type === 'error'
              ? 'bg-red-500/15 text-red-600 dark:text-red-400 ring-1 ring-red-500/35'
              : toast.type === 'warning'
                ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/35'
                : 'bg-sky-500/15 text-sky-600 dark:text-sky-400 ring-1 ring-sky-500/35'
        ]"
      >
        <!-- Success Checkmark -->
        <svg v-if="toast.type === 'success'" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>

        <!-- Error Alert -->
        <svg v-else-if="toast.type === 'error'" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
        </svg>

        <!-- Warning Alert -->
        <svg v-else-if="toast.type === 'warning'" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 18h.008v.008H12V18Z" />
        </svg>

        <!-- Info -->
        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
      </div>

      <!-- Content Area -->
      <div class="min-w-0 flex-1 pt-0.5">
        <h4
          v-if="toast.title"
          class="text-xs font-bold uppercase tracking-wider mb-0.5"
          :class="[
            toast.type === 'success' ? 'text-[#B8960F] dark:text-[#D4AF37]' :
            toast.type === 'error' ? 'text-red-600 dark:text-red-400' :
            toast.type === 'warning' ? 'text-amber-600 dark:text-amber-400' :
            'text-sky-600 dark:text-sky-400'
          ]"
        >
          {{ toast.title }}
        </h4>
        <p class="text-sm font-semibold leading-snug text-[#1A1A1A] dark:text-[#F5F5F5] break-words">
          {{ toast.message || toast.title || defaultMessageForType(toast.type) }}
        </p>

        <!-- Optional Action Button -->
        <div v-if="toast.action" class="mt-2.5">
          <button
            type="button"
            @click="onActionClick"
            class="inline-flex items-center gap-1.5 rounded-lg bg-[#D4AF37] px-3 py-1.5 text-xs font-bold text-[#0A0A0A] shadow-xs transition-all hover:bg-[#B8941E] active:scale-95 cursor-pointer"
          >
            {{ toast.action.label }}
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Dismiss Button -->
      <button
        type="button"
        @click="emit('dismiss', toast.id)"
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 transition-all hover:bg-gray-100 hover:text-[#1A1A1A] dark:text-[#A0A0A0] dark:hover:bg-[#2A2A2A] dark:hover:text-[#F5F5F5] cursor-pointer"
        title="Dismiss"
        aria-label="Dismiss notification"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Sleek Progress Countdown Bar -->
    <div
      v-if="duration > 0 && duration !== Infinity"
      class="absolute inset-x-0 bottom-0 h-0.5 bg-black/5 dark:bg-white/5 overflow-hidden"
    >
      <div
        class="h-full transition-all duration-75 ease-linear"
        :style="{ width: `${progress}%` }"
        :class="[
          toast.type === 'success'
            ? 'bg-[#D4AF37]'
            : toast.type === 'error'
              ? 'bg-red-500'
              : toast.type === 'warning'
                ? 'bg-amber-500'
                : 'bg-sky-500'
        ]"
      />
    </div>
  </div>
</template>
