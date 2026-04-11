import { useEffect, useState, useContext } from 'react'
import { Footer } from '../components/Footer.jsx'
import { Header } from '../components/Header.jsx'
import { UsuarioContext } from '../context/Usuario.jsx'
import { NotificacionesContext } from '../context/Notificaciones.jsx'
import { ToastContainer } from 'react-toastify'
import { Link } from 'react-router-dom'

export function Notificaciones () {
  const [notificaciones, setNotificaciones] = useState([])
  const [mensajes, setMensajes] = useState([])
  const [reservas, setReservas] = useState([])
  const [pagos, setPagos] = useState([])
  const { url, user } = useContext(UsuarioContext)
  const { marcarComoVistas } = useContext(NotificacionesContext)
  const URL_MENSAJES = `${url}/chat/${user.esProveedor ? 'chatsProveedor' : 'chatsCliente'}?id=${user.id}`
  const URL_PAGOS = `${url}/pago/${user.esProveedor ? 'historialProveedor' : 'historialCliente'}?id=${user.id}`
  const URL_RESERVAS = `${url}/reserva/${user.esProveedor ? 'getReservaProveedor' : 'getReservaCliente'}?id=${user.id}`
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(URL_MENSAJES)
        const data = await response.json()
        if (!response.ok) {
          console.error('Error al cargar mensajes:', data.message)
          setMensajes([])
        } else {
          setMensajes(Array.isArray(data.data) ? data.data : [])
        }
      } catch (error) {
        console.error('Error fetching notifications:', error)
        setMensajes([])
      }
    }
    fetchData()
  }, [URL_MENSAJES])
  useEffect(() => {
    const fetchReservas = async () => {
      try {
        const response = await fetch(URL_RESERVAS)
        const data = await response.json()
        if (!response.ok) {
          console.error('Error al cargar reservas:', data.message)
          setReservas([])
        } else {
          setReservas(Array.isArray(data.data) ? data.data : [])
        }
      } catch (error) {
        console.error('Error fetching reservations:', error)
        setReservas([])
      }
    }
    fetchReservas()
  }, [URL_RESERVAS])
  useEffect(() => {
    const fetchPagos = async () => {
      try {
        const response = await fetch(URL_PAGOS)
        const data = await response.json()
        if (!response.ok) {
          console.error('Error al cargar pagos:', data.message)
          setPagos([])
        } else {
          // Agregar tipo 'paid' a cada pago
          const pagosConTipo = (Array.isArray(data.data) ? data.data : []).map(pago => ({
            ...pago,
            tipo: 'paid'
          }))
          setPagos(pagosConTipo)
        }
      } catch (error) {
        console.error('Error fetching payments:', error)
        setPagos([])
      }
    }
    fetchPagos()
  }, [URL_PAGOS])
  useEffect(() => {
    setNotificaciones([...mensajes, ...reservas, ...pagos])
  }, [mensajes, reservas, pagos])

  // Marcar notificaciones como vistas cuando el usuario entra a la página
  useEffect(() => {
    if (notificaciones.length > 0) {
      // Esperar 1 segundo antes de marcar como vistas para que el usuario vea que hay notificaciones
      const timer = setTimeout(() => {
        marcarComoVistas()
      }, 1000)
      return () => clearTimeout(timer)
    }
  }, [notificaciones, marcarComoVistas])

  const TYPES_NOTIFICACION = {
    MENSAJE: 'Chat',
    RESERVA: 'schedule',
    PAGOS: 'paid',
    ALL: 'Todas'
  }
  const [filtro, setFiltro] = useState(TYPES_NOTIFICACION.ALL)

  const handleFiltroClick = (type) => {
    setFiltro(type)
  }
  const styles = (type) => {
    return filtro === type ? 'flex h-8 shrink-0 items-center justify-center gap-x-2 rounded-md bg-white text-azul-grisaceo px-4 shadow-sm' : 'flex h-8 shrink-0 items-center justify-center gap-x-2 rounded-md px-4 text-gray-500'
  }
  return (
    <>
      <Header />
      <main className='flex-1 px-4 sm:px-6 lg:px-8 py-8 bg-claro min-h-screen'>
        <div className='max-w-4xl mx-auto'>
          <div className='flex flex-wrap justify-between items-center gap-4 mb-8'>
            <h1 className='text-primary text-4xl font-black leading-tight tracking-[-0.033em] min-w-72'>Notificaciones</h1>
          </div>
          <div className='flex gap-3 p-1 mb-6 rounded-lg bg-gray-100 self-start'>
            <button className={styles(TYPES_NOTIFICACION.ALL)} onClick={() => handleFiltroClick(TYPES_NOTIFICACION.ALL)}>
              <p className='text-sm font-medium leading-normal'>Todas</p>
            </button>
            <button className={styles(TYPES_NOTIFICACION.MENSAJE)} onClick={() => handleFiltroClick(TYPES_NOTIFICACION.MENSAJE)}>
              <p className='text-sm font-medium leading-normal'>Mensajes</p>
            </button>
            <button className={styles(TYPES_NOTIFICACION.RESERVA)} onClick={() => handleFiltroClick(TYPES_NOTIFICACION.RESERVA)}>
              <p className='text-sm font-medium leading-normal'>Reservas</p>
            </button>
            <button className={styles(TYPES_NOTIFICACION.PAGOS)} onClick={() => handleFiltroClick(TYPES_NOTIFICACION.PAGOS)}>
              <p className='text-sm font-medium leading-normal'>Pagos</p>
            </button>
          </div>
          <div className='space-y-4'>
            {
              (() => {
                const notificacionesFiltradas = notificaciones
                  .filter(n => filtro === TYPES_NOTIFICACION.ALL || n.tipo === filtro)
                  .sort((a, b) => {
                    // Las reservas pendientes (confirmado === 0) van primero
                    if (a.tipo === 'schedule' && b.tipo === 'schedule') {
                      const aConfirmado = a.confirmado === 1 || a.confirmado === true
                      const bConfirmado = b.confirmado === 1 || b.confirmado === true
                      
                      if (aConfirmado === bConfirmado) return 0
                      return aConfirmado ? 1 : -1 // Pendientes (false) primero
                    }
                    return 0
                  })

                if (notificacionesFiltradas.length > 0) {
                  return notificacionesFiltradas.map((notificacion, index) => (
                    <Notificacion
                      key={index}
                      notificacion={notificacion}
                      user={user}
                    />
                  ))
                } else {
                  return (
                    <div className='text-center py-16'>
                      <div className='inline-block bg-gray-200 p-6 rounded-full'>
                        <span className='material-icons text-5xl text-gray-400'>notifications_off</span>
                      </div>
                      <h3 className='text-xl font-bold mt-6 text-azul-grisaceo'>No tienes notificaciones nuevas.</h3>
                      <p className='text-gray-500 mt-2'>Aquí aparecerán las actualizaciones importantes de tus servicios y mensajes.</p>
                    </div>
                  )
                }
              })()
            }
          </div>
        </div>
      </main>
      <Footer />
      <ToastContainer
        position='bottom-right'
      />
    </>
  )
}

