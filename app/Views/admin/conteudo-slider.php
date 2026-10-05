<?php
require __DIR__ . '/_icones.php';
$podeCriar = \Core\Permissoes::tem('conteudo', 'criar');
$podeEditar = \Core\Permissoes::tem('conteudo', 'editar');
$podeEliminar = \Core\Permissoes::tem('conteudo', 'eliminar');

/** Campos do formulário do slide (iguais em "novo" e "editar"). */
$camposSlide = static function (bool $imagemObrigatoria): void { ?>
    <div class="sheet__body space-y-4">
        <div>
            <label class="rotulo">Imagem de fundo</label>
            <div class="upload-preview">
                <img src="" alt="" class="upload-preview__img hidden" data-preview-imagem>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" data-max-mb="4" class="upload-field" <?= $imagemObrigatoria ? 'required' : '' ?>>
            </div>
            <p class="ajuda">Horizontal, pelo menos 1920×1080. JPG, PNG ou WEBP até 4MB.<?= $imagemObrigatoria ? '' : ' Deixe vazio para manter a actual.' ?></p>
            <p class="campo-erro hidden" data-campo="imagem"></p>
        </div>
        <div>
            <label class="rotulo">Texto do separador</label>
            <input type="text" name="rotulo" required maxlength="40" class="campo" placeholder="Ex.: Vistos &amp; Passaporte">
            <p class="ajuda">Aparece nos separadores por baixo do slider.</p>
            <p class="campo-erro hidden" data-campo="rotulo"></p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="rotulo">Título</label>
                <input type="text" name="titulo" required maxlength="160" class="campo" placeholder="O seu visto preparado">
                <p class="campo-erro hidden" data-campo="titulo"></p>
            </div>
            <div>
                <label class="rotulo">Título em destaque</label>
                <input type="text" name="titulo_destaque" maxlength="160" class="campo" placeholder="sem complicações.">
                <p class="ajuda">Continuação do título, a cor.</p>
            </div>
        </div>
        <div>
            <label class="rotulo">Texto</label>
            <textarea name="texto" rows="3" maxlength="400" class="campo"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="rotulo">Texto do botão</label>
                <input type="text" name="botao_texto" maxlength="60" class="campo" placeholder="Falar no WhatsApp">
            </div>
            <div>
                <label class="rotulo">Ordem</label>
                <input type="number" name="ordem" value="0" min="0" max="999" class="campo">
            </div>
        </div>
        <div>
            <label class="rotulo">Mensagem do WhatsApp</label>
            <input type="text" name="mensagem_whatsapp" maxlength="255" class="campo" placeholder="Olá! Preciso de ajuda com o meu visto.">
            <p class="ajuda">Texto já escrito quando o cliente carrega no botão.</p>
        </div>
    </div>
<?php };
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Slider da homepage</h1>
            <p>Os slides do topo da página inicial, pela ordem definida. Só os activos são mostrados.</p>
        </div>
        <?php if ($podeCriar): ?>
        <button type="button" class="btn-primary text-sm" data-sheet-open="sheet-slide-novo">+ Novo slide</button>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table class="tabela min-w-[720px]">
            <thead>
                <tr><th>Slide</th><th>Separador</th><th>Ordem</th><th>Activo</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($slides)): ?>
                <tr><td colspan="5" class="text-center text-muted py-10">Ainda não há slides. Sem slides, a homepage começa directamente nos serviços.</td></tr>
                <?php endif; ?>
                <?php foreach ($slides as $slide): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="<?= BASE . '/' . htmlspecialchars($slide['imagem']) ?>" alt="" class="admin-thumb">
                            <div class="min-w-0">
                                <p class="font-semibold text-ink truncate max-w-md"><?= htmlspecialchars($slide['titulo']) ?> <span class="text-teal-dark"><?= htmlspecialchars((string) $slide['titulo_destaque']) ?></span></p>
                                <p class="text-xs text-muted truncate max-w-md"><?= htmlspecialchars((string) $slide['texto']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($slide['rotulo']) ?></td>
                    <td class="text-muted"><?= (int) $slide['ordem'] ?></td>
                    <td><input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/slider/<?= (int) $slide['id'] ?>/estado" <?= $slide['ativo'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($podeEditar): ?>
                            <button type="button" class="icon-btn icon-btn--edit" title="Editar"
                                    data-editar="sheet-slide-editar"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/slider/<?= (int) $slide['id'] ?>/atualizar"
                                    data-valores="<?= htmlspecialchars(json_encode([
                                        'rotulo' => $slide['rotulo'], 'titulo' => $slide['titulo'], 'titulo_destaque' => $slide['titulo_destaque'],
                                        'texto' => $slide['texto'], 'botao_texto' => $slide['botao_texto'], 'mensagem_whatsapp' => $slide['mensagem_whatsapp'],
                                        'ordem' => (int) $slide['ordem'],
                                    ], JSON_UNESCAPED_UNICODE)) ?>"
                                    data-imagem="<?= BASE . '/' . htmlspecialchars($slide['imagem']) ?>"><?= $iconeEditar ?></button>
                            <?php endif; ?>
                            <?php if ($podeEliminar): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/conteudo/slider/<?= (int) $slide['id'] ?>/remover"
                                    data-confirmar="Remover este slide?"><?= $iconeRemover ?></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<aside class="sheet" id="sheet-slide-novo" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Novo slide</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="<?= BASE ?>/admin/conteudo/slider/criar" data-texto-enviando="A criar..." data-reload-on-success enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposSlide(true); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Criar slide</span></button>
        </div>
    </form>
</aside>

<aside class="sheet" id="sheet-slide-editar" aria-hidden="true">
    <div class="sheet__header">
        <h2 class="sheet__title">Editar slide</h2>
        <button type="button" class="sheet__close" data-sheet-close aria-label="Fechar">&times;</button>
    </div>
    <form data-ajax-form data-endpoint="" data-texto-enviando="A guardar..." data-reload-on-success enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <?php $camposSlide(false); ?>
        <div class="sheet__footer">
            <button type="button" class="btn-secondary text-sm" data-sheet-close>Cancelar</button>
            <button type="submit" class="btn-primary text-sm"><span class="texto-btn">Guardar alterações</span></button>
        </div>
    </form>
</aside>
