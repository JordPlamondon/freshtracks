interface UseModalFormOptions<T> {
  endpoint: string
  entityName: string
  getFormData: () => T
  entityId?: () => number | null
}

export function useModalForm<T>(options: UseModalFormOptions<T>) {
  const api = useApi()
  const saving = ref(false)

  const save = async (emit: (event: 'save' | 'close') => void) => {
    if (saving.value) return false

    try {
      saving.value = true
      const data = options.getFormData()
      const id = options.entityId?.()

      if (id) {
        await api.api(`${options.endpoint}/${id}`, {
          method: 'PUT',
          body: data
        })
      } else {
        await api.api(options.endpoint, {
          method: 'POST',
          body: data
        })
      }

      emit('save')
      emit('close')
      return true
    } catch (error) {
      console.error(`Failed to save ${options.entityName}:`, error)
      alert(`Failed to save ${options.entityName}. Please try again.`)
      return false
    } finally {
      saving.value = false
    }
  }

  return {
    saving,
    save
  }
}
