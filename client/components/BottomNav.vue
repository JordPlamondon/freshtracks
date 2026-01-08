<template>
  <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40">
    <!-- Bottom Nav Container -->
    <div class="relative flex items-center justify-around h-16 px-2">
      <!-- Home -->
      <NuxtLink
        to="/"
        class="flex flex-col items-center justify-center flex-1 py-2 transition-colors"
        :class="isActive('/') ? 'text-[#56c97b]' : 'text-gray-600'"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-[10px] font-normal">Home</span>
      </NuxtLink>

      <!-- Time Tracking -->
      <NuxtLink
        to="/time-tracking"
        class="flex flex-col items-center justify-center flex-1 py-2 transition-colors"
        :class="isActive('/time-tracking') ? 'text-[#56c97b]' : 'text-gray-600'"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-[10px] font-normal">Tracking</span>
      </NuxtLink>

      <!-- Timer Button (Center, Elevated) -->
      <div class="flex-1 flex items-center justify-center">
        <button
          @click="handleTimerClick"
          class="relative -mt-6 w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all active:scale-95"
          :class="activeTimer ? 'bg-[#56c97b]' : 'bg-gradient-to-br from-[#cefb47] to-[#b8e639]'"
        >
          <!-- Running Timer - Show Stop Icon -->
          <svg v-if="activeTimer" class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
            <rect x="6" y="6" width="12" height="12" rx="1" />
          </svg>
          <!-- No Timer - Show Play Icon -->
          <svg v-else class="w-7 h-7 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z" />
          </svg>

          <!-- Timer Duration Badge -->
          <div
            v-if="activeTimer && formattedDuration"
            class="absolute -top-2 left-1/2 -translate-x-1/2 bg-white text-[#56c97b] text-[9px] font-semibold px-1.5 py-0.5 rounded-full shadow-sm border border-gray-200"
          >
            {{ formattedDuration }}
          </div>
        </button>
      </div>

      <!-- Analytics -->
      <NuxtLink
        to="/analytics"
        class="flex flex-col items-center justify-center flex-1 py-2 transition-colors"
        :class="isActive('/analytics') ? 'text-[#56c97b]' : 'text-gray-600'"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span class="text-[10px] font-normal">Analytics</span>
      </NuxtLink>

      <!-- More -->
      <button
        @click="emit('open-more-sheet')"
        class="flex flex-col items-center justify-center flex-1 py-2 transition-colors"
        :class="showMoreSheet ? 'text-[#56c97b]' : 'text-gray-600'"
      >
        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <span class="text-[10px] font-normal">More</span>
      </button>
    </div>

    <!-- QuickTimerModal (Start Timer) -->
    <QuickTimerModal
      :isOpen="showQuickTimerModal"
      @close="showQuickTimerModal = false"
      @timer-started="handleTimerStarted"
    />
  </div>
</template>

<script setup>
const route = useRoute()
const api = useApi()
const emit = defineEmits(['open-more-sheet'])

defineProps({
  showMoreSheet: {
    type: Boolean,
    default: false
  }
})

const activeTimer = ref(null)
const showQuickTimerModal = ref(false)
const { currentTime } = useCurrentTime()

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}

const formattedDuration = computed(() => {
  if (!activeTimer.value) return ''

  const sessionStart = new Date(activeTimer.value.resumed_at || activeTimer.value.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
  const accumulatedSeconds = Math.floor((activeTimer.value.duration_minutes || 0) * 60)
  const totalSeconds = accumulatedSeconds + currentSessionSeconds

  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60

  if (hours > 0) {
    return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
  }
  return `${minutes}:${String(seconds).padStart(2, '0')}`
})

const fetchActiveTimer = async () => {
  try {
    const data = await api.api('/active-timer')
    activeTimer.value = data && data.id ? data : null
  } catch (error) {
    console.error('Failed to fetch active timer:', error)
    activeTimer.value = null
  }
}

const handleTimerClick = async () => {
  if (activeTimer.value) {
    try {
      await api.api(`/time-entries/${activeTimer.value.id}/stop`, {
        method: 'POST'
      })
      activeTimer.value = null
      if (import.meta.client) {
        window.dispatchEvent(new Event('timer-stopped'))
      }
    } catch (error) {
      console.error('Failed to stop timer:', error)
    }
  } else {
    showQuickTimerModal.value = true
  }
}

const handleTimerStarted = (timer) => {
  activeTimer.value = timer
  showQuickTimerModal.value = false
}

onMounted(() => {
  fetchActiveTimer()

  if (import.meta.client) {
    window.addEventListener('timer-started', fetchActiveTimer)
    window.addEventListener('timer-stopped', fetchActiveTimer)
  }
})

onUnmounted(() => {
  if (import.meta.client) {
    window.removeEventListener('timer-started', fetchActiveTimer)
    window.removeEventListener('timer-stopped', fetchActiveTimer)
  }
})
</script>
