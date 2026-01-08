<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Projects</h1>
      <button
        @click="openCreateModal"
        class="btn-accent px-3 md:px-4 py-2 text-xs md:text-sm font-medium text-text-primary rounded-md whitespace-nowrap"
      >
        <span class="relative z-[1]">
          <span class="hidden sm:inline">+ New project</span>
          <span class="sm:hidden">+ New</span>
        </span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white">
      <div class="flex items-center gap-2 md:gap-3">
        <!-- Client Filter Icon -->
        <div class="relative">
          <button
            @click="toggleClientPopover"
            :class="[
              'p-2 rounded-md transition-colors',
              clientFilter !== 'all'
                ? 'bg-accent text-text-primary'
                : showClientPopover
                  ? 'bg-gray-100 text-text-secondary'
                  : 'text-text-secondary hover:bg-gray-100'
            ]"
            :title="selectedClientName || 'Filter by client'"
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
                Filter by Client
              </div>
            </div>
            <div class="max-h-64 overflow-y-auto p-2">
              <button
                v-if="clientFilter !== 'all'"
                @click="selectClientFilter('all')"
                class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors text-red-600 hover:bg-red-50 mb-1 border-b border-border-light pb-2"
              >
                Clear filter
              </button>
              <button
                v-for="client in clients"
                :key="client.id"
                @click="selectClientFilter(client.id)"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  clientFilter == client.id ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
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

        <!-- Status Filter Icon -->
        <div class="relative">
          <button
            @click="toggleStatusPopover"
            :class="[
              'p-2 rounded-md transition-colors',
              statusFilter !== 'all'
                ? 'bg-accent text-text-primary'
                : showStatusPopover
                  ? 'bg-gray-100 text-text-secondary'
                  : 'text-text-secondary hover:bg-gray-100'
            ]"
            :title="statusFilter !== 'all' ? (statusFilter === 'active' ? 'Active' : 'Inactive') : 'Filter by status'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
          </button>

          <!-- Status Popover -->
          <div
            v-if="showStatusPopover"
            class="absolute top-full mt-2 left-0 bg-white border border-border-light rounded-md shadow-lg z-50 w-48"
            @click.stop
          >
            <div class="p-2 border-b border-border-light">
              <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">
                Filter by Status
              </div>
            </div>
            <div class="p-2">
              <button
                v-if="statusFilter !== 'all'"
                @click="selectStatusFilter('all')"
                class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors text-red-600 hover:bg-red-50 mb-1 border-b border-border-light pb-2"
              >
                Clear filter
              </button>
              <button
                @click="selectStatusFilter('active')"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  statusFilter === 'active' ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
                ]"
              >
                Active
              </button>
              <button
                @click="selectStatusFilter('inactive')"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  statusFilter === 'inactive' ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
                ]"
              >
                Inactive
              </button>
            </div>
          </div>
        </div>

        <!-- Search Input -->
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-full px-3 py-2 text-sm border border-border-light rounded-md focus:outline-none"
          />
        </div>
      </div>
    </div>

    <!-- Projects List -->
    <div class="bg-white rounded-lg">
      <div class="p-4 pb-[0.35rem] bg-[#f1f0ee] rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
            All Projects
          </div>
          <div class="text-xs md:text-sm text-text-secondary">
            Total: <span class="font-semibold text-text-primary">{{ filteredProjects.length }}</span>
          </div>
        </div>
      </div>

      <div class="p-2 bg-[#f1f0ee] rounded-b-lg">
        <div v-if="loading" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          Loading...
        </div>

        <div v-else-if="filteredProjects.length === 0" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          {{ searchQuery || statusFilter !== 'all' || clientFilter !== 'all' ? 'No projects found matching your filters.' : 'No projects yet. Create your first project to get started.' }}
        </div>

        <div v-else class="space-y-1">
          <ProjectCard
            v-for="project in filteredProjects"
            :key="project.id"
            :project="project"
            :deleting="deletingId === project.id"
            @edit="openEditModal"
            @delete="deleteProject"
          />
        </div>
      </div>
    </div>

    <!-- Project Modal -->
    <ProjectModal
      :isOpen="showModal"
      :project="editingProject"
      :clients="clients"
      @close="closeModal"
      @save="handleSave"
    />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const api = useApi()
const projects = ref([])
const clients = ref([])
const loading = ref(true)
const deletingId = ref(null)
const showModal = ref(false)
const editingProject = ref(null)
const searchQuery = ref('')
const clientFilter = ref('all')
const statusFilter = ref('all')
const showClientPopover = ref(false)
const showStatusPopover = ref(false)

// Computed for selected client name (for tooltip)
const selectedClientName = computed(() => {
  if (clientFilter.value === 'all') return null
  const client = clients.value.find(c => c.id == clientFilter.value)
  return client ? client.name : null
})

const closePopovers = () => {
  showClientPopover.value = false
  showStatusPopover.value = false
}

const toggleClientPopover = (event) => {
  event.stopPropagation()
  showClientPopover.value = !showClientPopover.value
  showStatusPopover.value = false
}

const toggleStatusPopover = (event) => {
  event.stopPropagation()
  showStatusPopover.value = !showStatusPopover.value
  showClientPopover.value = false
}

const selectClientFilter = (value) => {
  clientFilter.value = value
  showClientPopover.value = false
}

const selectStatusFilter = (value) => {
  statusFilter.value = value
  showStatusPopover.value = false
}

const filteredProjects = computed(() => {
  let filtered = projects.value

  // Filter by client
  if (clientFilter.value !== 'all') {
    filtered = filtered.filter(project => project.client_id == clientFilter.value)
  }

  // Filter by status
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(project => project.status === statusFilter.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(project =>
      project.name.toLowerCase().includes(query) ||
      (project.description && project.description.toLowerCase().includes(query)) ||
      (project.client?.name && project.client.name.toLowerCase().includes(query))
    )
  }

  return filtered.sort((a, b) => a.name.localeCompare(b.name))
})

const fetchProjects = async () => {
  try {
    loading.value = true
    projects.value = await api.api('/projects')
  } catch (error) {
    console.error('Failed to fetch projects:', error)
  } finally {
    loading.value = false
  }
}

const fetchClients = async () => {
  try {
    clients.value = await api.api('/clients')
  } catch (error) {
    console.error('Failed to fetch clients:', error)
  }
}

const openCreateModal = () => {
  editingProject.value = null
  showModal.value = true
}

const openEditModal = (project) => {
  editingProject.value = project
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProject.value = null
}

const handleSave = () => {
  fetchProjects()
  closeModal()
}

const deleteProject = async (id) => {
  const project = projects.value.find(p => p.id === id)

  if (!confirm(`Are you sure you want to delete ${project?.name}?`)) {
    return
  }

  try {
    deletingId.value = id
    await api.api(`/projects/${id}`, {
      method: 'DELETE'
    })
    projects.value = projects.value.filter(p => p.id !== id)
  } catch (error) {
    console.error('Failed to delete project:', error)
    alert('Failed to delete project. Please try again.')
  } finally {
    deletingId.value = null
  }
}

onMounted(() => {
  fetchProjects()
  fetchClients()
  document.addEventListener('click', closePopovers)
})

onUnmounted(() => {
  document.removeEventListener('click', closePopovers)
})
</script>
