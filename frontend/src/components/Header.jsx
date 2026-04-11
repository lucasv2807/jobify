import { Link } from 'react-router-dom'
import { SearchInput } from './SearchInput.jsx'
import { useContext } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { NotificacionesContext } from '../context/Notificaciones.jsx'
import { MenuDesplegable } from './MenuDesplegable.jsx'
export function Header () {
  return (
    <header className='transparent backdrop-blur-sm sticky top-0 z-50 shadow-sm'>
      <nav className='container mx-auto sm:px-6 lg:px-3'>
        <div className='flex items-center justify-between h-20'>
          <div className='flex justify-start h-full w-1/5 sm:w-1/3 p-1'>
            <Link to='/' className='h-full p-1'>
              <img src='/Jobify.webp' alt='Logo Proyecto' className='h-full aspect-square rounded-full' />
            </Link>
          </div>
          <SearchInput />
          <Buttons />
        </div>
      </nav>
    </header>

  )
}
function Buttons () {
  const { user } = useContext(UsuarioContext)
  const { notificacionesNuevas } = useContext(NotificacionesContext)
  
  return (
    <>
      <div className='flex items-center justify-end w-1/2 sm:w-1/3 space-x-4 mr-4'>
        {user && user.esProveedor === true && (
          <Link
            to='/crear'
            aria-label={user.id ? 'Ir a mi crear servicio' : 'Iniciar sesión'}
            title={user.id ? 'Ir a crear servicio' : 'Iniciar sesión'}
            className='bg-claro text-primary outline outline-primary hover:bg-primary hover:text-claro p-1 rounded-full transition ease-in-out duration-150 hover:outline-0 hover:scale-105 flex items-center justify-center'
          >
            <UploadIcon />
          </Link>
        )}
        {user && user.foto_perfil
          ? (
            <>
              <Link
                to={user.id ? '/notificaciones/' : '/login'}
                aria-label={user.id ? 'Ir a mis notificaciones' : 'Iniciar sesión'}
                title={user.id ? 'Ir a mis notificaciones' : 'Iniciar sesión'}
                className='bg-claro text-primary outline outline-primary hover:bg-primary hover:text-claro p-1 rounded-full transition ease-in-out duration-150 hover:outline-0 hover:scale-105 flex items-center justify-center relative'
              >
                <BellIcon />
                {notificacionesNuevas > 0 && (
                  <span className='absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse'>
                    {notificacionesNuevas > 9 ? '9+' : notificacionesNuevas}
                  </span>
                )}
              </Link>
              <MenuDesplegable />
            </>
            )
          : (
            <>
              <Link
                to='/login'
                aria-label='Iniciar sesión'
                title='Iniciar sesión'
                className='px-6 py-2 bg-primary text-white rounded-full hover:bg-green-600 transition-colors text-sm font-semibold'
              >Iniciar sesión
              </Link>
              <Link
                to='/register'
                aria-label='Registrarse'
                title='Registrarse'
                className='px-6 py-2 bg-transparent border border-primary text-primary rounded-full hover:bg-primary/10 transition-colors text-sm font-semibold hidden sm:block'
              >Registrarse
              </Link>
            </>
            )}
      </div>
    </>
  )
}
const BellIcon = () => {
  return (
    <svg
      xmlns='http://www.w3.org/2000/svg'
      fill='none'
      viewBox='0 0 24 24'
      strokeWidth={1.5}
      stroke='currentColor'
      className='size-7 sm:size-9'
      aria-hidden='true'
      focusable='false'
    >
      <path strokeLinecap='round' strokeLinejoin='round' d='M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0' />
    </svg>
  )
}
const UploadIcon = () => {
  return (
    <svg
      xmlns='http://www.w3.org/2000/svg'
      fill='none'
      viewBox='0 0 24 24'
      strokeWidth={1.5}
      stroke='currentColor'
      className='size-7 sm:size-9'
    >
      <path strokeLinecap='round' strokeLinejoin='round' d='M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5' />
    </svg>
  )
}
