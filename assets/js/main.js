/* ═══════════════════════════════════════════
   AXIORA — JavaScript Principal
═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  // ─── DARK / LIGHT MODE ───
  const html = document.documentElement
  const toggleBtn = document.getElementById('themeToggle')

  // Carregar tema guardado ou usar dark por padrão
  const savedTheme = localStorage.getItem('axiora-theme') || 'dark'
  html.setAttribute('data-theme', savedTheme)

  toggleBtn.addEventListener('click', () => {
    const current = html.getAttribute('data-theme')
    const next = current === 'dark' ? 'light' : 'dark'
    html.setAttribute('data-theme', next)
    localStorage.setItem('axiora-theme', next)
  })

  // ─── NAVBAR SCROLL ───
  const navbar = document.getElementById('navbar')
  window.addEventListener(
    'scroll',
    () => {
      navbar.classList.toggle('scrolled', window.scrollY > 80)
    },
    { passive: true },
  )

  // ─── HERO SLIDER ───
  const slides = document.querySelectorAll('.slide')
  const dots = document.querySelectorAll('.slider-dot')
  const currentNum = document.getElementById('currentNum')
  let current = 0
  let timer = null

  function goTo(index) {
    slides[current].classList.remove('active')
    dots[current].classList.remove('active')
    current = (index + slides.length) % slides.length
    slides[current].classList.add('active')
    dots[current].classList.add('active')
    currentNum.textContent = String(current + 1).padStart(2, '0')
  }

  function startAuto() {
    timer = setInterval(() => goTo(current + 1), 5500)
  }

  function resetAuto() {
    clearInterval(timer)
    startAuto()
  }

  document.getElementById('nextBtn').addEventListener('click', () => {
    goTo(current + 1)
    resetAuto()
  })
  document.getElementById('prevBtn').addEventListener('click', () => {
    goTo(current - 1)
    resetAuto()
  })

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      goTo(+dot.dataset.index)
      resetAuto()
    })
  })

  // Swipe support (mobile)
  let touchStartX = 0
  const hero = document.getElementById('hero')

  hero.addEventListener(
    'touchstart',
    (e) => {
      touchStartX = e.touches[0].clientX
    },
    { passive: true },
  )
  hero.addEventListener(
    'touchend',
    (e) => {
      const diff = touchStartX - e.changedTouches[0].clientX
      if (Math.abs(diff) > 50) {
        diff > 0 ? goTo(current + 1) : goTo(current - 1)
        resetAuto()
      }
    },
    { passive: true },
  )

  startAuto()

  // ─── SCROLL REVEAL ───
  const revealEls = document.querySelectorAll('.reveal')
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('visible')
          observer.unobserve(e.target)
        }
      })
    },
    { threshold: 0.12 },
  )

  revealEls.forEach((el) => observer.observe(el))

  // ─── SMOOTH SCROLL ───
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'))
      if (target) {
        e.preventDefault()
        target.scrollIntoView({ behavior: 'smooth' })
      }
    })
  })
})
