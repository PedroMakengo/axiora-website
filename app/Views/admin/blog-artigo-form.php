<?php
$editar = $artigo !== null;
$a = $artigo ?? [
    'titulo' => '', 'slug' => '', 'resumo' => '', 'conteudo' => '', 'imagem' => null, 'meta_descricao' => '',
    'estado' => 'rascunho', 'destaque' => 0, 'publicado_em' => null, 'categoria_id' => null,
];
$endpoint = $editar ? BASE . '/admin/blog/' . (int) $artigo['id'] . '/atualizar' : BASE . '/admin/blog/criar';
$publicadoEmInput = $a['publicado_em'] ? date('Y-m-d\TH:i', strtotime($a['publicado_em'])) : '';
$publico = $editar && $a['estado'] === 'publicado' && $a['publicado_em'] && strtotime($a['publicado_em']) <= time();
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <a href="<?= BASE ?>/admin/blog" class="text-xs font-semibold text-muted hover:text-ink">&larr; Artigos</a>
            <h1 class="mt-1"><?= $editar ? 'Editar artigo' : 'Novo artigo' ?></h1>
        </div>
        <?php if ($publico): ?>
        <a href="<?= BASE ?>/blog/<?= htmlspecialchars($a['slug']) ?>" target="_blank" rel="noopener" class="btn-secondary text-sm">Ver no site &nearr;</a>
        <?php endif; ?>
    </div>

    <form id="form-artigo" data-ajax-form data-manter-valores data-scroll-erro data-endpoint="<?= $endpoint ?>" data-texto-enviando="A guardar..." enctype="multipart/form-data" novalidate>
        <?= \Core\Csrf::campo() ?>
        <div class="grid xl:grid-cols-[1fr_340px] gap-5 items-start">
            <!-- Coluna principal -->
            <div class="space-y-5 min-w-0">
                <div class="card p-6 space-y-4">
                    <div>
                        <label class="rotulo" for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" required minlength="5" maxlength="180" value="<?= htmlspecialchars($a['titulo']) ?>" class="campo text-lg font-bold py-3" placeholder="Ex.: Como preparar os documentos para o visto Schengen">
                        <p class="campo-erro hidden" data-campo="titulo"></p>
                    </div>
                    <div>
                        <label class="rotulo" for="slug">Endereço (URL)</label>
                        <div class="flex items-stretch">
                            <span class="hidden sm:flex items-center px-3 text-xs text-muted bg-paper border border-r-0 border-line rounded-l-card whitespace-nowrap"><?= htmlspecialchars(preg_replace('#^https?://#', '', URL_BASE)) ?>/blog/</span>
                            <input type="text" id="slug" name="slug" maxlength="150" value="<?= htmlspecialchars($a['slug']) ?>" class="campo sm:rounded-l-none" placeholder="gerado a partir do título">
                        </div>
                        <p class="ajuda">Deixe vazio para gerar automaticamente. Evite alterar depois de publicado (os links partilhados deixam de funcionar).</p>
                        <p class="campo-erro hidden" data-campo="slug"></p>
                    </div>
                </div>

                <div class="card p-6">
                    <label class="rotulo">Conteúdo</label>
                    <div class="editor-artigo">
                        <div id="editor"><?= $a['conteudo'] ?></div>
                    </div>
                    <textarea name="conteudo" id="campo-conteudo" class="hidden"><?= htmlspecialchars($a['conteudo']) ?></textarea>
                    <p class="campo-erro hidden" data-campo="conteudo"></p>
                </div>

                <div class="card p-6 space-y-4">
                    <h2 class="text-base font-bold text-ink">Resumo e SEO</h2>
                    <div>
                        <label class="rotulo" for="resumo">Resumo</label>
                        <textarea id="resumo" name="resumo" rows="3" maxlength="320" class="campo" placeholder="Duas ou três frases que aparecem no cartão do artigo e no topo da página."><?= htmlspecialchars((string) $a['resumo']) ?></textarea>
                        <p class="ajuda">Se ficar vazio, é criado a partir do início do texto.</p>
                    </div>
                    <div>
                        <label class="rotulo" for="meta_descricao">Descrição para o Google <span class="font-normal text-muted">(opcional)</span></label>
                        <input type="text" id="meta_descricao" name="meta_descricao" maxlength="170" value="<?= htmlspecialchars((string) $a['meta_descricao']) ?>" class="campo" data-contador>
                        <p class="ajuda">Até 160 caracteres. Se ficar vazia, usa-se o resumo. <span data-contador-de="meta_descricao"></span></p>
                    </div>
                </div>
            </div>

            <!-- Barra lateral -->
            <aside class="space-y-5 xl:sticky xl:top-20">
                <div class="card p-5 space-y-4">
                    <h2 class="text-base font-bold text-ink">Publicação</h2>
                    <div>
                        <label class="rotulo" for="estado">Estado</label>
                        <select id="estado" name="estado" class="campo">
                            <option value="rascunho"<?= $a['estado'] === 'rascunho' ? ' selected' : '' ?>>Rascunho (não aparece no site)</option>
                            <option value="publicado"<?= $a['estado'] === 'publicado' ? ' selected' : '' ?>>Publicado</option>
                        </select>
                    </div>
                    <div>
                        <label class="rotulo" for="publicado_em">Data de publicação</label>
                        <input type="datetime-local" id="publicado_em" name="publicado_em" value="<?= $publicadoEmInput ?>" class="campo">
                        <p class="ajuda">Vazio = agora. Uma data futura agenda o artigo.</p>
                        <p class="campo-erro hidden" data-campo="publicado_em"></p>
                    </div>
                    <label class="flex items-start gap-2.5 text-sm text-ink cursor-pointer">
                        <input type="checkbox" name="destaque" value="1" class="mt-1" <?= $a['destaque'] ? 'checked' : '' ?>>
                        <span><strong>Destacar</strong><span class="block text-xs text-muted">Aparece primeiro na secção de notícias da homepage.</span></span>
                    </label>
                    <div class="flex gap-2 pt-1">
                        <button type="submit" class="btn-primary flex-1"><span class="texto-btn"><?= $editar ? 'Guardar alterações' : 'Guardar artigo' ?></span></button>
                    </div>
                </div>

                <div class="card p-5 space-y-3">
                    <label class="rotulo" for="categoria_id">Categoria</label>
                    <select id="categoria_id" name="categoria_id" class="campo">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"<?= (int) $a['categoria_id'] === (int) $cat['id'] ? ' selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?><?= $cat['ativo'] ? '' : ' (inactiva)' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="campo-erro hidden" data-campo="categoria_id"></p>
                    <?php if (\Core\Permissoes::tem('blog', 'criar')): ?>
                    <a href="<?= BASE ?>/admin/blog/categorias" class="text-xs font-semibold text-teal-dark hover:underline">Gerir categorias</a>
                    <?php endif; ?>
                </div>

                <div class="card p-5 space-y-3">
                    <label class="rotulo">Imagem de capa</label>
                    <img src="<?= $a['imagem'] ? BASE . '/' . htmlspecialchars($a['imagem']) : '' ?>" alt="" id="preview-capa" class="w-full aspect-video object-cover rounded-card border border-line bg-paper <?= $a['imagem'] ? '' : 'hidden' ?>" data-preview-imagem>
                    <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" data-max-mb="4" class="upload-field">
                    <p class="ajuda">Horizontal (16:9), ex.: 1600×900. Também é a imagem partilhada nas redes sociais.</p>
                    <p class="campo-erro hidden" data-campo="imagem"></p>
                    <?php if ($editar && $a['imagem']): ?>
                    <label class="flex items-center gap-2 text-xs text-muted cursor-pointer">
                        <input type="checkbox" name="remover_imagem" value="1"> Remover a imagem actual
                    </label>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </form>
