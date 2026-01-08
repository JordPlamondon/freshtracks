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
        <!-- Header -->
        <div class="flex justify-between items-center px-6 pt-4">
          <h3 class="text-lg font-semibold text-text-primary">Start timer</h3>
          <button
            @click="close"
            class="text-text-secondary hover:text-text-primary transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
          <!-- Client Selection -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-2">Client</label>
            <div class="relative">
              <button
                @click="toggleClientDropdown"
                class="w-full px-4 py-3 bg-gray-50 border border-border-light rounded-md text-left flex items-center justify-between focus:outline-none hover:border-gray-300 transition-colors"
              >
                <span :class="selectedClientId ? 'text-text-primary' : 'text-text-secondary'">
                  {{ selectedClient ? selectedClient.name : 'Select client...' }}
                </span>
                <svg class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <!-- Client Dropdown -->
              <div
                v-if="showClientDropdown"
                class="absolute top-full left-0 right-0 mt-1 bg-white border border-border-light rounded-md shadow-lg z-50 max-h-48 overflow-y-auto"
              >
                <button
                  v-for="(client, index) in clients"
                  :key="client.id"
                  @click="selectClient(client.id)"
                  @mouseenter="highlightedClientIndex = index"
                  :class="[
                    'w-full text-left px-4 py-2 text-sm transition-colors',
                    highlightedClientIndex === index ? 'bg-gray-100 text-text-primary' : 'text-text-primary hover:bg-gray-50'
                  ]"
                >
                  {{ client.name }}
                </button>
                <div v-if="clients.length === 0" class="px-4 py-3 text-sm text-text-secondary text-center">
                  No clients found
                </div>
              </div>
            </div>
          </div>

          <!-- Project Selection -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-2">Project</label>
            <div class="relative">
              <button
                @click="toggleProjectDropdown"
                :disabled="!selectedClientId"
                :class="[
                  'w-full px-4 py-3 bg-gray-50 border border-border-light rounded-md text-left flex items-center justify-between focus:outline-none transition-colors',
                  selectedClientId ? 'hover:border-gray-300' : 'cursor-not-allowed opacity-60'
                ]"
              >
                <span :class="selectedProjectId ? 'text-text-primary' : 'text-text-secondary'">
                  {{ selectedProject ? selectedProject.name : (selectedClientId ? 'Select project...' : 'Select client first') }}
                </span>
                <svg class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <!-- Project Dropdown -->
              <div
                v-if="showProjectDropdown && selectedClientId"
                class="absolute top-full left-0 right-0 mt-1 bg-white border border-border-light rounded-md shadow-lg z-50 max-h-48 overflow-y-auto"
              >
                <button
                  v-for="(project, index) in filteredProjects"
                  :key="project.id"
                  @click="selectProject(project.id)"
                  @mouseenter="highlightedProjectIndex = index"
                  :class="[
                    'w-full text-left px-4 py-2 text-sm transition-colors',
                    highlightedProjectIndex === index ? 'bg-gray-100 text-text-primary' : 'text-text-primary hover:bg-gray-50'
                  ]"
                >
                  {{ project.name }}
                </button>
                <div v-if="filteredProjects.length === 0" class="px-4 py-3 text-sm text-text-secondary text-center">
                  No projects for this client
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-2">
              Description
              <span class="font-normal text-text-secondary">(optional)</span>
            </label>
            <input
              ref="descriptionInput"
              v-model="description"
              type="text"
              placeholder="What are you working on?"
              class="w-full px-4 py-3 bg-gray-50 border border-border-light rounded-md focus:outline-none text-text-primary placeholder-text-secondary"
              @keydown.enter="startTimer"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-border-light bg-gray-50 flex items-center justify-between">
          <div class="text-xs text-text-secondary">
            Press <kbd class="kbd-sm">Enter</kbd> to start
          </div>
          <button
            @click="startTimer"
            :disabled="!selectedProjectId || starting"
            :class="[
              'px-5 py-2.5 rounded-md text-sm font-medium transition-colors flex items-center gap-2',
              selectedProjectId && !starting
                ? 'start-button text-text-primary'
                : 'bg-gray-200 text-gray-500 cursor-not-allowed'
            ]"
          >
            <svg v-if="!starting" class="w-4 h-4 relative z-[1]" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z"/>
            </svg>
            <svg v-else class="w-4 h-4 animate-spin relative z-[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="relative z-[1]">{{ starting ? 'Starting...' : 'Start timer' }}</span>
          </button>
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
  }
})

const emit = defineEmits(['close', 'started'])

const api = useApi()
const descriptionInput = ref(null)

const clients = ref([])
const projects = ref([])
const selectedClientId = ref('')
const selectedProjectId = ref('')
const description = ref('')
const starting = ref(false)
const showClientDropdown = ref(false)
const showProjectDropdown = ref(false)
const highlightedClientIndex = ref(0)
const highlightedProjectIndex = ref(0)

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

