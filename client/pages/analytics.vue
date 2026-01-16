<template>
  <div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <h1 class="text-xl md:text-2xl font-bold text-text-primary">Analytics</h1>

      <!-- Period Selector -->
      <div class="flex gap-2">
        <button
          v-for="period in periods"
          :key="period.value"
          @click="selectedPeriod = period.value"
          :class="[
            'flex-1 md:flex-none px-3 md:px-4 py-2 text-xs md:text-sm font-medium rounded-md transition-colors',
            selectedPeriod === period.value
              ? 'btn-accent text-text-primary'
              : 'bg-white border border-border-light text-text-secondary hover:bg-gray-50'
          ]"
        >
          <span :class="selectedPeriod === period.value ? 'relative z-[1]' : ''">
            {{ period.label }}
          </span>
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-text-secondary">
      Loading analytics...
    </div>

    <template v-else>
      <!-- Bento Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Total Hours Card -->
        <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Total Hours
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <div class="text-4xl font-bold text-text-primary mb-2">
            {{ formatHours(totalHours) }}
          </div>
          <div class="text-sm text-text-secondary">
            {{ periodLabel }}
          </div>
          <!-- Trend Indicator -->
          <div v-if="hoursTrend !== null" class="mt-2 flex items-center gap-1 text-xs">
            <svg v-if="hoursTrend > 0" class="w-3 h-3 text-[#56c97b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <svg v-else-if="hoursTrend < 0" class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
            <svg v-else class="w-3 h-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            <span :class="hoursTrend > 0 ? 'text-[#56c97b]' : hoursTrend < 0 ? 'text-red-500' : 'text-text-secondary'">
              {{ formatTrendPercent(hoursTrend) }} vs previous {{ selectedPeriod }} days
            </span>
          </div>
          <div class="mt-3 flex items-center gap-4 text-sm">
            <div>
              <span class="text-text-secondary">Entries: </span>
              <span class="font-semibold text-text-primary">{{ totalEntries }}</span>
            </div>
            <div>
              <span class="text-text-secondary">Avg/day: </span>
              <span class="font-semibold text-text-primary">{{ formatHours(avgHoursPerDay) }}</span>
            </div>
          </div>
          </div>
        </div>

        <!-- Billable Ratio Donut Chart -->
        <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Billable Ratio
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <ClientOnly>
              <apexchart
              v-if="billableChartSeries.length > 0"
              type="donut"
              height="180"
              :options="billableChartOptions"
              :series="billableChartSeries"
            />
            <div v-else class="flex items-center justify-center h-[180px] text-text-secondary text-sm">
              No data
            </div>
            </ClientOnly>
          </div>
        </div>

        <!-- Revenue Summary Card -->
        <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Revenue
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <div class="text-4xl font-bold text-text-primary mb-2">
            <RollingNumber :value="totalRevenue" :decimals="2" />
          </div>
          <div class="text-sm text-text-secondary">
            {{ periodLabel }}
          </div>
          <!-- Trend Indicator -->
          <div v-if="revenueTrend !== null" class="mt-2 flex items-center gap-1 text-xs">
            <svg v-if="revenueTrend > 0" class="w-3 h-3 text-[#56c97b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <svg v-else-if="revenueTrend < 0" class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
            <svg v-else class="w-3 h-3 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            <span :class="revenueTrend > 0 ? 'text-[#56c97b]' : revenueTrend < 0 ? 'text-red-500' : 'text-text-secondary'">
              {{ formatTrendPercent(revenueTrend) }} vs previous
            </span>
          </div>
          <div class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-text-secondary">Billable hours</span>
              <span class="font-semibold text-text-primary">{{ formatHours(billableHours) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-text-secondary">Avg rate</span>
              <span class="font-semibold text-text-primary">${{ formatAmount(avgHourlyRate) }}/hr</span>
            </div>
          </div>
          </div>
        </div>

        <!-- Hours by Client (spans 2 columns) -->
        <div class="md:col-span-2 bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Hours by Client
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <ClientOnly>
              <apexchart
              v-if="clientChartSeries.length > 0"
              type="bar"
              height="250"
              :options="clientChartOptions"
              :series="[{ name: 'Hours', data: clientChartSeries }]"
              />
              <div v-else class="flex items-center justify-center h-[250px] text-text-secondary text-sm">
                No data for selected period
              </div>
            </ClientOnly>
          </div>
        </div>

        <!-- Top Projects Card -->
        <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Top Projects
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <div v-if="topProjects.length === 0" class="text-center py-8 text-text-secondary text-sm">
            No data
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="(project, index) in topProjects"
              :key="project.name"
              class="flex items-center justify-between"
            >
              <div class="flex items-center gap-2 min-w-0 flex-1">
                <div
                  class="w-2 h-2 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: getClientColor(project.clientName) }"
                ></div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-medium text-text-primary truncate">
                    {{ project.name }}
                  </div>
                  <div class="text-xs text-text-secondary truncate">
                    {{ project.clientName }}
                  </div>
                </div>
              </div>
              <div class="text-sm font-semibold text-text-primary ml-2 flex-shrink-0">
                {{ formatHours(project.hours) }}
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- Hours by Project (spans 2 columns) -->
        <div class="md:col-span-2 bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Hours by Project
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <ClientOnly>
              <apexchart
              v-if="projectChartSeries.length > 0"
              type="bar"
              height="250"
              :options="projectChartOptions"
              :series="[{ name: 'Hours', data: projectChartSeries }]"
              />
              <div v-else class="flex items-center justify-center h-[250px] text-text-secondary text-sm">
                No data for selected period
              </div>
            </ClientOnly>
          </div>
        </div>

        <!-- Daily Breakdown Card -->
        <div class="bg-[#f1f0ee] rounded-xl p-2 flex flex-col">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider px-4 pt-2 pb-3">
            Daily Average
          </div>
          <div class="bg-white rounded-lg p-6 flex-1">
            <div v-if="dailyStats.length === 0" class="text-center py-8 text-text-secondary text-sm">
            No data
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="stat in dailyStats"
              :key="stat.day"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-text-secondary">{{ stat.day }}</span>
              <div class="flex items-center gap-2">
                <div class="w-20 h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-[#56c97b] rounded-full"
                    :style="{ width: `${(stat.hours / maxDailyHours) * 100}%` }"
                  ></div>
                </div>
                <span class="font-semibold text-text-primary w-16 text-right">{{ formatHours(stat.hours) }}</span>
              </div>
            </div>
          </div>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const api = useApi()
const { currentTime } = useCurrentTime()
const { settings, fetchSettings } = useSettings()
const { formatHours, formatAmount, getClientColor } = useFormatting()

const loading = ref(true)
const entries = ref([])
const clients = ref([])
const selectedPeriod = ref(30)
const activeTimer = ref(null)

const periods = [
  { label: 'Last 7 days', value: 7 },
  { label: 'Last 30 days', value: 30 },
  { label: 'Last 90 days', value: 90 }
]

const periodLabel = computed(() => {
  const period = periods.find(p => p.value === selectedPeriod.value)
  return period ? period.label : ''
})

const getStartDate = () => {
  const date = new Date()
  date.setDate(date.getDate() - selectedPeriod.value)
  date.setHours(0, 0, 0, 0)
  return date
}

const getPreviousPeriodRange = () => {
  const endDate = new Date()
  endDate.setDate(endDate.getDate() - selectedPeriod.value)
  endDate.setHours(0, 0, 0, 0)

  const startDate = new Date()
  startDate.setDate(startDate.getDate() - (selectedPeriod.value * 2))
  startDate.setHours(0, 0, 0, 0)

  return { startDate, endDate }
}

const filteredEntries = computed(() => {
  const startDate = getStartDate()
  return entries.value.filter(entry => {
    if (!entry.stopped_at) return false // Only completed entries
    const entryDate = new Date(entry.started_at)
    return entryDate >= startDate
  })
})

const previousPeriodEntries = computed(() => {
  const { startDate, endDate } = getPreviousPeriodRange()
  return entries.value.filter(entry => {
    if (!entry.stopped_at) return false
    const entryDate = new Date(entry.started_at)
    return entryDate >= startDate && entryDate < endDate
  })
})

const totalHours = computed(() => {
  const minutes = filteredEntries.value.reduce((sum, e) => sum + (e.duration_minutes || 0), 0)
  return minutes / 60
})

const previousPeriodHours = computed(() => {
  const minutes = previousPeriodEntries.value.reduce((sum, e) => sum + (e.duration_minutes || 0), 0)
  return minutes / 60
})

const hoursTrend = computed(() => {
  if (previousPeriodHours.value === 0) {
    return totalHours.value > 0 ? 100 : null // 100% increase if no previous data but current has data
  }
  return ((totalHours.value - previousPeriodHours.value) / previousPeriodHours.value) * 100
})

const totalEntries = computed(() => filteredEntries.value.length)

const avgHoursPerDay = computed(() => {
  if (selectedPeriod.value === 0) return 0
  return totalHours.value / selectedPeriod.value
})

const billableHours = computed(() => {
  const minutes = filteredEntries.value
    .filter(e => e.is_billable)
    .reduce((sum, e) => sum + (e.duration_minutes || 0), 0)
  return minutes / 60
})

const nonBillableHours = computed(() => {
  return totalHours.value - billableHours.value
})

const liveRevenue = computed(() => {
  if (!settings.value.show_live_revenue || !activeTimer.value) return 0

  const startDate = getStartDate()
  const timerStartDate = new Date(activeTimer.value.started_at)
  if (timerStartDate < startDate) return 0

  const sessionStart = new Date(activeTimer.value.resumed_at || activeTimer.value.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionMinutes = currentSessionMs / 1000 / 60

  const totalMinutes = (activeTimer.value.duration_minutes || 0) + currentSessionMinutes
  const hours = totalMinutes / 60

  const rate = activeTimer.value.project?.client?.hourly_rate || 0

  return activeTimer.value.is_billable ? hours * rate : 0
})

const totalRevenue = computed(() => {
  const completedRevenue = filteredEntries.value
    .filter(e => e.is_billable)
    .reduce((sum, e) => {
      const hours = (e.duration_minutes || 0) / 60
      const rate = e.project?.client?.hourly_rate || 0
      return sum + (hours * rate)
    }, 0)

  return completedRevenue + liveRevenue.value
})

const previousPeriodRevenue = computed(() => {
  return previousPeriodEntries.value
    .filter(e => e.is_billable)
    .reduce((sum, e) => {
      const hours = (e.duration_minutes || 0) / 60
      const rate = e.project?.client?.hourly_rate || 0
      return sum + (hours * rate)
    }, 0)
})

const revenueTrend = computed(() => {
  if (previousPeriodRevenue.value === 0) {
    return totalRevenue.value > 0 ? 100 : null
  }
  return ((totalRevenue.value - previousPeriodRevenue.value) / previousPeriodRevenue.value) * 100
})

const avgHourlyRate = computed(() => {
  if (billableHours.value === 0) return 0
  return totalRevenue.value / billableHours.value
})

const hoursByClient = computed(() => {
  const clientMap = {}
  filteredEntries.value.forEach(entry => {
    const clientName = entry.project?.client?.name || 'No Client'
    if (!clientMap[clientName]) {
      clientMap[clientName] = { name: clientName, minutes: 0 }
    }
    clientMap[clientName].minutes += entry.duration_minutes || 0
  })
  return Object.values(clientMap)
    .map(c => ({ ...c, hours: c.minutes / 60 }))
    .sort((a, b) => b.hours - a.hours)
})

const hoursByProject = computed(() => {
  const projectMap = {}
  filteredEntries.value.forEach(entry => {
    const projectName = entry.project?.name || 'No Project'
    const clientName = entry.project?.client?.name || 'No Client'
    const key = `${projectName}-${clientName}`
    if (!projectMap[key]) {
      projectMap[key] = { name: projectName, clientName, minutes: 0 }
    }
    projectMap[key].minutes += entry.duration_minutes || 0
  })
  return Object.values(projectMap)
    .map(p => ({ ...p, hours: p.minutes / 60 }))
    .sort((a, b) => b.hours - a.hours)
})

const topProjects = computed(() => {
  return hoursByProject.value.slice(0, 5)
})

const dailyStats = computed(() => {
  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const dayTotals = [0, 0, 0, 0, 0, 0, 0]
  const dayCounts = [0, 0, 0, 0, 0, 0, 0]

  filteredEntries.value.forEach(entry => {
    const date = new Date(entry.started_at)
    const dayIndex = date.getDay()
    dayTotals[dayIndex] += (entry.duration_minutes || 0) / 60
    dayCounts[dayIndex]++
  })

  // Reorder to start from Monday
  const reorderedStats = []
  for (let i = 1; i <= 7; i++) {
    const idx = i % 7
    const avg = dayCounts[idx] > 0 ? dayTotals[idx] / Math.ceil(selectedPeriod.value / 7) : 0
    reorderedStats.push({
      day: dayNames[idx].slice(0, 3),
      hours: avg
    })
  }

  return reorderedStats
})

const maxDailyHours = computed(() => {
  const max = Math.max(...dailyStats.value.map(s => s.hours), 0)
  return max > 0 ? max : 8
})


const billableChartSeries = computed(() => {
  if (totalHours.value === 0) return []
  return [billableHours.value, nonBillableHours.value]
})

const billableChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'Inter, sans-serif',
    selection: { enabled: false }
  },
  states: {
    active: { filter: { type: 'none' } }
  },
  labels: ['Billable', 'Non-billable'],
  colors: ['#56c97b', '#e2e8f0'],
  legend: {
    position: 'bottom',
    fontFamily: 'Inter, sans-serif',
    fontSize: '12px',
    labels: { colors: '#718096' }
  },
  dataLabels: {
    enabled: false
  },
  plotOptions: {
    pie: {
      donut: {
        size: '80%',
        labels: {
          show: true,
          name: {
            show: true,
            offsetY: 16,
            fontSize: '12px',
            color: '#718096'
          },
          value: {
            show: true,
            offsetY: -16,
            fontSize: '24px',
            fontWeight: 600,
            color: '#1a202c',
            formatter: () => `${Math.round((billableHours.value / totalHours.value) * 100)}%`
          },
          total: {
            show: true,
            showAlways: true,
            label: 'Billable',
            fontSize: '12px',
            color: '#718096',
            formatter: () => `${Math.round((billableHours.value / totalHours.value) * 100)}%`
          }
        }
      }
    }
  },
  stroke: { show: false },
  tooltip: {
    y: {
      formatter: (val) => `${val.toFixed(1)} hours`
    }
  }
}))

