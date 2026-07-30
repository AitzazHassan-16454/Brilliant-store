<script setup>
import { ref, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import { useAuthModal } from "../composables/useAuthModal.js"

const { isOpen, defaultTab, close } = useAuthModal()

const tab = ref("login")

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
    loginForm.reset()
    registerForm.reset()
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

function submitLogin() {
  loginForm.post("/login", {
    preserveScroll: true,
    onSuccess: () => {
      close()
    },
  })
}

function submitRegister() {
  registerForm.post("/register", {
    preserveScroll: true,
    onSuccess: () => {
      close()
    },
  })
}

function switchTab(newTab) {
  loginForm.clearErrors()
  registerForm.clearErrors()
  tab.value = newTab
}
</script>

<template>
  <Teleport to="body">
    <Transition name="auth-modal">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center px-4 py-8"
        style="background: rgba(10,10,10,0.5); backdrop-filter: blur(8px);"
        @click.self="close"
      >
        <div
          class="w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden max-h-[85vh] flex flex-col bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/15"
        >
          <!-- HEADER -->
          <div class="relative px-6 pt-6 pb-4">
            <button
              @click="close"
              class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90 cursor-pointer bg-black/5 dark:bg-white/10"
            >
              <svg class="w-4 h-4 text-[#6B6B6B] dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M18 6 6 18M6 6l12 12"/>
              </svg>
            </button>

            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4 bg-[#D4AF37]">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
            </div>

            <h1 class="text-xl font-extrabold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
              {{ tab === "login" ? "Welcome back" : "Create account" }}
            </h1>
            <p class="text-sm mt-1 text-[#6B6B6B] dark:text-[#A0A0A0]">
              {{ tab === "login" ? "Sign in to your account" : "Join us and start shopping" }}
            </p>
          </div>

          <!-- TABS -->
          <div class="px-6">
            <div class="flex rounded-xl p-1 bg-black/5 dark:bg-white/10">
              <button
                @click="switchTab('login')"
                class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer"
                :class="tab === 'login'
                  ? 'bg-[#D4AF37] text-white shadow-[0_2px_8px_rgba(212,175,55,0.25)]'
                  : 'text-[#6B6B6B] dark:text-[#A0A0A0] bg-transparent'"
              >
                Sign In
              </button>
              <button
                @click="switchTab('register')"
                class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer"
                :class="tab === 'register'
                  ? 'bg-[#D4AF37] text-white shadow-[0_2px_8px_rgba(212,175,55,0.25)]'
                  : 'text-[#6B6B6B] dark:text-[#A0A0A0] bg-transparent'"
              >
                Sign Up
              </button>
            </div>
          </div>

          <!-- FORMS -->
          <div class="px-6 py-4 overflow-y-auto flex-1 min-h-0">

            <!-- LOGIN FORM -->
            <form v-if="tab === 'login'" @submit.prevent="submitLogin" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Email</label>
                <input
                  v-model="loginForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="you@example.com"
                />
                <p v-if="loginForm.errors.email" class="text-xs mt-1.5 font-medium text-red-600">
                  {{ loginForm.errors.email }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Password</label>
                <input
                  v-model="loginForm.password"
                  type="password"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="Enter your password"
                />
                <p v-if="loginForm.errors.password" class="text-xs mt-1.5 font-medium text-red-600">
                  {{ loginForm.errors.password }}
                </p>
              </div>

              <button
                type="submit"
                :disabled="loginForm.processing"
                class="w-full text-sm font-bold py-3 rounded-xl transition-all duration-200 disabled:opacity-50 active:scale-[0.98] cursor-pointer border-none bg-[#D4AF37] text-white shadow-[0_4px_16px_rgba(212,175,55,0.3)]"
              >
                {{ loginForm.processing ? 'Signing in...' : 'Sign In' }}
              </button>
            </form>

            <!-- REGISTER FORM -->
            <form v-else @submit.prevent="submitRegister" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Name</label>
                <input
                  v-model="registerForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="John Doe"
                />
                <p v-if="registerForm.errors.name" class="text-xs mt-1.5 font-medium text-red-600">
                  {{ registerForm.errors.name }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Email</label>
                <input
                  v-model="registerForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="you@example.com"
                />
                <p v-if="registerForm.errors.email" class="text-xs mt-1.5 font-medium text-red-600">
                  {{ registerForm.errors.email }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Password</label>
                <input
                  v-model="registerForm.password"
                  type="password"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="Min. 6 characters"
                />
                <p v-if="registerForm.errors.password" class="text-xs mt-1.5 font-medium text-red-600">
                  {{ registerForm.errors.password }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1.5 text-[#1A1A1A] dark:text-[#F5F5F5]">Confirm Password</label>
                <input
                  v-model="registerForm.password_confirmation"
                  type="password"
                  required
                  class="w-full px-3 py-2.5 rounded-xl text-sm outline-none transition-all border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] text-[#1A1A1A] dark:text-[#F5F5F5] placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-[#D4AF37] focus:ring-[3px] focus:ring-[#D4AF37]/10"
                  placeholder="Repeat your password"
                />
              </div>

              <button
                type="submit"
                :disabled="registerForm.processing"
                class="w-full text-sm font-bold py-3 rounded-xl transition-all duration-200 disabled:opacity-50 active:scale-[0.98] cursor-pointer border-none bg-[#D4AF37] text-white shadow-[0_4px_16px_rgba(212,175,55,0.3)]"
              >
                {{ registerForm.processing ? 'Creating account...' : 'Create Account' }}
              </button>
            </form>

          </div>

          <!-- FOOTER -->
          <div class="px-6 pb-6 text-center">
            <p class="text-xs text-[#9B9B9B] dark:text-[#A0A0A0]">
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

@keyframes auth-modal-in {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.animate-auth-modal-in {
  animation: auth-modal-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
