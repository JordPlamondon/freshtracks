<template>
  <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
    <div v-if="activeTimer" class="space-y-4">
      <div class="flex justify-between items-start">
        <div>
          <h3 class="text-lg font-semibold">{{ activeTimer.project?.name }}</h3>
          <p class="text-sm text-gray-400">{{ activeTimer.project?.client?.name }}</p>
          <p v-if="activeTimer.description" class="text-sm text-gray-400 mt-1">
            {{ activeTimer.description }}
          </p>
        </div>
        <button
          @click="stopTimer"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md text-sm font-medium"
        >
          Stop
        </button>
      </div>
      
      <div class="text-4xl font-mono font-bold text-primary-accent">
        {{ formattedDuration }}
      </div>
    </div>

    <div v-else class="space-y-4">
      <h3 class="text-lg font-semibold">Start Timer</h3>
      
      <div>
        <label class="block text-sm font-medium mb-2">Project</label>
        <select
          v-model="selectedProjectId"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-accent"
        >
          <option value="">Select a project</option>
          <option v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.client?.name }} - {{ project.name }}
          </option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2">Description (optional)</label>
        <input
          v-model="description"
          type="text"
          placeholder="What are you working on?"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-accent"
        />
      </div>

      <button
        @click="startTimer"
        :disabled="!selectedProjectId"
        class="w-full px-4 py-2 bg-primary-accent hover:bg-indigo-700 disabled:bg-gray-700 disabled:cursor-not-allowed rounded-md font-medium"
      >
        Start Timer
      </button>
    </div>
  </div>
</template>

<script setup>
const timerApi = useApi()
const activeTimer = ref(null)
const projects = ref([])
const selectedProjectId = ref('')
const description = ref('')
const duration = ref(0)
let intervalId = null

const formattedDuration = computed(() => {
  const hours = Math.floor(duration.value / 3600)
  const minutes = Math.floor((duration.value % 3600) / 60)
  const seconds = duration.value % 60
  const pad = (n) => String(n).padStart(2, '0')
  return pad(hours) + ':' + pad(minutes) + ':' + pad(seconds)
})

const fetchActiveTimer = async () => {
  try {
    const data = await timerApi.api('/active-timer')
    activeTimer.value = data
    
    if (data) {
      const startedAt = new Date(data.started_at)
      duration.value = Math.floor((Date.now() - startedAt.getTime()) / 1000)
      startInterval()
    }
  } catch (error) {
    console.error('Failed to fetch active timer:', error)
  }
}

const fetchProjects = async () => {
  try {
    projects.value = await timerApi.api('/projects')
  } catch (error) {
    console.error('Failed to fetch projects:', error)
  }
}

const startTimer = async () => {
  try {
    const data = await timerApi.api('/time-entries', {
      method: 'POST',
      body: {
        project_id: selectedProjectId.value,
        description: description.value || null
      }
    })
    
    activeTimer.value = data
    selectedProjectId.value = ''
    description.value = ''
    duration.value = 0
    startInterval()
  } catch (error) {
    console.error('Failed to start timer:', error)
  }
}

const stopTimer = async () => {
  try {
    await timerApi.api('/time-entries/' + activeTimer.value.id + '/stop', { method: 'POST' })
    activeTimer.value = null
    duration.value = 0
    stopInterval()
  } catch (error) {
    console.error('Failed to stop timer:', error)
  }
}

const startInterval = () => {
  if (intervalId) clearInterval(intervalId)
  intervalId = setInterval(() => {
    duration.value++
  }, 1000)
}

const stopInterval = () => {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

onMounted(() => {
  fetchActiveTimer()
  fetchProjects()
})

onUnmounted(() => {
  stopInterval()
})
</script>
