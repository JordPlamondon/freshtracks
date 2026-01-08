<template>
  <div class="flex h-screen bg-sidebar-bg">
    <!-- Keyboard Shortcuts Modal -->
    <KeyboardShortcutsModal
      :isOpen="showShortcutsModal"
      :shortcuts="shortcuts"
      @close="showShortcutsModal = false"
    />

    <!-- Command Palette -->
    <CommandPalette
      :isOpen="showCommandPalette"
      :hasActiveTimer="!!activeTimer"
      @close="showCommandPalette = false"
      @execute="handleCommandExecute"
    />

    <!-- Quick Timer Modal (for S shortcut when no timer running) -->
    <QuickTimerModal
      :isOpen="showQuickTimerModal"
      @close="showQuickTimerModal = false"
      @started="handleQuickTimerStarted"
    />

    <!-- Mobile More Sheet (shows only on mobile) -->
    <MobileMoreSheet
      :isOpen="showMobileMoreSheet"
      @close="showMobileMoreSheet = false"
    />

    <!-- Sidebar (hidden on mobile) -->
    <aside class="hidden md:flex fixed left-0 top-0 h-full w-[18rem] bg-sidebar-bg flex-col">
      <!-- Logo Section -->
      <div class="pt-8 px-6 pb-6">
        <NuxtLink to="/" class="flex flex-row items-center gap-2">
          <img src="/freshtracks-logo.svg" alt="FreshTracks" class="h-6 w-6 flex-shrink-0" />
          <span class="text-xl font-bold text-text-primary whitespace-nowrap">FreshTracks</span>
        </NuxtLink>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4">
        <!-- Main Navigation -->
        <div class="mb-6">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2 px-3">
            Main
          </div>
          <NuxtLink
            to="/"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors relative"
            :class="$route.path === '/' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Home
          </NuxtLink>
          <NuxtLink
            to="/time-tracking"
            class="flex items-center justify-between px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/time-tracking' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <div class="flex items-center">
              <svg
                width="20"
                height="20"
                class="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <!-- Clock face -->
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <!-- Static hands when no timer -->
                <template v-if="!activeTimer">
                  <line x1="12" y1="12" x2="12" y2="8" stroke-width="2" stroke-linecap="round"/>
                  <line x1="12" y1="12" x2="15" y2="15" stroke-width="2" stroke-linecap="round"/>
                </template>
                <!-- Animated hands when timer active -->
                <template v-else>
                  <!-- Minute hand (slower) -->
                  <line
                    x1="12" y1="12" x2="12" y2="7"
                    stroke-width="2"
                    stroke-linecap="round"
                    :style="{ transform: `rotate(${minuteHandRotation}deg)`, transformOrigin: '12px 12px' }"
                  />
                  <!-- Second hand (faster) -->
                  <line
                    x1="12" y1="12" x2="12" y2="5"
                    stroke-width="2"
                    stroke-linecap="round"
                    :style="{ transform: `rotate(${secondHandRotation}deg)`, transformOrigin: '12px 12px' }"
                  />
                </template>
              </svg>
              Time Tracking
            </div>
            <span
              v-if="activeTimer"
              class="text-xs font-mono font-semibold text-[#56c97b] bg-green-50 py-[0.2rem] px-2 rounded absolute right-3"
            >
              {{ formattedDuration }}
            </span>
          </NuxtLink>
          <NuxtLink
            to="/analytics"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/analytics' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Analytics
          </NuxtLink>
          <NuxtLink
            to="/reports"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/reports' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
          </NuxtLink>
        </div>

        <!-- Manage Section -->
        <div>
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2 px-3">
            Manage
          </div>
          <NuxtLink
            to="/projects"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors relative"
            :class="$route.path === '/projects' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            Projects
          </NuxtLink>
          <NuxtLink
            to="/clients"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/clients' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Clients
          </NuxtLink>
          <NuxtLink
            to="/invoices"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/invoices' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Invoices
          </NuxtLink>
          <NuxtLink
            to="/entries"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors mt-1 relative"
            :class="$route.path === '/entries' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            All Entries
          </NuxtLink>
        </div>

        <!-- Settings Section -->
        <div class="mt-6">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2 px-3">
            Preferences
          </div>
          <NuxtLink
            to="/settings"
            class="flex items-center px-3 py-2 rounded-md text-sm font-normal transition-colors relative"
            :class="$route.path === '/settings' ? 'active-nav-link text-text-primary' : 'text-text-primary hover:bg-gray-100'"
          >
            <svg width="20" height="20" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
          </NuxtLink>
        </div>
      </nav>

      <!-- User Profile Section -->
      <div class="px-4 pb-6 pt-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full profile-avatar flex items-center justify-center text-text-primary text-sm font-medium">
              <span class="relative z-[1]">{{ userInitial }}</span>
            </div>
            <div class="ml-3">
              <div class="text-sm font-medium text-text-primary">{{ userEmail }}</div>
            </div>
          </div>
          <button
            @click="logout"
            class="text-text-secondary hover:text-text-primary transition-colors"
            title="Logout"
          >
            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="md:ml-[17rem] flex-1 overflow-y-auto px-4 md:px-6 pt-4 md:pt-6 pb-20 md:pb-6">
      <div class="min-h-full bg-content-bg rounded-lg p-4 md:p-6 shadow-[0_1px_4px_0_rgba(0,0,0,0.11)]">
        <slot />
      </div>
    </main>

    <!-- Bottom Navigation (mobile only) -->
    <BottomNav
      :showMoreSheet="showMobileMoreSheet"
      @open-more-sheet="showMobileMoreSheet = true"
    />

    <!-- Keyboard Shortcuts Floating Island (desktop only) -->
    <div class="hidden md:flex fixed bottom-4 left-[18rem] right-0 justify-center z-40 pointer-events-none">
      <div class="bg-white/70 backdrop-blur-md border border-gray-200/50 rounded-md px-5 py-2 flex items-center gap-5 text-xs text-text-secondary shadow-sm pointer-events-auto">
        <button
          @click="showShortcutsModal = true"
          class="flex items-center gap-1.5 hover:text-text-primary transition-colors"
        >
          <kbd class="kbd-hint">?</kbd>
          <span>Shortcuts</span>
        </button>
        <button
          @click="showCommandPalette = true"
          class="flex items-center gap-1.5 hover:text-text-primary transition-colors"
        >
          <kbd class="kbd-hint">{{ isMac ? '⌘' : 'Ctrl' }}</kbd>
          <kbd class="kbd-hint">K</kbd>
          <span>Command</span>
        </button>
        <button
          @click="handleToggleTimer"
          class="flex items-center gap-1.5 hover:text-text-primary transition-colors"
        >
          <kbd class="kbd-hint">S</kbd>
          <span>{{ activeTimer ? 'Stop' : 'Start' }} timer</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const api = useApi()
