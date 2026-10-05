(function () {
  'use strict';

  /* ===================== Toast (notificações) ===================== */
  var toastContainer = null;

  var garantirToastContainer = function () {
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      toastContainer.setAttribute('aria-live', 'polite');
      document.body.appendChild(toastContainer);
    }
    return toastContainer;
  };

  window.toast = function (mensagem, tipo) {
    var container = garantirToastContainer();
    var el = document.createElement('div');
    el.className = 'toast toast--' + (tipo || 'info');
    el.textContent = mensagem;
    container.appendChild(el);

    requestAnimationFrame(function () { el.classList.add('is-visible'); });

    setTimeout(function () {
      el.classList.remove('is-visible');
      setTimeout(function () { el.remove(); }, 250);
    }, 4000);
  };

  /* ===================== Sheets (paineis laterais) ===================== */
  var sheetOverlay = document.querySelector('[data-sheet-overlay]');
  var sheetsAbertos = [];

  window.abrirSheet = function (id) {
    var sheet = document.getElementById(id);
    if (!sheet) return;
    sheet.classList.add('is-open');
    sheet.setAttribute('aria-hidden', 'false');
    if (sheetOverlay) sheetOverlay.classList.add('is-open');
    if (sheetsAbertos.indexOf(id) === -1) sheetsAbertos.push(id);
  };

  window.fecharSheet = function (id) {
    var sheet = document.getElementById(id);
    if (sheet) {
      sheet.classList.remove('is-open');
      sheet.setAttribute('aria-hidden', 'true');
    }
    sheetsAbertos = sheetsAbertos.filter(function (s) { return s !== id; });
    if (!sheetsAbertos.length && sheetOverlay) sheetOverlay.classList.remove('is-open');
  };

  document.querySelectorAll('[data-sheet-open]').forEach(function (botao) {
    botao.addEventListener('click', function () {
      window.abrirSheet(botao.getAttribute('data-sheet-open'));
    });
  });

  document.querySelectorAll('[data-sheet-close]').forEach(function (botao) {
    botao.addEventListener('click', function () {
      var sheet = botao.closest('.sheet');
      if (sheet) window.fecharSheet(sheet.id);
    });
  });

  if (sheetOverlay) {
    sheetOverlay.addEventListener('click', function () {
      sheetsAbertos.slice().forEach(window.fecharSheet);
    });
  }

  document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && sheetsAbertos.length) {
      window.fecharSheet(sheetsAbertos[sheetsAbertos.length - 1]);
    }
  });

  /* ===================== Sidebar: grupos em accordion (dropdown animado) ===================== */
  var CHAVE_NAVGROUPS = 'axiora_admin_navgroups';
  var gruposAbertos = {};
  try { gruposAbertos = JSON.parse(localStorage.getItem(CHAVE_NAVGROUPS) || '{}'); } catch (e) { gruposAbertos = {}; }

  document.querySelectorAll('.admin-sidebar__group').forEach(function (grupo) {
    var chave = grupo.getAttribute('data-group');
    var toggle = grupo.querySelector('.admin-sidebar__group-toggle');
    var temAtivo = grupo.classList.contains('is-open');

    // Só aplica a preferência guardada se este grupo não contiver a página actual
    // (a página actual manda sempre abrir o seu próprio grupo).
    if (!temAtivo && Object.prototype.hasOwnProperty.call(gruposAbertos, chave)) {
      grupo.classList.toggle('is-open', !!gruposAbertos[chave]);
    }

    toggle.addEventListener('click', function () {
      var vaiAbrir = !grupo.classList.contains('is-open');
      grupo.classList.toggle('is-open', vaiAbrir);
      gruposAbertos[chave] = vaiAbrir;
      try { localStorage.setItem(CHAVE_NAVGROUPS, JSON.stringify(gruposAbertos)); } catch (e) { /* ignora */ }
    });
  });

  /* ===================== Sidebar: recolher/expandir (desktop) ===================== */
  var CHAVE_SIDEBAR_COLAPSADA = 'axiora_admin_sidebar_colapsada';
  var btnColapsar = document.getElementById('btn-admin-colapsar');

  try {
    if (localStorage.getItem(CHAVE_SIDEBAR_COLAPSADA) === '1') {
      document.body.classList.add('admin-sidebar-colapsada');
    }
  } catch (e) { /* localStorage indisponível — ignora */ }

  if (btnColapsar) {
    btnColapsar.addEventListener('click', function () {
      var colapsada = document.body.classList.toggle('admin-sidebar-colapsada');
      try { localStorage.setItem(CHAVE_SIDEBAR_COLAPSADA, colapsada ? '1' : '0'); } catch (e) { /* ignora */ }
    });
  }

  /* ===================== Menu do utilizador (topbar) ===================== */
  var btnUserMenu = document.getElementById('btn-user-menu');
  var menuUser = document.getElementById('menu-user');

  if (btnUserMenu && menuUser) {
    var fecharMenuUser = function () {
      menuUser.classList.add('hidden');
      btnUserMenu.setAttribute('aria-expanded', 'false');
    };

    btnUserMenu.addEventListener('click', function (evento) {
      evento.stopPropagation();
      var aberto = menuUser.classList.toggle('hidden') === false;
      btnUserMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });

    document.addEventListener('click', function (evento) {
      if (!menuUser.classList.contains('hidden') && !menuUser.contains(evento.target) && evento.target !== btnUserMenu) {
        fecharMenuUser();
      }
    });

    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape') fecharMenuUser();
    });

    menuUser.querySelectorAll('[data-sheet-open]').forEach(function (item) {
      item.addEventListener('click', fecharMenuUser);
    });
  }

  /* ===================== DataTables — inicialização genérica ===================== */
  var LOCALE_PT = {
    processing: 'A processar...',
    search: 'Pesquisar:',
    lengthMenu: 'Mostrar _MENU_ registos',
    info: '_START_ a _END_ de _TOTAL_ registos',
    infoEmpty: '0 registos',
    infoFiltered: '(filtrado de _MAX_ registos)',
    loadingRecords: 'A carregar...',
    zeroRecords: 'Não foram encontrados registos',
    emptyTable: 'Sem dados disponíveis',
    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
  };

  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
    document.querySelectorAll('table[data-datatable]').forEach(function (tabela) {
      var opcoes = {
        language: LOCALE_PT,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [],
      };

      var semOrdenar = (tabela.getAttribute('data-sem-ordenar') || '').split(',').filter(Boolean).map(Number);
      if (semOrdenar.length) {
        opcoes.columnDefs = [{ targets: semOrdenar, orderable: false }];
      }

      // Tabelas de edição em linha (ex.: mapa de notas) não devem paginar —
      // o utilizador precisa de ver e editar todas as linhas de uma vez.
      if (tabela.hasAttribute('data-sem-paginacao')) {
        opcoes.paging = false;
        opcoes.info = false;
      }

      window.jQuery(tabela).DataTable(opcoes);
    });
  }

  /* ===================== Sheets de edição genéricas =====================
     Botão com data-editar="id-da-sheet", data-endpoint e data-valores (JSON):
     preenche o formulário da sheet e abre-a. data-imagem mostra a imagem actual. */
  document.querySelectorAll('[data-editar]').forEach(function (botao) {
    botao.addEventListener('click', function () {
      var sheet = document.getElementById(botao.getAttribute('data-editar'));
      var form = sheet ? sheet.querySelector('form') : null;
      if (!form) return;

      form.setAttribute('data-endpoint', botao.getAttribute('data-endpoint'));
      form.querySelectorAll('.campo-erro').forEach(function (el) { el.textContent = ''; el.classList.add('hidden'); });
      form.querySelectorAll('input[type="file"]').forEach(function (f) { f.value = ''; });

      var valores = {};
      try { valores = JSON.parse(botao.getAttribute('data-valores') || '{}'); } catch (e) { valores = {}; }
      Object.keys(valores).forEach(function (nome) {
        var campo = form.elements.namedItem(nome);
        if (!campo) return;
        if (campo.type === 'checkbox') campo.checked = !!Number(valores[nome]);
        else campo.value = valores[nome] === null ? '' : valores[nome];
      });

      var preview = form.querySelector('[data-preview-imagem]');
      if (preview) {
        var imagem = botao.getAttribute('data-imagem') || '';
        preview.src = imagem;
        preview.classList.toggle('hidden', !imagem);
      }

      window.abrirSheet(sheet.id);
    });
  });

  /* ===================== Pré-visualização de imagens escolhidas ===================== */
  document.querySelectorAll('input[type="file"][accept^="image"]').forEach(function (input) {
    input.addEventListener('change', function () {
      var contentor = input.closest('.upload-preview, .card, .sheet__body');
      var preview = contentor ? contentor.querySelector('[data-preview-imagem]') : null;
      if (!preview || !input.files || !input.files[0] || !window.URL) return;
      preview.src = URL.createObjectURL(input.files[0]);
      preview.classList.remove('hidden');
    });
  });

  /* ===================== Contador de caracteres ===================== */
  document.querySelectorAll('[data-contador]').forEach(function (campo) {
    var alvo = document.querySelector('[data-contador-de="' + campo.name + '"]');
    if (!alvo) return;
    var atualizar = function () {
      alvo.textContent = '(' + campo.value.length + '/' + (campo.maxLength > 0 ? campo.maxLength : '∞') + ')';
    };
    campo.addEventListener('input', atualizar);
    atualizar();
  });
})();
