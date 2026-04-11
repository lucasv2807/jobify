import { Header } from '../components/Header.jsx'
import { Footer } from '../components/Footer.jsx'
import { SlideUp } from '../components/SlideUp.jsx'
import { Card } from '../components/Card.jsx'
import React, { useState, useContext, useEffect } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { ToastContainer } from 'react-toastify'
import Carousel from 'react-multi-carousel'
import 'react-multi-carousel/lib/styles.css'

export function Home () {
  const { url } = useContext(UsuarioContext)
  const [datos, setDatos] = useState([])

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${url}/servicios/getServicio`)
        const data = await response.json()

        // Verificar si la respuesta fue exitosa
        if (!response.ok || !data.success) {
          // Mostrar mensaje amigable si no hay servicios
          if (data.error === 'No se encontraron servicios') {
            setDatos([])
            return
          }
          console.error('Error al obtener servicios:', data?.message || data?.error)
          setDatos([])
          return
        }

        setDatos(Array.isArray(data.data) ? data.data : [])
      } catch (error) {
        console.error('Error al obtener servicios:', error)
        setDatos([])
      }
    }
    fetchData()
  }, [url])

  useEffect(() => {
    // Manejar el scroll a la sección cuando hay un hash en la URL
    const hash = window.location.hash
    if (hash) {
      setTimeout(() => {
        const element = document.querySelector(hash)
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }
      }, 100)
    }
  }, [])

  // Asegurarnos que 'datos' sea un array antes de usar flatMap
  const safeDatos = Array.isArray(datos) ? datos : []
  const conteo = safeDatos
    .flatMap(e => Array.isArray(e.categorias) ? e.categorias : [])
    .reduce((acc, cat) => {
      acc[cat] = (acc[cat] || 0) + 1
      return acc
    }, {})
  const mejoresCategorias = Object.entries(conteo)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 5)
    .map(([categoria]) => categoria)

  return (
    <>
      <Header />
      <main className='bg-claro'>
        <section className='relative pt-4'>
          <div className='container mx-auto px-4 sm:px-6 lg:px-10 flex flex-col lg:flex-row items-center lg:items-start justify-center lg:justify-around '>
            <div className='lg:w-1/2 text-center lg:text-left'>
              <h1 className='text-4xl md:text-6xl font-bold text-primary  leading-tight my-6'>
                El servicio que buscas en la palma de tu mano
              </h1>
              <p className='text-lg text-oscuro mb-8 max-w-xl mx-auto lg:mx-0'>
                Conectamos clientes con los mejores profesionales. Busca, encuentra y contrata. ¿Eres un profesional? Publica tus servicios y haz crecer tu negocio.
              </p>
            </div>
            <div className='lg:w-1/3 lg:ml-2 lg:mt-0'>
              <img alt='Trabajador con casco naranja mirando hacia arriba' className='w-full object-cover rounded-lg mt-10' src='https://www.captio.net/hubfs/Imatges/Organizacin_de_viajes_de_negocios_la_negociacin_con_proveedores.jpg' />
            </div>
          </div>
        </section>
        <section className='py-20' id='services'>
          <div className='container mx-auto px-4 sm:px-6 lg:px-8'>
            <h2 className='text-3xl font-bold text-center text-primary  mb-4'>Explora por Categoría</h2>
            <p className='text-center text-oscuro mb-12 max-w-2xl mx-auto'>Encuentra profesionales calificados en diversas áreas para resolver cualquier necesidad que tengas.</p>
          </div>
          <div className='flex flex-col gap-10 mb-20 px-10 w-full'>
            {
              mejoresCategorias.map((categoria, index) => {
                const servicios = datos.filter((serv) => serv.categorias.includes(categoria))
                return (
                  <article key={index + categoria} className='flex flex-col text-start gap-4 mb-5 bg-medio/40 p-6 rounded-xl'>
                    <h2 className='text-4xl text-white font-bold'>{categoria}</h2>
                    <Carousel
                      key={index + categoria}
                      additionalTransfrom={0}
                      arrows
                      autoPlaySpeed={2000}
                      autoPlay
                      centerMode
                      className=''
                      containerClass='container-with-dots'
                      dotListClass=''
                      draggable
                      focusOnSelect={false}
                      infinite
                      itemClass=''
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

                      {
                        servicios.map((data) => {
                          return (
                            <div key={data.id} className='px-2'>
                              <Card
                                Titulo={data.nombre}
                                Imagen={data.imagen}
                                Precio={data.precio}
                                id={data.id}
                                Calificacion={data.calificacion}
                                CantCalificaiones={data.calificaciones}
                                NombreUsuario={data.nombre_proveedor}
                              />
                            </div>
                          )
                        })
                      }
                    </Carousel>
                  </article>
                )
              })
            }
          </div>
        </section>
        <section className='py-20 bg-claro  ' id='comoFunciona'>
          <div className='container mx-auto px-4 sm:px-6 lg:px-8'>
            <h2 className='text-3xl font-bold text-center text-primary  mb-4'>¿Cómo funciona?</h2>
            <p className='text-center text-oscuro mb-12 max-w-2xl mx-auto'>Encuentra y contrata al profesional ideal en tres sencillos pasos.</p>
            <div className='grid md:grid-cols-3 gap-8 text-center'>
              <div className='p-6'>
                <div className='flex items-center justify-center h-16 w-16 bg-primary/10 text-primary rounded-full mx-auto mb-4'>
                  <span className='material-icons text-3xl'>search</span>
                </div>
                <h3 className='text-xl font-semibold text-oscuro  mb-2'>1. Busca el servicio</h3>
                <p className='text-medio'>Utiliza nuestro buscador o navega por las categorías para encontrar lo que necesitas.</p>
              </div>
              <div className='p-6'>
                <div className='flex items-center justify-center h-16 w-16 bg-primary/10 text-primary rounded-full mx-auto mb-4'>
                  <span className='material-icons text-3xl'>compare_arrows</span>
                </div>
                <h3 className='text-xl font-semibold text-oscuro  mb-2'>2. Compara y elige</h3>
                <p className='text-medio'>Revisa perfiles, calificaciones y precios para tomar la mejor decisión.</p>
              </div>
              <div className='p-6'>
                <div className='flex items-center justify-center h-16 w-16 bg-primary/10 text-primary rounded-full mx-auto mb-4'>
                  <span className='material-icons text-3xl'>handshake</span>
                </div>
                <h3 className='text-xl font-semibold text-oscuro  mb-2'>3. Contrata y califica</h3>
                <p className='text-medio '>Contacta al profesional, acuerda el trabajo y no olvides calificar su servicio.</p>
              </div>
            </div>
          </div>
        </section>
      </main>
      <SlideUp />
      <Footer />
      <ToastContainer
        position='bottom-right'
      />
    </>
  )
}
