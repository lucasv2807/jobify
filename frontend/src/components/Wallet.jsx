import { initMercadoPago, Wallet } from '@mercadopago/sdk-react'
import { useEffect, useState, useContext } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'

export function WalletComponent ({
  preferenceId,
  publicKey,
  idServicio,
  idProveedor,
  precio,
  titulo
}) {
  const [initialized, setInitialized] = useState(false)
  const [error, setError] = useState(null)
  const { user } = useContext(UsuarioContext)

  useEffect(() => {
    // Guardar datos del pago en localStorage cuando tengamos todos los datos
    if (idServicio && idProveedor && precio && titulo && user?.id) {
      const datosPago = {
        idCliente: user.id,
        idServicio,
        idProveedor,
        precio,
        titulo
      }
      window.localStorage.setItem('datosPago', JSON.stringify(datosPago))
    }
  }, [idServicio, idProveedor, precio, titulo, user])

  useEffect(() => {
    // Inicializar MercadoPago solo una vez cuando tengamos la public key
    if (publicKey && !initialized) {
      try {
        initMercadoPago(publicKey, { locale: 'es-UY' })
        setInitialized(true)
      } catch (err) {
        console.error('❌ Error al inicializar MercadoPago:', err)
        setError('Error al inicializar el sistema de pagos')
      }
    }
  }, [publicKey, initialized, preferenceId])
  // Si hay error de inicialización
  if (error) {
    return (
      <div className='mt-6 w-full bg-red-50 border-2 border-red-500 text-red-700 font-semibold py-3 px-6 rounded-lg flex items-center'>
        <span className='material-icons mr-2'>error</span>
        {error}
      </div>
    )
  }

  // Si no hay publicKey, mostrar mensaje
  if (!publicKey) {
    return (
      <div className='mt-6 w-full bg-red-50 border-2 border-red-500 text-red-700 font-semibold py-3 px-6 rounded-lg flex items-center'>
        <span className='material-icons mr-2'>error</span>
        El proveedor no ha configurado su cuenta de Mercado Pago
      </div>
    )
  }

  // Si no hay preferenceId, mostrar loading
  if (!preferenceId) {
    return (
      <div className='mt-6 w-full bg-yellow-50 border-2 border-yellow-500 text-yellow-700 font-semibold py-3 px-6 rounded-lg flex items-center'>
        <span className='material-icons mr-2 animate-spin'>refresh</span>
        Generando preferencia de pago...
      </div>
    )
  }

  // Si MercadoPago no está inicializado, mostrar loading
  if (!initialized) {
    return (
      <div className='mt-6 w-full bg-blue-50 border-2 border-blue-500 text-blue-700 font-semibold py-3 px-6 rounded-lg flex items-center'>
        <span className='material-icons mr-2 animate-spin'>refresh</span>
        Inicializando Mercado Pago...
      </div>
    )
  }

  // Renderizar el Wallet de MercadoPago
  return (
    <div className='mt-6 w-full'>
      <Wallet
        initialization={{ preferenceId }}
        customization={{
          texts: {
            valueProp: 'smart_option'
          },
          visual: {
            buttonBackground: 'default',
            borderRadius: '8px',
            verticalPadding: '12px'
          }
        }}
      />
    </div>
  )
}
