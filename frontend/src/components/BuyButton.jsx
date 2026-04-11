import { useState, useContext } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { useParams, useNavigate } from 'react-router'
import { toast } from 'react-toastify'
export function BuyButton ({ idProveedor, payment }) {
  const { url, user } = useContext(UsuarioContext)
  const { id } = useParams()
  const navigate = useNavigate()
  const [fecha, setFecha] = useState('')
  const changeFecha = (e) => {
    setFecha(e.target.value)
  }
  const handleClick = () => {
    const formData = new FormData()
    formData.append('idCliente', user.id)
    formData.append('idServicio', id)
    formData.append('idProveedor', idProveedor)
    formData.append('fecha', fecha)
    payment()
    fetch(`${url}/reserva/crear`, {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          navigate(`/chat/${user.id}-${id}`)
        } else {
          toast.error('Error al contratar el servicio: ' + data.message)
        }
      })
  }
  return (
    <>
      <input className='bg-claro text-oscuro border-1 rounded-full p-2' type='date' value={fecha} onChange={changeFecha} required />
      <button
        type='submit'
        className='bg-oscuro hover:bg-medio text-white font-bold rounded-3xl w-30 h-12 border transition ease-in-out duration-150'
        onClick={handleClick}
      >
        Solicitar
      </button>
    </>
  )
}
