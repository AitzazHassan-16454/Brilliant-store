<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Sidebar from "../components/Sidebar.vue";
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
    stock: 0,
    is_active: true,
    image: null,
    description: '',
})

const canSubmit = computed(() => {
    return form.name.trim() !== '' &&
           form.category_id !== '' &&
           form.price !== '' &&
           form.image !== null
})

const submit = () => {
    form.post('/products', {
        forceFormData: true,
        onError: () => error("Can't save data. Please check input fields."),
        onSuccess: () => form.reset()
    })
}

function handleFileChange(event) {
    form.image = event.target.files[0] || null
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

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">

    <!-- SIDEBAR -->
    <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-8 animate-fade-in-up-sm">
            <div>
                <h2 class="text-3xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Create Product</h2>
                <p class="text-gray-500 dark:text-[#A0A0A0] mt-2">Add a new product to your store</p>
            </div>
        </div>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-sm p-8 max-w-3xl animate-fade-in-up-sm" style="animation-delay:0.05s">

            <form @submit.prevent="submit" class="space-y-6">

                <!-- PRODUCT NAME -->
                <div>
                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Enter product name"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                 <!-- PRODUCT DESCRIPTION -->
                 <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium">Product Description <span class="text-red-500">*</span></label>
                        <button
                            type="button"
                            @click="generateWithAI"
                            :disabled="!form.name.trim() || generating"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-[#B8960F] dark:text-[#D4AF37] hover:bg-[#D4AF37]/20 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <svg v-if="generating" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <svg v-else class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/></svg>
                            {{ generating ? 'Generating...' : 'Generate with AI' }}
                        </button>
                    </div>
                    <input
                        type="text"
                        v-model="form.description"
                        placeholder="Enter product description"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />
                    <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                    <p v-if="aiError" class="text-red-500 text-sm mt-1">{{ aiError }}</p>
                </div>

                <!-- CATEGORY -->
                <div>
                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">Category <span class="text-red-500">*</span></label>
                    <select
                        v-model="form.category_id"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    >
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</p>
                </div>

                <!-- PRICE -->
                <div>
                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">Price ($) <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        v-model="form.price"
                        min="0"
                        step="0.01"
                        max="999999.99"
                        inputmode="decimal"
                        placeholder="Enter product price"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />
                    <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">{{ form.errors.price }}</p>
                </div>

                <!-- STOCK -->
                <div>
                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">Stock <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        v-model="form.stock"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        placeholder="Enter stock quantity"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />
                    <p v-if="form.errors.stock" class="text-red-500 text-sm mt-1">{{ form.errors.stock }}</p>
                </div>

                <!-- ACTIVE -->
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" v-model="form.is_active" class="peer sr-only" />
                        <div class="h-6 w-11 rounded-full bg-[#D4AF37]/10 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-[#D4AF37] peer-checked:after:translate-x-full"></div>
                    </label>
                    <span class="text-gray-700 dark:text-[#F5F5F5] font-medium">Active (visible on store)</span>
                </div>

                <!-- IMAGE -->
                <div>
                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">Product Image <span class="text-red-500">*</span></label>
                    <input
                        type="file"
                        @change="handleFileChange"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl px-4 py-3 bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />
                    <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</p>
                </div>

                <!-- BUTTONS -->
                <div class="flex gap-4 mt-4">
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="bg-[#D4AF37] hover:bg-[#B8960F] text-white px-6 py-3 rounded-xl font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 cursor-pointer"
                    >
                        Create Product
                    </button>
                    <Link
                        href="/dashboard"
                        class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] px-6 py-3 rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 hover:bg-[#D4AF37]/10 dark:hover:bg-[#21262d] transition active:scale-95"
                    >
                        Cancel
                    </Link>
                </div>

            </form>

        </div>

    </main>
</div>
</template>
