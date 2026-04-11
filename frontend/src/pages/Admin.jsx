import { useState, useEffect, useContext, useRef } from 'react'
import { Header } from '../components/Header'
import { Footer } from '../components/Footer'
import { UsuarioContext } from '../context/Usuario'
import { ToastContainer, toast } from 'react-toastify'
import { useNavigate } from 'react-router-dom'

export function Admin () {
  const { url } = useContext(UsuarioContext)
  
  // Estados para autenticación
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [password, setPassword] = useState('')
  const [loadingAuth, setLoadingAuth] = useState(false)

  const SECTIONS = [
    {
      name: 'Usuarios',
      icon: 'group',
      singular: 'Usuario',
      actions: [
        { name: 'Crear', icon: 'person_add', url: '/admin/userList' },
        { name: 'Modificar', icon: 'edit', url: '/admin/userModify' },
        { name: 'Eliminar', icon: 'person_remove', url: '/admin/deleteUser' }
      ],
      datos: ['ID', 'Imagen', 'Nombre', 'Correo', 'Fecha Nac.', 'Tipo'],
      url: '/userList'
    },
    {
      name: 'Servicios',
      icon: 'work',
      singular: 'Servicio',
      actions: [
        { name: 'Modificar', icon: 'edit', url: '/admin/serviceModify' },
        { name: 'Eliminar', icon: 'delete', url: '/admin/deleteService' }
      ],
      datos: ['ID', 'Título', 'Descripción', 'Categoría', 'Proveedor', 'Precio'],
      url: '/serviceList'
    },
    {
      name: 'Reseñas',
      icon: 'comment',
      singular: 'Reseña',
      actions: [
        { name: 'Modificar', icon: 'edit', url: '/admin/reviewModify' },
        { name: 'Eliminar', icon: 'comments_disabled', url: '/admin/deleteReview' }
      ],
      datos: ['ID', 'Usuario', 'Servicio', 'Calificación', 'Comentario'],
      url: '/reviewList'
    }
  ]

  const [selected, setSelected] = useState(SECTIONS[0].name)
  const [openMenus, setOpenMenus] = useState(
    SECTIONS.reduce((acc, section) => ({ ...acc, [section.name]: false }), {})
  )

  const handlePasswordSubmit = async (e) => {
    e.preventDefault()
    setLoadingAuth(true)

    try {
      const response = await fetch(`${url}/admin/verifyPassword`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ password })
      })

      const data = await response.json()

      if (response.ok && data.success) {
        toast.success('Acceso concedido')
        setIsAuthenticated(true)
        // Guardar en sessionStorage para mantener la sesión
        sessionStorage.setItem('adminAuth', 'true')
      } else {
        toast.error(data.message || 'Contraseña incorrecta')
        setPassword('')
      }
    } catch (error) {
      console.error('Error al verificar contraseña:', error)
      toast.error('Error al verificar la contraseña')
    } finally {
      setLoadingAuth(false)
    }
  }

  // Verificar si ya está autenticado al cargar la página
  useEffect(() => {
    const authStatus = sessionStorage.getItem('adminAuth')
    if (authStatus === 'true') {
      setIsAuthenticated(true)
    }
  }, [])

  // Si no está autenticado, mostrar modal de contraseña
  if (!isAuthenticated) {
    return (
      <>
        <Header />
        <div className='fixed inset-0 bg-black/50 flex items-center justify-center z-50'>
          <div className='bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4'>
            <div className='text-center mb-6'>
              <span className='material-icons text-primary text-6xl mb-4'>lock</span>
              <h2 className='text-2xl font-bold text-gray-900 mb-2'>Acceso Restringido</h2>
              <p className='text-gray-600'>Ingrese la contraseña de administrador para continuar</p>
            </div>
            
            <form onSubmit={handlePasswordSubmit} className='space-y-4'>
              <div>
                <label htmlFor='admin-password' className='block text-sm font-semibold text-gray-700 mb-2'>
                  Contraseña
                </label>
                <input
                  id='admin-password'
                  type='password'
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder='Ingrese la contraseña'
                  required
                  autoFocus
                  className='w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                  disabled={loadingAuth}
                />
              </div>

              <button
                type='submit'
                disabled={loadingAuth}
                className='w-full bg-primary text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center gap-2 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed'
              >
                <span className='material-icons'>{loadingAuth ? 'hourglass_empty' : 'login'}</span>
                {loadingAuth ? 'Verificando...' : 'Acceder'}
              </button>
            </form>
          </div>
        </div>
        <Footer />
        <ToastContainer position='bottom-right' autoClose={3000} />
      </>
    )
  }

  const toggleMenu = (section) => {
    setOpenMenus(prev => ({
      ...prev,
      [section]: !prev[section]
    }))
  }

  return (
    <>
      <Header />
      <div className='flex w-full min-h-screen bg-claro'>
        <nav className='bg-white shadow-lg min-w-64 min-h-screen'>
          <div className='p-6 border-b border-gray-200'>
            <h2 className='text-2xl font-bold text-primary flex items-center gap-2'>
              <span className='material-icons'>admin_panel_settings</span>
              Panel Admin
            </h2>
          </div>
          <ul className='py-4'>
            {SECTIONS.map((section) => (
              <li key={section.name}>
                <NavButton
                  section={section}
                  isActive={selected === section.name}
                  toggleMenu={toggleMenu}
                  isOpen={openMenus[section.name]}
                />
                {openMenus[section.name] && (
                  <ul className='bg-gray-50'>
                    {section.actions.map((action) => (
                      <SubMenuItem
                        key={action.name}
                        text={`${action.name} ${section.singular}`}
                        icon={action.icon}
                        onClick={() => setSelected(`${action.name} ${section.singular}`)}
                        isActive={selected === `${action.name} ${section.singular}`}
                      />
                    ))}
                  </ul>
                )}
              </li>
            ))}
          </ul>
        </nav>
        <main className='flex-1 p-8 overflow-x-hidden'>
          <div className='w-full mx-auto max-w-full'>
            {
              selected.split(' ')[0] === 'Crear'
                ? <Crear section={selected} key={selected} />
                : SECTIONS.map((section) => {
                  // Vista principal de la sección
                  if (selected === section.name) {
                    return (
                      <div key={section.name} className='bg-white rounded-lg shadow-md p-6'>
                        <div className='flex items-center gap-3 mb-6'>
                          <h2 className='text-2xl font-bold text-gray-900'>Panel de Administracion</h2>
                        </div>
                        <div className='text-gray-600'>
                          <p className='text-center py-12'>Selecciona una opción del menú...</p>
                        </div>
                      </div>
                    )
                  }
                  return section.actions.map((action) => {
                    const actionName = `${action.name} ${section.singular}`
                    if (selected === actionName) {
                      return (
                        <Table key={actionName} section={section} action={action} />
                      )
                    }
                    return null
                  })
                })
            }
          </div>
        </main>
      </div>
      <Footer />
      <ToastContainer
        position='bottom-right'
        autoClose={3000}
      />
    </>
  )
}

