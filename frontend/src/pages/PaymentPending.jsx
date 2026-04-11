import { useEffect, useContext, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { UsuarioContext } from '../context/Usuario.jsx'
import { Header } from '../components/Header.jsx'
import { Footer } from '../components/Footer.jsx'
import { ToastContainer, toast } from 'react-toastify'

export function PaymentPending () {
  const { url } = useContext(UsuarioContext)
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const registrarPagoPendiente = async () => {
      try {
        const datosPagoStr = window.localStorage.getItem('datosPago')

        if (!datosPagoStr) {
          toast.error('No se encontraron datos del pago')
          setLoading(false)
          return
        }

        const datosPago = JSON.parse(datosPagoStr)

        // Registrar el pago pendiente en la base de datos
        const formData = new FormData()
        formData.append('idCliente', datosPago.idCliente)
        formData.append('idServicio', datosPago.idServicio)
        formData.append('idProveedor', datosPago.idProveedor)
        formData.append('precio', datosPago.precio)
        formData.append('titulo', datosPago.titulo)
        formData.append('estado', 'pendiente')

        const response = await fetch(`${url}/pago/registrarPendiente`, {
          method: 'POST',
          body: formData
        })

        const data = await response.json()

        if (data.success) {
          toast.info('Tu pago está siendo procesado')
          // Limpiar localStorage
          window.localStorage.removeItem('datosPago')
        } else {
          toast.warning('No se pudo registrar el estado del pago')
        }
      } catch (error) {
        console.error('Error:', error)
        toast.error('Error al procesar el estado del pago')
      } finally {
        setLoading(false)
      }
    }

    registrarPagoPendiente()
  }, [url])

  const handleGoHome = () => {
    navigate('/')
  }

  const handleViewProfile = () => {
    const userId = window.localStorage.getItem('userId')
    if (userId) {
      navigate(`/Perfil/${userId}`)
    } else {
      navigate('/')
    }
  }

  return (
    <>
      <Header />
      <main className='min-h-screen flex items-center justify-center bg-claro px-4'>
        <div className='max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center'>
          {loading
            ? (
              <>
                <div className='animate-spin rounded-full h-16 w-16 border-b-2 border-yellow-500 mx-auto mb-4' />
                <h2 className='text-2xl font-bold text-gray-900 mb-2'>Verificando tu pago...</h2>
                <p className='text-gray-600'>Por favor espera un momento</p>
              </>
              )
            : (
              <>
                <div className='bg-yellow-100 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4'>
                  <span className='material-icons text-yellow-600 text-4xl'>schedule</span>
                </div>
                <h2 className='text-2xl font-bold text-gray-900 mb-2'>Pago pendiente</h2>
                <p className='text-gray-600 mb-4'>
                  Tu pago está siendo procesado. Esto puede tardar unos minutos.
                </p>
                
                <div className='mb-6 p-4 bg-yellow-50 rounded-lg text-left'>
                  <h3 className='font-semibold text-gray-800 mb-2'>¿Qué significa esto?</h3>
                  <ul className='text-sm text-gray-600 space-y-2'>
                    <li>• El pago está en proceso de verificación</li>
                    <li>• Recibirás una notificación cuando se confirme</li>
                    <li>• Puede tardar entre unos minutos y 48 horas</li>
                    <li>• No intentes realizar el pago nuevamente</li>
                  </ul>
                </div>

                <div className='space-y-3'>
                  <button
                    onClick={handleViewProfile}
                    className='w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors'
                  >
                    Ver mis pagos
                  </button>
                  
                  <button
                    onClick={handleGoHome}
                    className='w-full bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors'
                  >
                    Volver al inicio
                  </button>
                </div>

                <p className='mt-6 text-sm text-gray-500'>
                  Te enviaremos un correo electrónico cuando el pago sea confirmado o rechazado.
                </p>
              </>
              )}
        </div>
      </main>
      <Footer />
      <ToastContainer />
    </>
  )
}
