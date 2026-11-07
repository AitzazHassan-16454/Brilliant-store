<script setup>
import { computed, ref, onMounted } from "vue"
import { router, Link, Head, usePage } from "@inertiajs/vue3"
import { motion } from "motion-v"
import Modal from "../Components/Modal.vue"
import { useNotification } from "../composables/useNotification.js"

const props = defineProps({
  cartItems: { type: Array, default: () => [] },
})

const page = usePage()
const { success, error, info } = useNotification()
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

// Auto-fill shipping info from authenticated user if available
onMounted(() => {
  if (page.props.auth?.user) {
    if (!shipping.value.name) shipping.value.name = page.props.auth.user.name || ""
    if (!shipping.value.email) shipping.value.email = page.props.auth.user.email || ""
  }
})

const shippingErrors = ref({})

const paymentMethod = ref("stripe")
const paymentMethods = [
  {
    value: "stripe",
    label: "Credit / Debit Card (Stripe)",
    description: "Pay securely online with credit or debit card via Stripe.",
    badge: "Instant",
  },
  {
    value: "cod",
    label: "Cash on Delivery",
    description: "Pay with cash directly when your order is delivered.",
    badge: "Popular",
  },
  {
    value: "bank_transfer",
    label: "Direct Bank Transfer",
    description: "Transfer directly to our account and share payment slip.",
    badge: "Secure",
  },
  {
    value: "whatsapp",
    label: "Personal Concierge (WhatsApp)",
    description: "Finalize and confirm payment directly with our team.",
    badge: "Assisted",
  },
]

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

  router.post(
    "/coupons/validate",
    {
      code: couponCode.value,
      subtotal: subtotal.value,
    },
    {
      preserveScroll: true,
      onSuccess: (page) => {
        const flash = page.props.flash
        if (flash?.coupon) {
          appliedCoupon.value = flash.coupon
          couponDiscount.value = flash.coupon.discount
          couponError.value = ""
          success({
            title: "Promotion Applied",
            message: `Code "${flash.coupon.code}" applied for a $${flash.coupon.discount.toFixed(2)} discount.`,
          })
        } else if (flash?.error) {
          appliedCoupon.value = null
          couponDiscount.value = 0
          couponError.value = flash.error
          error({
            title: "Invalid Code",
            message: flash.error,
          })
        }
      },
      onFinish: () => {
        couponLoading.value = false
      },
    }
  )
}

const removeCoupon = () => {
  appliedCoupon.value = null
  couponDiscount.value = 0
  couponError.value = ""
  couponCode.value = ""
  info({
    title: "Promotion Removed",
    message: "Promotional code was removed from your order.",
  })
}

const removeItem = (id) => {
  deleteId.value = id
}

