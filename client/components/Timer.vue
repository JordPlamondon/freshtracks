<template>
  <div class="bg-white relative z-10">
    <!-- Timer Input for Starting NEW Timers Only -->
    <div class="relative mb-4">
      <input
        v-model="description"
        type="text"
        placeholder="What are you working on?"
        class="w-full px-4 py-4 pr-56 bg-gray-50 border border-border-light rounded-md focus:outline-none text-text-primary"
      />

      <!-- Client Icon Button -->
      <div class="absolute right-[15rem] top-1/2 -translate-y-1/2">
        <button
          @click="toggleClientPopover"
          :class="[
            'p-2 rounded-md transition-colors',
            selectedClientId
              ? 'bg-accent text-text-primary'
              : showClientPopover
                ? 'bg-gray-100 text-text-secondary'
                : 'text-text-secondary hover:bg-gray-100'
          ]"
          :title="selectedClient ? selectedClient.name : 'Select client'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </button>

        <!-- Client Popover -->
        <div
          v-if="showClientPopover"
          class="absolute top-full mt-2 left-0 bg-white border border-border-light rounded-md shadow-lg z-50 w-64"
          @click.stop
        >
          <div class="p-2 border-b border-border-light">
            <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">
              Select Client
            </div>
          </div>
          <div class="max-h-64 overflow-y-auto p-2">
            <button
              v-if="selectedClientId"
              @click="selectClient('')"
              class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors text-red-600 hover:bg-red-50 mb-1 border-b border-border-light pb-2"
            >
              Clear selection
            </button>
            <button
              v-for="client in clients"
              :key="client.id"
              @click="selectClient(client.id)"
              :class="[
                'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                selectedClientId === client.id ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
              ]"
            >
              {{ client.name }}
            </button>
            <div v-if="clients.length === 0" class="px-3 py-4 text-sm text-text-secondary text-center">
              No clients found
            </div>
          </div>
        </div>
      </div>

      <!-- Project Icon Button -->
      <div class="absolute right-[12.5rem] top-1/2 -translate-y-1/2">
        <button
          @click="toggleProjectPopover"
          :disabled="!selectedClientId"
          :class="[
            'p-2 rounded-md transition-colors',
            selectedProjectId
              ? 'bg-accent text-text-primary'
              : showProjectPopover
                ? 'bg-gray-100 text-text-secondary'
                : 'text-text-secondary hover:bg-gray-100',
            !selectedClientId && 'cursor-not-allowed'
          ]"
          :title="selectedProject ? selectedProject.name : (selectedClientId ? 'Select project' : 'Select client first')"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
        </button>

        <!-- Project Popover -->
        <div
          v-if="showProjectPopover"
          class="absolute top-full mt-2 left-0 bg-white border border-border-light rounded-md shadow-lg z-50 w-64"
          @click.stop
        >
          <div class="p-2 border-b border-border-light">
            <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">
              Select Project
            </div>
          </div>
          <div class="max-h-64 overflow-y-auto p-2">
            <button
              v-if="selectedProjectId"
              @click="selectProject('')"
              class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors text-red-600 hover:bg-red-50 mb-1 border-b border-border-light pb-2"
            >
              Clear selection
            </button>
            <button
              v-for="project in filteredProjects"
              :key="project.id"
              @click="selectProject(project.id)"
              :class="[
                'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                selectedProjectId === project.id ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
              ]"
            >
              {{ project.name }}
            </button>
            <div v-if="filteredProjects.length === 0" class="px-3 py-4 text-sm text-text-secondary text-center">
              {{ selectedClientId ? 'No projects for this client' : 'Select a client first' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Timer Display (shows 00:00:00 for new timer) -->
      <div class="absolute right-[4.5rem] top-1/2 -translate-y-1/2 text-xl font-mono font-bold text-text-secondary pointer-events-none">
        00:00:00
      </div>

      <!-- Play Button -->
      <button
        @click="startTimer"
        :disabled="!selectedProjectId || starting"
        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full disabled:bg-gray-300 text-text-primary disabled:text-gray-500 flex items-center justify-center disabled:cursor-not-allowed transition-colors"
        :class="selectedProjectId && !starting ? 'play-button' : ''"
        :title="!selectedProjectId ? 'Select a project first' : 'Start timer'"
      >
        <svg v-if="!starting" class="w-7 h-7 relative z-[1]" fill="currentColor" viewBox="0 0 24 24">
          <path d="M8 5v14l11-7z"/>
        </svg>
        <svg v-else class="w-5 h-5 animate-spin relative z-[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
const timerApi = useApi()
const clients = ref([])
const projects = ref([])
const selectedClientId = ref('')
const selectedProjectId = ref('')
const description = ref('')
const starting = ref(false)
const showClientPopover = ref(false)
const showProjectPopover = ref(false)

const filteredProjects = computed(() => {
  if (!selectedClientId.value) return []
  return projects.value.filter(p => p.client_id === selectedClientId.value)
})

const selectedClient = computed(() => {
  return clients.value.find(c => c.id === selectedClientId.value)
})

const selectedProject = computed(() => {
  return projects.value.find(p => p.id === selectedProjectId.value)
})

// Reset project selection when client changes
watch(selectedClientId, () => {
  selectedProjectId.value = ''
})

// Close popovers when clicking outside
onMounted(() => {
  document.addEventListener('click', closePopovers)
})

onUnmounted(() => {
  document.removeEventListener('click', closePopovers)
})

const closePopovers = () => {
  showClientPopover.value = false
  showProjectPopover.value = false
}

const toggleClientPopover = (event) => {
  event.stopPropagation()
  showClientPopover.value = !showClientPopover.value
  showProjectPopover.value = false
}

const toggleProjectPopover = (event) => {
  event.stopPropagation()
  if (!selectedClientId.value) return
  showProjectPopover.value = !showProjectPopover.value
  showClientPopover.value = false
}

const selectClient = (clientId) => {
  selectedClientId.value = clientId
  showClientPopover.value = false
}

const selectProject = (projectId) => {
  selectedProjectId.value = projectId
  showProjectPopover.value = false
}

const fetchClients = async () => {
  try {
    clients.value = await timerApi.api('/clients')
  } catch (error) {
    console.error('Failed to fetch clients:', error)
  }
}

const fetchProjects = async () => {
  try {
    projects.value = await timerApi.api('/projects')
  } catch (error) {
    console.error('Failed to fetch projects:', error)
  }
}

const startTimer = async () => {
  if (!selectedProjectId.value || starting.value) return

  try {
    starting.value = true

    // First, stop any currently running timer
    const activeTimer = await timerApi.api('/active-timer')
    if (activeTimer && activeTimer.id) {
      const stoppedEntry = await timerApi.api(`/time-entries/${activeTimer.id}/stop`, {
        method: 'POST'
      })
      // Notify to update the stopped entry locally
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('timer-entry-updated', { detail: stoppedEntry }))
      }
    }

    // Now start the new timer
    const newEntry = await timerApi.api('/time-entries', {
      method: 'POST',
      body: {
        project_id: selectedProjectId.value,
        description: description.value || null,
        is_billable: true
      }
    })

    // Clear the form
    selectedClientId.value = ''
    selectedProjectId.value = ''
    description.value = ''

    // Notify with the new entry data for local update (no full refresh needed)
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('timer-new-entry', { detail: newEntry }))
      // Also notify sidebar to update its display
      window.dispatchEvent(new Event('timer-started'))
    }
  } catch (error) {
    console.error('Failed to start timer:', error)
  } finally {
    starting.value = false
  }
}

onMounted(() => {
  fetchClients()
  fetchProjects()
})
</script>

<style scoped>
.play-button {
  background: linear-gradient(180deg, #e7ffa2, #cefb47);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.11);
}

.play-button::before {
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
</style>
