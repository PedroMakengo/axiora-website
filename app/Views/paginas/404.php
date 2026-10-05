<?php
// Página 404 — chamada directamente pelo Router, antes do layout normal.
$erroCodigo   = '404';
$erroTitulo   = 'Página não encontrada';
$erroMensagem = 'O endereço que introduziu pode ter mudado, ter um erro de escrita ou já não existir. Use os atalhos abaixo para continuar.';
require __DIR__ . '/_erro.php';
