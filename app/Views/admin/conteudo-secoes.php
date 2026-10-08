<?php
$podeEditar = \Core\Permissoes::tem('conteudo', 'editar');
$ordemNoSite = 1;
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Secções do site</h1>
            <p>Todos os blocos da página inicial e do rodapé, pela ordem em que aparecem. Edite os textos, imagens e listas, ou esconda uma secção.</p>
        </div>
        <a href="<?= BASE ?>/" target="_blank" rel="noopener" class="btn-secondary text-sm">Ver o site &nearr;</a>
    </div>

    <div class="card mb-4 flex flex-wrap items-center justify-between gap-3 p-5">
        <div class="flex items-center gap-4">
            <span class="w-9 h-9 rounded-full bg-marca text-white text-sm font-bold flex items-center justify-center">0</span>
            <div>
                <p class="font-bold text-ink">Slider (topo da página)</p>
                <p class="text-xs text-muted">Imagens, títulos e botões do carrossel inicial.</p>
            </div>
        </div>
        <a href="<?= BASE ?>/admin/conteudo/slider" class="btn-secondary text-sm">Gerir slides</a>
    </div>

    <div class="space-y-4">
        <?php foreach ($secoes as $chave => $s): ?>
        <div class="card flex flex-wrap items-center justify-between gap-4 p-5<?= $s['visivel'] ? '' : ' opacity-70' ?>">
            <div class="flex items-center gap-4 min-w-0">
                <span class="w-9 h-9 flex-none rounded-full bg-teal-soft text-teal-dark text-sm font-bold flex items-center justify-center"><?= $ordemNoSite++ ?></span>
                <div class="min-w-0">
                    <p class="font-bold text-ink">
                        <?= htmlspecialchars($s['nome']) ?>
                        <?php if (!$s['visivel']): ?><span class="badge bg-paper text-muted ml-1">Escondida</span><?php endif; ?>
                    </p>
                    <p class="text-xs text-muted"><?= htmlspecialchars($s['descricao']) ?></p>
                    <p class="text-xs text-muted mt-1">
                        <?= count($s['campos']) ?> campo(s)<?php if (!empty($s['grupos'])): ?> · <?= (int) $s['total_itens'] ?> item(ns) em <?= htmlspecialchars(implode(', ', array_column($s['grupos'], 'nome'))) ?><?php endif; ?>
                        <?php if ($s['atualizado_em']): ?> · editada a <?= date('d/m/Y H:i', strtotime($s['atualizado_em'])) ?><?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php if (!empty($s['ocultavel'])): ?>
                <label class="flex items-center gap-2 text-xs font-semibold text-muted cursor-pointer" title="Mostrar no site">
                    <input type="checkbox" class="admin-toggle-estado" data-endpoint="<?= BASE ?>/admin/conteudo/secoes/<?= $chave ?>/visibilidade" <?= $s['visivel'] ? 'checked' : '' ?> <?= $podeEditar ? '' : 'disabled' ?>>
                    Visível
                </label>
                <?php endif; ?>
                <a href="<?= BASE ?>/admin/conteudo/secoes/<?= $chave ?>" class="btn-primary text-sm">Editar</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
