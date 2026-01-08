<template>
  <div class="bg-white shadow-[0_1px_2px_rgba(0,0,0,0.12)] rounded-lg p-4 hover:shadow-sm transition-shadow">
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-1">
          <span class="font-medium text-text-primary">{{ entry.project?.name || 'No Project' }}</span>
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-text-secondary">
            {{ entry.project?.client?.name || 'No Client' }}
          </span>
          <span
            v-if="entry.is_billable"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"
          >
            Billable
          </span>
        </div>

        <p v-if="entry.description" class="text-sm text-text-secondary mb-2">
          {{ entry.description }}
        </p>

        <div class="text-xs text-text-secondary">
          {{ formatTimeRange(entry.started_at, entry.stopped_at) }}
        </div>
      </div>

      <div class="ml-4 flex flex-col items-end gap-2">
        <div class="font-mono text-lg font-semibold text-text-primary">
          {{ formatDuration(entry.duration_minutes) }}
        </div>

        <div class="flex items-center gap-1">
          <button
            v-if="showEdit"
            @click="$emit('edit', entry)"
            class="p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded transition-colors"
            title="Edit entry"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button
            v-if="showDelete"
            @click="$emit('delete', entry.id)"
            :disabled="deleting"
            class="p-1.5 text-text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
            :title="deleting ? 'Deleting...' : 'Delete entry'"
          >
            <svg v-if="deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  entry: {
    type: Object,
    required: true
  },
  showEdit: {
    type: Boolean,
    default: true
  },
  showDelete: {
    type: Boolean,
    default: true
  },
  deleting: {
    type: Boolean,
    default: false
  }
})

defineEmits(['edit', 'delete'])

const formatDuration = (minutes) => {
  if (!minutes) return '0h 0m'
  const hours = Math.floor(minutes / 60)
  const mins = Math.floor(minutes % 60)
  return `${hours}h ${mins}m`
}

const formatTimeRange = (start, end) => {
  const startDate = new Date(start)
  const dateStr = startDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })

  const startTime = startDate.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  if (!end) {
    return `${dateStr} • ${startTime} - Running`
  }

  const endDate = new Date(end)
  const endTime = endDate.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  return `${dateStr} • ${startTime} - ${endTime}`
}
</script>

<style scoped>
/* TimeEntryCard styles */
</style>
