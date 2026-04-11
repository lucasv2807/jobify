import { useState, useRef, useEffect, useContext } from 'react'
import { User, Edit, LogOut } from 'lucide-react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { Link } from 'react-router-dom'
export function MenuDesplegable () {
  const [open, setOpen] = useState(false)
  const menuRef = useRef(null)
  const { setUser } = useContext(UsuarioContext)
  useEffect(() => {
    function handleClickOutside (e) {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])
  const { user } = useContext(UsuarioContext)
  const handleClick = () => {
    window.localStorage.removeItem('user')
    setUser({})
  }
  return (
    <div className='relative' ref={menuRef}>
      <button
        onClick={() => setOpen(!open)}
        className='rounded-full overflow-hidden border-2 border-gray-200 hover:scale-105 transition-transform'
      >
        <img
          src={user.foto_perfil}
          alt='Foto de perfil de usuario'
          className='size-9 sm:w-11 sm:h-11 rounded-full'
        />
      </button>

      {open && (
        <div
          className='absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden z-50 animate-fade-slide'
        >
          <ul className='text-sm text-gray-700'>
            <Link to={`/Perfil/${user.id}`}>
              <li>
                <button className='flex items-center w-full px-4 py-2 hover:bg-gray-100'>
                  <User className='w-4 h-4 mr-2' /> Ver perfil
                </button>
              </li>
            </Link>
            <li>
              <Link to={`/Perfil/${user.id}/updatePerfil`}>
                <button className='flex items-center w-full px-4 py-2 hover:bg-gray-100'>
                  <Edit className='w-4 h-4 mr-2' /> Editar perfil
                </button>
              </Link>
            </li>
            <li>
              <button onClick={handleClick} className='flex items-center w-full px-4 py-2 hover:bg-gray-100 text-red-500'>
                <LogOut className='w-4 h-4 mr-2' /> Cerrar sesión
              </button>
            </li>
          </ul>
        </div>
      )}
    </div>
  )
}