function Notificacion ({ notificacion, user }) {
  // Renderiza un componente distinto según el tipo de notificación
  const renderNotificacionPorTipo = () => {
    switch (notificacion.tipo) {
      case 'Chat':
        return <NotificacionMensaje notificacion={notificacion} />
      case 'schedule':
        return <NotificacionReserva notificacion={notificacion} user={user} />
      case 'paid':
      case 'Cita':
        return <NotificacionPago notificacion={notificacion} />
      default:
        return null
    }
  }

  return renderNotificacionPorTipo()
}

// Componente para notificaciones de tipo MENSAJE/Chat
function NotificacionMensaje ({ notificacion }) {
  // Los datos de chat ya vienen con el nombre de la otra persona (cliente para proveedor, proveedor para cliente)
  const nombre = notificacion.nombre
  const apellido = notificacion.apellido

  return (
    <Link
      to={`/chat/${notificacion.idCliente}-${notificacion.idServicio}-${notificacion.idProveedor}`}
      className='block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow'
    >
      <div className='flex items-center mb-2 gap-4 h-10'>
        <div className='flex items-center justify-center shrink-0'>
          <span className='material-icons md-36'>
            chat
          </span>
        </div>
        <div className='flex-1 h-full flex items-center shrink-0'>
          <img src={notificacion.imagen} alt={nombre} className='h-full rounded-full mr-4' />
          <div>
            <span className='text-sm font-medium leading-normal'>{nombre} {apellido}</span>
            <div className='text-gray-600 text-sm'>{notificacion.servicioTitulo}</div>
          </div>
        </div>
      </div>
    </Link>
  )
}

