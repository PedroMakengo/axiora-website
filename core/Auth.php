<?php

namespace Core;

// =====================================================================
// Core\Auth — gestão da sessão do utilizador autenticado.
// =====================================================================

class Auth
{
    private const CHAVE_SESSAO = 'utilizador';

    /** Sessão termina após 2h sem actividade ou 12h desde o login. */
    private const INATIVIDADE_MAXIMA = 7200;
    private const DURACAO_MAXIMA = 43200;

    /** De quantos em quantos segundos se confirma na BD que a conta continua activa. */
    private const REVALIDAR_A_CADA = 300;

    public static function iniciarSessao(array $utilizador): void
    {
        session_regenerate_id(true);
        $agora = time();
        $_SESSION[self::CHAVE_SESSAO] = [
            'id'    => (int) $utilizador['id'],
            'nome'  => $utilizador['nome'],
            'email' => $utilizador['email'],
            'tipo'  => $utilizador['tipo'],
            'inicio'       => $agora,
            'ultimo_uso'   => $agora,
            'validado_em'  => $agora,
            'user_agent'   => hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? ''),
        ];
        // Novo token CSRF a cada login (o anterior pode ter sido exposto antes da autenticação).
        unset($_SESSION['csrf_token']);
    }

    public static function terminarSessao(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function autenticado(): bool
    {
        $sessao = $_SESSION[self::CHAVE_SESSAO] ?? null;
        if (empty($sessao)) {
            return false;
        }

        static $verificado = false;
        if ($verificado) {
            return !empty($_SESSION[self::CHAVE_SESSAO]);
        }
        $verificado = true;

        $agora = time();
        $expirada = ($agora - ($sessao['ultimo_uso'] ?? 0)) > self::INATIVIDADE_MAXIMA
            || ($agora - ($sessao['inicio'] ?? 0)) > self::DURACAO_MAXIMA
            || !hash_equals($sessao['user_agent'] ?? '', hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? ''));

        if ($expirada) {
            self::terminarSessao();
            return false;
        }

        // Conta desactivada, removida ou com tipo alterado pelo admin: aplica-se
        // em poucos minutos, sem esperar que o utilizador saia.
        if (($agora - ($sessao['validado_em'] ?? 0)) > self::REVALIDAR_A_CADA) {
            try {
                $stmt = pdo()->prepare("SELECT tipo, ativo, nome FROM utilizadores WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => (int) $sessao['id']]);
                $atual = $stmt->fetch();
                if (!$atual || !(int) $atual['ativo']) {
                    self::terminarSessao();
                    return false;
                }
                $_SESSION[self::CHAVE_SESSAO]['tipo'] = $atual['tipo'];
                $_SESSION[self::CHAVE_SESSAO]['nome'] = $atual['nome'];
                $_SESSION[self::CHAVE_SESSAO]['validado_em'] = $agora;
            } catch (\Throwable $e) {
                // Sem BD mantém-se a sessão; a próxima verificação volta a tentar.
            }
        }

        $_SESSION[self::CHAVE_SESSAO]['ultimo_uso'] = $agora;
        return true;
    }

    public static function utilizador(): ?array
    {
        if (!self::autenticado()) {
            return null;
        }
        return $_SESSION[self::CHAVE_SESSAO] ?? null;
    }

    public static function id(): ?int
    {
        if (!self::autenticado()) {
            return null;
        }
        return $_SESSION[self::CHAVE_SESSAO]['id'] ?? null;
    }

    public static function tipo(): ?string
    {
        if (!self::autenticado()) {
            return null;
        }
        return $_SESSION[self::CHAVE_SESSAO]['tipo'] ?? null;
    }

    public static function ehTipo(string ...$tipos): bool
    {
        return in_array(self::tipo(), $tipos, true);
    }

    public static function atualizarNomeSessao(string $nome): void
    {
        if (self::autenticado()) {
            $_SESSION[self::CHAVE_SESSAO]['nome'] = $nome;
        }
    }

    /**
     * Devolve o caminho do dashboard correspondente ao tipo de utilizador.
     */
    public static function caminhoDashboard(?string $tipo = null): string
    {
        $tipo = $tipo ?? self::tipo();
        $mapa = [
            'administrador' => '/admin',
            'funcionario'   => '/admin',
        ];
        $rota = $mapa[$tipo] ?? '/';
        return \caminho($rota);
    }
}
