export default defineNuxtRouteMiddleware((to, from) => {
  const { token } = useApi()

  // If no token, redirect to login
  if (!token.value) {
    return navigateTo('/login')
  }
})
