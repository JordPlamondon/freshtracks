<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">All Entries</h1>
    </div>

    <!-- Entries List -->
    <div class="bg-white rounded-lg">
      <div class="p-4 pb-[0.35rem] bg-[#f1f0ee] rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
            Entry History
          </div>
          <div class="text-xs md:text-sm text-text-secondary">
            Total: <span class="font-semibold text-text-primary">{{ entries.length }}</span>
          </div>
        </div>
      </div>

      <div class="p-2 bg-[#f1f0ee] rounded-b-lg">
        <div v-if="loading" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          Loading...
        </div>

        <div v-else-if="entries.length === 0" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          No time entries yet. Start tracking your time to see entries here.
        </div>

        <div v-else class="space-y-1">
          <TimeEntryCard
            v-for="entry in entries"
            :key="entry.id"
            :entry="entry"
            :deleting="deletingId === entry.id"
            @edit="openEditModal"
            @delete="deleteEntry"
          />
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <EditEntryModal
      :isOpen="showEditModal"
      :entry="editingEntry"
      :projects="projects"
      @close="closeEditModal"
      @save="handleSave"
    />
  </div>
</template>

<script setup>
const api = useApi()
const entries = ref([])
const projects = ref([])
const loading = ref(true)
const deletingId = ref(null)
const showEditModal = ref(false)
const editingEntry = ref(null)

const fetchEntries = async () => {
  try {
    loading.value = true
    entries.value = await api.api('/time-entries')
  } catch (error) {
    console.error('Failed to fetch entries:', error)
  } finally {
    loading.value = false
  }
}

const fetchProjects = async () => {
  try {
    projects.value = await api.api('/projects')
  } catch (error) {
    console.error('Failed to fetch projects:', error)
  }
}

const deleteEntry = async (id) => {
  if (!confirm('Are you sure you want to delete this time entry?')) {
    return
  }

  try {
    deletingId.value = id
    await api.api(`/time-entries/${id}`, {
      method: 'DELETE'
    })
    entries.value = entries.value.filter(e => e.id !== id)
  } catch (error) {
    console.error('Failed to delete entry:', error)
    alert('Failed to delete time entry. Please try again.')
  } finally {
    deletingId.value = null
  }
}

const openEditModal = (entry) => {
  editingEntry.value = entry
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingEntry.value = null
}

const handleSave = () => {
  fetchEntries()
  closeEditModal()
}

onMounted(() => {
  fetchEntries()
  fetchProjects()
})
</script>
