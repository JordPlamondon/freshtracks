<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Invoices</h1>
      <button
        @click="openGenerateModal"
        class="btn-accent px-3 md:px-4 py-2 text-xs md:text-sm font-medium text-text-primary rounded-md whitespace-nowrap"
      >
        <span class="relative z-[1]">
          <span class="hidden sm:inline">+ Generate invoice</span>
          <span class="sm:hidden">+ New</span>
        </span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white">
      <div class="flex items-center gap-2 md:gap-3">
        <!-- Status Filter Icon -->
        <div class="relative">
          <button
            @click="toggleStatusPopover"
            :class="[
              'p-2 rounded-md transition-colors',
              statusFilter !== 'all'
                ? 'bg-accent text-text-primary'
                : showStatusPopover
                  ? 'bg-gray-100 text-text-secondary'
                  : 'text-text-secondary hover:bg-gray-100'
            ]"
            :title="statusFilter !== 'all' ? statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) : 'Filter by status'"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
          </button>

          <!-- Status Popover -->
          <div
            v-if="showStatusPopover"
            class="absolute top-full mt-2 left-0 bg-white border border-border-light rounded-md shadow-lg z-50 w-48"
            @click.stop
          >
            <div class="p-2 border-b border-border-light">
              <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-2 py-1">
                Filter by Status
              </div>
            </div>
            <div class="p-2">
              <button
                v-if="statusFilter !== 'all'"
                @click="selectStatusFilter('all')"
                class="w-full text-left px-3 py-2 rounded-md text-sm transition-colors text-red-600 hover:bg-red-50 mb-1 border-b border-border-light pb-2"
              >
                Clear filter
              </button>
              <button
                @click="selectStatusFilter('draft')"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  statusFilter === 'draft' ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
                ]"
              >
                Draft
              </button>
              <button
                @click="selectStatusFilter('sent')"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  statusFilter === 'sent' ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
                ]"
              >
                Sent
              </button>
              <button
                @click="selectStatusFilter('paid')"
                :class="[
                  'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
                  statusFilter === 'paid' ? 'bg-gray-50 text-text-primary font-medium' : 'text-text-primary hover:bg-gray-50'
                ]"
              >
                Paid
              </button>
            </div>
          </div>
        </div>

        <!-- Search Input -->
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-full px-3 py-2 text-sm border border-border-light rounded-md focus:outline-none"
          />
        </div>
      </div>
    </div>

    <!-- Invoices List -->
    <div class="bg-white rounded-lg">
      <div class="p-4 pb-[0.35rem] bg-[#f1f0ee] rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
            All Invoices
          </div>
          <div class="text-xs md:text-sm text-text-secondary">
            Total: <span class="font-semibold text-text-primary">{{ filteredInvoices.length }}</span>
          </div>
        </div>
      </div>

      <div class="p-2 bg-[#f1f0ee] rounded-b-lg">
        <div v-if="loading" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          Loading...
        </div>

        <div v-else-if="filteredInvoices.length === 0" class="text-center py-8 text-text-secondary bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)]">
          {{ searchQuery || statusFilter !== 'all' ? 'No invoices found matching your filters.' : 'No invoices yet. Generate your first invoice to get started.' }}
        </div>

        <div v-else class="space-y-1">
          <InvoiceCard
            v-for="invoice in filteredInvoices"
            :key="invoice.id"
            :invoice="invoice"
            :deleting="deletingId === invoice.id"
            @view="viewInvoice"
            @update-status="updateStatus"
            @delete="deleteInvoice"
          />
        </div>
      </div>
    </div>

    <!-- Generate Invoice Modal -->
    <GenerateInvoiceModal
      :isOpen="showGenerateModal"
      @close="closeGenerateModal"
      @generated="handleGenerated"
    />

    <!-- View Invoice Modal -->
    <ViewInvoiceModal
      :isOpen="showViewModal"
      :invoice="viewingInvoice"
      @close="closeViewModal"
    />
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const api = useApi()
const { items: invoices, loading, deletingId, fetch: fetchInvoices, remove } = useResource('/invoices')
const showGenerateModal = ref(false)
const showViewModal = ref(false)
const viewingInvoice = ref(null)
const searchQuery = ref('')
const statusFilter = ref('all')
const showStatusPopover = ref(false)

const closePopovers = () => {
  showStatusPopover.value = false
}

const toggleStatusPopover = (event) => {
  event.stopPropagation()
  showStatusPopover.value = !showStatusPopover.value
}

const selectStatusFilter = (value) => {
  statusFilter.value = value
  showStatusPopover.value = false
}

const filteredInvoices = computed(() => {
  let filtered = invoices.value

  // Filter by status
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(invoice => invoice.status === statusFilter.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(invoice =>
      invoice.invoice_number.toLowerCase().includes(query) ||
      (invoice.client?.name && invoice.client.name.toLowerCase().includes(query))
    )
  }

  return filtered.sort((a, b) => new Date(b.invoice_date) - new Date(a.invoice_date))
})

const viewInvoice = async (invoice) => {
  try {
    // Fetch full invoice details with items
    const fullInvoice = await api.api(`/invoices/${invoice.id}`)
    viewingInvoice.value = fullInvoice
    showViewModal.value = true
  } catch (error) {
    console.error('Failed to fetch invoice details:', error)
    alert('Failed to load invoice details. Please try again.')
  }
}

const updateStatus = async (invoice) => {
  let newStatus = 'draft'

  if (invoice.status === 'draft') {
    newStatus = 'sent'
  } else if (invoice.status === 'sent') {
    newStatus = 'paid'
  }

  try {
    await api.api(`/invoices/${invoice.id}`, {
      method: 'PUT',
      body: {
        status: newStatus
      }
    })

    // Update local state
    const index = invoices.value.findIndex(inv => inv.id === invoice.id)
    if (index > -1) {
      invoices.value[index].status = newStatus
    }
  } catch (error) {
    console.error('Failed to update invoice status:', error)
    alert('Failed to update invoice status. Please try again.')
  }
}

const deleteInvoice = async (id) => {
  const invoice = invoices.value.find(inv => inv.id === id)
  await remove(id, `invoice #${invoice?.invoice_number}`)
}

const openGenerateModal = () => {
  showGenerateModal.value = true
}

const closeGenerateModal = () => {
  showGenerateModal.value = false
}

const handleGenerated = () => {
  fetchInvoices()
  closeGenerateModal()
}

const closeViewModal = () => {
  showViewModal.value = false
  viewingInvoice.value = null
}

onMounted(() => {
  fetchInvoices()
  document.addEventListener('click', closePopovers)
})

onUnmounted(() => {
  document.removeEventListener('click', closePopovers)
})
</script>
