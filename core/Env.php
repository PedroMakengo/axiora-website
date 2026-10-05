<?php
// =====================================================================
// core/Env.php — Leitor mínimo de ficheiros .env (sem Composer).
//
// Procura primeiro um .env FORA da pasta pública (um nível acima do
// projecto) e só depois na raiz do projecto. Em produção, recomenda-se
// guardar o .env fora do public_html; se ficar na raiz, o .htaccess
// bloqueia o acesso directo.
// =====================================================================

function carregarEnv(string $raiz): void
{
    $candidatos = [dirname($raiz) . '/.env', $raiz . '/.env'];

    foreach ($candidatos as $ficheiro) {
        if (!is_file($ficheiro) || !is_readable($ficheiro)) {
            continue;
        }

        foreach (file($ficheiro, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
            $linha = trim($linha);
            if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
                continue;
            }

            [$chave, $valor] = array_map('trim', explode('=', $linha, 2));
            $tamanho = strlen($valor);
            if ($tamanho >= 2 && ($valor[0] === '"' || $valor[0] === "'") && $valor[$tamanho - 1] === $valor[0]) {
                $valor = substr($valor, 1, -1);
            }

            // Variáveis já definidas no servidor (painel de alojamento) têm prioridade.
            if (getenv($chave) === false && !isset($_ENV[$chave])) {
                $_ENV[$chave] = $valor;
            }
        }
        return;
    }
}

/**
 * Lê uma variável de ambiente, com valor por omissão.
 */
function env(string $chave, $omissao = null)
{
    $valor = $_ENV[$chave] ?? getenv($chave);
    if ($valor === false || $valor === null) {
        return $omissao;
    }

    switch (strtolower((string) $valor)) {
        case 'true':  return true;
        case 'false': return false;
        case 'null':  return null;
    }
    return $valor;
}
