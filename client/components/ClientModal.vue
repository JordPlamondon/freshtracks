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
            {{ client ? 'Edit Client' : 'New Client' }}
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
          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">
              Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              placeholder="Client name"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
              placeholder="client@example.com"
            />
          </div>

          <!-- Hourly Rate -->
          <div>
            <label class="block text-sm font-medium text-text-primary mb-1">Hourly Rate</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-text-secondary">$</span>
              <input
                v-model.number="form.hourly_rate"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-7 pr-3 py-2 border border-border-light rounded-md focus:outline-none"
                placeholder="0.00"
              />
            </div>
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
              <span class="relative z-[1]">{{ saving ? 'Saving...' : (client ? 'Save changes' : 'Create client') }}</span>
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
  client: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'save'])

const form = ref({
  name: '',
  email: '',
  hourly_rate: null,
  status: 'active'
})

const { saving, save: saveForm } = useModalForm({
  endpoint: '/clients',
  entityName: 'client',
  getFormData: () => ({
    name: form.value.name,
    email: form.value.email || null,
    hourly_rate: form.value.hourly_rate || null,
    status: form.value.status
  }),
  entityId: () => props.client?.id
})

watch(() => props.client, (client) => {
  if (client) {
    form.value = {
      name: client.name || '',
      email: client.email || '',
      hourly_rate: client.hourly_rate || null,
      status: client.status || 'active'
    }
  } else {
    form.value = {
      name: '',
      email: '',
      hourly_rate: null,
      status: 'active'
    }
  }
}, { immediate: true })

const close = () => {
  emit('close')
}

const save = () => saveForm(emit)
</script>
