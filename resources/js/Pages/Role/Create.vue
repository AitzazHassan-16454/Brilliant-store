<script setup>
import { Link, useForm, Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Sidebar from '../components/Sidebar.vue'

defineOptions({ layout: false })

const props = defineProps({
    groupedPermissions: Array,
})

const form = useForm({
    name: '',
    permissions: [],
})

const selectAllByModule = ref({})
const selectAllPermissions = ref(false)

const initializeSelectAllModules = () => {
    props.groupedPermissions.forEach((module) => {
        selectAllByModule.value[module.name] = false
    })
}

if (props.groupedPermissions) {
    initializeSelectAllModules()
}

const isModuleFullySelected = (moduleName) => {
    const module = props.groupedPermissions.find((m) => m.name === moduleName)
    if (!module) return false
    return module.permissions.every((perm) =>
        form.permissions.includes(perm.id)
    )
}

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

const areAllPermissionsSelected = computed(() => {
    const totalPermissions = props.groupedPermissions.reduce(
        (sum, module) => sum + module.permissions.length,
        0
    )
    return form.permissions.length === totalPermissions && totalPermissions > 0
})

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

const submit = () => {
    form.post('/roles', {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Create Role" />

    <div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
        <Sidebar />

        <main class="flex-1 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Create Role</h2>
                    <p class="text-gray-500 dark:text-[#A0A0A0] mt-2">Create a new role and assign permissions</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 max-w-5xl dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
                <form @submit.prevent="submit" class="space-y-8">
                    <div>
                        <label class="block text-gray-700 dark:text-[#F5F5F5] font-medium mb-2">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Enter role name (e.g., Editor, Manager)"
                            class="w-full border rounded-xl px-4 py-3 outline-none focus:border-gray-400 dark:focus:border-gray-500 dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5]"
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">Permissions</h3>
                            <button
                                type="button"
                                @click="toggleAllPermissions"
                                class="px-4 py-2 text-sm font-semibold text-white bg-[#D4AF37] hover:bg-[#B8960F] rounded-xl cursor-pointer"
                            >
                                {{ areAllPermissionsSelected ? 'Deselect All' : 'Select All' }}
                            </button>
                        </div>

                        <div class="grid gap-6">
                            <div
                                v-for="module in groupedPermissions"
                                :key="module.name"
                                class="border border-gray-200 rounded-lg p-6 bg-[#FAF7F2] dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A]"
                            >
                                <div class="flex items-center mb-4 pb-4 border-b border-gray-200 dark:border-[#D4AF37]/20">
                                    <input
                                        type="checkbox"
                                        :checked="isModuleFullySelected(module.name)"
                                        @change="toggleModulePermissions(module.name)"
                                        class="w-5 h-5 rounded border-gray-200 cursor-pointer"
                                    />
                                    <label class="ml-3 text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] cursor-pointer">
                                        {{ module.display_name }}
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
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
                                            class="w-4 h-4 rounded border-gray-200 cursor-pointer"
                                        />
                                        <label
                                            :for="`permission-${permission.id}`"
                                            class="ml-2 text-sm text-gray-700 dark:text-[#F5F5F5] cursor-pointer hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]"
                                        >
                                            {{ permission.display_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p v-if="form.errors.permissions" class="text-red-500 text-sm mt-2">
                            {{ form.errors.permissions }}
                        </p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-[#D4AF37]/20">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Role' }}
                        </button>
                        <Link
                            href="/roles"
                            class="px-6 py-2.5 border border-gray-200 rounded-xl text-gray-700 hover:bg-[#FAF7F2] font-medium dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] dark:hover:bg-[#21262d]"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
