<?php
// =====================================================================
// core/polyfills.php — Compatibilidade com PHP < 8.0
// Alguns servidores cPanel ainda correm PHP 7.x, onde estas funções
// (introduzidas no PHP 8.0) não existem. Carregado antes de tudo o resto.
// =====================================================================

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

// PHP 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $esperado = 0;
        foreach ($array as $chave => $_) {
            if ($chave !== $esperado++) {
                return false;
            }
        }
        return true;
    }
}