const destroyItem = () => {
  router.delete(`/cart/${deleteId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      success({
        title: "Item Removed",
        message: "The piece has been removed from your shopping bag.",
      })
    },
    onFinish: () => (deleteId.value = null),
  })
}

const increaseQty = (id) => {
  router.post(`/cart/increase/${id}`, {}, { preserveScroll: true })
}

const decreaseQty = (id) => {
  router.post(`/cart/decrease/${id}`, {}, { preserveScroll: true })
}

const confirmOrder = () => {
  loading.value = true
  shippingErrors.value = {}

  router.post(
    "/checkout",
    {
      coupon_code: appliedCoupon.value
        ? appliedCoupon.value.code
        : couponCode.value.trim() || null,
      shipping_name: shipping.value.name,
      shipping_email: shipping.value.email,
      shipping_phone: shipping.value.phone,
      shipping_address: shipping.value.address,
      shipping_city: shipping.value.city,
      shipping_postal_code: shipping.value.postal_code,
      notes: shipping.value.notes,
      payment_method: paymentMethod.value,
    },
    {
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
          shipping.value = {
            name: "",
            email: "",
            phone: "",
            address: "",
            city: "",
            postal_code: "",
            notes: "",
          }
        }
      },
    }
  )
}
</script>

<template>
  <div class="min-h-screen bg-[var(--background)] text-neutral-900 dark:text-neutral-100 pb-24 selection:bg-[#D4AF37]/30">
    <Head title="Shopping Bag — Curated Collection" />

    <!-- TOP NAVIGATION BREADCRUMB -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 pb-4">
      <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
        <Link href="/" class="hover:text-[#D4AF37] transition-colors no-underline">Home</Link>
        <span class="text-neutral-300 dark:text-neutral-600">/</span>
        <span class="text-neutral-900 dark:text-white font-bold">Shopping Bag</span>
      </nav>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

      <!-- PAGE HEADER -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-neutral-200/80 dark:border-white/10">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-[#D4AF37]/15 text-[#D4AF37] border border-[#D4AF37]/30 shadow-xs">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
              Shopping Bag
            </h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
              {{ cartItems.length }} {{ cartItems.length === 1 ? 'item' : 'items' }} selected for acquisition
            </p>
          </div>
        </div>

        <Link
          href="/all-products"
          class="inline-flex items-center gap-2 text-xs font-bold text-[#D4AF37] hover:underline transition-all no-underline"
        >
          <span>&larr;</span> Continue Shopping
        </Link>
      </div>

      <!-- FLASH NOTIFICATIONS -->
      <div
        v-if="$page.props.flash?.success"
        class="mb-6 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center gap-2.5 bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-[#D4AF37]"
      >
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <!-- EMPTY STATE -->
      <motion.div
        v-if="cartItems.length === 0"
        initial="{ opacity: 0, scale: 0.96 }"
        animate="{ opacity: 1, scale: 1 }"
        class="rounded-3xl p-12 sm:p-20 text-center flex flex-col items-center gap-4 bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-[#D4AF37]/20 shadow-xl max-w-xl mx-auto my-10"
      >
        <div class="w-20 h-20 rounded-full flex items-center justify-center bg-[#D4AF37]/10 text-[#D4AF37] border border-[#D4AF37]/20 mb-2">
          <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-extrabold text-neutral-900 dark:text-white">Your Shopping Bag is Empty</h2>
          <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm">
            You haven't selected any luxury pieces yet. Explore our curated collections to find handcrafted excellence.
          </p>
        </div>
        <Link
          href="/all-products"
          class="mt-3 inline-flex items-center gap-2 px-8 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider text-neutral-950 bg-[#D4AF37] hover:bg-[#B8941E] shadow-md shadow-[#D4AF37]/25 transition-all no-underline"
        >
          Explore All Products &rarr;
        </Link>
      </motion.div>

      <!-- TWO COLUMN CART LAYOUT -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

        <!-- LEFT COLUMN: CART ITEMS LIST -->
        <div class="lg:col-span-8 flex flex-col gap-4">

          <!-- Complimentary delivery notice -->
          <div class="p-4 rounded-2xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-white/10 flex items-center justify-between text-xs shadow-xs">
            <div class="flex items-center gap-2.5">
              <svg class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
              </svg>
              <span class="text-neutral-700 dark:text-neutral-300 font-medium">
                Complimentary insured courier delivery unlocked on this order.
              </span>
            </div>
            <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">
              Free Shipping
            </span>
          </div>

          <!-- Items loop -->
          <div
            v-for="item in cartItems"
            :key="item.id"
            class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-white/10 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 transition-all hover:border-[#D4AF37]/40"
          >
            <!-- Product Thumbnail -->
            <Link
              :href="`/products/${item.product.uid}`"
              class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden shrink-0 bg-neutral-100 dark:bg-neutral-900 border border-neutral-200/60 dark:border-neutral-800 relative block group"
            >
              <img
                v-if="item.product.image"
                :src="`/storage/${item.product.image}`"
                :alt="item.product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-xs text-[#D4AF37] font-bold">
                {{ item.product.name.charAt(0) }}
              </div>
            </Link>

            <!-- Product Details -->
            <div class="flex-1 min-w-0">
              <span
                v-if="item.product.category"
                class="text-[10px] font-bold uppercase tracking-wider text-[#D4AF37] block mb-0.5"
              >
                {{ item.product.category.name }}
              </span>

              <Link
                :href="`/products/${item.product.uid}`"
                class="text-sm sm:text-base font-bold text-neutral-900 dark:text-white truncate block hover:text-[#D4AF37] transition-colors no-underline"
              >
                {{ item.product.name }}
              </Link>

              <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                ${{ Number(item.product.price).toFixed(2) }} each
              </p>

              <!-- Quantity Controls -->
              <div class="flex items-center gap-2 mt-3">
                <button
                  @click="decreaseQty(item.id)"
                  class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-sm border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer"
                  title="Decrease quantity"
                >
                  −
                </button>

                <span class="w-8 text-center text-xs font-bold text-neutral-900 dark:text-white">
                  {{ item.qty }}
                </span>

                <button
                  @click="increaseQty(item.id)"
                  :disabled="item.qty >= item.product.stock"
                  :class="[
                    'w-7 h-7 rounded-lg flex items-center justify-center font-bold text-sm border transition-colors cursor-pointer',
                    item.qty >= item.product.stock
                      ? 'border-neutral-200 dark:border-neutral-800 bg-neutral-100 dark:bg-neutral-900 text-neutral-400 cursor-not-allowed'
                      : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800'
                  ]"
                  title="Increase quantity"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Line Price & Remove Action -->
            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-neutral-100 dark:border-neutral-800">
              <div class="text-left sm:text-right">
                <span class="text-[10px] uppercase tracking-wider text-neutral-400 block font-semibold">Subtotal</span>
                <span class="text-base sm:text-lg font-extrabold text-neutral-900 dark:text-white">
                  ${{ (item.product.price * item.qty).toFixed(2) }}
                </span>
              </div>

              <button
                @click="removeItem(item.id)"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span>Remove</span>
              </button>
            </div>

          </div>

        </div>

        <!-- RIGHT COLUMN: STICKY ORDER SUMMARY & CHECKOUT -->
        <div class="lg:col-span-4 w-full lg:sticky lg:top-24">
          <div class="p-6 rounded-3xl bg-white dark:bg-[#1A1A1A] border border-neutral-200/80 dark:border-[#D4AF37]/20 shadow-xl space-y-6">

            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
              <h2 class="text-base font-bold uppercase tracking-wider text-neutral-900 dark:text-white">
                Order Summary
              </h2>
              <span class="text-xs text-neutral-400">
                {{ cartItems.length }} {{ cartItems.length === 1 ? 'item' : 'items' }}
              </span>
            </div>

            <!-- Direct Coupon Code Input -->
            <div class="pro-form-group">
              <label class="pro-label">
                <span>Promotional Code</span>
              </label>

              <div v-if="!appliedCoupon" class="flex gap-2">
                <input
                  v-model="couponCode"
                  type="text"
                  placeholder="e.g. LUXURY20"
                  class="pro-input font-mono uppercase"
                  :class="{ 'pro-input-error': couponError }"
                />
                <button
                  type="button"
                  @click="applyCoupon"
                  :disabled="couponLoading || !couponCode.trim()"
                  class="pro-btn-primary w-auto px-5 whitespace-nowrap text-xs"
                >
                  <svg v-if="couponLoading" class="w-3.5 h-3.5 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span>{{ couponLoading ? '...' : 'Apply' }}</span>
                </button>
              </div>

              <!-- Applied coupon banner -->
              <div
                v-else
                class="flex items-center justify-between px-3 py-2 rounded-xl bg-[#D4AF37]/10 border border-[#D4AF37]/30 text-xs font-bold text-[#D4AF37]"
              >
                <div class="flex items-center gap-1.5 font-mono">
                  <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386l3.874-2.274c.827-.486 1.168-1.504.757-2.336L16.5 10.5M9 7.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                  </svg>
                  <span>{{ appliedCoupon.code }}</span>
                  <span class="text-[11px] font-sans font-normal opacity-90">(-${{ couponDiscount.toFixed(2) }})</span>
                </div>
                <button
                  @click="removeCoupon"
                  class="text-xs text-neutral-500 hover:text-rose-500 underline cursor-pointer"
                >
                  Remove
                </button>
              </div>

              <p v-if="couponError" class="text-xs text-rose-500 font-medium">
                {{ couponError }}
              </p>
            </div>

            <!-- Price Breakdown Calculation -->
            <div class="space-y-3 pt-2 text-xs sm:text-sm">
              <div class="flex justify-between text-neutral-500 dark:text-neutral-400">
                <span>Items Subtotal</span>
                <span class="font-bold text-neutral-900 dark:text-white">${{ subtotal.toFixed(2) }}</span>
              </div>

              <div v-if="couponDiscount > 0" class="flex justify-between text-[#D4AF37]">
                <span>Promotional Savings</span>
                <span class="font-bold">-${{ couponDiscount.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between text-neutral-500 dark:text-neutral-400">
                <span>Insured Courier Delivery</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Complimentary</span>
              </div>

              <div class="flex justify-between text-neutral-500 dark:text-neutral-400">
                <span>Estimated Sales Taxes</span>
                <span class="font-semibold text-neutral-900 dark:text-white">Included</span>
              </div>

              <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex justify-between items-baseline">
                <div>
                  <span class="text-xs uppercase font-extrabold tracking-wider text-neutral-900 dark:text-white block">Total Investment</span>
                  <span class="text-[10px] text-neutral-400">Guaranteed final pricing</span>
                </div>
                <span class="text-2xl sm:text-3xl font-extrabold text-[#D4AF37]">
                  ${{ total.toFixed(2) }}
                </span>
              </div>
            </div>

            <!-- Checkout Trigger CTA -->
            <button
              @click="showCheckoutModal = true"
              class="w-full py-4 rounded-2xl bg-[#D4AF37] hover:bg-[#B8941E] text-neutral-950 font-extrabold text-sm tracking-wide shadow-md shadow-[#D4AF37]/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Proceed to Checkout</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
              </svg>
            </button>

            <!-- Trust Badges -->
            <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800 space-y-2 text-[11px] text-neutral-500 dark:text-neutral-400">
              <div class="flex items-center gap-2.5">
                <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>256-Bit Bank-Grade SSL Encrypted Checkout</span>
              </div>
              <div class="flex items-center gap-2.5">
                <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.125A1.125 1.125 0 0 0 13.125 3H4.875A1.125 1.125 0 0 0 3.75 4.125v10.125c0 .621.504 1.125 1.125 1.125h9.75" />
                </svg>
                <span>Insured & Tracked Express Dispatch</span>
              </div>
              <div class="flex items-center gap-2.5">
                <svg class="w-3.5 h-3.5 text-[#D4AF37] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                <span>30-Day Effortless Return Policy</span>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>

    <!-- REMOVE ITEM CONFIRMATION MODAL -->
    <Modal :show="!!deleteId" max-width="sm" @close="deleteId = null">
      <template #icon>
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-rose-500/10 text-rose-500 mx-auto">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
          </svg>
        </div>
      </template>

      <template #title>
        <h3 class="text-base font-bold text-center text-neutral-900 dark:text-white">Remove Piece from Bag?</h3>
      </template>

      <template #description>
        <p class="text-xs text-center text-neutral-500 mt-1">This item will be removed from your current order.</p>
      </template>

      <template #actions>
        <div class="flex items-center gap-3 w-full mt-4">
          <button
            @click="deleteId = null"
            class="flex-1 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="destroyItem"
            class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors cursor-pointer"
          >
            Remove Item
          </button>
        </div>
      </template>
    </Modal>

    <!-- CHECKOUT MODAL (SHIPPING & PAYMENT) -->
    <Modal :show="showCheckoutModal" max-width="lg" @close="showCheckoutModal = false">
      <template #title>
        <div class="flex items-center gap-2.5">
          <svg class="w-4 h-4 text-[#D4AF37] shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z"/>
          </svg>
          <h2 class="text-lg font-extrabold text-neutral-900 dark:text-white">Complete Your Order</h2>
        </div>
      </template>

      <template #description>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
          Enter your delivery destination and preferred payment method.
        </p>
      </template>

      <div class="mt-6 space-y-6">

        <!-- STEP 1: SHIPPING INFORMATION -->
        <div class="space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-[#D4AF37]">
            1. Delivery Address
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">
                Full Name <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="shipping.name"
                type="text"
                placeholder="Lord Byron"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
              <p v-if="shippingErrors.shipping_name" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.shipping_name }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">
                Email Address <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="shipping.email"
                type="email"
                placeholder="collector@domain.com"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
              <p v-if="shippingErrors.shipping_email" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.shipping_email }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">
                Phone Number <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="shipping.phone"
                type="tel"
                placeholder="+1 (555) 019-2834"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
              <p v-if="shippingErrors.shipping_phone" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.shipping_phone }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">
                City / Region <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="shipping.city"
                type="text"
                placeholder="New York"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
              <p v-if="shippingErrors.shipping_city" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.shipping_city }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">
              Street Address <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="shipping.address"
              type="text"
              placeholder="Suite, apartment, residential address"
              class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
            />
            <p v-if="shippingErrors.shipping_address" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.shipping_address }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">Postal / ZIP Code</label>
              <input
                v-model="shipping.postal_code"
                type="text"
                placeholder="10001"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold mb-1 text-neutral-700 dark:text-neutral-300">Delivery Instructions</label>
              <input
                v-model="shipping.notes"
                type="text"
                placeholder="Special notes (optional)"
                class="w-full rounded-xl px-3.5 py-2.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 focus:outline-none focus:border-[#D4AF37]"
              />
            </div>
          </div>
        </div>

        <!-- STEP 2: PAYMENT METHOD CARDS -->
        <div class="space-y-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
          <h3 class="text-xs font-bold uppercase tracking-wider text-[#D4AF37]">
            2. Payment Method
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <button
              v-for="method in paymentMethods"
              :key="method.value"
              type="button"
              @click="paymentMethod = method.value"
              :class="[
                'p-3 rounded-2xl border text-left transition-all flex flex-col justify-between cursor-pointer select-none',
                paymentMethod === method.value
                  ? 'border-[#D4AF37] bg-[#D4AF37]/10 text-neutral-900 dark:text-white shadow-xs'
                  : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50/60 dark:bg-neutral-900/60 text-neutral-600 dark:text-neutral-400 hover:border-neutral-300 dark:hover:border-neutral-700'
              ]"
            >
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold text-neutral-900 dark:text-white">{{ method.label }}</span>
                <span
                  class="text-[9px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded-full"
                  :class="paymentMethod === method.value ? 'bg-[#D4AF37] text-neutral-950' : 'bg-neutral-200 dark:bg-neutral-800 text-neutral-500'"
                >
                  {{ method.badge }}
                </span>
              </div>
              <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-normal">
                {{ method.description }}
              </p>
            </button>
          </div>
          <p v-if="shippingErrors.payment_method" class="text-[11px] text-rose-500 mt-1">{{ shippingErrors.payment_method }}</p>
        </div>

        <!-- STEP 3: ORDER TOTAL HIGHLIGHT -->
        <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
          <div>
            <span class="text-xs text-neutral-400 block">Final Amount Due</span>
            <span class="text-lg font-extrabold text-[#D4AF37]">${{ total.toFixed(2) }}</span>
          </div>
          <span class="text-[11px] text-neutral-500">Includes taxes & shipping</span>
        </div>

      </div>

      <template #actions>
        <div class="flex items-center gap-3 w-full mt-6">
          <button
            @click="showCheckoutModal = false"
            class="flex-1 py-3 rounded-xl border border-neutral-200 dark:border-neutral-800 text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer"
          >
            Cancel
          </button>
          <button
            @click="confirmOrder"
            :disabled="loading"
            class="flex-1 py-3 rounded-xl bg-[#D4AF37] hover:bg-[#B8941E] text-neutral-950 text-xs font-extrabold tracking-wide transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <span>{{ loading ? "Processing Order..." : `Place Order ($${total.toFixed(2)})` }}</span>
          </button>
        </div>
      </template>
    </Modal>

  </div>
</template>
