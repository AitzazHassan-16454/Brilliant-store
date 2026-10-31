<script setup>
import { ref } from "vue"
import { Head, Link, useForm } from "@inertiajs/vue3"

const props = defineProps({
  token: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    default: "",
  },
})

const showPassword = ref(false)

const form = useForm({
  token: props.token,
  email: props.email,
  password: "",
  password_confirmation: "",
})

function submit() {
  form.post("/reset-password", {
    onFinish: () => form.reset("password", "password_confirmation"),
  })
}
</script>

<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <Head title="Reset Password" />

    <div class="w-full max-w-md">
      <!-- CARD -->
      <div class="rounded-3xl p-8 sm:p-10 border border-gray-200 dark:border-[#D4AF37]/20 bg-white/70 dark:bg-[#1A1A1A]/70 backdrop-blur-xl shadow-xl shadow-black/5 dark:shadow-[#D4AF37]/5">

        <!-- BRAND ICON -->
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-6 bg-gradient-to-br from-[#D4AF37] to-[#B8960F] shadow-md shadow-[#D4AF37]/30">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
          </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
          Set a new password
        </h1>
        <p class="text-sm mt-2 text-[#6B6B6B] dark:text-[#A0A0A0] leading-relaxed">
          Please enter your email and choose a strong new password with at least 6 characters.
        </p>

        <!-- FORM -->
        <form @submit.prevent="submit" class="mt-6 space-y-4">
          <!-- EMAIL -->
          <div>
            <label class="block text-sm font-semibold mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">
              Email address
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="you@example.com"
              class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
            <p v-if="form.errors.email" class="text-xs mt-1.5 font-medium text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- NEW PASSWORD -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                New password
              </label>
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="text-xs font-medium text-[#6B6B6B] dark:text-[#A0A0A0] hover:text-[#D4AF37] cursor-pointer bg-transparent border-none p-0"
              >
                {{ showPassword ? "Hide" : "Show" }}
              </button>
            </div>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              autofocus
              placeholder="Min. 6 characters"
              class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
            <p v-if="form.errors.password" class="text-xs mt-1.5 font-medium text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- CONFIRM PASSWORD -->
          <div>
            <label class="block text-sm font-semibold mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">
              Confirm new password
            </label>
            <input
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Repeat your new password"
              class="w-full px-4 py-3 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
          </div>

          <!-- SUBMIT -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 px-4 rounded-xl text-sm font-bold transition-all duration-200 disabled:opacity-50 active:scale-[0.98] cursor-pointer border-none bg-[#D4AF37] text-white shadow-lg shadow-[#D4AF37]/30 hover:brightness-105 mt-2"
          >
            {{ form.processing ? "Resetting password..." : "Reset Password" }}
          </button>
        </form>

        <!-- BACK TO HOME -->
        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/5 text-center">
          <Link
            href="/"
            class="text-xs font-semibold text-[#6B6B6B] dark:text-[#A0A0A0] hover:text-[#D4AF37]"
          >
            &larr; Back to store
          </Link>
        </div>

      </div>
    </div>
  </div>
</template>
