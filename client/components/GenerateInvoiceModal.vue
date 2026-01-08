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
      <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-text-primary">Generate Invoice</h3>
          <button
            @click="close"
            class="text-text-secondary hover:text-text-primary"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Client Filter -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-text-primary mb-1">Filter by Client</label>
          <select
            v-model="selectedClientId"
            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
          >
            <option value="">All Clients</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </select>
        </div>

        <!-- Unbilled Time Entries -->
        <div class="mb-6">
          <div class="flex justify-between items-center mb-3">
            <h4 class="font-semibold text-text-primary">
              Select Time Entries
            </h4>
            <div class="text-sm text-text-secondary">
              {{ selectedEntries.length }} selected
            </div>
          </div>

          <div v-if="loading" class="text-center py-8 text-text-secondary">
            Loading unbilled entries...
          </div>

          <div v-else-if="filteredEntries.length === 0" class="text-center py-8 text-text-secondary border border-border-light rounded-lg">
            {{ selectedClientId ? 'No unbilled entries for this client.' : 'No unbilled time entries available.' }}
          </div>

          <div v-else class="border border-border-light rounded-lg overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      @change="toggleAll"
                      class="w-4 h-4 text-link border-border-light rounded"
                    />
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary">Date</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary">Client</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary">Project</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary">Description</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Hours</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Rate</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border-light">
                <tr v-for="entry in filteredEntries" :key="entry.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <input
                      type="checkbox"
                      :checked="selectedEntries.includes(entry.id)"
                      @change="toggleEntry(entry.id)"
                      class="w-4 h-4 text-link border-border-light rounded"
                    />
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary">
                    {{ formatDate(entry.started_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary">
                    {{ entry.project?.client?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary">
                    {{ entry.project?.name || 'N/A' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-secondary">
                    {{ entry.description || '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary text-right">
                    {{ formatHours(entry.duration_minutes) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary text-right">
                    ${{ formatAmount(entry.project?.client?.hourly_rate || 0) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-text-primary text-right">
                    ${{ formatAmount(calculateAmount(entry)) }}
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="selectedEntries.length > 0" class="bg-gray-50 font-semibold">
                <tr>
                  <td colspan="7" class="px-4 py-3 text-right text-text-primary">
                    Total:
                  </td>
                  <td class="px-4 py-3 text-right text-text-primary">
                    ${{ formatAmount(totalAmount) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
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
            @click="generate"
            :disabled="selectedEntries.length === 0 || generating"
            class="btn-accent px-4 py-2 text-sm font-medium text-text-primary rounded-md disabled:opacity-50"
          >
            <span class="relative z-[1]">{{ generating ? 'Generating...' : `Generate invoice ($${formatAmount(totalAmount)})` }}</span>
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

const emit = defineEmits(['close', 'generated'])

const api = useApi()
const loading = ref(false)
const generating = ref(false)
const unbilledEntries = ref([])
const clients = ref([])
const selectedEntries = ref([])
const selectedClientId = ref('')

const filteredEntries = computed(() => {
  if (!selectedClientId.value) {
    return unbilledEntries.value
  }
  return unbilledEntries.value.filter(entry =>
    entry.project?.client_id == selectedClientId.value
  )
})

const allSelected = computed(() => {
  return filteredEntries.value.length > 0 &&
    filteredEntries.value.every(entry => selectedEntries.value.includes(entry.id))
})

const totalAmount = computed(() => {
  return unbilledEntries.value
    .filter(entry => selectedEntries.value.includes(entry.id))
    .reduce((sum, entry) => sum + calculateAmount(entry), 0)
})

const calculateAmount = (entry) => {
  const hours = entry.duration_minutes / 60
  const rate = entry.project?.client?.hourly_rate || 0
  return hours * rate
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatHours = (minutes) => {
  if (!minutes) return '0.00'
  return (minutes / 60).toFixed(2)
}

const formatAmount = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2)
}

const toggleEntry = (id) => {
  const index = selectedEntries.value.indexOf(id)
  if (index > -1) {
    selectedEntries.value.splice(index, 1)
  } else {
    selectedEntries.value.push(id)
  }
}

const toggleAll = () => {
  if (allSelected.value) {
    // Deselect all filtered entries
    selectedEntries.value = selectedEntries.value.filter(
      id => !filteredEntries.value.find(entry => entry.id === id)
    )
  } else {
    // Select all filtered entries
    const filteredIds = filteredEntries.value.map(entry => entry.id)
    selectedEntries.value = [...new Set([...selectedEntries.value, ...filteredIds])]
  }
}

const fetchUnbilledEntries = async () => {
  try {
    loading.value = true
    const entries = await api.api('/time-entries')
    unbilledEntries.value = entries.filter(entry =>
      entry.is_billable && !entry.invoice_id
    )
  } catch (error) {
    console.error('Failed to fetch unbilled entries:', error)
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

const generate = async () => {
  if (selectedEntries.value.length === 0 || generating.value) return

  try {
    generating.value = true

    // Get the client_id from the first selected entry
    const firstSelectedEntry = unbilledEntries.value.find(
      entry => selectedEntries.value.includes(entry.id)
    )

    if (!firstSelectedEntry?.project?.client_id) {
      alert('Unable to determine client for selected entries.')
      generating.value = false
      return
    }

    const clientId = firstSelectedEntry.project.client_id

    // Verify all selected entries are for the same client
    const allSameClient = unbilledEntries.value
      .filter(entry => selectedEntries.value.includes(entry.id))
      .every(entry => entry.project?.client_id === clientId)

    if (!allSameClient) {
      alert('All selected entries must be for the same client.')
      generating.value = false
      return
    }

    await api.api('/invoices/generate', {
      method: 'POST',
      body: {
        client_id: clientId,
        time_entry_ids: selectedEntries.value
      }
    })

    emit('generated')
    close()
  } catch (error) {
    console.error('Failed to generate invoice:', error)
    alert('Failed to generate invoice. Please try again.')
  } finally {
    generating.value = false
  }
}

const close = () => {
  selectedEntries.value = []
  selectedClientId.value = ''
  emit('close')
}

watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    fetchUnbilledEntries()
    fetchClients()
  }
})
</script>
