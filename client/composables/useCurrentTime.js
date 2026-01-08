// Shared reactive current time for synchronized timer displays
// All components using this will be in sync since they share the same ref

const currentTime = ref(Date.now())
let intervalId = null
let subscriberCount = 0

export const useCurrentTime = () => {
  const startInterval = () => {
    if (intervalId === null) {
      // Update frequently for accurate timer display
      // Using 100ms ensures we're within 100ms of the true second change
      currentTime.value = Date.now()
      intervalId = setInterval(() => {
        currentTime.value = Date.now()
      }, 100)
    }
  }

  const stopInterval = () => {
    if (intervalId !== null && subscriberCount === 0) {
      clearInterval(intervalId)
      intervalId = null
    }
  }

  onMounted(() => {
    subscriberCount++
    startInterval()
  })

  onUnmounted(() => {
    subscriberCount--
    stopInterval()
  })

  return {
    currentTime: readonly(currentTime)
  }
}
