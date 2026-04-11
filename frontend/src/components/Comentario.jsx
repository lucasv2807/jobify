import Box from '@mui/material/Box'
import Rating from '@mui/material/Rating'
import { useState, useContext, useRef, useEffect } from 'react'
import { UsuarioContext } from '../context/Usuario'
import { useParams } from 'react-router-dom'
import { toast } from 'react-toastify'

export function Comentario () {
  const { url, user } = useContext(UsuarioContext)
  const { id } = useParams()
  const [comentario, setComentario] = useState('')
  const [imagen, setImagen] = useState(null)
  const [imagenPreview, setImagenPreview] = useState(null)
  const [servicioContratado, setServicioContratado] = useState(false)
  const [cargandoValidacion, setCargandoValidacion] = useState(true)

  // Verificar si el usuario ha contratado este servicio Y está confirmado
  useEffect(() => {
    const verificarContratacion = async () => {
      try {
        const response = await fetch(`${url}/reserva/getReservaCliente?id=${user.id}`)
        const data = await response.json()

        if (response.ok && Array.isArray(data.data)) {
          // Buscar si existe una reserva confirmada (confirmado === 1) para este servicio específico
          const reservaConfirmada = data.data.find(
            reserva => reserva.idServicio === parseInt(id) && reserva.confirmado === 1
          )
          
          setServicioContratado(!!reservaConfirmada)
        } else {
          setServicioContratado(false)
        }
      } catch (error) {
        console.error('Error al verificar contratación:', error)
        setServicioContratado(false)
      } finally {
        setCargandoValidacion(false)
      }
    }

    if (user.id && id) {
      verificarContratacion()
    }
  }, [url, user.id, id])

  const changeComentario = (e) => {
    setComentario(e.target.value)
  }

  const changeImagen = (e) => {
    const file = e.target.files[0]
    if (file) {
      setImagen(file)
      // Crear URL de previsualización
      const previewUrl = URL.createObjectURL(file)
      setImagenPreview(previewUrl)
    }
  }

  const eliminarImagen = () => {
    setImagen(null)
    if (imagenPreview) {
      URL.revokeObjectURL(imagenPreview)
      setImagenPreview(null)
    }
    // Limpiar el input file
    if (fileInputRef.current) {
      fileInputRef.current.value = ''
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    // Validar que el servicio haya sido contratado
    if (!servicioContratado) {
      toast.error('Debes contratar este servicio antes de dejar una reseña')
      return
    }

    // Validar que haya comentario
    if (!comentario.trim()) {
      toast.error('Por favor escribe un comentario')
      return
    }

    const formData = new FormData()
    formData.append('idCliente', user.id)
    formData.append('idServicio', id)
    formData.append('calificacion', value)
    formData.append('comentario', comentario)
    if (imagen) {
      formData.append('imagen', imagen)
    }

    try {
      const response = await fetch(`${url}/review/subirReview`, {
        method: 'POST',
        body: formData
      })

      if (response.ok) {
        toast.success('¡Reseña publicada exitosamente!')
        setComentario('')
        eliminarImagen()
        setValue(2)
        // Recargar la página para mostrar la nueva reseña
        setTimeout(() => {
          window.location.reload()
        }, 1500)
      } else {
        const data = await response.json()
        toast.error(data.message || 'Error al publicar la reseña')
      }
    } catch (error) {
      console.error('Error:', error)
      toast.error('Error al publicar la reseña')
    }
  }
  const [value, setValue] = useState(2)
  const fileInputRef = useRef(null)

  // Limpiar URL de previsualización al desmontar el componente
  useEffect(() => {
    return () => {
      if (imagenPreview) {
        URL.revokeObjectURL(imagenPreview)
      }
    }
  }, [imagenPreview])

  // Si está cargando la validación, mostrar mensaje
  if (cargandoValidacion) {
    return (
      <div className='bg-white w-full p-6 rounded-lg text-center'>
        <p className='text-gray-500'>Verificando permisos...</p>
      </div>
    )
  }

  // Si el servicio no ha sido contratado o no está confirmado, mostrar mensaje informativo
  if (!servicioContratado) {
    return (
      <div className='bg-yellow-50 border-2 border-yellow-400 w-full p-6 rounded-lg mb-4'>
        <div className='flex items-center gap-3'>
          <span className='material-icons text-yellow-600 text-3xl'>info</span>
          <div>
            <h4 className='font-semibold text-yellow-800'>Contrata este servicio para dejar una reseña</h4>
            <p className='text-sm text-yellow-700'>Solo los clientes que han contratado este servicio pueden publicar opiniones</p>
          </div>
        </div>
      </div>
    )
  }

  return (
    <form className='bg-white w-full p-6 rounded-lg' onSubmit={handleSubmit}>
      <div className='mb-1'>
        <textarea
          rows='2'
          placeholder='Ingresa tu opinion sobre el servicio aqui...'
          id='content'
          value={comentario}
          onChange={changeComentario}
          className='shadow resize-none appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
        />
      </div>

      {/* Previsualización de la imagen */}
      {imagenPreview && (
        <div className='mb-3 relative inline-block'>
          <img
            src={imagenPreview}
            alt='Vista previa'
            className='max-w-xs max-h-48 rounded-lg border-2 border-primary object-cover'
          />
          <button
            type='button'
            onClick={eliminarImagen}
            className='absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold transition-colors shadow-lg'
            aria-label='Eliminar imagen'
          >
            ×
          </button>
        </div>
      )}

      <div className='flex items-center justify-between'>
        <button
          type='submit'
          className='bg-primary text-sm hover:bg-green-600 text-white font-bold py-2 px-4 rounded-full focus:outline-none focus:shadow-outline transition ease-in-out duration-150'
        >
          Post
        </button>
        <div className='flex items-center justify-between gap-3'>
          <Box sx={{ '& > legend': { mt: 2 } }}>
            <Rating
              name='simple-controlled'
              value={value}
              onChange={(event, newValue) => {
                setValue(newValue)
              }}
            />
          </Box>
          <input
            type='file'
            onChange={changeImagen}
            ref={fileInputRef}
            style={{ display: 'none' }}
          />
          <svg
            xmlns='http://www.w3.org/2000/svg'
            viewBox='0 0 24 24'
            className='h-6 w-6 fill-current text-oscuro mr-2 hover:scale-110 transition-transform duration-200 cursor-pointer'
            onClick={() => fileInputRef.current && fileInputRef.current.click()}
            role='button'
            aria-label='Adjuntar imagen'
          >
            <path
              fill='#0F0F0F'
              d='M23 4C23 2.34315 21.6569 1 20 1H4C2.34315 1 1 2.34315 1 4V20C1 21.6569 2.34315 23 4 23H20C21.6569 23 23 21.6569 23 20V4ZM21 4C21 3.44772 20.5523 3 20 3H4C3.44772 3 3 3.44772 3 4V20C3 20.5523 3.44772 21 4 21H20C20.5523 21 21 20.5523 21 20V4Z'
              clipRule='evenodd'
              fillRule='evenodd'
            />
            <path
              fill='#0F0F0F'
              d='M4.80665 17.5211L9.1221 9.60947C9.50112 8.91461 10.4989 8.91461 10.8779 9.60947L14.0465 15.4186L15.1318 13.5194C15.5157 12.8476 16.4843 12.8476 16.8682 13.5194L19.1451 17.5039C19.526 18.1705 19.0446 19 18.2768 19H5.68454C4.92548 19 4.44317 18.1875 4.80665 17.5211Z'
            />
            <path
              fill='#0F0F0F'
              d='M18 8C18 9.10457 17.1046 10 16 10C14.8954 10 14 9.10457 14 8C14 6.89543 14.8954 6 16 6C17.1046 6 18 6.89543 18 8Z'
            />
          </svg>
        </div>
      </div>
    </form>
  )
}
