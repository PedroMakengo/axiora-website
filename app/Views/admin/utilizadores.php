<?php
require __DIR__ . '/_icones.php';
$usaDataTables = true;
$rotulosTipo = ['administrador' => 'Administrador', 'funcionario' => 'Funcionário'];
$corBadgeTipo = ['administrador' => 'bg-marca/10 text-marca', 'funcionario' => 'bg-gold-soft text-ink'];
$podeCriar = \Core\Permissoes::tem('utilizadores', 'criar');
$podeEditar = \Core\Permissoes::tem('utilizadores', 'editar');
$podeEliminar = \Core\Permissoes::tem('utilizadores', 'eliminar');
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Utilizadores</h1>
            <p>Contas de acesso ao painel. Os funcionários só vêem os módulos que lhes forem atribuídos.</p>
        </div>
        <?php if ($podeCriar): ?>
        <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-utilizador-novo">+ Novo utilizador</button>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table data-datatable data-sem-ordenar="3,5" class="tabela min-w-[720px]">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Activo</th>
                    <th>Desde</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilizadores as $u): ?>
                <?php
                $partesNomeU = preg_split('/\s+/', trim($u['nome']));
                $iniciaisU = mb_strtoupper(mb_substr($partesNomeU[0] ?? '', 0, 1) . mb_substr(count($partesNomeU) > 1 ? end($partesNomeU) : '', 0, 1));
                ?>
                <tr>
                    <td class="text-ink font-semibold">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($u['avatar'])): ?>
                            <img src="<?= BASE . '/' . htmlspecialchars($u['avatar']) ?>" alt="" class="upload-preview__avatar" style="width:30px;height:30px">
                            <?php else: ?>
                            <span class="upload-preview__avatar" style="width:30px;height:30px;font-size:.62rem"><?= htmlspecialchars($iniciaisU) ?></span>
                            <?php endif; ?>
                            <?= htmlspecialchars($u['nome']) ?>
                            <?php if ((int) $u['id'] === \Core\Auth::id()): ?><span class="text-xs text-muted font-normal">(você)</span><?php endif; ?>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $corBadgeTipo[$u['tipo']] ?? 'bg-paper text-muted' ?>"><?= htmlspecialchars($rotulosTipo[$u['tipo']] ?? ucfirst($u['tipo'])) ?></span></td>
                    <td>
                        <input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/utilizadores/<?= (int) $u['id'] ?>/estado"
                               <?= $u['ativo'] ? 'checked' : '' ?> <?= $podeEditar && (int) $u['id'] !== \Core\Auth::id() ? '' : 'disabled' ?>>
                    </td>
                    <td class="text-muted text-xs" data-order="<?= htmlspecialchars($u['criado_em']) ?>"><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($podeEditar): ?>
                            <button type="button" class="icon-btn icon-btn--edit btn-editar-utilizador" title="Editar"
                                    data-id="<?= (int) $u['id'] ?>"
                                    data-nome="<?= htmlspecialchars($u['nome']) ?>"
                                    data-email="<?= htmlspecialchars($u['email']) ?>"
                                    data-telefone="<?= htmlspecialchars($u['telefone'] ?? '') ?>"
                                    data-tipo="<?= htmlspecialchars($u['tipo']) ?>"><?= $iconeEditar ?></button>
                            <?php endif; ?>
                            <?php if ($podeEliminar && (int) $u['id'] !== \Core\Auth::id()): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/utilizadores/<?= (int) $u['id'] ?>/eliminar"
                                    data-confirmar="Remover a conta de &quot;<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>&quot;? Os artigos escritos por esta conta mantêm-se.">
                                <?= $iconeRemover ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- ===================== Sheet: Novo utilizador ===================== -->
