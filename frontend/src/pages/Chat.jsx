import { ToastContainer, toast } from 'react-toastify'
import { Header } from '../components/Header.jsx'
import { useEffect, useState, useContext } from 'react'
import { UsuarioContext } from '../context/Usuario.jsx'
import { useParams } from 'react-router-dom'

export function Chat () {
  const [message, setMessage] = useState([])
  const [chatInfo, setChatInfo] = useState(null)
  const { url, user } = useContext(UsuarioContext)
  const { chatId } = useParams()
  const idServicio = chatId ? chatId.split('-')[1] : null
  const idCliente = chatId ? chatId.split('-')[0] : null
  const idProveedor = chatId ? chatId.split('-')[2] : null
  const URL = `${url}/chat/history?idCliente=${idCliente}&idServicio=${idServicio}&idProveedor=${idProveedor}`
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(URL)
        const data = await response.json()
        if (!response.ok) {
          console.error('Error al cargar chat:', data.message)
        }
        setMessage(data.data.messages || [])
        setChatInfo(data.data.chat)
      } catch (error) {
        console.error('Error al cargar chat:', error.message)
      }
    }
    fetchData()
    const interval = setInterval(fetchData, 5000)
    return () => clearInterval(interval)
  }, [URL, idCliente, idServicio, user.id])
  const [inputValue, setInputValue] = useState('')
  const handleInputChange = (e) => {
    setInputValue(e.target.value)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    const data = new FormData()
    data.append('idCliente', idCliente)
    data.append('idServicio', idServicio)
    data.append('mensaje', inputValue)
    data.append('idEmisor', user.id)
    setInputValue('')
    const response = await fetch(`${url}/chat/send`, {
      method: 'POST',
      body: data
    })
    const Data = await response.json()
    if (!response.ok) {
      console.error('Error al enviar mensaje:', Data)
      toast.error('No se pudo enviar el mensaje. Por favor, intenta de nuevo.')
    }
  }
  return (
    <div className='h-screen flex flex-col overflow-hidden'>
      <Header />
      <main className='flex-1 flex flex-col bg-claro overflow-hidden'>
        <div className='container mx-auto px-4 py-4 max-w-4xl flex-1 flex flex-col overflow-hidden'>
          {/* Aviso de estado de confirmación */}
          {chatInfo && (
            <div className={`mb-4 rounded-xl p-4 shadow-lg border-2 ${
              chatInfo.confirmado === 1 || chatInfo.confirmado === true
                ? 'bg-green-50 border-green-500'
                : 'bg-yellow-50 border-yellow-500'
            }`}
            >
              <div className='flex items-center gap-3'>
                <span className={`material-icons text-3xl ${
                  chatInfo.confirmado === 1 || chatInfo.confirmado === true
                    ? 'text-green-600'
                    : 'text-yellow-600'
                }`}
                >
                  {chatInfo.confirmado === 1 || chatInfo.confirmado === true ? 'check_circle' : 'schedule'}
                </span>
                <div className='flex-1'>
                  <h4 className={`font-bold text-lg ${
                    chatInfo.confirmado === 1 || chatInfo.confirmado === true
                      ? 'text-green-800'
                      : 'text-yellow-800'
                  }`}
                  >
                    {chatInfo.confirmado === 1 || chatInfo.confirmado === true
                      ? 'Reserva Confirmada ✓'
                      : 'Reserva Pendiente de Confirmación'}
                  </h4>
                  <p className={`text-sm ${
                    chatInfo.confirmado === 1 || chatInfo.confirmado === true
                      ? 'text-green-700'
                      : 'text-yellow-700'
                  }`}
                  >
                    {chatInfo.confirmado === 1 || chatInfo.confirmado === true
                      ? (user.esProveedor
                          ? 'Has confirmado esta reserva. Coordina los detalles del servicio con tu cliente.'
                          : 'El proveedor ha confirmado tu reserva. Puedes coordinar los detalles del servicio.')
                      : (user.esProveedor
                          ? 'Esta reserva está pendiente. Ve a la página de reservas para confirmarla o rechazarla.'
                          : 'El proveedor aún no ha confirmado tu reserva. Te notificaremos cuando lo haga.')}
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* Área de mensajes */}
          <div className='bg-white rounded-2xl shadow-lg border border-gray-100 flex flex-col flex-1 overflow-hidden'>
            <div className='flex-1 overflow-y-auto p-6 bg-gradient-to-b from-gray-50 to-white'>
              {message && message.length > 0 && chatInfo
                ? (
                  <div className='space-y-4'>
                    {message.map((msg) => {
                      const isOwner = msg.idEmisor !== user.id
                      const isCliente = msg.idEmisor === chatInfo.idCliente
                      const imagenEmisor = isCliente ? chatInfo.imagenCliente : chatInfo.imagenProveedor
                      const nombreEmisor = isCliente
                        ? `${chatInfo.nombreCliente} ${chatInfo.apellidoCliente}`
                        : `${chatInfo.nombreProveedor} ${chatInfo.apellidoProveedor}`
                      return (
                        <Mensaje
                          key={msg.idMensaje}
                          msg={msg.mensaje}
                          owner={isOwner}
                          imagenEmisor={imagenEmisor}
                          nombreEmisor={nombreEmisor}
                        />
                      )
                    })}
                  </div>
                  )
                : (
                  <div className='h-full flex flex-col items-center justify-center text-gray-400'>
                    <span className='material-icons text-6xl mb-4'>chat_bubble_outline</span>
                    <p className='text-lg font-medium'>No hay mensajes aún</p>
                    <p className='text-sm'>Inicia la conversación</p>
                  </div>
                  )}
            </div>

            {/* Input área */}
            <div className='border-t border-gray-200 bg-white p-4'>
              <form onSubmit={handleSubmit} className='flex items-center gap-3'>
                <div className='flex-1 relative'>
                  <input
                    type='text'
                    placeholder='Escribe un mensaje...'
                    className='w-full pl-4 pr-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary focus:bg-white transition-all text-sm placeholder-gray-400'
                    value={inputValue}
                    onChange={handleInputChange}
                  />
                </div>
                <button
                  type='submit'
                  disabled={!inputValue.trim()}
                  className='bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-600 transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2'
                >
                  <span className='material-icons text-xl'>send</span>
                  Enviar
                </button>
              </form>
            </div>
          </div>
        </div>
      </main>
      <ToastContainer position='bottom-right' />
    </div>
  )
}

