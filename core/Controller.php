<?php

namespace Core;

// =====================================================================
// Core\Controller — classe base de todos os controladores.
// =====================================================================

class Controller
{
    /**
     * Renderiza uma view dentro do layout principal, com dados de SEO.
     */
    protected function view(string $caminho, array $dados = [], array $seo = [], string $layout = 'site'): void
    {
        extract($dados);

        // Valores SEO por omissão, sobrepostos pelos definidos no controlador
        $caminhoPedido = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (BASE !== '' && str_starts_with($caminhoPedido, BASE)) {
            $caminhoPedido = substr($caminhoPedido, strlen(BASE)) ?: '/';
        }

        $seo = array_merge([
            'titulo'      => NOME_SITE,
            'descricao'   => DESCRICAO_SITE,
            'canonical'   => URL_BASE . $caminhoPedido,
            'imagem'      => URL_BASE . '/assets/images/hero/luanda.webp',
            'tipo'        => 'website',
            'robots'      => 'index, follow',
            'jsonld'      => null,
        ], $seo);

        // 'site' → header.php/footer.php; 'admin' → admin-header.php...; 'auth' → auth-header.php...
        $prefixo = $layout === 'site' ? '' : $layout . '-';

        require CAMINHO_RAIZ . '/app/Views/layouts/' . $prefixo . 'header.php';
        require CAMINHO_RAIZ . '/app/Views/' . $caminho . '.php';
        require CAMINHO_RAIZ . '/app/Views/layouts/' . $prefixo . 'footer.php';
    }

    /**
     * Resposta JSON para chamadas AJAX/fetch.
     */
    protected function json(array $dados, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Valida que o pedido é POST + XHR e tem token CSRF válido.
     */
    protected function exigirCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405);
        }

        // Defesa extra: pedidos vindos de outro site (cabeçalho Origin de outro domínio) são recusados.
        $origem = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origem !== '' && $origem !== 'null') {
            $hostOrigem = parse_url($origem, PHP_URL_HOST);
            $portaOrigem = parse_url($origem, PHP_URL_PORT);
            $hostPedido = $_SERVER['HTTP_HOST'] ?? '';
            if ($hostOrigem . ($portaOrigem ? ':' . $portaOrigem : '') !== $hostPedido && $hostOrigem !== $hostPedido) {
                $this->json(['sucesso' => false, 'mensagem' => 'Pedido de origem não autorizada.'], 403);
            }
        }

        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($token) || !Csrf::validar($token)) {
            $this->json(['sucesso' => false, 'mensagem' => 'Sessão expirada. Recarregue a página e tente novamente.'], 419);
        }
    }

    /**
     * Rate limit: regista o pedido e, se passar de $maximo em $janela segundos,
     * responde 429 (JSON) com Retry-After. A chave deve identificar a acção + quem pede.
     */
    protected function limitar(string $chave, int $maximo, int $janela, string $mensagem = 'Demasiados pedidos. Aguarde um pouco e tente novamente.'): void
    {
        if (!RateLimiter::permitir($chave, $maximo, $janela)) {
            header('Retry-After: ' . RateLimiter::segundosAteLibertar($chave, $janela));
            $this->json(['sucesso' => false, 'mensagem' => $mensagem], 429);
        }
    }

    /**
     * Protege uma rota: exige sessão iniciada e, opcionalmente, um tipo
     * de utilizador específico. Redirecciona ou responde 403 conforme o caso.
     */
    protected function protegerRota(array $tiposPermitidos = []): void
    {
        if (!Auth::autenticado()) {
            $destino = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: ' . caminho('/login') . '?redirecionar=' . $destino);
            exit;
        }

        if (!empty($tiposPermitidos) && !Auth::ehTipo(...$tiposPermitidos)) {
            http_response_code(403);
            require CAMINHO_RAIZ . '/app/Views/paginas/403.php';
            exit;
        }
    }

    /**
     * Protege uma rota do painel administrativo por módulo/acção: exige sessão
     * iniciada e que o utilizador (administrador ou funcionário autorizado)
     * tenha essa permissão. Pedidos AJAX recebem JSON 403; os restantes, a página 403.
     */
    protected function exigirPermissao(string $modulo, string $acao = 'ver'): void
    {
        if (!Auth::autenticado()) {
            $destino = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: ' . caminho('/login') . '?redirecionar=' . $destino);
            exit;
        }

        if (!Auth::ehTipo('administrador', 'funcionario') || !Permissoes::tem($modulo, $acao)) {
            $ehAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
            if ($ehAjax) {
                $this->json(['sucesso' => false, 'mensagem' => 'Não tem permissão para esta acção.'], 403);
            }
            http_response_code(403);
            require CAMINHO_RAIZ . '/app/Views/paginas/403.php';
            exit;
        }
    }
}
