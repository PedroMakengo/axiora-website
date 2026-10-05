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

  // ─── SUBMENU "QUEM SOMOS" ───
  // Desktop: abre ao passar o rato (CSS) ou ao clicar; telemóvel: acordeão dentro do menu.
  const submenus = [...nav.querySelectorAll('.has-sub')]
  const setSub = (item, open) => {
    item.classList.toggle('is-open', open)
    item.querySelector('.nav-toggle').setAttribute('aria-expanded', String(open))
  }
  submenus.forEach((item) => {
    item.querySelector('.nav-toggle').addEventListener('click', (e) => {
      e.stopPropagation()
      const open = !item.classList.contains('is-open')
      submenus.forEach((outro) => setSub(outro, outro === item && open))
    })
    item.querySelectorAll('.nav-sub a').forEach((a) => a.addEventListener('click', () => setSub(item, false)))
  })
  document.addEventListener('click', (e) => {
    submenus.forEach((item) => {
      if (!item.contains(e.target)) setSub(item, false)
    })
  })
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return
    submenus.forEach((item) => {
      if (item.classList.contains('is-open')) {
        setSub(item, false)
        item.querySelector('.nav-toggle').focus()
      }
    })
  })

  // ─── HERO SLIDER ───
  // O avanço automático é conduzido pelo fim da animação da barra de progresso,
  // por isso pausar a animação (hover / separador oculto) pausa também o slider.
  const hero = document.getElementById('hero')
  if (hero) iniciarHero(hero)

  function iniciarHero(hero) {
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
  }

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
  // Só as âncoras da própria página (na homepage); o link "Blog" fica marcado pelo servidor.
  const navLinks = [...nav.querySelectorAll('ul a[href^="#"]')]
  const sectionObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        navLinks.forEach((a) => a.classList.toggle('is-current', a.getAttribute('href') === '#' + entry.target.id))
        // O botão "Quem somos" fica marcado quando a secção visível é uma das suas.
        submenus.forEach((item) => {
          item.querySelector('.nav-toggle').classList.toggle('is-current', !!item.querySelector('.nav-sub a.is-current'))
        })
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

  // ─── BLOG: copiar link do artigo ───
  document.querySelectorAll('[data-copiar]').forEach((botao) => {
    botao.addEventListener('click', () => {
      const texto = botao.dataset.copiar
      const feito = () => {
        botao.classList.add('is-copiado')
        botao.innerHTML = '<i class="mdi mdi-check"></i>'
        setTimeout(() => {
          botao.classList.remove('is-copiado')
          botao.innerHTML = '<i class="mdi mdi-link-variant"></i>'
        }, 1800)
      }
      if (navigator.clipboard) navigator.clipboard.writeText(texto).then(feito, () => window.prompt('Copie o link:', texto))
      else window.prompt('Copie o link:', texto)
    })
  })

  // ─── ANO NO RODAPÉ ───
  const ano = document.getElementById('year')
  if (ano) ano.textContent = new Date().getFullYear()
})
