export const useSettings = () => {
  const api = useApi()
  const settings = useState('userSettings', () => ({
    show_live_revenue: true
  }))
  const loading = useState('settingsLoading', () => false)

  const fetchSettings = async () => {
    if (loading.value) return

    loading.value = true
    try {
      const data = await api.api('/settings')
      settings.value = {
        show_live_revenue: data.show_live_revenue ?? true
      }
    } catch (error) {
      console.error('Failed to fetch settings:', error)
      // Keep default values on error
    } finally {
      loading.value = false
    }
  }

  const updateSettings = async (newSettings: { show_live_revenue?: boolean }) => {
    try {
      const data = await api.api('/settings', {
        method: 'PUT',
        body: JSON.stringify(newSettings)
      })
      settings.value = {
        show_live_revenue: data.show_live_revenue ?? true
      }
    } catch (error) {
      console.error('Failed to update settings:', error)
      throw error
    }
  }

  return {
    settings,
    loading,
    fetchSettings,
    updateSettings
  }
}
