<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Sidebar from '../components/Sidebar.vue';

defineOptions({ layout: false })

const props = defineProps({
  roles: {
    type: Object,
    default: () => ({}),
  },
});

const defaultRole = Object.keys(props.roles)[0] ?? '';
const hasRoles = Object.keys(props.roles).length > 0;

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: defaultRole,
});

const submit = () => {
  if (!hasRoles) {
    return;
  }

  form.post('/users');
};
</script>

<template>
  <Head title="Create User" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
    <Sidebar />

    <main class="flex-1 p-6 lg:p-8 overflow-x-auto">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Create User</h1>
          <p class="text-sm text-gray-500 dark:text-[#A0A0A0]">Only admins can add users with User or Saleman roles.</p>
        </div>

        <Link
          href="/users"
          class="inline-flex items-center justify-center rounded-xl bg-[#FAF7F2] dark:bg-[#1A1A1A] px-4 py-2 text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] hover:bg-[#D4AF37]/10 dark:hover:bg-[#30363d]"
        >
          Back to users
        </Link>
      </div>

      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-sm overflow-hidden p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-2 block w-full rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm focus:border-gray-400 dark:focus:border-gray-500 outline-none"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-2 block w-full rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm focus:border-gray-400 dark:focus:border-gray-500 outline-none"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Role</label>
            <select
              v-model="form.role"
              :disabled="!hasRoles"
              class="mt-2 block w-full rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm focus:border-gray-400 dark:focus:border-gray-500 outline-none disabled:cursor-not-allowed disabled:bg-[#FAF7F2] dark:disabled:bg-[#21262d]"
            >
              <option v-if="!hasRoles" disabled value="">No roles available</option>
              <option v-else v-for="(label, key) in props.roles" :key="key" :value="key">{{ label }}</option>
            </select>
            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
            <p v-if="!hasRoles" class="mt-1 text-sm text-yellow-600">
              No roles found in the database. Add roles first before creating a user.
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="mt-2 block w-full rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm focus:border-gray-400 dark:focus:border-gray-500 outline-none"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-[#F5F5F5]">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="mt-2 block w-full rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] px-4 py-3 text-sm text-[#1A1A1A] dark:text-[#F5F5F5] shadow-sm focus:border-gray-400 dark:focus:border-gray-500 outline-none"
            />
          </div>

          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-[#D4AF37] hover:bg-[#B8960F] px-5 py-3 text-sm font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            :disabled="form.processing || !hasRoles"
          >
            Create User
          </button>
        </form>
      </div>
    </main>
  </div>
</template>
