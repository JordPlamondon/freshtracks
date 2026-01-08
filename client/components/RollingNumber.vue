<template>
  <span class="rolling-number">
    <span v-for="(char, index) in currentChars" :key="`slot-${index}`" class="char-slot">
      <span v-if="char === '.' || char === ',' || char === '$'" class="static-char">
        {{ char }}
      </span>
      <span v-else class="digit-slot">
        <Transition name="roll">
          <span :key="charKeys[index]" class="digit">{{ char }}</span>
        </Transition>
      </span>
    </span>
  </span>
</template>

<script setup>
const props = defineProps({
  value: {
    type: Number,
    required: true
  },
  decimals: {
    type: Number,
    default: 2
  },
  roundTo: {
    type: Number,
    default: 0.05 // Round to nearest 5 cents
  }
})

const currentChars = ref([])
const charKeys = ref([])
const previousValue = ref(null)

const roundedValue = computed(() => {
  // Round to nearest increment (e.g., 0.05 for 5 cents, 0.10 for 10 cents)
  return Math.round(props.value / props.roundTo) * props.roundTo
})

const formatValue = (val) => {
  const formatted = val.toLocaleString('en-US', {
    minimumFractionDigits: props.decimals,
    maximumFractionDigits: props.decimals
  })
  return `$${formatted}`.split('')
}

// Initialize
onMounted(() => {
  currentChars.value = formatValue(roundedValue.value)
  charKeys.value = currentChars.value.map((char, i) => `${i}-${char}-0`)
  previousValue.value = roundedValue.value
})

// Watch for rounded value changes
watch(roundedValue, (newValue) => {
  const newChars = formatValue(newValue)

  // Update charKeys only for changed digits
  const newKeys = newChars.map((char, i) => {
    if (char !== currentChars.value[i] && char !== '.' && char !== ',' && char !== '$') {
      // Digit changed - increment key to trigger animation
      return `${i}-${char}-${Date.now()}`
    }
    // Digit unchanged - keep same key (no animation)
    return charKeys.value[i]
  })

  charKeys.value = newKeys
  currentChars.value = newChars
  previousValue.value = newValue
})
</script>

<style scoped>
.rolling-number {
  display: inline-flex;
  align-items: center;
  line-height: 1;
}

.char-slot {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.static-char {
  display: inline-block;
  line-height: 1;
}

.digit-slot {
  display: inline-block;
  position: relative;
  overflow: hidden;
  width: 0.65em;
  text-align: center;
  line-height: 1;
}

.digit {
  display: block;
  width: 100%;
  text-align: center;
  line-height: 1;
}

/* Rolling animation - slower and smoother */
.roll-enter-active {
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.roll-leave-active {
  transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.roll-enter-from {
  transform: translateY(100%);
}

.roll-leave-to {
  transform: translateY(-100%);
}

.roll-leave-active {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
}
</style>
