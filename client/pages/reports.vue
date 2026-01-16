<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Reports</h1>
      <div class="flex gap-2">
        <button
          @click="exportCSV"
          class="btn-accent px-4 py-2 text-xs md:text-sm font-medium text-text-primary rounded-md flex items-center gap-2"
        >
          <span class="relative z-[1] flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span class="hidden sm:inline">Export CSV</span>
            <span class="sm:hidden">Export</span>
          </span>
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg mb-4">
      <div class="flex flex-col md:flex-row md:flex-wrap md:items-center gap-3 md:gap-4">
        <!-- Date Range -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">From:</label>
          <input
            type="date"
            v-model="dateFrom"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none"
          />
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">To:</label>
          <input
            type="date"
            v-model="dateTo"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none"
          />
        </div>

        <!-- Client Filter -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">Client:</label>
          <select
            v-model="selectedClientId"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none md:min-w-[150px]"
          >
            <option :value="null">All Clients</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </select>
        </div>

        <!-- Project Filter -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">Project:</label>
          <select
            v-model="selectedProjectId"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none md:min-w-[150px]"
          >
            <option :value="null">All Projects</option>
            <option v-for="project in filteredProjects" :key="project.id" :value="project.id">
              {{ project.name }}
            </option>
          </select>
        </div>

        <!-- Billable Filter -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">Billable:</label>
          <select
            v-model="billableFilter"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none"
          >
            <option value="all">All</option>
            <option value="billable">Billable Only</option>
            <option value="non-billable">Non-billable Only</option>
          </select>
        </div>

        <!-- Group By -->
        <div class="flex items-center gap-2 w-full md:w-auto">
          <label class="text-xs md:text-sm text-text-secondary whitespace-nowrap">Group by:</label>
          <select
            v-model="groupBy"
            class="flex-1 md:flex-none px-3 py-2 text-xs md:text-sm border border-border-light rounded-md focus:outline-none"
          >
            <option value="day">Day</option>
            <option value="client">Client</option>
            <option value="project">Project</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Summary Bar -->
    <div class="bg-[#f1f0ee] rounded-lg p-4 mb-4">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6">
          <div>
            <span class="text-xs md:text-sm text-text-secondary">Total Hours: </span>
            <span class="text-base md:text-lg font-semibold text-text-primary">{{ formatHours(totalHours) }}</span>
          </div>
          <div>
            <span class="text-xs md:text-sm text-text-secondary">Revenue: </span>
            <span class="text-base md:text-lg font-semibold text-text-primary">${{ formatAmount(totalRevenue) }}</span>
          </div>
          <div>
            <span class="text-xs md:text-sm text-text-secondary">Entries: </span>
            <span class="text-base md:text-lg font-semibold text-text-primary">{{ filteredEntries.length }}</span>
          </div>
        </div>
        <div class="text-xs md:text-sm text-text-secondary">
          {{ dateRangeLabel }}
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)] p-12 text-center">
      <div class="text-text-secondary">Loading reports...</div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredEntries.length === 0" class="bg-white rounded-lg shadow-[0_1px_2px_rgba(0,0,0,0.12)] p-12 text-center">
      <svg class="w-12 h-12 mx-auto text-text-secondary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="text-text-secondary">No entries found for the selected filters.</p>
    </div>

    <!-- Grouped Results -->
    <div v-else class="space-y-4">
      <div
        v-for="group in groupedEntries"
        :key="group.key"
        class="bg-[#f1f0ee] rounded-lg p-2"
      >
        <div class="bg-white rounded-lg overflow-hidden">
          <!-- Group Header -->
          <div class="pt-2 pb-3 px-4 bg-[#f1f0ee] rounded-t-lg">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2 md:gap-3">
              <div
                v-if="groupBy === 'client'"
                class="w-2.5 md:w-3 h-2.5 md:h-3 rounded-full flex-shrink-0"
                :style="{ backgroundColor: getClientColor(group.label) }"
              ></div>
              <h3 class="text-xs font-semibold text-text-secondary uppercase tracking-wider">{{ group.label }}</h3>
              <span class="text-xs md:text-sm text-text-secondary">({{ group.entries.length }})</span>
            </div>
            <div class="flex items-center gap-3 md:gap-6 text-xs md:text-sm">
              <span class="text-text-secondary">
                <span class="font-semibold text-text-primary">{{ formatHours(group.totalHours) }}</span>
              </span>
              <span class="text-text-secondary">
                <span class="font-semibold text-text-primary">${{ formatAmount(group.totalRevenue) }}</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Mobile: Card-based View -->
        <div class="md:hidden divide-y divide-border-light rounded-b-lg">
          <div
            v-for="(entry, index) in group.entries"
            :key="entry.id"
            class="p-4"
            :class="{ 'rounded-t-lg': index === 0, 'rounded-b-lg': index === group.entries.length - 1 }"
          >
            <!-- Description & Billable -->
            <div class="mb-2">
              <div class="flex items-start gap-2 mb-1">
                <span class="text-sm font-medium text-text-primary flex-1">
                  {{ entry.description || 'No description' }}
                </span>
                <span
                  v-if="entry.is_billable"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200 flex-shrink-0"
                >
                  Billable
                </span>
              </div>
            </div>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-text-secondary mb-2">
              <div v-if="groupBy !== 'project'" class="flex items-center gap-1">
                <span class="text-text-secondary">Project:</span>
                <span class="text-text-primary">{{ entry.project?.name || 'No Project' }}</span>
              </div>
              <div v-if="groupBy !== 'client'" class="flex items-center gap-1.5">
                <div
                  class="w-2 h-2 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: getClientColor(entry.project?.client?.name) }"
                ></div>
                <span class="text-text-primary">{{ entry.project?.client?.name || 'No Client' }}</span>
              </div>
              <div v-if="groupBy !== 'day'">
                <span class="text-text-secondary">{{ formatDate(entry.started_at) }}</span>
              </div>
            </div>

            <!-- Duration & Amount -->
            <div class="flex items-center justify-between">
              <span class="text-sm font-mono font-semibold text-text-primary">
                {{ formatDuration(entry.duration_minutes) }}
              </span>
              <span class="text-sm font-semibold text-text-primary">
                {{ entry.is_billable ? '$' + formatAmount(calculateAmount(entry)) : '-' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Desktop: Table View -->
        <table class="hidden md:table w-full" style="border-collapse: separate; border-spacing: 0;">
          <thead>
            <tr class="border-b border-border-light">
              <th class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider rounded-tl-lg">
                Description
              </th>
              <th v-if="groupBy !== 'project'" class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
                Project
              </th>
              <th v-if="groupBy !== 'client'" class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
                Client
              </th>
              <th v-if="groupBy !== 'day'" class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
                Date
              </th>
              <th class="text-right py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
                Duration
              </th>
              <th class="text-right py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider rounded-tr-lg">
                Amount
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-light">
            <tr
              v-for="entry in group.entries"
              :key="entry.id"
              class="hover:bg-[#f1f0ee] transition-colors"
            >
              <!-- Description -->
              <td class="py-3 px-6">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-text-primary">
                    {{ entry.description || 'No description' }}
                  </span>
                  <span
                    v-if="entry.is_billable"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"
                  >
                    Billable
                  </span>
                </div>
              </td>

              <!-- Project -->
              <td v-if="groupBy !== 'project'" class="py-3 px-6">
                <span class="text-sm text-text-primary">
                  {{ entry.project?.name || 'No Project' }}
                </span>
              </td>

              <!-- Client -->
              <td v-if="groupBy !== 'client'" class="py-3 px-6">
                <div class="flex items-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: getClientColor(entry.project?.client?.name) }"
                  ></div>
                  <span class="text-sm text-text-primary">
                    {{ entry.project?.client?.name || 'No Client' }}
                  </span>
                </div>
              </td>

              <!-- Date -->
              <td v-if="groupBy !== 'day'" class="py-3 px-6">
                <span class="text-sm text-text-secondary">
                  {{ formatDate(entry.started_at) }}
                </span>
              </td>

              <!-- Duration -->
              <td class="py-3 px-6 text-right">
                <span class="text-sm font-mono text-text-primary">
                  {{ formatDuration(entry.duration_minutes) }}
                </span>
              </td>

              <!-- Amount -->
              <td class="py-3 px-6 text-right">
                <span class="text-sm text-text-primary">
                  {{ entry.is_billable ? '$' + formatAmount(calculateAmount(entry)) : '-' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const api = useApi()
const { formatHours, formatAmount, getClientColor } = useFormatting()
const { getDateStr } = useDateUtils()

const loading = ref(true)
const entries = ref([])
const clients = ref([])
const projects = ref([])

const dateFrom = ref('')
const dateTo = ref('')
const selectedClientId = ref(null)
const selectedProjectId = ref(null)
const billableFilter = ref('all')
const groupBy = ref('day')

onMounted(async () => {
  const today = new Date()
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(today.getDate() - 30)

  dateTo.value = formatDateISO(today)
  dateFrom.value = formatDateISO(thirtyDaysAgo)

  await fetchData()
  loading.value = false
})

const fetchData = async () => {
  try {
    const [entriesData, clientsData, projectsData] = await Promise.all([
      api.api('/time-entries'),
      api.api('/clients'),
      api.api('/projects')
    ])
    entries.value = entriesData
    clients.value = clientsData
    projects.value = projectsData
  } catch (error) {
    console.error('Failed to fetch data:', error)
  }
}

const filteredProjects = computed(() => {
  if (!selectedClientId.value) return projects.value
  return projects.value.filter(p => p.client_id === selectedClientId.value)
})

watch(selectedClientId, () => {
  selectedProjectId.value = null
})

const filteredEntries = computed(() => {
  return entries.value.filter(entry => {
    if (!entry.stopped_at) return false

    const entryDate = new Date(entry.started_at)
    const entryDateStr = formatDateISO(entryDate)

    if (dateFrom.value && entryDateStr < dateFrom.value) return false
    if (dateTo.value && entryDateStr > dateTo.value) return false

    if (selectedClientId.value && entry.project?.client_id !== selectedClientId.value) return false

    if (selectedProjectId.value && entry.project_id !== selectedProjectId.value) return false

    if (billableFilter.value === 'billable' && !entry.is_billable) return false
    if (billableFilter.value === 'non-billable' && entry.is_billable) return false

    return true
  })
})

const groupedEntries = computed(() => {
  const groups = {}

  filteredEntries.value.forEach(entry => {
    let key, label

    if (groupBy.value === 'day') {
      const date = new Date(entry.started_at)
      key = formatDateISO(date)
      label = formatDateDisplay(date)
    } else if (groupBy.value === 'client') {
      key = entry.project?.client?.id || 'no-client'
      label = entry.project?.client?.name || 'No Client'
    } else if (groupBy.value === 'project') {
      key = entry.project?.id || 'no-project'
      label = entry.project?.name || 'No Project'
    }

    if (!groups[key]) {
      groups[key] = {
        key,
        label,
        entries: [],
        totalMinutes: 0,
        totalRevenue: 0
      }
    }

    groups[key].entries.push(entry)
    groups[key].totalMinutes += entry.duration_minutes || 0
    if (entry.is_billable) {
      groups[key].totalRevenue += calculateAmount(entry)
    }
  })

  // Convert to array and add computed totals
  const groupArray = Object.values(groups).map(g => ({
    ...g,
    totalHours: g.totalMinutes / 60
  }))

  // Sort groups
  if (groupBy.value === 'day') {
    groupArray.sort((a, b) => b.key.localeCompare(a.key)) // Newest first
  } else {
    groupArray.sort((a, b) => b.totalMinutes - a.totalMinutes) // Most hours first
  }

  return groupArray
})

const totalHours = computed(() => {
  const minutes = filteredEntries.value.reduce((sum, e) => sum + (e.duration_minutes || 0), 0)
  return minutes / 60
})

const totalRevenue = computed(() => {
  return filteredEntries.value
    .filter(e => e.is_billable)
    .reduce((sum, e) => sum + calculateAmount(e), 0)
})

const dateRangeLabel = computed(() => {
  if (!dateFrom.value || !dateTo.value) return ''
  const from = new Date(dateFrom.value + 'T00:00:00')
  const to = new Date(dateTo.value + 'T00:00:00')
  return `${formatDateDisplay(from)} - ${formatDateDisplay(to)}`
})

// Calculate amount for an entry
const calculateAmount = (entry) => {
  const hours = (entry.duration_minutes || 0) / 60
  const rate = entry.project?.client?.hourly_rate || 0
  return hours * rate
}

const formatDateISO = (date) => getDateStr(date)

const formatDateDisplay = (date) => {
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const formatDuration = (minutes) => {
  if (!minutes) return '0:00'
  const h = Math.floor(minutes / 60)
  const m = Math.floor(minutes % 60)
  return `${h}:${String(m).padStart(2, '0')}`
}

// Export to CSV
const exportCSV = () => {
  const headers = ['Date', 'Description', 'Project', 'Client', 'Duration (hours)', 'Billable', 'Amount']
  const rows = filteredEntries.value.map(entry => {
    const date = new Date(entry.started_at)
    const hours = (entry.duration_minutes || 0) / 60
    const amount = entry.is_billable ? calculateAmount(entry) : 0

    return [
      formatDateISO(date),
      `"${(entry.description || '').replace(/"/g, '""')}"`,
      `"${(entry.project?.name || '').replace(/"/g, '""')}"`,
      `"${(entry.project?.client?.name || '').replace(/"/g, '""')}"`,
      hours.toFixed(2),
      entry.is_billable ? 'Yes' : 'No',
      amount.toFixed(2)
    ]
  })

  rows.push([])
  rows.push(['', '', '', 'TOTAL', totalHours.value.toFixed(2), '', totalRevenue.value.toFixed(2)])

  const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)

  link.setAttribute('href', url)
  link.setAttribute('download', `freshtracks-report-${dateFrom.value}-to-${dateTo.value}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
</script>
