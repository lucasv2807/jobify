import { Link } from 'react-router-dom'
import { useState, useRef, useEffect, useContext } from 'react'
import { Edit, LogOut } from 'lucide-react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { toast } from 'react-toastify'

export function Card ({ Imagen, Titulo, Precio, id, Calificacion, NombreUsuario, CantCalificaiones, userId, showMenu }) {
  const { user: myUser } = useContext(UsuarioContext)

  // Solo mostrar menú si showMenu es true Y el usuario es el dueño
  const shouldShowMenu = showMenu && userId === myUser.id

  return (
    <div className='relative my-2 h-full'>
      {shouldShowMenu && <MenuDesplegable id={id} userId={userId} />}
      <Link to={`/Buy/${id}`} className='flex flex-col h-full max-w-sm min-w-4xs rounded-lg overflow-hidden bg-white border border-gray-50 shadow-2xl'>
        <img className='w-full bg-center bg-no-repeat aspect-video bg-cover object-cover' alt='Plumber fixing a sink' src={Imagen} />
        <div className='px-4 py-3 flex flex-col gap-1 '>
          <p className='text-jobify-blue-gray text-base font-medium leading-normal line-clamp-2'>{Titulo}</p>
          <p className='text-jobify-text-secondary text-sm font-normal leading-normal'>{NombreUsuario}</p>
          <div className='flex items-center gap-1 text-jobify-text-secondary text-sm'>
            <span className='material-symbols-outlined text-jobify-accent-green text-base'>⭐</span> {Calificacion === null ? 'Sin calificación' : Calificacion} {CantCalificaiones === 0 ? '' : `(${CantCalificaiones})`}
          </div>
          <p className='text-jobify-blue-gray text-sm font-medium leading-normal mt-auto'>${Precio}</p>
        </div>
      </Link>
    </div>
  )
}

function MenuDesplegable ({ id, userId }) {
  const [open, setOpen] = useState(false)
  const menuRef = useRef(null)
  const { url } = useContext(UsuarioContext)
  useEffect(() => {
    function handleClickOutside (e) {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])
  const handleSubmit = async () => {
    try {
    // Lógica para eliminar el servicio
      const data = new FormData()
      // Forzar string para evitar enviar objetos por error
      data.append('idServicio', id != null ? String(id) : '')
      data.append('idProveedor', userId != null ? String(userId) : '')

      const response = await fetch(`${url}/servicios/borrar`, {
        method: 'POST',
        body: data
      })

      const json = await response.json()

      if (response.ok) {
      // Éxito - puedes mostrar un mensaje de confirmación
        toast.success(json.message || 'Servicio eliminado exitosamente')
        // Opción 1: recargar la página
        window.location.reload()

        // Opción 2 (mejor UX): redirigir a otra página
        // window.location.href = '/mis-servicios';

      // Opción 3 (mejor UX): actualizar solo la lista sin recargar
      // onServiceDeleted(id); // callback que actualiza el estado local
      } else {
      // Error del servidor (400, 500, etc.)
        const errorMessage = json.error || json.message || 'Error al eliminar el servicio'
        toast.error(errorMessage)
        console.error('Error del servidor:', json)
      }
    } catch (error) {
    // Error de red o parsing JSON
      console.error('Error en la petición:', error)
      toast.error('Error de conexión. Por favor, intenta de nuevo.')
    }
  }
  return (
    <div className='relative' ref={menuRef}>
      <button
        onClick={() => setOpen(!open)}
        className='absolute right-2 top-2 rounded-full overflow-hidden h-10 w-10 border-2 border-gray-200 hover:scale-105 transition-transform material-icons bg-white'
      >
        menu
      </button>
      {open && (
        <div
          className='absolute right-0 mt-13 w-48 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden z-50 animate-fade-slide'
        >
          <ul className='text-sm text-gray-700'>
            <li>

              <Link to={`/UpdateServicio/${id}`}>
                <button className='flex items-center w-full px-4 py-2 hover:bg-gray-100'>
                  <Edit className='w-4 h-4 mr-2' /> editar servicio
                </button>
              </Link>

            </li>
            <li>
              <button onClick={handleSubmit} className='flex items-center w-full px-4 py-2 hover:bg-gray-100 text-red-500'>
                <LogOut className='w-4 h-4 mr-2' /> eliminar servicio
              </button>
            </li>
          </ul>
        </div>
      )}
    </div>
  )
}
