<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="close"
  >
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-lg shadow-xl max-w-xl w-full p-6 z-10">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-text-primary">Keyboard shortcuts</h3>
          <button
            @click="close"
            class="text-text-secondary hover:text-text-primary"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="space-y-6">
          <!-- Global Shortcuts -->
          <div>
            <h4 class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-3">Global</h4>
            <div class="space-y-2">
              <div v-for="shortcut in shortcuts.global" :key="shortcut.action" class="flex items-center justify-between">
                <span class="text-sm text-text-primary">{{ shortcut.description }}</span>
                <div class="flex items-center gap-1">
                  <template v-if="shortcut.isMeta">
                    <kbd class="kbd">{{ isMac ? '⌘' : 'Ctrl' }}</kbd>
                    <kbd class="kbd">K</kbd>
                  </template>
                  <template v-else>
                    <kbd v-for="key in shortcut.keys" :key="key" class="kbd">{{ key }}</kbd>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Shortcuts -->
          <div>
            <h4 class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-3">Navigation</h4>
            <div class="space-y-2">
              <div v-for="shortcut in shortcuts.navigation" :key="shortcut.action" class="flex items-center justify-between">
                <span class="text-sm text-text-primary">{{ shortcut.description }}</span>
                <div class="flex items-center gap-1">
                  <kbd v-for="key in shortcut.keys" :key="key" class="kbd">{{ key }}</kbd>
                </div>
              </div>
            </div>
          </div>

          <!-- Time Tracking Shortcuts -->
          <div>
            <h4 class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-3">Time Tracking</h4>
            <div class="space-y-2">
              <div v-for="shortcut in shortcuts.timeTracking" :key="shortcut.action" class="flex items-center justify-between">
                <span class="text-sm text-text-primary">{{ shortcut.description }}</span>
                <div class="flex items-center gap-1">
                  <kbd v-for="key in shortcut.keys" :key="key" class="kbd">{{ key }}</kbd>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer hint -->
        <div class="mt-6 pt-4 border-t border-border-light">
          <p class="text-xs text-text-secondary text-center">
            Press <kbd class="kbd kbd-sm">{{ isMac ? '⌘' : 'Ctrl' }}</kbd> <kbd class="kbd kbd-sm">K</kbd> to open command palette
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  shortcuts: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

// Detect if user is on Mac
const isMac = ref(false)
onMounted(() => {
  isMac.value = navigator.platform.toUpperCase().indexOf('MAC') >= 0
})

const close = () => {
  emit('close')
}
</script>

<style scoped>
.kbd {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.5rem;
  height: 1.5rem;
  padding: 0 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  font-family: inherit;
  color: #374151;
  background: linear-gradient(180deg, #ffffff, #f3f4f6);
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.kbd-sm {
  min-width: 1.25rem;
  height: 1.25rem;
  font-size: 0.625rem;
}
</style>
