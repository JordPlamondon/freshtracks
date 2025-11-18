<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold">Dashboard</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <Timer />
        
        <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
          <h2 class="text-xl font-semibold mb-4">Recent Time Entries</h2>
          
          <div v-if="loading" class="text-center py-8 text-gray-400">
            Loading...
          </div>
          
          <div v-else-if="timeEntries.length === 0" class="text-center py-8 text-gray-400">
            No time entries yet. Start tracking your time!
          </div>
          
          <div v-else class="space-y-3">
            <div
              v-for="entry in timeEntries.slice(0, 5)"
              :key="entry.id"
              class="p-4 bg-gray-800 rounded-lg border border-gray-700"
            >
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-medium">{{ entry.project?.name }}</h3>
                  <p class="text-sm text-gray-400">{{ entry.project?.client?.name }}</p>
                  <p v-if="entry.description" class="text-sm text-gray-400 mt-1">
                    {{ entry.description }}
                  </p>
                </div>
                <div class="text-right">
                  <div class="font-mono font-medium">
                    {{ formatDuration(entry.duration_minutes) }}
                  </div>
                  <div class="text-sm text-gray-400">
                    {{ formatDate(entry.started_at) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="space-y-6">
        <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
          <h2 class="text-xl font-semibold mb-4">Quick Stats</h2>
          
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-400">Total Clients</p>
              <p class="text-2xl font-bold text-primary-accent">{{ stats.clients }}</p>
            </div>
            
            <div>
              <p class="text-sm text-gray-400">Active Projects</p>
              <p class="text-2xl font-bold text-primary-accent">{{ stats.projects }}</p>
            </div>
            
            <div>
              <p class="text-sm text-gray-400">This Week</p>
              <p class="text-2xl font-bold text-primary-accent">
                {{ formatDuration(stats.weekHours) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'default'
})

const dashApi = useApi()
const timeEntries = ref([])
const stats = ref({
  clients: 0,
  projects: 0,
  weekHours: 0
})
const loading = ref(true)

const formatDuration = (minutes) => {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return hours + 'h ' + mins + 'm'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const fetchData = async () => {
  try {
    loading.value = true
    const [entriesData, clientsData, projectsData] = await Promise.all([
      dashApi.api('/time-entries'),
      dashApi.api('/clients'),
      dashApi.api('/projects')
    ])
    
    timeEntries.value = entriesData
    stats.value.clients = clientsData.length
    stats.value.projects = projectsData.filter(p => p.status === 'active').length
    
    const weekAgo = new Date()
    weekAgo.setDate(weekAgo.getDate() - 7)
    stats.value.weekHours = entriesData
      .filter(e => new Date(e.started_at) >= weekAgo)
      .reduce((total, e) => total + (e.duration_minutes || 0), 0)
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
