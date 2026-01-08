import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

if (typeof window !== 'undefined') {
  (window as any).Pusher = Pusher
}

let echoInstance: Echo | null = null

export const useEcho = () => {
  const config = useRuntimeConfig()

  const initEcho = (userId: number = 1) => {
    if (typeof window === 'undefined') return null
    if (echoInstance) return echoInstance

    const reverbConfig = config.public.reverb as {
      appKey: string
      host: string
      port: string
      scheme: string
    }

    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: reverbConfig.appKey,
      wsHost: reverbConfig.host,
      wsPort: parseInt(reverbConfig.port),
      wssPort: parseInt(reverbConfig.port),
      forceTLS: reverbConfig.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      disableStats: true,
    })

    return echoInstance
  }

  const getEcho = () => echoInstance

  const disconnect = () => {
    if (echoInstance) {
      echoInstance.disconnect()
      echoInstance = null
    }
  }

  return {
    initEcho,
    getEcho,
    disconnect
  }
}
