import { createContext, useState } from 'react'

const UsuarioContext = createContext()

function UsuarioProvider({ children }) {
  const localUser = window.localStorage.getItem('user') ?? '{}'
  const url = import.meta.env.VITE_API_URL
  const [user, setUser] = useState(JSON.parse(localUser))
  return (
    <UsuarioContext.Provider value={
      {
        user,
        setUser,
        url
      }
    }
    >
      {children}
    </UsuarioContext.Provider>
  )
}

export { UsuarioContext, UsuarioProvider }
