<script setup>
import { Link, router, Head, usePage } from "@inertiajs/vue3";
import Sidebar from "../components/Sidebar.vue";
import Pagination from "../components/Pagination.vue";

defineOptions({ layout: false })

defineProps({
  products: Object
});

const deleteProduct = (uid) => {
  if (confirm("Are you sure you want to delete this product?")) {
    router.delete(`/products/${uid}`);
  }
};

const page = usePage();
const can = (permission) => Boolean(page.props.permissions?.can?.[permission]);
const canManageProducts = () => can('products.update') || can('products.delete');
</script>
<template>
  <Head title="Products" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <!-- SIDEBAR -->
    <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6 lg:p-8 overflow-x-auto">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

        <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
          All Products
        </h1>

        <div class="flex items-center gap-3">
          <Link
            v-if="can('products.create')"
            href="/CreateProduct"
            class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white rounded-xl font-semibold shadow-sm"
          >
            + Add Product
          </Link>
        </div>

      </div>

      <!-- TABLE CARD -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-sm overflow-hidden">

        <!-- TITLE -->
        <div class="p-5 border-b border-gray-100 dark:border-[#D4AF37]/20">
          <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
            Products List
          </h3>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

          <table class="w-full text-sm text-left">

            <!-- HEAD -->
            <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
              <tr>
                <th class="p-4">Image</th>
                <th class="p-4">Name</th>
                <th class="p-4">Category</th>
                <th class="p-4">Price</th>
                <th class="p-4">Stock</th>
                <th class="p-4">Status</th>
                <th v-if="canManageProducts()" class="p-4 text-center">Actions</th>
              </tr>
            </thead>

            <!-- BODY -->
            <tbody>

              <tr
                v-for="product in products.data"
                :key="product.id"
                class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#30363d]/50"
              >

                <!-- IMAGE -->
                <td class="p-4">
                  <img
                    :src="`/storage/${product.image}`"
                    class="w-14 h-14 rounded-xl object-cover border border-gray-200 dark:border-[#D4AF37]/20"
                  />
                </td>

                <!-- NAME -->
                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                  {{ product.name }}
                </td>

                <!-- CATEGORY -->
                <td class="p-4 text-gray-500 dark:text-[#A0A0A0]">
                  {{ product.category?.name }}
                </td>

                <!-- PRICE -->
                <td class="p-4 font-semibold text-green-600">
                  ${{ product.price }}
                </td>

                <!-- STOCK -->
                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                  {{ product.stock }}
                </td>

                <!-- STATUS -->
                <td class="p-4">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-xs font-semibold',
                      product.is_active ? 'bg-green-100 dark:bg-[#3fb950]/10 text-green-700' : 'bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0]'
                    ]"
                  >
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>

                <!-- ACTIONS -->
                <td v-if="canManageProducts()" class="p-4">

                  <div class="flex justify-center gap-2">

                    <Link
                      v-if="can('products.update')"
                      :href="`/products/${product.uid}/edit`"
                      class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] hover:bg-yellow-500 dark:hover:bg-yellow-500 hover:text-white"
                    >
                      Edit
                    </Link>

                    <button
                      v-if="can('products.delete')"
                      @click="deleteProduct(product.uid)"
                      class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] hover:bg-red-500 dark:hover:bg-red-500 hover:text-white"
                    >
                      Delete
                    </button>

                  </div>

                </td>

              </tr>

              <!-- EMPTY -->
              <tr v-if="products.data.length === 0">
                <td colspan="7" class="p-10 text-center text-gray-400 dark:text-[#A0A0A0]">
                  No products found
                </td>
              </tr>

            </tbody>

          </table>

           <div v-if="products.last_page > 1" class="max-w-7xl mx-auto px-4 sm:px-6 pb-12">
    <Pagination :links="products.links" />
  </div>

        </div>

      </div>

    </main>

  </div>
</template>
