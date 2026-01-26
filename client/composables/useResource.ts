export function useResource<T extends { id: number }>(endpoint: string) {
  const api = useApi()
  const items = ref<T[]>([]) as Ref<T[]>
  const loading = ref(true)
  const deletingId = ref<number | null>(null)

  const fetch = async () => {
    try {
      loading.value = true
      items.value = await api.api(endpoint)
    } catch (error) {
      console.error(`Failed to fetch ${endpoint}:`, error)
    } finally {
      loading.value = false
    }
  }

  const remove = async (id: number, name?: string) => {
    if (!confirm(`Are you sure you want to delete ${name || 'this item'}?`)) {
      return false
    }

    try {
      deletingId.value = id
      await api.api(`${endpoint}/${id}`, { method: 'DELETE' })
      items.value = items.value.filter(item => item.id !== id)
      return true
    } catch (error) {
      console.error(`Failed to delete from ${endpoint}:`, error)
      alert('Failed to delete. Please try again.')
      return false
    } finally {
      deletingId.value = null
    }
  }

  return {
    items,
    loading,
    deletingId,
    fetch,
    remove
  }
}
