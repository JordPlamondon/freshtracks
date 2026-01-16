export function useDateUtils() {
  const getTodayStr = (): string => {
    const today = new Date()
    return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
  }

  const getDateStr = (date: Date): string => {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
  }

  const getWeekStart = (date?: Date): Date => {
    const d = new Date(date || new Date())
    const day = d.getDay()
    const diff = d.getDate() - day + (day === 0 ? -6 : 1)
    d.setDate(diff)
    d.setHours(0, 0, 0, 0)
    return d
  }

  const getMonthStart = (date?: Date): Date => {
    const d = new Date(date || new Date())
    d.setDate(1)
    d.setHours(0, 0, 0, 0)
    return d
  }

  const formatDateDisplay = (dateStr: string): string => {
    const [year, month, day] = dateStr.split('-').map(Number)
    const date = new Date(year, month - 1, day)
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    return `${dayNames[date.getDay()]}, ${day} ${monthNames[date.getMonth()]}`
  }

  const isToday = (dateStr: string): boolean => {
    return dateStr === getTodayStr()
  }

  const getEntryDateStr = (entry: { started_at: string }): string => {
    const date = new Date(entry.started_at)
    return getDateStr(date)
  }

  return {
    getTodayStr,
    getDateStr,
    getWeekStart,
    getMonthStart,
    formatDateDisplay,
    isToday,
    getEntryDateStr
  }
}
