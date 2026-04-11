import { useEffect, useContext } from 'react'
import { useNavigate } from 'react-router-dom'
import { UsuarioContext } from '../context/Usuario.jsx'
import { Header } from '../components/Header.jsx'
import { Footer } from '../components/Footer.jsx'
import { ToastContainer, toast } from 'react-toastify'

export function PaymentFailure () {
  const { url } = useContext(UsuarioContext)
  const navigate = useNavigate()

  useEffect(() => {
    // Limpiar datos del pago fallido
    const datosPagoStr = window.localStorage.getItem('datosPago')
    
    if (datosPagoStr) {
      const datosPago = JSON.parse(datosPagoStr)
      
      // Opcional: registrar el pago fallido en el backend
      const registrarFallo = async () => {
        try {
          const formData = new FormData()
          formData.append('idCliente', datosPago.idCliente)
          formData.append('idServicio', datosPago.idServicio)
          formData.append('estado', 'fallido')

          await fetch(`${url}/pago/registrarFallo`, {
            method: 'POST',
            body: formData
          })
        } catch (error) {
          console.error('Error al registrar fallo:', error)
        }
      }

      registrarFallo()
      
      // No limpiar localStorage para permitir reintentar
      toast.error('El pago no pudo ser procesado')
    }
  }, [url])

  const handleRetry = () => {
    const datosPagoStr = window.localStorage.getItem('datosPago')
    if (datosPagoStr) {
      const datosPago = JSON.parse(datosPagoStr)
      // Redirigir al servicio para reintentar el pago
      navigate(`/Buy/${datosPago.idServicio}`)
    } else {
      navigate('/')
    }
  }

  const handleCancel = () => {
    // Limpiar localStorage y volver al inicio
    window.localStorage.removeItem('datosPago')
    navigate('/')
  }

  return (
    <>
      <Header />
      <main className='min-h-screen flex items-center justify-center bg-claro px-4'>
        <div className='max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center'>
          <div className='bg-red-100 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4'>
            <span className='material-icons text-red-600 text-4xl'>cancel</span>
          </div>
          <h2 className='text-2xl font-bold text-gray-900 mb-2'>Pago rechazado</h2>
          <p className='text-gray-600 mb-6'>
            Tu pago no pudo ser procesado. Esto puede deberse a fondos insuficientes, 
            problemas con tu tarjeta o límites de tu banco.
          </p>
          
          <div className='space-y-3'>
            <button
              onClick={handleRetry}
              className='w-full bg-primary text-white font-bold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors'
            >
              Intentar nuevamente
            </button>
            
            <button
              onClick={handleCancel}
              className='w-full bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors'
            >
              Cancelar y volver al inicio
            </button>
          </div>
          
          <div className='mt-6 p-4 bg-blue-50 rounded-lg text-left'>
            <h3 className='font-semibold text-gray-800 mb-2'>Sugerencias:</h3>
            <ul className='text-sm text-gray-600 space-y-1'>
              <li>• Verifica que tengas fondos suficientes</li>
              <li>• Confirma que los datos de tu tarjeta sean correctos</li>
              <li>• Intenta con otro método de pago</li>
              <li>• Contacta a tu banco si el problema persiste</li>
            </ul>
          </div>
        </div>
      </main>
      <Footer />
      <ToastContainer />
    </>
  )
}
