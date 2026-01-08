<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="close"
  >
    <div class="flex min-h-screen items-start justify-center pt-[15vh] p-4">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full z-10 overflow-hidden">
        <!-- Search Input -->
        <div class="flex items-center px-4 border-b border-border-light">
          <svg class="w-5 h-5 text-text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input
            ref="searchInput"
            v-model="searchQuery"
            type="text"
            placeholder="Type a command or search..."
            class="flex-1 px-3 py-4 text-sm text-text-primary placeholder-text-secondary bg-transparent border-0 focus:outline-none focus:ring-0"
            @keydown="handleInputKeydown"
          />
          <kbd class="kbd kbd-sm text-text-secondary">Esc</kbd>
        </div>

        <!-- Results -->
        <div ref="resultsContainer" class="max-h-80 overflow-y-auto">
          <!-- Timer Section -->
          <div v-if="filteredCommands.timer.length > 0" class="p-2">
            <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">Timer</div>
            <button
              v-for="(command, index) in filteredCommands.timer"
              :key="command.id"
              :ref="el => { if (el) commandRefs[getGlobalIndex('timer', index)] = el }"
              @click="executeCommand(command)"
              class="command-item"
              :class="{ 'command-item-active': selectedIndex === getGlobalIndex('timer', index) }"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-gray-100 flex items-center justify-center">
                  <!-- Play icon -->
                  <svg v-if="command.id === 'start-timer'" class="w-4 h-4 text-text-secondary" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                  <!-- Stop icon -->
                  <svg v-else-if="command.id === 'stop-timer'" class="w-4 h-4 text-text-secondary" fill="currentColor" viewBox="0 0 24 24">
                    <rect x="6" y="6" width="12" height="12"/>
                  </svg>
                </div>
                <span class="text-sm text-text-primary">{{ command.label }}</span>
              </div>
              <div v-if="command.shortcut" class="flex items-center gap-1">
                <kbd v-for="key in command.shortcut" :key="key" class="kbd kbd-sm">{{ key }}</kbd>
              </div>
            </button>
          </div>

          <!-- Navigation Section -->
          <div v-if="filteredCommands.navigation.length > 0" class="p-2">
            <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">Navigation</div>
            <button
              v-for="(command, index) in filteredCommands.navigation"
              :key="command.id"
              :ref="el => { if (el) commandRefs[getGlobalIndex('navigation', index)] = el }"
              @click="executeCommand(command)"
              class="command-item"
              :class="{ 'command-item-active': selectedIndex === getGlobalIndex('navigation', index) }"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-gray-100 flex items-center justify-center">
                  <!-- Home icon -->
                  <svg v-if="command.id === 'nav-home'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                  </svg>
                  <!-- Time Tracking / Clock icon -->
                  <svg v-else-if="command.id === 'nav-time-tracking'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke-width="2"/>
                    <line x1="12" y1="12" x2="12" y2="8" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="12" x2="15" y2="15" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  <!-- Analytics / Chart icon -->
                  <svg v-else-if="command.id === 'nav-analytics'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  <!-- Reports / Document icon -->
                  <svg v-else-if="command.id === 'nav-reports'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <!-- Projects / Folder icon -->
                  <svg v-else-if="command.id === 'nav-projects'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                  </svg>
                  <!-- Clients / Users icon -->
                  <svg v-else-if="command.id === 'nav-clients'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                  <!-- Invoices / Document icon -->
                  <svg v-else-if="command.id === 'nav-invoices'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <span class="text-sm text-text-primary">{{ command.label }}</span>
              </div>
              <div v-if="command.shortcut" class="flex items-center gap-1">
                <kbd v-for="key in command.shortcut" :key="key" class="kbd kbd-sm">{{ key }}</kbd>
              </div>
            </button>
          </div>

          <!-- Actions Section -->
          <div v-if="filteredCommands.actions.length > 0" class="p-2">
            <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">Actions</div>
            <button
              v-for="(command, index) in filteredCommands.actions"
              :key="command.id"
              :ref="el => { if (el) commandRefs[getGlobalIndex('actions', index)] = el }"
              @click="executeCommand(command)"
              class="command-item"
              :class="{ 'command-item-active': selectedIndex === getGlobalIndex('actions', index) }"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-gray-100 flex items-center justify-center">
                  <!-- Keyboard shortcuts icon -->
                  <svg v-if="command.id === 'show-shortcuts'" class="w-4 h-4 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                  </svg>
                </div>
                <span class="text-sm text-text-primary">{{ command.label }}</span>
              </div>
              <div v-if="command.shortcut" class="flex items-center gap-1">
                <kbd v-for="key in command.shortcut" :key="key" class="kbd kbd-sm">{{ key }}</kbd>
              </div>
            </button>
          </div>

          <!-- No Results -->
          <div v-if="totalResults === 0" class="p-8 text-center">
            <svg class="w-12 h-12 text-text-secondary mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-text-secondary">No commands found</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-2 border-t border-border-light bg-gray-50 flex items-center justify-between text-xs text-text-secondary">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1">
              <kbd class="kbd kbd-sm">↑</kbd>
              <kbd class="kbd kbd-sm">↓</kbd>
              to navigate
            </span>
            <span class="flex items-center gap-1">
              <kbd class="kbd kbd-sm">↵</kbd>
              to select
            </span>
          </div>
          <span class="flex items-center gap-1">
            <kbd class="kbd kbd-sm">Esc</kbd>
            to close
          </span>
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
  hasActiveTimer: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'execute'])

