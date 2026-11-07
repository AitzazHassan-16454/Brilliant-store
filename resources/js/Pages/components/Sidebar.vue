<script setup>
import { ref, computed, provide, onMounted, onBeforeUnmount, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ThemeToggle from "./ThemeToggle.vue";
import NotificationModal from "../../Components/NotificationModal.vue";

const page = usePage();
const site = computed(() => page.props.site ?? {});
const siteName = computed(() => site.value.site_name || "Brilliant");
const siteLogo = computed(() => site.value.logo_url || null);
const siteInitial = computed(() => siteName.value.charAt(0).toUpperCase());

const authUser = computed(() => page.props.auth?.user ?? null);
const userInitial = computed(() => authUser.value?.name ? authUser.value.name.charAt(0).toUpperCase() : "A");

const collapsed = ref(localStorage.getItem("sidebar-collapsed") === "true");
const mobileOpen = ref(false);
let previousOverflow = "";

const openMobile = () => {
  mobileOpen.value = true;
};

const closeMobile = () => {
  mobileOpen.value = false;
};

const onKeydown = (event) => {
  if (event.key === "Escape") {
    closeMobile();
  }
};

watch(
  () => page.url,
  () => closeMobile()
);

watch(mobileOpen, (open) => {
  if (open) {
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = previousOverflow;
  }
});

onMounted(() => {
  document.body.classList.add("has-admin-bar");
  window.addEventListener("keydown", onKeydown);

  if (window.innerWidth < 1024) {
    collapsed.value = false;
  }
});

onBeforeUnmount(() => {
  document.body.classList.remove("has-admin-bar");
  document.body.style.overflow = previousOverflow;
  window.removeEventListener("keydown", onKeydown);
});

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
  {
    href: "/settings",
    label: "Settings",
    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>',
    permission: "settings.view",
  },
];

const isVisible = (item) => {
  if (item.alwaysShow) return true;
  if (item.checkProp) return Boolean(page.props.permissions?.[item.permission]);
  return can(item.permission);
};
</script>

