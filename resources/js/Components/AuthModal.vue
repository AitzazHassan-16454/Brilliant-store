<script setup>
import { ref, computed, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import { useAuthModal } from "../composables/useAuthModal.js"
import { useNotification } from "../composables/useNotification.js"

const { isOpen, defaultTab, close } = useAuthModal()
const { success } = useNotification()

const tab = ref("login")
const forgotSent = ref(false)

// Password visibility toggles
const showLoginPassword = ref(false)
const showRegisterPassword = ref(false)
const showRegisterConfirm = ref(false)

watch(defaultTab, (val) => {
  tab.value = val
})

watch(isOpen, (val) => {
  if (val) {
    document.body.style.overflow = "hidden"
  } else {
    document.body.style.overflow = ""
    loginForm.clearErrors()
    registerForm.clearErrors()
    forgotForm.clearErrors()
    loginForm.reset()
    registerForm.reset()
    forgotForm.reset()
    forgotSent.value = false
    showLoginPassword.value = false
    showRegisterPassword.value = false
    showRegisterConfirm.value = false
  }
})

const loginForm = useForm({
  email: "",
  password: "",
})

const registerForm = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
})

const forgotForm = useForm({
  email: "",
})

// Real-time password strength calculation
const passwordStrength = computed(() => {
  const pwd = registerForm.password || ""
  if (!pwd) return { score: 0, label: "", color: "" }

  let score = 0
  if (pwd.length >= 6) score += 1
  if (pwd.length >= 8) score += 1
  if (/[0-9]/.test(pwd)) score += 1
  if (/[A-Z]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd)) score += 1

  if (score <= 1) return { score: 1, label: "Weak", color: "bg-red-500", text: "text-red-500" }
  if (score === 2) return { score: 2, label: "Fair", color: "bg-amber-500", text: "text-amber-500" }
  if (score === 3) return { score: 3, label: "Good", color: "bg-[#D4AF37]", text: "text-[#D4AF37]" }
  return { score: 4, label: "Strong", color: "bg-emerald-500", text: "text-emerald-500" }
})

function submitLogin() {
  loginForm.post("/login", {
    preserveScroll: true,
    onSuccess: () => {
      close()
      success({ title: "Welcome back", message: "Signed in successfully." })
    },
  })
}

function submitRegister() {
  registerForm.post("/register", {
    preserveScroll: true,
    onSuccess: () => {
      close()
      success({ title: "Account created", message: "Welcome to RH Store!" })
    },
  })
}

function submitForgot() {
  forgotForm.post("/forgot-password", {
    preserveScroll: true,
    onSuccess: () => {
      forgotSent.value = true
      success({ title: "Email sent", message: "Check your inbox for reset instructions." })
    },
  })
}

function switchTab(newTab) {
  loginForm.clearErrors()
  registerForm.clearErrors()
  forgotForm.clearErrors()
  forgotSent.value = false
  tab.value = newTab
}
</script>

