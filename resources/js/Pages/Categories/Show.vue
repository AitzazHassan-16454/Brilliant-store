<script setup>
import { Head } from "@inertiajs/vue3"
import ShopCatalog from "../../Components/ShopCatalog.vue"

const props = defineProps({
  category: { type: Object, required: true },
  products: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  subcategories: { type: Array, default: () => [] },
  otherCategories: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  cartProductIds: { type: Array, default: () => [] },
  wishlistProductIds: { type: Array, default: () => [] },
})
</script>

<template>
  <Head :title="category?.name ? `${category.name} - Shop` : 'Category'" />
  <ShopCatalog
    :category="category"
    :products="products"
    :filters="filters"
    :subcategories="subcategories"
    :categories="categories && categories.length ? categories : [category, ...otherCategories]"
    :cart-product-ids="cartProductIds"
    :wishlist-product-ids="wishlistProductIds"
  />
</template>
