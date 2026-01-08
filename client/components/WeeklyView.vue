<template>
  <div class="bg-[#f1f0ee] rounded-lg p-2">
    <!-- Title Section -->
    <div class="pt-2 pb-3 px-4 bg-[#f1f0ee] flex items-center justify-between">
      <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">
        Weekly Timesheet
      </div>
      <div
        class="flex items-center gap-2"
        :class="{ 'invisible': !settings.show_live_revenue || !isSelectedDateToday }"
      >
        <span class="text-sm text-text-primary">Today's Revenue:</span>
        <span class="inline-flex items-center px-2 py-[0.175rem] rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200">
          <RollingNumber :value="todaysRevenue" :decimals="2" />
        </span>
      </div>
    </div>

    <!-- White Content Card -->
    <div class="bg-white rounded-lg overflow-hidden">
      <!-- Mobile: Single Day Header with Navigation -->
      <div class="md:hidden px-3 py-3">
      <div class="flex items-center justify-between mb-3">
        <!-- Left Arrow -->
        <button
          @click="navigateDay(-1)"
          class="p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Date Display -->
        <div class="flex-1 text-center px-2">
          <h3 class="text-base font-semibold text-text-primary leading-tight">
            <span v-if="isSelectedDateToday" class="text-[#56c97b]">Today:</span>
            {{ selectedDayHeader }}
          </h3>
          <div class="text-xs text-text-secondary mt-0.5 font-mono">
            {{ getDayDurationLive(selectedDate) }}
          </div>
        </div>

        <!-- Right Arrow -->
        <button
          @click="navigateDay(1)"
          class="p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded-lg transition-colors flex-shrink-0"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Today Button (only show if not already on today) -->
      <button
        v-if="!isSelectedDateToday"
        @click="goToToday"
        class="w-full btn-accent text-xs py-2"
      >
        Jump to today
      </button>
    </div>

    <!-- Desktop: Week Header and Days -->
    <div class="hidden md:block p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-text-primary">
          <span v-if="isSelectedDateToday" class="font-bold">Today:</span>
          {{ selectedDayHeader }}
        </h3>
        <div class="text-sm text-text-secondary">
          Weekly total: <span class="font-semibold text-text-primary">{{ formatDuration(weekTotal) }}</span>
        </div>
      </div>

      <div class="grid grid-cols-7 gap-2">
        <button
          v-for="day in weekDays"
          :key="day.date"
          class="text-center p-3 transition-colors cursor-pointer border-b-[3px] border-transparent"
          :class="[
            day.isToday && day.isSelected ? 'today-card-active rounded-md' :
            day.isToday ? 'bg-active-day-bg rounded-md' :
            day.isSelected ? 'bg-gray-50 !border-text-primary rounded-t-md' :
            'bg-gray-50 hover:bg-gray-100 rounded-md'
          ]"
          @click="$emit('selectDay', day.date)"
        >
          <div class="text-xs font-medium mb-1" :class="[day.isToday && day.isSelected ? 'relative z-[1] text-text-primary' : day.isToday ? 'text-text-primary' : 'text-text-secondary']">
            {{ day.dayName }}
          </div>
          <div class="text-lg font-semibold mb-1 text-text-primary" :class="day.isToday && day.isSelected ? 'relative z-[1]' : ''">
            {{ day.dayNumber }}
          </div>
          <div
            class="text-xs font-mono"
            :class="[
              day.isToday && day.isSelected ? 'relative z-[1] text-text-primary' :
              dayHasActiveTimer(day.date) ? 'text-[#56c97b] font-semibold' :
              day.isToday ? 'text-text-primary' :
              'text-text-secondary'
            ]"
          >
            {{ getDayDurationLive(day.date) }}
          </div>
        </button>
      </div>
    </div>

    <!-- Mobile: Card-based Entries -->
    <div class="md:hidden">
      <div v-if="loading" class="p-4">
        <div class="text-center py-8 text-text-secondary">
          Loading...
        </div>
      </div>

      <div v-else-if="dailyEntries.length === 0" class="p-4">
        <div class="text-center py-8 text-text-secondary">
          No time entries for this day.
        </div>
      </div>

      <div v-else class="divide-y divide-border-light">
        <div
          v-for="entry in dailyEntries"
          :key="entry.id"
          class="p-4"
          :class="!entry.stopped_at ? 'bg-[#f1f0ee]' : ''"
        >
          <!-- Client & Project -->
          <div class="flex items-center gap-2 mb-2">
            <div
              class="w-2.5 h-2.5 rounded-full flex-shrink-0"
              :style="{ backgroundColor: getClientColor(entry.project?.client?.name) }"
            ></div>
            <span class="text-sm font-medium text-text-secondary">
              {{ entry.project?.client?.name || 'No Client' }}
            </span>
            <span class="text-sm text-text-secondary">•</span>
            <span class="text-sm text-text-primary">
              {{ entry.project?.name || 'No Project' }}
            </span>
          </div>

          <!-- Description -->
          <div class="mb-2">
            <p class="text-base font-medium text-text-primary">
              {{ entry.description || 'No description' }}
            </p>
          </div>

          <!-- Duration & Time Range -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span
                class="text-lg font-mono font-bold"
                :class="entry.stopped_at ? 'text-text-primary' : 'text-[#56c97b]'"
              >
                {{ entry.stopped_at ? formatDurationHMS(entry.duration_minutes) : getLiveDuration(entry) }}
              </span>
              <span
                v-if="entry.is_billable"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"
              >
                Billable
              </span>
            </div>
            <span class="text-xs text-text-secondary">
              {{ formatTimeRange(entry.started_at, entry.stopped_at) }}
            </span>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <!-- Stop button (for running entries) -->
            <button
              v-if="!entry.stopped_at"
              @click="stopEntry(entry)"
              :disabled="stoppingId === entry.id"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="stoppingId === entry.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <rect x="6" y="6" width="12" height="12"/>
              </svg>
              <span class="text-sm font-medium">{{ stoppingId === entry.id ? 'Stopping...' : 'Stop timer' }}</span>
            </button>

            <!-- Play/Resume button (for stopped entries) -->
            <button
              v-else
              @click="handlePlayClick(entry, $event)"
              :disabled="restartingId === entry.id"
              class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-text-primary rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="restartingId === entry.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
              </svg>
              <span class="text-sm font-medium">{{ restartingId === entry.id ? 'Starting...' : 'Resume' }}</span>
            </button>

            <!-- Edit button -->
            <button
              @click="$emit('edit', entry)"
              class="p-2.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>

            <!-- Delete button -->
            <button
              @click="$emit('delete', entry.id)"
              :disabled="deletingId === entry.id"
              class="p-2.5 text-text-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="deletingId === entry.id" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop: Daily Entries Table -->
    <div v-if="loading" class="hidden md:block p-6">
      <div class="text-center py-8 text-text-secondary">
        Loading...
      </div>
    </div>

    <div v-else-if="dailyEntries.length === 0" class="hidden md:block p-6">
      <div class="text-center py-8 text-text-secondary">
        No time entries for this day.
      </div>
    </div>

    <div v-else class="hidden md:block overflow-x-auto">
      <table class="w-full" style="border-collapse: separate; border-spacing: 0;">
        <thead>
          <tr>
            <th class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
              Task Name
            </th>
            <th class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
              Project
            </th>
            <th class="text-left py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
              Client
            </th>
            <th class="text-right py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
              Duration
            </th>
            <th class="text-right py-3 px-6 text-xs font-semibold text-text-secondary uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border-light">
          <tr
            v-for="entry in dailyEntries"
            :key="entry.id"
            class="hover:bg-[#f1f0ee] transition-colors"
            :style="!entry.stopped_at ? { background: '#f1f0ee' } : {}"
          >
            <!-- Task Name -->
            <td class="py-4 px-6">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-text-primary">
                  {{ entry.description || 'No description' }}
                </span>
                <span
                  v-if="entry.is_billable"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 border border-green-200"
                >
                  Billable
                </span>
              </div>
            </td>

            <!-- Project -->
            <td class="py-4 px-6">
              <span class="text-sm text-text-primary">
                {{ entry.project?.name || 'No Project' }}
              </span>
            </td>

            <!-- Client -->
            <td class="py-4 px-6">
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

            <!-- Duration -->
            <td class="py-4 px-6 text-right">
              <div class="flex flex-col items-end">
                <span
                  class="text-sm font-mono font-semibold"
                  :class="entry.stopped_at ? 'text-text-primary' : 'text-[#56c97b]'"
                >
                  {{ entry.stopped_at ? formatDurationHMS(entry.duration_minutes) : getLiveDuration(entry) }}
                </span>
                <span class="text-xs text-text-secondary mt-0.5">
                  {{ formatTimeRange(entry.started_at, entry.stopped_at) }}
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td class="py-4 px-6">
              <div class="flex items-center justify-end gap-2 relative">
                <!-- Stop button (for running entries) -->
                <button
                  v-if="!entry.stopped_at"
                  @click="stopEntry(entry)"
                  :disabled="stoppingId === entry.id"
                  class="p-1.5 text-red-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
                  :title="stoppingId === entry.id ? 'Stopping...' : 'Stop timer'"
                >
                  <svg v-if="stoppingId === entry.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <rect x="6" y="6" width="12" height="12"/>
                  </svg>
                </button>

                <!-- Play/Resume button (for stopped entries) -->
                <div v-else class="relative">
                  <button
                    @click="handlePlayClick(entry, $event)"
                    :disabled="restartingId === entry.id"
                    class="p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded transition-colors disabled:opacity-50"
                    :title="restartingId === entry.id ? 'Starting...' : 'Resume timer'"
                  >
                    <svg v-if="restartingId === entry.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z"/>
                    </svg>
                  </button>

                </div>

                <!-- Popover for past day entries (teleported to body to avoid overflow clipping) -->
                <Teleport to="body">
                  <div
                    v-if="showPopoverForEntry === entry.id"
                    class="fixed z-[9999] w-72 p-4 text-center bg-white border border-border-light rounded-md shadow-lg"
                    :style="getPopoverPosition(entry.id)"
                    @click.stop
                  >
                    <p class="text-sm text-text-primary mb-4">
                      This is not today's timesheet. Are you sure you want to restart this timer?
                    </p>
                    <button
                      @click="closePopover"
                      class="w-full px-4 py-2 mb-2 text-sm font-medium text-white bg-text-primary hover:bg-text-primary/90 rounded-md transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      @click="restartEntryOnOriginalDay(entry)"
                      class="w-full px-4 py-2 mb-2 text-sm font-medium text-text-primary bg-white border border-border-light hover:bg-gray-50 rounded-md transition-colors"
                    >
                      Yes, restart this timer
                    </button>
                    <button
                      @click="startTimerOnToday(entry)"
                      class="w-full px-4 py-2 text-sm font-medium text-text-primary bg-white border border-border-light hover:bg-gray-50 rounded-md transition-colors"
                    >
                      Start timer on today's date
                    </button>
                  </div>
                </Teleport>

                <!-- Edit button -->
                <button
                  @click="$emit('edit', entry)"
                  class="p-1.5 text-text-secondary hover:text-text-primary hover:bg-gray-100 rounded transition-colors"
                  title="Edit entry"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>

                <!-- Delete button -->
                <button
                  @click="$emit('delete', entry.id)"
                  :disabled="deletingId === entry.id"
                  class="p-1.5 text-text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50"
                  :title="deletingId === entry.id ? 'Deleting...' : 'Delete entry'"
                >
                  <svg v-if="deletingId === entry.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    </div>
  </div>
