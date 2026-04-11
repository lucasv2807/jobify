import { createContext, useState } from 'react'

const FiltersContext = createContext()

function FiltersProvider ({ children }) {
  const [results, setResults] = useState([])
  const [filteredResults, setFilteredResults] = useState([])
  const [selectedCategories, setSelectedCategories] = useState([])
  const [showModal, setShowModal] = useState(false)

  const toggleCategory = (cat) => {
    setSelectedCategories((prev) => {
      const next = prev.includes(cat) ? prev.filter((c) => c !== cat) : [...prev, cat]
      // aplicar filtro sobre los resultados originales
      if (next.length === 0) {
        setFilteredResults(results)
      } else {
        setFilteredResults(results.filter((item) => (
          Array.isArray(item.categorias) && item.categorias.some((c) => next.includes(c))
        )))
      }
      return next
    })
  }

  const selectOnlyCategory = (cat) => {
    const next = [cat]
    setSelectedCategories(next)
    setFilteredResults(results.filter((item) => (
      Array.isArray(item.categorias) && item.categorias.includes(cat)
    )))
  }
  return (
    <FiltersContext.Provider value={
      {
        results,
        setResults,
        filteredResults,
        setFilteredResults,
        selectedCategories,
        toggleCategory,
        selectOnlyCategory,
        showModal,
        setShowModal
      }
    }
    >
      {children}
    </FiltersContext.Provider>
  )
}

export { FiltersContext, FiltersProvider }