<template>
  <!-- TOP MOBILE HEADER BAR (Fixed on screen top for < lg) -->
  <header
    class="fixed inset-x-0 top-0 z-30 flex h-14 items-center justify-between gap-2 border-b border-gray-200 bg-white/95 px-3 sm:px-4 backdrop-blur dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A]/95 lg:hidden"
  >
    <div class="flex items-center gap-2.5 min-w-0">
      <button
        type="button"
        @click="openMobile"
        class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 text-gray-700 transition hover:bg-gray-100 hover:text-[#1A1A1A] dark:text-[#F5F5F5] dark:hover:bg-[#21262d]"
        aria-label="Open admin navigation drawer"
        :aria-expanded="mobileOpen"
        aria-controls="admin-drawer"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#1A1A1A] text-xs font-bold text-white dark:bg-[#2A2A2A] shadow-sm">
        <img v-if="siteLogo" :src="siteLogo" :alt="siteName" class="h-full w-full bg-white object-contain p-0.5" />
        <span v-else>{{ siteInitial }}</span>
      </div>

      <div class="min-w-0">
        <p class="truncate text-sm font-semibold leading-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ siteName }}</p>
        <p class="truncate text-[10px] leading-tight text-gray-400 dark:text-[#A0A0A0]">Admin Panel</p>
      </div>
    </div>

    <!-- Mobile Header Quick Controls -->
    <div class="flex items-center gap-1.5 shrink-0">
      <ThemeToggle />

      <Link
        href="/"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 dark:border-[#D4AF37]/20 text-gray-600 transition hover:bg-gray-100 hover:text-[#1A1A1A] dark:text-[#A0A0A0] dark:hover:bg-[#21262d] dark:hover:text-[#F5F5F5]"
        title="Storefront"
        aria-label="Back to store"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
        </svg>
      </Link>
    </div>
  </header>

  <!-- MOBILE OVERLAY BACKDROP -->
  <Transition name="admin-fade">
    <div
      v-if="mobileOpen"
      class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden transition-opacity"
      @click="closeMobile"
      aria-hidden="true"
    ></div>
  </Transition>

  <!-- SIDEBAR / MOBILE DRAWER -->
  <aside
    id="admin-drawer"
    :class="[
      'fixed inset-y-0 left-0 z-50 flex h-dvh w-72 max-w-[85vw] shrink-0 flex-col border-r border-gray-200 bg-[#FAF7F2] shadow-2xl transition-transform duration-300 ease-in-out dark:border-[#D4AF37]/20 dark:bg-[#0A0A0A] lg:inset-auto lg:top-0 lg:z-40 lg:sticky lg:h-screen lg:translate-x-0 lg:shadow-sm',
      mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      collapsed ? 'lg:w-[72px]' : 'lg:w-64'
    ]"
  >
    <!-- DRAWER BRAND HEADER -->
    <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A] lg:px-5">
      <Link href="/dashboard" class="flex items-center gap-3 min-w-0 no-underline">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#1A1A1A] text-sm font-bold text-white dark:bg-[#2A2A2A] shadow-sm">
          <img v-if="siteLogo" :src="siteLogo" :alt="siteName" class="h-full w-full bg-white object-contain p-0.5" />
          <span v-else>{{ siteInitial }}</span>
        </div>
        <div :class="collapsed ? 'lg:hidden' : ''" class="min-w-0 overflow-hidden whitespace-nowrap">
          <h1 class="text-base font-semibold leading-tight tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ siteName }}</h1>
          <p class="text-[10px] uppercase font-semibold tracking-wider text-[#D4AF37] truncate">Admin Console</p>
        </div>
      </Link>

      <!-- Mobile Close (X) button -->
      <button
        type="button"
        @click="closeMobile"
        class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-gray-200 text-gray-400 transition hover:bg-gray-100 hover:text-[#1A1A1A] dark:border-[#D4AF37]/20 dark:text-[#A0A0A0] dark:hover:bg-[#21262d] dark:hover:text-[#F5F5F5] lg:hidden"
        aria-label="Close admin menu"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- DESKTOP COLLAPSE BUTTON -->
    <div class="hidden border-b border-gray-200 px-3 py-2 dark:border-[#D4AF37]/20 lg:block">
      <button
        type="button"
        @click="toggle"
        :class="[
          'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-semibold text-gray-500 transition hover:bg-white hover:text-[#1A1A1A] dark:text-[#A0A0A0] dark:hover:bg-[#1A1A1A] dark:hover:text-[#F5F5F5]',
          collapsed ? 'justify-center px-0' : ''
        ]"
        :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="collapsed ? 'rotate-180' : ''"
          fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
        <span v-if="!collapsed" class="whitespace-nowrap">Collapse Menu</span>
      </button>
    </div>

    <!-- NAVIGATION LINKS -->
    <nav class="no-scrollbar min-h-0 flex-1 space-y-1 overflow-y-auto overflow-x-hidden p-2.5 overscroll-contain">
      <template v-for="item in navItems" :key="item.href">
        <Link
          v-if="isVisible(item)"
          :href="item.href"
          :class="[
            'group relative flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all duration-150',
            collapsed ? 'lg:justify-center lg:px-0 lg:py-2.5' : '',
            isActive(item.href)
              ? 'bg-[#1A1A1A] text-white shadow-sm dark:bg-[#D4AF37] dark:text-[#1A1A1A] font-semibold'
              : 'text-gray-600 hover:bg-white hover:text-[#1A1A1A] dark:text-[#A0A0A0] dark:hover:bg-[#1A1A1A] dark:hover:text-[#F5F5F5]'
          ]"
        >
          <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" v-html="item.icon"></svg>
          <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">{{ item.label }}</span>

          <!-- Tooltip on collapsed desktop -->
          <div
            v-if="collapsed"
            class="pointer-events-none absolute left-full z-50 ml-3 hidden whitespace-nowrap rounded-lg bg-[#1A1A1A] px-2.5 py-1 text-xs font-semibold text-white shadow-lg opacity-0 transition-opacity duration-150 group-hover:opacity-100 lg:block dark:bg-white dark:text-[#1A1A1A]"
          >
            {{ item.label }}
          </div>
        </Link>
      </template>
    </nav>

    <!-- DRAWER / SIDEBAR FOOTER: USER & LOGOUT -->
    <div class="shrink-0 border-t border-gray-200 bg-white/70 p-3 dark:border-[#D4AF37]/20 dark:bg-[#1A1A1A]/70">
      <div v-if="authUser" class="flex items-center gap-3" :class="collapsed ? 'lg:justify-center' : ''">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#D4AF37]/15 border border-[#D4AF37]/30 text-xs font-bold text-[#B8960F] dark:text-[#D4AF37]">
          {{ userInitial }}
        </div>
        <div :class="collapsed ? 'lg:hidden' : ''" class="min-w-0 flex-1">
          <p class="truncate text-xs font-semibold text-[#1A1A1A] dark:text-[#F5F5F5]">{{ authUser.name }}</p>
          <p class="truncate text-[10px] text-gray-400 dark:text-[#A0A0A0] capitalize">{{ authUser.role || 'Admin' }}</p>
        </div>
        <Link
          href="/logout"
          method="post"
          as="button"
          :class="collapsed ? 'lg:hidden' : ''"
          class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/30 dark:hover:text-red-400"
          title="Sign out"
          aria-label="Logout"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
        </Link>
      </div>

      <!-- Collapsed-only logout icon for desktop -->
      <div v-if="collapsed && authUser" class="hidden lg:flex justify-center mt-2">
        <Link
          href="/logout"
          method="post"
          as="button"
          class="group relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/30 dark:hover:text-red-400"
          aria-label="Logout"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
          <div class="pointer-events-none absolute left-full z-50 ml-3 hidden whitespace-nowrap rounded-lg bg-[#1A1A1A] px-2.5 py-1 text-xs font-semibold text-white shadow-lg opacity-0 transition-opacity duration-150 group-hover:opacity-100 lg:block dark:bg-white dark:text-[#1A1A1A]">
            Logout
          </div>
        </Link>
      </div>
    </div>
  </aside>

  <NotificationModal />
</template>

<style scoped>
.admin-fade-enter-active,
.admin-fade-leave-active {
  transition: opacity 0.25s ease;
}

.admin-fade-enter-from,
.admin-fade-leave-to {
  opacity: 0;
}
</style>
