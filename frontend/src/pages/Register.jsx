import { useState, useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import { useRegister } from '../hooks/useRegister'
import { ToastContainer } from 'react-toastify'

export function Register () {
  const [esProveedor, setEsProveedor] = useState(false)
  const { nombre, apellido, email, password, confirmPassword, fechaNacimiento, telefono, nombreEmpresa, experienciaLaboral, descripcion, titulos, habilidades, changeNombre, changeApellido, changeEmail, changePassword, changeConfirmPassword, changeFechaNacimiento, changetelefono, changeFotoPerfil, handleSubmit, changeNombreEmpresa, changeExperienciaLaboral, changeDescripcion, agregarTitulo, eliminarTitulo, agregarHabilidad, eliminarHabilidad } = useRegister(esProveedor)
  const [phoneError, setPhoneError] = useState('')
  const [fotoPreview, setFotoPreview] = useState(null)
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const fileInputRef = useRef(null)
  const phoneRegex = /^(?:\+598 ?9\d{7}|9\d{7})$/

  const validatePhone = (value) => phoneRegex.test((value || '').trim())

  // Validaciones de contraseña
  const passwordValidations = {
    hasUpperCase: /[A-Z]/.test(password),
    hasLowerCase: /[a-z]/.test(password),
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password),
    hasNumber: /\d/.test(password),
    hasMinLength: password.length >= 8
  }

  const handleFotoChange = (e) => {
    const file = e.target.files[0]
    if (file) {
      changeFotoPerfil(e)
      const previewUrl = URL.createObjectURL(file)
      setFotoPreview(previewUrl)
    }
  }

  const handleRemoveFoto = () => {
    setFotoPreview(null)
    changeFotoPerfil({ target: { files: [null] } })
    // Reset file input
    if (fileInputRef.current) fileInputRef.current.value = ''
  }

  // Limpiar el URL del preview al desmontar
  useEffect(() => {
    return () => {
      if (fotoPreview) {
        URL.revokeObjectURL(fotoPreview)
      }
    }
  }, [fotoPreview])

  const onFormSubmit = (e) => {
    e.preventDefault()
    if (!validatePhone(telefono)) {
      setPhoneError('Número inválido. Usa 91123123 o +598 91123123')
      return
    }
    setPhoneError('')
    // forward to the hook's submit handler
    handleSubmit(e)
  }
  return (
    <>
      <main className='bg-claro'>
        <section className='min-h-screen flex items-center justify-center bg-claro py-12 px-4 sm:px-6 lg:px-8'>
          <div className='max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl'>
            <div>
              <h2 className='mt-6 text-center text-3xl font-extrabold text-primary'>
                Crea tu cuenta
              </h2>
              <p className='mt-2 text-center text-sm text-gray-600'>
                ¿Ya tienes una cuenta?
                <Link to='/login'>
                  <a className='font-medium text-primary hover:text-green-500'>
                    Inicia sesión aquí
                  </a>
                </Link>
              </p>
            </div>
            <div className='mt-6'>
              <form action='#' onSubmit={onFormSubmit} className='mt-8 space-y-6' method='POST'>
                <input name='remember' type='hidden' value='true' />
                <div className='rounded-md -space-y-px'>
                  <div className='grid grid-cols-2 gap-4 mb-4'>
                    <div>
                      <input value={nombre} onChange={changeNombre} className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm' id='first-name' name='first-name' placeholder='Nombre' required='' type='text' />
                    </div>
                    <div>
                      <input value={apellido} onChange={changeApellido} className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm' id='last-name' name='last-name' placeholder='Apellido' required='' type='text' />
                    </div>
                  </div>
                  <div className='mb-4'>
                    <input value={email} onChange={changeEmail} autocomplete='email' className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm' id='email-address' name='email' placeholder='Correo electrónico' required='' type='email' />
                  </div>
                  <div className='mb-4'>
                    <input
                      value={telefono}
                      onChange={changetelefono}
                      onBlur={() => {
                        if (!validatePhone(telefono)) setPhoneError('Número inválido. Usa 91123123 o +598 91123123')
                        else setPhoneError('')
                      }}
                      aria-invalid={phoneError ? 'true' : 'false'}
                      aria-describedby={phoneError ? 'phone-error' : undefined}
                      autoComplete='tel'
                      className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
                      id='phone-number'
                      name='phone'
                      placeholder='Teléfono'
                      required
                      type='tel'
                    />
                    <p className='mt-2 text-sm text-gray-500'>Formato: 91123123 o +598 91123123</p>
                    {phoneError && (
                      <p id='phone-error' className='mt-2 text-sm text-red-600'>
                        {phoneError}
                      </p>
                    )}
                  </div>
                  <div className='mb-4'>
                    <div className='relative'>
                      <input value={password} onChange={changePassword} autocomplete='current-password' className='appearance-none rounded-md relative block w-full px-3 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm' id='password' name='password' placeholder='Contraseña' required='' type={showPassword ? 'text' : 'password'} />
                      <button
                        type='button'
                        onClick={() => setShowPassword(!showPassword)}
                        className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
                      >
                        <span className='material-icons text-xl'>{showPassword ? 'visibility_off' : 'visibility'}</span>
                      </button>
                    </div>
                    {password && (
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
                  <div className='mb-4'>
                    <div className='relative'>
                      <input value={confirmPassword} onChange={changeConfirmPassword} autocomplete='current-password' className='appearance-none rounded-md relative block w-full px-3 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm' id='confirm-password' name='confirm-password' placeholder='Confirmar contraseña' required='' type={showConfirmPassword ? 'text' : 'password'} />
                      <button
                        type='button'
                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                        className='absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none'
                      >
                        <span className='material-icons text-xl'>{showConfirmPassword ? 'visibility_off' : 'visibility'}</span>
                      </button>
                    </div>
                    {confirmPassword && (
                      <div className='mt-2'>
                        <span className={`text-xs px-2 py-1 rounded-full flex items-center gap-1 w-fit ${password === confirmPassword ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                          <span className='material-icons' style={{ fontSize: '14px' }}>{password === confirmPassword ? 'check' : 'close'}</span>
                          {password === confirmPassword ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden'}
                        </span>
                      </div>
                    )}
                  </div>
                  <div className='mb-4'>
                    <label className='text-2xl font-bold text-primary cursor-pointer' htmlFor='fechaNacimiento'>
                      Fecha de nacimiento
                    </label>
                    <div className='relative mt-4'>
                      <input
                        value={fechaNacimiento}
                        onChange={changeFechaNacimiento}
                        className='appearance-none rounded-md block w-full px-4 py-3 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
                        id='fechaNacimiento'
                        name='fechaNacimiento'
                        required
                        type='date'
                      />
                    </div>
                  </div>
                  <label className='text-2xl font-bold text-primary cursor-pointer' htmlFor='img'>
                    Foto de perfil
                  </label>
                  <div className='mb-4 flex-col text-start'>
                    {!fotoPreview
                      ? (
                        <div className='mt-4'>
                          <input ref={fileInputRef} onChange={handleFotoChange} className='hidden' id='img' type='file' accept='image/*' />
                          <button type='button' onClick={() => fileInputRef.current && fileInputRef.current.click()} className='bg-primary hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg'>
                            Seleccionar foto
                          </button>
                        </div>
                        )
                      : (
                        <div className='mt-4 relative inline-block'>
                          <img src={fotoPreview} alt='Vista previa' className='w-32 h-32 object-cover rounded-full border border-primary' />
                          <button
                            type='button'
                            onClick={handleRemoveFoto}
                            className='absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold transition-colors shadow-lg'
                          >
                            ×
                          </button>
                        </div>
                        )}
                  </div>
                  {esProveedor ? <Proveedor nombreEmpresa={nombreEmpresa} experienciaLaboral={experienciaLaboral} descripcion={descripcion} titulos={titulos} habilidades={habilidades} changeNombreEmpresa={changeNombreEmpresa} changeExperienciaLaboral={changeExperienciaLaboral} changeDescripcion={changeDescripcion} agregarTitulo={agregarTitulo} eliminarTitulo={eliminarTitulo} agregarHabilidad={agregarHabilidad} eliminarHabilidad={eliminarHabilidad} /> : null}
                </div>
                <p className='text-sm font-medium text-gray-700 mb-3'>Quiero registrarme como:</p>
                <div className='grid grid-cols-2 gap-4'>
                  <label className='flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-primary has-[:checked]:bg-green-50' htmlFor='client'>
                    <input
                      checked={!esProveedor}
                      onChange={() => setEsProveedor(false)}
                      className='h-4 w-4 text-primary focus:ring-primary border-gray-300' id='client'
                      name='account-type'
                      type='radio'
                      value='client'
                    />
                    <span className='ml-3 font-medium text-gray-900'>Cliente</span>
                  </label>
                  <label className='flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-primary has-[:checked]:bg-green-50' htmlFor='provider'>
                    <input
                      checked={esProveedor}
                      onChange={() => setEsProveedor(true)}
                      className='h-4 w-4 text-primary focus:ring-primary border-gray-300'
                      id='provider'
                      name='account-type'
                      type='radio'
                      value='provider'
                    />
                    <span className='ml-3 font-medium text-gray-900'>Proveedor</span>
                  </label>
                </div>
                {/* <div className='flex items-center justify-between mt-6'>
                  <div className='flex items-center'>
                    <input className='h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded' id='terms-and-conditions' name='terms-and-conditions' type='checkbox' />
                    <label className='ml-2 block text-sm text-gray-900' for='terms-and-conditions'>
                      Acepto los <a className='font-medium text-primary hover:text-green-500' href='#'>Términos y Condiciones</a>
                    </label>
                  </div>
                </div> */}
                <div>
                  <button className='group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-full text-white bg-primary hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors' type='submit'>
                    Crear cuenta
                  </button>
                </div>
              </form>
            </div>
          </div>
        </section>
        <ToastContainer
          position='bottom-right'
        />
      </main>
    </>
  )
}
function Proveedor ({ nombreEmpresa, experienciaLaboral, descripcion, titulos, habilidades, changeNombreEmpresa, changeExperienciaLaboral, changeDescripcion, agregarTitulo, eliminarTitulo, agregarHabilidad, eliminarHabilidad }) {
  const [tituloInput, setTituloInput] = useState('')
  const [habilidadInput, setHabilidadInput] = useState('')

  const handleAgregarTitulo = () => {
    if (tituloInput.trim()) {
      agregarTitulo(tituloInput.trim())
      setTituloInput('')
    }
  }

  const handleAgregarHabilidad = () => {
    if (habilidadInput.trim()) {
      agregarHabilidad(habilidadInput.trim())
      setHabilidadInput('')
    }
  }

  return (
    <>
      <div className='mb-4'>
        <label className='sr-only' htmlFor='company-name'>Nombre de la empresa</label>
        <input
          className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
          id='company-name'
          type='text'
          value={nombreEmpresa}
          onChange={changeNombreEmpresa}
          placeholder='Nombre de la empresa'
        />
      </div>
      <div className='mb-4'>
        <label className='sr-only' htmlFor='work-experience'>Experiencia laboral</label>
        <input
          className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
          id='work-experience'
          type='text'
          value={experienciaLaboral}
          onChange={changeExperienciaLaboral}
          placeholder='Experiencia laboral'
        />
      </div>
      <div className='mb-4'>
        <label className='sr-only' htmlFor='description'>Descripción</label>
        <textarea
          className='appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm h-24 resize-none'
          id='description'
          value={descripcion}
          onChange={changeDescripcion}
          placeholder='Descripción'
        />
      </div>

      {/* Títulos con etiquetas */}
      <div className='mb-4'>
        <label className='text-base font-medium text-primary mb-2 block' htmlFor='titles'>Títulos</label>
        <div className='flex gap-2'>
          <input
            className='appearance-none rounded-md relative block flex-1 px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
            id='titles'
            type='text'
            value={tituloInput}
            onChange={(e) => setTituloInput(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault()
                handleAgregarTitulo()
              }
            }}
            placeholder='Agrega un título'
          />
          <button
            type='button'
            onClick={handleAgregarTitulo}
            className='bg-primary hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-md transition-colors'
          >
            Agregar
          </button>
        </div>
        {titulos.length > 0 && (
          <div className='mt-3 flex flex-wrap gap-2'>
            {titulos.map((titulo, index) => (
              <span key={index} className='px-3 py-1 border border-primary bg-white rounded-2xl flex items-center gap-2'>
                {titulo}
                <button type='button' onClick={() => eliminarTitulo(titulo)} className='text-primary font-bold hover:text-green-600'>
                  ×
                </button>
              </span>
            ))}
          </div>
        )}
      </div>

      {/* Habilidades con etiquetas */}
      <div className='mb-4'>
        <label className='text-base font-medium text-primary mb-2 block' htmlFor='skills'>Habilidades</label>
        <div className='flex gap-2'>
          <input
            className='appearance-none rounded-md relative block flex-1 px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm'
            id='skills'
            type='text'
            value={habilidadInput}
            onChange={(e) => setHabilidadInput(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault()
                handleAgregarHabilidad()
              }
            }}
            placeholder='Agrega una habilidad'
          />
          <button
            type='button'
            onClick={handleAgregarHabilidad}
            className='bg-primary hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-md transition-colors'
          >
            Agregar
          </button>
        </div>
        {habilidades.length > 0 && (
          <div className='mt-3 flex flex-wrap gap-2'>
            {habilidades.map((habilidad, index) => (
              <span key={index} className='px-3 py-1 border border-primary bg-white rounded-2xl flex items-center gap-2'>
                {habilidad}
                <button type='button' onClick={() => eliminarHabilidad(habilidad)} className='text-primary font-bold hover:text-green-600'>
                  ×
                </button>
              </span>
            ))}
          </div>
        )}
      </div>
    </>
  )
}
