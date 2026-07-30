<script setup>
import { computed, ref } from "vue"
import { router, Link, Head, usePage } from "@inertiajs/vue3"
import { useInView } from "../composables/useInView.js"
import Modal from "../Components/Modal.vue"

const props = defineProps({
    cartItems: Array,
})

const page = usePage()
const deleteId = ref(null)
const showCheckoutModal = ref(false)
const loading = ref(false)

const couponCode = ref("")
const appliedCoupon = ref(null)
const couponDiscount = ref(0)
const couponError = ref("")
const couponLoading = ref(false)

const shipping = ref({
    name: "",
    email: "",
    phone: "",
    address: "",
    city: "",
    postal_code: "",
    notes: "",
})

const shippingErrors = ref({})

const subtotal = computed(() => {
    return props.cartItems.reduce((sum, item) => {
        return sum + item.product.price * item.qty
    }, 0)
})

const total = computed(() => {
    return Math.max(0, subtotal.value - couponDiscount.value)
})

const applyCoupon = () => {
    if (!couponCode.value.trim()) return
    couponLoading.value = true
    couponError.value = ""

    router.post("/coupons/validate", {
        code: couponCode.value,
        subtotal: subtotal.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash
            if (flash?.coupon) {
                appliedCoupon.value = flash.coupon
                couponDiscount.value = flash.coupon.discount
                couponError.value = ""
            } else if (flash?.error) {
                appliedCoupon.value = null
                couponDiscount.value = 0
                couponError.value = flash.error
            }
        },
        onFinish: () => {
            couponLoading.value = false
        },
    })
}

const removeCoupon = () => {
    appliedCoupon.value = null
    couponDiscount.value = 0
    couponError.value = ""
    couponCode.value = ""
}

const removeItem = (id) => { deleteId.value = id }

const destroyItem = () => {
    router.delete(`/cart/${deleteId.value}`, {
        preserveScroll: true,
        onFinish: () => (deleteId.value = null)
    })
}

const increaseQty = (id) => { router.post(`/cart/increase/${id}`, {}, { preserveScroll: true }) }
const decreaseQty = (id) => { router.post(`/cart/decrease/${id}`, {}, { preserveScroll: true }) }

const confirmOrder = () => {
    loading.value = true
    shippingErrors.value = {}

    router.post("/checkout", {
        coupon_code: appliedCoupon.value ? appliedCoupon.value.code : (couponCode.value.trim() || null),
        shipping_name: shipping.value.name,
        shipping_email: shipping.value.email,
        shipping_phone: shipping.value.phone,
        shipping_address: shipping.value.address,
        shipping_city: shipping.value.city,
        shipping_postal_code: shipping.value.postal_code,
        notes: shipping.value.notes,
    }, {
        preserveScroll: true,
        onError: (errors) => {
            shippingErrors.value = errors
        },
        onSuccess: (page) => {
            if (page.props.flash?.error) {
                return
            }
        },
        onFinish: () => {
            loading.value = false
            if (Object.keys(shippingErrors.value).length === 0 && !page.props.flash?.error) {
                showCheckoutModal.value = false
                couponCode.value = ""
                appliedCoupon.value = null
                couponDiscount.value = 0
                couponError.value = ""
                shipping.value = { name: "", email: "", phone: "", address: "", city: "", postal_code: "", notes: "" }
            }
        },
    })
}

const { target: itemsRef, isInView: itemsVisible } = useInView()
</script>

<template>
<Head>
  <title>Cart — Brilliant</title>
  <meta name="description" content="Review your cart items before checkout." />
