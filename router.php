<?php
// =====================================================================
// router.php — só é usado quando se corre o servidor embutido do PHP:
//
//     php -S localhost:8000 router.php
//
// (No XAMPP/Apache este ficheiro é ignorado; quem trata das rotas é
//  o .htaccess + index.php.)
// Serve ficheiros estáticos que existam; tudo o resto vai ao front controller.
// =====================================================================

$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$ficheiro = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $caminho);

// Ficheiros privados (comprovativos de pagamento...) nunca são servidos
// directamente — equivalente ao storage/.htaccess do Apache.
if (preg_match('#^/(?:[^/]+/)*storage/#i', $caminho)) {
    http_response_code(403);
    exit;
}

// Mesmas regras do .htaccess: código, configuração e ficheiros ocultos
// nunca são servidos directamente.
if (preg_match('#(^|/)\.#', $caminho)
    || preg_match('#^/(app|config|core|database|docker|src|node_modules|vendor)(/|$)#i', $caminho)
    || preg_match('#\.(php|env|log|sql|md|bat|sh|ini|lock|example|yml)$#i', $caminho)
    || preg_match('#^/(package(-lock)?\.json|tailwind\.config\.js|Dockerfile)$#i', $caminho)) {
    http_response_code(403);
    exit;
}

if ($caminho !== '/' && is_file($ficheiro)) {
    return false; // deixa o servidor embutido servir o ficheiro tal como está
}

require __DIR__ . '/index.php';
