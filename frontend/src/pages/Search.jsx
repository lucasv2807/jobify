import { useEffect, useContext, useState } from 'react'
import { useSearchParams } from 'react-router'
import { UsuarioContext } from '../context/Usuario'
import { Header } from '../components/Header'
import { Card } from '../components/Card'
import { ToastContainer } from 'react-toastify'
import { Filters } from '../components/Filters'
import { FiltersContext } from '../context/Filters'

export function Search () {
  const [searchParams] = useSearchParams()
  const search = searchParams.get('search')
  const { url } = useContext(UsuarioContext)
  const { setResults, filteredResults: results, setFilteredResults, showModal } = useContext(FiltersContext)
  const [sortBy, setSortBy] = useState('default')
  const [showSortMenu, setShowSortMenu] = useState(false)
  const [showMobileFilters, setShowMobileFilters] = useState(false)

  const sortResults = (results, sortType) => {
    if (!results || results.length === 0) return results

    const sorted = [...results]
    switch (sortType) {
      case 'price-asc':
        return sorted.sort((a, b) => a.precio - b.precio)
      case 'price-desc':
        return sorted.sort((a, b) => b.precio - a.precio)
      case 'name-asc':
        return sorted.sort((a, b) => a.nombre.localeCompare(b.nombre))
      case 'name-desc':
        return sorted.sort((a, b) => b.nombre.localeCompare(a.nombre))
      default:
        return sorted
    }
  }

  const handleSortChange = (sortType) => {
    setSortBy(sortType)
    setShowSortMenu(false)
    const sorted = sortResults(results, sortType)
    setFilteredResults(sorted)
  }

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${url}/servicios/search?q=${search}`)
        const data = await response.json()
        if (!response.ok) {
          console.error('Error al buscar servicios:', data.message)
        }
        setResults(data.data || [])
        setFilteredResults(data.data || [])
      } catch (error) {
        console.error('Error al buscar servicios:', error)
        setResults([])
        setFilteredResults([])
      }
    }
    fetchData()
  }, [url, search, setResults, setFilteredResults])
  return (
    <>
      <Header />
      <div className='flex flex-1'>
        {/* Sidebar Desktop - Solo visible en lg+ */}
        <aside className='w-64 p-6 hidden lg:flex flex-col justify-between bg-jobify-background border-r border-jobify-light-gray/50 fixed top-15 left-0 h-screen'>
          <div className='flex flex-col gap-6'>
            <div className='flex flex-col'>
              <h1 className='text-jobify text-2xl font-bold text-primary leading-normal'>Categorías</h1>
            </div>
            <Filters />
          </div>
        </aside>

        {/* Sidebar Mobile - Overlay deslizable */}
        {showMobileFilters && (
          <>
            {/* Backdrop oscuro */}
            <div
              className='fixed inset-0 bg-black/50 z-40 lg:hidden'
              onClick={() => setShowMobileFilters(false)}
            />
            {/* Panel lateral */}
            <aside className='fixed top-0 left-0 h-full w-80 bg-white z-50 p-6 overflow-y-auto lg:hidden transform transition-transform duration-300 ease-in-out shadow-2xl'>
              <div className='flex justify-between items-center mb-6'>
                <h1 className='text-2xl font-bold text-primary'>Categorías</h1>
                <button
                  onClick={() => setShowMobileFilters(false)}
                  className='size-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors'
                  aria-label='Cerrar filtros'
                >
                  <span className='material-icons'>close</span>
                </button>
              </div>
              <Filters />
            </aside>
          </>
        )}

        <main className='flex-1 p-6 bg-claro lg:ml-64 min-h-screen'>
          <div className='flex flex-col gap-6'>
            <div className='flex flex-wrap justify-between items-center gap-4'>
              <p className='text-jobify-blue-gray text-4xl font-black leading-tight tracking-[-0.033em] min-w-72'>Explorar Servicios</p>
              {/* Botón de filtros visible solo en móvil */}
              <button
                onClick={() => setShowMobileFilters(true)}
                className='lg:hidden flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors'
                aria-label='Mostrar filtros'
              >
                <span className='material-icons'>tune</span>
                <span className='font-medium'>Filtros</span>
              </button>
            </div>
            <div className='flex items-center gap-2 relative'>
              <div className='relative'>
                <button
                  onClick={() => setShowSortMenu(!showSortMenu)}
                  className='border rounded-xl p-2 size-10 transition-colors'
                  title='Ordenar resultados'
                >
                  <span className='material-icons text-2xl text-gray-700'>sort</span>
                </button>
                {showSortMenu && (
                  <div className='absolute top-full left-0 mt-1 w-56 bg-white border border-gray-300 rounded-lg shadow-lg z-10'>
                    <button
                      onClick={() => handleSortChange('price-asc')}
                      className='w-full text-left px-4 py-2 hover:bg-gray-100 text-sm'
                    >
                      Precio: Menor a Mayor
                    </button>
                    <button
                      onClick={() => handleSortChange('price-desc')}
                      className='w-full text-left px-4 py-2 hover:bg-gray-100 text-sm'
                    >
                      Precio: Mayor a Menor
                    </button>
                    <button
                      onClick={() => handleSortChange('name-asc')}
                      className='w-full text-left px-4 py-2 hover:bg-gray-100 text-sm'
                    >
                      Nombre: A-Z
                    </button>
                    <button
                      onClick={() => handleSortChange('name-desc')}
                      className='w-full text-left px-4 py-2 hover:bg-gray-100 text-sm last:rounded-b-lg'
                    >
                      Nombre: Z-A
                    </button>
                  </div>
                )}
              </div>
              <span className='text-sm font-medium text-gray-600'>
                {sortBy === 'price-asc' && 'Precio: Menor a Mayor'}
                {sortBy === 'price-desc' && 'Precio: Mayor a Menor'}
                {sortBy === 'name-asc' && 'Nombre: A-Z'}
                {sortBy === 'name-desc' && 'Nombre: Z-A'}
              </span>
            </div>
            <div className='flex flex-wrap gap-3 overflow-x-auto pb-2' />
            <div className='grid grid-cols-[repeat(auto-fill,minmax(220px,1fr))] gap-6'>
              {results && results.length > 0
                ? results.map(item => (
                  <Card key={item.id} Imagen={item.imagenes} Precio={item.precio} Titulo={item.nombre} id={item.id} />
                ))
                : <h1 className='text-4xl text-claro font-bold mt-10'>No se encontraron resultados</h1>}
            </div>
          </div>
        </main>
        <ToastContainer />
      </div>
    </>
  )
}
