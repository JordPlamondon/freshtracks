<template>
  <div>
    <h1 class="text-2xl font-bold text-text-primary mb-6">Dashboard</h1>

    <!-- Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

      <!-- Active Timer Card (spans 2 columns) -->
      <div class="md:col-span-2 bg-[#f1f0ee] rounded-xl p-2">
        <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
          Active Timer
        </div>

        <div class="bg-white rounded-lg p-6">
          <!-- Empty State -->
          <div v-if="!activeTimer" class="flex flex-col items-center justify-center py-8">
          <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="9" stroke-width="2"/>
              <line x1="12" y1="12" x2="12" y2="8" stroke-width="2" stroke-linecap="round"/>
              <line x1="12" y1="12" x2="15" y2="15" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="text-text-secondary text-sm mb-4">No timer running</p>
          <NuxtLink
            to="/time-tracking"
            class="btn-accent px-4 py-2 text-sm font-medium text-text-primary rounded-md"
          >
            <span class="relative z-[1]">Start tracking</span>
          </NuxtLink>
        </div>

        <!-- Active Timer Display -->
        <div v-else>
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="text-4xl font-mono font-bold text-[#56c97b] mb-2">
                {{ formattedDuration }}
              </div>
              <div class="text-lg font-medium text-text-primary mb-1">
                {{ activeTimer.description || 'No description' }}
              </div>
              <div class="flex items-center gap-2 text-sm text-text-secondary">
                <div
                  class="w-2 h-2 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: getClientColor(activeTimer.project?.client?.name) }"
                ></div>
                <span>{{ activeTimer.project?.client?.name || 'No Client' }}</span>
                <span>•</span>
                <span>{{ activeTimer.project?.name || 'No Project' }}</span>
              </div>
            </div>

            <!-- Stop Button -->
            <button
              @click="stopTimer"
              :disabled="stopping"
              class="w-14 h-14 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-colors disabled:opacity-50"
              title="Stop timer"
            >
              <svg v-if="stopping" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                <rect x="6" y="6" width="12" height="12"/>
              </svg>
            </button>
          </div>
          </div>
        </div>
      </div>

      <!-- Time Summary Card -->
      <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
        <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
          Time Summary
        </div>

        <div class="bg-white rounded-lg p-6 flex-1">
          <div class="space-y-4">
          <!-- Today -->
          <div class="flex items-center justify-between">
            <span class="text-sm text-text-secondary">Today</span>
            <span class="text-lg font-semibold text-text-primary">{{ formatDurationHM(todayMinutes) }}</span>
          </div>

          <!-- This Week -->
          <div class="flex items-center justify-between">
            <span class="text-sm text-text-secondary">This Week</span>
            <span class="text-lg font-semibold text-text-primary">{{ formatDurationHM(weekMinutes) }}</span>
          </div>

          <!-- This Month -->
          <div class="flex items-center justify-between">
            <span class="text-sm text-text-secondary">This Month</span>
            <span class="text-lg font-semibold text-text-primary">{{ formatDurationHM(monthMinutes) }}</span>
          </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity Card (spans 2 columns) -->
      <div class="md:col-span-2 bg-[#f1f0ee] rounded-xl p-2">
        <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
          Recent Activity
        </div>

        <div class="bg-white rounded-lg p-6">
          <div v-if="loading" class="text-center py-8 text-text-secondary text-sm">
          Loading...
        </div>

        <div v-else-if="recentEntries.length === 0" class="text-center py-8 text-text-secondary text-sm">
          No recent activity
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="entry in recentEntries"
            :key="entry.id"
            class="flex items-center justify-between py-2 border-b border-border-light last:border-0"
          >
            <div class="flex items-center gap-3 flex-1 min-w-0">
              <div
                class="w-[0.175rem] h-6 rounded-full flex-shrink-0"
                :style="{ backgroundColor: getClientColor(entry.project?.client?.name) }"
              ></div>
              <div class="min-w-0 flex-1">
                <div class="text-sm font-medium text-text-primary truncate">
                  {{ entry.description || 'No description' }}
                </div>
                <div class="text-xs text-text-secondary truncate">
                  {{ entry.project?.client?.name || 'No Client' }} • {{ entry.project?.name || 'No Project' }}
                </div>
              </div>
            </div>
            <div class="flex flex-col items-end ml-4 flex-shrink-0">
              <div class="text-sm font-mono font-semibold text-text-primary">
                {{ formatDurationHM(entry.duration_minutes) }}
              </div>
              <div class="text-xs text-text-secondary">
                {{ formatTimestamp(entry.started_at) }}
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>

      <!-- Action Items Card -->
      <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
        <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
          Action Items
        </div>

        <div class="bg-white rounded-lg p-6 flex-1">
          <div class="space-y-4">
          <!-- Pending Invoices -->
          <NuxtLink
            to="/invoices"
            class="flex items-start gap-3 group"
          >
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="text-sm font-medium text-text-primary group-hover:text-accent transition-colors">
                {{ pendingInvoicesCount }} invoice{{ pendingInvoicesCount !== 1 ? 's' : '' }} pending
              </div>
              <div class="text-xs text-text-secondary">
                ${{ formatAmount(pendingInvoicesTotal) }}
              </div>
            </div>
          </NuxtLink>

          <!-- Unbilled Hours -->
          <NuxtLink
            to="/invoices"
            class="flex items-start gap-3 group"
          >
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="text-sm font-medium text-text-primary group-hover:text-accent transition-colors">
                {{ formatDurationHM(unbilledMinutes) }} unbilled
              </div>
              <div class="text-xs text-text-secondary">
                Ready to invoice
              </div>
            </div>
          </NuxtLink>
          </div>
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
const { currentTime } = useCurrentTime()