const clientChartSeries = computed(() => {
  return hoursByClient.value.slice(0, 8).map(c => ({
    x: c.name,
    y: parseFloat(c.hours.toFixed(1)),
    fillColor: getClientColor(c.name)
  }))
})

const clientChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    fontFamily: 'Inter, sans-serif',
    toolbar: { show: false },
    selection: { enabled: false }
  },
  states: {
    active: { filter: { type: 'none' } }
  },
  colors: [
    '#7a9ec2', '#8fb5a3', '#c4a67c', '#a89cc4',
    '#c49a9a', '#7eb8b8', '#b8a07a', '#9ab4c4'
  ],
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '60%',
      borderRadius: 4,
      distributed: true
    }
  },
  dataLabels: { enabled: false },
  legend: { show: false },
  xaxis: {
    labels: {
      style: {
        colors: '#718096',
        fontSize: '11px'
      },
      rotate: -45,
      trim: true,
      maxHeight: 80
    },
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    labels: {
      style: { colors: '#718096', fontSize: '11px' },
      formatter: (val) => `${val}h`
    }
  },
  grid: {
    borderColor: '#e2e8f0',
    strokeDashArray: 4
  },
  tooltip: {
    y: { formatter: (val) => `${val} hours` }
  }
}))

const projectChartSeries = computed(() => {
  return hoursByProject.value.slice(0, 8).map(p => ({
    x: p.name,
    y: parseFloat(p.hours.toFixed(1)),
    fillColor: getClientColor(p.clientName)
  }))
})

const projectChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    fontFamily: 'Inter, sans-serif',
    toolbar: { show: false },
    selection: { enabled: false }
  },
  states: {
    active: { filter: { type: 'none' } }
  },
  colors: [
    '#7a9ec2', '#8fb5a3', '#c4a67c', '#a89cc4',
    '#c49a9a', '#7eb8b8', '#b8a07a', '#9ab4c4'
  ],
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '60%',
      borderRadius: 4,
      distributed: true
    }
  },
  dataLabels: { enabled: false },
  legend: { show: false },
  xaxis: {
    labels: {
      style: { colors: '#718096', fontSize: '11px' },
      formatter: (val) => `${val}h`
    },
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    labels: {
      style: { colors: '#718096', fontSize: '11px' },
      maxWidth: 120
    }
  },
  grid: {
    borderColor: '#e2e8f0',
    strokeDashArray: 4
  },
  tooltip: {
    y: { formatter: (val) => `${val} hours` }
  }
}))


const formatTrendPercent = (trend) => {
  if (trend === null) return ''
  const absValue = Math.abs(Math.round(trend))
  return `${absValue}%`
}

const fetchData = async () => {
  try {
    const [entriesData, clientsData] = await Promise.all([
      api.api('/time-entries'),
      api.api('/clients')
    ])
    entries.value = entriesData
    clients.value = clientsData
  } catch (error) {
    console.error('Failed to fetch analytics data:', error)
  }
}

