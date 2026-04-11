import { useState, useContext } from 'react'
import { UsuarioContext } from '../context/Usuario'
import { toast } from 'react-toastify'

export function ModalIA () {
  const [input, setInput] = useState('')
  const [messages, setMessages] = useState([])
  const [loading, setLoading] = useState(false)
  const { url } = useContext(UsuarioContext)

  const handleInputChange = (e) => {
    setInput(e.target.value)
  }

  const handleSearchSubmit = async (e) => {
    e?.preventDefault()
    if (!input.trim() || loading) return

    // Agregar mensaje del usuario
    const userMessage = { rol: 'user', text: input }
    setMessages(prev => [...prev, userMessage])
    setInput('')
    setLoading(true)

    try {
      const response = await fetch(`${url}/servicios/IA?pregunta=${encodeURIComponent(input)}`)
      const json = await response.json()

      if (json.success && json.data && json.data.response) {
        // Extraer el contenido de la respuesta de la IA
        const aiMessage = {
          rol: 'assistant',
          text: json.data.response.content || 'Respuesta recibida'
        }
        setMessages(prev => [...prev, aiMessage])
      } else {
        toast.error('Error al obtener respuesta de IA')
        // Agregar mensaje de error
        setMessages(prev => [...prev, {
          rol: 'assistant',
          text: '❌ Lo siento, hubo un error al procesar tu solicitud. Por favor intenta de nuevo.'
        }])
      }
    } catch (error) {
      console.error('Error:', error)
      toast.error('Error de conexión con el servidor')
      setMessages(prev => [...prev, {
        rol: 'assistant',
        text: '❌ Error de conexión. Por favor verifica tu conexión a internet.'
      }])
    } finally {
      setLoading(false)
    }
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      handleSearchSubmit()
    }
  }

  return (
    <div className='absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 animate-fade-slide'>
      <div className='bg-gradient-to-r from-primary to-green-500 px-5 py-4'>
        <div className='flex items-center gap-2'>
          <span className='material-icons text-white text-2xl'>auto_awesome</span>
          <h3 className='text-lg font-bold text-white'>Búsqueda Asistida por IA</h3>
        </div>
      </div>

      <div className='p-5'>
        {/* Área de mensajes */}
        <div className='max-h-80 overflow-y-auto mb-4 space-y-3'>
          {/* Mensaje de bienvenida inicial */}
          {messages.length === 0 && (
            <div className='flex gap-3'>
              <div className='flex-shrink-0'>
                <div className='w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center'>
                  <span className='material-icons text-primary text-xl'>support_agent</span>
                </div>
              </div>
              <div className='flex-1'>
                <p className='text-sm text-gray-700 leading-relaxed'>
                  👋 ¡Hola! Soy tu asistente virtual. Estoy aquí para ayudarte a encontrar el servicio perfecto.
                  <span className='block mt-2 text-gray-600'>
                    Describe lo que necesitas y te ayudaré a encontrarlo.
                  </span>
                </p>
              </div>
            </div>
          )}

          {/* Historial de mensajes */}
          {messages.map((message, index) => (
            <Mensaje key={index} rol={message.rol} text={message.text} />
          ))}

          {/* Loading indicator */}
          {loading && (
            <div className='flex gap-3'>
              <div className='flex-shrink-0'>
                <div className='w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center'>
                  <span className='material-icons text-primary text-lg animate-spin'>autorenew</span>
                </div>
              </div>
              <div className='flex-1'>
                <div className='inline-block px-4 py-2 rounded-2xl rounded-tl-none bg-gray-100'>
                  <div className='flex gap-1'>
                    <span className='w-2 h-2 bg-gray-400 rounded-full animate-bounce' style={{ animationDelay: '0ms' }} />
                    <span className='w-2 h-2 bg-gray-400 rounded-full animate-bounce' style={{ animationDelay: '150ms' }} />
                    <span className='w-2 h-2 bg-gray-400 rounded-full animate-bounce' style={{ animationDelay: '300ms' }} />
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Input area */}
        <form onSubmit={handleSearchSubmit}>
          <div className='relative'>
            <input
              type='text'
              onChange={handleInputChange}
              onKeyPress={handleKeyPress}
              value={input}
              disabled={loading}
              placeholder='Ej: Necesito un electricista para...'
              className='w-full pl-4 pr-12 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary focus:bg-white transition-all text-sm placeholder-gray-400 disabled:opacity-50 disabled:cursor-not-allowed'
            />
            <button
              type='submit'
              disabled={loading || !input.trim()}
              className='absolute right-2 top-1/2 -translate-y-1/2 bg-primary text-white p-2 rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed'
            >
              <span className='material-icons text-sm'>{loading ? 'hourglass_empty' : 'send'}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

function Mensaje ({ rol, text }) {
  const ROLES = {
    user: 'user',
    assistant: 'assistant'
  }

  const isUser = rol === ROLES.user

  return (
    <div className={`flex gap-3 mb-3 ${isUser ? 'flex-row-reverse' : ''}`}>
      <div className='flex-shrink-0'>
        <div
          className={`w-8 h-8 rounded-full flex items-center justify-center ${
            isUser
              ? 'bg-blue-100'
              : 'bg-primary/10'
          }`}
        >
          <span
            className={`material-icons text-lg ${
              isUser ? 'text-blue-600' : 'text-primary'
            }`}
          >
            {isUser ? 'person' : 'smart_toy'}
          </span>
        </div>
      </div>
      <div className='flex-1'>
        <div
          className={`inline-block px-4 py-2 rounded-2xl max-w-full ${
            isUser
              ? 'bg-blue-500 text-white rounded-tr-none'
              : 'bg-gray-100 text-gray-800 rounded-tl-none'
          }`}
        >
          <p className='text-sm leading-relaxed break-words'>
            {text}
          </p>
        </div>
      </div>
    </div>
  )
}
