<script setup>
import { useForm } from '@inertiajs/vue3'
import Sidebar from '../components/Sidebar.vue';
import { useNotification } from "../../composables/useNotification.js";

const { error } = useNotification();

defineOptions({ layout: false })

const props = defineProps({
    product: Object,
    errors: Object,
    categories: Array,
});

const form = useForm({
    _method: 'PUT',
    name: props.product.name,
    price: props.product.price,
    stock: props.product.stock,
    is_active: props.product.is_active,
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
</script>

<template>

<Head title="Edit Product" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">

    <!-- SIDEBAR -->
     <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <!-- PAGE HEADER -->
        <div class="flex items-center justify-between mb-8 animate-fade-in-up-sm">

            <div>
                <h2 class="text-3xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Edit Product
                </h2>

                <p class="text-gray-400 dark:text-[#A0A0A0] mt-2">
                    Update your product information
                </p>
            </div>


        </div>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-sm p-8 max-w-3xl animate-fade-in-up-sm" style="animation-delay:0.05s">

            <form @submit.prevent="submit">

                <!-- PRODUCT NAME -->
                <div class="mb-6">

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        Product Name
                    </label>

                    <input
                        type="text"
                        v-model="form.name"
                        name="name"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                        placeholder="Enter product name"
                    />

                    <div
                        v-if="form.errors.name"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ form.errors.name }}
                    </div>

                </div>

                <!-- CATEGORY -->
                <div class="mb-6">

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        Category
                    </label>

                    <select
                        v-model="form.category_id"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    >

                        <option value="">
                            Select category
                        </option>

                        <option
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>

                    </select>

                    <div
                        v-if="form.errors.category_id"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ form.errors.category_id }}
                    </div>

                </div>

                <!-- PRICE -->
                <div class="mb-6">

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        Price
                    </label>

                    <input
                        type="number"
                        v-model="form.price"
                        name="price"
                        min="0"
                        step="0.01"
                        max="999999.99"
                        inputmode="decimal"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                        placeholder="Enter price"
                    />

                    <div
                        v-if="form.errors.price"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ form.errors.price }}
                    </div>

                </div>

                <!-- STOCK -->
                <div class="mb-6">

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        Stock
                    </label>

                    <input
                        type="number"
                        v-model="form.stock"
                        name="stock"
                        min="0"
                        step="1"
                        inputmode="numeric"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 focus:outline-none transition-all bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                        placeholder="Enter stock quantity"
                    />

                    <div
                        v-if="form.errors.stock"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ form.errors.stock }}
                    </div>

                </div>

                <!-- ACTIVE -->
                <div class="mb-6 flex items-center gap-3">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" v-model="form.is_active" class="peer sr-only" />
                        <div class="h-6 w-11 rounded-full bg-[#D4AF37]/10 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-[#D4AF37] peer-checked:after:translate-x-full"></div>
                    </label>
                    <span class="text-gray-700 dark:text-[#F5F5F5] font-medium">Active (visible on store)</span>
                </div>

                <!-- CURRENT IMAGE -->
                <div
                    v-if="product.image"
                    class="mb-6"
                >

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        Current Image
                    </label>

                    <img
                        :src="`/${product.image}`"
                        class="w-36 h-36 object-cover rounded-lg border border-gray-200 dark:border-[#D4AF37]/20"
                    />

                </div>

                <!-- NEW IMAGE -->
                <div class="mb-8">

                    <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                        New Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        @input="form.image = $event.target.files[0]"
                        class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 bg-white dark:bg-[#1A1A1A] dark:text-[#F5F5F5]"
                    />

                    <div
                        v-if="form.errors.image"
                        class="text-red-500 text-sm mt-2"
                    >
                        {{ form.errors.image }}
                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="flex gap-4">

                    <button
                        type="submit"
                        class="bg-[#D4AF37] hover:bg-[#B8960F] text-white px-6 py-3 rounded-xl font-semibold transition active:scale-95 cursor-pointer"
                    >
                        Update Product
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
