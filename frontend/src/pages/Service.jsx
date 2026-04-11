import { useParams, Link } from 'react-router-dom'
import { Header } from '../components/Header.jsx'
import { Reseña } from '../components/Reseña.jsx'
import { SlideUp } from '../components/SlideUp'
import { useEffect, useContext, useState } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { Comentario } from '../components/Comentario.jsx'
import { WalletComponent } from '../components/Wallet.jsx'
import { CarouselP } from '../components/Carousel.jsx'
import { ToastContainer, toast } from 'react-toastify'
import { Footer } from '../components/Footer.jsx'
import { Tag } from '../components/Tag'

export function Service () {
  const { id } = useParams()
  const { url, user } = useContext(UsuarioContext)
  const [dato, setDato] = useState({})
  const [review, setReview] = useState([])
  const [toShowReviews, setToShowReviews] = useState(3)
  const [mp, setMp] = useState({})
  const [noTengoGanasDePensar, setNoTengoGanasDePensar] = useState(0)

  useEffect(() => {
    fetch(`${url}/servicios/getServicio?id=${id}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`)
        }
        return res.json()
      })
      .then(data => setDato(data.data))
      .catch(err => {
        console.error('Error al cargar servicio:', err)
      })
  }, [url, id])

  const { nombre, descripcion, imagenes, precio, categorias, diasDisponibles, nombreProveedor, apellidoProveedor, imagenProveedor, idProveedor } = dato

  useEffect(() => {
    fetch(`${url}/review/getReview?idServicio=${id}`)
      .then(res => res.json())
      .then(data => setReview(data.data))
  }, [url, id])

  useEffect(() => {
  // esperar a que nombre y precio estén definidos
    if (!dato?.nombre || dato?.precio == null || idProveedor == null) {
      return
    }

    const titulo = encodeURIComponent(dato.nombre)
    const precioQuery = encodeURIComponent(dato.precio)

    fetch(`${url}/pago/mp?titulo=${titulo}&precio=${precioQuery}&idProveedor=${idProveedor}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`)
        }
        return res.json()
      })
      .then(data => {
        if (data.success && data.data?.preferenciaMP) {
          const { preference_id, public_key } = data.data.preferenciaMP

          setMp({ preference_id, public_key })
        } else {
          console.error('❌ Error en respuesta:', data.message)
        }
      })
      .catch(err => {
        console.error('❌ Error al obtener preferencia:', err)
      })
  }, [url, dato.nombre, dato.precio, idProveedor])

  const [isFavorite, setIsFavorite] = useState(false)
  useEffect(() => {
    // Verificar si el servicio es favorito
    const checkFavorite = async () => {
      const response = await fetch(`${url}/servicios/getFavoritos?idCliente=${user.id}`)
      const data = await response.json()
      if (!response.ok) {
        if (data.message === 'El usuario no es un cliente') {
          return
        }
      }
      const servicios = data.data.response
      if (Array.isArray(servicios)) {
        setIsFavorite(servicios.some(servicio => servicio.id === parseInt(id)))
      }
    }
    checkFavorite()
  }, [url, id, user.id, noTengoGanasDePensar])

  const calificacionPromedio = review.length > 0 ? (review.reduce((sum, r) => sum + r.calificacion, 0) / review.length) : 0
  const Estrellas = (numero) => {
    // tres posibles estreellas, star, star_half, star_border
    return Array.from({ length: 5 }).map((_, i) => {
      if (i < Math.floor(numero)) {
        return 'star '
      } else if (i < numero) {
        return 'star_half '
      } else {
        return 'star_border '
      }
    })
  }
  const mostrarMasOpiniones = () => {
    setToShowReviews(prev => prev + 3)
  }

  const handleSetFavorite = async (isFavorite) => {
    // Lógica para agregar o eliminar de favoritos
    const Data = new FormData()
    Data.append('idCliente', user.id)
    Data.append('idServicio', id)
    try {
      const response = await fetch(`${url}/servicios/${isFavorite ? 'eliminarFavorito' : 'saveFavorito'}`, {
        method: 'POST',
        body: Data
      })
      await response.json()
      if (response.ok) {
        // Toggle the favorite state based on the action
        setIsFavorite(!isFavorite)
        toast.success(isFavorite ? 'Servicio eliminado de favoritos' : 'Servicio guardado en favoritos')
      } else {
        console.error('Error al actualizar favorito')
        toast.error('No se pudo actualizar el favorito. Por favor, intenta de nuevo.')
      }
    } catch (e) {
      console.error('Error al actualizar favorito:', e)
      toast.error('No se pudo actualizar el favorito. Por favor, intenta de nuevo.')
    }
    setNoTengoGanasDePensar(prev => prev + 1)
  }

  return (
    <>
      <Header />
      <main className='mx-auto px-4 sm:px-6 lg:px-8 py-12 bg-claro'>
        <div className='grid grid-cols-1 lg:grid-cols-3 gap-12'>
          <div className='lg:col-span-2'>
            <div className='bg-white rounded-lg shadow-md p-6'>
              <Link to={`/perfil/${idProveedor}`} className='flex items-center w-fit mb-6'>
                <img alt={`Foto de perfil de ${nombreProveedor}`} className='size-16 rounded-full mr-4 object-cover' src={imagenProveedor} />
                <div>
                  <h2 className='text-2xl font-bold text-gray-900'>{nombreProveedor} {apellidoProveedor}</h2>
                </div>
              </Link>
              <div>
                <h1 className='text-4xl font-bold text-gray-900 mb-4'>{nombre}</h1>
                <div className='flex items-center text-gray-500 mb-6'>
                  <span className='material-icons mr-2'>location_on</span>
                  {dato.ubicaciones ? dato.ubicaciones.join(', ') : 'Ubicación no disponible'}
                </div>
                <CarouselP images={imagenes} />
                <div className='prose max-w-none'>
                  <h3 className='font-semibold text-xl text-gray-800'>Descripción</h3>
                  <p>
                    {descripcion}
                  </p>
                </div>
              </div>
            </div>
            <div className='bg-white rounded-lg shadow-md p-6 mt-8'>
              <Comentario />
              <h3 className='text-2xl font-bold text-gray-900 mb-6'>Opiniones de Clientes</h3>
              <div className='space-y-6'>
                {review && review.length > 0
                  ? review.slice(0, toShowReviews).map((rev, index) =>
                    <Reseña key={index} calificacion={rev.calificacion} comentario={rev.comentario} imagen={rev.imagen} nombreCliente={rev.nombreCliente} apellidoCliente={rev.apellidoCliente} imagenCliente={rev.imagenCliente} idCliente={rev.idCliente} />
                  )
                  : <p className='text-gray-600'>No hay reseñas disponibles para este servicio.</p>}
                <div className='text-center pt-4'>
                  {
                    toShowReviews < review.length
                      ? (
                        <button
                          onClick={mostrarMasOpiniones}
                          className='bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center mx-auto'
                        >
                          Mostrar más opiniones
                        </button>
                        )
                      : <p className='text-gray-600'>No hay más opiniones para mostrar.</p>
                  }
                </div>
              </div>
            </div>
          </div>
          <div className='lg:col-span-1 space-y-8'>
            <div className='bg-white rounded-lg shadow-md p-6 top-28'>
              <h3 className='text-2xl font-bold text-gray-900 mb-4'>Precio: ${precio}</h3>
              <div className='space-y-4'>
                <div className='flex justify-between items-center'>
                  <div className='flex items-center'>
                    {Estrellas(calificacionPromedio).map((estrella, index) => (
                      <span key={index} className='material-icons text-yellow-400 text-sm'>{estrella}</span>
                    ))} {calificacionPromedio === 0 ? 'N/A' : calificacionPromedio.toFixed(1)}
                  </div>
                  <span className='font-semibold text-gray-800'>{review.length} reseñas</span>
                </div>
                <span className='text-gray-600'>Categorias</span>
                <div className='flex flex-wrap justify-start items-start gap-2'>
                  {
                    categorias && categorias.map((cat, index) => (
                      <Tag Categoria={cat} key={index} />
                    ))
                  }
                </div>
                <span className='text-gray-600'>Dias Disponibles</span>
                <div className='flex flex-wrap justify-start items-start gap-2'>
                  {
                    diasDisponibles && diasDisponibles.map((dia, index) => (
                      <Tag Categoria={dia} key={index} />
                    ))
                  }
                </div>
              </div>
              <p className='text-sm text-gray-500 mt-4'>* Los precios no incluyen el costo de los materiales.</p>
              <div className='w-full'>
                {
                user.id == null
                  ? (
                    <div className='bg-red-50 border-2 border-red-500 w-full p-6 rounded-lg my-4 text-center'>
                      <span className='material-icons text-red-600 text-3xl'>warning</span>
                      <div className='flex items-center gap-3'>
                        <div>
                          <h4 className='font-semibold text-red-800'>Inicia sesion para poder contratar el servicio</h4>
                        </div>
                      </div>
                    </div>
                    )
                  : (
                      user.esProveedor == true
                        ? (
                          <div className='bg-red-50 border-2 border-red-500 w-full p-6 rounded-lg my-4 text-center'>
                            <span className='material-icons text-red-600 text-3xl'>warning</span>
                            <div className='flex items-center gap-3'>
                              <div>
                                <h4 className='font-semibold text-red-800'>Solo los clientes pueden contratar servicios</h4>
                              </div>
                            </div>
                          </div>
                          )
                        : (
                          <>
                            <WalletComponent
                              preferenceId={mp.preference_id}
                              nombreProveedor={nombreProveedor}
                              publicKey={mp.public_key}
                              idServicio={id}
                              idProveedor={idProveedor}
                              precio={precio}
                              titulo={nombre}
                            />
                            <button
                              className={`mt-3 w-full ${isFavorite ? 'bg-red-500 text-white' : 'bg-primary/10 text-primary'} font-bold py-3 px-6 rounded-lg ${isFavorite ? 'hover:bg-red-600' : 'hover:bg-primary/20'} transition-colors flex items-center justify-center`}
                              onClick={() => handleSetFavorite(isFavorite)}
                            >
                              <span className='material-icons mr-2'>{isFavorite ? 'bookmark' : 'bookmark_border'}</span>
                              {!isFavorite ? 'Guardar Favorito' : 'Eliminar Favorito'}
                            </button>
                          </>
                          ))
            }
              </div>
            </div>
          </div>
        </div>
      </main>
      <Footer />
      <ToastContainer />
      <SlideUp />
    </>
  )
}
