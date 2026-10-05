<?php
$minutos = \App\Models\Artigo::minutosLeitura($artigo['conteudo']);
$urlCodificada = rawurlencode($urlPartilha);
$tituloCodificado = rawurlencode($artigo['titulo']);
?>
    <!-- ─── CABEÇALHO DO ARTIGO ─── -->
    <section class="page-hero page-hero-artigo">
      <div class="container container-artigo">
        <nav class="breadcrumb" aria-label="Caminho">
          <a href="<?= BASE ?>/">Início</a>
          <i class="mdi mdi-chevron-right"></i>
          <a href="<?= BASE ?>/blog">Blog</a>
          <?php if (!empty($artigo['categoria_nome'])): ?>
          <i class="mdi mdi-chevron-right"></i>
          <a href="<?= BASE ?>/blog/categoria/<?= htmlspecialchars($artigo['categoria_slug']) ?>"><?= htmlspecialchars($artigo['categoria_nome']) ?></a>
          <?php endif; ?>
        </nav>
        <h1 class="page-title"><?= htmlspecialchars($artigo['titulo']) ?></h1>
        <?php if (!empty($artigo['resumo'])): ?>
        <p class="page-lead"><?= htmlspecialchars($artigo['resumo']) ?></p>
        <?php endif; ?>
        <ul class="article-meta">
          <li><i class="mdi mdi-calendar-blank-outline"></i> <time datetime="<?= date('c', strtotime($artigo['publicado_em'])) ?>"><?= dataPt($artigo['publicado_em']) ?></time></li>
          <li><i class="mdi mdi-clock-outline"></i> <?= $minutos ?> min de leitura</li>
          <?php if (!empty($artigo['autor_nome'])): ?>
          <li><i class="mdi mdi-account-outline"></i> <?= htmlspecialchars($artigo['autor_nome']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </section>

    <article class="section section-artigo">
      <div class="container container-artigo">
        <?php if (!empty($artigo['imagem'])): ?>
        <figure class="article-cover">
          <img src="<?= BASE . '/' . htmlspecialchars($artigo['imagem']) ?>" alt="<?= htmlspecialchars($artigo['titulo']) ?>" />
        </figure>
        <?php endif; ?>

        <!-- O conteúdo foi limpo por Core\Html::limpar() ao gravar (lista branca de tags). -->
        <div class="prose">
          <?= $artigo['conteudo'] ?>
        </div>

        <div class="article-share">
          <span>Partilhar:</span>
          <a href="https://wa.me/?text=<?= $tituloCodificado ?>%20<?= $urlCodificada ?>" target="_blank" rel="noopener" aria-label="Partilhar no WhatsApp"><i class="mdi mdi-whatsapp"></i></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $urlCodificada ?>" target="_blank" rel="noopener" aria-label="Partilhar no Facebook"><i class="mdi mdi-facebook"></i></a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $urlCodificada ?>" target="_blank" rel="noopener" aria-label="Partilhar no LinkedIn"><i class="mdi mdi-linkedin"></i></a>
          <button type="button" data-copiar="<?= htmlspecialchars($urlPartilha) ?>" aria-label="Copiar link"><i class="mdi mdi-link-variant"></i></button>
        </div>

        <div class="article-cta">
          <div>
            <h2>Precisa de ajuda com este assunto?</h2>
            <p>A nossa equipa trata do processo por si, com atendimento próximo e preço claro desde o início.</p>
          </div>
          <a href="<?= htmlspecialchars(\App\Models\ConfiguracaoSite::linkWhatsapp('Olá! Li o artigo "' . $artigo['titulo'] . '" e gostaria de mais informações.')) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> Falar no WhatsApp</a>
        </div>
      </div>
    </article>

    <?php if (!empty($relacionados)): ?>
    <section class="section section-soft section-tight">
      <div class="container">
        <div class="section-head section-head-split">
          <div>
            <span class="eyebrow">Continue a ler</span>
            <h2 class="section-title">Outras publicações</h2>
          </div>
          <div class="section-head-action">
            <a href="<?= BASE ?>/blog" class="btn btn-outline">Ver o blog <i class="mdi mdi-arrow-right"></i></a>
          </div>
        </div>
        <div class="posts-grid">
          <?php foreach ($relacionados as $artigo): ?>
            <?php require __DIR__ . '/_cartao.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
