import { Link } from 'react-router'
export function Notificacion ({ notificacion }) {
  return (
    <div className='w-80 h-40 bg-white rounded-lg shadow-md p-4 flex flex-col justify-between'>
      <Link to={`/Chat/${notificacion.idCliente}-${notificacion.idServicio}-${notificacion.idProveedor}`}>
        <div>
          <span className='flex items-center gap-3 mb-2'>
            <img src={notificacion.imagen} className='w-12 h-12 rounded-full' alt='' />
            <h2 className='font-bold'>{notificacion.nombre} {notificacion.apellido}</h2>
          </span>
        </div>
      </Link>
      <p><Link to={`/Buy/${notificacion.idServicio}`}>Servicio: {notificacion.servicioTitulo}</Link></p>
    </div>
  )
}