</section>

<script>
// O Quill é carregado no rodapé do painel (layouts/admin-footer.php) — arranca quando a página estiver pronta.
document.addEventListener("DOMContentLoaded", function () {
    var BASE = window.__BASE__ || '';
    var campoConteudo = document.getElementById('campo-conteudo');
    var form = document.getElementById('form-artigo');

    if (!window.Quill) {
        // Se o editor não carregar, edita-se o HTML directamente na caixa de texto.
        document.getElementById('editor').parentNode.classList.add('hidden');
        campoConteudo.classList.remove('hidden');
        campoConteudo.classList.add('campo', 'font-mono', 'text-xs');
        campoConteudo.rows = 18;
        return;
    }

    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Escreva aqui o artigo...',
        modules: {
            toolbar: {
                container: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'link', 'image', 'video'],
                    [{ align: [] }],
                    ['clean']
                ],
                handlers: { image: escolherImagem }
            }
        }
    });

    var sincronizar = function () {
        campoConteudo.value = quill.getText().trim() === '' && !quill.root.querySelector('img,iframe') ? '' : quill.root.innerHTML;
    };
    quill.on('text-change', sincronizar);
    sincronizar();

    // Imagens no corpo do artigo: enviadas para o servidor (nunca gravadas em base64 na BD).
    function escolherImagem() {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/jpeg,image/png,image/webp';
        input.addEventListener('change', function () {
            var ficheiro = input.files && input.files[0];
            if (!ficheiro) return;
            if (ficheiro.size > 4 * 1024 * 1024) { window.toast('A imagem não pode exceder 4MB.', 'erro'); return; }

            var dados = new FormData();
            dados.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            dados.append('imagem', ficheiro);
            window.toast('A enviar imagem...', 'info');

            fetch(BASE + '/admin/blog/imagem', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: dados })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.sucesso) { window.toast(json.mensagem || 'Não foi possível enviar a imagem.', 'erro'); return; }
                    var posicao = (quill.getSelection(true) || { index: quill.getLength() }).index;
                    quill.insertEmbed(posicao, 'image', json.url, 'user');
                    quill.setSelection(posicao + 1);
                })
                .catch(function () { window.toast('Erro de ligação ao enviar a imagem.', 'erro'); });
        });
        input.click();
    }

    // Avisa antes de sair com alterações por guardar.
    var alterado = false;
    quill.on('text-change', function (d, o, origem) { if (origem === 'user') alterado = true; });
    form.addEventListener('input', function () { alterado = true; });
    form.addEventListener('ajax-form:sucesso', function (e) {
        alterado = false;
        var json = e.detail || {};
        var capa = document.getElementById('preview-capa');
        if ('imagem' in json && capa) {
            capa.src = json.imagem || '';
            capa.classList.toggle('hidden', !json.imagem);
            var remover = form.querySelector('[name="remover_imagem"]');
            if (remover) remover.checked = false;
            var ficheiro = form.querySelector('[name="imagem"]');
            if (ficheiro) ficheiro.value = '';
        }
    });
    window.addEventListener('beforeunload', function (e) {
        if (alterado) { e.preventDefault(); e.returnValue = ''; }
    });
});
</script>
