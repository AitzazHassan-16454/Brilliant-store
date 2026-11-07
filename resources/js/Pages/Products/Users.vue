<script setup>
import Sidebar from '../components/Sidebar.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineOptions({ layout: false })

defineProps({
  users: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const can = (permission) => Boolean(page.props.permissions?.can?.[permission]);
</script>

<template>
  <Head title="Users" />

  <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">

    <!-- SIDEBAR -->
    <Sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
            All Users
          </h1>
          <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">
            Manage registered customers, sales staff, and administrator accounts
          </p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="can('users.create')"
            href="/users/create"
            class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-[#D4AF37] hover:bg-[#B8960F] px-4 sm:px-5 py-2.5 text-sm font-semibold text-white transition shadow-xs"
          >
            + Add New User
          </Link>
        </div>
      </div>

      <!-- TABLE CARD -->
      <div class="bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-[#D4AF37]/20 rounded-2xl shadow-xs overflow-hidden">

        <!-- TITLE -->
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
          <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
            Users List
          </h3>
          <span class="text-xs text-gray-400 dark:text-[#A0A0A0] lg:hidden">
            Scroll horizontally &rarr;
          </span>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

          <table class="w-full text-sm text-left min-w-[500px]">

            <!-- HEAD -->
            <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
              <tr>
                <th class="p-4">Id</th>
                <th class="p-4">Name</th>
                <th class="p-4">Email</th>
                <th class="p-4">Role</th>
              </tr>
            </thead>

            <!-- BODY -->
            <tbody>

              <tr
                v-for="user in users"
                :key="user.id"
                class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#30363d]/50"
              >
                <!-- USER ID -->
                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                  #{{ user.id }}
                </td>

                <!-- NAME -->
                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                  {{ user.name }}
                </td>

                <!-- EMAIL -->
                <td class="p-4 text-gray-500 dark:text-[#A0A0A0]">
                  {{ user.email }}
                </td>

                  <!-- ROLE -->
                <td class="p-4">
                  <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase tracking-wider bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-[#F5F5F5]">
                    {{ user.role || 'User' }}
                  </span>
                </td>

              </tr>

              <!-- EMPTY -->
              <tr v-if="users.length === 0">
                <td colspan="4" class="p-10 text-center text-gray-400 dark:text-[#A0A0A0]">
                  No Users found
                </td>
              </tr>

            </tbody>

          </table>

        </div>

      </div>

    </main>

  </div>
</template>
