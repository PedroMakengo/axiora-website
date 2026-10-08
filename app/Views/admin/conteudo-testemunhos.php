<?php
require __DIR__ . '/_icones.php';
$podeCriar = \Core\Permissoes::tem('conteudo', 'criar');
$podeEditar = \Core\Permissoes::tem('conteudo', 'editar');
$podeEliminar = \Core\Permissoes::tem('conteudo', 'eliminar');

$camposTestemunho = static function (): void { ?>
    <div class="sheet__body space-y-4">
        <div>
            <label class="rotulo">Nome do cliente</label>
            <input type="text" name="nome" required maxlength="120" class="campo" placeholder="Ex.: Carlos M.">
            <p class="ajuda">Pode abreviar o apelido para proteger a privacidade do cliente.</p>
            <p class="campo-erro hidden" data-campo="nome"></p>
        </div>
        <div>
            <label class="rotulo">Profissão · serviço usado</label>
            <input type="text" name="descricao" maxlength="160" class="campo" placeholder="Ex.: Empresário · Visto VFS">
        </div>
        <div>
            <label class="rotulo">Testemunho</label>
            <textarea name="texto" data-editor="curto" required class="campo"></textarea>
            <p class="campo-erro hidden" data-campo="texto"></p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="rotulo">Estrelas</label>
                <select name="estrelas" class="campo">
                    <?php for ($e = 5; $e >= 1; $e--): ?>
                    <option value="<?= $e ?>"><?= str_repeat('★', $e) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="rotulo">Ordem</label>
                <input type="number" name="ordem" value="0" min="0" max="999" class="campo">
            </div>
        </div>
    </div>
<?php };
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Testemunhos</h1>
            <p>Opiniões de clientes mostradas na homepage (até 6 activos).</p>
        </div>
        <?php if ($podeCriar): ?>
        <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-testemunho-novo">+ Novo testemunho</button>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table class="tabela min-w-[720px]">
            <thead>
                <tr><th>Cliente</th><th>Testemunho</th><th>Ordem</th><th>Activo</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($testemunhos)): ?>
                <tr><td colspan="5" class="text-center text-muted py-10">Ainda não há testemunhos.</td></tr>
                <?php endif; ?>
                <?php foreach ($testemunhos as $t): ?>
                <tr>
                    <td>
                        <p class="font-semibold text-ink"><?= htmlspecialchars($t['nome']) ?></p>
                        <p class="text-xs text-muted"><?= htmlspecialchars((string) $t['descricao']) ?></p>
                    </td>
                    <td class="text-muted"><span class="text-gold"><?= str_repeat('★', (int) $t['estrelas']) ?></span> <span class="block truncate max-w-md"><?= htmlspecialchars(\Core\Html::texto($t['texto'])) ?></span></td>
                    <td class="text-muted"><?= (int) $t['ordem'] ?></td>
                    <td><input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/testemunhos/<?= (int) $t['id'] ?>/estado" <?= $t['ativo'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($podeEditar): ?>
                            <button type="button" class="icon-btn icon-btn--edit" title="Editar"
                                    data-editar="sheet-testemunho-editar"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/testemunhos/<?= (int) $t['id'] ?>/atualizar"
                                    data-valores="<?= htmlspecialchars(json_encode([
                                        'nome' => $t['nome'], 'descricao' => $t['descricao'], 'texto' => $t['texto'],
                                        'estrelas' => (int) $t['estrelas'], 'ordem' => (int) $t['ordem'],
                                    ], JSON_UNESCAPED_UNICODE)) ?>"><?= $iconeEditar ?></button>
                            <?php endif; ?>
                            <?php if ($podeEliminar): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/testemunhos/<?= (int) $t['id'] ?>/remover"
                                    data-confirmar="Remover este testemunho?"><?= $iconeRemover ?></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<aside class="sheet" id="sheet-testemunho-novo" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Novo testemunho</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/conteudo/testemunhos/criar" data-texto-enviando="A criar..." data-reload-on-success novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposTestemunho(); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Criar testemunho</span></button>
        </div>
    </form>
</aside>

<aside class="sheet" id="sheet-testemunho-editar" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Editar testemunho</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="" data-texto-enviando="A guardar..." data-reload-on-success novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposTestemunho(); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar alterações</span></button>
        </div>
    </form>
</aside>