<aside class="sheet" id="sheet-utilizador-novo" aria-hidden="true">
    <div class="sheet__header">
        <div>
            <h2 class="sheet__title">Novo utilizador</h2>
            <p class="sheet__subtitle">A pessoa entra em /login com o email e a senha definidos aqui.</p>
        </div>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/utilizadores/criar" data-texto-enviando="A criar..." data-reload-on-success novalidate>
        <?= \Core\Csrf::campo() ?>
        <div class="sheet__body space-y-4">
            <div>
                <label class="rotulo">Nome</label>
                <input type="text" name="nome" required maxlength="150" class="campo">
                <p class="campo-erro hidden" data-campo="nome"></p>
            </div>
            <div>
                <label class="rotulo">Email</label>
                <input type="email" name="email" required maxlength="150" class="campo">
                <p class="campo-erro hidden" data-campo="email"></p>
            </div>
            <div>
                <label class="rotulo">Telefone <span class="font-normal text-muted">(opcional)</span></label>
                <input type="tel" name="telefone" maxlength="20" class="campo">
                <p class="campo-erro hidden" data-campo="telefone"></p>
            </div>
            <div>
                <label class="rotulo">Tipo de conta</label>
                <select name="tipo" required class="campo">
                    <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= $tipo ?>"<?= $tipo === 'funcionario' ? ' selected' : '' ?>><?= htmlspecialchars($rotulosTipo[$tipo]) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="ajuda">Administrador: acesso total. Funcionário: só os módulos que definir depois em "Editar".</p>
                <p class="campo-erro hidden" data-campo="tipo"></p>
            </div>
            <div>
                <label class="rotulo">Senha</label>
                <input type="password" name="senha" required minlength="8" maxlength="72" autocomplete="new-password" data-forca-senha class="campo">
                <p class="campo-erro hidden" data-campo="senha"></p>
            </div>
        </div>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Criar utilizador</span></button>
        </div>
    </form>
</aside>

<!-- ===================== Sheet: Editar utilizador ===================== -->
<aside class="sheet" id="sheet-utilizador-editar" aria-hidden="true">
    <div class="sheet__header">
        <div>
            <h2 class="sheet__title">Editar utilizador</h2>
            <p class="sheet__subtitle" id="editar-utilizador-subtitulo"></p>
        </div>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <div class="sheet__body space-y-6">
        <form id="form-utilizador-perfil" data-ajax-form data-endpoint="" data-texto-enviando="A guardar..." data-reload-on-success novalidate>
            <?= \Core\Csrf::campo() ?>
            <div class="space-y-4">
                <div>
                    <label class="rotulo">Nome</label>
                    <input type="text" name="nome" required maxlength="150" class="campo">
                    <p class="campo-erro hidden" data-campo="nome"></p>
                </div>
                <div>
                    <label class="rotulo">Email</label>
                    <input type="email" name="email" required maxlength="150" class="campo">
                    <p class="campo-erro hidden" data-campo="email"></p>
                </div>
                <div>
                    <label class="rotulo">Telefone</label>
                    <input type="tel" name="telefone" maxlength="20" class="campo">
                    <p class="campo-erro hidden" data-campo="telefone"></p>
                </div>
                <div>
                    <label class="rotulo">Tipo de conta</label>
                    <select name="tipo" id="editar-utilizador-tipo" required class="campo">
                        <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= $tipo ?>"><?= htmlspecialchars($rotulosTipo[$tipo]) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="campo-erro hidden" data-campo="tipo"></p>
                </div>
                <div>
                    <label class="rotulo">Nova senha</label>
                    <input type="password" name="senha" minlength="8" maxlength="72" autocomplete="new-password" placeholder="Deixar em branco para manter a actual" class="campo">
                    <p class="campo-erro hidden" data-campo="senha"></p>
                </div>
            </div>
            <div class="mt-5">
                <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar dados</span></button>
            </div>
        </form>

        <div id="bloco-permissoes-funcionario" class="hidden border-t border-line pt-5">
            <h3 class="text-sm font-bold text-ink mb-1">Permissões por módulo</h3>
            <p class="text-xs text-muted mb-3">Só se aplicam a contas do tipo "Funcionário" — o Administrador tem sempre acesso total.</p>
            <form id="form-utilizador-permissoes" data-ajax-form data-manter-valores data-endpoint="" data-texto-enviando="A guardar..." novalidate>
                <?= \Core\Csrf::campo() ?>
                <div class="overflow-x-auto border border-line rounded-card">
                    <table class="w-full text-xs">
                        <thead class="bg-paper text-muted">
                            <tr>
                                <th class="text-left px-3 py-2 font-semibold">Módulo</th>
                                <?php foreach ($acoes as $rotuloAcao): ?>
                                <th class="px-2 py-2 font-semibold text-center"><?= htmlspecialchars($rotuloAcao) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modulos as $chaveModulo => $rotuloModulo): ?>
                            <tr class="border-t border-line">
                                <td class="px-3 py-2 text-ink font-semibold"><?= htmlspecialchars($rotuloModulo) ?></td>
                                <?php foreach ($acoes as $chaveAcao => $rotuloAcao): ?>
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" class="perm-checkbox" name="perm[<?= $chaveModulo ?>][<?= $chaveAcao ?>]" value="1"
                                           data-modulo="<?= $chaveModulo ?>" data-acao="<?= $chaveAcao ?>" aria-label="<?= htmlspecialchars($rotuloAcao . ' ' . $rotuloModulo) ?>">
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-secondary text-sm"><span class="texto-btn">Guardar permissões</span></button>
                </div>
            </form>
        </div>
    </div>
