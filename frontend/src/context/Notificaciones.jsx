import { createContext, useState, useEffect, useContext } from 'react'
import { UsuarioContext } from './Usuario.jsx'

export const NotificacionesContext = createContext()

export function NotificacionesProvider ({ children }) {
  const [notificacionesNuevas, setNotificacionesNuevas] = useState(0)
  const [ultimasNotificaciones, setUltimasNotificaciones] = useState([])
  const { url, user } = useContext(UsuarioContext)

  useEffect(() => {
    if (!user || !user.id) {
      setNotificacionesNuevas(0)
      return
    }

    const fetchNotificaciones = async () => {
      try {
        const URL_MENSAJES = `${url}/chat/${user.esProveedor ? 'chatsProveedor' : 'chatsCliente'}?id=${user.id}`
        const URL_RESERVAS = `${url}/reserva/${user.esProveedor ? 'getReservaProveedor' : 'getReservaCliente'}?id=${user.id}`
        const URL_PAGOS = `${url}/pago/${user.esProveedor ? 'historialProveedor' : 'historialCliente'}?id=${user.id}`

        const [mensajesRes, reservasRes, pagosRes] = await Promise.all([
          fetch(URL_MENSAJES),
          fetch(URL_RESERVAS),
          fetch(URL_PAGOS)
        ])

        const mensajesData = await mensajesRes.json()
        const reservasData = await reservasRes.json()
        const pagosData = await pagosRes.json()

        const mensajes = Array.isArray(mensajesData.data) ? mensajesData.data : []
        const reservas = Array.isArray(reservasData.data) ? reservasData.data : []
        const pagos = Array.isArray(pagosData.data) ? pagosData.data.map(p => ({ ...p, tipo: 'paid' })) : []

        const todasNotificaciones = [...mensajes, ...reservas, ...pagos]
        setUltimasNotificaciones(todasNotificaciones)

        // Obtener notificaciones vistas del localStorage
        const vistasStr = localStorage.getItem(`notificaciones_vistas_${user.id}`)
        const vistas = vistasStr ? JSON.parse(vistasStr) : []

        // Generar IDs únicos para cada notificación
        const notificacionesConId = todasNotificaciones.map(n => {
          let id
          if (n.idReserva) {
            id = `reserva-${n.idReserva}`
          } else if (n.idCliente && n.idServicio && n.idProveedor) {
            id = `chat-${n.idCliente}-${n.idServicio}-${n.idProveedor}`
          } else if (n.fechaPago) {
            id = `pago-${n.idCliente}-${n.idServicio}-${n.fechaPago}`
          } else {
            id = `notif-${Math.random()}`
          }
          return { ...n, uniqueId: id }
        })

        // Contar notificaciones no vistas
        const nuevas = notificacionesConId.filter(n => !vistas.includes(n.uniqueId))
        setNotificacionesNuevas(nuevas.length)
      } catch (error) {
        console.error('Error al cargar notificaciones:', error)
      }
    }

    fetchNotificaciones()
    // Actualizar cada 30 segundos
    const interval = setInterval(fetchNotificaciones, 30000)

    return () => clearInterval(interval)
  }, [url, user])

  const marcarComoVistas = () => {
    if (!user || !user.id) return

    const notificacionesIds = ultimasNotificaciones.map(n => {
      if (n.idReserva) {
        return `reserva-${n.idReserva}`
      } else if (n.idCliente && n.idServicio && n.idProveedor) {
        return `chat-${n.idCliente}-${n.idServicio}-${n.idProveedor}`
      } else if (n.fechaPago) {
        return `pago-${n.idCliente}-${n.idServicio}-${n.fechaPago}`
      }
      return `notif-${Math.random()}`
    })

    localStorage.setItem(`notificaciones_vistas_${user.id}`, JSON.stringify(notificacionesIds))
    setNotificacionesNuevas(0)
  }

  return (
    <NotificacionesContext.Provider value={{ notificacionesNuevas, marcarComoVistas }}>
      {children}
    </NotificacionesContext.Provider>
  )
}
