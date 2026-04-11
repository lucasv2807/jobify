import { Link } from 'react-router-dom'
import { Header } from '../components/Header'
import { Footer } from '../components/Footer'
export function ComoFuncionaC () {
  return (
    <>
      <Header />
      <div className='layout-content-container flex flex-col max-w-[960px] mx-auto'>
        <div className='flex flex-wrap justify-between gap-3 p-4'>
          <div className='flex min-w-72 flex-col gap-3'>
            <p className='text-primary text-4xl font-black leading-tight tracking-[-0.033em]'>Centro de Ayuda</p>
            <p className='text-[#648779] text-base font-normal leading-normal'>Encuentra respuestas a tus preguntas y ponte en contacto con nuestro equipo de soporte.</p>
          </div>
        </div>
        <h2 className='text-[#111715] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-10'>Preguntas Frecuentes</h2>
        <div className='flex flex-col p-4'>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como registrarse?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para registrarse, dirígete a la página de <Link to='/register' className='text-primary hover:underline'>Registro</Link>, selecciona registrarme como cliente e ingresa tus datos.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group' open=''>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'> Como iniciar sesion?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Dirigete a la pagina de <Link to='/login' className='text-primary hover:underline'>Iniciar sesion</Link>, ingresa email y contraseña.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como buscar servicios?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para buscar servicios, utiliza la barra de búsqueda en la parte superior de la página o navega por la pagina de <Link to='/buscar' className='text-primary hover:underline'>Búsqueda</Link>.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como contratar servicios?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para contratar servicios, selecciona el servicio que deseas, selecciona el boton de mercado pago y sigue las instrucciones.</p>
          </details>
          <details className='flex flex-col border-t border-b border-b-[#dce5e1] border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como editar mi perfil?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para editar tu perfil, dale click a tu foto de perfil en la esquina superior derecha y selecciona "Editar perfil" y te llevará a la página de edición.</p>
          </details>
        </div>
        <div className='mb-10 '>
          <h1 className='text-primary text-3xl font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-10'>
            Necesitas mas ayuda?
          </h1>
          <h2 className='text-md'>Contactanos a nuestro mail: clutchsoftware2025@gmail.com</h2>
        </div>
      </div>
      <Footer />
    </>
  )
}

export function ComoFuncionaP () {
  return (
    <>
      <Header />
      <div className='layout-content-container flex flex-col max-w-[960px] mx-auto'>
        <div className='flex flex-wrap justify-between gap-3 p-4'>
          <div className='flex min-w-72 flex-col gap-3'>
            <p className='text-primary text-4xl font-black leading-tight tracking-[-0.033em]'>Centro de Ayuda</p>
            <p className='text-[#648779] text-base font-normal leading-normal'>Encuentra respuestas a tus preguntas y ponte en contacto con nuestro equipo de soporte.</p>
          </div>
        </div>
        <h2 className='text-[#111715] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-10'>Preguntas Frecuentes</h2>
        <div className='flex flex-col p-4'>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group' open=''>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como registrarse?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para registrarse, dirígete a la página de <Link to='/register' className='text-primary hover:underline'>Registro</Link>, selecciona registrarme como proveedor e ingresa tus datos.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group' open=''>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'> Como iniciar sesion?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Dirigete a la pagina de <Link to='/login' className='text-primary hover:underline'>Iniciar sesion</Link>, ingresa email y contraseña.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como iniciar sesion en mercado pago?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para iniciar sesión en Mercado Pago, dirígete a tu perfil y selecciona "Conectar con mercado pago", ingresa tus credenciales y accede a tu cuenta.</p>
          </details>
          <details className='flex flex-col border-t border-t-[#dce5e1] py-2 group'>
            <summary className='flex cursor-pointer items-center justify-between gap-6 py-2'>
              <p className='text-[#111715] text-sm font-medium leading-normal'>Como publicar un servicio?</p>
              <div className='text-[#111715] group-open:rotate-180 transition-transform'>
                <span className='material-icons'>expand_more</span>
              </div>
            </summary>
            <p className='text-[#648779] text-sm font-normal leading-normal pb-2'>Para poder publicar un servicio primero debes haber enlazado tu cuenta de mercado pago, una vez ya hecho eso presiona el botón de publicar en la parte superior y te dirigira a la sección de publicación, completa los detalles requeridos y envía tu solicitud para revisión.</p>
          </details>
          <div>
            <h1 className='text-primary text-3xl font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-10'>
              Necesitas mas ayuda?
            </h1>
            <h2 className='text-md'>Contactanos a nuestro mail: clutchsoftware2025@gmail.com</h2>
          </div>
        </div>
      </div>
      <Footer />
    </>
  )
}
