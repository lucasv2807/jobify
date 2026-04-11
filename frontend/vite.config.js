import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'
import tailwindcss from '@tailwindcss/vite'
import { loadEnv } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), 'VITE_')
  const proxyTarget = env.VITE_API_PROXY_TARGET
  const proxyConfig = proxyTarget
    ? {
      '/servicios': {
        target: proxyTarget,
        changeOrigin: true,
        secure: true
        // keep path as-is: /servicios/getServicio -> https://.../servicios/getServicio
      }
    }
    : undefined

  return {
    plugins: [
      react(),
      tailwindcss()
    ],
    // Proxy API requests in development to avoid CORS issues when the remote API
    // does not send Access-Control-Allow-Origin. Requests starting with
    // /servicios will be proxied to the remote worker domain.
    server: {
      proxy: proxyConfig
    }
  }
})
