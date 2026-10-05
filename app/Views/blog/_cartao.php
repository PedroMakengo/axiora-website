<?php
/** Cartão de artigo — usado na homepage, na listagem do blog e nos relacionados. Espera $artigo. */
$minutos = \App\Models\Artigo::minutosLeitura((string) ($artigo['tamanho_conteudo'] ?? '0'));
?>
<article class="post-card reveal">
  <a href="<?= BASE ?>/blog/<?= htmlspecialchars($artigo['slug']) ?>" class="post-media" tabindex="-1" aria-hidden="true">
    <?php if (!empty($artigo['imagem'])): ?>
    <img src="<?= BASE . '/' . htmlspecialchars($artigo['imagem']) ?>" alt="" loading="lazy" />
    <?php else: ?>
    <span class="post-media-vazio"><i class="mdi mdi-newspaper-variant-outline"></i></span>
    <?php endif; ?>
  </a>
  <div class="post-body">
    <div class="post-meta">
      <?php if (!empty($artigo['categoria_nome'])): ?>
      <a href="<?= BASE ?>/blog/categoria/<?= htmlspecialchars($artigo['categoria_slug']) ?>" class="post-cat"><?= htmlspecialchars($artigo['categoria_nome']) ?></a>
      <?php endif; ?>
      <time datetime="<?= date('Y-m-d', strtotime($artigo['publicado_em'])) ?>"><?= dataPt($artigo['publicado_em'], true) ?></time>
    </div>
    <h3><a href="<?= BASE ?>/blog/<?= htmlspecialchars($artigo['slug']) ?>"><?= htmlspecialchars($artigo['titulo']) ?></a></h3>
    <?php if (!empty($artigo['resumo'])): ?>
    <p><?= htmlspecialchars($artigo['resumo']) ?></p>
    <?php endif; ?>
    <div class="post-foot">
      <span><i class="mdi mdi-clock-outline"></i> <?= $minutos ?> min de leitura</span>
      <a href="<?= BASE ?>/blog/<?= htmlspecialchars($artigo['slug']) ?>" class="service-link">Ler artigo <i class="mdi mdi-arrow-right"></i></a>
    </div>
  </div>
</article>
