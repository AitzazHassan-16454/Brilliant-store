import { ref } from "vue"

const isVisible = ref(false)
const message = ref("")
const type = ref("success")

let timeout = null

export function useNotification() {
  function notify(text, toastType = "success") {
    message.value = text
    type.value = toastType
    isVisible.value = true

    if (timeout) clearTimeout(timeout)
    timeout = setTimeout(() => {
      isVisible.value = false
    }, 3000)
  }

  function close() {
    isVisible.value = false
    if (timeout) clearTimeout(timeout)
  }

  function success(text) {
    notify(text, "success")
  }

  function error(text) {
    notify(text, "error")
  }

  return { isVisible, message, type, notify, success, error, close }
}
