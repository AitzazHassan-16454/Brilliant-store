// Shared motion-v animation presets for a polished, luxury feel.
// Import { motionPresets as m } from "@/lib/motion" in any component.

export const EASE_OUT_EXPO = [0.16, 1, 0.3, 1]
export const EASE_OUT_SOFT = [0.22, 1, 0.36, 1]
export const EASE_IN_OUT = [0.45, 0, 0.15, 1]

export const DURATION = {
  fast: 0.3,
  base: 0.5,
  slow: 0.7,
  slower: 0.9,
}

export const SPRING = {
  gentle: { type: "spring", stiffness: 120, damping: 20, mass: 0.8 },
  snappy: { type: "spring", stiffness: 320, damping: 26, mass: 0.6 },
  bouncy: { type: "spring", stiffness: 260, damping: 14 },
}

export const VIEWPORT = {
  once: { once: true, amount: 0.2 },
  onceLight: { once: true, amount: 0.05 },
  onceBottom: { once: true, amount: 0.3 },
}

const ease = EASE_OUT_EXPO

// ————————————————————————————————————————
// Reusable variants
// ————————————————————————————————————————

export const fadeUp = {
  hidden: { opacity: 0, y: 28 },
  visible: { opacity: 1, y: 0, transition: { duration: DURATION.slow, ease } },
}

export const fadeUpSmall = {
  hidden: { opacity: 0, y: 14 },
  visible: { opacity: 1, y: 0, transition: { duration: DURATION.base, ease } },
}

export const fadeDown = {
  hidden: { opacity: 0, y: -24 },
  visible: { opacity: 1, y: 0, transition: { duration: DURATION.base, ease } },
}

export const fadeIn = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { duration: DURATION.base, ease } },
}

export const scaleIn = {
  hidden: { opacity: 0, scale: 0.92 },
  visible: { opacity: 1, scale: 1, transition: { duration: DURATION.base, ease } },
}

export const scaleInSpring = {
  hidden: { opacity: 0, scale: 0.86 },
  visible: { opacity: 1, scale: 1, transition: { ...SPRING.gentle } },
}

export const slideLeft = {
  hidden: { opacity: 0, x: -40 },
  visible: { opacity: 1, x: 0, transition: { duration: DURATION.slow, ease } },
}

export const slideRight = {
  hidden: { opacity: 0, x: 40 },
  visible: { opacity: 1, x: 0, transition: { duration: DURATION.slow, ease } },
}

export const blurIn = {
  hidden: { opacity: 0, filter: "blur(8px)", y: 16 },
  visible: { opacity: 1, filter: "blur(0px)", y: 0, transition: { duration: DURATION.slow, ease } },
}

// ————————————————————————————————————————
// Stagger helpers
// ————————————————————————————————————————

export const staggerContainer = (stagger = 0.08, delayChildren = 0) => ({
  hidden: {},
  visible: { transition: { staggerChildren: stagger, delayChildren } },
})

export const staggerItem = fadeUpSmall

// Build a single-item variant with a custom delay (great for list grids).
export const itemFadeUp = (delay = 0) => ({
  hidden: { opacity: 0, y: 24 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: DURATION.base, ease, delay: Math.min(delay, 0.5) },
  },
})

export const itemScaleIn = (delay = 0) => ({
  hidden: { opacity: 0, scale: 0.9 },
  visible: {
    opacity: 1,
    scale: 1,
    transition: { duration: DURATION.base, ease, delay: Math.min(delay, 0.5) },
  },
})

// Hover / tap gestures that stay consistent across cards & buttons.
export const GESTURE = {
  lift: { y: -6, transition: { ...SPRING.gentle } },
  liftSm: { y: -3, transition: { ...SPRING.snappy } },
  press: { scale: 0.97 },
  pressSm: { scale: 0.98 },
  icon: { x: 4 },
  grow: { scale: 1.03 },
}

// ————————————————————————————————————————
// Presets bundle
// ————————————————————————————————————————

export const motionPresets = {
  easeOutExpo: ease,
  easeOutSoft: EASE_OUT_SOFT,
  spring: SPRING,
  viewport: VIEWPORT,
  duration: DURATION,
  fadeUp,
  fadeUpSmall,
  fadeDown,
  fadeIn,
  scaleIn,
  scaleInSpring,
  slideLeft,
  slideRight,
  blurIn,
  stagger: staggerContainer,
  staggerContainer,
  item: itemFadeUp,
  itemFadeUp,
  itemScale: itemScaleIn,
  itemScaleIn,
  gesture: GESTURE,
}
