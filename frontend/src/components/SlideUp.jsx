export function SlideUp () {
  const handleClick = () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    })
  }
  return (
    <button
      onClick={handleClick}
      className='fixed bottom-0 right-0 w-10 text-claro aspect-square border border-claro hover:border-oscuro bg-oscuro rounded-full grid place-content-center m-4 hover:bg-claro hover:text-oscuro transition-colors duration-300 cursor-pointer focus:outline-2 focus:outline-claro'
      aria-label='Volver arriba'
      title='Volver arriba'
    >
      <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' strokeWidth={2} stroke='currentColor' className='size-5' aria-hidden='true'>
        <path strokeLinecap='round' strokeLinejoin='round' d='M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18' />
      </svg>
    </button>
  )
}
