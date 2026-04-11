import { Link, useNavigate } from 'react-router-dom'
import { useState, useContext } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { ToastContainer, toast } from 'react-toastify'

export function Login () {
  const sinImplementar = () => {
    toast.warning('Funcionalidad no implementada')
  }
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const navigate = useNavigate()
  const { setUser, url } = useContext(UsuarioContext)
  const changeEmail = (e) => {
    setEmail(e.target.value)
  }
  const changePassword = (e) => {
    setPassword(e.target.value)
  }
  const handleSubmit = async (e) => {
    e.preventDefault()
    toast.loading('Iniciando sesión...', { toastId: 'login' })
    try {
      const response = await fetch(`${url}/usuario/login?email=${email}&password=${password}`)
      if (response.ok) {
        const res = await response.json()
        const data = res.data
        toast.dismiss('login')
        if (data.id === undefined || data.nombre === null) {
          console.error('Error en login:', res.message)
          toast.error('Correo o contraseña incorrectos')
        } else {
          fetch(`${url}/usuario/getUser?id=${data.id}`)
            .then((response) => response.json())
            .then((data) => {
              window.localStorage.setItem('user', JSON.stringify(data.data))
              setUser(data.data)
            })
          navigate('/Perfil/' + data.id)
        }
      } else {
        toast.dismiss('login')
        const text = await response.json()
        console.error('Error en login:', text.message)
        toast.error('Correo o contraseña incorrectos')
      }
    } catch (error) {
      console.error('Error al iniciar sesión:', error)
      toast.error('No se pudo iniciar sesión. Por favor, intenta de nuevo.')
      toast.dismiss('login')
    }
  }
  return (
    <>
      <div className='flex h-screen'>
        <div className='hidden lg:flex lg:w-1/2 items-center justify-center p-12 bg-claro'>
          <div className='max-w-md w-1/2'>
            <img src='/Jobify.webp' alt='Logo' className='w-90 aspect-square' />
            <div className='text-center mt-8'>
              <h2 className='text-3xl font-bold text-primary  '>Conecta con los mejores profesionales</h2>
              <p className='mt-4 text-oscuro'>Encuentra servicios de calidad o publica tus habilidades para llegar a más clientes.</p>
            </div>
          </div>
        </div>
        <div className='w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 login-bg'>
          <div className='max-w-md w-full'>
            <div className='text-center mb-10'>
              <h1 className='text-3xl font-bold text-primary  mt-4'>Bienvenido de nuevo</h1>
              <p className='text-oscuro   mt-2'>Inicia sesión para continuar</p>
            </div>
            <form onSubmit={handleSubmit} action='#' className='space-y-6' method='POST'>
              <div>
                <label className='block text-sm font-medium text-gray-700  ' htmlFor='email'>Correo electrónico o Usuario</label>
                <div className='mt-1'>
                  <input
                    onChange={changeEmail}
                    value={email}
                    autoComplete='email'
                    className='w-full px-4 py-3 rounded-lg bg-gray-50   border border-gray-300   focus:ring-primary focus:border-primary transition' id='email' name='email' placeholder='tu@email.com' required='' type='email'
                  />
                </div>
              </div>
              <div>
                <div className='flex items-center justify-between'>
                  <label className='block text-sm font-medium text-gray-700  ' htmlFor='password'>Contraseña</label>
                  <button type='button' onClick={sinImplementar} className='text-sm text-primary hover:underline bg-transparent border-0 cursor-pointer'>
                    ¿Olvidaste tu contraseña?
                  </button>
                </div>
                <div className='mt-1 relative'>
                  <input
                    onChange={changePassword}
                    value={password}
                    autoComplete='current-password'
                    className='w-full px-4 py-3 pr-12 rounded-lg bg-gray-50   border border-gray-300   focus:ring-primary focus:border-primary transition'
                    id='password'
                    name='password'
                    placeholder='Ingresa tu contraseña'
                    required=''
                    type={showPassword ? 'text' : 'password'}
                  />
                  <button
                    type='button'
                    onClick={() => setShowPassword(!showPassword)}
                    className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
                  >
                    <span className='material-icons text-xl'>{showPassword ? 'visibility_off' : 'visibility'}</span>
                  </button>
                </div>
              </div>
              <div>
                <button className='w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-bold text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors' type='submit'>
                  Iniciar Sesión
                </button>
              </div>
            </form>
            <div className='mt-8 text-center'>
              <p className='text-sm text-gray-600 '>
                ¿No tienes una cuenta?{' '}
                <Link to='/register' className='font-medium text-primary hover:underline'>
                  Regístrate aquí
                </Link>
              </p>
            </div>
          </div>
        </div>
      </div>
      <ToastContainer
        position='bottom-right'
      />
    </>
  )
}
