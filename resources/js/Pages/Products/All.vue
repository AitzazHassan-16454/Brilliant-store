<script setup>
import { Head } from "@inertiajs/vue3"
import ShopCatalog from "../../Components/ShopCatalog.vue"

const props = defineProps({
  products: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  subcategories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  cartProductIds: { type: Array, default: () => [] },
  wishlistProductIds: { type: Array, default: () => [] },
})
</script>

<template>
  <Head title="Shop Collection" />
  <ShopCatalog
    :products="products"
    :categories="categories"
    :subcategories="subcategories"
    :filters="filters"
    :cart-product-ids="cartProductIds"
    :wishlist-product-ids="wishlistProductIds"
  />
</template>
