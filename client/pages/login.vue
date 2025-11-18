<template>
  <div class="min-h-screen bg-primary-bg text-primary-text flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="text-center text-3xl font-bold">TallyHo</h2>
        <p class="mt-2 text-center text-sm text-gray-400">
          Sign in to your account
        </p>
      </div>
      
      <form @submit.prevent="login" class="mt-8 space-y-6">
        <div v-if="error" class="p-3 bg-red-900/50 border border-red-800 rounded-md text-sm">
          {{ error }}
        </div>
        
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium mb-2">
              Email address
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-accent"
              placeholder="you@example.com"
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-accent"
              placeholder="••••••••"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2 px-4 bg-primary-accent hover:bg-indigo-700 disabled:bg-gray-700 rounded-md font-medium disabled:cursor-not-allowed"
        >
          {{ loading ? 'Signing in...' : 'Sign in' }}
        </button>
        
        <p class="text-center text-sm text-gray-400">
          Demo: demo@tallyho.test / password
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

const loginApi = useApi()
const email = ref('demo@tallyho.test')
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
