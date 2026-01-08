const showShortcutsModal = ref(false)
const showCommandPalette = ref(false)
const waitingForSecondKey = ref(null)
const waitingTimeout = ref(null)

const isInputFocused = () => {
  const active = document.activeElement
  if (!active) return false
  const tag = active.tagName?.toLowerCase()
  return ['input', 'textarea', 'select'].includes(tag) || active.isContentEditable
}

const shortcuts = {
  global: [
    { keys: ['S'], description: 'Start / Stop timer', action: 'toggle-timer' },
    { keys: ['?'], description: 'Show keyboard shortcuts', action: 'show-shortcuts' },
    { keys: ['⌘', 'K'], description: 'Open command palette', action: 'show-command-palette', isMeta: true },
  ],
  navigation: [
    { keys: ['G', 'H'], description: 'Go to Home', action: 'nav-home', isSequence: true },
    { keys: ['G', 'T'], description: 'Go to Time Tracking', action: 'nav-time-tracking', isSequence: true },
    { keys: ['G', 'A'], description: 'Go to Analytics', action: 'nav-analytics', isSequence: true },
    { keys: ['G', 'R'], description: 'Go to Reports', action: 'nav-reports', isSequence: true },
    { keys: ['G', 'P'], description: 'Go to Projects', action: 'nav-projects', isSequence: true },
    { keys: ['G', 'C'], description: 'Go to Clients', action: 'nav-clients', isSequence: true },
    { keys: ['G', 'I'], description: 'Go to Invoices', action: 'nav-invoices', isSequence: true },
  ],
  timeTracking: [
    { keys: ['←'], description: 'Previous day', action: 'prev-day' },
    { keys: ['→'], description: 'Next day', action: 'next-day' },
    { keys: ['T'], description: 'Jump to today', action: 'jump-today' },
  ]
}

export const useKeyboardShortcuts = () => {
  const router = useRouter()
  const route = useRoute()

  const emitShortcut = (action) => {
    window.dispatchEvent(new CustomEvent('keyboard-shortcut', { detail: { action } }))
  }

  const executeAction = (action) => {
    switch (action) {
      case 'show-shortcuts':
        showShortcutsModal.value = true
        break
      case 'show-command-palette':
        showCommandPalette.value = true
        break

      case 'toggle-timer':
        emitShortcut('toggle-timer')
        break

      case 'nav-home':
        router.push('/')
        break
      case 'nav-time-tracking':
        router.push('/time-tracking')
        break
      case 'nav-analytics':
        router.push('/analytics')
        break
      case 'nav-reports':
        router.push('/reports')
        break
      case 'nav-projects':
        router.push('/projects')
        break
      case 'nav-clients':
        router.push('/clients')
        break
      case 'nav-invoices':
        router.push('/invoices')
        break

      case 'prev-day':
      case 'next-day':
      case 'jump-today':
        if (route.path === '/time-tracking') {
          emitShortcut(action)
        }
        break
    }
  }

  const handleKeydown = (e) => {
    if (e.key === 'Escape') {
      if (showCommandPalette.value) {
        showCommandPalette.value = false
        e.preventDefault()
        return
      }
      if (showShortcutsModal.value) {
        showShortcutsModal.value = false
        e.preventDefault()
        return
      }
      return
    }

    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault()
      if (showShortcutsModal.value) {
        showShortcutsModal.value = false
      }
      showCommandPalette.value = true
      return
    }

    if (showCommandPalette.value || showShortcutsModal.value) {
      return
    }

    if (isInputFocused()) {
      return
    }

    const key = e.key.toLowerCase()

    if (waitingForSecondKey.value === 'g') {
      clearTimeout(waitingTimeout.value)
      waitingForSecondKey.value = null

      const sequenceMap = {
        'h': 'nav-home',
        't': 'nav-time-tracking',
        'a': 'nav-analytics',
        'r': 'nav-reports',
        'p': 'nav-projects',
        'c': 'nav-clients',
        'i': 'nav-invoices',
      }

      if (sequenceMap[key]) {
        e.preventDefault()
        executeAction(sequenceMap[key])
      }
      return
    }

    if (key === 'g') {
      e.preventDefault()
      waitingForSecondKey.value = 'g'
      waitingTimeout.value = setTimeout(() => {
        waitingForSecondKey.value = null
      }, 1500)
      return
    }

    if (key === 's') {
      e.preventDefault()
      executeAction('toggle-timer')
      return
    }

    if (e.key === '?') {
      e.preventDefault()
      executeAction('show-shortcuts')
      return
    }

    if (route.path === '/time-tracking') {
      if (e.key === 'ArrowLeft') {
        e.preventDefault()
        executeAction('prev-day')
        return
      }
      if (e.key === 'ArrowRight') {
        e.preventDefault()
        executeAction('next-day')
        return
      }
      if (key === 't') {
        e.preventDefault()
        executeAction('jump-today')
        return
      }
    }
  }

  const initShortcuts = () => {
    if (import.meta.client) {
      window.addEventListener('keydown', handleKeydown)
    }
  }

  const destroyShortcuts = () => {
    if (import.meta.client) {
      window.removeEventListener('keydown', handleKeydown)
      if (waitingTimeout.value) {
        clearTimeout(waitingTimeout.value)
      }
    }
  }

  return {
    showShortcutsModal,
    showCommandPalette,
    waitingForSecondKey,
    shortcuts,
    initShortcuts,
    destroyShortcuts,
    executeAction,
  }
}
