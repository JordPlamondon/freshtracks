<template>
  <div class="min-h-screen bg-primary-bg text-primary-text">
    <nav class="border-b border-gray-800 bg-primary-bg/80 backdrop-blur">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center space-x-8">
            <NuxtLink to="/" class="text-xl font-bold">TallyHo</NuxtLink>
            <NuxtLink to="/clients" class="hover:text-primary-accent">Clients</NuxtLink>
            <NuxtLink to="/projects" class="hover:text-primary-accent">Projects</NuxtLink>
            <NuxtLink to="/time-entries" class="hover:text-primary-accent">Time Entries</NuxtLink>
            <NuxtLink to="/invoices" class="hover:text-primary-accent">Invoices</NuxtLink>
          </div>
          <div class="flex items-center">
            <button @click="logout" class="px-4 py-2 text-sm hover:text-primary-accent">
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
const { api, token } = useApi()

const logout = async () => {
  try {
    await api('/logout', { method: 'POST' })
  } finally {
    token.value = null
    navigateTo('/login')
  }
}
</script>