const fetchActiveTimer = async () => {
  try {
    const data = await api.api('/active-timer')
    activeTimer.value = data && data.id ? data : null
  } catch (error) {
    activeTimer.value = null
  }
}

const handleTimerStarted = (e) => {
  fetchActiveTimer()
}

const handleTimerStopped = () => {
  activeTimer.value = null
  fetchData()
}

const handleWsTimerStarted = (e) => {
  if (e.detail) {
    activeTimer.value = e.detail
  }
}

const handleWsTimerStopped = () => {
  activeTimer.value = null
  fetchData()
}

onMounted(async () => {
  await Promise.all([fetchData(), fetchSettings(), fetchActiveTimer()])
  loading.value = false

  // Listen for timer events
  window.addEventListener('timer-started', handleTimerStarted)
  window.addEventListener('timer-stopped', handleTimerStopped)
  window.addEventListener('ws-timer-started', handleWsTimerStarted)
  window.addEventListener('ws-timer-stopped', handleWsTimerStopped)
})

onUnmounted(() => {
  window.removeEventListener('timer-started', handleTimerStarted)
  window.removeEventListener('timer-stopped', handleTimerStopped)
  window.removeEventListener('ws-timer-started', handleWsTimerStarted)
  window.removeEventListener('ws-timer-stopped', handleWsTimerStopped)
})
</script>

<style scoped>
/* Analytics page styles */
</style>
