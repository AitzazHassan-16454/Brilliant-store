<script setup>
import { computed } from "vue"
import { router } from "@inertiajs/vue3"

const props = defineProps({
    links: {
        type: Array,
        default: () => []
    },
    meta: {
        type: Object,
        default: null
    },
    from: Number,
    to: Number,
    total: Number,
    perPage: [Number, String],
    perPageOptions: {
        type: Array,
        default: () => [8, 12, 24, 48, 96]
    },
    showPerPage: {
        type: Boolean,
        default: true
    },
    itemLabel: {
        type: String,
        default: "products"
    }
})

const emit = defineEmits(["update:perPage", "change-per-page"])

const displayFrom = computed(() => props.meta?.from ?? props.from ?? 0)
const displayTo = computed(() => props.meta?.to ?? props.to ?? 0)
const displayTotal = computed(() => props.meta?.total ?? props.total ?? 0)
const currentPerPage = computed(() => Number(props.perPage ?? props.meta?.per_page ?? props.perPageOptions[0]))

const paginationLinks = computed(() => {
    return props.links?.length ? props.links : (props.meta?.links || [])
})

const decodeLabel = (label) => {
    if (!label) return ""
    if (typeof document === "undefined") return String(label)
    const txt = document.createElement("textarea")
    txt.innerHTML = label
    return txt.value
}

const isPrevious = (label) => {
    if (!label) return false
    const lower = String(label).toLowerCase()
    return lower.includes("previous") || lower.includes("«") || lower.includes("&laquo;")
}

const isNext = (label) => {
    if (!label) return false
    const lower = String(label).toLowerCase()
    return lower.includes("next") || lower.includes("»") || lower.includes("&raquo;")
}

const currentPage = computed(() => {
    if (props.meta?.current_page) {
        return Number(props.meta.current_page)
    }
    const active = paginationLinks.value.find((l) => l.active)
    if (active) {
        const num = Number(decodeLabel(active.label))
        if (!isNaN(num) && num > 0) return num
    }
    if (typeof window !== "undefined" && window.location) {
        const params = new URLSearchParams(window.location.search)
        const p = Number(params.get("page"))
        if (p > 0) return p
    }
    return 1
})

const lastPage = computed(() => {
    if (props.meta?.last_page) {
        return Number(props.meta.last_page)
    }
    if (displayTotal.value && currentPerPage.value) {
        const calculated = Math.ceil(displayTotal.value / currentPerPage.value)
        if (calculated > 0) return calculated
    }
    let max = 1
    for (const link of paginationLinks.value) {
        if (!isPrevious(link.label) && !isNext(link.label)) {
            const num = Number(decodeLabel(link.label))
            if (!isNaN(num) && num > max) {
                max = num
            }
        }
    }
    return max
})

const hasPages = computed(() => {
    return lastPage.value > 1
})

const getPageUrl = (pageNumber) => {
    const existing = paginationLinks.value.find((l) => {
        const decoded = decodeLabel(l.label).trim()
        return decoded === String(pageNumber) && l.url
    })
    if (existing && existing.url) {
        return existing.url
    }

    if (typeof window !== "undefined" && window.location) {
        try {
            const url = new URL(window.location.href)
            url.searchParams.set("page", pageNumber)
            return url.pathname + url.search
        } catch (e) {
            // fallback
        }
    }

    const sampleLink = paginationLinks.value.find((l) => l.url)
    if (sampleLink && sampleLink.url) {
        try {
            const base = typeof window !== "undefined" ? window.location.origin : "http://localhost"
            const url = new URL(sampleLink.url, base)
            url.searchParams.set("page", pageNumber)
            return url.pathname + url.search
        } catch (e) {
            // fallback
        }
    }

    return `?page=${pageNumber}`
}

const prevLink = computed(() => {
    if (currentPage.value <= 1) {
        return { url: null, label: "Previous" }
    }
    const found = paginationLinks.value.find((l) => isPrevious(l.label))
    if (found && found.url) {
        return found
    }
    return {
        url: props.meta?.prev_page_url || getPageUrl(currentPage.value - 1),
        label: "Previous",
    }
})

const nextLink = computed(() => {
    if (currentPage.value >= lastPage.value) {
        return { url: null, label: "Next" }
    }
    const found = paginationLinks.value.find((l) => isNext(l.label))
    if (found && found.url) {
        return found
    }
    return {
        url: props.meta?.next_page_url || getPageUrl(currentPage.value + 1),
        label: "Next",
    }
})

