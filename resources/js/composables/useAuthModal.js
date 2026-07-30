import { ref } from "vue"

const isOpen = ref(false)
const defaultTab = ref("login")

export function useAuthModal() {
  function openLogin() {
    defaultTab.value = "login"
    isOpen.value = true
  }

  function openRegister() {
    defaultTab.value = "register"
    isOpen.value = true
  }

  function close() {
    isOpen.value = false
  }

  return { isOpen, defaultTab, openLogin, openRegister, close }
}
