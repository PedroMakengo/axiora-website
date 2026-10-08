<?php
use App\Models\ConfiguracaoSite;

$cfg = ConfiguracaoSite::publicas();
$paginaAtual = $paginaAtual ?? '';
$naHome = $paginaAtual === 'inicio';
// Na homepage as âncoras ficam relativas (scroll suave + destaque da secção actual);
// nas outras páginas levam de volta à homepage.
$ancora = static fn (string $id): string => ($naHome ? '' : BASE . '/') . '#' . $id;
$waGeral = ConfiguracaoSite::linkWhatsapp();
$telLink = ConfiguracaoSite::telefoneLink();
$telCurto = preg_replace('/^\+?244\s*/', '', (string) $cfg['telefone']);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($seo['titulo']) ?></title>

  <meta name="description" content="<?= htmlspecialchars($seo['descricao']) ?>" />
  <meta name="author" content="Axiora" />
  <meta name="robots" content="<?= htmlspecialchars($seo['robots']) ?>" />
  <meta name="theme-color" content="#0b1f33" />
  <link rel="canonical" href="<?= htmlspecialchars($seo['canonical']) ?>" />

  <meta property="og:type" content="<?= htmlspecialchars($seo['tipo']) ?>" />
  <meta property="og:url" content="<?= htmlspecialchars($seo['canonical']) ?>" />
  <meta property="og:site_name" content="Axiora" />
  <meta property="og:title" content="<?= htmlspecialchars($seo['titulo']) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($seo['descricao']) ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($seo['imagem']) ?>" />
  <meta property="og:locale" content="pt_PT" />
  <?php if (!empty($seo['publicado'])): ?>
  <meta property="article:published_time" content="<?= htmlspecialchars($seo['publicado']) ?>" />
  <?php endif; ?>

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= htmlspecialchars($seo['titulo']) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars($seo['descricao']) ?>" />
  <meta name="twitter:image" content="<?= htmlspecialchars($seo['imagem']) ?>" />

  <link rel="icon" href="<?= BASE ?>/favicon.ico" />
  <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE ?>/assets/images/favicon-32x32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE ?>/assets/images/favicon-16x16.png" />
  <link rel="apple-touch-icon" href="<?= BASE ?>/assets/images/apple-touch-icon.png" />
  <link rel="manifest" href="<?= BASE ?>/assets/images/site.webmanifest" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" />
  <?php if (!empty($seo['preload'])): ?>
  <link rel="preload" as="image" href="<?= BASE . '/' . htmlspecialchars($seo['preload']) ?>" />
  <?php endif; ?>
  <link rel="stylesheet" href="<?= BASE ?>/assets/css/main.css?v=4" />

  <?php if (!empty($seo['jsonld'])): ?>
  <script type="application/ld+json"><?= json_encode($seo['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
  <?php endif; ?>
</head>

<body class="<?= $naHome ? 'is-home' : 'is-interna' ?>">

  <a class="skip-link" href="#conteudo">Saltar para o conteúdo</a>

  <!-- ─── TOPBAR ─── -->
  <div class="topbar">
    <div class="container topbar-inner">
      <ul class="topbar-info">
        <li><i class="mdi mdi-map-marker-outline"></i> <?= htmlspecialchars($cfg['endereco_curto']) ?></li>
        <li><i class="mdi mdi-clock-outline"></i> <?= htmlspecialchars($cfg['horario']) ?></li>
      </ul>
      <ul class="topbar-info">
        <li><a href="mailto:<?= htmlspecialchars($cfg['email']) ?>"><i class="mdi mdi-email-outline"></i> <?= htmlspecialchars($cfg['email']) ?></a></li>
        <li><a href="tel:<?= htmlspecialchars($telLink) ?>"><i class="mdi mdi-phone-outline"></i> <?= htmlspecialchars($cfg['telefone']) ?></a></li>
      </ul>
    </div>
  </div>

  <!-- ─── HEADER ─── -->
  <header class="header" id="header">
    <div class="container header-inner">
      <a href="<?= BASE ?>/" class="brand" aria-label="Axiora — página inicial">
        <img src="<?= BASE ?>/assets/images/logo.png" alt="<?= htmlspecialchars(NOME_EMPRESA) ?>" width="608" height="236" />
      </a>

      <nav class="nav" id="nav" aria-label="Navegação principal">
        <ul class="nav-list">
          <li class="nav-item has-sub">
            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="sub-quem-somos">
              Quem somos <i class="mdi mdi-chevron-down" aria-hidden="true"></i>
            </button>
            <ul class="nav-sub" id="sub-quem-somos">
              <li><a href="<?= $ancora('servicos') ?>"><i class="mdi mdi-briefcase-outline"></i> Serviços</a></li>
              <li><a href="<?= $ancora('como-funciona') ?>"><i class="mdi mdi-format-list-numbered"></i> Como funciona</a></li>
              <li><a href="<?= $ancora('sobre') ?>"><i class="mdi mdi-account-group-outline"></i> Sobre nós</a></li>
            </ul>
          </li>
          <li><a href="<?= BASE ?>/blog"<?= $paginaAtual === 'blog' ? ' class="is-current" aria-current="page"' : '' ?>>Blog</a></li>
          <li><a href="<?= $ancora('websites') ?>">Websites</a></li>
          <li><a href="<?= $ancora('contacto') ?>">Contacto</a></li>
        </ul>
        <div class="nav-mobile-extra">
          <a href="tel:<?= htmlspecialchars($telLink) ?>" class="btn btn-ghost-dark"><i class="mdi mdi-phone"></i> <?= htmlspecialchars($cfg['telefone']) ?></a>
          <a href="<?= htmlspecialchars($waGeral) ?>" class="btn btn-primary" target="_blank" rel="noopener"><i class="mdi mdi-whatsapp"></i> Falar no WhatsApp</a>
        </div>
      </nav>

      <div class="header-actions">
        <a href="tel:<?= htmlspecialchars($telLink) ?>" class="header-phone">
          <span class="header-phone-icon"><i class="mdi mdi-phone"></i></span>
          <span><small>Ligue-nos</small><?= htmlspecialchars($telCurto) ?></span>
        </a>
        <a href="<?= htmlspecialchars($waGeral) ?>" class="btn btn-primary header-cta" target="_blank" rel="noopener">Pedir atendimento</a>
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="nav">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>

  <main id="conteudo">
