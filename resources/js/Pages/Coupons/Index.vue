<script setup>
import { ref, watch } from "vue"
import { router, Head, usePage } from "@inertiajs/vue3"
import Sidebar from "../components/Sidebar.vue"
import { useNotification } from "../../composables/useNotification.js"

const { error, success } = useNotification()

defineOptions({ layout: false })

const props = defineProps({
  coupons: Array,
})

const page = usePage()
const can = (permission) => Boolean(page.props.permissions?.can?.[permission])

const form = ref({
  code: "",
  type: "percentage",
  value: "",
  min_order: "0",
  max_uses: "",
  expires_at: "",
  active: true,
})
const editingId = ref(null)

const reset = () => {
  form.value = { code: "", type: "percentage", value: "", min_order: "0", max_uses: "", expires_at: "", active: true }
  editingId.value = null
}

const save = () => {
  if (!form.value.code.trim() || !form.value.value) {
    error("Code and value are required.")
    return
  }

  const payload = { ...form.value }
  if (!payload.max_uses) payload.max_uses = null
  if (!payload.expires_at) payload.expires_at = null

  if (editingId.value) {
    if (!can("coupons.update")) return
    router.put(`/coupons/${editingId.value}`, payload, {
      onSuccess: () => {
        success("Coupon updated.")
        reset()
        router.reload({ only: ["coupons"] })
      },
    })
  } else {
    if (!can("coupons.create")) return
    router.post("/coupons", payload, {
      onSuccess: () => {
        success("Coupon created.")
        reset()
        router.reload({ only: ["coupons"] })
      },
    })
  }
}

const edit = (coupon) => {
  if (!can("coupons.update")) return
  form.value = {
    code: coupon.code,
    type: coupon.type,
    value: coupon.value,
    min_order: coupon.min_order,
    max_uses: coupon.max_uses ?? "",
    expires_at: coupon.expires_at ? coupon.expires_at.split("T")[0] : "",
    active: coupon.active,
  }
  editingId.value = coupon.id
}

const remove = (id) => {
  if (!can("coupons.delete")) return
  if (!confirm("Delete this coupon?")) return
  router.delete(`/coupons/${id}`, {
    onSuccess: () => {
      success("Coupon deleted.")
      router.reload({ only: ["coupons"] })
    },
  })
}

const isExpired = (coupon) => coupon.expires_at && new Date(coupon.expires_at) < new Date()
const isMaxed = (coupon) => coupon.max_uses !== null && coupon.used_count >= coupon.max_uses
const statusLabel = (coupon) => {
  if (!coupon.active) return { text: "Inactive", class: "bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500" }
  if (isExpired(coupon)) return { text: "Expired", class: "bg-red-50 dark:bg-[#f85149]/10 text-red-500" }
  if (isMaxed(coupon)) return { text: "Maxed Out", class: "bg-yellow-50 dark:bg-[#d29922]/10 text-yellow-600" }
  return { text: "Active", class: "bg-green-50 dark:bg-[#3fb950]/10 text-green-600" }
}
</script>