</Head>

  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 dark:bg-[#0A0A0A]">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8 sm:mb-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#D4AF37]/10">
          <svg class="w-5 h-5" fill="none" stroke="#D4AF37" stroke-width="2" viewBox="0 0 24 24">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900 dark:text-[#F5F5F5]">Shopping Cart</h1>
          <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">{{ cartItems.length }} item{{ cartItems.length !== 1 ? 's' : '' }} in your cart</p>
        </div>
      </div>
      <Link href="/"
        class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold no-underline transition-colors text-gray-500 dark:text-[#A0A0A0] hover:text-gray-900 dark:hover:text-[#c9d1d9]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Continue Shopping
      </Link>
    </div>

    <!-- FLASH -->
    <Transition name="flash">
      <div v-if="$page.props.flash?.success"
        class="mb-5 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 bg-[#D4AF37]/5 border border-[#D4AF37]/20 text-[#D4AF37] dark:text-[#D4AF37]">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ $page.props.flash.success }}
      </div>
    </Transition>

    <!-- EMPTY STATE -->
    <div v-if="cartItems.length === 0"
      class="rounded-2xl p-16 text-center flex flex-col items-center gap-4 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 shadow-lg">
      <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-[#D4AF37]/10">
        <svg class="w-9 h-9" fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-[#F5F5F5]">Your cart is empty</h2>
        <p class="text-sm mt-1 text-gray-500 dark:text-[#A0A0A0]">Looks like you haven't added anything yet.</p>
      </div>
      <Link href="/"
        class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:-translate-y-0.5 no-underline mt-2 text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30">
        Start Shopping
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </Link>
    </div>

    <!-- CART LAYOUT -->
    <div v-else class="flex flex-col lg:flex-row gap-6 items-start">

      <!-- ITEMS LIST -->
      <div ref="itemsRef" class="flex-1 flex flex-col gap-3 w-full">
        <div v-for="(item, index) in cartItems" :key="item.id"
          :class="['rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4 items-start sm:items-center transition-all duration-300 hover:-translate-y-0.5 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 shadow-lg', itemsVisible ? 'animate-fade-in-up' : 'opacity-0']"
          :style="itemsVisible ? { animationDelay: `${Math.min(index * 0.06, 0.3)}s`, animationFillMode: 'both' } : {}">

          <!-- Image -->
          <Link :href="`/products/${item.product.uid}`" class="w-20 h-20 rounded-xl overflow-hidden shrink-0 bg-gray-50 dark:bg-[#1A1A1A] block">
            <img :src="`/storage/${item.product.image}`" :alt="item.product.name"
              class="w-full h-full object-cover" />
          </Link>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <Link :href="`/products/${item.product.uid}`" class="font-bold text-sm sm:text-base truncate no-underline transition-colors text-gray-900 dark:text-[#F5F5F5]">
              {{ item.product.name }}
            </Link>
            <p class="text-sm mt-0.5 text-gray-500 dark:text-[#A0A0A0]">${{ Number(item.product.price).toFixed(2) }} each</p>

            <!-- Qty controls -->
            <div class="flex items-center gap-2 mt-3">
              <button @click="decreaseQty(item.id)"
                class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-base transition-all duration-200 cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] hover:bg-stone-50 dark:hover:bg-[#21262d]">
                −
              </button>
              <span class="w-8 text-center font-bold text-sm text-gray-900 dark:text-[#F5F5F5]">{{ item.qty }}</span>
              <button @click="increaseQty(item.id)"
                class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-base transition-all duration-200 cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] hover:bg-stone-50 dark:hover:bg-[#21262d]">
                +
              </button>
            </div>
          </div>

          <!-- Subtotal + Remove -->
          <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 w-full sm:w-auto">
            <div class="text-right">
              <p class="text-xs font-medium text-gray-400 dark:text-[#A0A0A0]">Subtotal</p>
              <p class="text-lg font-extrabold text-gray-900 dark:text-[#F5F5F5]">${{ (item.product.price * item.qty).toFixed(2) }}</p>
            </div>
            <button @click="removeItem(item.id)"
              class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all duration-200 cursor-pointer text-red-600 bg-red-600/5 border border-transparent hover:border-red-600/15">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/></svg>
              Remove
            </button>
          </div>

        </div>
      </div>

      <!-- ORDER SUMMARY SIDEBAR -->
      <div class="w-full lg:w-80 shrink-0">
        <div class="rounded-2xl p-6 sticky top-24 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 shadow-lg">

          <h3 class="font-bold text-base mb-5 text-gray-900 dark:text-[#F5F5F5]">Order Summary</h3>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between text-gray-500 dark:text-[#A0A0A0]">
              <span>Subtotal</span>
              <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">${{ subtotal.toFixed(2) }}</span>
            </div>
            <div v-if="couponDiscount > 0" class="flex justify-between text-[#D4AF37] dark:text-[#D4AF37]">
              <span>Discount</span>
              <span class="font-semibold">-${{ couponDiscount.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-gray-500 dark:text-[#A0A0A0]">
              <span>Shipping</span>
              <span class="font-semibold text-[#D4AF37] dark:text-[#D4AF37]">Free</span>
            </div>
            <div class="h-px my-1 bg-gray-100 dark:bg-[#2A2A2A]"></div>
            <div class="flex justify-between">
              <span class="font-bold text-gray-900 dark:text-[#F5F5F5]">Total</span>
              <span class="font-extrabold text-xl text-gray-900 dark:text-[#F5F5F5]">${{ total.toFixed(2) }}</span>
            </div>
          </div>

          <button @click="showCheckoutModal = true"
            class="w-full mt-6 font-bold text-sm py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer text-white bg-gradient-to-br from-[#D4AF37] via-[#B8960F] to-[#D4AF37] shadow-lg shadow-[#D4AF37]/30 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Confirm Order
          </button>

          <p class="text-center text-xs mt-3 flex items-center justify-center gap-1 text-gray-400 dark:text-[#A0A0A0]">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Secure checkout
          </p>
        </div>
      </div>

    </div>
  </div>

<Modal :show="!!deleteId" @close="deleteId = null">
  <template #icon>
    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-600/10">
      <svg class="w-6 h-6" fill="none" stroke="#DC2626" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m5 0V4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2"/></svg>
    </div>
  </template>
  <template #title>
    <h2 class="text-base font-bold text-gray-900 dark:text-[#F5F5F5]">Remove this item?</h2>
  </template>
  <template #description>
    <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">This will remove the item from your cart.</p>
  </template>
  <template #actions>
    <button @click="deleteId = null"
      class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-900 dark:text-[#F5F5F5] hover:bg-stone-50 dark:hover:bg-[#21262d]">
      Cancel
    </button>
    <button @click="destroyItem"
      class="flex-1 px-4 py-2.5 rounded-xl text-sm font-bold text-white cursor-pointer bg-red-600 hover:bg-red-700">
      Remove
    </button>
  </template>
</Modal>

<Modal :show="showCheckoutModal" max-width="md" @close="showCheckoutModal = false">
  <template #title>
    <h2 class="text-lg font-extrabold text-gray-900 dark:text-[#F5F5F5]">Shipping Details</h2>
  </template>
  <template #description>
    <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Enter your delivery information.</p>
  </template>

  <div class="mt-5 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Full Name <span class="text-red-600">*</span></label>
        <input v-model="shipping.name" type="text" placeholder="John Doe"
          class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
          autocomplete="off" />
        <p v-if="shippingErrors.shipping_name" class="text-xs mt-1 text-red-600">{{ shippingErrors.shipping_name }}</p>
      </div>
      <div>
        <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Email <span class="text-red-600">*</span></label>
        <input v-model="shipping.email" type="email" placeholder="john@example.com"
          class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
          autocomplete="off" />
        <p v-if="shippingErrors.shipping_email" class="text-xs mt-1 text-red-600">{{ shippingErrors.shipping_email }}</p>
      </div>
    </div>
    <div>
      <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Phone <span class="text-red-600">*</span></label>
      <input v-model="shipping.phone" type="tel" placeholder="03XX XXXXXXX"
        class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
        autocomplete="off" />
      <p v-if="shippingErrors.shipping_phone" class="text-xs mt-1 text-red-600">{{ shippingErrors.shipping_phone }}</p>
    </div>
    <div>
      <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Address <span class="text-red-600">*</span></label>
      <input v-model="shipping.address" type="text" placeholder="Street address, apartment, suite, etc."
        class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
        autocomplete="off" />
      <p v-if="shippingErrors.shipping_address" class="text-xs mt-1 text-red-600">{{ shippingErrors.shipping_address }}</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">City <span class="text-red-600">*</span></label>
        <input v-model="shipping.city" type="text" placeholder="Lahore"
          class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
          autocomplete="off" />
        <p v-if="shippingErrors.shipping_city" class="text-xs mt-1 text-red-600">{{ shippingErrors.shipping_city }}</p>
      </div>
      <div>
        <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Postal Code</label>
        <input v-model="shipping.postal_code" type="text" placeholder="54000"
          class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
          autocomplete="off" />
      </div>
    </div>
    <div>
      <label class="block text-xs font-semibold mb-1.5 text-gray-900 dark:text-[#F5F5F5]">Order Notes (optional)</label>
      <textarea v-model="shipping.notes" rows="2" placeholder="Any special instructions for delivery..."
        class="w-full rounded-xl px-3 py-2.5 text-sm outline-none transition-all resize-none border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
        autocomplete="off"></textarea>
    </div>
  </div>

  <div class="mt-5">
    <div v-if="!appliedCoupon" class="flex gap-2">
      <input v-model="couponCode" type="text" placeholder="Coupon code"
        class="flex-1 rounded-xl px-3 py-2 text-sm outline-none transition-all font-mono uppercase border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-[#F5F5F5] placeholder-gray-400 dark:placeholder-slate-500 focus:border-[#D4AF37]"
        autocomplete="off" />
      <button @click="applyCoupon" :disabled="couponLoading || !couponCode.trim()"
        class="px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-all duration-200 whitespace-nowrap cursor-pointer bg-[#D4AF37] hover:bg-[#B8960F]">
        {{ couponLoading ? '...' : 'Apply' }}
      </button>
    </div>
    <div v-else class="flex items-center justify-between rounded-xl px-3 py-2 bg-[#D4AF37]/5 border border-[#D4AF37]/20">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="#D4AF37" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span class="text-sm font-bold font-mono text-[#D4AF37] dark:text-[#D4AF37]">{{ appliedCoupon.code }}</span>
      </div>
      <button @click="removeCoupon" class="text-xs font-semibold transition-colors cursor-pointer text-[#D4AF37] dark:text-[#D4AF37] hover:text-red-600">Remove</button>
    </div>
    <p v-if="couponError" class="text-xs mt-1.5 font-medium text-red-600">{{ couponError }}</p>
  </div>

  <div class="mt-5 rounded-xl p-4 space-y-3 border border-gray-200 dark:border-[#D4AF37]/20 bg-stone-50 dark:bg-[#1A1A1A]/50">
    <div class="flex justify-between text-sm">
      <span class="text-gray-500 dark:text-[#A0A0A0]">Items</span>
      <span class="font-semibold text-gray-900 dark:text-[#F5F5F5]">{{ cartItems.length }}</span>
    </div>
    <div class="flex justify-between text-sm">
      <span class="text-gray-500 dark:text-[#A0A0A0]">Shipping</span>
      <span class="font-semibold text-[#D4AF37] dark:text-[#D4AF37]">Free</span>
    </div>
    <div v-if="appliedCoupon" class="flex justify-between text-sm">
      <span class="text-[#D4AF37] dark:text-[#D4AF37]">Coupon ({{ appliedCoupon.code }})</span>
      <span class="font-semibold text-[#D4AF37] dark:text-[#D4AF37]">-${{ couponDiscount.toFixed(2) }}</span>
    </div>
    <div class="h-px bg-gray-100 dark:bg-[#2A2A2A]"></div>
    <div class="flex justify-between">
      <span class="font-bold text-sm text-gray-900 dark:text-[#F5F5F5]">Total</span>
      <span class="font-extrabold text-gray-900 dark:text-[#F5F5F5]">${{ total.toFixed(2) }}</span>
    </div>
  </div>

  <template #actions>
    <button @click="showCheckoutModal = false"
      class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold cursor-pointer border border-gray-200 dark:border-[#D4AF37]/20 bg-transparent text-gray-900 dark:text-[#F5F5F5] hover:bg-stone-50 dark:hover:bg-[#21262d]">
      Cancel
    </button>
    <button @click="confirmOrder" :disabled="loading"
      class="flex-1 px-4 py-2.5 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer bg-[#D4AF37] hover:bg-[#B8960F]">
      <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
      {{ loading ? "Processing..." : "Place Order" }}
    </button>
  </template>
</Modal>
</template>