<template>
  <Teleport to="body">
    <Transition name="auth-modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-8"
        style="background: rgba(10,10,10,0.6); backdrop-filter: blur(10px);"
        @click.self="close"
      >
        <div
          class="w-full max-w-md rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col bg-white dark:bg-[#141414] border border-black/10 dark:border-[#D4AF37]/20 transition-all duration-300"
        >
          <!-- HEADER -->
          <div class="relative px-6 sm:px-8 pt-7 pb-4">
            <button
              @click="close"
              class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center transition-all hover:bg-muted text-muted-foreground hover:text-foreground cursor-pointer bg-black/5 dark:bg-white/10"
              aria-label="Close modal"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/>
              </svg>
            </button>

            <!-- Brand Icon -->
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 bg-gradient-to-br from-[#E0BF48] to-[#B8941E] text-[#0A0A0A] shadow-md shadow-[#D4AF37]/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
              </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-foreground">
              {{ tab === "login" ? "Welcome Back" : tab === "register" ? "Create an Account" : "Reset Password" }}
            </h1>
            <p class="text-xs sm:text-sm mt-1 text-muted-foreground leading-relaxed">
              {{ tab === "login" ? "Sign in to access your orders, saved items, and bag." : tab === "register" ? "Join our luxury shopping collective in seconds." : "Enter your email and we'll send a secure password reset link." }}
            </p>
          </div>

          <!-- TABS -->
          <div class="px-6 sm:px-8">
            <div v-if="tab === 'forgot'">
              <button
                type="button"
                @click="switchTab('login')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-[#D4AF37] cursor-pointer bg-transparent border-none p-0 mb-1 transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Back to Sign In
              </button>
            </div>
            <div v-else class="flex rounded-xl p-1 bg-muted/60 border border-border">
              <button
                type="button"
                @click="switchTab('login')"
                class="flex-1 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                :class="tab === 'login'
                  ? 'bg-card text-foreground shadow-xs'
                  : 'text-muted-foreground hover:text-foreground bg-transparent'"
              >
                Sign In
              </button>
              <button
                type="button"
                @click="switchTab('register')"
                class="flex-1 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                :class="tab === 'register'
                  ? 'bg-card text-foreground shadow-xs'
                  : 'text-muted-foreground hover:text-foreground bg-transparent'"
              >
                Sign Up
              </button>
            </div>
          </div>

          <!-- FORMS -->
          <div class="px-6 sm:px-8 py-5 overflow-y-auto flex-1 min-h-0">

            <!-- LOGIN FORM -->
            <form v-if="tab === 'login'" @submit.prevent="submitLogin" class="space-y-4">
              <!-- Email -->
              <div class="pro-form-group">
                <label class="pro-label">
                  <span>Email Address</span>
                </label>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                  </svg>
                  <input
                    v-model="loginForm.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="pro-input pro-input-with-icon"
                    :class="{ 'pro-input-error': loginForm.errors.email }"
                    placeholder="you@example.com"
                  />
                </div>
                <div v-if="loginForm.errors.email" class="pro-form-error">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                  </svg>
                  <span>{{ loginForm.errors.email }}</span>
                </div>
              </div>

              <!-- Password -->
              <div class="pro-form-group">
                <div class="flex items-center justify-between">
                  <label class="pro-label">
                    <span>Password</span>
                  </label>
                  <button
                    type="button"
                    @click="switchTab('forgot')"
                    class="text-xs font-semibold text-[#D4AF37] hover:underline cursor-pointer bg-transparent border-none p-0"
                  >
                    Forgot password?
                  </button>
                </div>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                  </svg>
                  <input
                    v-model="loginForm.password"
                    :type="showLoginPassword ? 'text' : 'password'"
                    required
                    autocomplete="current-password"
                    class="pro-input pro-input-with-icon pro-input-with-action"
                    :class="{ 'pro-input-error': loginForm.errors.password }"
                    placeholder="Enter your password"
                  />
                  <button
                    type="button"
                    @click="showLoginPassword = !showLoginPassword"
                    class="pro-input-action"
                    :title="showLoginPassword ? 'Hide password' : 'Show password'"
                  >
                    <!-- Eye open -->
                    <svg v-if="!showLoginPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <!-- Eye closed -->
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  </button>
                </div>
                <div v-if="loginForm.errors.password" class="pro-form-error">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                  </svg>
                  <span>{{ loginForm.errors.password }}</span>
                </div>
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="loginForm.processing"
                  class="pro-btn-primary"
                >
                  <svg v-if="loginForm.processing" class="w-4 h-4 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span>{{ loginForm.processing ? 'Signing in...' : 'Sign In' }}</span>
                </button>
              </div>
            </form>

            <!-- REGISTER FORM -->
            <form v-else-if="tab === 'register'" @submit.prevent="submitRegister" class="space-y-4">
              <!-- Name -->
              <div class="pro-form-group">
                <label class="pro-label">
                  <span>Full Name</span>
                </label>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                  </svg>
                  <input
                    v-model="registerForm.name"
                    type="text"
                    required
                    autocomplete="name"
                    class="pro-input pro-input-with-icon"
                    :class="{ 'pro-input-error': registerForm.errors.name }"
                    placeholder="John Doe"
                  />
                </div>
                <div v-if="registerForm.errors.name" class="pro-form-error">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                  </svg>
                  <span>{{ registerForm.errors.name }}</span>
                </div>
              </div>

              <!-- Email -->
              <div class="pro-form-group">
                <label class="pro-label">
                  <span>Email Address</span>
                </label>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                  </svg>
                  <input
                    v-model="registerForm.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="pro-input pro-input-with-icon"
                    :class="{ 'pro-input-error': registerForm.errors.email }"
                    placeholder="you@example.com"
                  />
                </div>
                <div v-if="registerForm.errors.email" class="pro-form-error">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                  </svg>
                  <span>{{ registerForm.errors.email }}</span>
                </div>
              </div>

              <!-- Password + Strength Meter -->
              <div class="pro-form-group">
                <div class="flex items-center justify-between">
                  <label class="pro-label">
                    <span>Password</span>
                  </label>
                  <span v-if="passwordStrength.score > 0" class="text-[11px] font-bold" :class="passwordStrength.text">
                    {{ passwordStrength.label }}
                  </span>
                </div>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                  </svg>
                  <input
                    v-model="registerForm.password"
                    :type="showRegisterPassword ? 'text' : 'password'"
                    required
                    autocomplete="new-password"
                    class="pro-input pro-input-with-icon pro-input-with-action"
                    :class="{ 'pro-input-error': registerForm.errors.password }"
                    placeholder="Min. 8 characters"
                  />
                  <button
                    type="button"
                    @click="showRegisterPassword = !showRegisterPassword"
                    class="pro-input-action"
                    :title="showRegisterPassword ? 'Hide password' : 'Show password'"
                  >
                    <svg v-if="!showRegisterPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  </button>
                </div>
                <!-- Strength Bar -->
                <div v-if="registerForm.password" class="flex gap-1.5 pt-1">
                  <div
                    v-for="idx in 4"
                    :key="`strength-${idx}`"
                    class="h-1 flex-1 rounded-full transition-all duration-300"
                    :class="idx <= passwordStrength.score ? passwordStrength.color : 'bg-muted'"
                  />
                </div>
                <div v-if="registerForm.errors.password" class="pro-form-error">
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                  </svg>
                  <span>{{ registerForm.errors.password }}</span>
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="pro-form-group">
                <label class="pro-label">
                  <span>Confirm Password</span>
                </label>
                <div class="pro-input-wrap">
                  <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                  </svg>
                  <input
                    v-model="registerForm.password_confirmation"
                    :type="showRegisterConfirm ? 'text' : 'password'"
                    required
                    autocomplete="new-password"
                    class="pro-input pro-input-with-icon pro-input-with-action"
                    placeholder="Repeat your password"
                  />
                  <button
                    type="button"
                    @click="showRegisterConfirm = !showRegisterConfirm"
                    class="pro-input-action"
                    :title="showRegisterConfirm ? 'Hide password' : 'Show password'"
                  >
                    <svg v-if="!showRegisterConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="registerForm.processing"
                  class="pro-btn-primary"
                >
                  <svg v-if="registerForm.processing" class="w-4 h-4 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span>{{ registerForm.processing ? 'Creating account...' : 'Create Account' }}</span>
                </button>
              </div>
            </form>

            <!-- FORGOT PASSWORD FORM -->
            <div v-else-if="tab === 'forgot'">
              <div v-if="forgotSent" class="space-y-4 text-center py-4">
                <div class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center bg-[#D4AF37]/15 text-[#D4AF37] ring-1 ring-[#D4AF37]/30">
                  <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-base font-bold text-foreground">Check your inbox</h2>
                  <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                    We've sent a password reset link to <strong class="text-foreground">{{ forgotForm.email }}</strong>. Please check your inbox and click the link to reset your password.
                  </p>
                </div>
                <button
                  type="button"
                  @click="switchTab('login')"
                  class="pro-btn-primary"
                >
                  Return to Sign In
                </button>
              </div>

              <form v-else @submit.prevent="submitForgot" class="space-y-4">
                <div class="pro-form-group">
                  <label class="pro-label">
                    <span>Email Address</span>
                  </label>
                  <div class="pro-input-wrap">
                    <svg class="pro-input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <input
                      v-model="forgotForm.email"
                      type="email"
                      required
                      autocomplete="email"
                      class="pro-input pro-input-with-icon"
                      :class="{ 'pro-input-error': forgotForm.errors.email }"
                      placeholder="you@example.com"
                    />
                  </div>
                  <div v-if="forgotForm.errors.email" class="pro-form-error">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 6.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ forgotForm.errors.email }}</span>
                  </div>
                </div>

                <div class="pt-2">
                  <button
                    type="submit"
                    :disabled="forgotForm.processing"
                    class="pro-btn-primary"
                  >
                    <svg v-if="forgotForm.processing" class="w-4 h-4 animate-spin text-[#0A0A0A]" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>{{ forgotForm.processing ? 'Sending link...' : 'Send Reset Link' }}</span>
                  </button>
                </div>
              </form>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="px-6 sm:px-8 pb-6 text-center border-t border-border/50 pt-4">
            <p class="text-xs text-muted-foreground">
              By continuing, you agree to our Terms of Service and Privacy Policy.
            </p>
          </div>

        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.auth-modal-enter-active,
.auth-modal-leave-active {
  transition: opacity 0.25s ease;
}
.auth-modal-enter-from,
.auth-modal-leave-to {
  opacity: 0;
}
</style>
