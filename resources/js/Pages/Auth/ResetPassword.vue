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

          <!-- NEW PASSWORD -->
          <div class="pro-form-group">
            <label class="pro-label">
              <span>New Password</span>
            </label>
            <div class="pro-input-wrap">
              <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
              </svg>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                autofocus
                autocomplete="new-password"
                placeholder="Min. 6 characters"
                class="pro-input pro-input-with-icon pro-input-with-action"
                :class="{ 'pro-input-error': form.errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="pro-input-action"
                :title="showPassword ? 'Hide password' : 'Show password'"
              >
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
            <div v-if="form.errors.password" class="pro-form-error">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
              </svg>
              <span>{{ form.errors.password }}</span>
            </div>
          </div>

          <!-- CONFIRM PASSWORD -->
          <div class="pro-form-group">
            <label class="pro-label">
              <span>Confirm New Password</span>
            </label>
            <div class="pro-input-wrap">
              <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
              </svg>
              <input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="new-password"
                placeholder="Repeat your new password"
                class="pro-input pro-input-with-icon"
              />
            </div>
          </div>

          <!-- SUBMIT -->
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
              <span>{{ form.processing ? "Resetting password..." : "Reset Password" }}</span>
            </button>
          </div>
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
