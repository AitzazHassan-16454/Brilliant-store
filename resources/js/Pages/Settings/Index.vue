<script setup>
import { computed, onBeforeUnmount, ref } from "vue"
import { Head, useForm } from "@inertiajs/vue3"
import Sidebar from "../components/Sidebar.vue"

defineOptions({ layout: false })

const props = defineProps({
  settings: Object,
})

const logoInput = ref(null)
const logoPreview = ref(null)

const form = useForm({
  site_name: props.settings.site_name || "Brilliant",
  tagline: props.settings.tagline || "",
  logo: null,
  remove_logo: false,
  primary_color: props.settings.primary_color || "#D4AF37",
  accent_color: props.settings.accent_color || "#B8960F",
  contact_email: props.settings.contact_email || "",
  contact_hours: props.settings.contact_hours || "",
  footer_description: props.settings.footer_description || "",
})

const displayLogo = computed(() => {
  if (logoPreview.value) return logoPreview.value
  if (form.remove_logo) return null
  return props.settings.logo_url || null
})

const presets = [
  { name: "Classic Gold", primary: "#D4AF37", accent: "#B8960F" },
  { name: "Rose", primary: "#C76B7A", accent: "#9F4A58" },
  { name: "Emerald", primary: "#1F9D74", accent: "#157A59" },
  { name: "Sapphire", primary: "#3B82F6", accent: "#1D4ED8" },
  { name: "Violet", primary: "#8B5CF6", accent: "#6D28D9" },
  { name: "Onyx", primary: "#64748B", accent: "#334155" },
]

function handleLogo(event) {
  const file = event.target.files?.[0] || null
  form.logo = file
  form.remove_logo = false

  if (logoPreview.value) {
    URL.revokeObjectURL(logoPreview.value)
  }

  logoPreview.value = file ? URL.createObjectURL(file) : null
}

function removeLogo() {
  form.logo = null
  form.remove_logo = true

  if (logoPreview.value) {
    URL.revokeObjectURL(logoPreview.value)
    logoPreview.value = null
  }

  if (logoInput.value) {
    logoInput.value.value = ""
  }
}

function applyPreset(preset) {
  form.primary_color = preset.primary
  form.accent_color = preset.accent
}

function submit() {
  form.post("/settings", {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.logo = null
      form.remove_logo = false
      if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value)
        logoPreview.value = null
      }
      if (logoInput.value) {
        logoInput.value.value = ""
      }
    },
  })
}

onBeforeUnmount(() => {
  if (logoPreview.value) {
    URL.revokeObjectURL(logoPreview.value)
  }
})
</script>

