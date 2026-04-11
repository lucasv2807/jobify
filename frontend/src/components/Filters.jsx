import { useEffect, useState, useContext } from 'react'
import { FiltersContext } from '../context/Filters'
import { useFilters } from '../hooks/useFilters'

export function Filters () {
  const [maxPrice, setMaxPrice] = useState(0)
  const [priceRange, setPriceRange] = useState(0)
  const [days, setDays] = useState([])
  const [locations, setLocations] = useState([])
  const [selectedDays, setSelectedDays] = useState([])
  const [selectedLocations, setSelectedLocations] = useState([])
  const [showPriceFilter, setShowPriceFilter] = useState(false)
  const [showDaysFilter, setShowDaysFilter] = useState(false)
  const [showLocationFilter, setShowLocationFilter] = useState(false)
  const { results } = useContext(FiltersContext)
  const { FilterByPrice, FilterByDays, FilterByLocation } = useFilters()
  const [categoryes, setCategoryes] = useState([])
  const [category, setCategory] = useState([])
  const [showCategoryFilter, setShowCategoryFilter] = useState(true)
  const { FilterByCategory } = useFilters()
  const handleChangeCategory = (e, category) => {
    // Solo permitir una categoría activa a la vez.
    let newCategories
    if (e.target.checked) {
      // seleccionar únicamente la categoría actual
      newCategories = [category]
      FilterByCategory(newCategories)
    } else {
      // desmarcar (ninguna seleccionada)
      newCategories = []
      FilterByCategory(newCategories)
    }
    setCategoryes(newCategories)
  }
  useEffect(() => {
    if (results.length > 0) {
      const categories = [...new Set(results.flatMap(item => item.categorias))]
      setCategory(categories)
    } else {
      setCategory([])
    }
  }, [results])
  useEffect(() => {
    const getMaxPrice = () => {
      if (results.length > 0) {
        const maxPrice = Math.max(...results.map(item => item.precio))
        setMaxPrice(maxPrice)
      }
    }
    const getDays = () => {
      if (results.length > 0) {
        const days = [...new Set(results.flatMap(item => item.diasDisponibles))]
        setDays(days)
      }
    }
    const getLocations = () => {
      if (results.length > 0) {
        const locations = [...new Set(results.flatMap(item => item.ubicaciones))]
        setLocations(locations)
      }
    }
    getDays()
    getMaxPrice()
    getLocations()
  }, [results])

  const handleChangePrice = (e) => {
    setPriceRange(e.target.value)
    FilterByPrice(e.target.value)
  }
  const handleChangeDays = (e, day) => {
    let newDays
    if (e.target.checked) {
      newDays = [...selectedDays, day]
    } else {
      newDays = selectedDays.filter(d => d !== day)
    }
    setSelectedDays(newDays)
    FilterByDays(newDays)
  }
  const handleChangeLocation = (e, location) => {
    let newSelectedLocations
    if (e.target.checked) {
      newSelectedLocations = [...selectedLocations, location]
    } else {
      newSelectedLocations = selectedLocations.filter(loc => loc !== location)
    }
    setSelectedLocations(newSelectedLocations)
    FilterByLocation(newSelectedLocations)
  }
  return (
    <>
      <div className='mb-4'>
        <button
          onClick={() => setShowCategoryFilter(!showCategoryFilter)}
          className='flex items-center justify-between w-full text-sm font-semibold text-oscuro mb-2'
        >
          <span>Filtrar por Categoría</span>
          <span className='material-icons text-lg'>
            {showCategoryFilter ? 'expand_less' : 'expand_more'}
          </span>
        </button>
        {showCategoryFilter && (
          category && category.length > 0
            ? (
                category.map((cat, index) => (
                  <label key={index} className={`flex items-start justify-start cursor-pointer gap-4 rounded-xl border border-gray-300 p-2 mb-1 hover:bg-gray-200 ${categoryes.includes(cat) ? 'bg-primary/80 hover:bg-primary/80' : ''}`}>
                    <input
                      type='checkbox'
                      id={`category-${index}`}
                      className='sr-only peer'
                      checked={categoryes.includes(cat)}
                      onChange={(e) => handleChangeCategory(e, cat)}
                    />
                    <span className={`text-sm font-bold ${categoryes.includes(cat) ? 'text-white' : 'text-primary'}`}>
                      {cat}
                    </span>
                  </label>
                ))
              )
            : <p className='text-sm text-gray-500'>No hay categorías</p>
        )}
      </div>
      <div className='mb-4'>
        <button
          onClick={() => setShowPriceFilter(!showPriceFilter)}
          className='flex items-center justify-between w-full text-sm font-semibold text-oscuro mb-2'
        >
          <span>Filtrar por Precio</span>
          <span className='material-icons text-lg'>
            {showPriceFilter ? 'expand_less' : 'expand_more'}
          </span>
        </button>
        {showPriceFilter && (
          <div className='p-4 bg-white rounded-xl border border-gray-300'>
            <div className='mb-4'>
              <p className='text-lg font-bold text-primary mb-1'>Precio máximo</p>
              <p className='text-2xl font-black text-primary'>${priceRange}</p>
            </div>
            <div className='space-y-2'>
              <input
                type='range'
                min='0'
                max={maxPrice}
                step='1'
                value={priceRange}
                onChange={handleChangePrice}
                className='w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary'
              />
              <div className='flex justify-between text-xs text-gray-600'>
                <span>$0</span>
                <span>${maxPrice}</span>
              </div>
            </div>
          </div>
        )}
      </div>
      <div className='mb-4'>
        <button
          onClick={() => setShowDaysFilter(!showDaysFilter)}
          className='flex items-center justify-between w-full text-sm font-semibold text-oscuro mb-2'
        >
          <span>Días Disponibles</span>
          <span className='material-icons text-lg'>
            {showDaysFilter ? 'expand_less' : 'expand_more'}
          </span>
        </button>
        {showDaysFilter && (
          days && days.length > 0
            ? (
                days.map((day, index) => (
                  <label key={index} className={`flex items-start justify-start cursor-pointer gap-4 rounded-xl border border-gray-300 p-2 mb-1 hover:bg-gray-200 ${selectedDays.includes(day) ? 'bg-primary/80 hover:bg-primary/80' : ''}`}>
                    <input
                      type='checkbox'
                      id={`day-${index}`}
                      className='sr-only peer'
                      checked={selectedDays.includes(day)}
                      onChange={(e) => handleChangeDays(e, day)}
                    />
                    <span className={`text-sm font-bold ${selectedDays.includes(day) ? 'text-white' : 'text-primary'}`}>
                      {day}
                    </span>
                  </label>
                ))
              )
            : (
              <p className='text-sm text-gray-500'>No hay días disponibles</p>
              )
        )}
      </div>
      <div className='mb-4'>
        <button
          onClick={() => setShowLocationFilter(!showLocationFilter)}
          className='flex items-center justify-between w-full text-sm font-semibold text-oscuro mb-2'
        >
          <span>Ubicación</span>
          <span className='material-icons text-lg'>
            {showLocationFilter ? 'expand_less' : 'expand_more'}
          </span>
        </button>
        {showLocationFilter && (
          locations && locations.length > 0
            ? (
                locations.map((loc, index) => (
                  <label key={index} className={`flex items-start justify-start cursor-pointer gap-4 rounded-xl border border-gray-300 p-2 mb-1 hover:bg-gray-200 ${selectedLocations.includes(loc) ? 'bg-primary/80 hover:bg-primary/80' : ''}`}>
                    <input
                      type='checkbox'
                      id={`location-${index}`}
                      className='sr-only peer'
                      checked={selectedLocations.includes(loc)}
                      onChange={(e) => handleChangeLocation(e, loc)}
                    />
                    <span className={`text-sm font-bold ${selectedLocations.includes(loc) ? 'text-white' : 'text-primary'}`}>
                      {loc}
                    </span>
                  </label>
                ))
              )
            : (
              <p className='text-sm text-gray-500'>No hay ubicaciones disponibles</p>
              )
        )}
      </div>
    </>
  )
}