const searchInput = ref(null)
const resultsContainer = ref(null)
const searchQuery = ref('')
const selectedIndex = ref(-1) // -1 means nothing selected
const commandRefs = ref({})

// All available commands
const allCommands = computed(() => ({
  timer: [
    props.hasActiveTimer
      ? { id: 'stop-timer', label: 'Stop timer', shortcut: ['S'], action: 'toggle-timer' }
      : { id: 'start-timer', label: 'Start timer', shortcut: ['S'], action: 'toggle-timer' }
  ],
  navigation: [
    { id: 'nav-home', label: 'Go to Home', shortcut: ['G', 'H'], action: 'nav-home' },
    { id: 'nav-time-tracking', label: 'Go to Time Tracking', shortcut: ['G', 'T'], action: 'nav-time-tracking' },
    { id: 'nav-analytics', label: 'Go to Analytics', shortcut: ['G', 'A'], action: 'nav-analytics' },
    { id: 'nav-reports', label: 'Go to Reports', shortcut: ['G', 'R'], action: 'nav-reports' },
    { id: 'nav-projects', label: 'Go to Projects', shortcut: ['G', 'P'], action: 'nav-projects' },
    { id: 'nav-clients', label: 'Go to Clients', shortcut: ['G', 'C'], action: 'nav-clients' },
    { id: 'nav-invoices', label: 'Go to Invoices', shortcut: ['G', 'I'], action: 'nav-invoices' },
  ],
  actions: [
    { id: 'show-shortcuts', label: 'View keyboard shortcuts', shortcut: ['?'], action: 'show-shortcuts' },
  ]
}))

// Filter commands based on search
const filteredCommands = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()

  if (!query) {
    return allCommands.value
  }

  const filterSection = (commands) => {
    return commands.filter(cmd =>
      cmd.label.toLowerCase().includes(query) ||
      cmd.id.toLowerCase().includes(query)
    )
  }

  return {
    timer: filterSection(allCommands.value.timer),
    navigation: filterSection(allCommands.value.navigation),
    actions: filterSection(allCommands.value.actions),
  }
})

// Total number of results
const totalResults = computed(() => {
  return filteredCommands.value.timer.length +
    filteredCommands.value.navigation.length +
    filteredCommands.value.actions.length
})

// Get flat list of all filtered commands for keyboard navigation
const flatCommands = computed(() => {
  return [
    ...filteredCommands.value.timer,
    ...filteredCommands.value.navigation,
    ...filteredCommands.value.actions,
  ]
})

// Calculate global index for a command in a section
const getGlobalIndex = (section, localIndex) => {
  let offset = 0
  if (section === 'navigation') {
    offset = filteredCommands.value.timer.length
  } else if (section === 'actions') {
    offset = filteredCommands.value.timer.length + filteredCommands.value.navigation.length
  }
  return offset + localIndex
}

// Reset selection when search changes - select first result if searching
watch(searchQuery, (query) => {
  selectedIndex.value = query.trim() ? 0 : -1
})

// Scroll selected item into view when selection changes
watch(selectedIndex, (newIndex) => {
  if (newIndex >= 0) {
    nextTick(() => {
      const element = commandRefs.value[newIndex]
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
      }
    })
  }
})

// Focus input when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    searchQuery.value = ''
    selectedIndex.value = -1 // Nothing selected initially
    commandRefs.value = {} // Clear refs
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
})

const close = () => {
  emit('close')
}

const executeCommand = (command) => {
  emit('execute', command.action)
  close()
}

const handleInputKeydown = (e) => {
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    // From -1 (nothing selected) go to 0 (first item)
    if (selectedIndex.value < flatCommands.value.length - 1) {
      selectedIndex.value++
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    // Allow going back to -1 (nothing selected)
    if (selectedIndex.value > -1) {
      selectedIndex.value--
    }
  } else if (e.key === 'Enter') {
    e.preventDefault()
    // Only execute if something is selected
    if (selectedIndex.value >= 0) {
      const command = flatCommands.value[selectedIndex.value]
      if (command) {
        executeCommand(command)
      }
    }
  }
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
  padding: 0 0.25rem;
}

.command-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: background-color 0.1s;
}

.command-item:hover,
.command-item-active {
  background-color: #f3f4f6;
}

.command-item-active {
  background-color: #f3f4f6;
}
</style>
