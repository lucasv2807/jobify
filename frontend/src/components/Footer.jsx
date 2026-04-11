import { Link } from 'react-router-dom'
export function Footer () {
  return (
    <footer className='bg-oscuro' id='contact'>
      <div className='container px-4 sm:px-6 lg:px-8 pb-4 pt-8 mx-auto'>
        <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4'>
          <div className='col-span-1'>
            <h2 className='text-2xl font-bold text-primary mb-4'>Jobify</h2>
            <p className='text-claro'>Conectando oportunidades, construyendo futuro.</p>
          </div>
          <div>
            <h3 className='font-semibold text-claro  mb-4'>Para Clientes</h3>
            <ul className='space-y-2'>
              <li>
                <Link to='/buscar' className='text-claro hover:text-primary'>
                  Buscar Servicios
                </Link>
              </li>
              <li>
                <Link to='/ComoFuncionaC' className='text-claro hover:text-primary'>
                  ¿Cómo funciona?
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h3 className='font-semibold text-claro  mb-4'>Para Proveedores</h3>
            <ul className='space-y-2'>
              <li>
                <Link to='/publicar' className='text-claro hover:text-primary'>
                  Publicar Servicio
                </Link>
              </li>
              <li>
                <Link to='/ComoFuncionaP' className='text-claro hover:text-primary'>
                  ¿Cómo funciona?
                </Link>
              </li>
            </ul>
          </div>
          <div>
            <h3 className='font-semibold text-claro '>Contáctanos</h3>
            <ul className='space-y-2 text-claro'>
              <li>
                <span className='material-icons text-claro font-bold mt-1 mr-2'>location_on:</span>
                <span>Uruguay, Montevideo</span>
              </li>
              <li>
                <span className='material-icons text-claro font-bold mr-2'>email:</span>
                <span>clutchsoftware2025@gmail.com</span>
              </li>
            </ul>
          </div>
        </div>
        <div className='mt-4 border-t border-gray-200 y-700 pt-4 text-center text-sm text-claro'>
          <p>© 2025 Jobify. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
  )
}
