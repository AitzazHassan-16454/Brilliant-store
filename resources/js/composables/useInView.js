import { ref, onMounted, onUnmounted } from 'vue'

export function useInView(threshold = 0.1) {
    const target = ref(null)
    const isInView = ref(false)
    let observer = null

    onMounted(() => {
        if (target.value) {
            observer = new IntersectionObserver(
                ([entry]) => {
                    if (entry.isIntersecting) {
                        isInView.value = true
                        observer?.disconnect()
                    }
                },
                { threshold }
            )
            observer.observe(target.value)
        }
    })

    onUnmounted(() => {
        observer?.disconnect()
    })

    return { target, isInView }
}
