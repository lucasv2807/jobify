import { Header } from '../components/Header'
import { useState, useContext, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { UsuarioContext } from '../context/Usuario'
import { ToastContainer, toast } from 'react-toastify'
import { Footer } from '../components/Footer'
export function Crear () {
  const { url, user } = useContext(UsuarioContext)
  const navigate = useNavigate()

  const [nombre, setNombre] = useState('')
  const [descripcion, setDescripcion] = useState('')
  const [categoria, setCategoria] = useState('')
  const [precio, setPrecio] = useState('')
  const [fotosConPreview, setFotosConPreview] = useState([]) // { file: File, preview: string, id: number }
  const [diasDisponibles, setDiasDisponibles] = useState([])
  const [departamento, setDepartamento] = useState('')
  const [loading, setLoading] = useState(false)

  const [categoriasElegidas, setCategoriasElegidas] = useState([])
  const [departamentosElegidos, setDepartamentosElegidos] = useState([])

  const DEPARTAMENTOS = ['Montevideo', 'Canelones', 'Maldonado', 'San José', 'Colonia', 'Lavalleja', 'Rocha', 'Tacuarembó', 'Paysandú', 'Salto', 'Rivera', 'Artigas', 'Cerro Largo', 'Durazno', 'Florida', 'Soriano', 'Treinta y Tres', 'Flores', 'Rio Negro']
  const DIAS_SEMANA = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
  // Lista de oficios única (deduplicada) solicitada por el usuario
  const JOBS = [
    'Electricista',
    'Plomero',
    'Albañil',
    'Dibujantes',
    'Carpintero',
    'Mecánico',
    'Cerrajero',
    'Vidriero',
    'Limpieza doméstica',
    'Jardinería y paisajismo',
    'Mantenimiento general',
    'Pintura interior/exterior',
    'Control de plagas',
    'Mudanzas y fletes',
    'Herrero / Soldador',
    'Calefacción y gasista',
    'Instalador de aire acondicionado',
    'Soporte informático / Técnico',
    'Fotógrafo',
    'Masajista',
    'Estética / Cosmética',
    'Peluquería a domicilio',
    'Paseador de perros'
  ]

  // estado para filtrar la lista de categorías en el select
  const [jobQuery, setJobQuery] = useState('')
  const filteredJobs = JOBS.filter((j) => j.toLowerCase().includes(jobQuery.toLowerCase()))
  const [showDropdown, setShowDropdown] = useState(false)
  const dropdownRef = useRef(null)
  // departamentos: buscador/autocomplete (similar al de categorías)
  const [deptQuery, setDeptQuery] = useState('')
  const filteredDeps = DEPARTAMENTOS.filter((d) => d.toLowerCase().includes(deptQuery.toLowerCase()))
  const [showDeptDropdown, setShowDeptDropdown] = useState(false)
  const deptDropdownRef = useRef(null)
  const fileInputRef = useRef(null)

  // cerrar dropdown al click fuera
  useEffect(() => {
    function handleClickOutside (e) {
      if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
        setShowDropdown(false)
      }
      if (deptDropdownRef.current && !deptDropdownRef.current.contains(e.target)) {
        setShowDeptDropdown(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const changeNombre = (e) => {
    setNombre(e.target.value)
  }
  const changeDescripcion = (e) => {
    setDescripcion(e.target.value)
  }
  // Agrega la categoría seleccionada o pasada como parámetro a la lista de categoríasElegidas
  const agregarCategoria = (job) => {
    const cat = job || categoria
    if (cat && !categoriasElegidas.includes(cat)) {
      setCategoriasElegidas((prev) => [...prev, cat])
    }
    // limpiar selección si la agregamos desde el autocomplete
    if (job) {
      setCategoria('')
      setJobQuery('')
      setShowDropdown(false)
    }
  }
  const eliminarCategoria = (cat) => {
    setCategoriasElegidas(categoriasElegidas.filter(c => c !== cat))
  }
  // Agrega departamento seleccionado o pasado como parámetro
  const agregarDepartamento = (dep) => {
    const d = dep || departamento
    if (d && !departamentosElegidos.includes(d)) {
      setDepartamentosElegidos((prev) => [...prev, d])
    }
    if (dep) {
      setDepartamento('')
      setDeptQuery('')
      setShowDeptDropdown(false)
    }
  }
  const eliminarDepartamento = (dep) => {
    setDepartamentosElegidos(departamentosElegidos.filter(d => d !== dep))
  }
  const changePrecio = (e) => {
    setPrecio(e.target.value)
  }
  const changeFotoServicio = (e) => {
    const files = Array.from(e.target.files || [])
    if (files.length === 0) return

    const nuevasFotos = files.map((file) => ({
      id: Date.now() + Math.random(), // ID único
      file,
      preview: URL.createObjectURL(file)
    }))

    setFotosConPreview((prev) => [...prev, ...nuevasFotos])
  }

  const eliminarFoto = (id) => {
    // Encontrar la foto a eliminar
    const fotoAEliminar = fotosConPreview.find(f => f.id === id)
    if (fotoAEliminar) {
      // Revocar el URL del objeto para liberar memoria
      URL.revokeObjectURL(fotoAEliminar.preview)
    }
    // Eliminar del array
    setFotosConPreview((prev) => prev.filter(f => f.id !== id))
  }

  const changeDiasDisponibles = (e) => {
    const { value, checked } = e.target
    if (checked) {
      setDiasDisponibles((prev) => [...prev, value])
    } else {
      setDiasDisponibles((prev) => prev.filter((dia) => dia !== value))
    }
  }

  useEffect(() => {
    return () => {
      // limpiar object URLs al desmontar
      fotosConPreview.forEach(foto => URL.revokeObjectURL(foto.preview))
    }
  }, [fotosConPreview])
  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    toast.loading('Creando servicio...', { toastId: 'create-service' })
    try {
      if (!nombre || !descripcion || (!categoriasElegidas.length && !categoria) || !precio) {
        toast.dismiss('create-service')
        toast.warning('Por favor completa todos los campos')
        setLoading(false)
        return
      }

      // Validar precio
      const precioNumerico = parseFloat(precio)
      if (isNaN(precioNumerico) || precioNumerico < 0) {
        toast.dismiss('create-service')
        toast.error('No se permite el precio negativo')
        setLoading(false)
        return
      }

      if (precioNumerico > 500000) {
        toast.dismiss('create-service')
        toast.error('Si desea publicar un servicio más caro de lo permitido contáctanos: clutchsoftware2025@gmail.com')
        setLoading(false)
        return
      }

      if (diasDisponibles.length === 0) {
        toast.dismiss('create-service')
        toast.warning('Selecciona al menos un día disponible')
        setLoading(false)
        return
      }
      if (fotosConPreview.length === 0) {
        toast.dismiss('create-service')
        toast.warning('Por favor agrega al menos una foto del servicio')
        setLoading(false)
        return
      }
      if (departamentosElegidos.length === 0) {
        toast.dismiss('create-service')
        toast.warning('Por favor agrega al menos un departamento')
        setLoading(false)
        return
      }
      if (categoriasElegidas.length === 0) {
        toast.dismiss('create-service')
        toast.warning('Por favor agrega al menos una categoría')
        setLoading(false)
        return
      }
      if (user.registroMp === false) {
        toast.dismiss('create-service')
        toast.warning('Debe registrar su cuenta de Mercado Pago antes de crear un servicio. Vaya a su perfil y haga clic en "Registrar cuenta de Mercado Pago".')
        setLoading(false)
        return
      }
      const data = new FormData()
      data.append('nombre', nombre)
      data.append('descripcion', descripcion)
      data.append('precio', precio)
      data.append('idProveedor', user.id)
      categoriasElegidas.forEach(cat => data.append('categoria[]', cat))
      departamentosElegidos.forEach(dep => data.append('ubicacion[]', dep))
      fotosConPreview.forEach((foto) => data.append('fotoServicio[]', foto.file))
      diasDisponibles.forEach((dia) => data.append('diasDisponibles[]', dia))

      const response = await fetch(`${url}/servicios/crear`, {
        method: 'POST',
        body: data
      })

      if (response.ok) {
        const json = await response.json()
        toast.dismiss('create-service')
        toast.success('Servicio creado exitosamente')
        navigate('/buy/' + json.data.id)
      } else {
        toast.dismiss('create-service')
        try {
          const errorData = await response.json()
          console.error('Error al crear servicio:', errorData.message || errorData.error)
          toast.error('No se pudo crear el servicio. Por favor, verifica los datos.')
        } catch (parseError) {
          console.error('Error parseando respuesta:', parseError)
          toast.error('No se pudo crear el servicio. Por favor, verifica los datos.')
        }
      }
    } catch (err) {
      toast.dismiss('create-service')
      console.error('Error en la solicitud:', err)
      toast.error('No se pudo crear el servicio. Por favor, intenta de nuevo.')
    } finally {
      setLoading(false)
    }
  }
  return (
    <>
      <Header />
      <div className='layout-container flex h-full grow flex-col'>
        <main className='flex flex-1 justify-center py-10 lg:py-16 px-4 sm:px-6 lg:px-8'>
          <div className='layout-content-container flex flex-col w-full max-w-4xl flex-1'>
            <div className='flex flex-wrap justify-between gap-4 items-start pb-8'>
              <div className='flex flex-col gap-2'>
                <h1 className='text-primary  text-4xl font-black leading-tight tracking-[-0.033em]'>Publicar un nuevo servicio</h1>
                <p className='text-[#648779] text-base font-normal leading-normal'>Completa los siguientes campos para añadir un nuevo servicio a tu perfil.</p>
              </div>
            </div>
            <div className='px-4 pb-4 flex flex-col gap-6 border rounded-lg border-[#dce5e1] pt-4'>
              <label className='flex flex-col w-full'>
                <p className='text-[#111715]  text-base font-medium leading-normal pb-2'>Nombre del servicio</p>
                <input onChange={changeNombre} value={nombre} className='form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111715]  focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dce5e1]  bg-white focus:border-primary  h-14 placeholder:text-[#648779] p-[15px] text-base font-normal leading-normal' placeholder='Ej: Plomero, fontanero, electricista' />
              </label>
              <label className='flex flex-col w-full'>
                <p className='text-[#111715]  text-base font-medium leading-normal pb-2'>Descripción detallada</p>
                <textarea onChange={changeDescripcion} value={descripcion} className='form-input no-resize flex w-full min-w-0 flex-1 resize-y overflow-hidden rounded-lg text-[#111715]  focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dce5e1]  bg-white focus:border-primary  min-h-36 placeholder:text-[#648779] p-[15px] text-base font-normal leading-normal' placeholder='Describe tu servicio con todo detalle, qué incluye, tu metodología de trabajo, etc.' />
              </label>
              <div className='relative flex flex-col sm:flex-row sm:items-start sm:space-x-4 gap-2'>
                <div className='w-full sm:w-1/3' ref={dropdownRef}>
                  <p className='text-[#111715] text-base font-medium leading-normal pb-2'>Categoría</p>
                  <input
                    type='text'
                    placeholder='Buscar categoría...'
                    value={jobQuery}
                    onChange={(e) => { setJobQuery(e.target.value); setShowDropdown(true) }}
                    onFocus={() => setShowDropdown(true)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault()
                        if (filteredJobs.length > 0) {
                          agregarCategoria(filteredJobs[0])
                        }
                      }
                      if (e.key === 'Escape') setShowDropdown(false)
                    }}
                    className='form-input mb-2 sm:mb-0 p-2 rounded-2xl h-10 w-full bg-white placeholder:text-[#648779] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dce5e1]'
                  />
                  {showDropdown && (
                    <ul className='absolute z-20 mt-2 max-h-48 w-full overflow-auto rounded-lg border bg-white shadow-lg p-1'>
                      {filteredJobs.length === 0 && (
                        <li className='px-3 py-2 text-sm text-gray-500'>Sin coincidencias</li>
                      )}
                      {filteredJobs.map((job) => (
                        <li key={job}>
                          <button
                            type='button'
                            onClick={() => { agregarCategoria(job) }}
                            className='w-full text-left px-3 py-2 hover:bg-gray-100 rounded'
                          >
                            {job}
                          </button>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </div>
              {categoriasElegidas.length > 0 && (
                <div className=' flex flex-wrap gap-2 mb-3'>
                  {categoriasElegidas.map((cat) => (
                    <span key={cat} className='px-3 py-1 border border-primary bg-white rounded-2xl flex items-center gap-2'>
                      {cat}
                      <button type='button' onClick={() => eliminarCategoria(cat)} className='text-primary font-bold'>
                        ×
                      </button>
                    </span>
                  ))}
                </div>
              )}
              <div className='relative flex flex-col sm:flex-row sm:items-start sm:space-x-4 gap-2'>
                <div className='w-full sm:w-1/3' ref={deptDropdownRef}>
                  <p className='text-[#111715] text-base font-medium leading-normal pb-2'>Departamento</p>
                  <input
                    type='text'
                    placeholder='Buscar departamento...'
                    value={deptQuery}
                    onChange={(e) => { setDeptQuery(e.target.value); setShowDeptDropdown(true) }}
                    onFocus={() => setShowDeptDropdown(true)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault()
                        if (filteredDeps.length > 0) {
                          agregarDepartamento(filteredDeps[0])
                        }
                      }
                      if (e.key === 'Escape') setShowDeptDropdown(false)
                    }}
                    className='form-input mb-2 sm:mb-0 p-2 rounded-2xl h-10 w-full bg-white placeholder:text-[#648779] focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#dce5e1]'
                  />
                  {showDeptDropdown && (
                    <ul className='absolute z-20 mt-2 max-h-48 w-full overflow-auto rounded-lg border bg-white shadow-lg p-1'>
                      {filteredDeps.length === 0 && (
                        <li className='px-3 py-2 text-sm text-gray-500'>Sin coincidencias</li>
                      )}
                      {filteredDeps.map((dep) => (
                        <li key={dep}>
                          <button
                            type='button'
                            onClick={() => { agregarDepartamento(dep) }}
                            className='w-full text-left px-3 py-2 hover:bg-gray-100 rounded'
                          >
                            {dep}
                          </button>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              </div>
              {departamentosElegidos.length > 0 && (
                <div className='mt-3 flex flex-wrap gap-2'>
                  {departamentosElegidos.map((dep) => (
                    <span key={dep} className='px-3 py-1 bg-white rounded-2xl flex items-center gap-2 border border-primary'>
                      {dep}
                      <button type='button' onClick={() => eliminarDepartamento(dep)} className='text-primary font-bold'>
                        ×
                      </button>
                    </span>
                  ))}
                </div>
              )}
              <div className='mt-4'>
                <p className='text-[#111715] text-base font-medium leading-normal mb-2'>Precio</p>
                <div className='relative inline-block w-full sm:w-1/3'>
                  <span className='absolute left-3 top-1/2 -translate-y-1/2 text-primary font-bold'>$</span>
                  <input
                    type='number'
                    min='0'
                    step='1'
                    placeholder='Precio del servicio'
                    onChange={changePrecio}
                    value={precio}
                    className='pl-9 p-2 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-primary/50 w-full'
                    required
                  />
                </div>
                <p className='text-sm text-gray-500'>Precio max: $500,000</p>
              </div>
              <div className='mt-4'>
                <p className='text-[#111715] text-base font-medium leading-normal mb-2'>Fotos del servicio</p>
                <div className='flex items-center gap-3'>
                  <input ref={fileInputRef} name='fotoServicio[]' type='file' onChange={changeFotoServicio} className='hidden' multiple accept='image/*' />
                  <button type='button' onClick={() => fileInputRef.current && fileInputRef.current.click()} className='bg-primary hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg'>
                    Seleccionar fotos
                  </button>
                  <span className='text-sm text-[#648779]'>
                    {fotosConPreview.length > 0 ? `${fotosConPreview.length} imagen(es) seleccionada(s)` : 'Haz click para seleccionar imágenes'}
                  </span>
                </div>
                {fotosConPreview.length > 0 && (
                  <div className='mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4'>
                    {fotosConPreview.map((foto) => (
                      <div key={foto.id} className='relative group'>
                        <img src={foto.preview} alt={foto.file.name} className='w-full h-32 object-cover rounded-lg border-2 border-primary' />
                        <button
                          type='button'
                          onClick={() => eliminarFoto(foto.id)}
                          className='absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold transition-colors shadow-lg'
                        >
                          ×
                        </button>
                        <p className='text-xs text-gray-600 mt-1 truncate'>{foto.file.name}</p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
              <label className='text-primary text-xl font-bold font-family-poppins'>Selecciona los dias de la semana que estas disponible para trabajar</label>
              <ul className='flex flex-wrap gap-4 text-primary justify-between'>
                {DIAS_SEMANA.map((dia) => (
                  <li key={dia} className='flex items-center gap-2'>
                    <input type='checkbox' id={dia} value={dia} onChange={changeDiasDisponibles} className='custom-checkbox' />
                    <label htmlFor={dia} className='checkbox-label select-none text-primary'>{dia}</label>
                  </li>
                ))}
              </ul>
              <div className='flex space-x-4 gap-2 items-center justify-center'>
                <button type='submit' disabled={loading} className='bg-primary hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed' onClick={handleSubmit}>
                  Publicar servicio
                </button>
              </div>
            </div>
          </div>
        </main>
        <ToastContainer position='top-right' autoClose={5000} hideProgressBar={false} newestOnTop={false} closeOnClick rtl={false} pauseOnFocusLoss draggable pauseOnHover theme='light' />
      </div>
      <Footer />
    </>
  )
}
