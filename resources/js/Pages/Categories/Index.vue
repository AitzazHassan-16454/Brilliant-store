<script setup>
import { ref, watch } from "vue";
import { router, Head } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";
import { useNotification } from "../../composables/useNotification.js";

defineOptions({ layout: false });

const { notify, error, success } = useNotification();

const props = defineProps({
    categories: Array
});

const categories = ref(props.categories);

watch(
    () => props.categories,
    (newVal) => {
        categories.value = newVal;
    }
);

const name = ref("");
const description = ref("");
const imageFile = ref(null);
const imagePreview = ref(null);
const editingUid = ref(null);

const reset = () => {
    name.value = "";
    description.value = "";
    imageFile.value = null;
    imagePreview.value = null;
    editingUid.value = null;
};

const onImageSelect = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    imageFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => { imagePreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const save = () => {
    if (!name.value.trim()) {
        error("Category name is required");
        return;
    }

    const formData = new FormData();
    formData.append("name", name.value);
    formData.append("description", description.value);
    if (imageFile.value) {
        formData.append("image", imageFile.value);
    }

    if (editingUid.value) {
        formData.append("_method", "PUT");
        router.post(`/categories/${editingUid.value}`, formData, {
            onSuccess: () => {
                success("Category Updated");
                categories.value = categories.value.map(c =>
                    c.uid === editingUid.value
                        ? { ...c, name: name.value, description: description.value, image: imagePreview.value || c.image }
                        : c
                );
                reset();
            }
        });
    } else {
        router.post("/categories", formData, {
            onSuccess: (page) => {
                success("Category Added");
                if (page?.props?.category) {
                    categories.value.unshift(page.props.category);
                } else {
                    router.reload({ only: ["categories"] });
                }
                reset();
            }
        });
    }
};

const edit = (cat) => {
    name.value = cat.name;
    description.value = cat.description || "";
    imagePreview.value = cat.image ? (cat.image.startsWith("http") ? cat.image : `/storage/${cat.image}`) : null;
    imageFile.value = null;
    editingUid.value = cat.uid;
};

const remove = (uid) => {
    if (!confirm("Delete this category?")) return;

    router.delete(`/categories/${uid}`, {
        onSuccess: () => {
            categories.value = categories.value.filter(c => c.uid !== uid);
            success("Category Deleted");
        }
    });
};

const categoryImageUrl = (cat) => {
    if (!cat.image) return null;
    return cat.image.startsWith("http") ? cat.image : `/storage/${cat.image}`;
};
</script>

<template>
<Head title="Categories" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <Sidebar />

    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Categories
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">
                    Manage all product categories and collections
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-4 sm:p-6 mb-6 dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
            <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] mb-4">
                {{ editingUid ? "Edit Category" : "New Category" }}
            </h3>

            <div class="flex flex-col gap-4">
                <input
                    v-model="name"
                    type="text"
                    placeholder="Category name"
                    class="border border-gray-200 px-4 py-2.5 rounded-xl dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] outline-none focus:border-gray-400 dark:focus:border-gray-500 text-sm"
                />

                <textarea
                    v-model="description"
                    placeholder="Short description (optional)"
                    rows="2"
                    class="border border-gray-200 px-4 py-2.5 rounded-xl dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] outline-none focus:border-gray-400 dark:focus:border-gray-500 resize-none text-sm"
                />

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    <label class="cursor-pointer">
                        <span class="inline-block px-4 py-2.5 bg-[#FAF7F2] dark:bg-[#0A0A0A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-xl text-xs sm:text-sm font-medium text-gray-600 dark:text-[#A0A0A0] hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer transition">
                            {{ imageFile ? imageFile.name : "Choose category image" }}
                        </span>
                        <input type="file" accept="image/*" class="hidden" @change="onImageSelect" />
                    </label>

                    <img v-if="imagePreview" :src="imagePreview" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl object-cover border border-gray-200 dark:border-[#D4AF37]/20" />
                </div>

                <div class="flex flex-wrap gap-2.5 pt-1">
                    <button
                        @click="save"
                        class="bg-[#D4AF37] hover:bg-[#B8960F] text-white px-5 py-2.5 rounded-xl font-semibold text-sm cursor-pointer transition shadow-xs"
                    >
                        {{ editingUid ? "Update Category" : "Add Category" }}
                    </button>
                    <button
                        v-if="editingUid"
                        @click="reset"
                        class="px-5 py-2.5 rounded-xl font-semibold text-sm border border-gray-200 dark:border-[#D4AF37]/20 text-gray-600 dark:text-[#A0A0A0] hover:bg-gray-100 dark:hover:bg-[#0A0A0A] cursor-pointer transition"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">

            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
                <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                    Categories List
                </h3>
                <span class="text-xs text-gray-400 dark:text-[#A0A0A0] lg:hidden">
                    Scroll horizontally &rarr;
                </span>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left min-w-[620px]">

                    <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
                        <tr>
                            <th class="p-4">Image</th>
                            <th class="p-4">ID</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Description</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr
                            v-for="cat in categories"
                            :key="cat.uid"
                            class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]/50"
                        >
                            <td class="p-4">
                                <img
                                    v-if="categoryImageUrl(cat)"
                                    :src="categoryImageUrl(cat)"
                                    :alt="cat.name"
                                    class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-[#D4AF37]/20"
                                />
                                <div v-else class="w-12 h-12 rounded-xl bg-[#FAF7F2] dark:bg-[#0A0A0A] border border-gray-200 dark:border-[#D4AF37]/20 flex items-center justify-center text-xs text-gray-400 dark:text-[#A0A0A0]">
                                    No img
                                </div>
                            </td>

                            <td class="p-4 text-gray-700 dark:text-[#F5F5F5]">
                                #{{ cat.id }}
                            </td>

                            <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                                {{ cat.name }}
                            </td>

                            <td class="p-4 text-gray-500 dark:text-[#A0A0A0] max-w-[200px] truncate">
                                {{ cat.description || "—" }}
                            </td>

                            <td class="p-4">
                                <div class="flex justify-center gap-2">

                                    <button
                                        @click="edit(cat)"
                                        class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#0A0A0A] rounded-lg hover:bg-yellow-500 hover:text-white dark:hover:bg-yellow-500 cursor-pointer text-xs font-semibold"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        @click="remove(cat.uid)"
                                        class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#0A0A0A] rounded-lg hover:bg-red-500 hover:text-white dark:hover:bg-red-500 cursor-pointer text-xs font-semibold"
                                    >
                                        Delete
                                    </button>

                                </div>
                            </td>

                        </tr>

                        <tr v-if="categories.length === 0">
                            <td colspan="5" class="p-10 text-center text-gray-400 dark:text-[#A0A0A0]">
                                No categories found
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>
</template>