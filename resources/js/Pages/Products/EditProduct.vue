<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import Sidebar from '../components/Sidebar.vue';
import { useNotification } from "../../composables/useNotification.js";

const { error } = useNotification();
const page = usePage();

const csrfToken = () =>
    page.props.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

defineOptions({ layout: false })

const props = defineProps({
    product: Object,
    errors: Object,
    categories: Array,
});

const form = useForm({
    _method: 'PUT',
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price,
    stock: props.product.stock,
    is_active: props.product.is_active,
    is_trending: props.product.is_trending,
    category_id: props.product.category_id,
})

const submit = () => {
    form.post(`/products/${props.product.uid}`, {
        forceFormData: true,

        onError: () => {
            error('Can`t Update Data Please Check Your Inputs');
        }
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

<Head title="Edit Product" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <!-- SIDEBAR -->
     <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

        <!-- PAGE HEADER -->
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div>
                <h2 class="text-xl sm:text-3xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Edit Product
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-1">
                    Update product details, pricing, and stock information
                </p>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-xs p-4 sm:p-6 lg:p-8 max-w-3xl">

            <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">

                <!-- PRODUCT NAME -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                        Product Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="form.name"
                        name="name"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                        placeholder="Enter product name"
                    />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- PRODUCT DESCRIPTION -->
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5]">
                            Product Description <span class="text-red-500">*</span>
                        </label>
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
                        v-model="form.description"
                        name="description"
                        rows="3"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37] resize-none"
                        placeholder="Enter product description"
                    ></textarea>

                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1.5">
                        {{ form.errors.description }}
                    </div>

                    <div v-if="aiError" class="text-red-500 text-xs mt-1.5">
                        {{ aiError }}
                    </div>
                </div>

                <!-- CATEGORY -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.category_id"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                    >
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>

                    <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1.5">
                        {{ form.errors.category_id }}
                    </div>
                </div>

                <!-- PRICE & STOCK ROW -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <!-- PRICE -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                            Price ($) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model="form.price"
                            name="price"
                            min="0"
                            step="0.01"
                            max="999999.99"
                            inputmode="decimal"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                            placeholder="0.00"
                        />
                        <div v-if="form.errors.price" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.price }}
                        </div>
                    </div>

                    <!-- STOCK -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                            Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            v-model="form.stock"
                            name="stock"
                            min="0"
                            step="1"
                            inputmode="numeric"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm focus:outline-none bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5] transition focus:border-[#D4AF37]"
                            placeholder="0"
                        />
                        <div v-if="form.errors.stock" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.stock }}
                        </div>
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

                <!-- CURRENT & NEW IMAGE -->
                <div class="space-y-4 pt-1">
                    <div v-if="product.image">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                            Current Image
                        </label>
                        <img
                            :src="`/${product.image}`"
                            class="w-28 h-28 sm:w-36 sm:h-36 object-cover rounded-xl border border-gray-200 dark:border-[#D4AF37]/20"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                            Upload New Image
                        </label>
                        <input
                            type="file"
                            name="image"
                            @input="form.image = $event.target.files[0]"
                            class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 text-sm bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                        />
                        <div v-if="form.errors.image" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.image }}
                        </div>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="flex flex-col sm:flex-row gap-3 pt-3">
                    <button
                        type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center bg-[#D4AF37] hover:bg-[#B8960F] text-white px-6 py-3 rounded-xl font-semibold text-sm cursor-pointer transition shadow-xs"
                    >
                        Update Product
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
