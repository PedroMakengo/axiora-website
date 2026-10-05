<?php
/**
 * Esqueleto partilhado das páginas de erro (404, 403, ...).
 * Espera as variáveis: $erroCodigo, $erroTitulo, $erroMensagem.
 * É incluído directamente pelo Router / Controller, sem o layout normal,
 * por isso traz o seu próprio HTML e estilos.
 */
$base       = defined('BASE') ? BASE : '';
$nomeSite   = defined('NOME_SITE') ? NOME_SITE : 'Axiora — Comércio Geral e Prestação de Serviços';
$erroCodigo   = $erroCodigo   ?? '404';
$erroTitulo   = $erroTitulo   ?? 'Página não encontrada';
$erroMensagem = $erroMensagem ?? 'O conteúdo que procura pode ter sido movido ou já não existe.';
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($erroTitulo) ?> · <?= htmlspecialchars($nomeSite) ?></title>
<meta name="robots" content="noindex, follow">
<link rel="icon" type="image/png" href="<?= htmlspecialchars($base) ?>/assets/images/favicon-32x32.png">
<style>
    :root{
        --navy:#0b1f33; --navy-2:#1c3d63; --gold:#12a596; --gold-light:#5fd9cb;
        --ink:#0e1b2b; --paper:#f4f7fa; --muted:#627285; --line:#e2e8ef;
    }
    *,*::before,*::after{box-sizing:border-box}
    html,body{margin:0;padding:0}
    body{
        min-height:100vh;
        font-family:"Manrope",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
        color:var(--ink);
        background:
            radial-gradient(60rem 40rem at 15% -10%, rgba(11,31,51,.10), transparent 60%),
            radial-gradient(50rem 40rem at 100% 110%, rgba(18,165,150,.12), transparent 55%),
            var(--paper);
        display:flex;align-items:center;justify-content:center;
        padding:2rem 1.25rem;
    }
    .cartao{
        width:100%;max-width:34rem;background:#fff;
        border:1px solid var(--line);border-radius:18px;
        box-shadow:0 24px 60px -20px rgba(11,31,51,.28);
        padding:2.75rem 2.25rem;text-align:center;
    }
    .cartao__logo{height:64px;width:auto;margin:0 auto 1.75rem}
    .cartao__codigo{
        font-family:"Manrope",system-ui,sans-serif;
        font-weight:700;line-height:1;letter-spacing:-.02em;
        font-size:clamp(4rem,18vw,6.5rem);
        color:var(--navy);
        margin:0;
    }
    @supports (-webkit-background-clip:text) or (background-clip:text){
        .cartao__codigo{
            background:linear-gradient(135deg,var(--navy) 0%,var(--navy-2) 45%,var(--gold) 130%);
            -webkit-background-clip:text;background-clip:text;
            -webkit-text-fill-color:transparent;color:transparent;
        }
    }
    .cartao__barra{width:56px;height:4px;border-radius:99px;background:var(--gold);margin:.9rem auto 1.4rem}
    .cartao__titulo{
        font-family:"Manrope",system-ui,sans-serif;
        font-size:1.4rem;font-weight:600;margin:0 0 .5rem;color:var(--ink);
    }
    .cartao__texto{margin:0 auto;max-width:26rem;color:var(--muted);font-size:.975rem;line-height:1.6}
    .cartao__accoes{margin-top:2rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap}
    .btn{
        display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
        font-size:.9rem;font-weight:600;text-decoration:none;
        padding:.8rem 1.5rem;border-radius:11px;transition:transform .12s ease,box-shadow .15s ease,background .15s ease;
    }
    .btn--primario{background:var(--navy);color:#fff;box-shadow:0 8px 20px -8px rgba(11,31,51,.6)}
    .btn--primario:hover{background:var(--navy-2);transform:translateY(-1px)}
    .btn--linha{background:#fff;color:var(--navy);border:1px solid var(--line)}
    .btn--linha:hover{border-color:var(--navy);transform:translateY(-1px)}
    .cartao__links{
        margin-top:1.9rem;padding-top:1.4rem;border-top:1px solid var(--line);
        display:flex;gap:1rem 1.5rem;justify-content:center;flex-wrap:wrap;
        font-size:.85rem;
    }
    .cartao__links a{color:var(--muted);text-decoration:none}
    .cartao__links a:hover{color:var(--navy);text-decoration:underline}
    @media (max-width:420px){ .cartao{padding:2rem 1.4rem} .btn{width:100%} }
</style>
</head>
<body>
    <main class="cartao">
        <img class="cartao__logo" src="<?= htmlspecialchars($base) ?>/assets/images/logo.png" alt="<?= htmlspecialchars($nomeSite) ?>">
        <p class="cartao__codigo"><?= htmlspecialchars($erroCodigo) ?></p>
        <div class="cartao__barra"></div>
        <h1 class="cartao__titulo"><?= htmlspecialchars($erroTitulo) ?></h1>
        <p class="cartao__texto"><?= htmlspecialchars($erroMensagem) ?></p>

        <div class="cartao__accoes">
            <a class="btn btn--primario" href="<?= htmlspecialchars($base) ?>/">Voltar ao início</a>
            <a class="btn btn--linha" href="<?= htmlspecialchars($base) ?>/blog">Ver o blog</a>
        </div>

        <nav class="cartao__links">
            <a href="<?= htmlspecialchars($base) ?>/#servicos">Serviços</a>
            <a href="<?= htmlspecialchars($base) ?>/blog">Blog &amp; Notícias</a>
            <a href="<?= htmlspecialchars($base) ?>/#contacto">Contacto</a>
        </nav>
    </main>
</body>
</html>
