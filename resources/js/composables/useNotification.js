import { ref } from "vue"

let nextToastId = 1
const toasts = ref([])

// Legacy backwards-compatible refs
const isVisible = ref(false)
const message = ref("")
const type = ref("success")

export function useNotification() {
  function notify(payload, maybeType = "success") {
    let item = {}

    if (typeof payload === "string") {
      item = {
        id: nextToastId++,
        title: "",
        message: payload,
        type: maybeType || "success",
        duration: 4000,
        action: null,
        createdAt: Date.now(),
      }
    } else if (payload && typeof payload === "object") {
      const extractedMessage =
        payload.message ||
        payload.description ||
        payload.text ||
        payload.error ||
        payload.status ||
        (payload instanceof Error ? payload.message : "") ||
        ""

      item = {
        id: nextToastId++,
        title: payload.title || "",
        message: extractedMessage,
        type: payload.type || maybeType || "success",
        duration: payload.duration !== undefined ? payload.duration : 4000,
        action: payload.action || null, // { label: string, onClick: Function }
        createdAt: Date.now(),
      }
    } else {
      return null
    }

    // Keep max 4 concurrent toasts visible on screen
    if (toasts.value.length >= 4) {
      toasts.value.shift()
    }

    toasts.value.push(item)

    // Sync legacy refs
    isVisible.value = true
    message.value = item.message
    type.value = item.type

    return item.id
  }

  function dismiss(id) {
    const idx = toasts.value.findIndex((t) => t.id === id)
    if (idx !== -1) {
      toasts.value.splice(idx, 1)
    }
    if (toasts.value.length === 0) {
      isVisible.value = false
      message.value = ""
    }
  }

  function close() {
    toasts.value = []
    isVisible.value = false
    message.value = ""
  }

  function success(payload) {
    if (typeof payload === "string") {
      return notify(payload, "success")
    }
    return notify({ ...payload, type: "success" })
  }

  function error(payload) {
    if (typeof payload === "string") {
      return notify(payload, "error")
    }
    return notify({ ...payload, type: "error" })
  }

  function warning(payload) {
    if (typeof payload === "string") {
      return notify(payload, "warning")
    }
    return notify({ ...payload, type: "warning" })
  }

  function info(payload) {
    if (typeof payload === "string") {
      return notify(payload, "info")
    }
    return notify({ ...payload, type: "info" })
  }

  return {
    toasts,
    isVisible,
    message,
    type,
    notify,
    dismiss,
    close,
    success,
    error,
    warning,
    info,
  }
}