function Mensaje ({ msg, owner, imagenEmisor, nombreEmisor }) {
  return (
    <div className={`flex ${owner ? '' : 'justify-end'}`}>
      <div className={`flex gap-2 max-w-[85%] md:max-w-md ${owner ? '' : 'flex-row-reverse'}`}>
        <div className='flex-shrink-0 mt-auto'>
          {imagenEmisor
            ? (
              <img
                src={imagenEmisor}
                alt='Avatar'
                className='w-8 h-8 rounded-full object-cover border-2 border-gray-200'
              />
              )
            : (
              <div
                className={`w-8 h-8 rounded-full flex items-center justify-center ${
              owner ? 'bg-primary/10' : 'bg-blue-100'
            }`}
              >
                <span
                  className={`material-icons text-sm ${
                owner ? 'text-primary' : 'text-blue-600'
              }`}
                >
                  {owner ? 'person' : 'account_circle'}
                </span>
              </div>
              )}
        </div>
        <div className='flex flex-col gap-1 min-w-0 flex-1'>
          {owner && (
            <span className='text-xs font-medium text-gray-600 truncate'>
              {nombreEmisor}
            </span>
          )}
          <div
            className={`px-4 py-3 rounded-2xl shadow-sm ${
            owner
              ? 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
              : 'bg-primary text-white rounded-tr-none'
          }`}
          >
            <p className='text-sm leading-relaxed break-words overflow-wrap-anywhere'>
              {msg}
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