</template>

<script setup>
const api = useApi()
const { currentTime } = useCurrentTime()
const { settings, fetchSettings } = useSettings()

const props = defineProps({
  entries: {
    type: Array,
    default: () => []
  },
  selectedDate: {
    type: String,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  deletingId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['selectDay', 'edit', 'delete', 'updateEntry', 'addEntry'])

const stoppingId = ref(null)
const restartingId = ref(null)
const showPopoverForEntry = ref(null)
const popoverPosition = ref({ top: 0, left: 0 })

const getTodayStr = () => {
  const today = new Date()
  return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
}

const isEntryFromToday = (entry) => {
  const date = new Date(entry.started_at)
  const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  return entryDate === getTodayStr()
}

const closePopover = () => {
  showPopoverForEntry.value = null
}

const getPopoverPosition = (entryId) => {
  return {
    top: `${popoverPosition.value.top}px`,
    left: `${popoverPosition.value.left}px`
  }
}

const stopActiveTimer = async () => {
  try {
    const activeTimer = await api.api('/active-timer')
    if (activeTimer && activeTimer.id) {
      const updatedEntry = await api.api(`/time-entries/${activeTimer.id}/stop`, {
        method: 'POST'
      })
      emit('updateEntry', updatedEntry)
      return updatedEntry
    }
    return null
  } catch (error) {
    console.error('Failed to stop active timer:', error)
    return null
  }
}

const stopEntry = async (entry) => {
  try {
    stoppingId.value = entry.id
    const updatedEntry = await api.api(`/time-entries/${entry.id}/stop`, {
      method: 'POST'
    })
    emit('updateEntry', updatedEntry)
    window.dispatchEvent(new Event('timer-stopped'))
  } catch (error) {
    console.error('Failed to stop timer:', error)
  } finally {
    stoppingId.value = null
  }
}

const handlePlayClick = (entry, event) => {
  event.stopPropagation()

  if (isEntryFromToday(entry)) {
    restartEntry(entry)
  } else {
    const button = event.currentTarget
    const rect = button.getBoundingClientRect()
    popoverPosition.value = {
      top: rect.bottom + 8,
      left: rect.right - 288
    }
    showPopoverForEntry.value = entry.id
  }
}

const restartEntry = async (entry) => {
  try {
    restartingId.value = entry.id
    closePopover()
    await stopActiveTimer()

    const updatedEntry = await api.api(`/time-entries/${entry.id}/restart`, {
      method: 'POST'
    })

    emit('updateEntry', updatedEntry)
    window.dispatchEvent(new Event('timer-started'))
  } catch (error) {
    console.error('Failed to restart timer:', error)
  } finally {
    restartingId.value = null
  }
}

const restartEntryOnOriginalDay = async (entry) => {
  await restartEntry(entry)
}

const startTimerOnToday = async (entry) => {
  try {
    restartingId.value = entry.id
    closePopover()
    await stopActiveTimer()

    const newEntry = await api.api('/time-entries', {
      method: 'POST',
      body: {
        project_id: entry.project?.id,
        description: entry.description || null,
        is_billable: entry.is_billable
      }
    })

    emit('addEntry', newEntry)
    const todayStr = getTodayStr()
    emit('selectDay', todayStr)
    window.dispatchEvent(new Event('timer-started'))
  } catch (error) {
    console.error('Failed to start timer on today:', error)
  } finally {
    restartingId.value = null
  }
}

const getLiveDuration = (entry) => {
  if (!entry.started_at) return '0:00:00'

  const sessionStart = new Date(entry.resumed_at || entry.started_at)
  const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
  const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
  const accumulatedSeconds = Math.floor((entry.duration_minutes || 0) * 60)
  const totalSeconds = accumulatedSeconds + currentSessionSeconds

  const hours = Math.floor(totalSeconds / 3600)
  const mins = Math.floor((totalSeconds % 3600) / 60)
  const secs = Math.floor(totalSeconds % 60)
  return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

const getDayDurationLive = (dateStr) => {
  const dayEntries = props.entries.filter(entry => {
    const date = new Date(entry.started_at)
    const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    return entryDate === dateStr
  })

  let totalSeconds = 0

  dayEntries.forEach(entry => {
    if (entry.stopped_at) {
      totalSeconds += Math.floor((entry.duration_minutes || 0) * 60)
    } else {
      const sessionStart = new Date(entry.resumed_at || entry.started_at)
      const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
      const currentSessionSeconds = Math.floor(currentSessionMs / 1000)
      const accumulatedSeconds = Math.floor((entry.duration_minutes || 0) * 60)
      totalSeconds += accumulatedSeconds + currentSessionSeconds
    }
  })

  const hours = Math.floor(totalSeconds / 3600)
  const mins = Math.floor((totalSeconds % 3600) / 60)
  const secs = Math.floor(totalSeconds % 60)
  return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

const dayHasActiveTimer = (dateStr) => {
  return props.entries.some(entry => {
    if (entry.stopped_at) return false
    const date = new Date(entry.started_at)
    const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    return entryDate === dateStr
  })
}

const formatDuration = (minutes) => {
  if (!minutes) return '0h'
  const hours = Math.floor(minutes / 60)
  const mins = Math.floor(minutes % 60)
  return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`
}

const formatDurationHMS = (minutes) => {
  if (!minutes) return '0:00:00'
  const totalSeconds = Math.floor(minutes * 60)
  const hours = Math.floor(totalSeconds / 3600)
  const mins = Math.floor((totalSeconds % 3600) / 60)
  const secs = Math.floor(totalSeconds % 60)
  return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

const formatTimeRange = (start, end) => {
  const startDate = new Date(start)
  const startTime = startDate.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  if (!end) {
    return `${startTime} - Running`
  }

  const endDate = new Date(end)
  const endTime = endDate.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })

  return `${startTime} - ${endTime}`
}

// Generate consistent color for client names
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

const getWeekDays = () => {
  const [year, month, day] = props.selectedDate.split('-').map(Number)
  const selectedDateObj = new Date(year, month - 1, day)
  const currentDay = selectedDateObj.getDay()
  const monday = new Date(selectedDateObj)
  monday.setDate(selectedDateObj.getDate() - (currentDay === 0 ? 6 : currentDay - 1))

  const days = []
  const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
  const todayStr = getTodayStr()

  for (let i = 0; i < 7; i++) {
    const date = new Date(monday)
    date.setDate(monday.getDate() + i)
    const dateStr = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

    days.push({
      date: dateStr,
      dayName: dayNames[i],
      dayNumber: date.getDate(),
      isToday: dateStr === todayStr,
      isSelected: dateStr === props.selectedDate,
      hours: 0
    })
  }

  return days
}

const weekDays = computed(() => {
  const days = getWeekDays()

  props.entries.forEach(entry => {
    const date = new Date(entry.started_at)
    const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    const day = days.find(d => d.date === entryDate)
    if (day) {
      day.hours += entry.duration_minutes || 0
    }
  })

  return days
})

const weekTotal = computed(() => {
  return weekDays.value.reduce((total, day) => total + day.hours, 0)
})

const dailyEntries = computed(() => {
  return props.entries.filter(entry => {
    const date = new Date(entry.started_at)
    const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    return entryDate === props.selectedDate
  }).sort((a, b) => new Date(b.started_at) - new Date(a.started_at))
})

const selectedDayHeader = computed(() => {
  const [year, month, day] = props.selectedDate.split('-').map(Number)
  const date = new Date(year, month - 1, day)

  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

  const dayName = dayNames[date.getDay()]
  const monthName = monthNames[date.getMonth()]
  const dayNum = date.getDate()

  return `${dayName}, ${dayNum} ${monthName}`
})

const isSelectedDateToday = computed(() => {
  return props.selectedDate === getTodayStr()
})

const navigateDay = (direction) => {
  const [year, month, day] = props.selectedDate.split('-').map(Number)
  const currentDate = new Date(year, month - 1, day)
  currentDate.setDate(currentDate.getDate() + direction)

  const newDateStr = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`
  emit('selectDay', newDateStr)
}

const goToToday = () => {
  emit('selectDay', getTodayStr())
}

const todaysRevenue = computed(() => {
  const todayStr = getTodayStr()

  const completedRevenue = props.entries
    .filter(entry => {
      if (!entry.is_billable || !entry.stopped_at) return false
      const date = new Date(entry.started_at)
      const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
      return entryDate === todayStr
    })
    .reduce((sum, entry) => {
      const hours = (entry.duration_minutes || 0) / 60
      const rate = entry.project?.client?.hourly_rate || 0
      return sum + (hours * rate)
    }, 0)

  const activeEntry = props.entries.find(e => !e.stopped_at)
  let liveRevenue = 0

  if (activeEntry && activeEntry.is_billable) {
    const date = new Date(activeEntry.started_at)
    const entryDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`

    if (entryDate === todayStr) {
      const sessionStart = new Date(activeEntry.resumed_at || activeEntry.started_at)
      const currentSessionMs = Math.max(0, currentTime.value - sessionStart.getTime())
      const currentSessionMinutes = currentSessionMs / 1000 / 60
      const totalMinutes = (activeEntry.duration_minutes || 0) + currentSessionMinutes
      const hours = totalMinutes / 60
      const rate = activeEntry.project?.client?.hourly_rate || 0
      liveRevenue = hours * rate
    }
  }

  return completedRevenue + liveRevenue
})

const formatRevenue = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

onMounted(() => {
  fetchSettings()
  document.addEventListener('click', closePopover)
})

onUnmounted(() => {
  document.removeEventListener('click', closePopover)
})
</script>

<style scoped>
.today-card-active {
  position: relative;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.11);
}

.today-card-active::before {
  background: linear-gradient(180deg, #f6fce3, #c2ec42);
  border-radius: inherit;
  content: "";
  inset: 0;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  padding: 2px;
  pointer-events: none;
  position: absolute;
  bottom: -3px;
}

.today-card-active::after {
  content: '';
  background: linear-gradient(180deg, #e4ff93, #cefb47);
  position: absolute;
  top: 1px;
  left: 1px;
  right: 1px;
  bottom: -2px;
  z-index: 0;
  border-radius: 0.375rem;
}
</style>
