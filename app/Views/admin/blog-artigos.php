<?php
require __DIR__ . '/_icones.php';
$usaDataTables = true;
$podeCriar = \Core\Permissoes::tem('blog', 'criar');
$podeEditar = \Core\Permissoes::tem('blog', 'editar');
$podeEliminar = \Core\Permissoes::tem('blog', 'eliminar');
$agora = time();
?>
<section class="admin-container pb-14">
    <div class="admin-page-head">
        <div>
            <h1>Artigos do blog</h1>
            <p>Notícias, comunicados e dicas publicados em <a href="<?= BASE ?>/blog" target="_blank" rel="noopener" class="text-teal-dark font-semibold hover:underline"><?= htmlspecialchars(preg_replace('#^https?://#', '', URL_BASE)) ?>/blog</a>.</p>
        </div>
        <?php if ($podeCriar): ?>
        <a href="<?= BASE ?>/admin/blog/novo" class="btn-primary text-sm">+ Novo artigo</a>
        <?php endif; ?>
    </div>

    <div class="card overflow-x-auto">
        <table data-datatable data-sem-ordenar="0,6" class="tabela min-w-[860px]">
            <thead>
                <tr>
                    <th></th>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Estado</th>
                    <th>Publicação</th>
                    <th>Leituras</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artigos as $a): ?>
                <?php
                $agendado = $a['estado'] === 'publicado' && $a['publicado_em'] && strtotime($a['publicado_em']) > $agora;
                if ($a['estado'] === 'rascunho') {
                    [$rotuloEstado, $corEstado] = ['Rascunho', 'bg-paper text-muted'];
                } elseif ($agendado) {
                    [$rotuloEstado, $corEstado] = ['Agendado', 'bg-gold-soft text-ink'];
                } else {
                    [$rotuloEstado, $corEstado] = ['Publicado', 'bg-teal-soft text-teal-dark'];
                }
                $publico = $a['estado'] === 'publicado' && !$agendado;
                ?>
                <tr>
                    <td class="w-20">
                        <?php if (!empty($a['imagem'])): ?>
                        <img src="<?= BASE . '/' . htmlspecialchars($a['imagem']) ?>" alt="" class="admin-thumb">
                        <?php else: ?>
                        <span class="admin-thumb--placeholder"><?= $iconeImagem ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($podeEditar): ?>
                        <a href="<?= BASE ?>/admin/blog/<?= (int) $a['id'] ?>/editar" class="font-semibold text-ink hover:text-teal-dark"><?= htmlspecialchars($a['titulo']) ?></a>
                        <?php else: ?>
                        <span class="font-semibold text-ink"><?= htmlspecialchars($a['titulo']) ?></span>
                        <?php endif; ?>
                        <?php if ($a['destaque']): ?><span class="badge bg-gold-soft text-ink ml-1">Destaque</span><?php endif; ?>
                        <span class="block text-xs text-muted mt-0.5"><?= htmlspecialchars($a['autor_nome'] ?? '—') ?></span>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($a['categoria_nome'] ?? 'Sem categoria') ?></td>
                    <td><span class="badge estado-pill <?= $corEstado ?>"><?= $rotuloEstado ?></span></td>
                    <td class="text-muted text-xs whitespace-nowrap" data-order="<?= htmlspecialchars((string) ($a['publicado_em'] ?? $a['criado_em'])) ?>">
                        <?= $a['publicado_em'] ? date('d/m/Y H:i', strtotime($a['publicado_em'])) : '—' ?>
                    </td>
                    <td class="text-muted" data-order="<?= (int) $a['visualizacoes'] ?>"><?= number_format((int) $a['visualizacoes'], 0, ',', '.') ?></td>
                    <td class="text-right whitespace-nowrap">
                        <div class="icon-btn-group justify-end">
                            <?php if ($publico): ?>
                            <a href="<?= BASE ?>/blog/<?= htmlspecialchars($a['slug']) ?>" target="_blank" rel="noopener" class="icon-btn" title="Ver no site"><?= $iconeVer ?></a>
                            <?php endif; ?>
                            <?php if ($podeEditar): ?>
                            <a href="<?= BASE ?>/admin/blog/<?= (int) $a['id'] ?>/editar" class="icon-btn icon-btn--edit" title="Editar"><?= $iconeEditar ?></a>
                            <?php endif; ?>
                            <?php if ($podeEliminar): ?>
                            <button type="button" class="icon-btn icon-btn--danger admin-remover" title="Remover"
                                    data-endpoint="<?= BASE ?>/admin/blog/<?= (int) $a['id'] ?>/eliminar"
                                    data-confirmar="Remover o artigo &quot;<?= htmlspecialchars($a['titulo'], ENT_QUOTES) ?>&quot;? Esta acção não pode ser desfeita.">
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
