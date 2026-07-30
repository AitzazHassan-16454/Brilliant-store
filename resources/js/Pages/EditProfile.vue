<script setup>
import { ref } from "vue"
import { useForm, usePage, Link } from "@inertiajs/vue3"
import { useNotification } from "../composables/useNotification.js"

const page = usePage()
const { error } = useNotification()
const user = page.props.auth.user

const avatarPreview = ref(user.avatar || null)

const form = useForm({
  name: user.name,
  email: user.email,
  password: "",
  password_confirmation: "",
  avatar: null,
})

const handleAvatar = (e) => {
  const file = e.target.files[0]
  form.avatar = file

  if (file) {
    const reader = new FileReader()
    reader.onload = (ev) => {
      avatarPreview.value = ev.target.result
    }
    reader.readAsDataURL(file)
  }
}

const submit = () => {
  form.post("/profile/update", {
    forceFormData: true,
    onSuccess: () => form.reset("password", "password_confirmation"),
    onError: () => {
      error("Password must be at least 6 characters and match the confirmation.")
    },
  })
}
</script>

<template>
<Head title="Edit Profile" />

<div class="min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5]" style="font-family:'Inter',system-ui,sans-serif;">

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/Profile" class="inline-flex items-center gap-1.5 text-sm font-medium mb-5 no-underline text-[#6B6B6B] dark:text-[#A0A0A0] transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to Profile
      </Link>
      <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
        Edit Profile
      </h1>
      <p class="text-sm mt-2 text-[#6B6B6B] dark:text-[#A0A0A0]">
        Update your account information and settings
      </p>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl p-6 sm:p-8 border border-white/25 dark:border-[#D4AF37]/20 bg-white/45 dark:bg-[#1A1A1A]/60 backdrop-blur-lg shadow-sm">

      <form @submit.prevent="submit" class="space-y-6">

        <!-- AVATAR -->
        <div class="flex items-center gap-5">

          <div class="w-20 h-20 rounded-2xl overflow-hidden flex items-center justify-center shrink-0 border border-black/6 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A]/60 backdrop-blur-lg">
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              class="w-full h-full object-cover"
            />
            <svg v-else class="w-8 h-8 text-[#9B9B9B] dark:text-[#A0A0A0]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
          </div>

          <div>
            <label class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
              Profile Image
            </label>

            <input
              type="file"
              class="block mt-2 text-sm text-[#6B6B6B] dark:text-[#A0A0A0] file:text-[#1A1A1A] dark:file:text-slate-400"
              @change="handleAvatar"
            />

            <p class="text-xs mt-1 text-[#9B9B9B] dark:text-[#A0A0A0]">
              JPG, PNG up to 2MB
            </p>

            <p v-if="form.errors.avatar" class="text-xs text-red-500 mt-1">
              {{ form.errors.avatar }}
            </p>
          </div>

        </div>

        <!-- NAME -->
        <div>
          <label class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
            Full Name
          </label>

          <input
            v-model="form.name"
            type="text"
            class="w-full mt-2 px-4 py-3 rounded-xl outline-none transition-all border border-black/8 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A] backdrop-blur-lg text-[#1A1A1A] dark:text-[#F5F5F5] focus:border-[#D4AF37]/40 focus:ring-[3px] focus:ring-[#D4AF37]/10"
          />

          <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- EMAIL -->
        <div>
          <label class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
            Email Address
          </label>

          <input
            v-model="form.email"
            type="email"
            class="w-full mt-2 px-4 py-3 rounded-xl outline-none transition-all border border-black/8 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A] backdrop-blur-lg text-[#1A1A1A] dark:text-[#F5F5F5] focus:border-[#D4AF37]/40 focus:ring-[3px] focus:ring-[#D4AF37]/10"
          />

          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <!-- PASSWORD -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

          <div>
            <label class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
              New Password
            </label>

            <input
              v-model="form.password"
              type="password"
              class="w-full mt-2 px-4 py-3 rounded-xl outline-none transition-all border border-black/8 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A] backdrop-blur-lg text-[#1A1A1A] dark:text-[#F5F5F5] focus:border-[#D4AF37]/40 focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
          </div>

          <div>
            <label class="text-sm font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
              Confirm Password
            </label>

            <input
              v-model="form.password_confirmation"
              type="password"
              class="w-full mt-2 px-4 py-3 rounded-xl outline-none transition-all border border-black/8 dark:border-[#D4AF37]/20 bg-white/60 dark:bg-[#1A1A1A] backdrop-blur-lg text-[#1A1A1A] dark:text-[#F5F5F5] focus:border-[#D4AF37]/40 focus:ring-[3px] focus:ring-[#D4AF37]/10"
            />
          </div>

        </div>

        <!-- ACTIONS -->
        <div class="flex items-center justify-between pt-4">

          <Link
            href="/Profile"
            class="text-sm font-medium no-underline text-[#6B6B6B] dark:text-[#A0A0A0] transition-colors"
          >
            Cancel
          </Link>

          <button
            type="submit"
            class="px-8 py-3 rounded-xl font-semibold text-white transition-all duration-300 hover:-translate-y-0.5 cursor-pointer"
            style="background:linear-gradient(135deg,#D4AF37,#B8960F,#D4AF37); box-shadow:0 4px 16px rgba(212,175,55,0.3),0 1px 3px rgba(0,0,0,0.1); border:none;"
          >
            Save Changes
          </button>

        </div>

      </form>

    </div>

  </div>

</div>
</template>
