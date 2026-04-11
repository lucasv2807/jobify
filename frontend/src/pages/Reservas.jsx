import { useEffect, useContext, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { Header } from '../components/Header.jsx'
import { UsuarioContext } from '../context/Usuario.jsx'
import { toast } from 'react-toastify'
import { ConfirmButton } from '../components/ConfirmButton.jsx'
export function Reservas () {
  const { id } = useParams()
  const { url } = useContext(UsuarioContext)
  const [reserva, setReserva] = useState(null)
  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(`${url}/reserva/getReservaById?id=${id}`)
      const data = await response.json()
      if (!response.ok) {
        console.error('Error al cargar reserva:', data.message)
      }
      const reserva = data.data
      setReserva(reserva)
    }
    fetchData()
  }, [id, url])
  console.log(reserva)
  // Formatear la fecha para mostrarla mejor
  const formatearFecha = (fecha) => {
    if (!fecha) return ''
    const date = new Date(fecha)
    return date.toLocaleDateString('es-ES', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
  const handleCancelar = async () => {
    try {
      const data = new FormData()
      data.append('id', reserva.idReserva)
      const response = await fetch(`${url}/reserva/eliminarReserva`, {
        method: 'POST',
        body: data
      })
      const result = await response.json()
      if (!response.ok) {
        console.error('Error al cancelar reserva:', result.message)
        toast.error('No se pudo cancelar la reserva. Por favor, intenta de nuevo.')
      } else {
        toast.success('Reserva cancelada con éxito')
        // Recargar la página después de 1.5 segundos para que el usuario vea el mensaje
        setTimeout(() => {
          window.location.reload()
        }, 1500)
      }
    } catch (error) {
      console.error('Error al cancelar reserva:', error)
      toast.error('No se pudo cancelar la reserva. Por favor, intenta de nuevo.')
    }
  }
  return (
    <div className='min-h-screen flex flex-col bg-claro'>
      <Header />
      <main className='flex-1 p-6 md:p-10'>
        <div className='max-w-4xl mx-auto'>
          {reserva
            ? (
              <div className='bg-white rounded-2xl shadow-xl overflow-hidden'>
                {/* Header de la reserva */}
                <div className='bg-primary p-6 text-white'>
                  <div className='flex items-center gap-4'>
                    <span className='material-icons text-5xl'>event_available</span>
                    <div>
                      <h1 className='text-3xl font-bold'>Detalles de la Reserva</h1>
                      <p className='text-green-100 mt-1'>
                        {reserva.confirmado === 1 ? 'Confirmada' : 'Pendiente de confirmación'}
                      </p>
                    </div>
                  </div>
                </div>
                {/* Contenido principal */}
                <div className='p-6 md:p-8 space-y-6'>
                  {/* Información del cliente/proveedor */}
                  <Link to={`/perfil/${reserva.idUsuario}`} className='block'>
                    <div className='bg-green-50 rounded-xl p-6'>
                      <h2 className='text-xl font-bold text-primary mb-4 flex items-center gap-2'>
                        <span className='material-icons'>person</span>
                        Información de Contacto
                      </h2>
                      <div className='flex items-center gap-4'>
                        <img
                          src={reserva.imagen}
                          alt={`${reserva.nombre} ${reserva.apellido}`}
                          className='w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg'
                        />
                        <div className='flex-1'>
                          <p className='text-xl font-bold text-gray-900'>{reserva.nombre} {reserva.apellido}</p>
                          <div className='flex items-center gap-2 mt-2 text-gray-600'>
                            <span className='material-icons text-sm'>email</span>
                            <p className='text-sm'>{reserva.email}</p>
                          </div>
                          {reserva.fechaNac && (
                            <div className='flex items-center gap-2 mt-1 text-gray-600'>
                              <span className='material-icons text-sm'>cake</span>
                              <p className='text-sm'>Fecha de nacimiento: {new Date(reserva.fechaNac).toLocaleDateString('es-ES')}</p>
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  </Link>
                  {/* Información del servicio */}
                  <Link to={`/Buy/${reserva.idServicio}`} className='block'>
                    <div className='bg-gray-50 rounded-xl p-6'>
                      <h2 className='text-xl font-bold text-primary mb-4 flex items-center gap-2'>
                        <span className='material-icons'>work</span>
                        Servicio Solicitado
                      </h2>
                      <div className='space-y-3'>
                        <div className='flex items-start gap-3'>
                          <span className='material-icons text-primary'>title</span>
                          <div>
                            <p className='text-sm text-gray-600'>Título</p>
                            <p className='text-lg font-semibold text-gray-900'>{reserva.titulo}</p>
                          </div>
                        </div>
                        {reserva.descripcion && (
                          <div className='flex items-start gap-3'>
                            <span className='material-icons text-primary'>description</span>
                            <div>
                              <p className='text-sm text-gray-600'>Descripción</p>
                              <p className='text-gray-800'>{reserva.descripcion}</p>
                            </div>
                          </div>
                        )}
                        <div className='flex items-start gap-3'>
                          <span className='material-icons text-primary'>attach_money</span>
                          <div>
                            <p className='text-sm text-gray-600'>Precio</p>
                            <p className='text-2xl font-bold text-primary'>${reserva.precio}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </Link>
                  {/* Fecha de la reserva */}
                  <div className='bg-blue-50 rounded-xl p-6'>
                    <h2 className='text-xl font-bold text-primary mb-4 flex items-center gap-2'>
                      <span className='material-icons'>calendar_today</span>
                      Fecha Programada
                    </h2>
                    <div className='flex items-center gap-3'>
                      <div className='bg-white rounded-lg p-4 shadow-sm'>
                        <p className='text-3xl font-bold text-primary'>{new Date(reserva.fecha).getDate()}</p>
                        <p className='text-sm text-gray-600 uppercase'>{new Date(reserva.fecha).toLocaleDateString('es-ES', { month: 'short' })}</p>
                      </div>
                      <div>
                        <p className='text-lg font-semibold text-gray-900'>{formatearFecha(reserva.fecha)}</p>
                        <p className='text-sm text-gray-600 capitalize'>{reserva.tipo === 'schedule' ? 'Reserva programada' : 'Reserva inmediata'}</p>
                      </div>
                    </div>
                  </div>

                  {/* Estado de confirmación visual */}
                  {reserva.confirmado === 1
                    ? (
                      <>
                        <div className='items-center flex flex-col min-w-full'>
                          <div className='bg-green-100 min-w-full border-2 border-green-500 rounded-xl p-4 flex items-center gap-3'>
                            <span className='material-icons text-green-600 text-3xl'>check_circle</span>
                            <div>
                              <p className='font-bold text-green-800'>Reserva Confirmada</p>
                              <p className='text-sm text-green-700'>Esta reserva ha sido confirmada por el proveedor</p>
                            </div>
                          </div>
                          <button onClick={handleCancelar} className='mt-6 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center gap-2 mx-auto'>
                            <span className='material-icons'>cancel</span>
                            Cancelar Servicio
                          </button>
                        </div>
                      </>
                      )
                    : (
                      <ConfirmButton
                        url={url}
                        idCliente={reserva.idCliente}
                        idServicio={reserva.idServicio}
                        idReserva={reserva.idReserva}
                        reserva={reserva}
                        handleCancelar={handleCancelar}
                      />
                      )}
                </div>
              </div>
              )
            : (
              <div className='flex flex-col items-center justify-center'>
                <div className='relative w-30 h-30 mb-8'>
                  {/* Círculo rojo */}
                  <div className='absolute inset-0 rounded-full border-8 border-red-500 bg-red-50' />
                  {/* X roja grande */}
                  <div className='absolute inset-0 flex items-center justify-center'>
                    <span className='material-icons text-red-600' style={{ fontSize: '120px', fontWeight: 'bold' }}>close</span>
                  </div>
                </div>
                <h2 className='text-3xl font-bold text-red-600 mb-3'>Reserva No Encontrada</h2>
                <p className='text-gray-600 text-lg text-center max-w-md'>
                  Lo sentimos, no se pudo encontrar la reserva que estás buscando. Es posible que haya sido eliminada o no exista.
                </p>
                <Link to='/' className='mt-8 bg-primary hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-xl shadow-lg transition-all duration-300'>
                  Volver al Inicio
                </Link>
              </div>
              )}
        </div>
      </main>
    </div>
  )
}
