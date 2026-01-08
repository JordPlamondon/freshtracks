<template>
  <div class="card bg-white rounded-lg p-3 md:p-4 shadow-[0_1px_2px_rgba(0,0,0,0.12)] hover:shadow-md transition-shadow">
    <div class="flex justify-between items-start gap-3">
      <div class="flex-1 min-w-0">
        <!-- Project Name -->
        <div class="mb-2">
          <span class="text-sm md:text-base font-medium text-text-primary break-words">{{ project.name }}</span>
        </div>

        <!-- Badges -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-text-secondary">
            {{ project.client?.name || 'No Client' }}
          </span>
          <span
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
              project.status === 'active'
                ? 'bg-green-50 text-green-700 border border-green-200'
                : 'bg-gray-100 text-text-secondary'
            ]"
          >
            {{ project.status === 'active' ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <!-- Description -->
        <p v-if="project.description" class="text-xs md:text-sm text-text-secondary mt-2 line-clamp-2">
          {{ project.description }}
        </p>

        <!-- Hourly Rate -->
        <div v-if="project.client?.hourly_rate" class="text-xs text-text-secondary mt-2">
          Rate: <span class="font-medium text-text-primary">${{ project.client.hourly_rate }}/hr</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col md:flex-row items-center gap-1 flex-shrink-0">
        <button
          v-if="showEdit"
          @click="$emit('edit', project)"
          class="p-2 md:p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded transition-colors"
          title="Edit project"
        >
          <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </button>
        <button
          v-if="showDelete"
          @click="$emit('delete', project.id)"
          :disabled="deleting"
          class="p-2 md:p-1.5 text-text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
          :title="deleting ? 'Deleting...' : 'Delete project'"
        >
          <svg v-if="deleting" class="w-5 h-5 md:w-4 md:h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  project: {
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
</script>

