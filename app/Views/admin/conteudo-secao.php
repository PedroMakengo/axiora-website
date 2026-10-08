<?php
require __DIR__ . '/_icones.php';
require __DIR__ . '/_campo-cms.php';
$podeCriar = \Core\Permissoes::tem('conteudo', 'criar');
$podeEditar = \Core\Permissoes::tem('conteudo', 'editar');
$podeEliminar = \Core\Permissoes::tem('conteudo', 'eliminar');
$campos = $definicao['campos'];
$grupos = $definicao['grupos'] ?? [];

/** Primeiro campo de texto de um item — usado como título na tabela. */
$tituloItem = static function (array $defGrupo, array $dados): string {
    foreach ($defGrupo['campos'] as $nome => $def) {
        if ($def['tipo'] === 'texto' && ($dados[$nome] ?? '') !== '') {
            return (string) $dados[$nome];
        }
    }
    return '(sem título)';
};
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <a href="<?= BASE ?>/admin/conteudo/secoes" class="text-xs font-semibold text-muted hover:text-ink">&larr; Secções do site</a>
            <h1 class="mt-1"><?= htmlspecialchars($definicao['nome']) ?></h1>
            <p><?= htmlspecialchars($definicao['descricao']) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <?php if (!empty($definicao['ocultavel'])): ?>
            <label class="card flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-ink cursor-pointer">
                <input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/secoes/<?= $chave ?>/visibilidade" <?= $secao['visivel'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>>
                Visível no site
            </label>
            <?php endif; ?>
            <?php foreach ($definicao['gerir'] ?? [] as $rotuloGerir => $hrefGerir): ?>
            <a href="<?= BASE . $hrefGerir ?>" class="btn-secondary text-sm"><?= htmlspecialchars($rotuloGerir) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($campos)): ?>
    <form data-ajax-form data-manter-valores data-scroll-erro data-endpoint="<?= BASE ?>/admin/conteudo/secoes/<?= $chave ?>/guardar" data-texto-enviando="A guardar..." enctype="multipart/form-data" novalidate class="card p-6 mb-6 max-w-5xl">
        <?= \Core\Csrf::campo() ?>
        <h2 class="text-base font-bold text-ink mb-5">Textos e imagens</h2>
        <div class="grid md:grid-cols-2 gap-x-5 gap-y-5">
            <?php foreach ($campos as $nome => $def): ?>
            <?php $largo = in_array($def['tipo'], ['curto', 'rico', 'lista'], true) || $nome === 'titulo'; ?>
            <div class="<?= $largo ? 'md:col-span-2' : '' ?>">
                <?= campoCms($nome, $def, $secao['campos'][$nome] ?? '', 'secao') ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($podeEditar): ?>
        <div class="flex justify-end mt-6 pt-5 border-t border-line">
            <button type="submit" class="btn-primary"><span class="texto-btn">Guardar secção</span></button>
        </div>
        <?php endif; ?>
    </form>
    <?php endif; ?>

    <?php foreach ($grupos as $grupo => $defGrupo): ?>
    <div class="mb-6 max-w-5xl">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <div>
                <h2 class="text-base font-bold text-ink"><?= htmlspecialchars($defGrupo['nome']) ?></h2>
                <p class="text-xs text-muted">Aparecem pela ordem indicada. Desactive para esconder sem apagar.</p>
            </div>
            <?php if ($podeCriar): ?>
            <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-<?= $grupo ?>-novo">+ Adicionar</button>
            <?php endif; ?>
        </div>

        <div class="card overflow-x-auto">
            <table class="tabela min-w-[560px]">
                <thead><tr><th>Item</th><th>Ordem</th><th>Activo</th><th></th></tr></thead>
                <tbody>
                    <?php if (empty($itensGrupos[$grupo])): ?>
                    <tr><td colspan="4" class="text-center text-muted py-8">Ainda não há itens.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($itensGrupos[$grupo] as $item): ?>
                    <?php $dados = $item['dados']; ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <?php if (!empty($dados['icone'])): ?>
                                <span class="campo-icone__preview"><i class="mdi <?= htmlspecialchars($dados['icone']) ?>"></i></span>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <p class="font-semibold text-ink">
                                        <?= htmlspecialchars($tituloItem($defGrupo, $dados)) ?>
                                        <?php if (!empty($dados['destaque'])): ?><span class="badge bg-gold-soft text-ink ml-1">Destaque</span><?php endif; ?>
                                        <?php if (!empty($dados['preco'])): ?><span class="text-muted font-normal">· <?= htmlspecialchars($dados['preco'] . ' ' . ($dados['moeda'] ?? '')) ?></span><?php endif; ?>
                                    </p>
                                    <?php if (!empty($dados['texto'])): ?>
                                    <p class="text-xs text-muted truncate max-w-lg"><?= htmlspecialchars(\Core\Html::texto($dados['texto'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted"><?= (int) $item['ordem'] ?></td>
                        <td><input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/itens/<?= (int) $item['id'] ?>/estado" <?= $item['ativo'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>></td>
                        <td class="text-right whitespace-nowrap">
                            <div class="icon-btn-group justify-end">
                                <?php if ($podeEditar): ?>
                                <button type="button" class="icon-btn icon-btn--edit" title="Editar"
                                        data-editar="sheet-<?= $grupo ?>-editar"
                                        data-endpoint="<?= BASE ?>/admin/conteudo/itens/<?= (int) $item['id'] ?>/atualizar"
                                        data-valores="<?= htmlspecialchars(json_encode($dados + ['ordem' => (int) $item['ordem']], JSON_UNESCAPED_UNICODE)) ?>"><?= $iconeEditar ?></button>
                                <?php endif; ?>
                                <?php if ($podeEliminar): ?>
                                <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                        data-endpoint="<?= BASE ?>/admin/conteudo/itens/<?= (int) $item['id'] ?>/remover"
                                        data-confirmar="Remover este item?"><?= $iconeRemover ?></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php foreach (['novo' => 'Adicionar', 'editar' => 'Editar'] as $modo => $rotuloModo): ?>
    <aside class="sheet" id="sheet-<?= $grupo ?>-<?= $modo ?>" aria-hidden="true">
        <div class="sheet__header">
            <h2 class="sheet__title"><?= $rotuloModo ?> — <?= htmlspecialchars($defGrupo['nome']) ?></h2>
            <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
        </div>
        <form data-ajax-form data-endpoint="<?= $modo === 'novo' ? BASE . '/admin/conteudo/secoes/' . $chave . '/itens/criar' : '' ?>" data-texto-enviando="A guardar..." data-reload-on-success novalidate>
            <?= \Core\Csrf::campo() ?>
            <input type="hidden" name="grupo" value="<?= $grupo ?>">
            <div class="sheet__body space-y-4">
                <?php foreach ($defGrupo['campos'] as $nome => $def): ?>
                <?= campoCms($nome, $def, $def['tipo'] === 'checkbox' ? 0 : '', $grupo . '-' . $modo) ?>
                <?php endforeach; ?>
                <div>
                    <label class="rotulo">Ordem</label>
                    <input type="number" name="ordem" value="<?= $modo === 'novo' ? count($itensGrupos[$grupo]) + 1 : 0 ?>" min="0" max="999" class="campo">
                </div>
            </div>
            <div class="sheet__footer">
                <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
                <button type="submit" class="btn-primary text-sm"><span class="texto-btn"><?= $modo === 'novo' ? 'Adicionar' : 'Guardar alterações' ?></span></button>
            </div>
        </form>
    </aside>
    <?php endforeach; ?>
    <?php endforeach; ?>
</section>

<datalist id="icones-mdi">
    <?php foreach (['mdi-whatsapp', 'mdi-phone-outline', 'mdi-email-outline', 'mdi-map-marker-outline', 'mdi-clock-outline', 'mdi-storefront-outline', 'mdi-shield-check-outline', 'mdi-file-document-check-outline', 'mdi-check-decagram-outline', 'mdi-message-text-outline', 'mdi-clipboard-text-search-outline', 'mdi-cog-outline', 'mdi-account-group-outline', 'mdi-account-heart-outline', 'mdi-briefcase-outline', 'mdi-office-building-outline', 'mdi-car-outline', 'mdi-airplane', 'mdi-passport', 'mdi-earth', 'mdi-cash-multiple', 'mdi-currency-usd', 'mdi-currency-eur', 'mdi-truck-delivery-outline', 'mdi-package-variant-closed', 'mdi-wrench-outline', 'mdi-flash-outline', 'mdi-water-pump', 'mdi-timer-sand', 'mdi-eye-outline', 'mdi-view-grid-outline', 'mdi-star-outline', 'mdi-handshake-outline', 'mdi-lightbulb-on-outline', 'mdi-web', 'mdi-cellphone'] as $icone): ?>
    <option value="<?= $icone ?>"></option>
    <?php endforeach; ?>
</datalist>
