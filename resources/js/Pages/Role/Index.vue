<script setup>
import { Link, router, Head, usePage } from '@inertiajs/vue3'
import Sidebar from '../components/Sidebar.vue'

defineOptions({ layout: false })

defineProps({
    roles: Array,
})

const deleteRole = (id) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(`/roles/${id}`, {
            preserveScroll: true,
        })
    }
}

const page = usePage()
const can = (permission) => Boolean(page.props.permissions?.can?.[permission])
const canManageRoles = () => can('roles.update') || can('roles.delete')
</script>

<template>
    <Head title="Roles" />

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
        <Sidebar />

        <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
                        All Roles
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">
                        Manage system roles and permission sets
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="can('roles.create')"
                        href="/roles/create"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white rounded-xl text-sm font-semibold shadow-xs transition"
                    >
                        + Add Role
                    </Link>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
                <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                        Roles List
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left min-w-[360px]">
                        <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
                            <tr>
                                <th class="p-4">Role Name</th>
                                <th v-if="canManageRoles()" class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="role in roles"
                                :key="role.id"
                                class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]/50"
                            >
                                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                                    {{ role.name }}
                                </td>

                                <td v-if="canManageRoles()" class="p-4">
                                    <div class="flex justify-center gap-2">
                                        <Link
                                            v-if="can('roles.update')"
                                            :href="`/roles/${role.id}/edit`"
                                            class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#0A0A0A] text-gray-700 dark:text-[#F5F5F5] hover:bg-yellow-500 hover:text-white dark:hover:bg-yellow-500 text-xs font-semibold cursor-pointer transition"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            v-if="can('roles.delete')"
                                            @click="deleteRole(role.id)"
                                            class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#0A0A0A] text-gray-700 dark:text-[#F5F5F5] hover:bg-red-500 hover:text-white dark:hover:bg-red-500 text-xs font-semibold cursor-pointer transition"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="roles.length === 0" class="p-12 text-center">
                    <p class="text-gray-500 dark:text-[#A0A0A0]">No roles found. Create your first role to get started.</p>
                </div>
            </div>
        </main>
    </div>
</template>
