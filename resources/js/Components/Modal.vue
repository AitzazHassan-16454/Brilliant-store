<script setup>
defineProps({
  show: Boolean,
  maxWidth: { type: String, default: 'sm' },
  closable: { type: Boolean, default: true },
})

const emit = defineEmits(['close'])

const widthClasses = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-xl',
}

const close = () => {
  emit('close')
}
</script>

<template>
  <Transition name="modal">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-0 sm:px-4"
      style="background: rgba(0,0,0,0.35); backdrop-filter: blur(4px);"
      @click.self="closable && close()"
    >
      <div
        :class="[
          'bg-white dark:bg-[#1A1A1A] w-full flex flex-col overflow-hidden shadow-xl max-h-[92vh] sm:max-h-[85vh] rounded-t-2xl sm:rounded-2xl',
          widthClasses[maxWidth] || 'max-w-sm',
        ]"
      >
        <div class="flex-1 overflow-y-auto p-4 sm:p-6">
          <div v-if="$slots.icon" class="mb-4">
            <slot name="icon" />
          </div>

          <div v-if="$slots.title || $slots.description" class="space-y-1">
            <div v-if="$slots.title">
              <slot name="title" />
            </div>
            <div v-if="$slots.description">
              <slot name="description" />
            </div>
          </div>

          <slot />
        </div>

        <div v-if="$slots.actions" class="flex gap-2 px-4 sm:px-6 py-3 sm:py-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] shrink-0 border-t border-gray-100 dark:border-[#D4AF37]/15">
          <slot name="actions" />
        </div>
      </div>
    </div>
  </Transition>
</template>

<style>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
