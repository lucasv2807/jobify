import { Link } from 'react-router-dom'
export function Reseña ({ calificacion, comentario, imagen, nombreCliente, imagenCliente, idCliente }) {
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
  return (
    <div className='flex items-start'>
      <Link to={`/perfil/${idCliente}`} className='mr-4'>
        <img alt={`Foto de ${nombreCliente}`} className='size-12 rounded-full' src={imagenCliente} />
      </Link>
      <div>
        <div className='flex items-center mb-1'>
          <h4 className='font-semibold text-gray-800 mr-2'>{nombreCliente}</h4>
          <div className='flex items-center'>
            <div className='flex items-center'>
              {Estrellas(calificacion).map((estrella, index) => (
                <span key={index} className='material-icons text-yellow-400 text-sm'>{estrella}</span>
              ))}
            </div>
            <span className='ml-2 text-gray-600'>{calificacion}</span>
          </div>
        </div>
        <p className='text-gray-600'>{comentario}</p>
        {imagen ? <img alt='Imagen de la reseña' className='w-full h-auto object-cover rounded-lg mt-2' src={imagen} /> : null}
      </div>
    </div>
  )
}
