import { useState, useContext } from 'react'
import { useNavigate } from 'react-router-dom'
import { UsuarioContext } from '../context/Usuario'
import { toast } from 'react-toastify'

export function useRegister (esProveedor) {
  const { url } = useContext(UsuarioContext)
  const [nombre, setNombre] = useState('')
  const [apellido, setApellido] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [telefono, setTelefono] = useState('')
  const [fechaNacimiento, setFechaNacimiento] = useState('')
  const [fotoPerfil, setFotoPerfil] = useState(null)
  const [nombreEmpresa, setNombreEmpresa] = useState('')
  const [experienciaLaboral, setExperienciaLaboral] = useState('0')
  const [descripcion, setDescripcion] = useState('')
  const [titulos, setTitulos] = useState([])
  const [habilidades, setHabilidades] = useState([])
  const navigate = useNavigate()

  const changeNombre = e => setNombre(e.target.value)
  const changeApellido = e => setApellido(e.target.value)
  const changeEmail = e => setEmail(e.target.value)
  const changePassword = e => setPassword(e.target.value)
  const changeConfirmPassword = e => setConfirmPassword(e.target.value)
  const changetelefono = e => setTelefono(e.target.value)
  const changeFechaNacimiento = e => setFechaNacimiento(e.target.value)
  const changeFotoPerfil = e => setFotoPerfil(e.target.files[0])
  const changeNombreEmpresa = e => setNombreEmpresa(e.target.value)
  const changeExperienciaLaboral = e => setExperienciaLaboral(e.target.value)
  const changeDescripcion = e => setDescripcion(e.target.value)

  // Funciones para manejar titulos como array
  const agregarTitulo = (titulo) => {
    if (titulo && !titulos.includes(titulo)) {
      setTitulos((prev) => [...prev, titulo])
    }
  }
  const eliminarTitulo = (titulo) => {
    setTitulos(titulos.filter(t => t !== titulo))
  }

  // Funciones para manejar habilidades como array
  const agregarHabilidad = (habilidad) => {
    if (habilidad && !habilidades.includes(habilidad)) {
      setHabilidades((prev) => [...prev, habilidad])
    }
  }
  const eliminarHabilidad = (habilidad) => {
    setHabilidades(habilidades.filter(h => h !== habilidad))
  }

  const validarContrasena = (pass, confirmPassword) => {
    if (pass.length < 8) {
      toast.warning('La contraseña debe tener al menos 8 caracteres')
      return false
    }
    if (/\s/.test(pass)) {
      toast.warning('La contraseña no puede contener espacios')
      return false
    }
    if (!/[A-Z]/.test(pass)) {
      toast.warning('La contraseña debe tener al menos una letra mayúscula')
      return false
    }
    if (!/[a-z]/.test(pass)) {
      toast.warning('La contraseña debe tener al menos una letra minúscula')
      return false
    }
    if (!/[0-9]/.test(pass)) {
      toast.warning('La contraseña debe tener al menos un número')
      return false
    }
    if (!/[^A-Za-z0-9]/.test(pass)) {
      toast.warning('La contraseña debe tener al menos un carácter especial')
      return false
    }
    if (pass !== confirmPassword) {
      toast.warning('Las contraseñas no coinciden')
      return false
    }
    return true
  }

  // 🔧 Función reutilizable para manejar errores del servidor
  const obtenerErrorServidor = async (response) => {
    try {
      const raw = await response.text()
      return raw ? JSON.parse(raw) : { message: 'Error desconocido del servidor' }
    } catch {
      return { message: 'Error al procesar la respuesta del servidor' }
    }
  }

  // ================== PROVEEDOR ==================
  if (esProveedor) {
    const handleSubmit = async (e) => {
      e.preventDefault()
      toast.loading('Registrando usuario...', { toastId: 'register' })

      if (!validarContrasena(password, confirmPassword)) {
        toast.dismiss('register')
        return
      }

      const data = new FormData()
      data.append('nombre', nombre)
      data.append('apellido', apellido)
      data.append('email', email)
      data.append('password', password)
      data.append('telefono', telefono)
      data.append('fecha_nacimiento', fechaNacimiento)
      data.append('foto_perfil', fotoPerfil)
      data.append('nombre_empresa', nombreEmpresa)
      data.append('experiencia', experienciaLaboral)
      data.append('descripcion', descripcion)
      titulos.forEach(titulo => data.append('titulos[]', titulo))
      habilidades.forEach(habilidad => data.append('habilidades[]', habilidad))
      data.append('es_proveedor', true)

      try {
        const response = await fetch(`${url}/usuario/register`, {
          method: 'POST',
          body: data
        })

        if (response.ok) {
          toast.dismiss('register')
          toast.success('Usuario registrado con éxito')
          navigate('/login')
        } else {
          // Read raw response body for debugging (use clone so obtenerErrorServidor can still read it)
          try {
            const raw = await response.clone().text()
            console.error('Register (provider) failed:', response.status, raw)
          } catch (e) {
            console.error('Failed to read response clone text', e)
          }

          const errorData = await obtenerErrorServidor(response)
          toast.dismiss('register')
          toast.error(errorData.message || 'Error al registrar usuario')
        }
      } catch (err) {
        toast.dismiss('register')
        toast.error('Error de conexión con el servidor')
        console.error(err)
      }
    }

    return {
      nombre,
      apellido,
      email,
      password,
      confirmPassword,
      telefono,
      fechaNacimiento,
      nombreEmpresa,
      experienciaLaboral,
      descripcion,
      titulos,
      habilidades,
      changeNombre,
      changeApellido,
      changeEmail,
      changePassword,
      changeConfirmPassword,
      changetelefono,
      changeFechaNacimiento,
      changeFotoPerfil,
      changeNombreEmpresa,
      changeExperienciaLaboral,
      changeDescripcion,
      agregarTitulo,
      eliminarTitulo,
      agregarHabilidad,
      eliminarHabilidad,
      handleSubmit
    }
  }

  // ================== CLIENTE ==================
  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!validarContrasena(password, confirmPassword)) {
      return
    }

    toast.loading('Registrando usuario...', { toastId: 'register' })

    const data = new FormData()
    data.append('nombre', nombre)
    data.append('apellido', apellido)
    data.append('email', email)
    data.append('password', password)
    data.append('telefono', telefono)
    data.append('fecha_nacimiento', fechaNacimiento)
    data.append('es_proveedor', false)
    data.append('foto_perfil', fotoPerfil)

    try {
      const response = await fetch(`${url}/usuario/register`, {
        method: 'POST',
        body: data
      })

      if (response.ok) {
        toast.dismiss('register')
        toast.success('Usuario registrado con éxito')
        navigate('/login')
      } else {
        const errorData = await obtenerErrorServidor(response)
        toast.dismiss('register')
        toast.error(errorData.message || 'Error al registrar usuario')
      }
    } catch (err) {
      toast.dismiss('register')
      toast.error('Error de conexión con el servidor')
      console.error(err)
    }
  }

  return {
    nombre,
    apellido,
    email,
    password,
    confirmPassword,
    telefono,
    fechaNacimiento,
    changeNombre,
    changeApellido,
    changeEmail,
    changePassword,
    changeConfirmPassword,
    changetelefono,
    changeFechaNacimiento,
    changeFotoPerfil,
    handleSubmit
  }
}
