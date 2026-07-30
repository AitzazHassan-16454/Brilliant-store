<script setup>
import { ref, computed } from "vue"
import { router, usePage, Link, Head } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"
import { useAuthModal } from "../composables/useAuthModal.js"
import AppFooter from "./components/AppFooter.vue"

const page = usePage()
const { success } = useNotification()
const { openLogin } = useAuthModal()

const whatsappNumber = "03414425591"

const whatsappUrl = computed(() => {
  const text = encodeURIComponent("Hi! I'd like to discuss a custom order.")
  return `https://wa.me/${whatsappNumber}?text=${text}`
})

const form = ref({
  name: page.props.auth?.user?.name || "",
  email: page.props.auth?.user?.email || "",
  phone: "",
  description: "",
  reference_image: null,
  budget_min: "",
  budget_max: "",
  desired_date: "",
})

const previewUrl = ref(null)
const submitting = ref(false)
const errors = ref({})
const showForm = ref(false)

const onImageChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.value.reference_image = file
  previewUrl.value = URL.createObjectURL(file)
}

const submit = () => {
  submitting.value = true
  errors.value = {}

  if (!page.props.auth?.user) {
    openLogin()
    submitting.value = false
    return
  }

  const payload = new FormData()
  payload.append("name", form.value.name)
  payload.append("email", form.value.email)
  payload.append("phone", form.value.phone)
  payload.append("description", form.value.description)
  if (form.value.reference_image) payload.append("reference_image", form.value.reference_image)
  if (form.value.budget_min) payload.append("budget_min", form.value.budget_min)
  if (form.value.budget_max) payload.append("budget_max", form.value.budget_max)
  if (form.value.desired_date) payload.append("desired_date", form.value.desired_date)

  router.post("/custom-order", payload, {
    onSuccess: () => {
      success("Custom order submitted! We'll contact you soon.")
      form.value = { name: page.props.auth?.user?.name || "", email: page.props.auth?.user?.email || "", phone: "", description: "", reference_image: null, budget_min: "", budget_max: "", desired_date: "" }
      previewUrl.value = null
      submitting.value = false
      showForm.value = false
    },
    onError: (err) => {
      errors.value = err
      submitting.value = false
    },
  })
}
</script>

