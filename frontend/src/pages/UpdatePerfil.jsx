import { useEffect, useState, useContext, useRef } from 'react'
import { Header } from '../components/Header'
import { useParams } from 'react-router'
import { UsuarioContext } from '../context/Usuario'
import { ToastContainer, toast } from 'react-toastify'
import { Footer } from '../components/Footer'

export function UpdatePerfil () {
  const { user, setUser, url } = useContext(UsuarioContext)
  const [userProfile, setUserProfile] = useState({})
  const [edad, setEdad] = useState(null)
  const { id } = useParams()
  const [loading, setLoading] = useState(false)
  const [fotoPerfil, setFotoPerfil] = useState(null)
  const [fotoPreview, setFotoPreview] = useState(null)
  const fotoInputRef = useRef(null)
  // Estados para cambio de contraseña
  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [confirmNewPassword, setConfirmNewPassword] = useState('')
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${url}/usuario/getUser?id=${id}`)
        if (!response.ok) {
          console.error('Error al cargar perfil')
          toast.error('No se pudo cargar el perfil. Por favor, intenta de nuevo.')
          return
        }

        const text = await response.text()
        if (!text) {
          console.error('Respuesta vacía del servidor')
          toast.error('No se pudo cargar el perfil. Por favor, intenta de nuevo.')
          return
        }

        try {
          const data = JSON.parse(text)
          setUserProfile(data.data)
        } catch (jsonError) {
          console.error('Error al parsear JSON:', text)
          toast.error('No se pudo cargar el perfil. Por favor, intenta de nuevo.')
        }
      } catch (error) {
        console.error('Error fetching user:', error)
        toast.error('No se pudo cargar el perfil. Por favor, intenta de nuevo.')
      }
    }

    const hoy = new Date()
    const nacimiento = new Date(userProfile.fecha_nacimiento)
    const mes = hoy.getMonth() - nacimiento.getMonth()
    let edadUsuario = hoy.getFullYear() - nacimiento.getFullYear()

    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
      edadUsuario--
    }
    if (!isNaN(edadUsuario)) {
      setEdad(edadUsuario)
    }
    fetchData()
  }, [id, url, userProfile.fecha_nacimiento])
  const handleChange = (field, value) => {
    setUserProfile(prev => ({
      ...prev,
      [field]: value
    }))
  }

  const handleFotoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
      setFotoPerfil(file)
      const previewUrl = URL.createObjectURL(file)
      setFotoPreview(previewUrl)
    }
  }

  const handleSubmit = async (e) => {
    if (e) e.preventDefault()
    setLoading(true)
    const loadingToast = toast.loading('Guardando cambios...')

    try {
      // Validar contraseñas si se intenta cambiar
      if (currentPassword || newPassword || confirmNewPassword) {
        if (!currentPassword || !newPassword || !confirmNewPassword) {
          toast.dismiss(loadingToast)
          toast.error('Para cambiar la contraseña, completa todos los campos de contraseña')
          setLoading(false)
          return
        }

        if (newPassword !== confirmNewPassword) {
          toast.dismiss(loadingToast)
          toast.error('Las contraseñas nuevas no coinciden')
          setLoading(false)
          return
        }

        if (newPassword.length < 8) {
          toast.dismiss(loadingToast)
          toast.error('La nueva contraseña debe tener al menos 8 caracteres')
          setLoading(false)
          return
        }

        // Primero cambiar la contraseña
        try {
          const passForm = new FormData()
          passForm.append('idUsuario', user.id)
          passForm.append('passwordActual', currentPassword)
          passForm.append('nuevoPassword', newPassword)

          const passRes = await fetch(`${url}/usuario/updatePassword`, {
            method: 'POST',
            body: passForm
          })

          if (!passRes.ok) {
            const errorData = await passRes.json().catch(() => ({}))
            throw new Error(errorData.message || 'Error al cambiar la contraseña')
          }

          toast.dismiss(loadingToast)
          toast.success('Contraseña cambiada correctamente')

          // Limpiar campos de contraseña
          setCurrentPassword('')
          setNewPassword('')
          setConfirmNewPassword('')
        } catch (passError) {
          toast.dismiss(loadingToast)
          console.error('Error al cambiar contraseña:', passError.message)
          toast.error('No se pudo cambiar la contraseña. Verifica que la contraseña actual sea correcta.')
          setLoading(false)
          return
        }
      }

      const dato = new FormData()
      dato.append('idUsuario', userProfile.id)
      dato.append('nombre', userProfile.nombre)
      dato.append('apellido', userProfile.apellido)
      dato.append('email', userProfile.email)
      dato.append('telefono', userProfile.telefono || '')

      // Si hay una nueva foto, la enviamos
      if (fotoPerfil) {
        dato.append('foto_perfil', fotoPerfil)
      }

      // Campos de proveedor
      if (userProfile.esProveedor) {
        dato.append('es_proveedor', 'true') // CAMPO REQUERIDO POR EL BACKEND
        dato.append('nombre_empresa', userProfile.nombre_empresa || '')
        dato.append('experiencia', userProfile.experiencia || '')
        dato.append('descripcion', userProfile.descripcion || '')

        // Enviar habilidades
        if (userProfile.habilidades && userProfile.habilidades.length > 0) {
          userProfile.habilidades.forEach(hab => {
            dato.append('habilidades[]', hab.habilidad)
          })
        }

        // Enviar títulos
        if (userProfile.titulos && userProfile.titulos.length > 0) {
          userProfile.titulos.forEach(tit => {
            dato.append('titulos[]', tit.titulo)
          })
        }
      }

      const response = await fetch(`${url}/usuario/updateProfile`, {
        method: 'POST',
        body: dato
      })

      if (!response.ok) {
        let errorMessage = 'Error al guardar cambios'
        try {
          const errorData = await response.json()
          console.error('Error del servidor:', errorData)
          errorMessage = errorData.message || errorData.error || errorMessage
        } catch (jsonError) {
          console.error('No se pudo parsear el error como JSON')
        }
        throw new Error(errorMessage)
      }

      // Intentar parsear la respuesta
      let data = null
      const responseText = await response.text()

      if (responseText) {
        try {
          data = JSON.parse(responseText)
        } catch (jsonError) {
          console.warn('La respuesta no es JSON válido:', responseText)
        }
      }

      toast.dismiss(loadingToast)
      toast.success('Cambios guardados con éxito')

      // Si el usuario que edita es el mismo que está logueado, actualizar el contexto
      if (user.id === userProfile.id) {
        // Obtener los datos actualizados del servidor
        try {
          const userResponse = await fetch(`${url}/usuario/getUser?id=${userProfile.id}`)
          if (userResponse.ok) {
            const userData = await userResponse.json()
            const usuarioActualizado = userData.data

            // Actualizar contexto y localStorage con los datos frescos del servidor
            setUser(usuarioActualizado)
            window.localStorage.setItem('user', JSON.stringify(usuarioActualizado))
          }
        } catch (fetchError) {
          console.error('Error al obtener usuario actualizado:', fetchError)
        }
      }

      // Limpiar preview de foto
      if (fotoPreview) {
        URL.revokeObjectURL(fotoPreview)
        setFotoPreview(null)
        setFotoPerfil(null)
      }

      // Recargar datos del perfil desde el servidor para obtener la foto actualizada
      // Si se cambió la foto, recargar inmediatamente para actualizar el header
      if (fotoPerfil) {
        setTimeout(() => {
          window.location.reload()
        }, 1000)
      } else {
        setTimeout(() => {
          window.location.reload()
        }, 1500)
      }
    } catch (error) {
      console.error('=== ERROR AL GUARDAR CAMBIOS ===')
      console.error('Error completo:', error)
      console.error('Mensaje:', error.message)
      console.error('Stack:', error.stack)
      toast.dismiss(loadingToast)

      toast.error('No se pudieron guardar los cambios. Por favor, intenta de nuevo.')
    } finally {
      setLoading(false)
    }
  }

  if (!userProfile || Object.keys(userProfile).length === 0) {
    return null
  }

  // Verificar si el usuario actual es el propietario del perfil
  const isOwner = user.id === parseInt(id)

  return (
    <>
      <Header />
      <main className='container min-h-screen bg-claro mx-auto px-4 sm:px-6 lg:px-8 py-12'>
        <div className='w-full mx-auto'>
          <div className='flex flex-col xl:flex-row gap-6 w-full mb-10'>
            {/* Columna 1: Información Personal (siempre visible) */}
            <div className={userProfile.esProveedor ? 'xl:w-1/3' : 'sm:w-1/2'}>
              <div className='bg-white p-6 rounded-lg shadow-md h-full'>
                <div className='text-center'>
                  <div className='relative w-32 h-32 mx-auto mb-10'>
                    <input
                      type='file'
                      ref={fotoInputRef}
                      onChange={handleFotoChange}
                      accept='image/*'
                      className='hidden'
                    />
                    <img
                      alt=''
                      className='w-full h-full rounded-full object-cover cursor-pointer border-2 border-gray-300 hover:border-blue-500'
                      src={fotoPreview || userProfile.foto_perfil}
                      onClick={() => fotoInputRef.current?.click()}
                    />
                    {fotoPreview && (
                      <button
                        onClick={() => {
                          setFotoPreview(null)
                          setFotoPerfil(null)
                          fotoInputRef.current.value = ''
                        }}
                        className='absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 text-lg font-bold'
                      >
                        ×
                      </button>
                    )}
                  </div>
                  <div className='space-y-2 '>
                    <div className='grid grid-cols-2 gap-2 justify-center mb-10'>
                      <p>Nombre:</p>
                      <p>Apellido:</p>
                      <input
                        type='text'
                        value={userProfile.nombre || ''}
                        onChange={(e) => handleChange('nombre', e.target.value)}
                        className='text-xl font-bold text-gray-900 text-center border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent px-2'
                        placeholder='Nombre'
                        disabled={!isOwner}
                      />
                      <input
                        type='text'
                        value={userProfile.apellido || ''}
                        onChange={(e) => handleChange('apellido', e.target.value)}
                        className='text-xl font-bold text-gray-900 text-center border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent px-2'
                        placeholder='Apellido'
                        disabled={!isOwner}
                      />
                    </div>
                    {userProfile.esProveedor && (
                      <div>
                        <label className='text-xs text-gray-500 '>Nombre de la empresa:</label>
                        <input
                          type='text'
                          value={userProfile.nombre_empresa || ''}
                          onChange={(e) => handleChange('nombre_empresa', e.target.value)}
                          className='text-lg text-gray-600 text-center border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent w-full px-2 '
                          placeholder='Nombre de la empresa'
                          disabled={!isOwner}
                        />
                      </div>
                    )}
                  </div>
                </div>
                <hr className='my-6 border-gray-200' />
                <div>
                  <h3 className='font-semibold text-gray-800 mb-3 text-lg'>Información de Contacto</h3>
                  <div className='space-y-3 text-gray-600'>
                    <div className='flex items-center gap-2'>
                      <span className='material-icons text-primary'>email</span>
                      <input
                        type='email'
                        value={userProfile.email || ''}
                        onChange={(e) => handleChange('email', e.target.value)}
                        className='flex-1 border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent px-2'
                        placeholder='Correo electrónico'
                        disabled={!isOwner}
                      />
                    </div>
                    {user.id == id && (
                      <div className='flex items-center gap-2'>
                        <span className='material-icons text-primary'>phone</span>
                        <input
                          type='text'
                          value={userProfile.telefono || ''}
                          onChange={(e) => handleChange('telefono', e.target.value)}
                          className='flex-1 border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent px-2'
                          placeholder='Teléfono'
                          disabled={!isOwner}
                        />
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
            {/* Columna 2: Información de Proveedor (solo si es proveedor) */}
            {userProfile.esProveedor && (
              <div className='xl:w-1/3'>
                <UpdateProveedor user={userProfile} edad={edad} handleChange={handleChange} isOwner={isOwner} />
              </div>
            )}
            {/* Columna 3 (o 2 si no es proveedor): Cambiar Contraseña (siempre visible) */}
            {isOwner && (
              <div className={userProfile.esProveedor ? 'xl:w-1/3' : 'sm:w-1/2'}>
                <div className='bg-white rounded-lg shadow-md p-6 h-full'>
                  <h3 className='text-xl font-bold text-gray-800 mb-6 flex items-center gap-2'>
                    <span className='material-icons text-primary'>lock</span>
                    Cambiar Contraseña
                  </h3>
                  <UpdatePassword
                    currentPassword={currentPassword}
                    setCurrentPassword={setCurrentPassword}
                    newPassword={newPassword}
                    setNewPassword={setNewPassword}
                    confirmNewPassword={confirmNewPassword}
                    setConfirmNewPassword={setConfirmNewPassword}
                  />
                </div>
              </div>
            )}
          </div>
          {/* Botón Guardar Cambios */}
          {isOwner && (
            <div className='flex justify-center mt-8'>
              <button
                onClick={handleSubmit}
                disabled={loading}
                className='bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors'
              >
                {loading ? 'Guardando...' : 'Guardar cambios'}
              </button>
            </div>
          )}
          <ToastContainer
            position='bottom-right'
          />
        </div>
      </main>
      <Footer />
    </>
  )
}

function UpdateProveedor ({ user, edad, handleChange, isOwner }) {
  const [nuevoTitulo, setNuevoTitulo] = useState('')
  const [nuevaHabilidad, setNuevaHabilidad] = useState('')

  const agregarTitulo = () => {
    if (nuevoTitulo.trim()) {
      const nuevosTitulos = [...(user.titulos || []), { titulo: nuevoTitulo.trim() }]
      handleChange('titulos', nuevosTitulos)
      setNuevoTitulo('')
    }
  }

  const eliminarTitulo = (index) => {
    const nuevosTitulos = user.titulos.filter((_, i) => i !== index)
    handleChange('titulos', nuevosTitulos)
  }

  const agregarHabilidad = () => {
    if (nuevaHabilidad.trim()) {
      const nuevasHabilidades = [...(user.habilidades || []), { habilidad: nuevaHabilidad.trim() }]
      handleChange('habilidades', nuevasHabilidades)
      setNuevaHabilidad('')
    }
  }

  const eliminarHabilidad = (index) => {
    const nuevasHabilidades = user.habilidades.filter((_, i) => i !== index)
    handleChange('habilidades', nuevasHabilidades)
  }

  return (
    <div className='bg-white p-6 rounded-lg shadow-md h-full'>
      <div>
        <h3 className='text-xl font-bold text-primary mb-4'>Información Proveedor</h3>
        <div className='grid grid-cols-1 gap-4'>
          <div className=''>
            <label className='text-primary' htmlFor='full-name'>Edad</label>
            <h2>{edad}</h2>
          </div>
          <div>
            <label className='text-primary' htmlFor='experiencia'>Experiencia (Años)</label>
            <input
              type='number'
              min='0'
              value={user.experiencia || ''}
              onChange={(e) => handleChange('experiencia', e.target.value)}
              className='mt-1 w-full border-b-2 border-gray-300 focus:border-blue-500 outline-none bg-transparent px-2'
              placeholder='Años de experiencia'
              disabled={!isOwner}
            />
          </div>
        </div>
        <div className='mt-4'>
          <label className='text-primary' htmlFor='descripcion'>Descripción</label>
          <textarea
            value={user.descripcion || ''}
            onChange={(e) => handleChange('descripcion', e.target.value)}
            className='mt-2 w-full text-gray-700 border-2 border-gray-300 rounded-lg p-2 focus:border-blue-500 outline-none '
            placeholder='Escribe una descripción sobre ti y tus servicios...'
            disabled={!isOwner}
          />
        </div>
        <div>
          <h3 className='text-lg font-bold text-primary mt-4 mb-3'>Habilidades</h3>
          {isOwner && (
            <div className='flex gap-2 mb-3'>
              <input
                type='text'
                value={nuevaHabilidad}
                onChange={(e) => setNuevaHabilidad(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), agregarHabilidad())}
                className='flex-1 border-2 border-gray-300 rounded-lg px-3 py-1 focus:border-blue-500 outline-none text-sm'
                placeholder='Agregar habilidad'
              />
              <button
                onClick={agregarHabilidad}
                className='bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 text-sm'
              >
                +
              </button>
            </div>
          )}
          <div className='flex flex-wrap gap-2'>
            {user.habilidades && user.habilidades.length > 0
              ? (
                  user.habilidades.map((habilidad, index) => (
                    <span key={index} className='bg-primary text-white px-2 py-1 rounded-full text-xs flex items-center gap-1'>
                      {habilidad.habilidad}
                      {isOwner && (
                        <button
                          onClick={() => eliminarHabilidad(index)}
                          className='hover:text-red-300 font-bold'
                        >
                          ×
                        </button>
                      )}
                    </span>
                  ))
                )
              : (
                <p className='text-gray-500 text-xs'>No hay habilidades agregadas</p>
                )}
          </div>
          <div>
            <h3 className='text-lg font-bold text-primary mt-4 mb-3'>Títulos</h3>
            {isOwner && (
              <div className='flex gap-2 mb-3'>
                <input
                  type='text'
                  value={nuevoTitulo}
                  onChange={(e) => setNuevoTitulo(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), agregarTitulo())}
                  className='flex-1 border-2 border-gray-300 rounded-lg px-3 py-1 focus:border-blue-500 outline-none text-sm'
                  placeholder='Agregar título'
                />
                <button
                  onClick={agregarTitulo}
                  className='bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 text-sm'
                >
                  +
                </button>
              </div>
            )}
            <div className='flex flex-wrap gap-2'>
              {user.titulos && user.titulos.length > 0
                ? (
                    user.titulos.map((titulo, index) => (
                      <span key={index} className='bg-primary text-white px-2 py-1 rounded-full text-xs flex items-center gap-1'>
                        {titulo.titulo}
                        {isOwner && (
                          <button
                            onClick={() => eliminarTitulo(index)}
                            className='hover:text-red-300 font-bold'
                          >
                            ×
                          </button>
                        )}
                      </span>
                    ))
                  )
                : (
                  <p className='text-gray-500 text-xs'>No hay títulos agregados</p>
                  )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

function UpdatePassword ({ currentPassword, setCurrentPassword, newPassword, setNewPassword, confirmNewPassword, setConfirmNewPassword }) {
  const [showCurrentPassword, setShowCurrentPassword] = useState(false)
  const [showNewPassword, setShowNewPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)

  // Validaciones de contraseña
  const passwordValidations = {
    hasUpperCase: /[A-Z]/.test(newPassword),
    hasLowerCase: /[a-z]/.test(newPassword),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>-_]/.test(newPassword),
    hasNumber: /\d/.test(newPassword),
    hasMinLength: newPassword.length >= 8
  }

  return (
    <div className='space-y-4'>
      <div>
        <label className='block text-sm font-medium text-gray-700 mb-2' htmlFor='currentPassword'>
          Contraseña Actual
        </label>
        <div className='relative'>
          <input
            id='currentPassword'
            type={showCurrentPassword ? 'text' : 'password'}
            value={currentPassword}
            onChange={(e) => setCurrentPassword(e.target.value)}
            className='w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none'
            placeholder='Ingresa tu contraseña actual'
          />
          <button
            type='button'
            onClick={() => setShowCurrentPassword(!showCurrentPassword)}
            className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
          >
            <span className='material-icons text-xl'>{showCurrentPassword ? 'visibility_off' : 'visibility'}</span>
          </button>
        </div>
      </div>

      <div>
        <label className='block text-sm font-medium text-gray-700 mb-2' htmlFor='newPassword'>
          Nueva Contraseña
        </label>
        <div className='relative'>
          <input
            id='newPassword'
            type={showNewPassword ? 'text' : 'password'}
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            className='w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none'
            placeholder='Ingresa tu nueva contraseña'
          />
          <button
            type='button'
            onClick={() => setShowNewPassword(!showNewPassword)}
            className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
          >
            <span className='material-icons text-xl'>{showNewPassword ? 'visibility_off' : 'visibility'}</span>
          </button>
        </div>
        {newPassword && (
          <div className='mt-2 flex flex-wrap gap-2'>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 ${passwordValidations.hasUpperCase ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{passwordValidations.hasUpperCase ? 'check' : 'close'}</span>
              Mayúscula
            </span>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 ${passwordValidations.hasLowerCase ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{passwordValidations.hasLowerCase ? 'check' : 'close'}</span>
              Minúscula
            </span>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 ${passwordValidations.hasSpecialChar ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{passwordValidations.hasSpecialChar ? 'check' : 'close'}</span>
              Especial
            </span>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 ${passwordValidations.hasNumber ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{passwordValidations.hasNumber ? 'check' : 'close'}</span>
              Número
            </span>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 ${passwordValidations.hasMinLength ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{passwordValidations.hasMinLength ? 'check' : 'close'}</span>
              8+ caracteres
            </span>
          </div>
        )}
      </div>

      <div>
        <label className='block text-sm font-medium text-gray-700 mb-2' htmlFor='confirmNewPassword'>
          Confirmar Nueva Contraseña
        </label>
        <div className='relative'>
          <input
            id='confirmNewPassword'
            type={showConfirmPassword ? 'text' : 'password'}
            value={confirmNewPassword}
            onChange={(e) => setConfirmNewPassword(e.target.value)}
            className='w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none'
            placeholder='Confirma tu nueva contraseña'
          />
          <button
            type='button'
            onClick={() => setShowConfirmPassword(!showConfirmPassword)}
            className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
          >
            <span className='material-icons text-xl'>{showConfirmPassword ? 'visibility_off' : 'visibility'}</span>
          </button>
        </div>
        {confirmNewPassword && (
          <div className='mt-2'>
            <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 w-fit ${newPassword === confirmNewPassword ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
              <span className='material-icons' style={{ fontSize: '14px' }}>{newPassword === confirmNewPassword ? 'check' : 'close'}</span>
              {newPassword === confirmNewPassword ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden'}
            </span>
          </div>
        )}
      </div>
    </div>
  )
}
