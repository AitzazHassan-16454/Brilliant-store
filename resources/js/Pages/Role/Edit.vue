<script setup>
import { Link, useForm, Head } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import Sidebar from '../components/Sidebar.vue'

defineOptions({ layout: false })

const props = defineProps({
    role: Object,
    groupedPermissions: Array,
    assignedPermissions: Array,
})

const form = useForm({
    name: props.role.name,
    permissions: [...props.assignedPermissions],
})

const selectAllByModule = ref({})
const selectAllPermissions = ref(false)

const isModuleFullySelected = (moduleName) => {
    const module = props.groupedPermissions.find((m) => m.name === moduleName)
    if (!module) return false
    return module.permissions.every((perm) =>
        form.permissions.includes(perm.id)
    )
}

const areAllPermissionsSelected = computed(() => {
    const totalPermissions = props.groupedPermissions.reduce(
        (sum, module) => sum + module.permissions.length,
        0
    )
    return form.permissions.length === totalPermissions && totalPermissions > 0
})

const toggleModulePermissions = (moduleName) => {
    const module = props.groupedPermissions.find((m) => m.name === moduleName)
    if (!module) return

    const isFullySelected = isModuleFullySelected(moduleName)

    module.permissions.forEach((perm) => {
        if (isFullySelected) {
            form.permissions = form.permissions.filter((p) => p !== perm.id)
        } else {
            if (!form.permissions.includes(perm.id)) {
                form.permissions.push(perm.id)
            }
        }
    })

    selectAllByModule.value[moduleName] = !isFullySelected
}

const toggleAllPermissions = () => {
    if (areAllPermissionsSelected.value) {
        form.permissions = []
        selectAllByModule.value = {}
        props.groupedPermissions.forEach((module) => {
            selectAllByModule.value[module.name] = false
        })
    } else {
        form.permissions = props.groupedPermissions.reduce((acc, module) => {
            return acc.concat(module.permissions.map((p) => p.id))
        }, [])
        props.groupedPermissions.forEach((module) => {
            selectAllByModule.value[module.name] = true
        })
    }
    selectAllPermissions.value = !selectAllPermissions.value
}

const togglePermission = (permissionId) => {
    const index = form.permissions.indexOf(permissionId)
    if (index > -1) {
        form.permissions.splice(index, 1)
    } else {
        form.permissions.push(permissionId)
    }
}

const initializeSelectAllModules = () => {
    props.groupedPermissions.forEach((module) => {
        selectAllByModule.value[module.name] = isModuleFullySelected(module.name)
    })
    selectAllPermissions.value = areAllPermissionsSelected.value
}

if (props.groupedPermissions) {
    initializeSelectAllModules()
}

const submit = () => {
    form.put(`/roles/${props.role.id}`, {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Edit Role" />

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
        <Sidebar />

        <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <div>
                    <h2 class="text-xl sm:text-3xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Edit Role</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-1">Update role details and granular module permissions</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs p-4 sm:p-6 lg:p-8 max-w-5xl dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
                <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-[#F5F5F5] mb-2">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Enter role name (e.g., Editor, Manager)"
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#D4AF37] dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] transition"
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                            <div>
                                <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Permissions</h3>
                                <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Toggle all permissions or select by category</p>
                            </div>
                            <button
                                type="button"
                                @click="toggleAllPermissions"
                                class="self-start sm:self-auto px-4 py-2 text-xs font-semibold text-white bg-[#D4AF37] hover:bg-[#B8960F] rounded-xl cursor-pointer transition shadow-xs"
                            >
                                {{ areAllPermissionsSelected ? 'Deselect All' : 'Select All Permissions' }}
                            </button>
                        </div>

                        <div class="grid gap-4 sm:gap-6">
                            <div
                                v-for="module in groupedPermissions"
                                :key="module.name"
                                class="border border-gray-200 rounded-xl p-4 sm:p-5 bg-[#FAF7F2] dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A]"
                            >
                                <div class="flex items-center mb-3 sm:mb-4 pb-3 border-b border-gray-200 dark:border-[#D4AF37]/20">
                                    <input
                                        type="checkbox"
                                        :checked="isModuleFullySelected(module.name)"
                                        @change="toggleModulePermissions(module.name)"
                                        class="w-4 h-4 sm:w-5 sm:h-5 rounded border-gray-300 cursor-pointer text-[#D4AF37]"
                                    />
                                    <label class="ml-2.5 sm:ml-3 text-sm sm:text-base font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] cursor-pointer">
                                        {{ module.display_name }}
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3">
                                    <div
                                        v-for="permission in module.permissions"
                                        :key="permission.id"
                                        class="flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`permission-${permission.id}`"
                                            :value="permission.id"
                                            :checked="form.permissions.includes(permission.id)"
                                            @change="togglePermission(permission.id)"
                                            class="w-4 h-4 rounded border-gray-300 cursor-pointer text-[#D4AF37]"
                                        />
                                        <label
                                            :for="`permission-${permission.id}`"
                                            class="ml-2 text-xs sm:text-sm text-gray-700 dark:text-[#F5F5F5] cursor-pointer hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]"
                                        >
                                            {{ permission.display_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p v-if="form.errors.permissions" class="text-red-500 text-xs mt-2">
                            {{ form.errors.permissions }}
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-[#D4AF37]/20">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white rounded-xl text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition shadow-xs"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Role' }}
                        </button>
                        <Link
                            href="/roles"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-200 rounded-xl text-gray-700 hover:bg-[#FAF7F2] text-sm font-semibold dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] dark:hover:bg-[#21262d] transition"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