<template>
  <Head title="Custom Order" />

  <div class="min-h-screen py-8 sm:py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

      <!-- Section Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-[#D4AF37]/30 bg-[#D4AF37]/10 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#D4AF37] dark:text-[#D4AF37] mb-4">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
          </svg>
          Custom Order
        </div>
        <h1 class="text-3xl sm:text-4xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Bring Your Vision to Life</h1>
        <p class="mt-2 text-base text-gray-500 dark:text-[#A0A0A0] max-w-xl mx-auto">
          Start a conversation on WhatsApp for the fastest response, or fill out the form below.
        </p>
      </div>

      <!-- Main Card: Split Layout -->
      <div class="mb-8 overflow-hidden rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] shadow-sm">
        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:gap-0">

          <!-- Left: Hero -->
          <div class="relative overflow-hidden p-8 sm:p-10 lg:p-12">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(212,175,55,0.12),transparent_40%),linear-gradient(135deg,rgba(212,175,55,0.06),transparent_55%)]"></div>

            <div class="relative">
              <h2 class="mb-6 max-w-2xl text-3xl font-semibold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5] sm:text-4xl">
                Share your idea and get a quick conversation started.
              </h2>
              <p class="max-w-2xl text-base leading-7 text-gray-500 dark:text-[#A0A0A0] sm:text-lg">
                Tell us your preferred style, size, budget, and references. We'll guide you personally and help shape the best custom piece for your space or gift.
              </p>

              <!-- CTA Buttons -->
              <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                <a :href="whatsappUrl" target="_blank" rel="noreferrer"
                  class="inline-flex items-center justify-center gap-3 rounded-2xl px-6 py-4 text-base font-semibold text-white shadow-lg transition-all hover:-translate-y-0.5"
                  style="background:#25D366; box-shadow:0 18px 40px -18px rgba(37,211,102,0.75);">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                  </svg>
                  Chat on WhatsApp
                  <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <button @click="showForm = !showForm"
                  class="inline-flex items-center justify-center gap-3 rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-6 py-4 text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] transition-all hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] hover:-translate-y-0.5 cursor-pointer">
                  <svg class="w-5 h-5 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                  </svg>
                  {{ showForm ? "Hide Form" : "Fill Out Form" }}
                </button>
              </div>

              <!-- Feature Cards -->
              <div class="mt-10 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] p-5">
                  <svg class="mb-3 h-6 w-6 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                  </svg>
                  <h3 class="font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Style Guidance</h3>
                  <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">Abstract, portraits, landscapes, luxury wall pieces, and more.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] p-5">
                  <svg class="mb-3 h-6 w-6 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16.5 12"/>
                  </svg>
                  <h3 class="font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Fast Response</h3>
                  <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">Quick replies on WhatsApp to discuss sizes, timing, and revisions.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] p-5">
                  <svg class="mb-3 h-6 w-6 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                  </svg>
                  <h3 class="font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Personal Follow-Up</h3>
                  <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">Direct human conversation instead of a long form workflow.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Sidebar -->
          <div class="border-t border-gray-200 dark:border-[#D4AF37]/20 bg-[#FAF7F2] dark:bg-[#0A0A0A] p-8 sm:p-10 lg:border-l lg:border-t-0">
            <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] p-6 shadow-sm">
              <p class="text-sm font-semibold uppercase tracking-widest text-[#D4AF37] dark:text-[#D4AF37]">What to Share</p>
              <h2 class="mt-3 text-xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Make your message more effective</h2>

              <div class="mt-6 space-y-4">
                <div class="flex items-start gap-3">
                  <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                  </svg>
                  <div>
                    <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Artwork type</p>
                    <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Abstract, portrait, calligraphy, landscape, or any custom idea.</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                  </svg>
                  <div>
                    <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Preferred size</p>
                    <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Share wall size or frame dimensions if you know them.</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                  </svg>
                  <div>
                    <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Budget range</p>
                    <p class="text-sm text-gray-500 dark:[#8b949e]">A rough budget helps suggest better options faster.</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#D4AF37] dark:text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                  </svg>
                  <div>
                    <p class="font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Reference images</p>
                    <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Attach screenshots, inspiration, or room photos directly in WhatsApp.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Collapsible Form -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-3 max-h-0 overflow-hidden"
        enter-to-class="opacity-100 translate-y-0 max-h-[2000px]"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 max-h-[2000px]"
        leave-to-class="opacity-0 -translate-y-3 max-h-0 overflow-hidden"
      >
        <div v-if="showForm" class="mb-8">
          <form @submit.prevent="submit" class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl p-6 sm:p-8 space-y-5">
            <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Or submit a request here</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Name</label>
                <input v-model="form.name" type="text" placeholder="Your name"
                  class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
                <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Email</label>
                <input v-model="form.email" type="email" placeholder="you@example.com"
                  class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
                <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Phone</label>
              <input v-model="form.phone" type="tel" placeholder="+1 234 567 890"
                class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
              <p v-if="errors.phone" class="text-xs text-red-500 mt-1">{{ errors.phone }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Describe What You Need</label>
              <textarea v-model="form.description" rows="4" placeholder="Describe your custom order in detail — size, colors, style, material, any specific requirements..."
                class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none resize-y bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]"></textarea>
              <p v-if="errors.description" class="text-xs text-red-500 mt-1">{{ errors.description }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Reference Image (optional)</label>
              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg cursor-pointer hover:bg-[#FAF7F2] dark:hover:bg-[#21262d] transition text-sm text-gray-600 dark:text-[#A0A0A0]">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                  </svg>
                  Upload Image
                  <input type="file" accept="image/*" @change="onImageChange" class="hidden" />
                </label>
                <span v-if="form.reference_image" class="text-xs text-gray-500 dark:text-[#A0A0A0]">{{ form.reference_image.name }}</span>
              </div>
              <img v-if="previewUrl" :src="previewUrl" class="mt-3 w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-[#D4AF37]/20" />
              <p v-if="errors.reference_image" class="text-xs text-red-500 mt-1">{{ errors.reference_image }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Budget Min ($)</label>
                <input v-model="form.budget_min" type="number" step="0.01" min="0" placeholder="0.00"
                  class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Budget Max ($)</label>
                <input v-model="form.budget_max" type="number" step="0.01" min="0" placeholder="0.00"
                  class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 dark:text-[#A0A0A0] mb-1">Desired Completion Date</label>
              <input v-model="form.desired_date" type="date"
                class="w-full border border-gray-200 dark:border-[#D4AF37]/20 rounded-lg px-4 py-2.5 text-sm outline-none bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5]" />
              <p v-if="errors.desired_date" class="text-xs text-red-500 mt-1">{{ errors.desired_date }}</p>
            </div>

            <div class="pt-2">
              <button type="submit" :disabled="submitting"
                class="w-full sm:w-auto px-8 py-3 bg-[#D4AF37] dark:bg-[#D4AF37] hover:bg-[#B8960F] dark:hover:bg-[#C9A032] text-white text-sm font-semibold rounded-xl transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ submitting ? "Submitting..." : "Submit Request" }}
              </button>
            </div>
          </form>
        </div>
      </transition>

      <!-- 3-Step Process -->
      <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] p-6 shadow-sm">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D4AF37]/10 text-[#D4AF37] dark:text-[#D4AF37]">
            <span class="text-lg font-bold">1</span>
          </div>
          <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Share Your Vision</h3>
          <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">Start via WhatsApp or the form above. Tell us about the style, concept, colors, and size you want.</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] p-6 shadow-sm">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D4AF37]/10 text-[#D4AF37] dark:text-[#D4AF37]">
            <span class="text-lg font-bold">2</span>
          </div>
          <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Get Proposal & Quote</h3>
          <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">We discuss details, guide the direction, and share pricing or execution options.</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] p-6 shadow-sm">
          <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D4AF37]/10 text-[#D4AF37] dark:text-[#D4AF37]">
            <span class="text-lg font-bold">3</span>
          </div>
          <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Move Into Production</h3>
          <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-[#A0A0A0]">Once details are confirmed, your custom piece moves forward with personal follow-up.</p>
        </div>
      </div>

    </div>
  </div>

  <AppFooter />
</template>
