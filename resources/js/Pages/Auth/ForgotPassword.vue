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
          <div>
            <label class="block text-sm font-semibold mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">
              Email address
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              autofocus
              placeholder="you@example.com"
              class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
            <p v-if="form.errors.email" class="text-xs mt-1.5 font-medium text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 disabled:opacity-50 active:scale-[0.98] cursor-pointer border-none bg-[#D4AF37] text-white shadow-lg shadow-[#D4AF37]/30 hover:brightness-105"
          >
            {{ form.processing ? "Sending link..." : "Email Password Reset Link" }}
          </button>
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
