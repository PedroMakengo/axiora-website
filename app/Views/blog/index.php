<?php
/** Link de paginação mantendo a pesquisa. */
$linkPagina = static function (int $n) use ($caminhoBase, $pesquisa): string {
    $query = array_filter(['q' => $pesquisa, 'pagina' => $n > 1 ? $n : null]);
    return BASE . $caminhoBase . ($query ? '?' . http_build_query($query) : '');
};
?>
    <!-- ─── CABEÇALHO DA PÁGINA ─── -->
    <section class="page-hero">
      <div class="container">
        <nav class="breadcrumb" aria-label="Caminho">
          <a href="<?= BASE ?>/">Início</a>
          <i class="mdi mdi-chevron-right"></i>
          <?php if ($categoria): ?>
          <a href="<?= BASE ?>/blog">Blog</a>
          <i class="mdi mdi-chevron-right"></i>
          <span><?= htmlspecialchars($categoria['nome']) ?></span>
          <?php else: ?>
          <span>Blog</span>
          <?php endif; ?>
        </nav>
        <span class="eyebrow eyebrow-light">Blog &amp; Notícias</span>
        <h1 class="page-title"><?= $categoria ? htmlspecialchars($categoria['nome']) : 'Novidades, comunicados e dicas práticas.' ?></h1>
        <p class="page-lead"><?= htmlspecialchars($categoria['descricao'] ?? 'Acompanhe as notícias da Axiora e aprenda a tratar de vistos, passaportes, viaturas e câmbio com menos burocracia.') ?></p>
      </div>
    </section>

    <section class="section section-blog">
      <div class="container blog-layout">
        <div class="blog-main">
          <?php if ($pesquisa !== ''): ?>
          <div class="blog-aviso">
            <span><?= (int) $total ?> resultado(s) para <strong>“<?= htmlspecialchars($pesquisa) ?>”</strong></span>
            <a href="<?= BASE . $caminhoBase ?>">Limpar pesquisa <i class="mdi mdi-close"></i></a>
          </div>
          <?php endif; ?>

          <?php if (empty($artigos)): ?>
          <div class="blog-vazio">
            <i class="mdi mdi-newspaper-variant-outline"></i>
            <h2>Ainda não há publicações aqui</h2>
            <p><?= $pesquisa !== '' ? 'Tente pesquisar por outras palavras.' : 'Volte em breve — estamos a preparar novidades para si.' ?></p>
            <a href="<?= BASE ?>/" class="btn btn-outline">Voltar ao início</a>
          </div>
          <?php else: ?>
          <div class="posts-grid posts-grid-2">
            <?php foreach ($artigos as $artigo): ?>
              <?php require __DIR__ . '/_cartao.php'; ?>
            <?php endforeach; ?>
          </div>

          <?php if ($totalPaginas > 1): ?>
          <nav class="pagination" aria-label="Paginação">
            <?php if ($pagina > 1): ?>
            <a href="<?= htmlspecialchars($linkPagina($pagina - 1)) ?>" rel="prev" aria-label="Página anterior"><i class="mdi mdi-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($n = 1; $n <= $totalPaginas; $n++): ?>
              <?php if ($n === 1 || $n === $totalPaginas || abs($n - $pagina) <= 1): ?>
              <a href="<?= htmlspecialchars($linkPagina($n)) ?>"<?= $n === $pagina ? ' class="is-current" aria-current="page"' : '' ?>><?= $n ?></a>
              <?php elseif (abs($n - $pagina) === 2): ?>
              <span class="pagination-gap">…</span>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
            <a href="<?= htmlspecialchars($linkPagina($pagina + 1)) ?>" rel="next" aria-label="Página seguinte"><i class="mdi mdi-chevron-right"></i></a>
            <?php endif; ?>
          </nav>
          <?php endif; ?>
          <?php endif; ?>
        </div>

        <aside class="blog-sidebar">
          <form class="sidebar-box sidebar-search" action="<?= BASE . $caminhoBase ?>" method="get" role="search">
            <label for="pesquisa-blog" class="sidebar-title">Pesquisar</label>
            <div class="search-field">
              <input type="search" id="pesquisa-blog" name="q" value="<?= htmlspecialchars($pesquisa) ?>" placeholder="Ex.: visto, passaporte..." maxlength="80" />
              <button type="submit" aria-label="Pesquisar"><i class="mdi mdi-magnify"></i></button>
            </div>
          </form>

          <?php if (!empty($categorias)): ?>
          <div class="sidebar-box">
            <h2 class="sidebar-title">Categorias</h2>
            <ul class="sidebar-cats">
              <li><a href="<?= BASE ?>/blog"<?= !$categoria ? ' class="is-current"' : '' ?>>Todas as publicações</a></li>
              <?php foreach ($categorias as $cat): ?>
              <li>
                <a href="<?= BASE ?>/blog/categoria/<?= htmlspecialchars($cat['slug']) ?>"<?= $categoria && (int) $categoria['id'] === (int) $cat['id'] ? ' class="is-current"' : '' ?>>
                  <?= htmlspecialchars($cat['nome']) ?> <span><?= (int) $cat['total_artigos'] ?></span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <?php if (!empty($ultimos)): ?>
          <div class="sidebar-box">
            <h2 class="sidebar-title">Mais recentes</h2>
            <ul class="sidebar-posts">
              <?php foreach ($ultimos as $recente): ?>
              <li>
                <a href="<?= BASE ?>/blog/<?= htmlspecialchars($recente['slug']) ?>">
                  <?php if (!empty($recente['imagem'])): ?>
                  <img src="<?= BASE . '/' . htmlspecialchars($recente['imagem']) ?>" alt="" loading="lazy" />
                  <?php else: ?>
                  <span class="sidebar-posts-vazio"><i class="mdi mdi-newspaper-variant-outline"></i></span>
                  <?php endif; ?>
                  <span>
                    <strong><?= htmlspecialchars($recente['titulo']) ?></strong>
                    <small><?= dataPt($recente['publicado_em'], true) ?></small>
                  </span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <div class="sidebar-box sidebar-cta">
            <i class="mdi mdi-whatsapp"></i>
            <h2>Precisa de ajuda com algum assunto?</h2>
            <p>Fale connosco — respondemos no horário de atendimento.</p>
            <a href="<?= htmlspecialchars($waGeral) ?>" class="btn btn-primary btn-block" target="_blank" rel="noopener">Falar no WhatsApp</a>
          </div>
        </aside>
      </div>
    </section>