const { currentTime } = useCurrentTime()
const { initEcho, disconnect: disconnectEcho } = useEcho()
const {
  showShortcutsModal,
  showCommandPalette,
  shortcuts,
  initShortcuts,
  destroyShortcuts,
  executeAction
} = useKeyboardShortcuts()

const userEmail = ref('demo@freshtracks.test')
const userInitial = computed(() => userEmail.value.charAt(0).toUpperCase())
const isMac = ref(false)
const showQuickTimerModal = ref(false)
const showMobileMoreSheet = ref(false)
const activeTimer = ref(null)

const formattedDuration = computed(() => {
  if (!activeTimer.value) return ''

  const sessionStart = new Date(activeTimer.value.resumed_at || activeTimer.value.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
  const accumulatedSeconds = Math.floor((activeTimer.value.duration_minutes || 0) * 60)
  const totalSeconds = accumulatedSeconds + currentSessionSeconds

  const hours = Math.floor(totalSeconds / 3600)
  const mins = Math.floor((totalSeconds % 3600) / 60)
  const secs = Math.floor(totalSeconds % 60)

  return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
})

const timerTotalSeconds = computed(() => {
  if (!activeTimer.value) return 0
  const sessionStart = new Date(activeTimer.value.resumed_at || activeTimer.value.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
  const accumulatedSeconds = Math.floor((activeTimer.value.duration_minutes || 0) * 60)
  return accumulatedSeconds + currentSessionSeconds
})

const secondHandRotation = computed(() => {
  const seconds = timerTotalSeconds.value % 60
  return seconds * 6
})

const minuteHandRotation = computed(() => {
  const totalSeconds = timerTotalSeconds.value
  const minutes = Math.floor(totalSeconds / 60) % 60
  const seconds = totalSeconds % 60
  return (minutes * 6) + (seconds * 0.1)
})

const fetchActiveTimer = async () => {
  try {
    const data = await api.api('/active-timer')
    activeTimer.value = data && data.id ? data : null
  } catch (error) {
    activeTimer.value = null
  }
}

const handleTimerStarted = () => {
  fetchActiveTimer()
}

const handleTimerStopped = () => {
  fetchActiveTimer()
}

watchEffect(() => {
  if (import.meta.client) {
    if (activeTimer.value && formattedDuration.value) {
      document.title = `${formattedDuration.value} - FreshTracks`
    } else {
      document.title = 'FreshTracks - Time Tracking'
    }
  }
})

const handleCommandExecute = (action) => {
  if (action === 'show-shortcuts') {
    showShortcutsModal.value = true
  } else {
    executeAction(action)
  }
}

const handleToggleTimer = async () => {
  if (activeTimer.value) {
    try {
      await api.api(`/time-entries/${activeTimer.value.id}/stop`, { method: 'POST' })
      window.dispatchEvent(new Event('timer-stopped'))
    } catch (error) {
      console.error('Failed to stop timer:', error)
    }
  } else {
    showQuickTimerModal.value = true
  }
}

const handleQuickTimerStarted = () => {
  fetchActiveTimer()
}

const handleKeyboardShortcut = (e) => {
  if (e.detail.action === 'toggle-timer') {
    handleToggleTimer()
  }
}

const handleOpenCommandPalette = () => {
  showCommandPalette.value = true
}

const handleShowKeyboardShortcuts = () => {
  showShortcutsModal.value = true
}

onMounted(() => {
  fetchActiveTimer()
  initShortcuts()
  isMac.value = navigator.platform.toUpperCase().indexOf('MAC') >= 0

  window.addEventListener('timer-started', handleTimerStarted)
  window.addEventListener('timer-stopped', handleTimerStopped)
  window.addEventListener('keyboard-shortcut', handleKeyboardShortcut)
  window.addEventListener('open-command-palette', handleOpenCommandPalette)
  window.addEventListener('show-keyboard-shortcuts', handleShowKeyboardShortcuts)

  const echo = initEcho()
  if (echo) {
    const userId = 1
    echo.channel(`timers.${userId}`)
      .listen('.timer.started', (data) => {
        activeTimer.value = data.entry
        window.dispatchEvent(new CustomEvent('ws-timer-started', { detail: data.entry }))
      })
      .listen('.timer.stopped', (data) => {
        activeTimer.value = null
        window.dispatchEvent(new CustomEvent('ws-timer-stopped', { detail: data.entry }))
      })
      .listen('.timer.deleted', (data) => {
        if (activeTimer.value && activeTimer.value.id === data.entry_id) {
          activeTimer.value = null
        }
        window.dispatchEvent(new CustomEvent('ws-timer-deleted', { detail: data.entry_id }))
      })
  }
})

onUnmounted(() => {
  destroyShortcuts()
  disconnectEcho()
  window.removeEventListener('timer-started', handleTimerStarted)
  window.removeEventListener('timer-stopped', handleTimerStopped)
  window.removeEventListener('keyboard-shortcut', handleKeyboardShortcut)
  window.removeEventListener('open-command-palette', handleOpenCommandPalette)
  window.removeEventListener('show-keyboard-shortcuts', handleShowKeyboardShortcuts)
})

const logout = async () => {
  try {
    await api.api('/logout', { method: 'POST' })
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    api.token.value = null
    navigateTo('/login')
  }
}
</script>

<style scoped>
.active-nav-link {
  position: relative;
  background: linear-gradient(180deg, #ffffff, #f8f8f8);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
}

.active-nav-link::before {
  background: linear-gradient(180deg, #ffffff, #e1e1e1);
  border-radius: inherit;
  content: "";
  inset: 0;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  padding: 1px;
  pointer-events: none;
  position: absolute;
}

.profile-avatar {
  position: relative;
  background: linear-gradient(180deg, #e7ffa2, #cefb47);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.11);
}

.profile-avatar::before {
  background: linear-gradient(180deg, #f6fce3, #c2ec42);
  border-radius: inherit;
  content: "";
  inset: 0;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  padding: 1px;
  pointer-events: none;
  position: absolute;
}

.kbd-hint {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.25rem;
  height: 1.25rem;
  padding: 0 0.25rem;
  font-size: 0.625rem;
  font-weight: 500;
  font-family: inherit;
  color: #6b7280;
  background: linear-gradient(180deg, #ffffff, #f3f4f6);
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

</style>
