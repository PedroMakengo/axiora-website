(function () {
  'use strict';

  /* Prefixo da subpasta base (ex.: /axiora-website). Definido no header. */
  var BASE = (window.__BASE__ || '');

  /* ============ Bloqueio de envios duplicados ============
     Um duplo clique (ou Enter repetido) não pode gravar/cobrar duas vezes: enquanto
     um POST idêntico (mesmo endereço e mesmos dados) está em curso, os repetidos
     recebem a mesma resposta em vez de chegarem ao servidor. */
  if (window.fetch && !window.__fetchProtegido) {
    window.__fetchProtegido = true;
    var fetchOriginal = window.fetch.bind(window);
    var pedidosEmCurso = {};

    var assinaturaCorpo = function (corpo) {
      if (!corpo) return '';
      if (typeof corpo === 'string') return corpo;
      if (corpo instanceof URLSearchParams) return corpo.toString();
      if (typeof FormData !== 'undefined' && corpo instanceof FormData) {
        var partes = [];
        corpo.forEach(function (valor, chave) {
          partes.push(chave + '=' + (typeof valor === 'string' ? valor : (valor.name + ':' + valor.size)));
        });
        return partes.join('&');
      }
      return String(Math.random()); // corpo desconhecido: nunca agrupa
    };

    window.fetch = function (url, opcoes) {
      var metodo = ((opcoes && opcoes.method) || 'GET').toUpperCase();
      if (metodo === 'GET' || metodo === 'HEAD') return fetchOriginal(url, opcoes);

      var chave = metodo + ' ' + String(url) + ' ' + assinaturaCorpo(opcoes && opcoes.body);
      if (pedidosEmCurso[chave]) {
        return pedidosEmCurso[chave].then(function (r) { return r.clone(); });
      }

      var pedido = fetchOriginal(url, opcoes);
      pedidosEmCurso[chave] = pedido;
      var libertar = function () { delete pedidosEmCurso[chave]; };
      pedido.then(libertar, libertar);
      return pedido.then(function (r) { return r.clone(); });
    };
  }

  /* ============ Validação no cliente ============
     Os formulários usam "novalidate" (mensagens próprias em vez das do browser),
     por isso a validação dos atributos HTML (required, type, minlength, maxlength,
     pattern, min/max, accept, data-igual-a, data-max-mb) é feita aqui antes de
     enviar. O servidor valida sempre de novo — isto é só para feedback imediato. */
  var validarFormulario = function (form) {
    var erros = {};
    var EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    Array.prototype.forEach.call(form.elements, function (campo) {
      if (!campo.name || campo.disabled || campo.type === 'hidden' || campo.type === 'submit' || campo.type === 'button') return;
      if (erros[campo.name]) return;

      var valor = (campo.type === 'file' || campo.type === 'checkbox' || campo.type === 'radio') ? '' : String(campo.value || '').trim();
      var rotulo = null;

      if (campo.required) {
        if (campo.type === 'checkbox' && !campo.checked) rotulo = 'Este campo é obrigatório.';
        else if (campo.type === 'radio' && !form.querySelector('input[name="' + campo.name + '"]:checked')) rotulo = 'Escolha uma opção.';
        else if (campo.type === 'file' && (!campo.files || !campo.files.length)) rotulo = 'Seleccione um ficheiro.';
        else if (campo.type !== 'checkbox' && campo.type !== 'radio' && campo.type !== 'file' && valor === '') rotulo = 'Este campo é obrigatório.';
      }

      if (!rotulo && valor !== '') {
        var min = parseInt(campo.getAttribute('minlength'), 10);
        var max = parseInt(campo.getAttribute('maxlength'), 10);
        if (campo.type === 'email' && !EMAIL.test(valor)) rotulo = 'Indique um email válido.';
        else if (!isNaN(min) && valor.length < min) rotulo = 'Mínimo de ' + min + ' caracteres.';
        else if (!isNaN(max) && max > 0 && valor.length > max) rotulo = 'Máximo de ' + max + ' caracteres.';
        else if (campo.getAttribute('pattern')) {
          try {
            if (!new RegExp('^(?:' + campo.getAttribute('pattern') + ')$').test(valor)) rotulo = campo.getAttribute('title') || 'Formato inválido.';
          } catch (e) { /* padrão inválido: ignora */ }
        }
        if (!rotulo && campo.type === 'number') {
          var numero = Number(valor.replace(',', '.'));
          if (isNaN(numero)) rotulo = 'Indique um número válido.';
          else if (campo.min !== '' && numero < Number(campo.min)) rotulo = 'O valor mínimo é ' + campo.min + '.';
          else if (campo.max !== '' && numero > Number(campo.max)) rotulo = 'O valor máximo é ' + campo.max + '.';
        }
        if (!rotulo && campo.type === 'url' && !/^https?:\/\/\S+$/i.test(valor)) rotulo = 'Indique um endereço começado por http:// ou https://';
      }

      var igualA = campo.getAttribute('data-igual-a');
      if (!rotulo && igualA && form.elements.namedItem(igualA) && campo.value !== form.elements.namedItem(igualA).value) {
        rotulo = 'Os valores não coincidem.';
      }

      if (!rotulo && campo.type === 'file' && campo.files && campo.files.length) {
        var maxMb = parseFloat(campo.getAttribute('data-max-mb'));
        var aceites = (campo.getAttribute('accept') || '').split(',').map(function (a) { return a.trim().toLowerCase(); }).filter(Boolean);
        Array.prototype.forEach.call(campo.files, function (f) {
          if (rotulo) return;
          if (!isNaN(maxMb) && f.size > maxMb * 1024 * 1024) rotulo = 'O ficheiro não pode exceder ' + maxMb + 'MB.';
          if (!rotulo && aceites.length) {
            var nome = f.name.toLowerCase();
            var tipo = (f.type || '').toLowerCase();
            var ok = aceites.some(function (a) {
              if (a.charAt(0) === '.') return nome.slice(-a.length) === a;
              if (a.slice(-2) === '/*') return tipo.indexOf(a.slice(0, -1)) === 0;
              return tipo === a;
            });
            if (!ok) rotulo = 'Tipo de ficheiro não permitido.';
          }
        });
      }

      if (rotulo) erros[campo.name] = rotulo;
    });

    return erros;
  };
  window.validarFormulario = validarFormulario;


  /* ===================== Painel admin: sidebar mobile ===================== */
  var btnAdminMenu = document.getElementById('btn-admin-menu');
  var adminSidebar = document.getElementById('admin-sidebar');
  var adminOverlay = document.getElementById('admin-sidebar-overlay');

  if (btnAdminMenu && adminSidebar) {
    var fecharSidebarAdmin = function () {
      adminSidebar.classList.remove('is-open');
      if (adminOverlay) adminOverlay.classList.remove('is-open');
      btnAdminMenu.setAttribute('aria-expanded', 'false');
    };

    btnAdminMenu.addEventListener('click', function () {
      var aberto = adminSidebar.classList.toggle('is-open');
      if (adminOverlay) adminOverlay.classList.toggle('is-open', aberto);
      btnAdminMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });

    if (adminOverlay) adminOverlay.addEventListener('click', fecharSidebarAdmin);
  }


  /* ============ Formulários AJAX genéricos (contacto, login, registo, recuperação) ============ */
  var formulariosAjax = document.querySelectorAll('form[data-ajax-form]');

  formulariosAjax.forEach(function (form) {
    var caixaMensagem = form.querySelector('.form-mensagem');
    var btnEnviar = form.querySelector('button[type="submit"]');
    var textoBtn = btnEnviar ? btnEnviar.querySelector('.texto-btn') : null;
    var textoOriginalBtn = textoBtn ? textoBtn.textContent : null;
    var textoEnviando = form.getAttribute('data-texto-enviando') || 'A processar...';

    var limparErros = function () {
      form.querySelectorAll('.campo-erro').forEach(function (el) {
        el.textContent = '';
        el.classList.add('hidden');
      });
    };

    var mostrarMensagem = function (texto, tipo) {
      if (typeof window.toast === 'function') {
        window.toast(texto, tipo === 'erro' ? 'erro' : 'sucesso');
      }
      if (!caixaMensagem) return;
      caixaMensagem.textContent = texto;
      caixaMensagem.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-teal-soft', 'text-teal-dark');
      caixaMensagem.classList.add.apply(
        caixaMensagem.classList,
        tipo === 'erro' ? ['bg-red-50', 'text-red-700'] : ['bg-teal-soft', 'text-teal-dark']
      );
    };

    form.addEventListener('submit', function (evento) {
      evento.preventDefault();
      // Bloqueia novo envio enquanto o anterior não terminar (duplo clique / Enter repetido).
      if (form.getAttribute('data-a-enviar') === '1') return;

      limparErros();
      if (caixaMensagem) caixaMensagem.classList.add('hidden');

      var errosCliente = validarFormulario(form);
      var camposComErro = Object.keys(errosCliente);
      if (camposComErro.length) {
        var semCaixa = [];
        camposComErro.forEach(function (campo) {
          var elemento = form.querySelector('.campo-erro[data-campo="' + campo + '"]');
          if (elemento) {
            elemento.textContent = errosCliente[campo];
            elemento.classList.remove('hidden');
          } else {
            semCaixa.push(errosCliente[campo]);
          }
        });
        mostrarMensagem(semCaixa.length ? semCaixa[0] : 'Verifique os campos assinalados.', 'erro');
        var primeiro = form.elements.namedItem(camposComErro[0]);
        if (primeiro && typeof primeiro.focus === 'function') primeiro.focus();
        return;
      }

      form.setAttribute('data-a-enviar', '1');
      if (textoBtn) textoBtn.textContent = textoEnviando;
      if (btnEnviar) btnEnviar.disabled = true;

      var dados = new FormData(form);
      var endpoint = form.getAttribute('data-endpoint');

      fetch(endpoint, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: dados,
      })
        .then(function (resposta) {
          return resposta.json().then(function (json) { return { status: resposta.status, json: json }; });
        })
        .then(function (resultado) {
          var json = resultado.json;

          if (json.sucesso) {
            // Permite às páginas reagirem à gravação (ex.: limpar aviso de alterações por guardar).
            form.dispatchEvent(new CustomEvent('ajax-form:sucesso', { detail: json }));
            if (json.redirecionar) {
              mostrarMensagem(json.mensagem || 'Sucesso! A redireccionar...', 'sucesso');
              window.location.href = json.redirecionar;
              return;
            }
            mostrarMensagem(json.mensagem || 'Operação concluída com sucesso.', 'sucesso');
            if (form.hasAttribute('data-manter-valores')) {
              // Formulários de edição (ex.: Definições): manter o que foi gravado, já normalizado pelo servidor
              Object.keys(json.dados || {}).forEach(function (campo) {
                var campoEl = form.elements.namedItem(campo);
                if (campoEl && 'value' in campoEl) campoEl.value = json.dados[campo];
              });
            } else {
              form.reset();
            }
            if (form.hasAttribute('data-reload-on-success')) {
              setTimeout(function () { window.location.reload(); }, 900);
            }
          } else if (json.erros) {
            Object.keys(json.erros).forEach(function (campo) {
              var elemento = form.querySelector('.campo-erro[data-campo="' + campo + '"]');
              if (elemento) {
                elemento.textContent = json.erros[campo];
                elemento.classList.remove('hidden');
              }
            });
            mostrarMensagem('Verifique os campos assinalados.', 'erro');
            // Formulários longos: levar o utilizador ao primeiro campo com erro.
            if (form.hasAttribute('data-scroll-erro')) {
              var primeiroErro = form.querySelector('.campo-erro:not(.hidden)');
              if (primeiroErro) primeiroErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          } else {
            mostrarMensagem(json.mensagem || 'Não foi possível concluir. Tente novamente.', 'erro');
          }
        })
        .catch(function () {
          mostrarMensagem('Erro de ligação. Verifique a sua internet e tente novamente.', 'erro');
        })
        .finally(function () {
          form.removeAttribute('data-a-enviar');
          if (textoBtn) textoBtn.textContent = textoOriginalBtn;
          if (btnEnviar) btnEnviar.disabled = false;
        });
    });
  });

  /* ============ Campos de senha: mostrar/ocultar + medidor de força ============ */
  var ICONE_OLHO_ABERTO = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>';
  var ICONE_OLHO_FECHADO = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M6.61 6.61A18.5 18.5 0 0 0 1 12s4 8 11 8a9.26 9.26 0 0 0 5.39-1.61"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    if (input.closest('.campo-senha')) return;

    var wrapper = document.createElement('div');
    wrapper.className = 'campo-senha';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var botao = document.createElement('button');
    botao.type = 'button';
    botao.className = 'campo-senha__toggle';
    botao.setAttribute('aria-label', 'Mostrar senha');
    botao.tabIndex = -1;
    botao.innerHTML = ICONE_OLHO_ABERTO;
    wrapper.appendChild(botao);

    botao.addEventListener('click', function () {
      var aVerSenha = input.type === 'password';
      input.type = aVerSenha ? 'text' : 'password';
      botao.innerHTML = aVerSenha ? ICONE_OLHO_FECHADO : ICONE_OLHO_ABERTO;
      botao.setAttribute('aria-label', aVerSenha ? 'Ocultar senha' : 'Mostrar senha');
    });
  });

  var avaliarForcaSenha = function (valor) {
    var pontos = 0;
    if (valor.length >= 8) pontos++;
    if (valor.length >= 10) pontos++;
    if (/[a-z]/.test(valor) && /[A-Z]/.test(valor)) pontos++;
    if (/\d/.test(valor)) pontos++;
    if (/[^A-Za-z0-9]/.test(valor)) pontos++;

    if (pontos <= 1) return { nivel: 1, texto: 'Fraca — combine letras e números, com pelo menos 8 caracteres.' };
    if (pontos === 2) return { nivel: 2, texto: 'Razoável — tente adicionar maiúsculas, números ou símbolos.' };
    if (pontos <= 4) return { nivel: 3, texto: 'Forte' };
    return { nivel: 4, texto: 'Muito forte' };
  };

  document.querySelectorAll('input[data-forca-senha]').forEach(function (input) {
    var medidor = document.createElement('div');
    medidor.className = 'forca-senha hidden';
    medidor.innerHTML = '<div class="forca-senha__barra">'
      + '<span class="forca-senha__seg"></span><span class="forca-senha__seg"></span>'
      + '<span class="forca-senha__seg"></span><span class="forca-senha__seg"></span>'
      + '</div><p class="forca-senha__texto"></p>';

    var alvoInsercao = input.closest('.campo-senha') || input;
    alvoInsercao.parentNode.insertBefore(medidor, alvoInsercao.nextSibling);

    var texto = medidor.querySelector('.forca-senha__texto');
    var niveis = ['', 'is-fraca', 'is-media', 'is-forte', 'is-muito-forte'];

    input.addEventListener('input', function () {
      medidor.classList.toggle('hidden', !input.value);
      medidor.classList.remove('is-fraca', 'is-media', 'is-forte', 'is-muito-forte');
      if (!input.value) return;

      var resultado = avaliarForcaSenha(input.value);
      if (niveis[resultado.nivel]) medidor.classList.add(niveis[resultado.nivel]);
      texto.textContent = resultado.texto;
    });
  });


  /* ============ Painel admin: acções rápidas (toggle activo, remover) ============ */
  var csrfMetaToken = document.querySelector('meta[name="csrf-token"]');
  var mostrarMensagemAdmin = function (texto, tipo) {
    if (typeof window.toast === 'function') {
      window.toast(texto, tipo === 'erro' ? 'erro' : 'sucesso');
    }
  };

  var enviarAcaoAdmin = function (endpoint, dadosExtra) {
    if (!csrfMetaToken) return Promise.resolve({ sucesso: false });
    var dados = new FormData();
    dados.append('csrf_token', csrfMetaToken.getAttribute('content'));
    Object.keys(dadosExtra).forEach(function (chave) { dados.append(chave, dadosExtra[chave]); });

    return fetch(endpoint, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: dados,
    })
      .then(function (resposta) { return resposta.json(); })
      .then(function (json) {
        mostrarMensagemAdmin(json.mensagem || (json.sucesso ? 'Actualizado.' : 'Não foi possível concluir.'), json.sucesso ? 'sucesso' : 'erro');
        return json;
      })
      .catch(function () {
        mostrarMensagemAdmin('Erro de ligação. Tente novamente.', 'erro');
        return { sucesso: false };
      });
  };

  document.querySelectorAll('.admin-toggle-estado').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      enviarAcaoAdmin(checkbox.getAttribute('data-endpoint'), { ativo: checkbox.checked ? 1 : 0 })
        .then(function (json) {
          // Se o servidor recusou, o checkbox volta ao estado anterior.
          if (!json.sucesso) checkbox.checked = !checkbox.checked;
        });
    });
  });

  document.querySelectorAll('.admin-remover').forEach(function (botao) {
    botao.addEventListener('click', function () {
      var confirmar = botao.getAttribute('data-confirmar') || 'Tem a certeza que quer remover?';
      if (!window.confirm(confirmar)) return;

      enviarAcaoAdmin(botao.getAttribute('data-endpoint'), {}).then(function (json) {
        if (!json.sucesso) return;
        var linha = botao.closest('tr, [data-linha-removivel]');
        if (!linha) return;
        var tabela = linha.closest('table');
        if (tabela && window.jQuery && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable(tabela)) {
          window.jQuery(tabela).DataTable().row(linha).remove().draw(false);
        } else {
          linha.remove();
        }
      });
    });
  });
})();
