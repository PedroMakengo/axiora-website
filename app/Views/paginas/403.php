<?php
// Página 403 — acesso não autorizado. Chamada directamente, sem o layout.
$erroCodigo   = '403';
$erroTitulo   = 'Acesso não autorizado';
$erroMensagem = 'A sua conta não tem permissão para aceder a esta página. Se acha que se trata de um engano, contacte o administrador do painel.';
require __DIR__ . '/_erro.php';
