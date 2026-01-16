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
      <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-text-primary">Invoice Details</h3>
          <button
            @click="close"
            class="text-text-secondary hover:text-text-primary"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="invoice" class="space-y-6">
          <!-- Invoice Header -->
          <div class="border-b border-border-light pb-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-text-secondary">Invoice Number</p>
                <p class="font-semibold text-text-primary">#{{ invoice.invoice_number }}</p>
              </div>
              <div>
                <p class="text-sm text-text-secondary">Status</p>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                    statusColor(invoice.status)
                  ]"
                >
                  {{ capitalize(invoice.status) }}
                </span>
              </div>
              <div>
                <p class="text-sm text-text-secondary">Client</p>
                <p class="font-semibold text-text-primary">{{ invoice.client?.name || 'N/A' }}</p>
                <p v-if="invoice.client?.email" class="text-sm text-text-secondary">{{ invoice.client.email }}</p>
              </div>
              <div>
                <p class="text-sm text-text-secondary">Invoice Date</p>
                <p class="font-semibold text-text-primary">{{ formatDate(invoice.invoice_date) }}</p>
                <p v-if="invoice.due_date" class="text-sm text-text-secondary">Due: {{ formatDate(invoice.due_date) }}</p>
              </div>
            </div>
          </div>

          <!-- Line Items -->
          <div>
            <h4 class="font-semibold text-text-primary mb-3">Line Items</h4>
            <div class="border border-border-light rounded-lg overflow-hidden">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-text-secondary">Description</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Hours</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Rate</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-text-secondary">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                  <tr v-for="item in invoice.items" :key="item.id">
                    <td class="px-4 py-3 text-sm text-text-primary">
                      {{ item.description || 'Time Entry' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-text-primary text-right">
                      {{ formatHours(item.hours) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-text-primary text-right">
                      ${{ formatAmount(item.rate) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-text-primary text-right">
                      ${{ formatAmount(item.amount) }}
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                  <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-text-primary">
                      Total:
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-text-primary">
                      ${{ formatAmount(invoice.total_amount) }}
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
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const { formatAmount } = useFormatting()

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  invoice: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const statusColor = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-gray-100 text-text-secondary'
    case 'sent':
      return 'bg-blue-50 text-blue-700 border border-blue-200'
    case 'paid':
      return 'bg-green-50 text-green-700 border border-green-200'
    default:
      return 'bg-gray-100 text-text-secondary'
  }
}

const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatHours = (hours) => {
  if (!hours) return '0.00'
  return parseFloat(hours).toFixed(2)
}

const close = () => {
  emit('close')
}
</script>
