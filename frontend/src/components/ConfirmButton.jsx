import { toast } from 'react-toastify'

export function ConfirmButton ({ url, idCliente, idServicio, idReserva, handleCancelar }) {
  const onConfirm = async () => {
    try {
      const data = new FormData()
      data.append('idCliente', idCliente)
      data.append('idServicio', idServicio)
      data.append('idReserva', idReserva)
      data.append('confirmado', 1)
      const response = await fetch(`${url}/reserva/confirmar`, {
        method: 'POST',
        body: data
      })
      console.log(response)
      if (response.ok) {
        toast.success('¡Reserva confirmada exitosamente!')
        setTimeout(() => {
          window.location.reload()
        }, 1500)
      } else {
        toast.error('Error al confirmar la reserva. Por favor, intenta de nuevo.')
      }
    } catch (error) {
      console.error('Error al confirmar:', error)
      toast.error('Error al confirmar la reserva. Por favor, intenta de nuevo.')
    }
  }

  const onRechazar = async () => {
    try {
      await handleCancelar()
      toast.success('Reserva rechazada')
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    } catch (error) {
      console.error('Error al rechazar:', error)
    }
  }

  return (
    <div className='items-center flex flex-col min-w-full'>
      <div className='bg-yellow-100 min-w-full border-2 border-yellow-500 rounded-xl p-4 flex items-center gap-3 mb-4'>
        <span className='material-icons text-yellow-600 text-3xl'>schedule</span>
        <div>
          <p className='font-bold text-yellow-800'>Reserva Pendiente</p>
          <p className='text-sm text-yellow-700'>Esta reserva está esperando tu confirmación</p>
        </div>
      </div>
      
      <div className='flex gap-4 w-full max-w-md'>
        <button
          onClick={onRechazar}
          className='flex-1 px-6 py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-all duration-200 active:scale-95 shadow-lg flex items-center justify-center gap-2'
        >
          <span className='material-icons'>close</span>
          Rechazar
        </button>
        <button
          onClick={onConfirm}
          className='flex-1 px-6 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-green-600 transition-all duration-200 active:scale-95 shadow-lg flex items-center justify-center gap-2'
        >
          <span className='material-icons'>check</span>
          Confirmar
        </button>
      </div>
    </div>
  )
}
