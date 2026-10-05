<?php

namespace Core;

// =====================================================================
// Core\Router — Router central do projecto.
// Cada rota liga um padrão de URL a [Controlador, método].
// Suporta parâmetros dinâmicos com {slug}, {id}, etc.
// =====================================================================

class Router
{
    private array $rotas = [];

    public function get(string $padrao, array $destino): void
    {
        $this->registar('GET', $padrao, $destino);
    }

    public function post(string $padrao, array $destino): void
    {
        $this->registar('POST', $padrao, $destino);
    }

    private function registar(string $metodo, string $padrao, array $destino): void
    {
        $this->rotas[] = [
            'metodo'  => $metodo,
            'padrao'  => $padrao,
            'destino' => $destino,
        ];
    }

    public function despachar(string $uri, string $metodo): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri = rawurldecode($uri);

        // Remove a subpasta base (ex.: "/axiora-website") para as rotas
        // ficarem sempre relativas à raiz da aplicação.
        if (defined('BASE') && BASE !== '' && str_starts_with($uri, BASE)) {
            $uri = substr($uri, strlen(BASE));
        }

        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->rotas as $rota) {
            if ($rota['metodo'] !== $metodo) {
                continue;
            }

            $regex = $this->paraRegex($rota['padrao']);
            if (preg_match($regex, $uri, $parametros)) {
                array_shift($parametros);
                [$classe, $acao] = $rota['destino'];

                // Aceita tanto o nome curto ("HomeController") como o nome
                // completo com namespace (HomeController::class).
                $classeCompleta = str_contains($classe, '\\')
                    ? $classe
                    : "App\\Controllers\\{$classe}";
                if (!class_exists($classeCompleta)) {
                    $this->erro404();
                    return;
                }

                $controlador = new $classeCompleta();
                if (!method_exists($controlador, $acao)) {
                    $this->erro404();
                    return;
                }

                call_user_func_array([$controlador, $acao], array_values($parametros));
                return;
            }
        }

        $this->erro404();
    }

    private function paraRegex(string $padrao): string
    {
        // {id} só aceita dígitos (evita TypeError/500 com IDs inválidos); os
        // restantes parâmetros (slug, token, código) aceitam letras, números e hífen.
        $padrao = str_replace('{id}', '([0-9]{1,10})', $padrao);
        $padrao = preg_replace('#\{[a-zA-Z_]+\}#', '([a-zA-Z0-9\-]{1,150})', $padrao);
        return '#^' . $padrao . '$#';
    }

    private function erro404(): void
    {
        http_response_code(404);
        require CAMINHO_RAIZ . '/app/Views/paginas/404.php';
    }
}
