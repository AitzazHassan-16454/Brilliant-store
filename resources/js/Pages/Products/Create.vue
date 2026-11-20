<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Sidebar from "../components/Sidebar.vue";
import ProductImageUploader from "../../Components/ProductImageUploader.vue";
import { Link, Head } from '@inertiajs/vue3'
import { useNotification } from "../../composables/useNotification.js";

const { error } = useNotification();
const page = usePage();

const csrfToken = () =>
    page.props.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

defineOptions({ layout: false })

const props = defineProps({
    categories: Array,
})

const form = useForm({
    name: '',
    category_id: '',
    price: '',
    compare_price: '',
    stock: 0,
    is_active: true,
    is_trending: false,
    image: null,
    images: [],
    description: '',
})

const canSubmit = computed(() => {
    return form.name.trim() !== '' &&
           form.category_id !== '' &&
           form.price !== '' &&
           (form.images?.length > 0 || form.image !== null)
})

const submit = () => {
    form.post('/products', {
        forceFormData: true,
        onError: () => error("Can't save data. Please check input fields."),
        onSuccess: () => form.reset()
    })
}

const generating = ref(false)
const aiError = ref('')

const generateWithAI = async () => {
    if (!form.name.trim() || generating.value) return

    generating.value = true
    aiError.value = ''

    try {
        const res = await fetch('/products/ai-description', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                name: form.name,
                category_id: form.category_id || null,
                price: form.price || null,
            }),
        })

        if (!res.ok) throw new Error('Failed')

        const data = await res.json()
        if (data.description) {
            form.description = data.description
        }
    } catch (e) {
        aiError.value = "Couldn't generate the description. Please try again."
    } finally {
        generating.value = false
    }
}
</script>

<template>
<Head title="Create Product" />

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <!-- SIDEBAR -->
    <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl sm:text-3xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Create Product</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-1">Add a new product to your store inventory</p>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-xs p-4 sm:p-6 lg:p-8 max-w-3xl">

            <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">

                <!-- PRODUCT NAME -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Enter product name"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">{{ form.errors.name }}</p>
                </div>

                 <!-- PRODUCT DESCRIPTION -->
                 <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5]">Product Description <span class="text-red-500">*</span></label>
                        <button
                            type="button"
                            @click="generateWithAI"
                            :disabled="!form.name.trim() || generating"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-[#B8960F] dark:text-[#D4AF37] hover:bg-[#D4AF37]/20 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition"
                        >
                            <svg v-if="generating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <svg v-else class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/></svg>
                            {{ generating ? 'Generating...' : 'Generate with AI' }}
                        </button>
                    </div>
                    <textarea
                        rows="3"
                        v-model="form.description"
                        placeholder="Enter product description"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37] resize-none"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-red-500 text-xs mt-1.5">{{ form.errors.description }}</p>
                    <p v-if="aiError" class="text-red-500 text-xs mt-1.5">{{ aiError }}</p>
                </div>

                <!-- CATEGORY -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">Category <span class="text-red-500">*</span></label>
                    <select
                        v-model="form.category_id"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                    >
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-1.5">{{ form.errors.category_id }}</p>
                </div>

                <!-- PRICE & STOCK ROW -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                    <!-- SELLING / DISCOUNTED PRICE -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-1">
                            Selling Price ($) <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 mb-2">The active price customers pay</p>
                        <input
                            type="number"
                            v-model="form.price"
                            min="0"
                            step="0.01"
                            max="999999.99"
                            inputmode="decimal"
                            placeholder="0.00"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                        />
                        <p v-if="form.errors.price" class="text-red-500 text-xs mt-1.5">{{ form.errors.price }}</p>
                    </div>

                    <!-- ORIGINAL / COMPARE AT PRICE (WITH LINE THROUGH) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-1">
                            Original Price ($) <span class="text-xs font-normal text-gray-400">(Optional)</span>
                        </label>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 mb-2">Shown with strikethrough line</p>
                        <input
                            type="number"
                            v-model="form.compare_price"
                            min="0"
                            step="0.01"
                            max="999999.99"
                            inputmode="decimal"
                            placeholder="e.g. 150.00"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                        />
                        <p v-if="form.errors.compare_price" class="text-red-500 text-xs mt-1.5">{{ form.errors.compare_price }}</p>
                        <div v-if="form.compare_price && form.price && Number(form.compare_price) > Number(form.price)" class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800/40">
                            <span>Save {{ Math.round(((Number(form.compare_price) - Number(form.price)) / Number(form.compare_price)) * 100) }}% badge preview</span>
                        </div>
                    </div>

                    <!-- STOCK -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-1">
                            Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 mb-2">Available inventory units</p>
                        <input
                            type="number"
                            v-model="form.stock"
                            min="0"
                            step="1"
                            inputmode="numeric"
                            placeholder="0"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                        />
                        <p v-if="form.errors.stock" class="text-red-500 text-xs mt-1.5">{{ form.errors.stock }}</p>
                    </div>
                </div>

                <!-- TOGGLES -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 pt-1">
                    <!-- ACTIVE -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" v-model="form.is_active" class="peer sr-only" />
                            <div class="h-6 w-11 rounded-full bg-[#D4AF37]/15 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:bg-[#D4AF37] peer-checked:after:translate-x-full"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Active (visible in store)</span>
                    </div>

                    <!-- TRENDING -->
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" v-model="form.is_trending" class="peer sr-only" />
                            <div class="h-6 w-11 rounded-full bg-[#D4AF37]/15 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-transform peer-checked:bg-[#D4AF37] peer-checked:after:translate-x-full"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Trending (featured)</span>
                    </div>
                </div>

                <!-- PRODUCT IMAGES WITH MOBILE CAROUSEL -->
                <div>
                    <ProductImageUploader
                        v-model="form.images"
                        :error="form.errors.images || form.errors.image"
                        @update:modelValue="(files) => form.image = files[0] || null"
                    />
                </div>

                <!-- BUTTONS -->
                <div class="flex flex-col sm:flex-row gap-3 pt-3">
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="w-full sm:w-auto inline-flex items-center justify-center bg-[#D4AF37] hover:bg-[#B8960F] text-white px-6 py-3 rounded-xl font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition shadow-xs"
                    >
                        Create Product
                    </button>
                    <Link
                        href="/products"
                        class="w-full sm:w-auto inline-flex items-center justify-center bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] px-6 py-3 rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 hover:bg-[#D4AF37]/10 dark:hover:bg-[#21262d] text-sm font-semibold transition"
                    >
                        Cancel
                    </Link>
                </div>

            </form>

        </div>

    </main>
</div>
</template>
