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
      class="fixed inset-0 z-50 flex items-center justify-center px-4"
      style="background: rgba(0,0,0,0.35); backdrop-filter: blur(4px);"
      @click.self="closable && close()"
    >
      <div
        :class="[
          'bg-white dark:bg-[#1A1A1A] w-full rounded-2xl p-6 shadow-xl max-h-[85vh] overflow-y-auto',
          widthClasses[maxWidth] || 'max-w-sm',
        ]"
      >
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

        <div v-if="$slots.actions" class="flex gap-2 mt-6">
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
