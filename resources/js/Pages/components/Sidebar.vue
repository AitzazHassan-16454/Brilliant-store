<script setup>
import { ref, provide } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const collapsed = ref(localStorage.getItem("sidebar-collapsed") === "true");

const toggle = () => {
  collapsed.value = !collapsed.value;
  localStorage.setItem("sidebar-collapsed", collapsed.value);
};

provide("sidebarCollapsed", collapsed);

const isActive = (url) => page.url === url;
const can = (permission) => Boolean(page.props.permissions?.can?.[permission]);

const navItems = [
  {
    href: "/",
    label: "Back to Home",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>',
    alwaysShow: true,
  },
  {
    href: "/dashboard",
    label: "Dashboard",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>',
    permission: "canViewDashboard",
    checkProp: true,
  },
  {
    href: "/categories",
    label: "Categories",
    icon: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    permission: "categories.view",
  },
  {
    href: "/subcategories",
    label: "Subcategories",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h10"/><circle cx="20" cy="18" r="2"/>',
    permission: "subcategories.view",
  },
  {
    href: "/products",
    label: "Products",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
    permission: "products.view",
  },
  {
    href: "/users",
    label: "Users",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    permission: "users.view",
  },
  {
    href: "/roles",
    label: "Roles",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    permission: "roles.view",
  },
  {
    href: "/Orders",
    label: "Orders",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
    permission: "orders.view",
  },
  {
    href: "/reviews",
    label: "Reviews",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>',
    permission: "reviews.view",
  },
  {
    href: "/coupons",
    label: "Coupons",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
    permission: "coupons.view",
  },
  {
    href: "/custom-orders",
    label: "Custom Orders",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
    permission: "orders.view",
  },
  {
    href: "/faqs",
    label: "FAQs",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>',
    permission: "faqs.view",
  },
];

const isVisible = (item) => {
  if (item.alwaysShow) return true;
  if (item.checkProp) return Boolean(page.props.permissions?.[item.permission]);
  return can(item.permission);
};
</script>

<template>
  <aside
    :class="[
      'h-screen sticky top-0 bg-[#FAF7F2] dark:bg-[#0A0A0A] border-r border-gray-200 dark:border-[#D4AF37]/20 flex flex-col shadow-sm shrink-0',
      collapsed ? 'w-[68px]' : 'w-64'
    ]"
  >

    <div class="p-5 border-b border-gray-100 dark:border-[#D4AF37]/20 bg-white dark:bg-[#1A1A1A] flex items-center gap-3 min-h-[72px]">
      <div class="w-9 h-9 rounded-lg bg-[#1A1A1A] dark:bg-[#2A2A2A] text-white flex items-center justify-center text-sm font-bold shrink-0">
        B
      </div>
      <div v-if="!collapsed" class="overflow-hidden whitespace-nowrap">
        <h1 class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] tracking-wide leading-tight">Brilliant</h1>
        <p class="text-[10px] text-gray-400 dark:text-[#A0A0A0]">Admin Control Panel</p>
      </div>
    </div>

    <div class="px-2 py-2 border-b border-gray-100 dark:border-[#D4AF37]/20">
      <button
        @click="toggle"
        :class="[
          'w-full flex items-center gap-3 rounded-lg px-4 py-2 text-gray-400 dark:text-[#A0A0A0] hover:bg-white dark:hover:bg-[#21262d] hover:shadow-sm hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9] cursor-pointer',
          collapsed ? 'justify-center px-0' : ''
        ]"
      >
        <svg
          class="w-5 h-5 shrink-0"
          :class="collapsed ? 'rotate-180' : ''"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
        <span v-if="!collapsed" class="text-sm font-medium whitespace-nowrap">Collapse</span>
      </button>
    </div>

    <nav class="flex-1 p-2 space-y-0.5 overflow-y-auto overflow-x-hidden">

      <template v-for="item in navItems" :key="item.href">
        <Link
          v-if="isVisible(item)"
          :href="item.href"
          :class="[
            'relative flex items-center gap-3 rounded-lg font-medium group',
            collapsed ? 'px-0 py-3 justify-center' : 'px-4 py-3',
            isActive(item.href)
              ? 'text-[#1A1A1A] dark:text-[#F5F5F5] font-medium bg-white dark:bg-[#1A1A1A] shadow-sm'
              : 'text-gray-400 dark:text-[#A0A0A0] hover:bg-white dark:hover:bg-[#161b22] hover:shadow-sm hover:text-[#1A1A1A] dark:hover:text-[#c9d1d9]'
          ]"
        >
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" v-html="item.icon"></svg>
          <span v-if="!collapsed" class="whitespace-nowrap text-sm">{{ item.label }}</span>

          <div
            v-if="collapsed"
            class="absolute left-full ml-2 px-2.5 py-1.5 rounded-lg bg-[#1A1A1A] text-white text-xs font-medium whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 z-50"
          >
            {{ item.label }}
          </div>
        </Link>
      </template>

    </nav>

  </aside>
</template>
