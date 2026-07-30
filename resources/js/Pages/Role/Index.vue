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

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] transition-colors duration-300">
        <Sidebar />

        <main class="flex-1 p-6 lg:p-8 overflow-x-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 animate-fade-in-up-sm">
                <h1 class="text-2xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                    All Roles
                </h1>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="can('roles.create')"
                        href="/roles/create"
                        class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white rounded-xl font-semibold transition shadow-sm active:scale-95"
                    >
                        + Add Role
                    </Link>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
                <div class="p-5 border-b border-gray-100 dark:border-[#D4AF37]/20">
                    <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                        Roles List
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
                            <tr>
                                <th class="p-4">Name</th>
                                <th v-if="canManageRoles()" class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="role in roles"
                                :key="role.id"
                                class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#21262d]/50 transition"
                            >
                                <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                                    {{ role.name }}
                                </td>

                                <td v-if="canManageRoles()" class="p-4">
                                    <div class="flex justify-center gap-2">
                                        <Link
                                            v-if="can('roles.update')"
                                            :href="`/roles/${role.id}/edit`"
                                            class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] hover:bg-yellow-500 hover:text-white transition"
                                        >
                                            Edit
                                        </Link>

                                        <button
                                            v-if="can('roles.delete')"
                                            @click="deleteRole(role.id)"
                                            class="px-3 py-1.5 rounded-lg bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-700 dark:text-[#F5F5F5] hover:bg-red-500 hover:text-white transition"
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
