<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Clients</h1>
      <button
        @click="openCreateModal"
        class="btn-accent px-3 md:px-4 py-2 text-xs md:text-sm font-medium text-text-primary rounded-md whitespace-nowrap"
      >
        <span class="relative z-[1]">
          <span class="hidden sm:inline">+ New client</span>
          <span class="sm:hidden">+ New</span>
        </span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white">
      <div class="flex items-center gap-2 md:gap-3">
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

    <!-- Clients List -->
    <div class="bg-white rounded-lg">
      <div class="p-4 pb-[0.35rem] bg-[#f1f0ee] rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
            All Clients
          </div>
          <div class="text-xs md:text-sm text-text-secondary">
            Total: <span class="font-semibold text-text-primary">{{ filteredClients.length }}</span>
          </div>
        </div>
      </div>

      <div class="p-2 bg-[#f1f0ee] rounded-b-lg">
        <div v-if="loading" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          Loading...
        </div>

        <div v-else-if="filteredClients.length === 0" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          {{ searchQuery || statusFilter !== 'all' ? 'No clients found matching your filters.' : 'No clients yet. Create your first client to get started.' }}
        </div>

        <div v-else class="space-y-1">
          <ClientCard
            v-for="client in filteredClients"
            :key="client.id"
            :client="client"
            :deleting="deletingId === client.id"
            @edit="openEditModal"
            @delete="deleteClient"
          />
        </div>
      </div>
    </div>

    <!-- Client Modal -->
    <ClientModal
      :isOpen="showModal"
      :client="editingClient"
      @close="closeModal"
      @save="handleSave"
    />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const { items: clients, loading, deletingId, fetch: fetchClients, remove } = useResource('/clients')
const showModal = ref(false)
const editingClient = ref(null)
const searchQuery = ref('')
const statusFilter = ref('all')
const showStatusPopover = ref(false)

const closePopovers = () => {
  showStatusPopover.value = false
}

const toggleStatusPopover = (event) => {
  event.stopPropagation()
  showStatusPopover.value = !showStatusPopover.value
}

const selectStatusFilter = (value) => {
  statusFilter.value = value
  showStatusPopover.value = false
}

const filteredClients = computed(() => {
  let filtered = clients.value

  // Filter by status
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(client => client.status === statusFilter.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(client =>
      client.name.toLowerCase().includes(query) ||
      (client.email && client.email.toLowerCase().includes(query))
    )
  }

  return filtered.sort((a, b) => a.name.localeCompare(b.name))
})

const openCreateModal = () => {
  editingClient.value = null
  showModal.value = true
}

const openEditModal = (client) => {
  editingClient.value = client
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingClient.value = null
}

const handleSave = () => {
  fetchClients()
  closeModal()
}

const deleteClient = async (id) => {
  const client = clients.value.find(c => c.id === id)

  if (client?.projects?.length > 0) {
    alert(`Cannot delete ${client.name} because they have ${client.projects.length} project(s). Please delete or reassign the projects first.`)
    return
  }

  await remove(id, client?.name)
}

onMounted(() => {
  fetchClients()
  document.addEventListener('click', closePopovers)
})

onUnmounted(() => {
  document.removeEventListener('click', closePopovers)
})
</script>
