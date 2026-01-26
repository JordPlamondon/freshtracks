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
const { getTodayStr, getDateStr, getWeekStart } = useDateUtils()

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
  const todayMonday = getWeekStart()
  const [year, month, day] = props.modelValue.split('-').map(Number)
  const selectedMonday = getWeekStart(new Date(year, month - 1, day))
  return getDateStr(todayMonday) === getDateStr(selectedMonday)
})

const previousWeek = () => {
  const [year, month, day] = props.modelValue.split('-').map(Number)
  const current = new Date(year, month - 1, day)
  current.setDate(current.getDate() - 7)
  emit('update:modelValue', getDateStr(getWeekStart(current)))
}

const nextWeek = () => {
  if (!isCurrentWeek.value) {
    const [year, month, day] = props.modelValue.split('-').map(Number)
    const current = new Date(year, month - 1, day)
    current.setDate(current.getDate() + 7)
    const nextWeekMonday = getWeekStart(current)
    const todayMonday = getWeekStart()
    if (getDateStr(nextWeekMonday) === getDateStr(todayMonday)) {
      emit('update:modelValue', getTodayStr())
    } else {
      emit('update:modelValue', getDateStr(nextWeekMonday))
    }
  }
}

const goToToday = () => {
  emit('update:modelValue', getTodayStr())
}
</script>
