import { useState } from 'react'

export function CarouselP ({ images = [] }) {
  // images: array of image URLs (optional). If empty, renders 4 placeholder slides.
  const slides = images.length ? images : [null, null, null, null]

  const [index, setIndex] = useState(0)

  const scrollPrev = () => setIndex(i => Math.max(0, i - 1))
  const scrollNext = () => setIndex(i => Math.min(slides.length - 1, i + 1))

  // percent of the inner track that represents a single slide
  const slidePercent = 100 / slides.length

  const handlePrev = () => {
    if (index === 0) return
    scrollPrev()
  }

  const handleNext = () => {
    if (index === slides.length - 1) return
    scrollNext()
  }

  return (
    <section className='relative w-full mx-auto px-5 mb-6'>
      <div className='relative shadow-[0_0_10px_rgba(0,0,0,0.2)] perspective-[100px] overflow-hidden rounded-md'>
        <div className='overflow-hidden z-1'>
          <div
            className='flex transition-transform duration-300 z-0'
            style={{ transform: `translateX(-${index * slidePercent}%)`, width: `${slides.length * 100}%` }}
          >
            {slides.map((src, i) => (
              <div key={i} className='flex-none' style={{ width: `${100 / slides.length}%` }}>
                {src && (
                  <img src={src} alt={`Imagen ${i + 1}`} className='block w-full h-auto object-cover' />
                )}
                {!src && (
                  <div className='w-full min-h-[200px] flex items-center justify-center'>
                    <span className='text-white text-3xl font-semibold'>{i + 1}</span>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>
      {/* Botones colocados fuera del contenedor con overflow-hidden para evitar que sean tapados */}
      <button
        type='button'
        onClick={handlePrev}
        disabled={index === 0}
        className={`absolute left-3 top-1/2 -translate-y-1/2 z-[999] inline-flex items-center justify-center w-12 h-12 rounded-full text-white pointer-events-auto focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300
          ${index === 0 ? 'bg-gray-700/40 opacity-50' : 'bg-gray-800/80 hover:bg-gray-900'}`}
        aria-label='Previous slide'
      >
        <svg xmlns='http://www.w3.org/2000/svg' className='w-6 h-6' viewBox='0 0 24 24' fill='none' stroke='currentColor' strokeWidth='2'>
          <path strokeLinecap='round' strokeLinejoin='round' d='M15 19l-7-7 7-7' />
        </svg>
      </button>

      <button
        type='button'
        onClick={handleNext}
        disabled={index === slides.length - 1}
        className={`absolute right-3 top-1/2 -translate-y-1/2 z-[999] inline-flex items-center justify-center w-12 h-12 rounded-full text-white pointer-events-auto focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300
          ${index === slides.length - 1 ? 'bg-gray-700/40 opacity-50' : 'bg-gray-800/80 hover:bg-gray-900'}`}
        aria-label='Next slide'
      >
        <svg xmlns='http://www.w3.org/2000/svg' className='w-6 h-6' viewBox='0 0 24 24' fill='none' stroke='currentColor' strokeWidth='2'>
          <path strokeLinecap='round' strokeLinejoin='round' d='M9 5l7 7-7 7' />
        </svg>
      </button>
    </section>
  )
}
