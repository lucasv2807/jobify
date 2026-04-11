import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Home } from './pages/Home.jsx'
import { Login } from './pages/Login.jsx'
import { Register } from './pages/Register.jsx'
import { Service } from './pages/Service.jsx'
import { NotFound } from './pages/NotFound.jsx'
import { Perfil } from './pages/Perfil.jsx'
import { UsuarioProvider } from './context/Usuario.jsx'
import { NotificacionesProvider } from './context/Notificaciones.jsx'
import { Notificaciones } from './pages/Notificaciones.jsx'
import { Crear } from './pages/Crear.jsx'
import { Search } from './pages/Search.jsx'
import { Chat } from './pages/Chat.jsx'
import { FiltersProvider } from './context/Filters.jsx'
import { UpdateServicio } from './pages/UpdateServicio.jsx'
import { UpdatePerfil } from './pages/UpdatePerfil.jsx'
import { SesionMp } from './pages/SesionMp.jsx'
import { PaymentSuccess } from './pages/PaymentSuccess'
import { PaymentFailure } from './pages/PaymentFailure'
import { PaymentPending } from './pages/PaymentPending'
import { Admin } from './pages/Admin.jsx'
import { ComoFuncionaC, ComoFuncionaP } from './pages/ComoFunciona.jsx'
import { Reservas } from './pages/Reservas.jsx'

createRoot(document.getElementById('root')).render(
  <BrowserRouter>
    <UsuarioProvider>
      <NotificacionesProvider>
        <Routes>
          <Route path='*' element={<NotFound />} />
          <Route path='/' element={<Home />} />
          <Route path='/Buy/:id' element={<Service />} />
          <Route path='/Perfil/:id' element={<Perfil />} />
          <Route path='/login' element={<Login />} />
          <Route path='/register' element={<Register />} />
          <Route path='/crear' element={<Crear />} />
          <Route path='/buscar' element={<FiltersProvider><Search /></FiltersProvider>} />
          <Route path='/notificaciones' element={<Notificaciones />} />
          <Route path='/chat/:chatId' element={<Chat />} />
          <Route path='/updateServicio/:id' element={<UpdateServicio />} />
          <Route path='/Perfil/:id/updatePerfil' element={<UpdatePerfil />} />
          <Route path='/confirm' element={<SesionMp />} />
          <Route path='/payment' element={<PaymentSuccess />} />
          <Route path='/payment-success' element={<PaymentSuccess />} />
          <Route path='/payment-failure' element={<PaymentFailure />} />
          <Route path='/payment-pending' element={<PaymentPending />} />
          <Route path='/admin' element={<Admin />} />
          <Route path='/comoFuncionaC' element={<ComoFuncionaC />} />
          <Route path='/comoFuncionaP' element={<ComoFuncionaP />} />
          <Route path='/reservas/:id' element={<Reservas />} />
        </Routes>
      </NotificacionesProvider>
    </UsuarioProvider>
  </BrowserRouter>
)
