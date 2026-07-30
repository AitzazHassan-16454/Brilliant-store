<script setup>
import { router } from "@inertiajs/vue3"

const props = defineProps({
    links: Array
})

const hasPages = () => props.links && props.links.length > 3

const decodeLabel = (label) => {
    const txt = document.createElement('textarea')
    txt.innerHTML = label
    return txt.value
}
</script>

<template>
    <div v-if="hasPages()" class="flex justify-end mt-8">
        <nav class="inline-flex items-center gap-0.5 bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-xl p-1 shadow-sm">
            <template v-for="(link, index) in links" :key="link.label">
                <button
                    v-if="decodeLabel(link.label) === 'Previous'"
                    :disabled="!link.url"
                    @click="link.url && router.visit(link.url, { preserveState: true, replace: true })"
                    class="inline-flex items-center gap-1 h-8 px-2.5 rounded-md text-xs font-medium transition-all duration-200 active:scale-95 cursor-pointer"
                    :class="link.url
                        ? 'text-gray-500 dark:text-[#A0A0A0] hover:bg-gray-100 dark:hover:bg-[#21262d] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]'
                        : 'text-gray-300 dark:text-[#A0A0A0] cursor-default'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button
                    v-else-if="decodeLabel(link.label) === 'Next'"
                    :disabled="!link.url"
                    @click="link.url && router.visit(link.url, { preserveState: true, replace: true })"
                    class="inline-flex items-center gap-1 h-8 px-2.5 rounded-md text-xs font-medium transition-all duration-200 active:scale-95 cursor-pointer"
                    :class="link.url
                        ? 'text-gray-500 dark:text-[#A0A0A0] hover:bg-gray-100 dark:hover:bg-[#21262d] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]'
                        : 'text-gray-300 dark:text-[#A0A0A0] cursor-default'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <button
                    v-else
                    :disabled="!link.url"
                    @click="link.url && router.visit(link.url, { preserveState: true, replace: true })"
                    class="min-w-[32px] h-8 flex items-center justify-center px-2 rounded-md text-xs font-medium transition-all duration-200 active:scale-90 cursor-pointer"
                    :class="link.active
                        ? 'bg-[#1A1A1A] dark:bg-[#D4AF37] text-white shadow-sm'
                        : link.url
                            ? 'text-gray-500 dark:text-[#A0A0A0] hover:bg-gray-100 dark:hover:bg-[#21262d] hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]'
                            : 'text-gray-300 dark:text-[#A0A0A0] cursor-default'"
                >
                    {{ decodeLabel(link.label) }}
                </button>
            </template>
        </nav>
    </div>
</template>
