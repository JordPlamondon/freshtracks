<template>
  <div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Settings</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-text-secondary">
      Loading settings...
    </div>

    <template v-else>
      <!-- Settings Card -->
      <div class="bg-white rounded-lg">
        <div class="p-4 pb-[0.35rem] bg-[#f1f0ee] rounded-t-lg">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
            Display Preferences
          </div>
        </div>
        <div class="p-2 bg-[#f1f0ee] rounded-b-lg">
          <div class="bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
            <!-- Show Live Revenue Setting -->
            <div class="flex items-center justify-between p-4">
              <div class="flex-1">
                <div class="text-sm font-semibold text-text-primary">
                  Show Live Revenue
                </div>
                <div class="text-xs text-text-secondary mt-1">
                  Display revenue calculations in real-time (menu bar widget and analytics)
                </div>
              </div>
              <div class="ml-4">
                <ToggleSwitch
                  v-model="showLiveRevenue"
                  @update:modelValue="handleToggleChange"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Notification -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div
          v-if="showSaveNotification"
          class="fixed bottom-4 right-4 bg-[#56c97b] text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <span class="font-medium">Settings saved</span>
        </div>
      </Transition>

      <!-- Error Notification -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div
          v-if="showErrorNotification"
          class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span class="font-medium">Failed to save settings</span>
        </div>
      </Transition>
    </template>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const { settings, loading, fetchSettings, updateSettings } = useSettings()

const showSaveNotification = ref(false)
const showErrorNotification = ref(false)

const showLiveRevenue = computed({
  get: () => settings.value.show_live_revenue,
  set: (value) => {
    settings.value.show_live_revenue = value
  }
})

const handleToggleChange = async () => {
  try {
    await updateSettings({ show_live_revenue: showLiveRevenue.value })

    showSaveNotification.value = true
    setTimeout(() => {
      showSaveNotification.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to save settings:', error)

    showErrorNotification.value = true
    setTimeout(() => {
      showErrorNotification.value = false
    }, 3000)
  }
}

onMounted(async () => {
  await fetchSettings()
})
</script>

<style scoped>
/* Settings page styles */
</style>
