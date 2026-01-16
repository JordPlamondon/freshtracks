const CLIENT_COLORS = [
  '#7a9ec2',
  '#8fb5a3',
  '#c4a67c',
  '#a89cc4',
  '#c49a9a',
  '#7eb8b8',
  '#b8a07a',
  '#9ab4c4'
]

export function useFormatting() {
  const formatHours = (hours: number): string => {
    if (!hours || hours === 0) return '0h'
    const h = Math.floor(hours)
    const m = Math.round((hours - h) * 60)
    if (h === 0) return `${m}m`
    if (m === 0) return `${h}h`
    return `${h}h ${m}m`
  }

  const formatDuration = (minutes: number): string => {
    if (!minutes) return '0h'
    const hours = Math.floor(minutes / 60)
    const mins = Math.floor(minutes % 60)
    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`
  }

  const formatDurationHMS = (minutes: number): string => {
    if (!minutes) return '0:00:00'
    const totalSeconds = Math.floor(minutes * 60)
    const hours = Math.floor(totalSeconds / 3600)
    const mins = Math.floor((totalSeconds % 3600) / 60)
    const secs = Math.floor(totalSeconds % 60)
    return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
  }

  const formatAmount = (amount: number): string => {
    if (!amount) return '0.00'
    return parseFloat(String(amount)).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }

  const getClientColor = (clientName: string | null | undefined): string => {
    if (!clientName) return '#cbd5e0'
    let hash = 0
    for (let i = 0; i < clientName.length; i++) {
      hash = clientName.charCodeAt(i) + ((hash << 5) - hash)
    }
    return CLIENT_COLORS[Math.abs(hash) % CLIENT_COLORS.length]
  }

  const formatTimeRange = (start: string, end: string | null): string => {
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

  return {
    formatHours,
    formatDuration,
    formatDurationHMS,
    formatAmount,
    getClientColor,
    formatTimeRange
  }
}
