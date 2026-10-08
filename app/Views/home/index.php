<?php
use App\Models\ConfiguracaoSite;
use Core\Html;

// Conteúdo de cada secção vem do CMS (Painel › Conteúdo do site › Secções do site).
$campo = static fn (string $secao, string $nome): string => (string) ($secoes[$secao]['campos'][$nome] ?? '');
$texto = static fn (string $secao, string $nome): string => htmlspecialchars($campo($secao, $nome));   // campo "texto"
$curto = static fn (string $secao, string $nome): string => Html::inline($campo($secao, $nome));      // editor curto
$itens = static fn (string $secao, string $grupo): array => $secoes[$secao]['grupos'][$grupo] ?? [];
$visivel = static fn (string $secao): bool => !empty($secoes[$secao]['visivel']);
$imagem = static fn (string $secao, string $nome): string => BASE . '/' . htmlspecialchars($campo($secao, $nome));
$icone = static fn (?string $nome): string => htmlspecialchars(preg_replace('/[^a-z0-9-]/', '', strtolower((string) $nome)));
?>
    <?php if (!empty($heroSlides)): ?>
    <!-- ─── HERO SLIDER ─── -->
    <section class="hero" id="hero" aria-roledescription="carrossel" aria-label="Serviços em destaque" style="--hero-tabs: <?= count($heroSlides) ?>">
      <div class="hero-slides">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <?php $tagTitulo = $i === 0 ? 'h1' : 'h2'; ?>
        <article class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" aria-roledescription="slide" aria-label="<?= $i + 1 ?> de <?= count($heroSlides) ?>">
          <div class="hero-media" style="background-image:url('<?= BASE . '/' . htmlspecialchars($slide['imagem'], ENT_QUOTES) ?>')"></div>
          <div class="container hero-content">
            <<?= $tagTitulo ?> class="hero-title"><?= htmlspecialchars($slide['titulo']) ?><?php if (!empty($slide['titulo_destaque'])): ?> <span><?= htmlspecialchars($slide['titulo_destaque']) ?></span><?php endif; ?></<?= $tagTitulo ?>>
            <?php if (!empty($slide['texto'])): ?>
            <p class="hero-text"><?= Html::inline($slide['texto']) ?></p>
            <?php endif; ?>
            <div class="hero-actions">
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($slide['mensagem_whatsapp'])) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> <?= htmlspecialchars($slide['botao_texto']) ?></a>
              <a href="#servicos" class="btn btn-ghost btn-lg"><?= $i === 0 ? 'Ver todos os serviços' : 'Saber mais' ?></a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <div class="hero-nav">
        <div class="container hero-nav-inner">
          <div class="hero-tabs" role="tablist" aria-label="Escolher slide">
            <?php foreach ($heroSlides as $i => $slide): ?>
            <button class="hero-tab<?= $i === 0 ? ' is-active' : '' ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-index="<?= $i ?>">
              <span class="hero-tab-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><span class="hero-tab-label"><?= htmlspecialchars($slide['rotulo']) ?></span>
              <span class="hero-tab-bar"><span></span></span>
            </button>
            <?php endforeach; ?>
          </div>
          <div class="hero-arrows">
            <button class="hero-arrow" id="heroPrev" aria-label="Slide anterior"><i class="mdi mdi-arrow-left"></i></button>
            <button class="hero-arrow" id="heroNext" aria-label="Slide seguinte"><i class="mdi mdi-arrow-right"></i></button>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('confianca') && $itens('confianca', 'itens')): ?>
    <!-- ─── TRUST STRIP ─── -->
    <section class="trust" aria-label="Porque confiar na Axiora">
      <div class="container">
        <ul class="trust-grid">
          <?php foreach ($itens('confianca', 'itens') as $item): ?>
          <li>
            <span class="trust-icon"><i class="mdi <?= $icone($item['icone'] ?? '') ?>"></i></span>
            <div><strong><?= htmlspecialchars($item['titulo'] ?? '') ?></strong><span><?= Html::inline($item['texto'] ?? '') ?></span></div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('servicos')): ?>
    <!-- ─── SERVIÇOS ─── -->
    <section class="section" id="servicos">
      <div class="container">
        <div class="section-head section-head-split">
          <div>
            <span class="eyebrow reveal"><?= $texto('servicos', 'eyebrow') ?></span>
            <h2 class="section-title reveal"><?= $texto('servicos', 'titulo') ?></h2>
          </div>
          <p class="section-lead reveal"><?= $curto('servicos', 'texto') ?></p>
        </div>

        <div class="services-grid">
          <?php foreach ($servicos as $servico): ?>
          <article class="service-card reveal">
            <?php if (!empty($servico['imagem'])): ?>
            <div class="service-media">
              <img src="<?= BASE . '/' . htmlspecialchars($servico['imagem']) ?>" alt="<?= htmlspecialchars($servico['imagem_alt'] ?: $servico['titulo']) ?>" loading="lazy" />
            </div>
            <?php endif; ?>
            <div class="service-body">
              <h3><?= htmlspecialchars($servico['titulo']) ?></h3>
              <p><?= Html::inline($servico['descricao']) ?></p>
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($servico['mensagem_whatsapp'])) ?>" class="service-link" target="_blank" rel="noopener">Pedir pelo WhatsApp <i class="mdi mdi-arrow-right"></i></a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('como-funciona')): ?>
    <!-- ─── COMO FUNCIONA ─── -->
    <section class="section section-dark" id="como-funciona">
      <div class="container">
        <div class="section-head section-head-center">
          <span class="eyebrow eyebrow-light reveal"><?= $texto('como-funciona', 'eyebrow') ?></span>
          <h2 class="section-title reveal"><?= $texto('como-funciona', 'titulo') ?></h2>
        </div>

        <ol class="steps">
          <?php foreach ($itens('como-funciona', 'passos') as $i => $passo): ?>
          <li class="step reveal">
            <span class="step-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <span class="step-icon"><i class="mdi <?= $icone($passo['icone'] ?? '') ?>"></i></span>
            <h3><?= htmlspecialchars($passo['titulo'] ?? '') ?></h3>
            <p><?= Html::inline($passo['texto'] ?? '') ?></p>
          </li>
          <?php endforeach; ?>
        </ol>

        <div class="steps-cta reveal">
          <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($campo('como-funciona', 'mensagem_whatsapp') ?: null)) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> <?= $texto('como-funciona', 'botao_texto') ?></a>
          <span><?= htmlspecialchars($campo('como-funciona', 'nota') ?: $cfg['horario']) ?></span>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('sobre')): ?>
    <!-- ─── SOBRE NÓS ─── -->
    <section class="section" id="sobre">
      <div class="container about">
        <div class="about-media reveal">
          <img src="<?= $imagem('sobre', 'imagem') ?>" alt="<?= $texto('sobre', 'imagem_alt') ?>" loading="lazy" class="about-img" />
          <div class="about-card">
            <div class="about-team">
              <?php foreach (['equipa_1', 'equipa_2'] as $foto): ?>
                <?php if ($campo('sobre', $foto) !== ''): ?>
                <img src="<?= $imagem('sobre', $foto) ?>" alt="Membro da equipa Axiora" loading="lazy" />
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
            <div>
              <strong><?= $texto('sobre', 'cartao_titulo') ?></strong>
              <span><?= $texto('sobre', 'cartao_texto') ?></span>
            </div>
          </div>
        </div>

        <div class="about-text">
          <span class="eyebrow reveal"><?= $texto('sobre', 'eyebrow') ?></span>
          <h2 class="section-title reveal"><?= $texto('sobre', 'titulo') ?></h2>
          <div class="texto-rico reveal"><?= Html::bloco($campo('sobre', 'texto')) ?></div>

          <?php $valores = Html::itensLista($campo('sobre', 'valores')); ?>
          <div class="mvv reveal">
            <div class="mvv-tabs" role="tablist">
              <button class="mvv-tab is-active" role="tab" aria-selected="true" data-tab="missao">Missão</button>
              <button class="mvv-tab" role="tab" aria-selected="false" data-tab="visao">Visão</button>
              <?php if ($valores): ?>
              <button class="mvv-tab" role="tab" aria-selected="false" data-tab="valores">Valores</button>
              <?php endif; ?>
            </div>
            <div class="mvv-panel is-active texto-rico" role="tabpanel" data-panel="missao">
              <?= Html::bloco($campo('sobre', 'missao')) ?>
            </div>
            <div class="mvv-panel texto-rico" role="tabpanel" data-panel="visao" hidden>
              <?= Html::bloco($campo('sobre', 'visao')) ?>
            </div>
            <?php if ($valores): ?>
            <div class="mvv-panel" role="tabpanel" data-panel="valores" hidden>
              <ul class="values">
                <?php foreach ($valores as $valor): ?>
                <li><?= $valor ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('porque')): ?>
    <!-- ─── PORQUÊ NÓS ─── -->
    <section class="section section-soft">
      <div class="container why">
        <div class="why-text">
          <span class="eyebrow reveal"><?= $texto('porque', 'eyebrow') ?></span>
          <h2 class="section-title reveal"><?= $texto('porque', 'titulo') ?></h2>

          <ul class="why-list">
            <?php foreach ($itens('porque', 'motivos') as $motivo): ?>
            <li class="reveal">
              <i class="mdi <?= $icone($motivo['icone'] ?? '') ?>"></i>
              <div>
                <strong><?= htmlspecialchars($motivo['titulo'] ?? '') ?></strong>
                <p><?= Html::inline($motivo['texto'] ?? '') ?></p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="why-media reveal">
          <img src="<?= $imagem('porque', 'imagem') ?>" alt="<?= $texto('porque', 'imagem_alt') ?>" loading="lazy" />
          <?php if ($itens('porque', 'publico')): ?>
          <div class="why-audience">
            <span class="why-audience-title"><?= $texto('porque', 'publico_titulo') ?></span>
            <ul>
              <?php foreach ($itens('porque', 'publico') as $publico): ?>
              <li><i class="mdi <?= $icone($publico['icone'] ?? '') ?>"></i> <?= htmlspecialchars($publico['titulo'] ?? '') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('testemunhos') && !empty($testemunhos)): ?>
    <!-- ─── TESTEMUNHOS ─── -->
    <section class="section" id="testemunhos">
      <div class="container">
        <div class="section-head section-head-center">
          <span class="eyebrow reveal"><?= $texto('testemunhos', 'eyebrow') ?></span>
          <h2 class="section-title reveal"><?= $texto('testemunhos', 'titulo') ?></h2>
        </div>

        <div class="testimonials">
          <?php $coresAvatar = ['', ' avatar-teal', ' avatar-gold']; ?>
          <?php foreach ($testemunhos as $i => $testemunho): ?>
          <?php
          $partesNome = preg_split('/\s+/', trim(str_replace('.', '', $testemunho['nome'])));
          $iniciais = mb_strtoupper(mb_substr($partesNome[0] ?? '', 0, 1) . (count($partesNome) > 1 ? mb_substr(end($partesNome), 0, 1) : ''));
          $estrelas = max(1, min(5, (int) $testemunho['estrelas']));
          ?>
          <figure class="testimonial reveal">
            <div class="stars" aria-label="<?= $estrelas ?> de 5 estrelas"><?= str_repeat('<i class="mdi mdi-star"></i>', $estrelas) ?></div>
            <blockquote>“<?= Html::inline($testemunho['texto']) ?>”</blockquote>
            <figcaption>
              <span class="avatar<?= $coresAvatar[$i % 3] ?>"><?= htmlspecialchars($iniciais) ?></span>
              <span><strong><?= htmlspecialchars($testemunho['nome']) ?></strong><?php if (!empty($testemunho['descricao'])): ?><small><?= htmlspecialchars($testemunho['descricao']) ?></small><?php endif; ?></span>
            </figcaption>
          </figure>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('noticias') && !empty($artigos)): ?>
    <!-- ─── BLOG / NOTÍCIAS ─── -->
    <section class="section section-soft" id="noticias">
      <div class="container">
        <div class="section-head section-head-split">
          <div>
            <span class="eyebrow reveal"><?= $texto('noticias', 'eyebrow') ?></span>
            <h2 class="section-title reveal"><?= $texto('noticias', 'titulo') ?></h2>
          </div>
          <div class="section-head-action reveal">
            <p class="section-lead"><?= $curto('noticias', 'texto') ?></p>
            <a href="<?= BASE ?>/blog" class="btn btn-outline"><?= $texto('noticias', 'botao_texto') ?> <i class="mdi mdi-arrow-right"></i></a>
          </div>
        </div>

        <div class="posts-grid">
          <?php foreach ($artigos as $artigo): ?>
            <?php require CAMINHO_RAIZ . '/app/Views/blog/_cartao.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('websites')): ?>
    <!-- ─── WEBSITES ─── -->
    <section class="section section-tight" id="websites">
      <div class="container">
        <div class="promo reveal">
          <div class="promo-text">
            <span class="eyebrow eyebrow-light"><?= $texto('websites', 'eyebrow') ?></span>
            <h2><?= $texto('websites', 'titulo') ?></h2>
            <p><?= $curto('websites', 'texto') ?></p>
            <?php $inclui = Html::itensLista($campo('websites', 'inclui')); ?>
            <?php if ($inclui): ?>
            <span class="promo-includes"><?= $texto('websites', 'inclui_titulo') ?></span>
            <ul class="promo-list">
              <?php foreach ($inclui as $linha): ?>
              <li><i class="mdi mdi-check"></i> <?= $linha ?></li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>

          <div class="plans">
            <?php foreach ($itens('websites', 'planos') as $plano): ?>
            <?php $destaque = !empty($plano['destaque']); ?>
            <div class="plan<?= $destaque ? ' plan-featured' : '' ?>">
              <?php if ($destaque && !empty($plano['selo'])): ?>
              <span class="plan-badge"><?= htmlspecialchars($plano['selo']) ?></span>
              <?php endif; ?>
              <span class="plan-name"><?= htmlspecialchars($plano['titulo'] ?? '') ?></span>
              <?php if (!empty($plano['preco'])): ?>
              <div class="plan-price"><?= htmlspecialchars($plano['preco']) ?> <small><?= htmlspecialchars($plano['moeda'] ?? '') ?></small></div>
              <?php endif; ?>
              <?php if (!empty($plano['nota'])): ?>
              <span class="plan-note"><?= htmlspecialchars($plano['nota']) ?></span>
              <?php endif; ?>
              <ul class="plan-list">
                <?php foreach (Html::itensLista($plano['lista'] ?? '') as $linha): ?>
                <li><i class="mdi mdi-check"></i> <?= $linha ?></li>
                <?php endforeach; ?>
              </ul>
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($plano['mensagem_whatsapp'] ?? null)) ?>" class="btn <?= $destaque ? 'btn-primary' : 'btn-outline' ?> btn-block" target="_blank" rel="noopener"><?= htmlspecialchars(($plano['botao_texto'] ?? '') ?: 'Pedir orçamento') ?></a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($visivel('contacto')): ?>
    <!-- ─── CONTACTO ─── -->
    <section class="section section-soft" id="contacto">
      <div class="container contact">
        <div class="contact-info">
          <span class="eyebrow reveal"><?= $texto('contacto', 'eyebrow') ?></span>
          <h2 class="section-title reveal"><?= $texto('contacto', 'titulo') ?></h2>

          <ul class="contact-list reveal">
            <li>
              <span class="contact-icon"><i class="mdi mdi-map-marker-outline"></i></span>
              <div><small>Endereço</small><?= htmlspecialchars($cfg['endereco']) ?></div>
            </li>
            <li>
              <span class="contact-icon"><i class="mdi mdi-phone-outline"></i></span>
              <div><small>Telefone / WhatsApp</small><a href="tel:<?= htmlspecialchars($telLink) ?>"><?= htmlspecialchars($cfg['telefone']) ?></a></div>
            </li>
            <li>
              <span class="contact-icon"><i class="mdi mdi-email-outline"></i></span>
              <div><small>Email</small><a href="mailto:<?= htmlspecialchars($cfg['email']) ?>"><?= htmlspecialchars($cfg['email']) ?></a></div>
            </li>
            <li>
              <span class="contact-icon"><i class="mdi mdi-clock-outline"></i></span>
              <div><small>Horário</small><?= htmlspecialchars($cfg['horario_completo']) ?></div>
            </li>
          </ul>

          <div class="contact-actions reveal">
            <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($campo('contacto', 'mensagem_whatsapp') ?: null)) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> WhatsApp</a>
            <a href="mailto:<?= htmlspecialchars($cfg['email']) ?>" class="btn btn-outline btn-lg"><i class="mdi mdi-email-outline"></i> Enviar email</a>
          </div>
        </div>

        <?php if (!empty($cfg['mapa_embed'])): ?>
        <div class="contact-map reveal">
          <iframe src="<?= htmlspecialchars($cfg['mapa_embed']) ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Localização da Axiora em Luanda"></iframe>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>
