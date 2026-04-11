import { useContext, useRef } from 'react'
import { FiltersContext } from '../context/Filters'

export function useFilters () {
  const { results, setFilteredResults } = useContext(FiltersContext)
  const filters = useRef({ price: null, category: [], location: [], days: [] })

  const applyFilters = () => {
    let filtered = results

    if (filters.current.price !== null && filters.current.price !== undefined) {
      filtered = filtered.filter(item => item.precio <= filters.current.price)
    }
    if (filters.current.category && filters.current.category.length > 0) {
      filtered = filtered.filter(item =>
        item.categorias.some(cat => filters.current.category.includes(cat))
      )
    }
    if (filters.current.location && filters.current.location.length > 0) {
      filtered = filtered.filter(item =>
        item.ubicaciones.some(loc => filters.current.location.includes(loc))
      )
    }
    if (filters.current.days && filters.current.days.length > 0) {
      filtered = filtered.filter(item =>
        item.diasDisponibles.some(day => filters.current.days.includes(day))
      )
    }
    setFilteredResults(filtered)
  }

  const FilterByPrice = (price) => {
    filters.current.price = price
    applyFilters()
  }

  const FilterByCategory = (category) => {
    filters.current.category = category || []
    applyFilters()
  }

  const FilterByLocation = (location) => {
    filters.current.location = location || []
    applyFilters()
  }

  const FilterByDays = (days) => {
    filters.current.days = days || []
    applyFilters()
  }

  return { FilterByPrice, FilterByCategory, FilterByLocation, FilterByDays }
}