// Focus description input and fetch data when modal opens
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    // Reset form
    selectedClientId.value = ''
    selectedProjectId.value = ''
    description.value = ''
    showClientDropdown.value = false
    showProjectDropdown.value = false

    // Fetch fresh data
    await Promise.all([fetchClients(), fetchProjects()])

    // Reset and auto-open client dropdown when modal opens
    highlightedClientIndex.value = 0
    highlightedProjectIndex.value = 0
    nextTick(() => {
      showClientDropdown.value = true
    })
  }
})

// Close dropdowns when clicking outside
const handleClickOutside = (e) => {
  if (!e.target.closest('.relative')) {
    showClientDropdown.value = false
    showProjectDropdown.value = false
  }
}

// Handle keyboard shortcuts when modal is open
const handleKeydown = (e) => {
  if (!props.isOpen) return

  // Escape closes modal
  if (e.key === 'Escape') {
    close()
    return
  }

  // Cmd+K closes this modal and opens command palette
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    close()
    // Small delay to allow this modal to close first
    nextTick(() => {
      window.dispatchEvent(new Event('open-command-palette'))
    })
    return
  }

  // Arrow key navigation for client dropdown
  if (showClientDropdown.value) {
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      highlightedClientIndex.value = Math.min(highlightedClientIndex.value + 1, clients.value.length - 1)
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      highlightedClientIndex.value = Math.max(highlightedClientIndex.value - 1, 0)
    } else if (e.key === 'Enter') {
      e.preventDefault()
      const client = clients.value[highlightedClientIndex.value]
      if (client) {
        selectClient(client.id)
      }
    }
    return
  }

  // Arrow key navigation for project dropdown
  if (showProjectDropdown.value) {
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      highlightedProjectIndex.value = Math.min(highlightedProjectIndex.value + 1, filteredProjects.value.length - 1)
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      highlightedProjectIndex.value = Math.max(highlightedProjectIndex.value - 1, 0)
    } else if (e.key === 'Enter') {
      e.preventDefault()
      const project = filteredProjects.value[highlightedProjectIndex.value]
      if (project) {
        selectProject(project.id)
      }
    }
    return
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

const toggleClientDropdown = (e) => {
  e.stopPropagation()
  showClientDropdown.value = !showClientDropdown.value
  showProjectDropdown.value = false
}

const toggleProjectDropdown = (e) => {
  e.stopPropagation()
  if (!selectedClientId.value) return
  showProjectDropdown.value = !showProjectDropdown.value
  showClientDropdown.value = false
}

const selectClient = (clientId) => {
  selectedClientId.value = clientId
  showClientDropdown.value = false

  // Reset and auto-open project dropdown after selecting client
  highlightedProjectIndex.value = 0
  setTimeout(() => {
    showProjectDropdown.value = true
  }, 50)
}

const selectProject = (projectId) => {
  selectedProjectId.value = projectId
  showProjectDropdown.value = false

  // Auto-focus description field after selecting project
  nextTick(() => {
    descriptionInput.value?.focus()
  })
}

const fetchClients = async () => {
  try {
    clients.value = await api.api('/clients')
  } catch (error) {
    console.error('Failed to fetch clients:', error)
  }
}

const fetchProjects = async () => {
  try {
    projects.value = await api.api('/projects')
  } catch (error) {
    console.error('Failed to fetch projects:', error)
  }
}

const close = () => {
  emit('close')
}

const startTimer = async () => {
  if (!selectedProjectId.value || starting.value) return

  try {
    starting.value = true

    // First, stop any currently running timer
    const activeTimer = await api.api('/active-timer')
    if (activeTimer && activeTimer.id) {
      const stoppedEntry = await api.api(`/time-entries/${activeTimer.id}/stop`, {
        method: 'POST'
      })
      // Notify to update the stopped entry locally
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('timer-entry-updated', { detail: stoppedEntry }))
      }
    }

    // Now start the new timer
    const newEntry = await api.api('/time-entries', {
      method: 'POST',
      body: {
        project_id: selectedProjectId.value,
        description: description.value || null,
        is_billable: true
      }
    })

    // Notify with the new entry data for local update
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('timer-new-entry', { detail: newEntry }))
      window.dispatchEvent(new Event('timer-started'))
    }

    emit('started', newEntry)
    close()
  } catch (error) {
    console.error('Failed to start timer:', error)
    alert('Failed to start timer. Please try again.')
  } finally {
    starting.value = false
  }
}
</script>

<style scoped>
.start-button {
  position: relative;
  background: linear-gradient(180deg, #e7ffa2, #cefb47);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.11);
}

.start-button::before {
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

.kbd-sm {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.25rem;
  height: 1.25rem;
  padding: 0 0.375rem;
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
