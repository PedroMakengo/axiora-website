<?php use Core\Auth; use Core\Permissoes; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($seo['titulo']) ?></title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0b1f33">
<meta name="csrf-token" content="<?= \Core\Csrf::token() ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE ?>/assets/images/favicon-32x32.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<link rel="stylesheet" href="<?= BASE ?>/assets/vendor/quill/quill.snow.css?v=2.0.3">
<link rel="stylesheet" href="<?= BASE ?>/assets/css/admin.css?v=2">
<script>window.__BASE__ = <?= json_encode(BASE) ?>;</script>
</head>
<body class="admin-body">

<?php
$paginaAtual = $paginaAtual ?? '';

// Menu agrupado por secção — cada item só aparece se o utilizador tiver "ver" no módulo.
$gruposAdmin = [
    'Principal' => [
        'dashboard' => ['label' => 'Dashboard', 'href' => '/admin', 'icon' => 'grid'],
    ],
    'Blog' => [
        'blog-artigos'    => ['label' => 'Artigos',    'href' => '/admin/blog',            'icon' => 'news', 'modulo' => 'blog'],
        'blog-categorias' => ['label' => 'Categorias', 'href' => '/admin/blog/categorias', 'icon' => 'tag',  'modulo' => 'blog'],
    ],
    'Conteúdo do site' => [
        'conteudo-secoes'      => ['label' => 'Secções do site', 'href' => '/admin/conteudo/secoes',      'icon' => 'sections', 'modulo' => 'conteudo'],
        'conteudo-slider'      => ['label' => 'Slider',      'href' => '/admin/conteudo/slider',      'icon' => 'layout', 'modulo' => 'conteudo'],
        'conteudo-servicos'    => ['label' => 'Serviços',    'href' => '/admin/conteudo/servicos',    'icon' => 'briefcase', 'modulo' => 'conteudo'],
        'conteudo-testemunhos' => ['label' => 'Testemunhos', 'href' => '/admin/conteudo/testemunhos', 'icon' => 'quote', 'modulo' => 'conteudo'],
    ],
    'Administração' => [
        'utilizadores' => ['label' => 'Utilizadores', 'href' => '/admin/utilizadores', 'icon' => 'users'],
        'definicoes'   => ['label' => 'Definições',   'href' => '/admin/definicoes',   'icon' => 'cog'],
    ],
];

$iconesAdmin = [
    'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'news'      => '<path d="M4 4h13a1 1 0 0 1 1 1v14a2 2 0 0 0 2 2H6a2 2 0 0 1-2-2V4Z"/><path d="M18 9h2a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2"/><path d="M8 8h6M8 12h6M8 16h4"/>',
    'tag'       => '<path d="M12.6 2H4a2 2 0 0 0-2 2v8.6a2 2 0 0 0 .6 1.4l9.4 9.4a2 2 0 0 0 2.8 0l7.4-7.4a2 2 0 0 0 0-2.8L12.6 2Z"/><circle cx="8" cy="8" r="1.5" fill="currentColor" stroke="none"/>',
    'sections'  => '<rect x="3" y="3" width="18" height="5" rx="1.5"/><rect x="3" y="10" width="18" height="5" rx="1.5"/><rect x="3" y="17" width="18" height="4" rx="1.5"/>',
    'layout'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
    'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    'quote'     => '<path d="M9 7H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h2v2a2 2 0 0 1-2 2"/><path d="M19 7h-4a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h2v2a2 2 0 0 1-2 2"/>',
    'users'     => '<circle cx="9" cy="7" r="3.2"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16.5 8.2a3.2 3.2 0 1 1 0-6.3"/><path d="M15.5 14.3A6.5 6.5 0 0 1 21.5 20"/>',
    'cog'       => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
];

$utilizadorAtual = Auth::utilizador();
$iniciaisUtilizador = '';
if ($utilizadorAtual) {
    $partesNome = preg_split('/\s+/', trim($utilizadorAtual['nome']));
    $iniciaisUtilizador = mb_strtoupper(mb_substr($partesNome[0] ?? '', 0, 1) . mb_substr(count($partesNome) > 1 ? end($partesNome) : '', 0, 1));
    $dadosUtilizadorAtual = (new \App\Models\Utilizador())->porId((int) $utilizadorAtual['id']);
    $utilizadorAtual['avatar'] = $dadosUtilizadorAtual['avatar'] ?? null;
    $utilizadorAtual['telefone'] = $dadosUtilizadorAtual['telefone'] ?? '';
}
?>

