export function LoginMp() {
  const clientId = import.meta.env.VITE_MP_CLIENT_ID
  // IMPORTANTE: Para OAuth, SIEMPRE usar el túnel del backend (sin el 2)
  // Esta URL debe coincidir con la configurada en Mercado Pago
  const backendUrl = import.meta.env.VITE_API_URL

  if (!clientId || !backendUrl) {
    console.error('Faltan VITE_MP_CLIENT_ID o VITE_API_URL en variables de entorno')
    return null
  }
  const redirectUri = encodeURIComponent(`${backendUrl}/pago/callback`)

  const handleConnect = () => {
    const url = `https://auth.mercadopago.com/authorization?response_type=code&client_id=${clientId}&redirect_uri=${redirectUri}&sandbox=true`
    window.location.href = url
  }

  return (
    <button
      onClick={handleConnect}
      className='w-full bg-primary hover:bg-primary/90 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-3'
    >
      <span className='material-icons'>account_balance_wallet</span>
      <span>Conectar Mercado Pago</span>
    </button>
  )
}
