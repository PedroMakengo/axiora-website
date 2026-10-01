/* ═══════════════════════════════════════════
   AXIORA — JavaScript Principal
═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
  // ─── HEADER: sombra ao fazer scroll ───
  const header = document.getElementById('header')
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 10)
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()

  // ─── MENU MOBILE ───
  const menuToggle = document.getElementById('menuToggle')
  const nav = document.getElementById('nav')

  function setMenu(open) {
    document.body.classList.toggle('menu-open', open)
    menuToggle.setAttribute('aria-expanded', String(open))
    menuToggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu')
    document.body.style.overflow = open ? 'hidden' : ''
  }

  menuToggle.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')))
  nav.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setMenu(false)))
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setMenu(false)
  })
  window.matchMedia('(min-width: 1025px)').addEventListener('change', (e) => {
    if (e.matches) setMenu(false)
  })

  // ─── HERO SLIDER ───
  // O avanço automático é conduzido pelo fim da animação da barra de progresso,
  // por isso pausar a animação (hover / separador oculto) pausa também o slider.
  const hero = document.getElementById('hero')
  const slides = hero.querySelectorAll('.hero-slide')
  const tabs = hero.querySelectorAll('.hero-tab')
  let current = 0

  function goTo(index) {
    const next = (index + slides.length) % slides.length
    if (next === current) return
    slides[current].classList.remove('is-active')
    tabs[current].classList.remove('is-active')
    tabs[current].setAttribute('aria-selected', 'false')
    current = next
    slides[current].classList.add('is-active')
    tabs[current].classList.add('is-active')
    tabs[current].setAttribute('aria-selected', 'true')
  }

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => goTo(+tab.dataset.index))
    tab.querySelector('.hero-tab-bar span').addEventListener('animationend', () => {
      if (tab.classList.contains('is-active')) goTo(current + 1)
    })
  })
  document.getElementById('heroNext').addEventListener('click', () => goTo(current + 1))
  document.getElementById('heroPrev').addEventListener('click', () => goTo(current - 1))

  const pause = (on) => hero.classList.toggle('is-paused', on)
  hero.addEventListener('mouseenter', () => pause(true))
  hero.addEventListener('mouseleave', () => pause(false))
  hero.addEventListener('focusin', () => pause(true))
  hero.addEventListener('focusout', () => pause(false))
  document.addEventListener('visibilitychange', () => pause(document.hidden))

  let touchStartX = 0
  hero.addEventListener('touchstart', (e) => (touchStartX = e.touches[0].clientX), { passive: true })
  hero.addEventListener(
    'touchend',
    (e) => {
      const diff = touchStartX - e.changedTouches[0].clientX
      if (Math.abs(diff) > 50) goTo(diff > 0 ? current + 1 : current - 1)
    },
    { passive: true },
  )

  // ─── MISSÃO / VISÃO / VALORES ───
  const mvvTabs = document.querySelectorAll('.mvv-tab')
  const mvvPanels = document.querySelectorAll('.mvv-panel')
  mvvTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      mvvTabs.forEach((t) => {
        t.classList.toggle('is-active', t === tab)
        t.setAttribute('aria-selected', String(t === tab))
      })
      mvvPanels.forEach((p) => (p.hidden = p.dataset.panel !== tab.dataset.tab))
    })
  })

  // ─── NAV: secção actual ───
  const navLinks = [...nav.querySelectorAll('ul a')]
  const sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        navLinks.forEach((a) => a.classList.toggle('is-current', a.getAttribute('href') === '#' + entry.target.id))
      })
    },
    { rootMargin: '-45% 0px -50% 0px' },
  )
  navLinks.forEach((a) => {
    const section = document.querySelector(a.getAttribute('href'))
    if (section) sectionObserver.observe(section)
  })

  // ─── SCROLL REVEAL ───
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
          revealObserver.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
  )
  document.querySelectorAll('.reveal').forEach((el) => {
    // pequeno escalonamento entre elementos irmãos (cartões em grelha)
    const siblings = [...el.parentElement.children].filter((c) => c.classList.contains('reveal'))
    el.style.transitionDelay = `${Math.min(siblings.indexOf(el), 4) * 80}ms`
    revealObserver.observe(el)
  })

  // ─── ANO NO RODAPÉ ───
  document.getElementById('year').textContent = new Date().getFullYear()
})
