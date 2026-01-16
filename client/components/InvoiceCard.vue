<template>
  <div class="invoice-card bg-white rounded-lg p-3 md:p-4 shadow-[0_1px_2px_rgba(0,0,0,0.12)] hover:shadow-md transition-shadow">
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-3">
      <div class="flex-1 min-w-0">
        <!-- Client Name -->
        <div class="mb-2">
          <span class="text-sm md:text-base font-medium text-text-primary break-words">{{ invoice.client?.name || 'No Client' }}</span>
        </div>

        <!-- Badges -->
        <div class="flex flex-wrap items-center gap-2 mb-2">
          <span
            :class="[
              'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
              statusColor(invoice.status)
            ]"
          >
            {{ capitalize(invoice.status) }}
          </span>
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
            {{ invoice.items?.length || 0 }} {{ invoice.items?.length === 1 ? 'Item' : 'Items' }}
          </span>
        </div>

        <!-- Invoice Details -->
        <div class="space-y-0.5 mt-2">
          <p class="text-xs md:text-sm text-text-secondary">
            Invoice #{{ invoice.invoice_number }}
          </p>
          <p class="text-xs md:text-sm text-text-secondary">
            {{ formatDate(invoice.invoice_date) }}
          </p>
          <p v-if="invoice.due_date" class="text-xs md:text-sm text-text-secondary">
            Due: {{ formatDate(invoice.due_date) }}
          </p>
        </div>
      </div>

      <!-- Amount & Actions -->
      <div class="flex md:flex-col items-center md:items-end justify-between md:justify-start gap-2 md:gap-2 flex-shrink-0">
        <div class="font-mono text-base md:text-lg font-semibold text-text-primary">
          ${{ formatAmount(invoice.total_amount) }}
        </div>

        <div class="flex items-center gap-1">
          <button
            v-if="showView"
            @click="$emit('view', invoice)"
            class="p-2 md:p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded transition-colors"
            title="View invoice"
          >
            <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button
            v-if="showUpdateStatus && invoice.status !== 'paid'"
            @click="$emit('update-status', invoice)"
            class="p-2 md:p-1.5 text-text-secondary hover:text-[#56c97b] hover:bg-green-50 rounded transition-colors"
            :title="getStatusAction(invoice.status)"
          >
            <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </button>
          <button
            v-if="showDelete"
            @click="$emit('delete', invoice.id)"
            :disabled="deleting"
            class="p-2 md:p-1.5 text-text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
            :title="deleting ? 'Deleting...' : 'Delete invoice'"
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
  </div>
</template>

<script setup>
const { formatAmount } = useFormatting()

const props = defineProps({
  invoice: {
    type: Object,
    required: true
  },
  showView: {
    type: Boolean,
    default: true
  },
  showUpdateStatus: {
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

defineEmits(['view', 'update-status', 'delete'])

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

const getStatusAction = (status) => {
  switch (status) {
    case 'draft':
      return 'Mark Sent'
    case 'sent':
      return 'Mark Paid'
    default:
      return 'Update'
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
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

</script>

