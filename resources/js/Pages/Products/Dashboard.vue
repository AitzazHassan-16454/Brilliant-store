<script setup>
import { Head, Link } from "@inertiajs/vue3"
import { computed } from "vue"
import Sidebar from "../components/Sidebar.vue"
import { Line, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Title, Tooltip, Legend, Filler } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
    productsCount: Number,
    totalStock: Number,
    categoriesCount: Number,
    subcategoriesCount: Number,
    usersCount: Number,
    rolesCount: Number,
    ordersCount: Number,
    couponsCount: Number,
    pendingReviewsCount: Number,
    revenue: Number,
    revenueThisMonth: Number,
    revenueTrend: Number,
    ordersThisMonth: Number,
    ordersTrend: Number,
    usersThisMonth: Number,
    usersTrend: Number,
    productsThisMonth: Number,
    productsTrend: Number,
    monthlyRevenue: Array,
    productsByCategory: Array,
    recentOrders: Array,
    pendingReviewList: Array,
    lowStockCount: Number,
})

const formatCurrency = (v) => `$${(v || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const trendColor = (v) => v >= 0 ? "text-emerald-500 dark:text-[#3fb950]" : "text-red-500 dark:text-[#f85149]"
const trendIcon = (v) => v >= 0
    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>'
    : '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941"/>'

const statusColor = (s) => ({
    pending: "bg-amber-100 text-amber-700 dark:bg-[#d29922]/10 dark:text-[#d29922]",
    processing: "bg-blue-100 text-blue-700 dark:bg-[#58a6ff]/10 dark:text-[#58a6ff]",
    completed: "bg-emerald-100 text-emerald-700 dark:bg-[#3fb950]/10 dark:text-[#3fb950]",
    cancelled: "bg-red-100 text-red-700 dark:bg-[#f85149]/10 dark:text-[#f85149]",
    shipped: "bg-purple-100 text-purple-700 dark:bg-[#bc8cff]/10 dark:text-[#bc8cff]",
}[s] || "bg-gray-100 text-gray-700 dark:bg-[#2A2A2A] dark:text-[#A0A0A0]")

const starPath = 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z'

const revenueChartData = computed(() => ({
    labels: props.monthlyRevenue?.map(r => r.month) || [],
    datasets: [{
        label: 'Revenue',
        data: props.monthlyRevenue?.map(r => r.revenue) || [],
        borderColor: '#58a6ff',
        backgroundColor: 'rgba(88, 166, 255, 0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#58a6ff',
        pointBorderColor: '#0d1117',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
    }]
}))

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#161b22',
            titleFont: { size: 12 },
            bodyFont: { size: 13, weight: '500' },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => `$${ctx.parsed.y.toLocaleString()}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#94A3B8', font: { size: 11 } },
        },
        y: {
            grid: { color: 'rgba(148, 163, 184, 0.15)' },
            ticks: {
                color: '#94A3B8',
                font: { size: 11 },
                callback: (v) => `$${(v / 1000).toFixed(0)}k`,
            },
        },
    },
}

const categoryChartData = computed(() => ({
    labels: props.productsByCategory?.map(c => c.name) || [],
    datasets: [{
        data: props.productsByCategory?.map(c => c.count) || [],
        backgroundColor: ['#58a6ff', '#3fb950', '#d29922', '#f85149', '#bc8cff', '#f0883e', '#79c0ff'],
        borderWidth: 0,
        hoverOffset: 8,
    }]
}))

const categoryChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 16,
                usePointStyle: true,
                pointStyle: 'circle',
                color: '#64748B',
                font: { size: 11 },
            },
        },
        tooltip: {
            backgroundColor: '#1E293B',
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => ` ${ctx.label}: ${ctx.parsed} products`,
            },
        },
    },
}
</script>

<template>
<Head title="Dashboard" />

