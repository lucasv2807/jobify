import { useContext } from 'react'
import { UsuarioContext } from '../context/Usuario'
import { useSearchParams, useNavigate } from 'react-router-dom'
export function SesionMp () {
  const { url, user, setUser } = useContext(UsuarioContext)
  const [searchParams] = useSearchParams()
  const requestId = searchParams.get('requestId')
  const requestType = searchParams.get('requestType')
  const publicKey = searchParams.get('publicKey')
  const navigate = useNavigate()
  const handleLoginMp = async () => {
    try {
      const formData = new FormData()
      formData.append('access_token', requestId)
      formData.append('refresh_token', requestType)
      formData.append('public_key', publicKey)
      formData.append('id_proveedor', user.id)

      const response = await fetch(`${url}/pago/saveTokens`, {
        method: 'POST',
        body: formData
      })
      if (!response.ok) {
        throw new Error('Error al guardar la sesión')
      }

      await response.json()
      // Actualizar el usuario con el estado de Mercado Pago conectado
      const updatedUser = {
        ...user,
        registroMp: true
      }
      // Actualizar tanto el localStorage como el contexto
      window.localStorage.setItem('user', JSON.stringify(updatedUser))
      setUser(updatedUser)
      navigate(`/Perfil/${user.id}`)
    } catch (error) {
      console.error('Error al guardar la sesión:', error)
    }
  }

  return (
    <div className='container mx-auto px-4 py-12'>
      <div className='max-w-md mx-auto bg-white rounded-lg shadow-md p-6'>
        <h1 className='text-2xl font-bold text-primary mb-4'>Guardar sesión de Mercado Pago</h1>
        <button
          onClick={handleLoginMp}
          className='w-full bg-primary hover:bg-primary/90 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300'
        >
          Guardar sesión
        </button>
      </div>
    </div>
  )
}
