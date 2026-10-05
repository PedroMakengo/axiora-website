<?php
require __DIR__ . '/_icones.php';
$podeCriar = \Core\Permissoes::tem('blog', 'criar');
$podeEditar = \Core\Permissoes::tem('blog', 'editar');
$podeEliminar = \Core\Permissoes::tem('blog', 'eliminar');

$camposCategoria = static function (): void { ?>
    <div class="sheet__body space-y-4">
        <div>
            <label class="rotulo">Nome</label>
            <input type="text" name="nome" required maxlength="80" class="campo" placeholder="Ex.: Dicas de viagem">
            <p class="campo-erro hidden" data-campo="nome"></p>
        </div>
        <div>
            <label class="rotulo">Endereço (URL)</label>
            <input type="text" name="slug" maxlength="100" class="campo" placeholder="gerado a partir do nome">
            <p class="ajuda">Fica em /blog/categoria/<em>endereco</em>.</p>
            <p class="campo-erro hidden" data-campo="slug"></p>
        </div>
        <div>
            <label class="rotulo">Descrição <span class="font-normal text-muted">(opcional)</span></label>
            <textarea name="descricao" rows="3" maxlength="255" class="campo"></textarea>
            <p class="ajuda">Mostrada no topo da página da categoria e usada pelo Google.</p>
        </div>
        <div>
            <label class="rotulo">Ordem</label>
            <input type="number" name="ordem" value="0" min="0" max="999" class="campo">
        </div>
    </div>
<?php };
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Categorias do blog</h1>
            <p>Organizam os artigos e aparecem na barra lateral do blog. Categorias inactivas ficam escondidas do site.</p>
        </div>
        <?php if ($podeCriar): ?>
        <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-categoria-nova">+ Nova categoria</button>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table class="tabela min-w-[640px]">
            <thead>
                <tr><th>Categoria</th><th>Artigos</th><th>Ordem</th><th>Activa</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                <tr><td colspan="5" class="text-center text-muted py-10">Ainda não há categorias.</td></tr>
                <?php endif; ?>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td>
                        <p class="font-semibold text-ink"><?= htmlspecialchars($cat['nome']) ?></p>
                        <p class="text-xs text-muted">/blog/categoria/<?= htmlspecialchars($cat['slug']) ?></p>
                    </td>
                    <td class="text-muted"><?= (int) $cat['total_artigos'] ?></td>
                    <td class="text-muted"><?= (int) $cat['ordem'] ?></td>
                    <td><input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/blog/categorias/<?= (int) $cat['id'] ?>/estado" <?= $cat['ativo'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($podeEditar): ?>
                            <button type="button" class="icon-btn icon-btn--edit" title="Editar"
                                    data-editar="sheet-categoria-editar"
                                    data-endpoint="<?= BASE ?>/admin/blog/categorias/<?= (int) $cat['id'] ?>/atualizar"
                                    data-valores="<?= htmlspecialchars(json_encode([
                                        'nome' => $cat['nome'], 'slug' => $cat['slug'], 'descricao' => $cat['descricao'], 'ordem' => (int) $cat['ordem'],
                                    ], JSON_UNESCAPED_UNICODE)) ?>"><?= $iconeEditar ?></button>
                            <?php endif; ?>
                            <?php if ($podeEliminar): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/blog/categorias/<?= (int) $cat['id'] ?>/remover"
                                    data-confirmar="Remover a categoria &quot;<?= htmlspecialchars($cat['nome'], ENT_QUOTES) ?>&quot;?<?= (int) $cat['total_artigos'] > 0 ? ' Os ' . (int) $cat['total_artigos'] . ' artigo(s) ficam sem categoria.' : '' ?>"><?= $iconeRemover ?></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<aside class="sheet" id="sheet-categoria-nova" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Nova categoria</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/blog/categorias/criar" data-texto-enviando="A criar..." data-reload-on-success novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposCategoria(); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Criar categoria</span></button>
        </div>
    </form>
</aside>

<aside class="sheet" id="sheet-categoria-editar" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Editar categoria</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="" data-texto-enviando="A guardar..." data-reload-on-success novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposCategoria(); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar alterações</span></button>
        </div>
    </form>
</aside>
