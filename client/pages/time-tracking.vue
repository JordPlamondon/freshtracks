<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Time Tracking</h1>
      <ClientOnly>
        <DatePicker v-model="selectedDate" />
      </ClientOnly>
    </div>

    <!-- Timer Input Area (for starting NEW timers only - desktop only) -->
    <div class="hidden md:block">
      <Timer />
    </div>

    <!-- Weekly View with Daily Entries Table -->
    <ClientOnly>
      <WeeklyView
        :entries="entries"
        :selectedDate="selectedDate"
        :loading="loading"
        :deletingId="deletingId"
        @selectDay="selectDay"
        @edit="openEditModal"
        @delete="deleteEntry"
        @updateEntry="updateEntryLocal"
        @addEntry="addEntryLocal"
      />
    </ClientOnly>

    <!-- Edit Modal -->
    <EditEntryModal
      :isOpen="showEditModal"
      :entry="editingEntry"
      :projects="allProjects"
      @close="closeEditModal"
      @save="handleSave"
    />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const api = useApi()
const entries = ref([])
const allProjects = ref([])
const loading = ref(true)
const deletingId = ref(null)

const getTodayStr = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
}
const selectedDate = ref(getTodayStr())
const showEditModal = ref(false)
const editingEntry = ref(null)

const selectDay = (date) => {
  selectedDate.value = date
}

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
    allProjects.value = await api.api('/projects')
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

    const entryToDelete = entries.value.find(e => e.id === id)
    const wasRunning = entryToDelete && !entryToDelete.stopped_at

    await api.api(`/time-entries/${id}`, {
      method: 'DELETE'
    })
    entries.value = entries.value.filter(e => e.id !== id)

    if (wasRunning) {
      window.dispatchEvent(new Event('timer-stopped'))
    }
  } catch (error) {
    console.error('Failed to delete entry:', error)
    alert('Failed to delete time entry. Please try again.')
  } finally {
    deletingId.value = null
  }
}

const updateEntryLocal = (updatedEntry) => {
  const index = entries.value.findIndex(e => e.id === updatedEntry.id)
  if (index !== -1) {
    entries.value = [
      ...entries.value.slice(0, index),
      updatedEntry,
      ...entries.value.slice(index + 1)
    ]
  }
}

const addEntryLocal = (newEntry) => {
  const existingIndex = entries.value.findIndex(e => e.id === newEntry.id)
  if (existingIndex !== -1) {
    entries.value = [
      ...entries.value.slice(0, existingIndex),
      newEntry,
      ...entries.value.slice(existingIndex + 1)
    ]
  } else {
    entries.value = [newEntry, ...entries.value]
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

const handleNewEntry = (event) => {
  addEntryLocal(event.detail)
}

const handleEntryUpdated = (event) => {
  updateEntryLocal(event.detail)
}

const handleWsTimerStarted = (event) => {
  console.log('Time-tracking: ws-timer-started', event.detail)
  const index = entries.value.findIndex(e => e.id === event.detail.id)
  if (index !== -1) {
    entries.value = [
      ...entries.value.slice(0, index),
      event.detail,
      ...entries.value.slice(index + 1)
    ]
  } else {
    entries.value = [event.detail, ...entries.value]
  }
}

const handleWsTimerStopped = (event) => {
  console.log('Time-tracking: ws-timer-stopped', event.detail)
  const index = entries.value.findIndex(e => e.id === event.detail.id)
  if (index !== -1) {
    entries.value = [
      ...entries.value.slice(0, index),
      event.detail,
      ...entries.value.slice(index + 1)
    ]
  }
}

const handleWsTimerDeleted = (event) => {
  console.log('Time-tracking: ws-timer-deleted', event.detail)
  const entryId = event.detail
  entries.value = entries.value.filter(e => e.id !== entryId)
}

const handleKeyboardShortcut = (e) => {
  const action = e.detail?.action
  if (action === 'prev-day') {
    navigateDay(-1)
  } else if (action === 'next-day') {
    navigateDay(1)
  } else if (action === 'jump-today') {
    selectedDate.value = getTodayStr()
  }
}

const navigateDay = (offset) => {
  const [year, month, day] = selectedDate.value.split('-').map(Number)
  const date = new Date(year, month - 1, day)
  date.setDate(date.getDate() + offset)
  selectedDate.value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const handleTimerStopped = () => {
  fetchEntries()
}

onMounted(() => {
  selectedDate.value = getTodayStr()

  fetchEntries()
  fetchProjects()
  window.addEventListener('timer-new-entry', handleNewEntry)
  window.addEventListener('timer-entry-updated', handleEntryUpdated)
  window.addEventListener('keyboard-shortcut', handleKeyboardShortcut)
  window.addEventListener('timer-stopped', handleTimerStopped)
  window.addEventListener('ws-timer-started', handleWsTimerStarted)
  window.addEventListener('ws-timer-stopped', handleWsTimerStopped)
  window.addEventListener('ws-timer-deleted', handleWsTimerDeleted)
})

onUnmounted(() => {
  window.removeEventListener('timer-new-entry', handleNewEntry)
  window.removeEventListener('timer-entry-updated', handleEntryUpdated)
  window.removeEventListener('keyboard-shortcut', handleKeyboardShortcut)
  window.removeEventListener('timer-stopped', handleTimerStopped)
  window.removeEventListener('ws-timer-started', handleWsTimerStarted)
  window.removeEventListener('ws-timer-stopped', handleWsTimerStopped)
  window.removeEventListener('ws-timer-deleted', handleWsTimerDeleted)
})
</script>