</aside>

<script>
(function () {
    var BASE = window.__BASE__ || '';
    var tipoSelect = document.getElementById('editar-utilizador-tipo');
    var formPermissoes = document.getElementById('form-utilizador-permissoes');

    var atualizarVisibilidadePermissoes = function () {
        document.getElementById('bloco-permissoes-funcionario').classList.toggle('hidden', tipoSelect.value !== 'funcionario');
    };
    tipoSelect.addEventListener('change', atualizarVisibilidadePermissoes);

    // "Criar", "Editar" ou "Eliminar" sem "Ver" não fazem sentido — marca "Ver" automaticamente.
    formPermissoes.addEventListener('change', function (evento) {
        var cb = evento.target;
        if (!cb.classList.contains('perm-checkbox') || !cb.checked || cb.getAttribute('data-acao') === 'ver') return;
        var ver = formPermissoes.querySelector('.perm-checkbox[data-modulo="' + cb.getAttribute('data-modulo') + '"][data-acao="ver"]');
        if (ver) ver.checked = true;
    });

    document.querySelectorAll('.btn-editar-utilizador').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var id = botao.getAttribute('data-id');
            var formPerfil = document.getElementById('form-utilizador-perfil');

            formPerfil.setAttribute('data-endpoint', BASE + '/admin/utilizadores/' + id + '/atualizar');
            formPerfil.querySelector('[name="nome"]').value = botao.getAttribute('data-nome');
            formPerfil.querySelector('[name="email"]').value = botao.getAttribute('data-email');
            formPerfil.querySelector('[name="telefone"]').value = botao.getAttribute('data-telefone');
            formPerfil.querySelector('[name="tipo"]').value = botao.getAttribute('data-tipo');
            formPerfil.querySelector('[name="senha"]').value = '';
            document.getElementById('editar-utilizador-subtitulo').textContent = botao.getAttribute('data-nome') + ' — ' + botao.getAttribute('data-email');

            formPermissoes.setAttribute('data-endpoint', BASE + '/admin/utilizadores/' + id + '/permissoes');
            formPermissoes.querySelectorAll('.perm-checkbox').forEach(function (cb) { cb.checked = false; });

            fetch(BASE + '/admin/utilizadores/' + id + '/permissoes', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (resposta) { return resposta.json(); })
                .then(function (json) {
                    if (!json.sucesso) return;
                    Object.keys(json.matriz).forEach(function (modulo) {
                        Object.keys(json.matriz[modulo]).forEach(function (acao) {
                            if (!json.matriz[modulo][acao]) return;
                            var cb = formPermissoes.querySelector('.perm-checkbox[data-modulo="' + modulo + '"][data-acao="' + acao + '"]');
                            if (cb) cb.checked = true;
                        });
                    });
                });

            atualizarVisibilidadePermissoes();
            window.abrirSheet('sheet-utilizador-editar');
        });
    });
})();
</script>