<template>
  <Head title="Business Settings" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
    <Sidebar />

    <main class="min-w-0 flex-1 p-3.5 sm:p-6 lg:p-10 overflow-y-auto">
      <div class="mx-auto max-w-5xl">
        <div class="mb-8">
          <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] sm:text-3xl">Business Settings</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-[#A0A0A0]">Customize your storefront name, logo, colors, and contact details.</p>
        </div>

        <div
          v-if="form.recentlySuccessful"
          class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400"
        >
          Settings saved successfully.
        </div>

        <form class="space-y-6" @submit.prevent="submit">
          <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.3)] dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A] dark:shadow-none sm:p-6">
            <div class="mb-5">
              <h2 class="text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Brand identity</h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-[#A0A0A0]">The name and tagline shown across the storefront and admin panel.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <div>
                <label for="site_name" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Website name</label>
                <input
                  id="site_name"
                  v-model="form.site_name"
                  type="text"
                  maxlength="60"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                />
                <p v-if="form.errors.site_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.site_name }}</p>
              </div>

              <div>
                <label for="tagline" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Tagline</label>
                <input
                  id="tagline"
                  v-model="form.tagline"
                  type="text"
                  maxlength="80"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                />
                <p v-if="form.errors.tagline" class="mt-1.5 text-xs text-red-500">{{ form.errors.tagline }}</p>
              </div>

              <div class="sm:col-span-2">
                <label for="footer_description" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Footer description</label>
                <textarea
                  id="footer_description"
                  v-model="form.footer_description"
                  rows="3"
                  maxlength="200"
                  class="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                ></textarea>
                <p v-if="form.errors.footer_description" class="mt-1.5 text-xs text-red-500">{{ form.errors.footer_description }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.3)] dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A] dark:shadow-none sm:p-6">
            <div class="mb-5">
              <h2 class="text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Logo</h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-[#A0A0A0]">Upload a PNG, JPG, or WebP image. Square logos work best.</p>
            </div>

            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
              <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-[#FAF7F2] dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A]">
                <img v-if="displayLogo" :src="displayLogo" alt="Store logo" class="h-full w-full object-contain p-2" />
                <svg v-else class="h-10 w-10 text-gray-300 dark:text-[#3A3A3A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="handleLogo" />
                  <button
                    type="button"
                    class="cursor-pointer rounded-xl bg-[#1A1A1A] px-4 py-2 text-sm font-medium text-white shadow-[0_10px_25px_-8px_rgba(0,0,0,0.45)] transition hover:bg-[#333]  dark:bg-[#F5F5F5] dark:text-[#1A1A1A] dark:shadow-none dark:hover:bg-white"
                    @click="logoInput?.click()"
                  >
                    Choose image
                  </button>
                  <button
                    v-if="displayLogo"
                    type="button"
                    class="cursor-pointer rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 shadow-[0_8px_20px_-8px_rgba(220,38,38,0.35)] transition hover:bg-red-50  dark:border-red-900/40 dark:text-red-400 dark:shadow-none dark:hover:bg-red-900/20"
                    @click="removeLogo"
                  >
                    Remove
                  </button>
                </div>
                <p class="mt-2 text-xs text-gray-400 dark:text-[#A0A0A0]">Recommended: square image, at least 256×256px, max 2MB.</p>
                <p v-if="form.errors.logo" class="mt-1.5 text-xs text-red-500">{{ form.errors.logo }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.3)] dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A] dark:shadow-none sm:p-6">
            <div class="mb-5">
              <h2 class="text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Color theme</h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-[#A0A0A0]">Set the brand colors used by buttons, accents, and highlights.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <div>
                <label for="primary_color" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Primary color</label>
                <div class="flex items-center gap-2">
                  <input
                    id="primary_color"
                    v-model="form.primary_color"
                    type="color"
                    class="h-11 w-12 shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white p-1 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A]"
                  />
                  <input
                    v-model="form.primary_color"
                    type="text"
                    maxlength="7"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 font-mono text-sm uppercase text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                  />
                </div>
                <p v-if="form.errors.primary_color" class="mt-1.5 text-xs text-red-500">{{ form.errors.primary_color }}</p>
              </div>

              <div>
                <label for="accent_color" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Accent color</label>
                <div class="flex items-center gap-2">
                  <input
                    id="accent_color"
                    v-model="form.accent_color"
                    type="color"
                    class="h-11 w-12 shrink-0 cursor-pointer rounded-lg border border-gray-200 bg-white p-1 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A]"
                  />
                  <input
                    v-model="form.accent_color"
                    type="text"
                    maxlength="7"
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 font-mono text-sm uppercase text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                  />
                </div>
                <p v-if="form.errors.accent_color" class="mt-1.5 text-xs text-red-500">{{ form.errors.accent_color }}</p>
              </div>
            </div>

            <div class="mt-5">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Presets</p>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="preset in presets"
                  :key="preset.name"
                  type="button"
                  class="group flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2 text-xs font-medium shadow-[0_4px_12px_-4px_rgba(0,0,0,0.2)] transition  dark:shadow-none"
                  :class="form.primary_color.toLowerCase() === preset.primary.toLowerCase()
                    ? 'border-[#D4AF37] bg-[#D4AF37]/10 text-[#1A1A1A] dark:text-[#F5F5F5]'
                    : 'border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-[#D4AF37]/20 dark:text-[#A0A0A0] dark:hover:bg-[#21262d]'"
                  @click="applyPreset(preset)"
                >
                  <span class="h-4 w-4 rounded-full" :style="{ background: `linear-gradient(135deg, ${preset.primary}, ${preset.accent})` }"></span>
                  {{ preset.name }}
                </button>
              </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200 dark:border-[#D4AF37]/20">
              <div class="flex flex-col gap-3 bg-[#FAF7F2] p-5 dark:bg-[#0A0A0A] sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl" :style="{ background: `linear-gradient(135deg, ${form.primary_color}, ${form.accent_color})` }">
                    <img v-if="displayLogo" :src="displayLogo" alt="" class="h-full w-full bg-white object-contain p-0.5" />
                    <svg v-else class="h-5 w-5" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                      <path d="M6 3h12l4 6-10 13L2 9z" />
                      <path d="M2 9h20" />
                      <path d="M12 22L6 9" />
                      <path d="M12 22l6-13" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ form.site_name || "Your store" }}</p>
                    <p class="text-[10px] font-medium uppercase tracking-widest text-gray-400 dark:text-[#A0A0A0]">{{ form.tagline || "Your tagline" }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <span class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white" :style="{ backgroundColor: form.primary_color }">Primary</span>
                  <span class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white" :style="{ backgroundColor: form.accent_color }">Accent</span>
                </div>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.3)] dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A] dark:shadow-none sm:p-6">
            <div class="mb-5">
              <h2 class="text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Contact details</h2>
              <p class="mt-1 text-sm text-gray-500 dark:text-[#A0A0A0]">Shown in the storefront footer.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
              <div>
                <label for="contact_email" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Contact email</label>
                <input
                  id="contact_email"
                  v-model="form.contact_email"
                  type="email"
                  maxlength="120"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                />
                <p v-if="form.errors.contact_email" class="mt-1.5 text-xs text-red-500">{{ form.errors.contact_email }}</p>
              </div>

              <div>
                <label for="contact_hours" class="mb-1.5 block text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Contact hours</label>
                <input
                  id="contact_hours"
                  v-model="form.contact_hours"
                  type="text"
                  maxlength="120"
                  class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-[#1A1A1A] outline-none transition focus:border-[#D4AF37] focus:ring-2 focus:ring-[#D4AF37]/20 dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] dark:text-[#F5F5F5]"
                />
                <p v-if="form.errors.contact_hours" class="mt-1.5 text-xs text-red-500">{{ form.errors.contact_hours }}</p>
              </div>
            </div>
          </section>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <p v-if="form.hasErrors" class="text-sm text-red-500 sm:mr-auto">Please check the fields above.</p>
            <button
              type="submit"
              :disabled="form.processing"
              class="cursor-pointer rounded-xl bg-[#1A1A1A] px-6 py-3 text-sm font-semibold text-white shadow-[0_12px_30px_-8px_rgba(0,0,0,0.5)] transition hover:bg-[#333]  disabled:cursor-not-allowed disabled:opacity-50 dark:bg-[#F5F5F5] dark:text-[#1A1A1A] dark:shadow-none dark:hover:bg-white"
            >
              {{ form.processing ? "Saving..." : "Save settings" }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
