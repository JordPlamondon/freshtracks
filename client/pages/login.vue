<template>
  <div class="min-h-screen bg-sidebar-bg flex flex-col items-center pt-24">
    <!-- Logo -->
    <div class="flex items-center gap-2 mb-8">
      <img src="/freshtracks-logo.svg" alt="FreshTracks" class="h-8 w-8 flex-shrink-0" />
      <span class="text-2xl font-bold text-text-primary">FreshTracks</span>
    </div>

    <div class="bg-white p-8 rounded-lg border border-border-light w-full max-w-md shadow-[0_1px_4px_0_rgba(0,0,0,0.11)]">
      <h1 class="text-2xl font-semibold text-text-primary mb-6">Sign In</h1>

      <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
        {{ error }}
      </div>

      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-text-primary mb-1">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-text-primary mb-1">Password</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-3 py-2 border border-border-light rounded-md focus:outline-none"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="btn-accent w-full py-2 rounded-md font-medium text-text-primary disabled:opacity-50"
        >
          <span class="relative z-[1]">{{ loading ? 'Signing in...' : 'Sign in' }}</span>
        </button>

      </form>
    </div>

    <!-- Demo Notice -->
    <div class="mt-6 text-center max-w-md">
      <div class="bg-white/60 backdrop-blur-sm border border-border-light rounded-lg px-4 py-3">
        <p class="text-sm font-medium text-text-primary">Demo Mode</p>
        <p class="text-sm text-text-secondary mt-1">Feel free to explore! Data resets every 3 hours.</p>
        <p class="text-xs text-text-secondary mt-1">Credentials: demo@freshtracks.test / password</p>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

const loginApi = useApi()
const email = ref('demo@freshtracks.test')
const password = ref('password')
const error = ref('')
const loading = ref(false)

const login = async () => {
  error.value = ''
  loading.value = true

  try {
    const data = await loginApi.api('/login', {
      method: 'POST',
      body: {
        email: email.value,
        password: password.value
      }
    })

    loginApi.token.value = data.token
    navigateTo('/')
  } catch (err) {
    error.value = 'Invalid credentials. Please try again.'
    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}
</script>
