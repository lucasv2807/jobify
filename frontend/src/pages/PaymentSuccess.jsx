import { useEffect, useContext, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { UsuarioContext } from '../context/Usuario.jsx'
import { Header } from '../components/Header.jsx'
import { Footer } from '../components/Footer.jsx'
import { ToastContainer, toast } from 'react-toastify'

export function PaymentSuccess () {
  const { url } = useContext(UsuarioContext)
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [loading, setLoading] = useState(true)
  const [success, setSuccess] = useState(false)
  const [datosPago, setDatosPago] = useState(null)
  const [fecha, setFecha] = useState('')

  // Calcular fecha mínima (mañana) y máxima (2 meses desde hoy)
  const getFechaMinima = () => {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    return tomorrow.toISOString().split('T')[0]
  }

  const getFechaMaxima = () => {
    const maxDate = new Date()
    maxDate.setMonth(maxDate.getMonth() + 2)
    return maxDate.toISOString().split('T')[0]
  }

  useEffect(() => {
    const registrarPago = async () => {
      try {
        // Obtener parámetros de MercadoPago de la URL
        const status = searchParams.get('status')

        // Obtener datos guardados en localStorage
        const datosPagoStr = window.localStorage.getItem('datosPago')

        if (!datosPagoStr) {
          console.error('No se encontraron datos del pago en localStorage')
          toast.error('Ocurrió un error al procesar el pago. Por favor, contacta a soporte.')
          setLoading(false)
          return
        }

        const datosPagoLocal = JSON.parse(datosPagoStr)
        setDatosPago(datosPagoLocal)

        // Verificar que el estado sea aprobado
        if (status && status !== 'approved') {
          toast.warning('El pago no fue aprobado')
          setLoading(false)
          return
        }

        // Registrar el pago en tu base de datos
        const formData = new FormData()
        formData.append('idCliente', datosPagoLocal.idCliente)
        formData.append('idServicio', datosPagoLocal.idServicio)
        formData.append('idProveedor', datosPagoLocal.idProveedor)
        formData.append('precio', datosPagoLocal.precio)
        formData.append('titulo', datosPagoLocal.titulo)

        const response = await fetch(`${url}/pago/pagar`, {
          method: 'POST',
          body: formData
        })

        const data = await response.json()

        if (data.success) {
          setSuccess(true)
        } else {
          console.error('Error al registrar pago:', data.message)
          toast.error('Ocurrió un error al procesar el pago. Por favor, contacta a soporte.')
        }
      } catch (error) {
        console.error('Error:', error)
        toast.error('Ocurrió un error al procesar el pago. Por favor, contacta a soporte.')
      } finally {
        setLoading(false)
      }
    }

    registrarPago()
  }, [url, searchParams])

  const handleRegistrarReserva = async (e) => {
    e.preventDefault()

    // Validar que la fecha esté en el rango permitido
    const fechaSeleccionada = new Date(fecha)
    const fechaMinima = new Date(getFechaMinima())
    const fechaMaxima = new Date(getFechaMaxima())

    if (fechaSeleccionada < fechaMinima) {
      toast.error('La fecha debe ser al menos un día en el futuro')
      return
    }

    if (fechaSeleccionada > fechaMaxima) {
      toast.error('La fecha no puede ser mayor a 2 meses desde hoy')
      return
    }

    try {
      const formData = new FormData()
      formData.append('idServicio', datosPago.idServicio)
      formData.append('idCliente', datosPago.idCliente)
      formData.append('fecha', fecha)

      const response = await fetch(`${url}/reserva/crear`, {
        method: 'POST',
        body: formData
      })

      const data = await response.json()

      if (data.success) {
        // Limpiar localStorage
        window.localStorage.removeItem('datosPago')
        toast.success('Reserva registrada con éxito')
        navigate('/')
      } else {
        console.error('Error al registrar reserva:', data.message)
        toast.error('No se pudo registrar la reserva. Por favor, intenta de nuevo.')
      }
    } catch (error) {
      console.error('Error:', error)
      toast.error('No se pudo registrar la reserva. Por favor, intenta de nuevo.')
    }
  }

  return (
    <>
      <Header />
      <main className='min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-blue-50 px-4 py-12'>
        <div className='max-w-lg w-full bg-white rounded-2xl shadow-2xl overflow-hidden'>
          {loading
            ? (
              <div className='p-12 text-center'>
                <div className='relative mb-8'>
                  <div className='animate-spin rounded-full h-20 w-20 border-4 border-gray-200 border-t-primary mx-auto' />
                  <div className='absolute inset-0 flex items-center justify-center'>
                    <span className='material-icons text-primary text-3xl animate-pulse'>payments</span>
                  </div>
                </div>
                <h2 className='text-3xl font-bold text-gray-900 mb-3'>Procesando tu pago</h2>
                <p className='text-gray-600 text-lg'>Por favor espera mientras confirmamos tu transacción</p>
                <div className='mt-6 flex justify-center gap-2'>
                  <div className='w-2 h-2 bg-primary rounded-full animate-bounce' style={{ animationDelay: '0s' }} />
                  <div className='w-2 h-2 bg-primary rounded-full animate-bounce' style={{ animationDelay: '0.2s' }} />
                  <div className='w-2 h-2 bg-primary rounded-full animate-bounce' style={{ animationDelay: '0.4s' }} />
                </div>
              </div>
              )
            : !success
              ? (
                <div className='p-8'>
                  {/* Header de éxito */}
                  <div className='bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white text-center mb-8'>
                    <div className='bg-white rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4 shadow-lg'>
                      <span className='material-icons text-green-600 text-5xl'>check_circle</span>
                    </div>
                    <h2 className='text-3xl font-bold mb-2'>¡Pago Exitoso!</h2>
                    <p className='text-green-100 text-lg'>Tu pago ha sido procesado correctamente</p>
                  </div>

                  {/* Información del servicio */}
                  {datosPago && (
                    <div className='bg-gray-50 rounded-xl p-6 mb-6'>
                      <h3 className='text-lg font-bold text-gray-900 mb-4 flex items-center gap-2'>
                        <span className='material-icons text-primary'>receipt_long</span>
                        Resumen del Pago
                      </h3>
                      <div className='space-y-3'>
                        <div className='flex justify-between items-center'>
                          <span className='text-gray-600'>Servicio:</span>
                          <span className='font-semibold text-gray-900'>{datosPago.titulo}</span>
                        </div>
                        <div className='flex justify-between items-center'>
                          <span className='text-gray-600'>Monto pagado:</span>
                          <span className='font-bold text-2xl text-primary'>${datosPago.precio}</span>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Formulario de reserva */}
                  <form onSubmit={handleRegistrarReserva} className='space-y-6'>
                    <div>
                      <label className='block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2'>
                        <span className='material-icons text-primary text-lg'>calendar_today</span>
                        Selecciona la fecha de tu reserva
                      </label>
                      <input
                        type='date'
                        name='fecha'
                        value={fecha}
                        onChange={(e) => setFecha(e.target.value)}
                        required
                        min={getFechaMinima()}
                        max={getFechaMaxima()}
                        className='w-full border-2 border-gray-300 rounded-xl p-4 text-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all'
                      />
                      <p className='mt-2 text-sm text-gray-500 flex items-center gap-1'>
                        <span className='material-icons text-xs'>info</span>
                        Reserva desde mañana hasta 2 meses en el futuro
                      </p>
                    </div>

                    <button
                      type='submit'
                      disabled={!fecha}
                      className='w-full bg-gradient-to-r from-primary to-green-600 text-white font-bold py-4 px-6 rounded-xl hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2'
                    >
                      <span className='material-icons'>event_available</span>
                      Confirmar Reserva
                    </button>

                    <button
                      type='button'
                      onClick={() => {
                        window.localStorage.removeItem('datosPago')
                        navigate('/')
                      }}
                      className='w-full bg-gray-100 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center gap-2'
                    >
                      <span className='material-icons'>home</span>
                      Volver al inicio
                    </button>
                  </form>
                </div>
                )
              : (
                <div className='p-12 text-center'>
                  {/* Header de error */}
                  <div className='bg-red-100 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-6 shadow-lg'>
                    <span className='material-icons text-red-600 text-6xl md-48'>error_outline</span>
                  </div>
                  <h2 className='text-3xl font-bold text-gray-900 mb-3'>Error al Procesar</h2>
                  <p className='text-gray-600 text-lg mb-8'>Hubo un problema al registrar tu pago. Por favor, contacta a soporte si el problema persiste.</p>
                  
                  <div className='space-y-3'>
                    <button
                      onClick={() => navigate('/')}
                      className='w-full bg-primary text-white font-bold py-4 px-6 rounded-xl hover:bg-green-600 transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2'
                    >
                      <span className='material-icons'>home</span>
                      Volver a Inicio
                    </button>
                    
                    <button
                      onClick={() => window.location.reload()}
                      className='w-full bg-gray-100 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center gap-2'
                    >
                      <span className='material-icons'>refresh</span>
                      Reintentar
                    </button>
                  </div>
                </div>
                )}
        </div>
      </main>
      <Footer />
      <ToastContainer />
    </>
  )
}