<template>
<Head title="Coupons" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A]">
  <Sidebar />

  <main class="flex-1 min-w-0 p-3.5 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">Coupons</h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">Manage store promotional discount codes</p>
      </div>
    </div>

    <div v-if="$page.props.flash?.success"
      class="mb-5 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 dark:bg-[#3fb950]/10 dark:border-green-800 dark:text-[#3fb950]">
      {{ $page.props.flash.success }}
    </div>

    <div v-if="can('coupons.create') || can('coupons.update')" class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 mb-6 shadow-xs dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
      <h3 class="text-sm font-bold uppercase tracking-wider text-[#1A1A1A] dark:text-[#F5F5F5] mb-4">
        {{ editingId ? "Edit Coupon" : "Create Coupon" }}
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Code</label>
          <input v-model="form.code" type="text" placeholder="e.g. SUMMER20"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] uppercase tracking-wider font-mono focus:border-[#D4AF37]" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Type</label>
          <select v-model="form.type"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] focus:border-[#D4AF37]">
            <option value="percentage">Percentage (%)</option>
            <option value="fixed">Fixed ($)</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Discount Value</label>
          <input v-model="form.value" type="number" step="0.01" min="0.01" placeholder="0.00"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] focus:border-[#D4AF37]" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Min Order ($)</label>
          <input v-model="form.min_order" type="number" step="0.01" min="0" placeholder="0"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] focus:border-[#D4AF37]" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Max Uses</label>
          <input v-model="form.max_uses" type="number" min="1" placeholder="Unlimited"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] focus:border-[#D4AF37]" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 dark:text-[#A0A0A0] mb-1.5">Expires At</label>
          <input v-model="form.expires_at" type="date"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none dark:bg-[#0A0A0A] dark:border-[#D4AF37]/20 dark:text-[#F5F5F5] focus:border-[#D4AF37]" />
        </div>
        <div class="flex items-end pb-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="form.active" type="checkbox" class="w-4 h-4 rounded border-gray-200 cursor-pointer" />
            <span class="text-sm font-semibold text-gray-700 dark:text-[#F5F5F5]">Active Coupon</span>
          </label>
        </div>
      </div>

      <div class="flex flex-wrap gap-2.5 mt-5">
        <button @click="save"
          class="px-5 py-2.5 bg-[#D4AF37] hover:bg-[#B8960F] text-white text-sm font-semibold rounded-xl transition cursor-pointer shadow-xs">
          {{ editingId ? "Update Coupon" : "Create Coupon" }}
        </button>
        <button v-if="editingId" @click="reset"
          class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-semibold rounded-xl hover:bg-[#FAF7F2] dark:border-[#D4AF37]/20 dark:text-[#A0A0A0] dark:hover:bg-[#21262d] transition cursor-pointer">
          Cancel
        </button>
      </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden dark:bg-[#1A1A1A] dark:border-[#D4AF37]/20">
      <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 flex items-center justify-between">
        <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">All Coupons</h3>
        <span class="text-xs text-gray-400 dark:text-[#A0A0A0] lg:hidden">
          Scroll horizontally &rarr;
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[700px]">
          <thead class="bg-[#FAF7F2] dark:bg-[#1A1A1A] text-gray-500 dark:text-[#A0A0A0] uppercase text-xs">
            <tr>
              <th class="p-4">Code</th>
              <th class="p-4">Type</th>
              <th class="p-4">Value</th>
              <th class="p-4">Min Order</th>
              <th class="p-4">Uses</th>
              <th class="p-4">Expires</th>
              <th class="p-4">Status</th>
              <th v-if="can('coupons.update') || can('coupons.delete')" class="p-4 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="coupon in coupons" :key="coupon.id"
              class="border-t border-gray-100 dark:border-[#D4AF37]/20 hover:bg-[#FAF7F2] dark:hover:bg-[#161b22]/50">
              <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5] font-mono">{{ coupon.code }}</td>
              <td class="p-4 text-gray-600 dark:text-[#A0A0A0] capitalize">{{ coupon.type }}</td>
              <td class="p-4 font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">
                {{ coupon.type === 'percentage' ? `${coupon.value}%` : `$${coupon.value}` }}
              </td>
              <td class="p-4 text-gray-600 dark:text-[#A0A0A0]">${{ coupon.min_order }}</td>
              <td class="p-4 text-gray-600 dark:text-[#A0A0A0]">
                {{ coupon.used_count }}{{ coupon.max_uses ? ` / ${coupon.max_uses}` : '' }}
              </td>
              <td class="p-4 text-gray-600 dark:text-[#A0A0A0]">
                {{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : 'Never' }}
              </td>
              <td class="p-4">
                <span :class="['text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md', statusLabel(coupon).class]">
                  {{ statusLabel(coupon).text }}
                </span>
              </td>
              <td v-if="can('coupons.update') || can('coupons.delete')" class="p-4">
                <div class="flex justify-center gap-2">
                  <button v-if="can('coupons.update')" @click="edit(coupon)"
                    class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#0A0A0A] rounded-lg hover:bg-yellow-500 hover:text-white dark:hover:bg-yellow-500 text-xs font-semibold cursor-pointer">
                    Edit
                  </button>
                  <button v-if="can('coupons.delete')" @click="remove(coupon.id)"
                    class="px-3 py-1.5 bg-[#FAF7F2] dark:bg-[#0A0A0A] rounded-lg hover:bg-red-500 hover:text-white dark:hover:bg-red-500 text-xs font-semibold cursor-pointer">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="coupons.length === 0">
              <td colspan="8" class="p-10 text-center text-gray-400 dark:text-[#A0A0A0]">No coupons found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</template>
