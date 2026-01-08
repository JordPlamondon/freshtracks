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
      <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-text-primary">Edit Time Entry</h3>
          <button
            @click="close"
            class="text-text-secondary hover:text-text-primary"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="save" class="space-y-4">
          <!-- Project Selection -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Project</label>
            <select
              v-model="form.project_id"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
            >
              <option value="">Select project...</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.client?.name }} - {{ project.name }}
              </option>
            </select>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              placeholder="What were you working on?"
            ></textarea>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Date</label>
            <input
              v-model="form.date"
              type="date"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
            />
          </div>

          <!-- Time Range -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-text-primary mb-1">Start Time</label>
              <input
                v-model="form.start_time"
                type="time"
                step="1"
                required
                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-text-primary mb-1">End Time</label>
              <input
                v-model="form.end_time"
                type="time"
                step="1"
                required
                class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              />
            </div>
          </div>

          <!-- Billable Toggle -->
          <div class="flex items-center">
            <input
              v-model="form.is_billable"
              type="checkbox"
              id="billable"
              class="w-4 h-4 text-link border-border-light rounded"
            />
            <label for="billable" class="ml-2 text-sm text-text-primary">
              Billable
            </label>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-border-light">
            <button
              type="button"
              @click="close"
              class="px-4 py-2 text-sm font-medium text-text-secondary hover:text-text-primary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="btn-accent px-4 py-2 text-sm font-medium text-text-primary rounded-md disabled:opacity-50"
            >
              <span class="relative z-[1]">{{ saving ? 'Saving...' : 'Save changes' }}</span>
            </button>
          </div>
        </form>
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
  entry: {
    type: Object,
    default: null
  },
  projects: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'save'])

const api = useApi()
const saving = ref(false)

const form = ref({
  project_id: '',
  description: '',
  date: '',
  start_time: '',
  end_time: '',
  is_billable: true
})

// Track original time values to detect if user changed them
const originalTimes = ref({
  date: '',
  start_time: '',
  end_time: ''
})

watch(() => props.entry, (entry) => {
  if (entry) {
    const started = new Date(entry.started_at)
    const stopped = entry.stopped_at ? new Date(entry.stopped_at) : new Date()

    // Format date in local timezone (not UTC)
    const formatDate = (date) => {
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }

    // Format time as HH:MM:SS for time input with step="1"
    const formatTime = (date) => {
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const seconds = String(date.getSeconds()).padStart(2, '0')
      return `${hours}:${minutes}:${seconds}`
    }

    const dateStr = formatDate(started)
    const startTimeStr = formatTime(started)
    const endTimeStr = formatTime(stopped)

    form.value = {
      project_id: entry.project_id || '',
      description: entry.description || '',
      date: dateStr,
      start_time: startTimeStr,
      end_time: endTimeStr,
      is_billable: entry.is_billable
    }

    // Store original values to compare later
    originalTimes.value = {
      date: dateStr,
      start_time: startTimeStr,
      end_time: endTimeStr
    }
  }
}, { immediate: true })

const close = () => {
  emit('close')
}

const save = async () => {
  if (saving.value || !props.entry) return

  try {
    saving.value = true

    // Check if times were changed by the user
    const timesChanged =
      form.value.date !== originalTimes.value.date ||
      form.value.start_time !== originalTimes.value.start_time ||
      form.value.end_time !== originalTimes.value.end_time

    // Build the data object - only include times if they were changed
    const data = {
      project_id: form.value.project_id,
      description: form.value.description || null,
      is_billable: form.value.is_billable
    }

    // Only send time data if the user actually changed the times
    // This preserves accumulated duration_minutes for entries with multiple sessions
    if (timesChanged) {
      const startDateTime = new Date(`${form.value.date}T${form.value.start_time}`)
      const stopDateTime = new Date(`${form.value.date}T${form.value.end_time}`)

      // Validate that stop time is after start time
      if (stopDateTime <= startDateTime) {
        alert('End time must be after start time.')
        saving.value = false
        return
      }

      data.started_at = startDateTime.toISOString()
      data.stopped_at = stopDateTime.toISOString()
    }

    const response = await api.api(`/time-entries/${props.entry.id}`, {
      method: 'PUT',
      body: data
    })

    emit('save')
    close()
  } catch (error) {
    console.error('Failed to update entry:', error)
    console.error('Error data:', error.data)
    alert('Failed to update time entry. Please try again.')
  } finally {
    saving.value = false
  }
}
</script>