const loading = ref(true)
const stopping = ref(false)
const entries = ref([])
const invoices = ref([])
const activeTimer = ref(null)

const fetchActiveTimer = async () => {
  try {
    const data = await api.api('/active-timer')
    activeTimer.value = data && data.id ? data : null
  } catch (error) {
    activeTimer.value = null
  }
}

const fetchEntries = async () => {
  try {
    entries.value = await api.api('/time-entries')
  } catch (error) {
    console.error('Failed to fetch entries:', error)
  }
}

const fetchInvoices = async () => {
  try {
    invoices.value = await api.api('/invoices')
  } catch (error) {
    console.error('Failed to fetch invoices:', error)
  }
}

const stopTimer = async () => {
  if (!activeTimer.value || stopping.value) return

  try {
    stopping.value = true
    await api.api(`/time-entries/${activeTimer.value.id}/stop`, {
      method: 'POST'
    })
    activeTimer.value = null
    await fetchEntries()
    window.dispatchEvent(new Event('timer-stopped'))
  } catch (error) {
    console.error('Failed to stop timer:', error)
  } finally {
    stopping.value = false
  }
}

const formattedDuration = computed(() => {
  if (!activeTimer.value) return '0:00:00'

  const sessionStart = new Date(activeTimer.value.resumed_at || activeTimer.value.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
  const accumulatedSeconds = Math.floor((activeTimer.value.duration_minutes || 0) * 60)
  const totalSeconds = accumulatedSeconds + currentSessionSeconds

  const hours = Math.floor(totalSeconds / 3600)
  const mins = Math.floor((totalSeconds % 3600) / 60)
  const secs = Math.floor(totalSeconds % 60)

  return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
})

const getTodayStr = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
}

const getWeekStart = () => {
  const today = new Date()
  const day = today.getDay()
  const diff = today.getDate() - (day === 0 ? 6 : day - 1)
  const monday = new Date(today)
  monday.setDate(diff)
  monday.setHours(0, 0, 0, 0)
  return monday
}

const getMonthStart = () => {
  const today = new Date()
  return new Date(today.getFullYear(), today.getMonth(), 1)
}
const todayMinutes = computed(() => {
  const todayStr = getTodayStr()
  return entries.value
    .filter(entry => {
      const date = new Date(entry.started_at)
      const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
      return entryDate === todayStr && entry.stopped_at
    })
    .reduce((sum, entry) => sum + (entry.duration_minutes || 0), 0)
})

const weekMinutes = computed(() => {
  const weekStart = getWeekStart()
  return entries.value
    .filter(entry => {
      const entryDate = new Date(entry.started_at)
      return entryDate >= weekStart && entry.stopped_at
    })
    .reduce((sum, entry) => sum + (entry.duration_minutes || 0), 0)
})

const monthMinutes = computed(() => {
  const monthStart = getMonthStart()
  return entries.value
    .filter(entry => {
      const entryDate = new Date(entry.started_at)
      return entryDate >= monthStart && entry.stopped_at
    })
    .reduce((sum, entry) => sum + (entry.duration_minutes || 0), 0)
})

