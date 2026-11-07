<script setup>
import { watch } from "vue"
import { usePage } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"
import ToastItem from "./ToastItem.vue"

const page = usePage()
const { toasts, dismiss, success, error, warning, info } = useNotification()

// Watch Inertia flash props and trigger professional notifications
watch(
  () => page.props.flash?.success,
  (val) => {
    if (val) success(val)
  },
  { immediate: true }
)

watch(
  () => page.props.flash?.error,
  (val) => {
    if (val) error(val)
  },
  { immediate: true }
)

watch(
  () => page.props.flash?.warning,
  (val) => {
    if (val) warning(val)
  },
  { immediate: true }
)

watch(
  () => page.props.flash?.info,
  (val) => {
    if (val) info(val)
  },
  { immediate: true }
)

watch(
  () => page.props.flash?.status,
  (val) => {
    if (val) success(val)
  },
  { immediate: true }
)
</script>

<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed inset-x-3 top-4 z-[9999] flex flex-col items-center gap-2.5 sm:inset-x-auto sm:top-auto sm:bottom-6 sm:right-6 sm:w-full sm:max-w-md sm:items-end"
      role="region"
      aria-label="Notifications"
    >
      <TransitionGroup name="pro-toast">
        <ToastItem
          v-for="toast in toasts"
          :key="toast.id"
          :toast="toast"
          @dismiss="dismiss"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style>
.pro-toast-enter-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.pro-toast-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 1, 1);
}
.pro-toast-enter-from {
  opacity: 0;
  transform: translateY(-16px) scale(0.96);
}
.pro-toast-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.94);
}

@media (min-width: 640px) {
  .pro-toast-enter-from {
    opacity: 0;
    transform: translateX(24px) scale(0.96);
  }
  .pro-toast-leave-to {
    opacity: 0;
    transform: translateX(24px) scale(0.94);
  }
}
</style>