// Componente para notificaciones de tipo RESERVA
function NotificacionReserva ({ notificacion, user }) {
  // Determinar si mostrar datos del cliente o del proveedor
  const nombre = user.esProveedor ? notificacion.nombreCliente : notificacion.nombreProveedor
  const apellido = user.esProveedor ? notificacion.apellidoCliente : notificacion.apellidoProveedor
  const estaConfirmada = notificacion.confirmado === 1 || notificacion.confirmado === true

  return (
    <Link
      to={`/reservas/${notificacion.idReserva}`}
      className='block bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4'
      style={{ borderLeftColor: estaConfirmada ? '#10b981' : '#f59e0b' }}
    >
      <div className='flex items-center gap-4'>
        {/* Ícono principal */}
        <div className='flex items-center justify-center shrink-0'>
          <span className='material-icons md-36 text-primary'>
            schedule
          </span>
        </div>

        {/* Contenido principal */}
        <div className='flex-1 flex items-center justify-between gap-4'>
          {/* Información de la persona */}
          <div className='flex items-center gap-3'>
            <img 
              src={notificacion.imagen} 
              alt={nombre} 
              className='h-10 w-10 rounded-full object-cover border-2 border-gray-200' 
            />
            <div>
              <div className='flex items-center gap-2'>
                <span className='text-sm font-semibold text-gray-900'>{nombre} {apellido}</span>
                {estaConfirmada && (
                  <span className='flex items-center gap-1 text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded-full'>
                    <span className='material-icons text-xs'>check_circle</span>
                    Confirmada
                  </span>
                )}
                {!estaConfirmada && (
                  <span className='flex items-center gap-1 text-xs font-medium text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full'>
                    <span className='material-icons text-xs'>schedule</span>
                    Pendiente
                  </span>
                )}
              </div>
              <div className='text-gray-600 text-sm'>{notificacion.servicioTitulo}</div>
            </div>
          </div>

          {/* Fecha */}
          <div className='flex flex-col items-end'>
            <div className='text-sm font-medium text-gray-700'>
              {new Date(notificacion.fecha).toLocaleDateString('es-ES', { 
                day: '2-digit', 
                month: 'short' 
              })}
            </div>
            <div className='text-xs text-gray-500'>
              {new Date(notificacion.fecha).toLocaleDateString('es-ES', { 
                year: 'numeric' 
              })}
            </div>
          </div>
        </div>
      </div>
    </Link>
  )
}

// Componente para notificaciones de tipo PAGO/Cita
function NotificacionPago ({ notificacion }) {
  // Los datos de pago ya vienen con el nombre de la otra persona (cliente para proveedor, proveedor para cliente)
  const nombre = notificacion.nombre
  const apellido = notificacion.apellido
  const imagen = notificacion.imagen

  return (
    <div className='block bg-white p-4 rounded-lg shadow-sm'>
      <div className='flex items-center mb-2 gap-4 h-10'>
        <div className='flex items-center justify-center shrink-0'>
          <span className='material-icons md-36'>
            paid
          </span>
        </div>
        <div className='flex-1 h-full flex items-center shrink-0 justify-between'>
          <div className='flex-1 h-full flex items-center shrink-0'>
            <img src={imagen} alt={nombre} className='h-full rounded-full mr-4' />
            <div>
              <span className='text-sm font-medium leading-normal'>{nombre} {apellido}</span>
              <div className='text-gray-600 text-sm'>
                {notificacion.servicioTitulo} - ${notificacion.precio ? parseFloat(notificacion.precio).toFixed(2) : '0.00'}
              </div>
            </div>
          </div>
          <div className='text-sm text-gray-500'>
            {notificacion.fechaPago ? new Date(notificacion.fechaPago).toLocaleDateString() : ''}
          </div>
        </div>
      </div>
    </div>
  )
}