const recentEntries = computed(() => {
  return entries.value
    .filter(entry => entry.stopped_at)
    .sort((a, b) => new Date(b.started_at) - new Date(a.started_at))
    .slice(0, 5)
})

const pendingInvoicesCount = computed(() => {
  return invoices.value.filter(inv => inv.status === 'draft' || inv.status === 'sent').length
})

const pendingInvoicesTotal = computed(() => {
  return invoices.value
    .filter(inv => inv.status === 'draft' || inv.status === 'sent')
    .reduce((sum, inv) => sum + parseFloat(inv.total_amount || 0), 0)
})

const unbilledMinutes = computed(() => {
  return entries.value
    .filter(entry => entry.stopped_at && entry.is_billable && !entry.invoice_item_id)
    .reduce((sum, entry) => sum + (entry.duration_minutes || 0), 0)
})

const formatDurationHM = (minutes) => {
  if (!minutes) return '0h'
  const hours = Math.floor(minutes / 60)
  const mins = Math.floor(minutes % 60)
  if (hours === 0) return `${mins}m`
  if (mins === 0) return `${hours}h`
  return `${hours}h ${mins}m`
}

const formatAmount = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatTimestamp = (dateStr) => {
  const date = new Date(dateStr)
  const now = new Date()
  const todayStr = getTodayStr()

  const entryDateStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

  const time = date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  if (entryDateStr === todayStr) {
    return `Today ${time}`
  }

  const yesterday = new Date(now)
  yesterday.setDate(yesterday.getDate() - 1)
  const yesterdayStr = `${yesterday.getFullYear()}-${String(yesterday.getMonth() + 1).padStart(2, '0')}-${String(yesterday.getDate()).padStart(2, '0')}`

  if (entryDateStr === yesterdayStr) {
    return `Yesterday ${time}`
  }

  const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  return `${dayNames[date.getDay()]} ${time}`
}

const getClientColor = (clientName) => {
  if (!clientName) return '#cbd5e0'

  const colors = [
    '#7a9ec2', // slate blue
    '#8fb5a3', // sage green
    '#c4a67c', // warm sand
    '#a89cc4', // soft purple
    '#c49a9a', // dusty rose
    '#7eb8b8', // teal
    '#b8a07a', // warm taupe
    '#9ab4c4'  // steel blue
  ]

  let hash = 0
  for (let i = 0; i < clientName.length; i++) {
    hash = clientName.charCodeAt(i) + ((hash << 5) - hash)
  }

  return colors[Math.abs(hash) % colors.length]
}

const handleTimerStarted = () => {
  fetchActiveTimer()
  fetchEntries()
}

const handleTimerStopped = () => {
  fetchActiveTimer()
  fetchEntries()
}

const handleWsTimerStarted = (event) => {
  activeTimer.value = event.detail
  const exists = entries.value.some(e => e.id === event.detail.id)
  if (!exists) {
    entries.value.unshift(event.detail)
  }
}

const handleWsTimerStopped = (event) => {
  activeTimer.value = null
  const index = entries.value.findIndex(e => e.id === event.detail.id)
  if (index !== -1) {
    entries.value[index] = event.detail
  }
}

const handleWsTimerDeleted = (event) => {
  const entryId = event.detail
  if (activeTimer.value && activeTimer.value.id === entryId) {
    activeTimer.value = null
  }
  entries.value = entries.value.filter(e => e.id !== entryId)
}

onMounted(async () => {
  window.addEventListener('timer-started', handleTimerStarted)
  window.addEventListener('timer-stopped', handleTimerStopped)
  window.addEventListener('ws-timer-started', handleWsTimerStarted)
  window.addEventListener('ws-timer-stopped', handleWsTimerStopped)
  window.addEventListener('ws-timer-deleted', handleWsTimerDeleted)

  await Promise.all([
    fetchActiveTimer(),
    fetchEntries(),
    fetchInvoices()
  ])

  loading.value = false
})

onUnmounted(() => {
  window.removeEventListener('timer-started', handleTimerStarted)
  window.removeEventListener('timer-stopped', handleTimerStopped)
  window.removeEventListener('ws-timer-started', handleWsTimerStarted)
  window.removeEventListener('ws-timer-stopped', handleWsTimerStopped)
  window.removeEventListener('ws-timer-deleted', handleWsTimerDeleted)
})
</script>
