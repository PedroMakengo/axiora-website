<?php
require __DIR__ . '/_icones.php';
$podeCriar = \Core\Permissoes::tem('conteudo', 'criar');
$podeEditar = \Core\Permissoes::tem('conteudo', 'editar');
$podeEliminar = \Core\Permissoes::tem('conteudo', 'eliminar');

$camposServico = static function (bool $novo): void { ?>
    <div class="sheet__body space-y-4">
        <div>
            <label class="rotulo">Imagem</label>
            <div class="upload-preview">
                <img src="" alt="" class="upload-preview__img hidden" data-preview-imagem>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" data-max-mb="4" class="upload-field">
            </div>
            <p class="ajuda">Formato 4:3 (ex.: 800×600). JPG, PNG ou WEBP até 4MB.<?= $novo ? '' : ' Deixe vazio para manter a actual.' ?></p>
            <p class="campo-erro hidden" data-campo="imagem"></p>
        </div>
        <div>
            <label class="rotulo">Descrição da imagem</label>
            <input type="text" name="imagem_alt" maxlength="160" class="campo" placeholder="Ex.: Viajante num terminal de aeroporto">
            <p class="ajuda">Para acessibilidade e Google Imagens.</p>
        </div>
        <div>
            <label class="rotulo">Nome do serviço</label>
            <input type="text" name="titulo" required maxlength="120" class="campo">
            <p class="campo-erro hidden" data-campo="titulo"></p>
        </div>
        <div>
            <label class="rotulo">Descrição</label>
            <textarea name="descricao" data-editor="curto" required class="campo"></textarea>
            <p class="campo-erro hidden" data-campo="descricao"></p>
        </div>
        <div>
            <label class="rotulo">Mensagem do WhatsApp</label>
            <input type="text" name="mensagem_whatsapp" maxlength="255" class="campo" placeholder="Olá! Quero saber mais sobre...">
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
            <h1>Serviços</h1>
            <p>Cartões da secção "Os nossos serviços" e lista de serviços do rodapé.</p>
        </div>
        <?php if ($podeCriar): ?>
        <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-servico-novo">+ Novo serviço</button>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table class="tabela min-w-[720px]">
            <thead>
                <tr><th>Serviço</th><th>Ordem</th><th>Activo</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($servicos)): ?>
                <tr><td colspan="4" class="text-center text-muted py-10">Ainda não há serviços.</td></tr>
                <?php endif; ?>
                <?php foreach ($servicos as $servico): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <?php if (!empty($servico['imagem'])): ?>
                            <img src="<?= BASE . '/' . htmlspecialchars($servico['imagem']) ?>" alt="" class="admin-thumb">
                            <?php else: ?>
                            <span class="admin-thumb--placeholder"><?= $iconeImagem ?></span>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <p class="font-semibold text-ink"><?= htmlspecialchars($servico['titulo']) ?></p>
                                <p class="text-xs text-muted truncate max-w-lg"><?= htmlspecialchars(\Core\Html::texto($servico['descricao'])) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted"><?= (int) $servico['ordem'] ?></td>
                    <td><input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/servicos/<?= (int) $servico['id'] ?>/estado" <?= $servico['ativo'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($podeEditar): ?>
                            <button type="button" class="icon-btn icon-btn--edit" title="Editar"
                                    data-editar="sheet-servico-editar"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/servicos/<?= (int) $servico['id'] ?>/atualizar"
                                    data-valores="<?= htmlspecialchars(json_encode([
                                        'titulo' => $servico['titulo'], 'descricao' => $servico['descricao'], 'imagem_alt' => $servico['imagem_alt'],
                                        'mensagem_whatsapp' => $servico['mensagem_whatsapp'], 'ordem' => (int) $servico['ordem'],
                                    ], JSON_UNESCAPED_UNICODE)) ?>"
                                    data-imagem="<?= $servico['imagem'] ? BASE . '/' . htmlspecialchars($servico['imagem']) : '' ?>"><?= $iconeEditar ?></button>
                            <?php endif; ?>
                            <?php if ($podeEliminar): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/servicos/<?= (int) $servico['id'] ?>/remover"
                                    data-confirmar="Remover o serviço &quot;<?= htmlspecialchars($servico['titulo'], ENT_QUOTES) ?>&quot;?"><?= $iconeRemover ?></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<aside class="sheet" id="sheet-servico-novo" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Novo serviço</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/conteudo/servicos/criar" data-texto-enviando="A criar..." data-reload-on-success enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposServico(true); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Criar serviço</span></button>
        </div>
    </form>
</aside>

<aside class="sheet" id="sheet-servico-editar" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Editar serviço</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="" data-texto-enviando="A guardar..." data-reload-on-success enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposServico(false); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar alterações</span></button>
        </div>
    </form>
</aside>
