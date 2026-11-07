<script setup>
import { ref } from "vue"
import { Head, Link, useForm } from "@inertiajs/vue3"
import { useAuthModal } from "../../composables/useAuthModal.js"

const props = defineProps({
  status: {
    type: String,
    default: null,
  },
})

const { openLogin } = useAuthModal()
const isSubmitted = ref(false)

const form = useForm({
  email: "",
})

function submit() {
  form.post("/forgot-password", {
    onSuccess: () => {
      isSubmitted.value = true
    },
  })
}
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <Head title="Forgot Password" />

    <div class="w-full max-w-md">
      <!-- CARD -->
      <div class="rounded-3xl p-8 sm:p-10 border border-gray-200 dark:border-[#D4AF37]/20 bg-white/70 dark:bg-[#1A1A1A]/70 backdrop-blur-xl shadow-xl shadow-black/5 dark:shadow-[#D4AF37]/5">

        <!-- BRAND ICON -->
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-6 bg-gradient-to-br from-[#D4AF37] to-[#B8960F] shadow-md shadow-[#D4AF37]/30">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
          Reset your password
        </h1>
        <p class="text-sm mt-2 text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
          Enter the email address associated with your account and we'll send you a link to reset your password.
        </p>

        <!-- SUCCESS STATE -->
        <div v-if="status || isSubmitted" class="mt-6 p-4 rounded-2xl bg-[#D4AF37]/10 border border-[#D4AF37]/30">
          <div class="flex items-start gap-3">
            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 bg-[#D4AF37] text-white text-xs font-bold mt-0.5">
              ✓
            </div>
            <div class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">
              {{ status || "A password reset link has been sent to your email address." }}
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-[#D4AF37]/20 flex justify-end">
            <Link
              href="/"
              class="text-xs font-bold text-[#D4AF37] hover:underline"
            >
              Return to store &rarr;
            </Link>
          </div>
        </div>

        <!-- FORM -->
        <form v-else @submit.prevent="submit" class="mt-6 space-y-4">
          <div class="pro-form-group">
            <label class="pro-label">
              <span>Email Address</span>
            </label>
            <div class="pro-input-wrap">
              <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              <input
                v-model="form.email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                class="pro-input pro-input-with-icon"
                :class="{ 'pro-input-error': form.errors.email }"
              />
            </div>
            <div v-if="form.errors.email" class="pro-form-error">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
              </svg>
              <span>{{ form.errors.email }}</span>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="pro-btn-primary"
            >
              <svg v-if="form.processing" class="w-4 h-4 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              <span>{{ form.processing ? "Sending link..." : "Email Password Reset Link" }}</span>
            </button>
          </div>
        </form>

        <!-- BACK TO LOGIN -->
        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/5 text-center">
          <p class="text-xs text-[#6B6B6B] dark:text-[#A0A0A0]">
            Remember your password?
            <button
              type="button"
              @click="openLogin"
              class="font-semibold text-[#D4AF37] hover:underline cursor-pointer bg-transparent border-none p-0 ml-1"
            >
              Sign In
            </button>
          </p>
        </div>

      </div>
    </div>
  </div>
</template>