<div class="admin-shell">
    <div class="admin-sidebar-overlay" id="admin-sidebar-overlay"></div>
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar__brand">
            <a href="<?= BASE ?>/admin" aria-label="Painel Axiora">
                <img src="<?= BASE ?>/assets/images/logo.png" alt="Axiora" class="admin-sidebar__logo-full">
                <img src="<?= BASE ?>/assets/images/apple-touch-icon.png" alt="Axiora" class="admin-sidebar__logo-icon">
            </a>
        </div>

        <nav class="admin-sidebar__nav" aria-label="Secções do painel">
            <?php foreach ($gruposAdmin as $grupoNome => $itensMenu): ?>
            <?php
            $itensVisiveis = array_filter($itensMenu, function ($abaMenu, $chaveMenu) {
                return Permissoes::tem($abaMenu['modulo'] ?? $chaveMenu, 'ver');
            }, ARRAY_FILTER_USE_BOTH);
            if (empty($itensVisiveis)) continue;
            $grupoTemAtivo = array_key_exists($paginaAtual, $itensVisiveis);
            $chaveGrupo = 'g-' . preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($grupoNome));
            ?>
            <?php if (count($itensVisiveis) === 1 && count($itensMenu) === 1): ?>
                <?php foreach ($itensVisiveis as $chaveMenu => $abaMenu): ?>
                <a href="<?= BASE . $abaMenu['href'] ?>" class="admin-sidebar__link<?= $paginaAtual === $chaveMenu ? ' is-active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $iconesAdmin[$abaMenu['icon']] ?></svg>
                    <span class="admin-sidebar__label"><?= htmlspecialchars($abaMenu['label']) ?></span>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="admin-sidebar__group<?= $grupoTemAtivo ? ' is-open' : '' ?>" data-group="<?= $chaveGrupo ?>">
                    <button type="button" class="admin-sidebar__group-toggle">
                        <span class="admin-sidebar__label"><?= htmlspecialchars($grupoNome) ?></span>
                        <svg class="admin-sidebar__group-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
                    </button>
                    <div class="admin-sidebar__group-items">
                        <?php foreach ($itensVisiveis as $chaveMenu => $abaMenu): ?>
                        <a href="<?= BASE . $abaMenu['href'] ?>" class="admin-sidebar__link<?= $paginaAtual === $chaveMenu ? ' is-active' : '' ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $iconesAdmin[$abaMenu['icon']] ?></svg>
                            <span class="admin-sidebar__label"><?= htmlspecialchars($abaMenu['label']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <?php if (Permissoes::tem('blog', 'criar')): ?>
        <div class="admin-sidebar__rodape">
            <a href="<?= BASE ?>/admin/blog/novo" class="admin-sidebar__link" style="background:rgba(255,255,255,.06)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                <span class="admin-sidebar__label">Novo artigo</span>
            </a>
        </div>
        <?php endif; ?>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" id="btn-admin-menu" class="admin-topbar__icon-btn admin-topbar__icon-btn--mobile" aria-label="Abrir menu" aria-expanded="false" aria-controls="admin-sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <button type="button" id="btn-admin-colapsar" class="admin-topbar__icon-btn admin-topbar__icon-btn--desktop" aria-label="Recolher menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="9" y1="4" x2="9" y2="20"/></svg>
            </button>
            <span class="admin-topbar__titulo">Painel Administrativo</span>

            <div class="admin-topbar__spacer"></div>

            <a href="<?= BASE ?>/" target="_blank" rel="noopener" class="admin-topbar__site">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                Ver o site
            </a>

            <?php if ($utilizadorAtual): ?>
            <div class="admin-user-menu">
                <button type="button" id="btn-user-menu" class="admin-user-menu__toggle" aria-haspopup="true" aria-expanded="false">
                    <?php if (!empty($utilizadorAtual['avatar'])): ?>
                    <img src="<?= BASE . '/' . htmlspecialchars($utilizadorAtual['avatar']) ?>" alt="" class="admin-user-menu__avatar admin-user-menu__avatar--img">
                    <?php else: ?>
                    <span class="admin-user-menu__avatar"><?= htmlspecialchars($iniciaisUtilizador) ?></span>
                    <?php endif; ?>
                    <span class="admin-user-menu__nome"><?= htmlspecialchars(explode(' ', $utilizadorAtual['nome'])[0]) ?></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div id="menu-user" class="admin-user-menu__dropdown hidden">
                    <button type="button" class="admin-user-menu__item" data-sheet-open="sheet-meu-perfil">Editar perfil</button>
                    <?php if (Permissoes::tem('definicoes', 'ver')): ?>
                    <a href="<?= BASE ?>/admin/definicoes" class="admin-user-menu__item">Definições</a>
                    <?php endif; ?>
                    <div class="admin-user-menu__divisor"></div>
                    <a href="<?= BASE ?>/" class="admin-user-menu__item" target="_blank" rel="noopener">Ver o site</a>
                    <a href="<?= BASE ?>/logout?t=<?= \Core\Csrf::token() ?>" class="admin-user-menu__item admin-user-menu__item--sair">Terminar sessão</a>
                </div>
            </div>
            <?php endif; ?>
        </header>

        <main id="conteudo" class="admin-conteudo">
