<template>
  <div class="flex items-center gap-4">
    <button
      v-if="!isCurrentWeek"
      @click="goToToday"
      class="btn-accent px-3 py-1.5 text-sm font-medium text-text-primary rounded-md"
    >
      <span class="relative z-[1]">Return to today</span>
    </button>

    <button
      @click="previousWeek"
      class="p-2 hover:bg-gray-100 rounded-md transition-colors"
      title="Previous week"
    >
      <svg class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>

    <div class="flex items-center gap-2">
      <svg class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span class="text-sm font-medium text-text-primary">
        {{ formattedDate }}
      </span>
    </div>

    <button
      @click="nextWeek"
      :disabled="isCurrentWeek"
      class="p-2 hover:bg-gray-100 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      title="Next week"
    >
      <svg class="w-5 h-5 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const formattedDate = computed(() => {
  const [year, month, day] = props.modelValue.split('-').map(Number)
  const date = new Date(year, month - 1, day)
  const options = { month: 'long', day: 'numeric', year: 'numeric' }
  return date.toLocaleDateString('en-US', options)
})

const isCurrentWeek = computed(() => {
  const today = new Date()
  const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`

  // Get Monday of current week
  const todayDay = today.getDay()
  const todayMonday = new Date(today)
  todayMonday.setDate(today.getDate() - (todayDay === 0 ? 6 : todayDay - 1))
  const todayMondayStr = `${todayMonday.getFullYear()}-${String(todayMonday.getMonth() + 1).padStart(2, '0')}-${String(todayMonday.getDate()).padStart(2, '0')}`

  // Get Monday of selected week
  const [year, month, day] = props.modelValue.split('-').map(Number)
  const selected = new Date(year, month - 1, day)
  const selectedDay = selected.getDay()
  const selectedMonday = new Date(selected)
  selectedMonday.setDate(selected.getDate() - (selectedDay === 0 ? 6 : selectedDay - 1))
  const selectedMondayStr = `${selectedMonday.getFullYear()}-${String(selectedMonday.getMonth() + 1).padStart(2, '0')}-${String(selectedMonday.getDate()).padStart(2, '0')}`

  return todayMondayStr === selectedMondayStr
})

const getMondayOfWeek = (dateStr) => {
  const [year, month, day] = dateStr.split('-').map(Number)
  const date = new Date(year, month - 1, day)
  const dayOfWeek = date.getDay()
  const monday = new Date(date)
  monday.setDate(date.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1))
  return `${monday.getFullYear()}-${String(monday.getMonth() + 1).padStart(2, '0')}-${String(monday.getDate()).padStart(2, '0')}`
}

const previousWeek = () => {
  const [year, month, day] = props.modelValue.split('-').map(Number)
  const current = new Date(year, month - 1, day)
  current.setDate(current.getDate() - 7)

  // Set to Monday of the previous week
  const mondayStr = getMondayOfWeek(`${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-${String(current.getDate()).padStart(2, '0')}`)
  emit('update:modelValue', mondayStr)
}

const nextWeek = () => {
  if (!isCurrentWeek.value) {
    const [year, month, day] = props.modelValue.split('-').map(Number)
    const current = new Date(year, month - 1, day)
    current.setDate(current.getDate() + 7)

    // Check if next week is the current week
    const nextWeekStr = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-${String(current.getDate()).padStart(2, '0')}`
    const nextWeekMonday = getMondayOfWeek(nextWeekStr)

    const today = new Date()
    const todayDay = today.getDay()
    const todayMonday = new Date(today)
    todayMonday.setDate(today.getDate() - (todayDay === 0 ? 6 : todayDay - 1))
    const todayMondayStr = `${todayMonday.getFullYear()}-${String(todayMonday.getMonth() + 1).padStart(2, '0')}-${String(todayMonday.getDate()).padStart(2, '0')}`

    // If next week is current week, go to today; otherwise go to Monday
    if (nextWeekMonday === todayMondayStr) {
      const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
      emit('update:modelValue', todayStr)
    } else {
      emit('update:modelValue', nextWeekMonday)
    }
  }
}

const goToToday = () => {
  const today = new Date()
  const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
  emit('update:modelValue', todayStr)
}
</script>
