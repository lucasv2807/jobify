import { useEffect, useState, useContext, useRef } from 'react'
import { Header } from '../components/Header.jsx'
import { useParams } from 'react-router'
import { UsuarioContext } from '../context/Usuario.jsx'
import { ToastContainer, toast } from 'react-toastify'
import { Footer } from '../components/Footer.jsx'
import Carousel from 'react-multi-carousel'
import { Card } from '../components/Card.jsx'
import { LoginMp } from '../components/LoginMp.jsx'

export function Perfil () {
  const { user, url, setUser } = useContext(UsuarioContext)
  const [userProfile, setUserProfile] = useState({})
  const [edad, setEdad] = useState(null)
  const { id } = useParams()
  const [servicios, setDatos] = useState([])
  const [favoritos, setFavoritos] = useState([])
  const [serviciosContratados, setServiciosContratados] = useState([])
  const [mostrarTodos, setMostrarTodos] = useState(false)
  const userRef = useRef(user)

  // Mantener la referencia actualizada
  useEffect(() => {
    userRef.current = user
  }, [user])

  // Efecto para cargar datos del perfil
  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(`${url}/usuario/getUser?id=${id}`)
      const data = await response.json()
      if (!response.ok) {
        toast.error('Error al cargar el perfil del usuario')
        return
      }
      setUserProfile(data.data)
    }
    fetchData()
  }, [id, url, setUser])

  // Efecto para calcular edad
  useEffect(() => {
    if (!userProfile.fecha_nacimiento) return

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
  }, [userProfile.fecha_nacimiento])
  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(`${url}/servicios/getServiciosByProveedor?idProveedor=${id}`)
      const data = await response.json()
      if (!response.ok) {
        if (data.message === 'El usuario no es un proveedor') {
          return
        }
      }
      const servicios = data.data.response
      setDatos(servicios)
    }
    fetchData()
  }, [url, id])
  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(`${url}/servicios/getFavoritos?idCliente=${user.id}`)
      const data = await response.json()
      if (!response.ok) {
        if (data.message === 'El usuario no es un cliente') {
          return
        }
      }
      const favoritos = data.data.response
      setFavoritos(favoritos)
    }
    fetchData()
  }, [url, user.id])

  // Obtener servicios contratados con reservas confirmadas
  useEffect(() => {
    const fetchServiciosContratados = async () => {
      try {
        const response = await fetch(`${url}/reserva/getReservaCliente?id=${id}`)
        const data = await response.json()

        if (response.ok && Array.isArray(data.data)) {
          // Filtrar solo las reservas confirmadas (confirmado === 1)
          const reservasConfirmadas = data.data.filter(reserva => reserva.confirmado === 1)
          setServiciosContratados(reservasConfirmadas)
        } else {
          setServiciosContratados([])
        }
      } catch (error) {
        console.error('Error al cargar servicios contratados:', error)
        setServiciosContratados([])
      }
    }

    if (id) {
      fetchServiciosContratados()
    }
  }, [url, id])
  return (
    <>
      <Header />
      <main className='container min-w-full min-h-screen bg-claro mx-auto px-4 sm:px-6 lg:px-8 py-12'>
        <div className='w-full h-full mx-auto'>
          <div className='flex flex-col md:flex-row gap-8 w-full mb-10 h-full'>
            <div className='md:w-1/2'>
              <div className='bg-white p-6 rounded-lg shadow-md pb-10 h-full'>
                <div className='text-center'>
                  <div className='relative w-32 h-32 mx-auto mb-4'>
                    <img alt='' className='w-full h-full rounded-full object-cover' src={userProfile.foto_perfil} />
                  </div>
                  <h2 className='text-2xl font-bold text-gray-900'>{userProfile.nombre} {userProfile.apellido}</h2>
                  {userProfile.esProveedor && <h2 className='text-lg text-gray-600'>Nombre de la empresa: {userProfile.nombre_empresa}</h2>}
                  {userProfile.esProveedor && userProfile.totalResenas > 0 && (
                    <div className='mt-3 flex items-center justify-center gap-2'>
                      <div className='flex items-center'>
                        {[1, 2, 3, 4, 5].map((star) => (
                          <span
                            key={star}
                            className='material-icons text-2xl'
                            style={{
                              color: star <= Math.round(userProfile.calificacionPromedio) ? '#FFD700' : '#E0E0E0'
                            }}
                          >
                            star
                          </span>
                        ))}
                      </div>
                      <span className='text-lg font-semibold text-gray-700'>
                        {userProfile.calificacionPromedio.toFixed(1)}
                      </span>
                      <span className='text-sm text-gray-500'>
                        ({userProfile.totalResenas} {userProfile.totalResenas === 1 ? 'reseña' : 'reseñas'})
                      </span>
                    </div>
                  )}
                  {userProfile.esProveedor && userProfile.totalResenas === 0 && (
                    <div className='mt-3 text-gray-500 text-sm'>
                      Sin calificaciones aún
                    </div>
                  )}
                </div>
                <hr className='my-6 border-gray-200' />
                <div>
                  <h3 className='font-semibold text-gray-800 mb-3 text-lg'>Información de Contacto</h3>
                  <div className='space-y-3 text-gray-600'>
                    <div className='flex items-center'>
                      <span className='material-icons text-primary mr-3'>email</span>
                      <span>{userProfile.email}</span>
                    </div>
                    <div>
                      {user.id === parseInt(id) && userProfile.telefono !== null
                        ? (
                          <>
                            <span className='material-icons text-primary mr-3'>phone</span>
                            <span>{userProfile.telefono}</span>
                          </>
                          )
                        : null}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            {userProfile.esProveedor === true
              ? (<Proveedor user={userProfile} edad={edad} />)
              : (
                <div className='lg:col-span-2 bg-white p-6 sm:p-8 rounded-lg shadow-md'>
                  <h3 className='font-semibold text-gray-800 mb-6 text-lg'>Historial de servicios confirmados</h3>
                  <div className='space-y-4'>
                    {serviciosContratados.length > 0
                      ? (
                        <>
                          {serviciosContratados.slice(0, mostrarTodos ? serviciosContratados.length : 3).map((reserva) => (
                            <div key={reserva.idReserva} className='bg-white border-2 border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow'>
                              <div className='flex items-center gap-4'>
                                <img
                                  src={reserva.imagen}
                                  alt={reserva.servicioTitulo}
                                  className='w-16 h-16 rounded-lg object-cover'
                                />
                                <div className='flex-1'>
                                  <h4 className='font-bold text-gray-900'>
                                    Proveedor: {reserva.nombreProveedor} {reserva.apellidoProveedor}
                                    </h4>
                                  <p className='text-sm text-gray-600'>
                                    Servicio: {reserva.servicioTitulo}
                                  </p>
                                  <div className='flex items-center gap-2 mt-1'>
                                    <span className='material-icons text-primary text-sm'>calendar_today</span>
                                    <p className='text-sm text-gray-500'>
                                      {new Date(reserva.fecha).toLocaleDateString('es-ES', {
                                        day: '2-digit',
                                        month: 'long',
                                        year: 'numeric'
                                      })}
                                    </p>
                                  </div>
                                </div>
                                <div className='flex items-center gap-2 bg-green-100 px-3 py-1 rounded-full'>
                                  <span className='material-icons text-green-600 text-sm'>check_circle</span>
                                  <span className='text-xs font-semibold text-green-700'>Confirmado</span>
                                </div>
                              </div>
                            </div>
                          ))}
                          {serviciosContratados.length > 3 && (
                            <button
                              onClick={() => setMostrarTodos(!mostrarTodos)}
                              className='w-full py-3 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center justify-center gap-2'
                            >
                              <span>{mostrarTodos ? 'Ver menos' : `Ver más (${serviciosContratados.length - 3} más)`}</span>
                              <span className='material-icons text-lg'>
                                {mostrarTodos ? 'expand_less' : 'expand_more'}
                              </span>
                            </button>
                          )}
                        </>
                        )
                      : (
                        <div className='bg-gray-50 border-2 border-gray-200 rounded-lg p-4 text-center'>
                          <span className='material-icons text-gray-400 text-5xl mb-2'>history</span>
                          <p className='text-gray-600 font-medium'>No hay servicios confirmados aún</p>
                          <p className='text-sm text-gray-500 mt-2'>Los servicios aparecerán aquí una vez que el proveedor confirme tu reserva</p>
                        </div>
                        )}
                  </div>
                </div>
                )}
          </div>
          {userProfile.esProveedor && (
            <div className='bg-white p-6 rounded-lg shadow-md pb-10'>
              <h3 className='text-xl font-bold text-primary mt-6 mb-4'>Portafolio</h3>
              {servicios && servicios.length > 0
                ? (
                  <Carousel
                    additionalTransfrom={0}
                    arrows
                    autoPlay={false}
                    centerMode={false}
                    className=''
                    containerClass='container-with-dots'
                    dotListClass=''
                    draggable
                    focusOnSelect={false}
                    infinite
                    itemClass='h-full flex'
                    keyBoardControl
                    minimumTouchDrag={80}
                    pauseOnHover
                    renderArrowsWhenDisabled={false}
                    renderButtonGroupOutside={false}
                    renderDotsOutside={false}
                    responsive={{
                      desktop: {
                        breakpoint: {
                          max: 3000,
                          min: 1024
                        },
                        items: 4,
                        partialVisibilityGutter: 40
                      },
                      mobile: {
                        breakpoint: {
                          max: 464,
                          min: 0
                        },
                        items: 1,
                        partialVisibilityGutter: 1
                      },
                      tablet: {
                        breakpoint: {
                          max: 1024,
                          min: 464
                        },
                        items: 2,
                        partialVisibilityGutter: 30
                      }
                    }}
                    rewind={false}
                    rewindWithAnimation={false}
                    rtl={false}
                    shouldResetAutoplay
                    showDots={false}
                    sliderClass=''
                    slidesToSlide={1}
                    swipeable
                  >
                    {servicios.map((data) => (
                      <div key={data.id} className='px-2 h-full flex'>
                        <Card
                          Titulo={data.nombre}
                          Imagen={data.imagenes}
                          Precio={data.precio}
                          id={data.id}
                          Calificacion={data.calificacion}
                          CantCalificaiones={data.calificaciones}
                          NombreUsuario={data.nombre_proveedor}
                          userId={data.idProveedor || userProfile.id}
                          showMenu
                        />
                      </div>
                    ))}
                  </Carousel>
                  )
                : (
                  <p className='text-center text-gray-500 py-8'>No hay servicios disponibles en este momento</p>
                  )}
            </div>
          )}
          {userProfile.esProveedor === false && user.id === userProfile.id && (
            <div className='bg-white p-6 rounded-lg shadow-md pb-10'>
              <h3 className='text-xl font-bold text-primary mt-6 mb-4'>Favoritos</h3>
              {favoritos && favoritos.length > 0
                ? (
                  <Carousel
                    additionalTransfrom={0}
                    arrows
                    autoPlay={false}
                    centerMode={false}
                    className=''
                    containerClass='container-with-dots'
                    dotListClass=''
                    draggable
                    focusOnSelect={false}
                    infinite
                    itemClass='h-full flex'
                    keyBoardControl
                    minimumTouchDrag={80}
                    pauseOnHover
                    renderArrowsWhenDisabled={false}
                    renderButtonGroupOutside={false}
                    renderDotsOutside={false}
                    responsive={{
                      desktop: {
                        breakpoint: {
                          max: 3000,
                          min: 1024
                        },
                        items: 4,
                        partialVisibilityGutter: 40
                      },
                      mobile: {
                        breakpoint: {
                          max: 464,
                          min: 0
                        },
                        items: 1,
                        partialVisibilityGutter: 1
                      },
                      tablet: {
                        breakpoint: {
                          max: 1024,
                          min: 464
                        },
                        items: 2,
                        partialVisibilityGutter: 30
                      }
                    }}
                    rewind={false}
                    rewindWithAnimation={false}
                    rtl={false}
                    shouldResetAutoplay
                    showDots={false}
                    sliderClass=''
                    slidesToSlide={1}
                    swipeable
                  >
                    {favoritos.map((data) => (
                      <div key={data.id} className='px-2 h-full flex'>
                        <Card
                          Titulo={data.nombre}
                          Imagen={data.imagenes}
                          Precio={data.precio}
                          id={data.id}
                          Calificacion={data.calificacion}
                          CantCalificaiones={data.calificaciones}
                          NombreUsuario={data.nombre_proveedor}
                          userId={data.idProveedor || userProfile.id}

                        />
                      </div>
                    ))}
                  </Carousel>
                  )
                : (
                  <p className='text-center text-gray-500 py-8'>No hay servicios favoritos en este momento</p>
                  )}
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

function Proveedor ({ user, edad }) {
  const { id } = useParams()
  const { user: myUser } = useContext(UsuarioContext)
  return (
    <>
      <div className='lg:col-span-2 bg-white p-6 sm:p-8 rounded-lg shadow-md md:w-1/2 h-full'>
        <div>
          <h3 className='text-xl font-bold text-primary mb-4'>Información Proveedor</h3>
          <div className='grid grid-cols-1 md:grid-cols-2 gap-6'>
            <div className=''>
              <label className='text-primary' htmlFor='full-name'>Edad</label>
              <h2>{edad}</h2>
            </div>
            <div>
              <label className='text-primary' htmlFor='email'>Experiencia (Años)</label>
              <h2>{user.experiencia}</h2>
            </div>
          </div>
          <div className='mt-6'>
            <label className='text-primary' htmlFor='about'>Descripción</label>
            <p className='mt-2 text-gray-700'>{user.descripcion}</p>
          </div>
          <div>
            <h3 className='text-xl font-bold text-primary mt-6 mb-4'>Habilidades</h3>
            <div className='flex flex-wrap gap-2'>
              {user.habilidades.map((habilidad, index) => (
                <span key={index} className='bg-primary text-white px-3 py-1 rounded-full text-sm'>{habilidad.habilidad}</span>
              ))}
            </div>
            <div className='mb-4'>
              <h3 className='text-xl font-bold text-primary mt-6 mb-4'>Títulos</h3>
              <div className='flex flex-wrap gap-2'>
                {user.titulos.map((titulo, index) => (
                  <span key={index} className='bg-primary text-white px-3 py-1 rounded-full text-sm'>{titulo.titulo}</span>
                ))}
              </div>
            </div>
          </div>
        </div>{
          myUser.registroMp == true
            ? (
              <div className='mt-6 bg-green-50 border-2 border-green-500 rounded-lg p-4 flex items-center gap-3'>
                <span className='material-icons text-green-500 text-3xl'>check_circle</span>
                <div>
                  <h4 className='font-semibold text-green-700'>Mercado Pago Conectado</h4>
                  <p className='text-sm text-green-600'>Tu cuenta está lista para recibir pagos</p>
                </div>
              </div>
              )
            : myUser.id == parseInt(id) ? <LoginMp /> : null
        }
      </div>
    </>
  )
}
