import { Link } from 'react-router-dom'
export function NotFound () {
  return (
    <>
      <main className='bg-claro flex flex-col items-center justify-center w-screen h-screen gap-10 p-10'>
        <h1 className='text-oscuro text-9xl font-extrabold text-center'>404</h1>
        <h2 className='text-oscuro text-6xl font-extrabold text-center'>Page not found</h2>
        <Link to='/'>
          <button className='bg-medio hover:bg-oscuro text-white font-bold rounded-3xl w-90 h-12 border transition ease-in-out duration-150'>Volver al inicio</button>
        </Link>
      </main>
    </>
  )
}
