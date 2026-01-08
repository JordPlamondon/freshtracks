<template>
  <div class="md:hidden sticky top-0 bg-white border-b border-gray-200 z-30">
    <div class="flex items-center justify-between h-14 px-4">
      <!-- Logo -->
      <NuxtLink to="/" class="flex items-center space-x-2">
        <img src="/freshtracks-logo.svg" alt="FreshTracks" class="h-7 w-7" />
        <span class="text-lg font-semibold text-text-primary">FreshTracks</span>
      </NuxtLink>

      <!-- User Menu Button -->
      <button
        @click="showUserMenu = !showUserMenu"
        class="relative flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#cefb47] to-[#b8e639] flex items-center justify-center">
          <span class="text-sm font-semibold text-gray-800">{{ userInitials }}</span>
        </div>
      </button>
    </div>

    <!-- User Menu Dropdown -->
    <div
      v-if="showUserMenu"
      class="absolute top-14 right-4 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
    >
      <div class="px-4 py-3 border-b border-gray-100">
        <p class="text-sm font-medium text-text-primary">{{ user?.name }}</p>
        <p class="text-xs text-text-secondary">{{ user?.email }}</p>
      </div>

      <button
        @click="handleShowKeyboardShortcuts"
        class="w-full text-left px-4 py-2.5 text-sm text-text-primary hover:bg-gray-50 transition-colors flex items-center space-x-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        <span>Keyboard shortcuts</span>
      </button>

      <button
        @click="handleLogout"
        class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors flex items-center space-x-2 border-t border-gray-100"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Logout</span>
      </button>
    </div>

    <!-- Click outside to close -->
    <div
      v-if="showUserMenu"
      @click="showUserMenu = false"
      class="fixed inset-0 z-20"
    />
  </div>
</template>

<script setup>
const { $api } = useNuxtApp()
const router = useRouter()

const showUserMenu = ref(false)
const user = ref(null)

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  const names = user.value.name.split(' ')
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase()
  }
  return user.value.name[0].toUpperCase()
})

const fetchUser = async () => {
  try {
    const response = await $api('/user')
    user.value = response
  } catch (error) {
    console.error('Failed to fetch user:', error)
  }
}

// Show keyboard shortcuts
const handleShowKeyboardShortcuts = () => {
  showUserMenu.value = false
  if (import.meta.client) {
    window.dispatchEvent(new Event('show-keyboard-shortcuts'))
  }
}

// Logout
const handleLogout = async () => {
  try {
    await $api('/logout', { method: 'POST' })

    if (import.meta.client) {
      const authToken = useCookie('auth_token')
      authToken.value = null
    }

    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

// Mount
onMounted(() => {
  fetchUser()
})
</script>
