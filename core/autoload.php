<?php
// =====================================================================
// core/autoload.php — Autoload manual (sem Composer)
// Mapeia namespaces App\Controllers, App\Models, Core para pastas.
// =====================================================================

spl_autoload_register(function ($classe) {
    $mapa = [
        'App\\Controllers\\' => CAMINHO_RAIZ . '/app/Controllers/',
        'App\\Models\\'      => CAMINHO_RAIZ . '/app/Models/',
        'App\\Services\\'    => CAMINHO_RAIZ . '/app/Services/',
        'Core\\'             => CAMINHO_RAIZ . '/core/',
    ];

    foreach ($mapa as $prefixo => $pasta) {
        if (strncmp($prefixo, $classe, strlen($prefixo)) === 0) {
            $classeRelativa = substr($classe, strlen($prefixo));
            $caminho = $pasta . str_replace('\\', '/', $classeRelativa) . '.php';
            if (file_exists($caminho)) {
                require $caminho;
                return;
            }
        }
    }
});
