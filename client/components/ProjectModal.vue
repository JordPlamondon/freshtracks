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
          <h3 class="text-lg font-semibold text-text-primary">
            {{ project ? 'Edit Project' : 'New Project' }}
          </h3>
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
          <!-- Client Selection -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">
              Client <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.client_id"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
            >
              <option value="">Select client...</option>
              <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.name }}
              </option>
            </select>
          </div>

          <!-- Project Name -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">
              Project Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              placeholder="Project name"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              placeholder="What is this project about?"
            ></textarea>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">
              Status <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.status"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
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
              <span class="relative z-[1]">{{ saving ? 'Saving...' : (project ? 'Save changes' : 'Create project') }}</span>
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
  project: {
    type: Object,
    default: null
  },
  clients: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'save'])

const api = useApi()
const saving = ref(false)

const form = ref({
  client_id: '',
  name: '',
  description: '',
  status: 'active'
})

watch(() => props.project, (project) => {
  if (project) {
    form.value = {
      client_id: project.client_id || '',
      name: project.name || '',
      description: project.description || '',
      status: project.status || 'active'
    }
  } else {
    form.value = {
      client_id: '',
      name: '',
      description: '',
      status: 'active'
    }
  }
}, { immediate: true })

const close = () => {
  emit('close')
}

const save = async () => {
  if (saving.value) return

  try {
    saving.value = true

    const data = {
      client_id: form.value.client_id,
      name: form.value.name,
      description: form.value.description || null,
      status: form.value.status
    }

    if (props.project) {
      await api.api(`/projects/${props.project.id}`, {
        method: 'PUT',
        body: data
      })
    } else {
      await api.api('/projects', {
        method: 'POST',
        body: data
      })
    }

    emit('save')
    close()
  } catch (error) {
    console.error('Failed to save project:', error)
    alert('Failed to save project. Please try again.')
  } finally {
    saving.value = false
  }
}
</script>
