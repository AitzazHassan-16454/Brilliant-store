<script setup>
import { ref, watch } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";
import Pagination from "../components/Pagination.vue";
import { useNotification } from "../../composables/useNotification.js";

const { notify, error, success } = useNotification();

defineOptions({ layout: false });

const props = defineProps({
    subcategories: Object,
    categories: Array,
    filters: Object,
});

const name = ref("");
const categoryId = ref(props.filters?.category_id || "");
const editingId = ref(null);
const page = usePage();
const can = (permission) => Boolean(page.props.permissions?.can?.[permission]);
const canManage = () => can('categories.update') || can('categories.delete');

const reset = () => {
    name.value = "";
    categoryId.value = "";
    editingId.value = null;
};

const filterByCategory = () => {
    router.get("/subcategories", {
        category_id: categoryId.value,
    }, { preserveState: true, replace: true });
};

watch(categoryId, filterByCategory);

const save = () => {
    if (!name.value.trim()) {
        error("Subcategory name is required");
        return;
    }
    if (!categoryId.value) {
        error("Please select a category");
        return;
    }

    if (editingId.value) {
        if (!can('categories.update')) return;

        router.put(`/subcategories/${editingId.value}`, {
            name: name.value,
            category_id: categoryId.value,
        }, {
            onSuccess: () => {
                success("Subcategory Updated");
                reset();
            }
        });
    } else {
        if (!can('categories.create')) return;

        router.post("/subcategories", {
            name: name.value,
            category_id: categoryId.value,
        }, {
            onSuccess: () => {
                success("Subcategory Added");
                reset();
            }
        });
    }
};

const edit = (sub) => {
    if (!can('categories.update')) return;

    name.value = sub.name;
    categoryId.value = sub.category_id;
    editingId.value = sub.uid;
};

const remove = (uid) => {
    if (!can('categories.delete')) return;

    if (!confirm("Delete this subcategory?")) return;

    router.delete(`/subcategories/${uid}`, {
        onSuccess: () => {
            success("Subcategory Deleted");
        }
    });
};
</script>

<template>
<Head title="Subcategories" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <Sidebar />

    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Subcategories
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">
                    Manage subcategories for each category
                </p>
            </div>
        </div>

        <div v-if="can('categories.create') || can('categories.update')" class="flex flex-col sm:flex-row gap-3 mb-6">
            <select
                v-model="categoryId"
                class="border border-gray-200 px-4 py-2.5 rounded-xl dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] outline-none text-sm w-full sm:w-56 shrink-0"
            >
                <option value="">All Categories</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                </option>
            </select>

            <input
                v-model="name"
                type="text"
                placeholder="Enter subcategory name"
                class="border border-gray-200 px-4 py-2.5 flex-1 rounded-xl dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] outline-none focus:border-gray-400 dark:focus:border-gray-500 text-sm"
            />

            <button
                @click="save"
                class="bg-[#D4AF37] hover:bg-[#B8960F] text-white px-5 py-2.5 rounded-xl font-semibold text-sm cursor-pointer transition shadow-xs w-full sm:w-auto"
            >
                {{ editingId ? "Update Subcategory" : "Add Subcategory" }}
            </button>
        </div>

        <div v-if="categoryId" class="mb-4 flex items-center gap-2 flex-wrap">
            <span class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0]">Showing subcategories for:</span>
            <span class="px-3 py-1 bg-[#1a1f36] text-white text-xs sm:text-sm font-semibold rounded-full">
                {{ categories.find(c => c.id == categoryId)?.name }}
            </span>
            <button
                @click="categoryId = ''"
                class="text-xs sm:text-sm text-gray-400 hover:text-gray-600 dark:text-[#A0A0A0] dark:hover:text-slate-400 underline cursor-pointer"
            >
                Clear filter
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20 mb-6">

            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
                <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Subcategories List
                </h3>
                <span class="text-xs text-gray-400 dark:text-[#A0A0A0] lg:hidden">
                    Scroll horizontally &rarr;
                </span>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left min-w-[560px]">

                    <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
                        <tr>
                            <th class="p-4">ID</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Category</th>
                            <th v-if="canManage()" class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr
                            v-for="sub in subcategories.data"
                            :key="sub.id"
                            class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]/50"
                        >

                            <td class="p-4 text-gray-700 dark:text-[#F5F5F5]">
                                #{{ sub.id }}
                            </td>

                            <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                                {{ sub.name }}
                            </td>

                            <td class="p-4 text-gray-600 dark:text-[#A0A0A0]">
                                {{ sub.category?.name }}
                            </td>

                            <td v-if="canManage()" class="p-4">
                                <div class="flex justify-center gap-2">

                                    <button
                                        v-if="can('categories.update')"
                                        @click="edit(sub)"
                                        class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-lg hover:bg-yellow-500 hover:text-white dark:hover:bg-yellow-500 text-xs font-semibold cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        v-if="can('categories.delete')"
                                        @click="remove(sub.uid)"
                                        class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#1A1A1A] rounded-lg hover:bg-red-500 hover:text-white dark:hover:bg-red-500 text-xs font-semibold cursor-pointer"
                                    >
                                        Delete
                                    </button>

                                </div>
                            </td>

                        </tr>

                        <tr v-if="subcategories.data.length === 0">
                            <td colspan="4" class="p-10 text-center text-gray-400 dark:text-[#A0A0A0]">
                                No subcategories found
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        <Pagination :links="subcategories.links" :meta="subcategories" :per-page-options="[10, 25, 50, 100]" item-label="subcategories" />

    </main>

</div>
</template>
