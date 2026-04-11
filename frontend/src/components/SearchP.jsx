import { useNavigate, useLocation, useSearchParams } from 'react-router-dom'
import { useState, useEffect, useContext } from 'react'
import { useDebounce } from '../hooks/useDebounce.js'
import Tooltip from '@mui/material/Tooltip'
import { FiltersContext } from '../context/Filters.jsx'
import { ModalIA } from './ModalIA.jsx'

export function SearchInput () {
  const location = useLocation()
  const isInSearch = location.pathname === '/buscar'
  const [mobileOpen, setMobileOpen] = useState(false)
  const [showButton, setShowButton] = useState(true)
  const [lastScrollY, setLastScrollY] = useState(0)

  // Detectar dirección del scroll para mostrar/ocultar botón
  useEffect(() => {
    const handleScroll = () => {
      const currentScrollY = window.scrollY

      // Si estamos cerca del top, siempre mostrar
      if (currentScrollY < 50) {
        setShowButton(true)
      } else {
        // Si hacemos scroll hacia abajo, ocultar; hacia arriba, mostrar
        if (currentScrollY > lastScrollY) {
          setShowButton(false)
        } else {
          setShowButton(true)
        }
      }

      setLastScrollY(currentScrollY)
    }

    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [lastScrollY])

  // When the mobile overlay is open, prevent body scroll and close on ESC
  useEffect(() => {
    if (!mobileOpen) return
    const previousOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    const onKey = (e) => {
      if (e.key === 'Escape') setMobileOpen(false)
    }
    document.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = previousOverflow
      document.removeEventListener('keydown', onKey)
    }
  }, [mobileOpen])

  return (
    <>
      {/* Icon visible only on mobile */}
      <button
        onClick={() => setMobileOpen(true)}
        className={`sm:hidden size-10 rounded-full bg-primary text-white items-center justify-center p-2 transition-all duration-300 ${
          showButton ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-20 pointer-events-none'
        }`}
        aria-label='Abrir búsqueda'
      >
        <span className='material-icons'>search</span>
      </button>

      {/* Desktop / tablet input */}
      {
        isInSearch
          ? <ActiveInput showDesktop onMobileClose={() => setMobileOpen(false)} />
          : <FormInput showDesktop onMobileClose={() => setMobileOpen(false)} />
      }

      {/* Mobile overlay input */}
      {mobileOpen && (
        <div
          className='fixed inset-0 z-50 bg-black/40 flex items-start justify-center p-4'
          // close when clicking on backdrop (not on inner content)
          onClick={(e) => { if (e.target === e.currentTarget) setMobileOpen(false) }}
          aria-hidden='false'
        >
          <div className='w-full max-w-xl' role='dialog' aria-modal='true' aria-label='Buscar servicios'>
            {
              isInSearch
                ? <ActiveInput mobile onClose={() => setMobileOpen(false)} />
                : <FormInput mobile onClose={() => setMobileOpen(false)} />
            }
          </div>
        </div>
      )}
    </>
  )
}

function ActiveInput ({ mobile, onClose }) {
  const [searchParams, setSearchParams] = useSearchParams()
  const initialSearch = searchParams.get('search') || ''
  const [searchTerm, setSearchTerm] = useState(initialSearch)
  const debouncedSearch = useDebounce(searchTerm, 300)
  const [hovered, setHovered] = useState(false)
  const { showModal, setShowModal } = useContext(FiltersContext)
  const changeShowModal = () => {
    setShowModal(prev => !prev)
  }

  useEffect(() => {
    setSearchParams({ search: debouncedSearch })
  }, [debouncedSearch, setSearchParams])

  // classNamees: hidden on mobile (sm:block) unless rendered in mobile overlay (mobile === true)
  const wrapperclassName = mobile ? 'relative w-full' : 'relative w-1/3 mx-auto lg:mx-0 hidden sm:block'

  return (
    <div className={wrapperclassName}>
      <input
        onChange={(e) => setSearchTerm(e.target.value)}
        className='w-full pl-5 pr-12 py-4 rounded-full bg-claro   border border-gray-300   focus:ring-2 focus:ring-primary focus:border-transparent transition'
        placeholder='¿Qué servicio necesitas? (ej: plomero)'
        type='text'
        value={searchTerm}
        autoFocus={!!mobile}
      />
      <div className='absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2'>
        <div className='relative'>
          <Tooltip title='Buscar con IA' arrow>
            <button
              className={`size-10 bg-primary text-white p-2 rounded-full hover:bg-green-600 transition ${!hovered ? 'IAHover' : ''}`}
              aria-label='Buscar con IA'
              onMouseEnter={() => setHovered(true)}
              onClick={changeShowModal}
            >
              <span className='material-icons'>auto_awesome</span>
            </button>
          </Tooltip>
          {showModal && <ModalIA />}
        </div>
        {mobile && (
          <button
            onClick={() => { if (onClose) onClose() }}
            className='bg-oscuro text-claro p-2 rounded-full ml-2'
            aria-label='Cerrar búsqueda'
          >
            <span className='material-icons'>close</span>
          </button>
        )}
      </div>
    </div>
  )
}

function FormInput ({ mobile, onClose }) {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')

  const handleSubmit = (e) => {
    e.preventDefault()
    navigate(`/buscar?search=${encodeURIComponent(search)}`)
    if (mobile && onClose) onClose()
  }

  // show desktop unless mobile overlay
  // use w-full on desktop so the input is visible; keep hidden on mobile with hidden sm:block
  const formclassName = mobile ? 'w-full' : 'relative w-1/3 mx-auto lg:mx-0 hidden sm:block'

  return (
    <form onSubmit={handleSubmit} className={formclassName}>
      <input
        onChange={(e) => setSearch(e.target.value)}
        className='w-full h-12 pl-5 pr-12 py-4 rounded-full bg-claro   border border-gray-300   focus:ring-2 focus:ring-primary focus:border-transparent transition'
        placeholder='¿Qué servicio necesitas? (ej: plomero)'
        type='search'
        value={search}
        autoFocus={!!mobile}
      />
      <div className='absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2'>
        <button className='hidden sm:block size-10 bg-primary text-white p-2 rounded-full hover:bg-green-600 transition' aria-label='Buscar'>
          <span className='material-icons'>search</span>
        </button>
        {mobile && (
          <div className='flex gap-2'>
            <button type='button' onClick={() => onClose && onClose()} className='mr-4'>
              <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' strokeWidth={1.5} stroke='currentColor' className='size-6'>
                <path strokeLinecap='round' strokeLinejoin='round' d='M6 18 18 6M6 6l12 12' />
              </svg>

            </button>
          </div>
        )}
      </div>
    </form>
  )
}
