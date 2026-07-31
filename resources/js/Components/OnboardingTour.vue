<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const open = ref(Boolean(page.props.isFirstLogin))
const step = ref(0)
const saving = ref(false)

const steps = [
    {
        icon: 'M12 0l2.5 9.5L24 12l-9.5 2.5L12 24l-2.5-9.5L0 12l9.5-2.5z',
        title: 'Welcome to Brilliant',
        text: 'Your premium destination for jewelry, watches, fashion and electronics. Let us show you around.',
    },
    {
        icon: 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        title: 'Browse the collection',
        text: 'Explore products by category, search by style or intent, and filter to find exactly what you love.',
    },
    {
        icon: 'M9 20a1 1 0 100-2 1 1 0 000 2zM19 20a1 1 0 100-2 1 1 0 000 2zM2 2h3l2.4 12.1a2 2 0 002 1.6h9.6a2 2 0 002-1.6L23 6H6',
        title: 'Shop with ease',
        text: 'Add items to your cart, save favorites to your wishlist, or place a custom order for something unique.',
    },
    {
        icon: 'M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z',
        title: 'Get AI support',
        text: 'Stuck? Our AI shopping assistant can answer questions, suggest products, and help with your orders anytime.',
    },
    {
        icon: 'M20 6L9 17l-5-5',
        title: 'You are all set',
        text: 'Happy shopping! You can revisit this tour anytime from your account menu.',
    },
]

const isLast = computed(() => step.value === steps.length - 1)

const close = () => {
    open.value = false
}

const complete = async () => {
    if (saving.value) return

    saving.value = true

    try {
        const res = await fetch('/welcome/seen', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': page.props.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        })

        if (!res.ok) throw new Error('Failed')
    } catch (e) {
        // Ignore network errors; the tour can still close.
    } finally {
        saving.value = false
        close()
    }
}

const next = () => {
    if (isLast.value) {
        complete()
        return
    }
    step.value += 1
}

const skip = () => {
    complete()
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 tour-root"
                role="dialog"
                aria-modal="true"
                aria-label="Welcome tutorial"
            >
                <div class="absolute inset-0 bg-[#0A0A0A]/70 backdrop-blur-sm" @click="skip"></div>

                <div class="relative w-full max-w-md rounded-2xl overflow-hidden shadow-2xl shadow-black/40 tour-card">
                    <div class="absolute inset-0 rounded-2xl" style="background:linear-gradient(135deg,rgba(212,175,55,0.25),rgba(184,150,15,0.08));"></div>

                    <div class="relative p-8 bg-[#1a1f36] text-[#F5F5F5]">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center tour-icon" style="background:linear-gradient(135deg,#D4AF37,#B8960F);">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path :d="steps[step].icon"/>
                                </svg>
                            </div>

                            <h2 class="mt-5 text-xl font-extrabold tracking-tight text-white">{{ steps[step].title }}</h2>
                            <p class="mt-2 text-sm leading-relaxed text-[#C9C9D4]">{{ steps[step].text }}</p>

                            <div class="mt-5 flex items-center gap-2">
                                <span
                                    v-for="(s, i) in steps"
                                    :key="i"
                                    class="h-1.5 rounded-full transition-all duration-300"
                                    :class="i === step ? 'w-6 bg-[#D4AF37]' : 'w-1.5 bg-[#3A4157]'"
                                ></span>
                            </div>
                        </div>

                        <div class="mt-7 flex items-center justify-between">
                            <button
                                type="button"
                                class="px-3 py-2 rounded-lg text-sm font-semibold text-[#9B9B9B] hover:text-white transition cursor-pointer"
                                :disabled="saving"
                                @click="skip"
                            >
                                Skip
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    v-if="!isLast"
                                    type="button"
                                    class="px-3 py-2 rounded-lg text-sm font-semibold text-[#C9C9D4] hover:bg-white/5 transition cursor-pointer"
                                    :disabled="step === 0 || saving"
                                    @click="step -= 1"
                                >
                                    Back
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold text-[#1A1A1A] transition hover:opacity-90 cursor-pointer"
                                    style="background:linear-gradient(135deg,#D4AF37,#B8960F);"
                                    :disabled="saving"
                                    @click="next"
                                >
                                    <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                    {{ isLast ? 'Get Started' : 'Next' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