<div class="flex min-h-screen bg-[#FAF7F2] dark:bg-[#0A0A0A] text-[#1A1A1A] dark:text-[#F5F5F5] transition-colors duration-300">

    <Sidebar />

    <main class="flex-1 overflow-hidden">

        <!-- Header -->
        <div class="sticky top-0 z-30 bg-white/80 dark:bg-[#0A0A0A]/80 backdrop-blur-md border-b border-gray-100 dark:border-[#D4AF37]/20 transition-colors duration-300">
            <div class="h-16 px-6 sm:px-8 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">
                        Dashboard
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-[#A0A0A0] mt-0.5">
                        {{ new Date().toLocaleDateString("en-US", { weekday: "long", month: "long", day: "numeric", year: "numeric" }) }}
                    </p>
                </div>
                <div class="hidden sm:flex items-center gap-3">
                    <div class="px-4 py-2 rounded-lg border border-gray-200 dark:border-[#D4AF37]/20 text-sm font-medium text-gray-700 dark:text-[#F5F5F5] transition-colors duration-300">
                        Admin Panel
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Revenue</span>
                        <div class="w-9 h-9 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] text-gray-600 dark:text-[#A0A0A0] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ formatCurrency(revenueThisMonth) }}</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <svg class="w-4 h-4" :class="trendColor(revenueTrend)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="trendIcon(revenueTrend)"></svg>
                        <span class="text-sm font-medium" :class="trendColor(revenueTrend)">{{ Math.abs(revenueTrend) }}%</span>
                        <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">vs last month</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Orders</span>
                        <div class="w-9 h-9 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] text-gray-600 dark:text-[#A0A0A0] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ ordersThisMonth }}</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <svg class="w-4 h-4" :class="trendColor(ordersTrend)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="trendIcon(ordersTrend)"></svg>
                        <span class="text-sm font-medium" :class="trendColor(ordersTrend)">{{ Math.abs(ordersTrend) }}%</span>
                        <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">vs last month</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Products</span>
                        <div class="w-9 h-9 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] text-gray-600 dark:text-[#A0A0A0] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ productsCount }}</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <svg class="w-4 h-4" :class="trendColor(productsTrend)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="trendIcon(productsTrend)"></svg>
                        <span class="text-sm font-medium" :class="trendColor(productsTrend)">{{ Math.abs(productsTrend) }}%</span>
                        <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">vs last month</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-[#A0A0A0]">Customers</span>
                        <div class="w-9 h-9 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] text-gray-600 dark:text-[#A0A0A0] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-medium tracking-tight text-[#1A1A1A] dark:text-[#F5F5F5]">{{ usersCount }}</p>
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <svg class="w-4 h-4" :class="trendColor(usersTrend)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="trendIcon(usersTrend)"></svg>
                        <span class="text-sm font-medium" :class="trendColor(usersTrend)">{{ Math.abs(usersTrend) }}%</span>
                        <span class="text-xs text-gray-400 dark:text-[#A0A0A0]">vs last month</span>
                    </div>
                </div>

            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

                <!-- Revenue Chart -->
                <div class="xl:col-span-2 bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Revenue Overview</h3>
                            <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-0.5">Last 6 months</p>
                        </div>
                        <span class="text-xs font-medium text-[#58a6ff] bg-blue-50 dark:bg-[#58a6ff]/10 px-2.5 py-1 rounded-lg">
                            {{ formatCurrency(revenue) }} total
                        </span>
                    </div>
                    <div class="h-64">
                        <Line v-if="monthlyRevenue?.length" :data="revenueChartData" :options="revenueChartOptions" />
                        <div v-else class="h-full flex items-center justify-center text-sm text-gray-400 dark:text-[#A0A0A0]">
                            No revenue data yet
                        </div>
                    </div>
                </div>

                <!-- Category Distribution -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Products by Category</h3>
                            <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-0.5">{{ productsByCategory?.length || 0 }} categories</p>
                        </div>
                    </div>
                    <div class="h-64 flex items-center justify-center">
                        <Doughnut v-if="productsByCategory?.length" :data="categoryChartData" :options="categoryChartOptions" />
                        <div v-else class="text-sm text-gray-400 dark:text-[#A0A0A0]">
                            No products categorized
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

                <!-- Recent Orders -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Recent Orders</h3>
                            <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-0.5">Latest 5 orders</p>
                        </div>
                        <Link href="/Orders" class="text-xs font-medium text-[#58a6ff] hover:underline">
                            View all
                        </Link>
                    </div>
                    <div v-if="recentOrders?.length" class="space-y-2">
                        <div v-for="order in recentOrders" :key="order.id" class="flex items-center justify-between py-2.5 border-b border-gray-50 dark:border-[#D4AF37]/20 last:border-0">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-[#2A2A2A] flex items-center justify-center text-xs font-medium text-gray-400 dark:text-[#A0A0A0] shrink-0">
                                    #{{ String(order.id).slice(-2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ order.customer }}</p>
                                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">{{ order.date }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">{{ formatCurrency(order.total) }}</span>
                                <span class="text-[10px] font-medium uppercase px-2 py-1 rounded-md leading-none" :class="statusColor(order.status)">
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-sm text-gray-400 dark:text-[#A0A0A0]">
                        No orders yet
                    </div>
                </div>

                <!-- Pending Reviews -->
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg p-5 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5]">Pending Reviews</h3>
                            <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-0.5">{{ pendingReviewsCount }} awaiting moderation</p>
                        </div>
                        <Link href="/reviews" class="text-xs font-medium text-[#58a6ff] hover:underline">
                            View all
                        </Link>
                    </div>
                    <div v-if="pendingReviewList?.length" class="space-y-2">
                        <div v-for="review in pendingReviewList" :key="review.id" class="flex items-start justify-between py-2.5 border-b border-gray-50 dark:border-[#D4AF37]/20 last:border-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-[#1A1A1A] dark:text-[#F5F5F5] truncate">{{ review.product }}</p>
                                    <span class="text-xs shrink-0 flex items-center gap-[1px]">
                                        <svg v-for="i in review.rating" :key="'f'+i" class="w-3.5 h-3.5 text-[#d29922]" fill="currentColor" viewBox="0 0 20 20"><path :d="starPath"/></svg>
                                        <svg v-for="i in (5 - Math.min(review.rating, 5))" :key="'e'+i" class="w-3.5 h-3.5 text-gray-300 dark:text-[#30363d]" fill="currentColor" viewBox="0 0 20 20"><path :d="starPath"/></svg>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-[#A0A0A0] mt-0.5">
                                    by {{ review.user }} &middot; {{ review.date }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center text-sm text-gray-400 dark:text-[#A0A0A0]">
                        No pending reviews
                    </div>
                </div>

            </div>

            <!-- Quick Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Categories</p>
                    <p class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-0.5">{{ categoriesCount }}</p>
                </div>
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Subcategories</p>
                    <p class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-0.5">{{ subcategoriesCount }}</p>
                </div>
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Roles</p>
                    <p class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-0.5">{{ rolesCount }}</p>
                </div>
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Coupons</p>
                    <p class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-0.5">{{ couponsCount }}</p>
                </div>
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Stock Total</p>
                    <p class="text-lg font-medium text-[#1A1A1A] dark:text-[#F5F5F5] mt-0.5">{{ totalStock.toLocaleString() }}</p>
                </div>
                <div class="bg-white dark:bg-[#1A1A1A] border border-gray-100 dark:border-[#D4AF37]/20 rounded-lg px-4 py-3 transition-all duration-200">
                    <p class="text-xs text-gray-400 dark:text-[#A0A0A0]">Low Stock</p>
                    <p class="text-lg font-medium mt-0.5" :class="lowStockCount > 0 ? 'text-red-500 dark:text-[#f85149]' : 'text-emerald-500 dark:text-[#3fb950]'">{{ lowStockCount }}</p>
                </div>
            </div>

        </div>

    </main>

</div>
</template>