const NavButton = ({ section, isActive, toggleMenu, isOpen }) => {
  return (
    <button
      className={`w-full text-left px-6 py-3 flex items-center justify-between transition-colors ${
        isActive
          ? 'bg-primary text-white border-l-4 border-green-600'
          : 'text-gray-700 hover:bg-gray-100'
      }`}
      onClick={() => toggleMenu(section.name)}
    >
      <div className='flex items-center gap-3'>
        <span className='material-icons'>{section.icon}</span>
        <span className='font-medium'>{section.name}</span>
      </div>
      <span className={`material-icons transition-transform ${isOpen ? 'rotate-180' : ''}`}>
        expand_more
      </span>
    </button>
  )
}

const SubMenuItem = ({ text, icon, onClick, isActive }) => {
  return (
    <button
      className={`w-full text-left px-6 py-2 pl-12 flex items-center gap-3 transition-colors ${
        isActive
          ? 'bg-green-100 text-primary border-l-4 border-primary'
          : 'text-gray-600 hover:bg-gray-100'
      }`}
      onClick={onClick}
    >
      <span className='material-icons text-sm'>{icon}</span>
      <span className='text-sm font-medium'>{text}</span>
    </button>
  )
}

function Table ({ section, action }) {
  const [data, setData] = useState([])
  const { url } = useContext(UsuarioContext)
  const [refresh, setRefresh] = useState(false)
  const navigate = useNavigate()
  const [modalOpen, setModalOpen] = useState(false)
  const [selectedItem, setSelectedItem] = useState(null)

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${url}/admin${section.url}`)
        const result = await response.json()
        setData(result.data)
      } catch (error) {
        console.error('Error fetching data:', error)
      }
    }
    fetchData()
  }, [section, url, refresh])

  const handleAction = async (action, item, e) => {
    e.stopPropagation()
    if (action.name.toLowerCase() === 'modificar') {
      setSelectedItem(item)
      setModalOpen(true)
      return
    }
    try {
      const formData = new FormData()
      formData.append('id', item.id || item.idUsuario)
      const response = await fetch(`${url}${action.url}`, {
        method: 'POST',
        body: formData
      })
      const result = await response.json()
      if (response.ok) {
        toast.success(`${section.singular} ${action.name.toLowerCase()} exitoso`)
        // Actualizar la tabla después de la acción
        setData(prevData => prevData.filter(dataItem => dataItem.id !== (item.id || item.idUsuario)))
        setRefresh(!refresh)
      } else {
        toast.error(result.message || `Error al ${action.name.toLowerCase()} ${section.singular.toLowerCase()}`)
      }
    } catch (error) {
      console.error('Error al realizar la acción:', error)
      toast.error(`Error al ${action.name.toLowerCase()} ${section.singular.toLowerCase()}`)
    }
  }

  const handleRowClick = (item) => {
    const id = item.id
    // Determinar la ruta según la sección
    let route = ''
    if (section.name === 'Usuarios') {
      route = `/perfil/${id}`
    } else if (section.name === 'Servicios') {
      route = `/buy/${id}`
    } else if (section.name === 'Reseñas') {
      const idReseña = item.idServicio
      route = `/buy/${idReseña}`
    }
    navigate(route)
  }

  // Función para renderizar el valor de cada celda según el tipo de dato
  const renderCell = (item, key) => {
    // Mapeo de keys del header a propiedades reales de la API
    const keyMapping = {
      // Usuarios
      ID: item.idUsuario ? 'idUsuario' : 'id',
      Imagen: 'imagen',
      Nombre: 'nombreCompleto',
      Correo: 'email',
      'Fecha Nac.': 'fechaNac',
      Tipo: 'tipoUsuario',
      // Servicios
      Título: 'titulo',
      Descripción: 'descripcion',
      Categoría: 'categorias',
      Proveedor: 'idProveedor',
      Precio: 'precio',
      // Reseñas
      Usuario: 'idCliente',
      Servicio: 'idServicio',
      Calificación: 'calificacion',
      Comentario: 'comentario'
    }

    let value = item[keyMapping[key]] || item[key.toLowerCase().replace(/\s+/g, '').replace(/\./g, '')]

    // Si es Nombre, concatenar nombre y apellido
    if (key === 'Nombre' && item.nombre && item.apellido) {
      value = `${item.nombre} ${item.apellido}`
    }

    // Si es una imagen
    if (key === 'Imagen' && value) {
      return (
        <img
          src={value}
          alt='item'
          className='w-10 h-10 rounded-full object-cover border-2 border-gray-200'
        />
      )
    }

    // Si es fecha de nacimiento, formatearla
    if (key === 'Fecha Nac.' && value) {
      const date = new Date(value)
      const formattedDate = date.toLocaleDateString('es-UY', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
      return <span className='text-gray-600'>{formattedDate}</span>
    }

    // Si es tipo de cuenta/tipo
    if (key === 'Tipo' && value) {
      return (
        <span
          className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${
            value === 'Proveedor'
              ? 'bg-green-100 text-green-800'
              : value === 'Moderador'
                ? 'bg-purple-100 text-purple-800'
                : 'bg-blue-100 text-blue-800'
          }`}
        >
          {value}
        </span>
      )
    }

    // Si es calificación
    if (key === 'Calificación' && typeof value === 'number') {
      return (
        <div className='flex items-center gap-1'>
          {Array.from({ length: 5 }).map((_, i) => (
            <span key={i} className={`material-icons text-sm ${i < value ? 'text-yellow-400' : 'text-gray-300'}`}>
              star
            </span>
          ))}
        </div>
      )
    }

    // Si es precio
    if (key === 'Precio' && value) {
      const precioFormateado = typeof value === 'number' ? `$${value}` : value
      return <span className='font-semibold text-green-600'>{precioFormateado}</span>
    }

    // Si es categoría, mostrar de forma legible
    if (key === 'Categoría' && value) {
      return (
        <span className='text-sm text-gray-600'>
          {value}
        </span>
      )
    }

    // Si es descripción o comentario, limitar caracteres
    if ((key === 'Descripción' || key === 'Comentario') && value) {
      return (
        <span className='text-sm text-gray-600 truncate max-w-xs block' title={value}>
          {value.length > 50 ? `${value.substring(0, 50)}...` : value}
        </span>
      )
    }

    // Si es Proveedor (ID), mostrar el ID
    if (key === 'Proveedor' && value) {
      return <span className='text-gray-600'>ID: {value}</span>
    }

    // Si es Usuario (ID Cliente en reseñas), mostrar el ID
    if (key === 'Usuario' && value) {
      return <span className='text-gray-600'>ID: {value}</span>
    }

    // Si es Servicio (ID Servicio en reseñas), mostrar el ID
    if (key === 'Servicio' && value) {
      return <span className='text-gray-600'>ID: {value}</span>
    }

    // Valor por defecto
    return <span className={key === 'ID' ? 'font-medium text-gray-900' : 'text-gray-600'}>{value || '-'}</span>
  }
  return (
    <div className='bg-white rounded-lg shadow-md p-6'>
      <div className='overflow-x-auto'>
        <table className='w-full min-w-max'>
          <thead>
            <tr className='bg-gray-50 border-b border-gray-200'>
              {
                  section.datos.map((dato) => (
                    <th
                      key={dato}
                      className='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'
                    >
                      {dato}
                    </th>
                  ))
                }
              <th className='px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider'>Acciones</th>
            </tr>
          </thead>
          <tbody className='bg-white divide-y divide-gray-200'>
            {data && data.map((item, index) => (
              <tr
                key={item.id || index}
                className='hover:bg-gray-50 transition-colors cursor-pointer'
                onClick={() => handleRowClick(item)}
              >
                {section.datos.map((key) => (
                  <td key={key} className='px-6 py-4 whitespace-nowrap text-sm'>
                    {renderCell(item, key)}
                  </td>
                ))}
                <td className='px-6 py-4 whitespace-nowrap text-center text-sm font-medium'>
                  <div className='flex items-center justify-center gap-2'>
                    <button
                      className='bg-primary px-4 py-2 rounded-lg h-12 w-24 text-white hover:bg-green-600 hover:text-gray-200 flex items-center justify-center transition-colors'
                      title={action.name}
                      onClick={(e) => handleAction(action, item, e)}
                    >
                      <span className='material-icons md-36 text-center'>
                        {action.icon}
                      </span>
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      {modalOpen && (
        <Modificar
          section={section}
          item={selectedItem}
          onClose={() => {
            setModalOpen(false)
            setSelectedItem(null)
          }}
          onSuccess={() => {
            setRefresh(!refresh)
            setModalOpen(false)
            setSelectedItem(null)
          }}
        />
      )}
    </div>
  )
}

function Crear ({ section }) {
  const { url } = useContext(UsuarioContext)

  const [option, setOption] = useState('')
  const [nombre, setNombre] = useState('')
  const [apellido, setApellido] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [birthDate, setBirthDate] = useState('')
  const handleSubmit = async (e) => {
    e.preventDefault()
    const formData = new FormData()

    formData.append('nombre', nombre)
    formData.append('apellido', apellido)
    formData.append('email', email)
    formData.append('password', password)
    formData.append('fecha_nacimiento', birthDate)
    formData.append('tipo', option)
    try {
      const response = await fetch(`${url}/admin/register`, {
        method: 'POST',
        body: formData
      })
      const data = await response.json()
      if (!response.ok) {
        toast.error(data.message || 'Error al crear el usuario')
      } else {
        toast.success('Usuario creado exitosamente')
        setNombre('')
        setApellido('')
        setEmail('')
        setPassword('')
        setBirthDate('')
        setOption('')
      }
      // eslint-disable-next-line
    } catch (error) {
      toast.error('Error al crear el usuario')
    }
  }
  return (
    <div className='bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto'>
      <h2 className='text-3xl font-bold text-gray-900 mb-6 flex items-center gap-2'>
        <span className='material-icons text-primary'>person_add</span>
        {section}
      </h2>
      <form className='space-y-6' onSubmit={handleSubmit}>
        <div className='flex flex-col'>
          <label htmlFor='name' className='text-sm font-semibold text-gray-700 mb-2'>
            Nombre
          </label>
          <input
            value={nombre}
            onChange={e => setNombre(e.target.value)}
            type='text'
            id='name'
            name='name'
            required
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            placeholder='Ingrese el nombre'
          />
        </div>
        <div className='flex flex-col'>
          <label htmlFor='lastName' className='text-sm font-semibold text-gray-700 mb-2'>
            Apellido
          </label>
          <input
            value={apellido}
            onChange={e => setApellido(e.target.value)}
            type='text'
            id='lastName'
            name='lastName'
            required
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            placeholder='Ingrese el apellido'
          />
        </div>
        <div className='flex flex-col'>
          <label htmlFor='email' className='text-sm font-semibold text-gray-700 mb-2'>
            Email
          </label>
          <input
            value={email}
            onChange={e => setEmail(e.target.value)}
            type='email'
            id='email'
            name='email'
            required
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            placeholder='ejemplo@correo.com'
          />
        </div>
        <div className='flex flex-col'>
          <label htmlFor='password' className='text-sm font-semibold text-gray-700 mb-2'>
            Contraseña
          </label>
          <input
            value={password}
            onChange={e => setPassword(e.target.value)}
            type='password'
            id='password'
            name='password'
            required
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            placeholder='Ingrese la contraseña'
          />
        </div>
        <div>
          <label className='text-sm font-semibold text-gray-700 mb-2 block'>
            Fecha de Nacimiento
          </label>
          <input
            value={birthDate}
            onChange={e => setBirthDate(e.target.value)}
            type='date'
            id='birthDate'
            name='birthDate'
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none w-full'
          />
        </div>
        <div className='flex flex-col'>
          <label htmlFor='userType' className='text-sm font-semibold text-gray-700 mb-2'>
            Que tipo de usuario es?
          </label>
          <select
            id='userType'
            name='userType'
            value={option}
            onChange={e => setOption(e.target.value)}
            required
            className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
          >
            <option value='' disabled>Seleccione un tipo</option>
            <option value='Proveedor'>Proveedor</option>
            <option value='Cliente'>Cliente</option>
            <option value='Moderador'>Moderador</option>
          </select>
        </div>
        <button
          type='submit'
          className='w-full bg-primary text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center gap-2 shadow-md hover:shadow-lg'
        >
          <span className='material-icons'>save</span>
          Crear Usuario
        </button>
      </form>
    </div>
  )
}

function Modificar ({ section, item, onClose, onSuccess }) {
  const { user, setUser, url } = useContext(UsuarioContext)
  const [formData, setFormData] = useState({})
  const [loading, setLoading] = useState(false)
  const [fotoPerfil, setFotoPerfil] = useState(null)
  const [fotoPreview, setFotoPreview] = useState(null)
  // Estados para cambio de contraseña
  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [confirmNewPassword, setConfirmNewPassword] = useState('')
  // Estados para habilidades y títulos
  const [nuevaHabilidad, setNuevaHabilidad] = useState('')
  const [nuevoTitulo, setNuevoTitulo] = useState('')

  // Estados para reseñas
  const [imagenReseña, setImagenReseña] = useState(null)
  const [imagenReseñaPreview, setImagenReseñaPreview] = useState(null)

  // Estados para servicios
  const [fotosConPreview, setFotosConPreview] = useState([])
  const [diasDisponibles, setDiasDisponibles] = useState([])
  const [categoriasElegidas, setCategoriasElegidas] = useState([])
  const [departamentosElegidos, setDepartamentosElegidos] = useState([])
  const [categoria, setCategoria] = useState('')
  const [departamento, setDepartamento] = useState('')
  const [jobQuery, setJobQuery] = useState('')
  const [showDropdown, setShowDropdown] = useState(false)
  const [deptQuery, setDeptQuery] = useState('')
  const [showDeptDropdown, setShowDeptDropdown] = useState(false)
  const dropdownRef = useRef(null)
  const deptDropdownRef = useRef(null)
  const fileInputRef = useRef(null)

  const DEPARTAMENTOS = ['Montevideo', 'Canelones', 'Maldonado', 'San José', 'Colonia', 'Lavalleja', 'Rocha', 'Tacuarembó', 'Paysandú', 'Salto', 'Rivera', 'Artigas', 'Cerro Largo', 'Durazno', 'Florida', 'Soriano', 'Treinta y Tres', 'Flores', 'Rio Negro']
  const DIAS_SEMANA = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo']
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

  const filteredJobs = JOBS.filter((j) => j.toLowerCase().includes(jobQuery.toLowerCase()))
  const filteredDeps = DEPARTAMENTOS.filter((d) => d.toLowerCase().includes(deptQuery.toLowerCase()))

  useEffect(() => {
    if (item) {
      setFormData(item)

      // Cargar datos completos según la sección
      if (section.name === 'Usuarios' && (item.id || item.idUsuario)) {
        // Hacer fetch para obtener datos completos del usuario
        const fetchUsuario = async () => {
          try {
            setLoading(true)
            const userId = item.idUsuario || item.id
            const response = await fetch(`${url}/usuario/getUser?id=${userId}`)
            if (!response.ok) {
              toast.error('Error al cargar el usuario')
              return
            }

            const data = await response.json()
            const usuario = data.data

            // Actualizar formData con datos completos
            setFormData(usuario)

            // Si tiene foto de perfil, no necesitamos preview porque ya está en usuario.imagen
            setFotoPreview(null)
            setFotoPerfil(null)

            toast.success('Usuario cargado correctamente')
          } catch (error) {
            console.error('Error al cargar usuario:', error)
            toast.error('Error al cargar el usuario')
          } finally {
            setLoading(false)
          }
        }

        fetchUsuario()
      } else if (section.name === 'Servicios' && item.id) {
        // Hacer fetch para obtener datos completos del servicio
        const fetchServicio = async () => {
          try {
            setLoading(true)
            const response = await fetch(`${url}/servicios/getServicio?id=${item.id}`)
            if (!response.ok) {
              toast.error('Error al cargar el servicio')
              return
            }

            const data = await response.json()
            const servicio = data.data

            // Actualizar formData con datos completos
            setFormData(servicio)

            // Cargar categorías
            if (servicio.categorias && Array.isArray(servicio.categorias)) {
              setCategoriasElegidas(servicio.categorias)
            }

            // Cargar departamentos/ubicaciones
            if (servicio.ubicaciones && Array.isArray(servicio.ubicaciones)) {
              setDepartamentosElegidos(servicio.ubicaciones)
            }

            // Cargar días disponibles
            if (servicio.diasDisponibles && Array.isArray(servicio.diasDisponibles)) {
              setDiasDisponibles(servicio.diasDisponibles)
            }

            // Cargar imágenes existentes
            if (servicio.imagenes && Array.isArray(servicio.imagenes)) {
              const imagenesConPreview = servicio.imagenes.map((img, index) => {
                return {
                  id: Date.now() + index,
                  preview: typeof img === 'string' ? img : img.toString(),
                  file: null,
                  isExisting: true
                }
              })
              setFotosConPreview(imagenesConPreview)
            } else {
              console.warn('No se encontraron imágenes o no es un array:', servicio.imagenes)
            }

            toast.success('Servicio cargado correctamente')
          } catch (error) {
            console.error('Error al cargar servicio:', error)
            toast.error('Error al cargar el servicio')
          } finally {
            setLoading(false)
          }
        }

        fetchServicio()
      } else {
        // Para otras secciones (Reseñas), usar los datos del item directamente
        setFormData(item)
      }
    }
  }, [item, section.name, url])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
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

  const handleImagenReseñaChange = (e) => {
    const file = e.target.files[0]
    if (file) {
      // Validar tamaño (máx 2MB)
      const MAX_FILE_SIZE = 2 * 1024 * 1024
      if (file.size > MAX_FILE_SIZE) {
        toast.error(`La imagen es muy grande: ${(file.size / 1024 / 1024).toFixed(2)}MB. Máximo permitido: 2MB.`)
        return
      }
      setImagenReseña(file)
      const previewUrl = URL.createObjectURL(file)
      setImagenReseñaPreview(previewUrl)
    }
  }

  const agregarHabilidad = () => {
    if (nuevaHabilidad.trim()) {
      const habilidadesActuales = formData.habilidades || []
      setFormData(prev => ({
        ...prev,
        habilidades: [...habilidadesActuales, { habilidad: nuevaHabilidad.trim() }]
      }))
      setNuevaHabilidad('')
    }
  }

  const eliminarHabilidad = (index) => {
    const habilidadesActuales = [...(formData.habilidades || [])]
    habilidadesActuales.splice(index, 1)
    setFormData(prev => ({
      ...prev,
      habilidades: habilidadesActuales
    }))
  }

  const agregarTitulo = () => {
    if (nuevoTitulo.trim()) {
      const titulosActuales = formData.titulos || []
      setFormData(prev => ({
        ...prev,
        titulos: [...titulosActuales, { titulo: nuevoTitulo.trim() }]
      }))
      setNuevoTitulo('')
    }
  }

  const eliminarTitulo = (index) => {
    const titulosActuales = [...(formData.titulos || [])]
    titulosActuales.splice(index, 1)
    setFormData(prev => ({
      ...prev,
      titulos: titulosActuales
    }))
  }

  // Funciones para servicios
  const agregarCategoria = (job) => {
    const cat = job || categoria
    if (cat && !categoriasElegidas.includes(cat)) {
      setCategoriasElegidas((prev) => [...prev, cat])
    }
    if (job) {
      setCategoria('')
      setJobQuery('')
      setShowDropdown(false)
    }
  }

  const eliminarCategoria = (cat) => {
    setCategoriasElegidas(categoriasElegidas.filter(c => c !== cat))
  }

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

  const changeFotoServicio = (e) => {
    const files = Array.from(e.target.files || [])
    if (files.length === 0) return

    // Validar tamaño de archivos (máx 2MB por archivo)
    const MAX_FILE_SIZE = 2 * 1024 * 1024 // 2MB en bytes
    const archivosGrandes = files.filter(file => file.size > MAX_FILE_SIZE)

    if (archivosGrandes.length > 0) {
      const nombres = archivosGrandes.map(f => `${f.name} (${(f.size / 1024 / 1024).toFixed(2)}MB)`).join(', ')
      toast.error(`Las siguientes imágenes son muy grandes: ${nombres}. Máximo permitido: 2MB por imagen.`)
      return
    }

    const nuevasFotos = files.map((file) => ({
      id: Date.now() + Math.random(),
      file,
      preview: URL.createObjectURL(file),
      isExisting: false
    }))

    setFotosConPreview((prev) => [...prev, ...nuevasFotos])
  }

  const eliminarFoto = (id) => {
    const fotoAEliminar = fotosConPreview.find(f => f.id === id)
    if (fotoAEliminar && !fotoAEliminar.isExisting) {
      URL.revokeObjectURL(fotoAEliminar.preview)
    }
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

  // Limpiar object URLs al desmontar
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
    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
      fotosConPreview.forEach(foto => {
        if (!foto.isExisting) {
          URL.revokeObjectURL(foto.preview)
        }
      })
    }
  }, [fotosConPreview])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    const loadingToast = toast.loading('Guardando cambios...')

    try {
      const submitData = new FormData()

      // Agregar campos según la sección
      if (section.name === 'Usuarios') {
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
            passForm.append('idUsuario', item.idUsuario || item.id)
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

            toast.success('Contraseña cambiada correctamente')

            // Limpiar campos de contraseña
            setCurrentPassword('')
            setNewPassword('')
            setConfirmNewPassword('')
          } catch (passError) {
            toast.dismiss(loadingToast)
            toast.error(passError.message || 'Error al cambiar la contraseña')
            setLoading(false)
            return
          }
        }

        const dato = new FormData()
        dato.append('idUsuario', formData.idUsuario || formData.id)
        dato.append('nombre', formData.nombre || '')
        dato.append('apellido', formData.apellido || '')
        dato.append('email', formData.email || '')
        dato.append('telefono', formData.telefono || '')

        // Si hay una nueva foto, la enviamos
        if (fotoPerfil) {
          dato.append('foto_perfil', fotoPerfil)
        }

        // Determinar si es proveedor (puede venir como tipoUsuario === 'Proveedor' o esProveedor === true)
        const esProveedor = formData.tipoUsuario === 'Proveedor' || formData.esProveedor === true || formData.esProveedor === 'true'

        // Campos de proveedor
        if (esProveedor) {
          dato.append('es_proveedor', 'true')
          dato.append('nombre_empresa', formData.nombre_empresa || '')
          dato.append('experiencia', formData.experiencia || '')
          dato.append('descripcion', formData.descripcion || '')

          // Enviar habilidades
          if (formData.habilidades && formData.habilidades.length > 0) {
            formData.habilidades.forEach(hab => {
              dato.append('habilidades[]', hab.habilidad)
            })
          }

          // Enviar títulos
          if (formData.titulos && formData.titulos.length > 0) {
            formData.titulos.forEach(tit => {
              dato.append('titulos[]', tit.titulo)
            })
          }
        } else {
          // Si no es proveedor, enviar false
          dato.append('es_proveedor', 'false')
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
        if (user && user.id === formData.id) {
          // Obtener los datos actualizados del servidor
          try {
            const userResponse = await fetch(`${url}/usuario/getUser?id=${formData.id}`)
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

        // Cerrar modal y refrescar
        onSuccess()

        // Si se cambió la foto, recargar para actualizar el header
        if (fotoPerfil) {
          setTimeout(() => {
            window.location.reload()
          }, 1000)
        }

        return // Importante: salir después de procesar usuarios
      } else if (section.name === 'Servicios') {
        // Validaciones para servicios
        if (!formData.nombre || !formData.descripcion || !formData.precio) {
          toast.dismiss(loadingToast)
          toast.error('Por favor completa todos los campos obligatorios')
          setLoading(false)
          return
        }

        const precioNumerico = parseFloat(formData.precio)
        if (isNaN(precioNumerico) || precioNumerico < 0) {
          toast.dismiss(loadingToast)
          toast.error('No se permite el precio negativo')
          setLoading(false)
          return
        }

        if (precioNumerico > 500000) {
          toast.dismiss(loadingToast)
          toast.error('Si desea publicar un servicio más caro de lo permitido contáctanos: clutchsoftware2025@gmail.com')
          setLoading(false)
          return
        }

        if (diasDisponibles.length === 0) {
          toast.dismiss(loadingToast)
          toast.error('Selecciona al menos un día disponible')
          setLoading(false)
          return
        }

        if (fotosConPreview.length === 0) {
          toast.dismiss(loadingToast)
          toast.error('Por favor agrega al menos una foto del servicio')
          setLoading(false)
          return
        }

        if (departamentosElegidos.length === 0) {
          toast.dismiss(loadingToast)
          toast.error('Por favor agrega al menos un departamento')
          setLoading(false)
          return
        }

        if (categoriasElegidas.length === 0) {
          toast.dismiss(loadingToast)
          toast.error('Por favor agrega al menos una categoría')
          setLoading(false)
          return
        }

        const serviceData = new FormData()
        serviceData.append('idServicio', formData.id)
        serviceData.append('nombre', formData.nombre)
        serviceData.append('descripcion', formData.descripcion)
        serviceData.append('precio', formData.precio)
        serviceData.append('idProveedor', formData.idProveedor || formData.id_proveedor)

        // Agregar categorías y ubicaciones como arrays
        categoriasElegidas.forEach(cat => serviceData.append('categoria[]', cat))
        departamentosElegidos.forEach(dep => serviceData.append('ubicacion[]', dep))

        // Separar imágenes nuevas de existentes
        const imagenesExistentes = fotosConPreview
          .filter(f => f.isExisting)
          .map(f => {
            const preview = f.preview
            // Asegurarse de que sea una string limpia
            if (typeof preview === 'string') {
              return preview.trim()
            } else if (preview && typeof preview === 'object' && preview.toString) {
              return preview.toString().trim()
            } else {
              console.warn('Imagen con formato inesperado:', preview)
              return ''
            }
          })
          .filter(url => url && url.length > 0) // Filtrar strings vacías

        const imagenesNuevas = fotosConPreview.filter(f => !f.isExisting)

        // Enviar imágenes existentes - una por campo con índice
        // IMPORTANTE: Asegurar que sean SOLO strings, no objetos
        imagenesExistentes.forEach((urlImg, idx) => {
          // Triple validación para asegurar que sea string
          const urlString = String(urlImg)
          serviceData.append('imagenesExistentes[]', urlString)
        })

        // Enviar imágenes nuevas (archivos) - el backend espera $_FILES['fotoServicio'] como array
        imagenesNuevas.forEach((foto, idx) => {
          if (foto.file && foto.file instanceof File) {
            serviceData.append('fotoServicio[]', foto.file)
          } else {
            console.warn(`Imagen nueva ${idx} no es un archivo válido:`, foto)
          }
        })

        diasDisponibles.forEach((dia) => serviceData.append('diasDisponibles[]', dia))

        const response = await fetch(`${url}/servicios/updateServicio`, {
          method: 'POST',
          body: serviceData
        })

        if (response.ok) {
          const json = await response.json()
          toast.dismiss(loadingToast)
          toast.success('Servicio actualizado exitosamente')
          setLoading(false)
          onSuccess()
        } else {
          // Manejar error 413 (archivo muy grande)
          if (response.status === 413) {
            toast.dismiss(loadingToast)
            toast.error('Las imágenes son demasiado grandes. Por favor usa imágenes más pequeñas (máx. 2MB cada una).')
            setLoading(false)
            return
          }

          toast.dismiss(loadingToast)
          try {
            const errorData = await response.json()
            const errorMessage = errorData.message || errorData.error || 'Error al actualizar el servicio'
            toast.error(errorMessage)
          } catch (parseError) {
            console.error('Error parseando respuesta:', parseError)
            toast.error('Error al actualizar el servicio. Por favor verifica los datos.')
          }
          setLoading(false)
          return
        }

        return // Importante: salir después de procesar servicios
      } else if (section.name === 'Reseñas') {
        // Validaciones para reseñas
        if (!formData.calificacion || !formData.comentario) {
          toast.dismiss(loadingToast)
          toast.error('Por favor completa la calificación y el comentario')
          setLoading(false)
          return
        }

        const calificacion = parseInt(formData.calificacion)
        if (isNaN(calificacion) || calificacion < 1 || calificacion > 5) {
          toast.dismiss(loadingToast)
          toast.error('La calificación debe ser un número entre 1 y 5')
          setLoading(false)
          return
        }

        const reviewData = new FormData()
        reviewData.append('id', formData.id)
        reviewData.append('calificacion', formData.calificacion)
        reviewData.append('comentario', formData.comentario)

        // Si hay una nueva imagen, agregarla
        if (imagenReseña) {
          reviewData.append('imagen', imagenReseña)
        }

        const response = await fetch(`${url}/admin/reviewModify`, {
          method: 'POST',
          body: reviewData
        })

        if (response.ok) {
          const result = await response.json()
          toast.dismiss(loadingToast)
          toast.success('Reseña modificada exitosamente')

          // Limpiar preview de imagen
          if (imagenReseñaPreview) {
            URL.revokeObjectURL(imagenReseñaPreview)
            setImagenReseñaPreview(null)
            setImagenReseña(null)
          }

          setLoading(false)
          onSuccess()
        } else {
          toast.dismiss(loadingToast)
          try {
            const errorData = await response.json()
            const errorMessage = errorData.message || errorData.error || 'Error al modificar la reseña'
            toast.error(errorMessage)
          } catch (parseError) {
            console.error('Error parseando respuesta:', parseError)
            toast.error('Error al modificar la reseña')
          }
          setLoading(false)
          return
        }

        return // Importante: salir después de procesar reseñas
      }

      const response = await fetch(`${url}/admin/${section.name.toLowerCase()}Modify`, {
        method: 'POST',
        body: submitData
      })

      const result = await response.json()

      if (response.ok) {
        toast.success(`${section.singular} modificado exitosamente`)
        onSuccess()
      } else {
        toast.error(result.message || `Error al modificar ${section.singular.toLowerCase()}`)
      }
    } catch (error) {
      console.error('Error al modificar:', error)
      toast.error(`Error al modificar ${section.singular.toLowerCase()}`)
    }
  }

  const renderFormFields = () => {
    if (section.name === 'Usuarios') {
      return (
        <>
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Nombre</label>
            <input
              type='text'
              name='nombre'
              value={formData.nombre || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              required
            />
          </div>
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Apellido</label>
            <input
              type='text'
              name='apellido'
              value={formData.apellido || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              required
            />
          </div>
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Email</label>
            <input
              type='email'
              name='email'
              value={formData.email || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              required
            />
          </div>

          {/* Foto de perfil */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Foto de Perfil</label>
            {fotoPreview && (
              <img src={fotoPreview} alt='Preview' className='w-24 h-24 rounded-full object-cover mb-2' />
            )}
            {!fotoPreview && formData.imagen && (
              <img src={formData.imagen} alt='Actual' className='w-24 h-24 rounded-full object-cover mb-2' />
            )}
            <input
              type='file'
              accept='image/*'
              onChange={handleFotoChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            />
          </div>

          {/* Campos adicionales para proveedores */}
          {(formData.tipoUsuario === 'Proveedor' || formData.esProveedor) && (
            <div className='border-t pt-4 mt-4'>
              <h3 className='text-lg font-semibold text-gray-900 mb-4'>Información de Proveedor</h3>

              <div className='flex flex-col mb-4'>
                <label className='text-sm font-semibold text-gray-700 mb-2'>Nombre de Empresa</label>
                <input
                  type='text'
                  name='nombre_empresa'
                  value={formData.nombre_empresa || ''}
                  onChange={handleChange}
                  className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                  placeholder='Ingrese el nombre de la empresa'
                />
              </div>

              <div className='flex flex-col mb-4'>
                <label className='text-sm font-semibold text-gray-700 mb-2'>Descripción</label>
                <textarea
                  name='descripcion'
                  value={formData.descripcion || ''}
                  onChange={handleChange}
                  rows={4}
                  className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none resize-none'
                  placeholder='Describe los servicios o expertise'
                />
              </div>

              <div className='flex flex-col mb-4'>
                <label className='text-sm font-semibold text-gray-700 mb-2'>Experiencia (años)</label>
                <input
                  type='text'
                  name='experiencia'
                  value={formData.experiencia || ''}
                  onChange={handleChange}
                  className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                  placeholder='Ej: 5 años'
                />
              </div>

              <div className='flex flex-col mb-4'>
                <label className='text-sm font-semibold text-gray-700 mb-2'>Habilidades</label>
                <div className='flex gap-2 mb-2'>
                  <input
                    type='text'
                    value={nuevaHabilidad}
                    onChange={(e) => setNuevaHabilidad(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), agregarHabilidad())}
                    className='flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                    placeholder='Escribe una habilidad'
                  />
                  <button
                    type='button'
                    onClick={agregarHabilidad}
                    className='bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-1'
                  >
                    <span className='material-icons text-sm'>add</span>
                    Agregar
                  </button>
                </div>
                <div className='flex flex-wrap gap-2'>
                  {formData.habilidades && formData.habilidades.length > 0
                    ? (
                        formData.habilidades.map((hab, index) => (
                          <span
                            key={index}
                            className='bg-blue-100 text-blue-800 px-3 py-1 rounded-full flex items-center gap-2'
                          >
                            {hab.habilidad || hab}
                            <button
                              type='button'
                              onClick={() => eliminarHabilidad(index)}
                              className='text-blue-600 hover:text-blue-800'
                            >
                              <span className='material-icons text-sm'>close</span>
                            </button>
                          </span>
                        ))
                      )
                    : (
                      <span className='text-gray-500 text-sm'>No hay habilidades</span>
                      )}
                </div>
              </div>

              <div className='flex flex-col'>
                <label className='text-sm font-semibold text-gray-700 mb-2'>Títulos</label>
                <div className='flex gap-2 mb-2'>
                  <input
                    type='text'
                    value={nuevoTitulo}
                    onChange={(e) => setNuevoTitulo(e.target.value)}
                    onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), agregarTitulo())}
                    className='flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                    placeholder='Escribe un título'
                  />
                  <button
                    type='button'
                    onClick={agregarTitulo}
                    className='bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center gap-1'
                  >
                    <span className='material-icons text-sm'>add</span>
                    Agregar
                  </button>
                </div>
                <div className='flex flex-wrap gap-2'>
                  {formData.titulos && formData.titulos.length > 0
                    ? (
                        formData.titulos.map((tit, index) => (
                          <span
                            key={index}
                            className='bg-green-100 text-green-800 px-3 py-1 rounded-full flex items-center gap-2'
                          >
                            {tit.titulo || tit}
                            <button
                              type='button'
                              onClick={() => eliminarTitulo(index)}
                              className='text-green-600 hover:text-green-800'
                            >
                              <span className='material-icons text-sm'>close</span>
                            </button>
                          </span>
                        ))
                      )
                    : (
                      <span className='text-gray-500 text-sm'>No hay títulos</span>
                      )}
                </div>
              </div>
            </div>
          )}

          {/* Cambio de contraseña (opcional) */}
          <div className='border-t pt-4 mt-4'>
            <h3 className='text-lg font-semibold text-gray-900 mb-4'>Cambiar Contraseña (Opcional)</h3>
            <div className='flex flex-col mb-4'>
              <label className='text-sm font-semibold text-gray-700 mb-2'>Contraseña Actual</label>
              <input
                type='password'
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                placeholder='Dejar en blanco para no cambiar'
              />
            </div>
            <div className='flex flex-col mb-4'>
              <label className='text-sm font-semibold text-gray-700 mb-2'>Nueva Contraseña</label>
              <input
                type='password'
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                placeholder='Mínimo 8 caracteres'
              />
            </div>
            <div className='flex flex-col'>
              <label className='text-sm font-semibold text-gray-700 mb-2'>Confirmar Nueva Contraseña</label>
              <input
                type='password'
                value={confirmNewPassword}
                onChange={(e) => setConfirmNewPassword(e.target.value)}
                className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
                placeholder='Repetir nueva contraseña'
              />
            </div>
          </div>
        </>
      )
    } else if (section.name === 'Servicios') {
      return (
        <>
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Nombre del Servicio</label>
            <input
              type='text'
              name='nombre'
              value={formData.nombre || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              placeholder='Ej: Instalación eléctrica residencial'
              required
            />
          </div>

          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Descripción</label>
            <textarea
              name='descripcion'
              value={formData.descripcion || ''}
              onChange={handleChange}
              rows={4}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none resize-none'
              placeholder='Describe tu servicio en detalle...'
              required
            />
          </div>

          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Precio (UYU)</label>
            <input
              type='number'
              name='precio'
              value={formData.precio || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              placeholder='1000'
              required
              min='0'
              step='0.01'
            />
          </div>

          {/* Categorías */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Categorías</label>
            <div className='relative' ref={dropdownRef}>
              <input
                type='text'
                value={jobQuery}
                onChange={(e) => {
                  setJobQuery(e.target.value)
                  setShowDropdown(true)
                }}
                onFocus={() => setShowDropdown(true)}
                className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none w-full'
                placeholder='Buscar categoría...'
              />
              {showDropdown && filteredJobs.length > 0 && (
                <div className='absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto'>
                  {filteredJobs.map((job) => (
                    <div
                      key={job}
                      onClick={() => agregarCategoria(job)}
                      className='px-4 py-2 hover:bg-primary hover:text-white cursor-pointer transition-colors'
                    >
                      {job}
                    </div>
                  ))}
                </div>
              )}
            </div>
            <div className='flex flex-wrap gap-2 mt-2'>
              {categoriasElegidas.map((cat, index) => (
                <span
                  key={index}
                  className='bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center gap-2'
                >
                  {cat}
                  <button
                    type='button'
                    onClick={() => eliminarCategoria(cat)}
                    className='text-blue-600 hover:text-blue-800'
                  >
                    <span className='material-icons text-sm'>close</span>
                  </button>
                </span>
              ))}
              {categoriasElegidas.length === 0 && (
                <span className='text-gray-500 text-sm'>No hay categorías seleccionadas</span>
              )}
            </div>
          </div>

          {/* Departamentos */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Departamentos</label>
            <div className='relative' ref={deptDropdownRef}>
              <input
                type='text'
                value={deptQuery}
                onChange={(e) => {
                  setDeptQuery(e.target.value)
                  setShowDeptDropdown(true)
                }}
                onFocus={() => setShowDeptDropdown(true)}
                className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none w-full'
                placeholder='Buscar departamento...'
              />
              {showDeptDropdown && filteredDeps.length > 0 && (
                <div className='absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto'>
                  {filteredDeps.map((dep) => (
                    <div
                      key={dep}
                      onClick={() => agregarDepartamento(dep)}
                      className='px-4 py-2 hover:bg-primary hover:text-white cursor-pointer transition-colors'
                    >
                      {dep}
                    </div>
                  ))}
                </div>
              )}
            </div>
            <div className='flex flex-wrap gap-2 mt-2'>
              {departamentosElegidos.map((dep, index) => (
                <span
                  key={index}
                  className='bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm flex items-center gap-2'
                >
                  {dep}
                  <button
                    type='button'
                    onClick={() => eliminarDepartamento(dep)}
                    className='text-purple-600 hover:text-purple-800'
                  >
                    <span className='material-icons text-sm'>close</span>
                  </button>
                </span>
              ))}
              {departamentosElegidos.length === 0 && (
                <span className='text-gray-500 text-sm'>No hay departamentos seleccionados</span>
              )}
            </div>
          </div>

          {/* Días disponibles */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Días Disponibles</label>
            <div className='grid grid-cols-2 gap-3'>
              {DIAS_SEMANA.map((dia) => (
                <label key={dia} className='flex items-center gap-2 cursor-pointer'>
                  <input
                    type='checkbox'
                    value={dia}
                    checked={diasDisponibles.includes(dia)}
                    onChange={changeDiasDisponibles}
                    className='w-4 h-4 text-primary focus:ring-primary border-gray-300 rounded'
                  />
                  <span className='text-gray-700'>{dia}</span>
                </label>
              ))}
            </div>
          </div>

          {/* Fotos del servicio */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Fotos del Servicio</label>
            <input
              type='file'
              ref={fileInputRef}
              onChange={changeFotoServicio}
              accept='image/*'
              multiple
              className='hidden'
            />
            <button
              type='button'
              onClick={() => fileInputRef.current?.click()}
              className='px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-primary transition-colors flex items-center justify-center gap-2 text-gray-600 hover:text-primary'
            >
              <span className='material-icons'>add_photo_alternate</span>
              Agregar Fotos
            </button>
            <div className='grid grid-cols-3 gap-4 mt-4'>
              {fotosConPreview.map((foto) => (
                <div key={foto.id} className='relative group'>
                  <img
                    src={foto.preview}
                    alt='Preview'
                    className='w-full h-32 object-cover rounded-lg'
                  />
                  <button
                    type='button'
                    onClick={() => eliminarFoto(foto.id)}
                    className='absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity'
                  >
                    <span className='material-icons text-sm'>close</span>
                  </button>
                  {foto.isExisting && (
                    <span className='absolute bottom-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded'>
                      Existente
                    </span>
                  )}
                </div>
              ))}
            </div>
            {fotosConPreview.length === 0 && (
              <p className='text-gray-500 text-sm mt-2'>No hay fotos agregadas</p>
            )}
          </div>
        </>
      )
    } else if (section.name === 'Reseñas') {
      return (
        <>
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Calificación</label>
            <select
              name='calificacion'
              value={formData.calificacion || ''}
              onChange={handleChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
              required
            >
              <option value=''>Seleccione una calificación</option>
              <option value='1'>1 Estrella</option>
              <option value='2'>2 Estrellas</option>
              <option value='3'>3 Estrellas</option>
              <option value='4'>4 Estrellas</option>
              <option value='5'>5 Estrellas</option>
            </select>
          </div>

          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Comentario</label>
            <textarea
              name='comentario'
              value={formData.comentario || ''}
              onChange={handleChange}
              rows={4}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none resize-none'
              placeholder='Escribe tu comentario sobre el servicio...'
              required
            />
          </div>

          {/* Imagen de la reseña (opcional) */}
          <div className='flex flex-col'>
            <label className='text-sm font-semibold text-gray-700 mb-2'>Imagen (Opcional)</label>

            {/* Preview de la imagen nueva */}
            {imagenReseñaPreview && (
              <div className='mb-4 relative'>
                <img
                  src={imagenReseñaPreview}
                  alt='Preview nueva'
                  className='w-full max-h-64 object-cover rounded-lg border-2 border-green-500'
                />
                <span className='absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded'>
                  Nueva imagen
                </span>
                <button
                  type='button'
                  onClick={() => {
                    URL.revokeObjectURL(imagenReseñaPreview)
                    setImagenReseñaPreview(null)
                    setImagenReseña(null)
                  }}
                  className='absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors'
                >
                  <span className='material-icons text-sm'>close</span>
                </button>
              </div>
            )}

            {/* Imagen actual (si existe y no hay preview) */}
            {!imagenReseñaPreview && formData.imagen && (
              <div className='mb-4 relative'>
                <img
                  src={formData.imagen}
                  alt='Imagen actual'
                  className='w-full max-h-64 object-cover rounded-lg border-2 border-gray-300'
                />
                <span className='absolute top-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded'>
                  Imagen actual
                </span>
              </div>
            )}

            {/* Botón para subir nueva imagen */}
            <input
              type='file'
              accept='image/*'
              onChange={handleImagenReseñaChange}
              className='px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none'
            />
            <p className='text-xs text-gray-500 mt-2'>
              Tamaño máximo: 2MB. Formatos: JPG, PNG, GIF
            </p>
          </div>
        </>
      )
    }
  }

  return (
    <div className='fixed bg-black/50 w-screen h-screen top-0 left-0 flex items-center justify-center z-50' onClick={onClose}>
      <div className='bg-white rounded-lg shadow-xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto' onClick={(e) => e.stopPropagation()}>
        <div className='flex items-center justify-between mb-6'>
          <h2 className='text-3xl font-bold text-gray-900 flex items-center gap-2'>
            <span className='material-icons text-primary'>edit</span>
            Modificar {section.singular}
          </h2>
          <button
            onClick={onClose}
            className='text-gray-500 hover:text-gray-700 transition-colors'
          >
            <span className='material-icons'>close</span>
          </button>
        </div>

        <form onSubmit={handleSubmit} className='space-y-6'>
          {renderFormFields()}

          <div className='flex gap-4 pt-4'>
            <button
              type='button'
              onClick={onClose}
              disabled={loading}
              className='flex-1 bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed'
            >
              <span className='material-icons'>close</span>
              Cancelar
            </button>
            <button
              type='submit'
              disabled={loading}
              className='flex-1 bg-primary text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center gap-2 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed'
            >
              <span className='material-icons'>{loading ? 'hourglass_empty' : 'save'}</span>
              {loading ? 'Guardando...' : 'Guardar Cambios'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
