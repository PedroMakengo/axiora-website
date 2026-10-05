<?php
use App\Models\Artigo;
use App\Models\ConfiguracaoSite;
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
            <p class="hero-text"><?= htmlspecialchars($slide['texto']) ?></p>
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

    <!-- ─── TRUST STRIP ─── -->
    <section class="trust" aria-label="Porque confiar na Axiora">
      <div class="container">
        <ul class="trust-grid">
          <li>
            <span class="trust-icon"><i class="mdi mdi-storefront-outline"></i></span>
            <div><strong>Atendimento presencial</strong><span>Escritório em <?= htmlspecialchars($cfg['endereco_curto']) ?></span></div>
          </li>
          <li>
            <span class="trust-icon"><i class="mdi mdi-whatsapp"></i></span>
            <div><strong>Resposta rápida</strong><span>Fale connosco directamente no WhatsApp</span></div>
          </li>
          <li>
            <span class="trust-icon"><i class="mdi mdi-file-document-check-outline"></i></span>
            <div><strong>Preço claro à partida</strong><span>Sabe quanto paga antes de começarmos</span></div>
          </li>
          <li>
            <span class="trust-icon"><i class="mdi mdi-shield-check-outline"></i></span>
            <div><strong>Empresa registada</strong><span><?= htmlspecialchars(NOME_EMPRESA) ?></span></div>
          </li>
        </ul>
      </div>
    </section>

    <!-- ─── SERVIÇOS ─── -->
    <section class="section" id="servicos">
      <div class="container">
        <div class="section-head section-head-split">
          <div>
            <span class="eyebrow reveal">Os nossos serviços</span>
            <h2 class="section-title reveal">Serviços essenciais, tratados por quem conhece Luanda.</h2>
          </div>
          <p class="section-lead reveal">Escolha o serviço, envie-nos uma mensagem e tratamos do resto. Sem idas e
            voltas desnecessárias — acompanhamos cada passo consigo.</p>
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
              <p><?= htmlspecialchars($servico['descricao']) ?></p>
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp($servico['mensagem_whatsapp'])) ?>" class="service-link" target="_blank" rel="noopener">Pedir pelo WhatsApp <i class="mdi mdi-arrow-right"></i></a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ─── COMO FUNCIONA ─── -->
    <section class="section section-dark" id="como-funciona">
      <div class="container">
        <div class="section-head section-head-center">
          <span class="eyebrow eyebrow-light reveal">Como funciona</span>
          <h2 class="section-title reveal">Do primeiro contacto ao assunto resolvido, em 4 passos.</h2>
        </div>

        <ol class="steps">
          <li class="step reveal">
            <span class="step-num">01</span>
            <span class="step-icon"><i class="mdi mdi-message-text-outline"></i></span>
            <h3>Fale connosco</h3>
            <p>Envie uma mensagem no WhatsApp, ligue ou visite o nosso escritório em São Paulo.</p>
          </li>
          <li class="step reveal">
            <span class="step-num">02</span>
            <span class="step-icon"><i class="mdi mdi-clipboard-text-search-outline"></i></span>
            <h3>Analisamos o seu caso</h3>
            <p>Explicamos o que é preciso, os prazos e o custo — tudo claro antes de avançar.</p>
          </li>
          <li class="step reveal">
            <span class="step-num">03</span>
            <span class="step-icon"><i class="mdi mdi-cog-outline"></i></span>
            <h3>Tratamos de tudo</h3>
            <p>A nossa equipa executa o serviço e mantém-no informado em cada etapa.</p>
          </li>
          <li class="step reveal">
            <span class="step-num">04</span>
            <span class="step-icon"><i class="mdi mdi-check-decagram-outline"></i></span>
            <h3>Assunto resolvido</h3>
            <p>Entregamos o resultado e continuamos disponíveis para o que precisar a seguir.</p>
          </li>
        </ol>

        <div class="steps-cta reveal">
          <a href="<?= htmlspecialchars($waGeral) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> Começar agora</a>
          <span><?= htmlspecialchars($cfg['horario']) ?></span>
        </div>
      </div>
    </section>

    <!-- ─── SOBRE NÓS ─── -->
    <section class="section" id="sobre">
      <div class="container about">
        <div class="about-media reveal">
          <img src="<?= BASE ?>/assets/images/banner.webp" alt="Equipa Axiora em Luanda" loading="lazy" class="about-img" />
          <div class="about-card">
            <div class="about-team">
              <img src="<?= BASE ?>/assets/images/membros/1.jpeg" alt="Membro da equipa Axiora" loading="lazy" />
              <img src="<?= BASE ?>/assets/images/membros/2.jpeg" alt="Membro da equipa Axiora" loading="lazy" />
            </div>
            <div>
              <strong>Equipa local</strong>
              <span>Atendimento feito por pessoas, não por robôs.</span>
            </div>
          </div>
        </div>

        <div class="about-text">
          <span class="eyebrow reveal">Sobre a Axiora</span>
          <h2 class="section-title reveal">Uma empresa angolana feita para facilitar a sua vida.</h2>
          <p class="reveal">A <strong><?= htmlspecialchars(NOME_EMPRESA) ?></strong> nasceu em Luanda com
            uma missão clara: oferecer soluções práticas e confiáveis que facilitam a vida e os negócios dos nossos
            clientes. Num único lugar, encontra assessoria de viagens, gestão de viaturas, serviços técnicos e apoio
            logístico.</p>
          <p class="reveal">O nosso diferencial está no atendimento próximo, na orientação clara e no foco em soluções
            rápidas. Para nós, <strong>qualidade e transparência</strong> são o mínimo que cada cliente merece.</p>

          <div class="mvv reveal">
            <div class="mvv-tabs" role="tablist">
              <button class="mvv-tab is-active" role="tab" aria-selected="true" data-tab="missao">Missão</button>
              <button class="mvv-tab" role="tab" aria-selected="false" data-tab="visao">Visão</button>
              <button class="mvv-tab" role="tab" aria-selected="false" data-tab="valores">Valores</button>
            </div>
            <div class="mvv-panel is-active" role="tabpanel" data-panel="missao">
              <p>Prestar serviços com qualidade, transparência e eficiência, oferecendo soluções práticas em assessoria
                de viagens, gestão de viaturas, serviços técnicos e apoio logístico.</p>
            </div>
            <div class="mvv-panel" role="tabpanel" data-panel="visao" hidden>
              <p>Ser uma empresa de referência em Angola na prestação de serviços e comercialização, reconhecida pela
                confiança, qualidade, profissionalismo e pelos resultados que gera para os clientes.</p>
            </div>
            <div class="mvv-panel" role="tabpanel" data-panel="valores" hidden>
              <ul class="values">
                <li>Integridade</li>
                <li>Responsabilidade</li>
                <li>Profissionalismo</li>
                <li>Transparência</li>
                <li>Compromisso com o cliente</li>
                <li>Qualidade nos serviços</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── PORQUÊ NÓS ─── -->
    <section class="section section-soft">
      <div class="container why">
        <div class="why-text">
          <span class="eyebrow reveal">Porquê escolher-nos</span>
          <h2 class="section-title reveal">O que faz os nossos clientes voltarem.</h2>

          <ul class="why-list">
            <li class="reveal">
              <i class="mdi mdi-view-grid-outline"></i>
              <div>
                <strong>Vários serviços, um só contacto</strong>
                <p>Da assessoria de viagens à manutenção técnica — resolve tudo sem andar de um lado para o outro.</p>
              </div>
            </li>
            <li class="reveal">
              <i class="mdi mdi-account-heart-outline"></i>
              <div>
                <strong>Atendimento próximo e personalizado</strong>
                <p>Orientamos com clareza, passo a passo, até o processo estar concluído.</p>
              </div>
            </li>
            <li class="reveal">
              <i class="mdi mdi-timer-sand"></i>
              <div>
                <strong>Rapidez sem burocracia</strong>
                <p>Valorizamos o seu tempo. Soluções ágeis e sem etapas desnecessárias.</p>
              </div>
            </li>
            <li class="reveal">
              <i class="mdi mdi-eye-outline"></i>
              <div>
                <strong>Transparência total</strong>
                <p>Informação clara sobre preços, prazos e processos. Sem surpresas nem letras pequenas.</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="why-media reveal">
          <img src="<?= BASE ?>/assets/images/equipa-reuniao.webp" alt="Reunião de trabalho da equipa" loading="lazy" />
          <div class="why-audience">
            <span class="why-audience-title">Quem servimos</span>
            <ul>
              <li><i class="mdi mdi-account-group-outline"></i> Particulares &amp; famílias</li>
              <li><i class="mdi mdi-briefcase-outline"></i> Profissionais independentes</li>
              <li><i class="mdi mdi-office-building-outline"></i> Pequenas &amp; médias empresas</li>
              <li><i class="mdi mdi-car-outline"></i> Proprietários de viaturas</li>
              <li><i class="mdi mdi-earth"></i> Operações internacionais</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($testemunhos)): ?>
    <!-- ─── TESTEMUNHOS ─── -->
    <section class="section" id="testemunhos">
      <div class="container">
        <div class="section-head section-head-center">
          <span class="eyebrow reveal">Testemunhos</span>
          <h2 class="section-title reveal">O que dizem os nossos clientes.</h2>
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
            <blockquote>“<?= htmlspecialchars($testemunho['texto']) ?>”</blockquote>
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

    <?php if (!empty($artigos)): ?>
    <!-- ─── BLOG / NOTÍCIAS ─── -->
    <section class="section section-soft" id="noticias">
      <div class="container">
        <div class="section-head section-head-split">
          <div>
            <span class="eyebrow reveal">Blog &amp; Notícias</span>
            <h2 class="section-title reveal">Novidades, comunicados e dicas práticas.</h2>
          </div>
          <div class="section-head-action reveal">
            <p class="section-lead">Acompanhe o que há de novo na Axiora e aprenda a tratar dos seus assuntos com menos burocracia.</p>
            <a href="<?= BASE ?>/blog" class="btn btn-outline">Ver todas as publicações <i class="mdi mdi-arrow-right"></i></a>
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

    <!-- ─── WEBSITES ─── -->
    <section class="section section-tight" id="websites">
      <div class="container">
        <div class="promo reveal">
          <div class="promo-text">
            <span class="eyebrow eyebrow-light">Serviço digital</span>
            <h2>O seu negócio também merece um website profissional.</h2>
            <p>Criamos websites modernos para empresas que querem ser encontradas no Google e transmitir confiança
              online.</p>
            <span class="promo-includes">Incluído em todos os planos</span>
            <ul class="promo-list">
              <li><i class="mdi mdi-check"></i> Design personalizado</li>
              <li><i class="mdi mdi-check"></i> Adaptado a telemóveis</li>
              <li><i class="mdi mdi-check"></i> SEO para Google incluído</li>
              <li><i class="mdi mdi-check"></i> Integração com WhatsApp</li>
              <li><i class="mdi mdi-check"></i> Suporte após o lançamento</li>
            </ul>
          </div>

          <div class="plans">
            <div class="plan">
              <span class="plan-name">Website</span>
              <div class="plan-price">169.000 <small>Kz</small></div>
              <span class="plan-note">Website completo e funcional</span>
              <ul class="plan-list">
                <li><i class="mdi mdi-check"></i> Site institucional completo</li>
                <li><i class="mdi mdi-check"></i> Entrega em 2 semanas</li>
              </ul>
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp('Olá! Quero saber mais sobre o plano Website (169.000 Kz).')) ?>" class="btn btn-outline btn-block" target="_blank" rel="noopener">Pedir orçamento</a>
            </div>

            <div class="plan plan-featured">
              <span class="plan-badge">Mais completo</span>
              <span class="plan-name">Website + Blog</span>
              <div class="plan-price">259.000 <small>Kz</small></div>
              <span class="plan-note">Com blog e gestão de conteúdos</span>
              <ul class="plan-list">
                <li><i class="mdi mdi-check"></i> Tudo do plano Website</li>
                <li><i class="mdi mdi-check"></i> Blog para publicar artigos</li>
                <li><i class="mdi mdi-check"></i> Gestão de conteúdos: edite textos e imagens sem programar</li>
              </ul>
              <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp('Olá! Quero saber mais sobre o plano Website + Blog (259.000 Kz).')) ?>" class="btn btn-primary btn-block" target="_blank" rel="noopener">Pedir orçamento</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── CONTACTO ─── -->
    <section class="section section-soft" id="contacto">
      <div class="container contact">
        <div class="contact-info">
          <span class="eyebrow reveal">Contacto</span>
          <h2 class="section-title reveal">Venha falar connosco ou envie uma mensagem.</h2>

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
            <a href="<?= htmlspecialchars(ConfiguracaoSite::linkWhatsapp('Olá! Gostaria de mais informações sobre os vossos serviços.')) ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> WhatsApp</a>
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
