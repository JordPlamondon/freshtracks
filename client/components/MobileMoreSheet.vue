<template>
  <!-- Backdrop -->
  <Transition name="fade">
    <div
      v-if="isOpen"
      @click="emit('close')"
      class="md:hidden fixed inset-0 bg-black/50 z-50"
    />
  </Transition>

  <!-- Bottom Sheet -->
  <Transition name="slide-up">
    <div
      v-if="isOpen"
      class="md:hidden fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl z-50 max-h-[80vh] overflow-y-auto"
    >
      <!-- Handle -->
      <div class="flex justify-center pt-3 pb-2">
        <div class="w-10 h-1 bg-gray-300 rounded-full" />
      </div>

      <!-- Menu Items -->
      <div class="pt-2 pb-2">
        <!-- Reports -->
        <NuxtLink
          to="/reports"
          @click="emit('close')"
          class="flex items-center space-x-3 px-6 py-4 hover:bg-gray-50 transition-colors"
          :class="isActive('/reports') ? 'bg-green-50' : ''"
        >
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center"
            :class="isActive('/reports') ? 'bg-[#56c97b] text-white' : 'bg-gray-100 text-gray-600'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-text-primary">Reports</p>
            <p class="text-xs text-text-secondary">Export and analyze data</p>
          </div>
        </NuxtLink>

        <!-- Projects -->
        <NuxtLink
          to="/projects"
          @click="emit('close')"
          class="flex items-center space-x-3 px-6 py-4 hover:bg-gray-50 transition-colors"
          :class="isActive('/projects') ? 'bg-green-50' : ''"
        >
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center"
            :class="isActive('/projects') ? 'bg-[#56c97b] text-white' : 'bg-gray-100 text-gray-600'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-text-primary">Projects</p>
            <p class="text-xs text-text-secondary">Manage your projects</p>
          </div>
        </NuxtLink>

        <!-- Clients -->
        <NuxtLink
          to="/clients"
          @click="emit('close')"
          class="flex items-center space-x-3 px-6 py-4 hover:bg-gray-50 transition-colors"
          :class="isActive('/clients') ? 'bg-green-50' : ''"
        >
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center"
            :class="isActive('/clients') ? 'bg-[#56c97b] text-white' : 'bg-gray-100 text-gray-600'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-text-primary">Clients</p>
            <p class="text-xs text-text-secondary">Manage your clients</p>
          </div>
        </NuxtLink>

        <!-- Invoices -->
        <NuxtLink
          to="/invoices"
          @click="emit('close')"
          class="flex items-center space-x-3 px-6 py-4 hover:bg-gray-50 transition-colors"
          :class="isActive('/invoices') ? 'bg-green-50' : ''"
        >
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center"
            :class="isActive('/invoices') ? 'bg-[#56c97b] text-white' : 'bg-gray-100 text-gray-600'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-text-primary">Invoices</p>
            <p class="text-xs text-text-secondary">Generate and manage invoices</p>
          </div>
        </NuxtLink>

        <!-- Settings -->
        <NuxtLink
          to="/settings"
          @click="emit('close')"
          class="flex items-center space-x-3 px-6 py-4 hover:bg-gray-50 transition-colors"
          :class="isActive('/settings') ? 'bg-green-50' : ''"
        >
          <div
            class="w-10 h-10 rounded-lg flex items-center justify-center"
            :class="isActive('/settings') ? 'bg-[#56c97b] text-white' : 'bg-gray-100 text-gray-600'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-text-primary">Settings</p>
            <p class="text-xs text-text-secondary">Configure your preferences</p>
          </div>
        </NuxtLink>
      </div>

      <!-- Actions Section -->
      <div class="py-2 border-t border-gray-200">
        <!-- Logout -->
        <button
          @click="handleLogout"
          class="w-full flex items-center space-x-3 px-6 py-4 hover:bg-red-50 transition-colors"
        >
          <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </div>
          <div class="flex-1 text-left">
            <p class="text-sm font-medium text-red-600">Logout</p>
            <p class="text-xs text-text-secondary">Sign out of your account</p>
          </div>
        </button>
      </div>

      <!-- Safe area spacing for bottom nav -->
      <div class="h-4" />
    </div>
  </Transition>
</template>

<script setup>
const route = useRoute()
const router = useRouter()
const { $api } = useNuxtApp()
const emit = defineEmits(['close'])

defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const isActive = (path) => {
  return route.path.startsWith(path)
}

// Logout
const handleLogout = async () => {
  try {
    await $api('/logout', { method: 'POST' })

    if (import.meta.client) {
      const authToken = useCookie('auth_token')
      authToken.value = null
    }

    emit('close')
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}
</script>

<style scoped>
/* Fade transition for backdrop */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide up transition for sheet */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>
