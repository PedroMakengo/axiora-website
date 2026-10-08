<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($seo['titulo']) ?></title>
<meta name="robots" content="<?= htmlspecialchars($seo['robots']) ?>">
<meta name="theme-color" content="#0b1f33">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE ?>/assets/images/favicon-32x32.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE ?>/assets/css/admin.css?v=2">
<script>window.__BASE__ = <?= json_encode(BASE) ?>;</script>
</head>
<body class="auth-body">
<main class="auth-cartao">
    <a href="<?= BASE ?>/" aria-label="Voltar ao site da Axiora">
        <img src="<?= BASE ?>/assets/images/logo.png" alt="Axiora" class="auth-logo" width="608" height="236">
    </a>