const formattedPages = computed(() => {
    const total = lastPage.value
    const current = currentPage.value

    if (total <= 5) {
        return Array.from({ length: total }, (_, i) => ({
            type: "page",
            page: i + 1,
            label: String(i + 1),
            active: i + 1 === current,
            url: getPageUrl(i + 1),
        }))
    }

    const pages = []

    if (current <= 3) {
        for (let i = 1; i <= 3; i++) {
            pages.push({
                type: "page",
                page: i,
                label: String(i),
                active: i === current,
                url: getPageUrl(i),
            })
        }
        pages.push({ type: "ellipsis", label: "..." })
        pages.push({
            type: "page",
            page: total,
            label: String(total),
            active: total === current,
            url: getPageUrl(total),
        })
    } else if (current >= total - 2) {
        pages.push({
            type: "page",
            page: 1,
            label: "1",
            active: 1 === current,
            url: getPageUrl(1),
        })
        pages.push({ type: "ellipsis", label: "..." })
        for (let i = total - 2; i <= total; i++) {
            pages.push({
                type: "page",
                page: i,
                label: String(i),
                active: i === current,
                url: getPageUrl(i),
            })
        }
    } else {
        pages.push({
            type: "page",
            page: 1,
            label: "1",
            active: 1 === current,
            url: getPageUrl(1),
        })
        pages.push({ type: "ellipsis", label: "..." })
        for (let i = current - 1; i <= current + 1; i++) {
            pages.push({
                type: "page",
                page: i,
                label: String(i),
                active: i === current,
                url: getPageUrl(i),
            })
        }
        pages.push({ type: "ellipsis", label: "..." })
        pages.push({
            type: "page",
            page: total,
            label: String(total),
            active: total === current,
            url: getPageUrl(total),
        })
    }

    return pages
})

const handlePerPageChange = (event) => {
    const newPerPage = Number(event.target.value)
    emit("update:perPage", newPerPage)
    emit("change-per-page", newPerPage)

    const url = new URL(window.location.href)
    url.searchParams.set("per_page", newPerPage)
    url.searchParams.delete("page")

    router.visit(url.pathname + url.search, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>

<template>
    <div
        v-if="displayTotal > 0 || hasPages"
        class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 w-full"
    >
        <!-- Showing X-Y of Z & Per-page selector -->
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-gray-500 dark:text-[#A0A0A0]">
            <span v-if="displayTotal > 0">
                Showing
                <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">{{ displayFrom }}</span>
                to
                <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">{{ displayTo }}</span>
                of
                <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">{{ displayTotal }}</span>
                {{ itemLabel }}
            </span>

            <!-- Per page dropdown -->
            <div v-if="showPerPage" class="flex items-center gap-1.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-2.5 py-1 shadow-sm">
                <label for="per-page-select" class="text-[11px] font-medium text-gray-500 dark:text-[#A0A0A0]">Show:</label>
                <select
                    id="per-page-select"
                    :value="currentPerPage"
                    @change="handlePerPageChange"
                    aria-label="Items per page"
                    class="bg-transparent border-none text-xs font-semibold text-gray-900 dark:text-[#F5F5F5] focus:outline-none cursor-pointer pr-1 py-0.5"
                >
                    <option
                        v-for="opt in perPageOptions"
                        :key="opt"
                        :value="opt"
                        class="bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5]"
                    >
                        {{ opt }} per page
                    </option>
                </select>
            </div>
        </div>

        <!-- Page buttons -->
        <nav
            v-if="hasPages"
            aria-label="Pagination Navigation"
            class="inline-flex items-center gap-0.5 bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-xl p-1 shadow-sm max-w-full overflow-x-auto"
        >
            <!-- Previous Button -->
            <button
                type="button"
                :disabled="!prevLink.url"
                @click="prevLink.url && router.visit(prevLink.url, { preserveState: true, preserveScroll: true, replace: true })"
                class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-medium transition-colors"
                :class="prevLink.url
                    ? 'text-gray-600 dark:text-[#A0A0A0] hover:bg-[#D4AF37]/10 hover:text-[#D4AF37] cursor-pointer'
                    : 'text-gray-300 dark:text-neutral-600 cursor-not-allowed'"
                title="Previous page"
                aria-label="Previous page"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden md:inline">Prev</span>
            </button>

            <!-- Page Buttons and Ellipsis -->
            <template v-for="(item, index) in formattedPages" :key="index">
                <span
                    v-if="item.type === 'ellipsis'"
                    class="min-w-[32px] h-8 flex items-center justify-center text-xs font-semibold text-gray-400 dark:text-[#A0A0A0] select-none tracking-widest px-1"
                    aria-hidden="true"
                >
                    ...
                </span>

                <button
                    v-else
                    type="button"
                    :disabled="item.active"
                    @click="item.url && router.visit(item.url, { preserveState: true, preserveScroll: true, replace: true })"
                    class="min-w-[32px] h-8 flex items-center justify-center px-2 rounded-lg text-xs font-semibold transition-all"
                    :class="item.active
                        ? 'bg-[#D4AF37] text-white shadow-sm shadow-[#D4AF37]/30 cursor-default'
                        : 'text-gray-600 dark:text-[#A0A0A0] hover:bg-[#D4AF37]/10 hover:text-[#D4AF37] cursor-pointer'"
                    :aria-current="item.active ? 'page' : undefined"
                    :aria-label="`Page ${item.label}`"
                >
                    {{ item.label }}
                </button>
            </template>

            <!-- Next Button -->
            <button
                type="button"
                :disabled="!nextLink.url"
                @click="nextLink.url && router.visit(nextLink.url, { preserveState: true, preserveScroll: true, replace: true })"
                class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg text-xs font-medium transition-colors"
                :class="nextLink.url
                    ? 'text-gray-600 dark:text-[#A0A0A0] hover:bg-[#D4AF37]/10 hover:text-[#D4AF37] cursor-pointer'
                    : 'text-gray-300 dark:text-neutral-600 cursor-not-allowed'"
                title="Next page"
                aria-label="Next page"
            >
                <span class="hidden md:inline">Next</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </nav>
    </div>
</template>
