<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\RateLimiter;
use Core\Validador;
use App\Models\Utilizador;

// =====================================================================
// Autenticação do painel: login, logout e recuperação de senha.
// Não há registo público — as contas são criadas em Admin › Utilizadores.
// =====================================================================

class AuthController extends Controller
{
    /** Força bruta: 5 falhas por conta (email+IP) ou 20 por IP em 15 min → bloqueio temporário. */
    private const LOGIN_MAX_POR_CONTA = 5;
    private const LOGIN_MAX_POR_IP = 20;
    private const LOGIN_JANELA = 900;

    /**
     * Só aceita destinos internos ("/admin/..."): recusa "//site.com", "/\site.com"
     * e URLs absolutas, que levariam o utilizador para outro domínio (open redirect).
     */
    private function destinoSeguro(string $destino): ?string
    {
        if ($destino === '' || $destino[0] !== '/' || preg_match('#^/[/\\\\]#', $destino)
            || preg_match('/[\x00-\x1F\x7F\\\\]/', $destino) || parse_url($destino, PHP_URL_HOST) !== null) {
            return null;
        }
        return $destino;
    }

    // ---------------- Login ----------------

    public function loginForm(): void
    {
        if (Auth::autenticado()) {
            header('Location: ' . Auth::caminhoDashboard());
            exit;
        }

        $this->view('auth/login', [
            'redirecionar' => $this->destinoSeguro((string) ($_GET['redirecionar'] ?? '')) ?? '',
        ], [
            'titulo'    => 'Entrar | ' . NOME_CURTO,
            'canonical' => URL_BASE . '/login',
            'robots'    => 'noindex, nofollow',
        ], 'auth');
    }

    public function login(): void
    {
        $this->exigirCsrf();

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $senha = (string) ($_POST['senha'] ?? '');
        $redirecionar = (string) ($_POST['redirecionar'] ?? '');

        if (!Validador::email($email) || $senha === '' || strlen($senha) > Validador::SENHA_MAXIMO) {
            $this->json(['sucesso' => false, 'mensagem' => 'Email ou senha incorrectos.'], 401);
        }

        // Anti força bruta: bloqueio temporário por conta (email+IP) e por IP.
        $ip = RateLimiter::ip();
        $chaveConta = 'login:' . $email . '|' . $ip;
        $chaveIp = 'login-ip:' . $ip;
        $contaBloqueada = RateLimiter::excedido($chaveConta, self::LOGIN_MAX_POR_CONTA, self::LOGIN_JANELA);
        $ipBloqueado = RateLimiter::excedido($chaveIp, self::LOGIN_MAX_POR_IP, self::LOGIN_JANELA);
        if ($contaBloqueada || $ipBloqueado) {
            $espera = max(
                $contaBloqueada ? RateLimiter::segundosAteLibertar($chaveConta, self::LOGIN_JANELA) : 0,
                $ipBloqueado ? RateLimiter::segundosAteLibertar($chaveIp, self::LOGIN_JANELA) : 0
            );
            header('Retry-After: ' . $espera);
            $this->json(['sucesso' => false, 'mensagem' => 'Demasiadas tentativas falhadas. Tente novamente dentro de ' . max(1, (int) ceil($espera / 60)) . ' minuto(s).'], 429);
        }

        $modelo = new Utilizador();
        $utilizador = $modelo->porEmail($email);

        if (!$utilizador) {
            $modelo->verificarSenhaFicticia($senha); // mesmo tempo de resposta: não revela se o email existe
        }

        if (!$utilizador || !$modelo->verificarSenha($senha, $utilizador['senha_hash'])) {
            RateLimiter::registar($chaveConta, self::LOGIN_JANELA);
            RateLimiter::registar($chaveIp, self::LOGIN_JANELA);
            $modelo->registarLog(isset($utilizador['id']) ? (int) $utilizador['id'] : null, 'login_falhado', 'Tentativa com email: ' . $email);
            usleep(random_int(200000, 500000)); // atrasa ataques automatizados
            $this->json(['sucesso' => false, 'mensagem' => 'Email ou senha incorrectos.'], 401);
        }

        if (!$utilizador['ativo']) {
            $this->json(['sucesso' => false, 'mensagem' => 'Esta conta encontra-se desactivada. Contacte o administrador.'], 403);
        }

        RateLimiter::limpar($chaveConta);
        $modelo->rehashSeNecessario((int) $utilizador['id'], $senha, $utilizador['senha_hash']);

        Auth::iniciarSessao($utilizador);
        \Core\Permissoes::carregarNaSessao($utilizador['id']);
        $modelo->registarLog($utilizador['id'], 'login', null);

        $destino = $this->destinoSeguro($redirecionar) ?? Auth::caminhoDashboard($utilizador['tipo']);

        $this->json(['sucesso' => true, 'redirecionar' => $destino]);
    }

    public function logout(): void
    {
        // Exige o token CSRF no link: outro site não consegue terminar a sessão do utilizador.
        $token = (string) ($_GET['t'] ?? '');
        if (Auth::autenticado() && !\Core\Csrf::validar($token)) {
            header('Location: ' . Auth::caminhoDashboard());
            exit;
        }

        $modelo = new Utilizador();
        $modelo->registarLog(Auth::id(), 'logout', null);
        Auth::terminarSessao();
        header('Location: ' . caminho('/login'));
        exit;
    }

    // ---------------- Recuperação de senha ----------------

    public function recuperarForm(): void
    {
        $this->view('auth/recuperar-senha', [], [
            'titulo'    => 'Recuperar Senha | ' . NOME_CURTO,
            'canonical' => URL_BASE . '/recuperar-senha',
            'robots'    => 'noindex, nofollow',
        ], 'auth');
    }

    public function recuperar(): void
    {
        $this->exigirCsrf();
        $this->limitar('recuperar-ip:' . RateLimiter::ip(), 5, 3600, 'Demasiados pedidos de recuperação. Tente novamente mais tarde.');

        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!Validador::email($email)) {
            $this->json(['sucesso' => false, 'erros' => ['email' => 'Indique um email válido.']], 422);
        }

        $modelo = new Utilizador();
        // No máximo 3 emails de recuperação por conta/hora (não inunda a caixa da vítima).
        $utilizador = RateLimiter::permitir('recuperar-email:' . $email, 3, 3600) ? $modelo->porEmail($email) : null;

        // Resposta idêntica quer o email exista ou não, para não revelar contas registadas
        if ($utilizador && $utilizador['ativo']) {
            $token = $modelo->criarTokenRecuperacao($utilizador['id']);
            $link = URL_BASE . '/redefinir-senha/' . $token;

            try {
                \Core\Mailer::enviar(
                    $utilizador['email'],
                    $utilizador['nome'],
                    'Recuperação de senha — ' . NOME_CURTO,
                    \Core\Mailer::template(
                        'Redefinir a sua senha',
                        '<p>Recebemos um pedido para redefinir a senha da sua conta no painel da Axiora. Se não foi você, pode ignorar este email.</p><p>Este link expira dentro de 1 hora.</p>',
                        ['url' => $link, 'texto' => 'Redefinir senha']
                    )
                );
            } catch (\Throwable $e) {
                error_log('Email de recuperação de senha não enviado: ' . $e->getMessage());
            }

            $modelo->registarLog($utilizador['id'], 'pedido_recuperacao_senha', null);
        }

        $this->json([
            'sucesso'  => true,
            'mensagem' => 'Se existir uma conta com este email, enviaremos as instruções de recuperação.',
        ]);
    }

    public function redefinirForm(string $token): void
    {
        $modelo = new Utilizador();
        $registoToken = $modelo->validarTokenRecuperacao($token);

        $seo = [
            'titulo'    => ($registoToken ? 'Redefinir Senha' : 'Link inválido') . ' | ' . NOME_CURTO,
            'canonical' => URL_BASE . '/redefinir-senha',
            'robots'    => 'noindex, nofollow',
        ];

        if (!$registoToken) {
            $this->view('auth/token-invalido', [], $seo, 'auth');
            return;
        }

        $this->view('auth/redefinir-senha', ['token' => $token], $seo, 'auth');
    }

    public function redefinir(): void
    {
        $this->exigirCsrf();
        $this->limitar('redefinir:' . RateLimiter::ip(), 10, 3600);

        $token = (string) ($_POST['token'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        $modelo = new Utilizador();
        $registoToken = $modelo->validarTokenRecuperacao($token);

        if (!$registoToken) {
            $this->json(['sucesso' => false, 'mensagem' => 'Este link expirou ou já foi utilizado.'], 410);
        }

        if (!hash_equals($senha, $confirmarSenha)) {
            $this->json(['sucesso' => false, 'erros' => ['confirmar_senha' => 'As senhas não coincidem.']], 422);
        }
        $dono = $modelo->porId((int) $registoToken['utilizador_id']);
        if (($erroSenha = Validador::erroSenha($senha, $dono['email'] ?? '')) !== null) {
            $this->json(['sucesso' => false, 'erros' => ['senha' => $erroSenha]], 422);
        }

        $modelo->atualizarSenha((int) $registoToken['utilizador_id'], $senha);
        $modelo->marcarTokenUsado((int) $registoToken['id']);
        $modelo->invalidarTokensRecuperacao((int) $registoToken['utilizador_id']);
        $modelo->registarLog((int) $registoToken['utilizador_id'], 'senha_redefinida', null);

        $this->json(['sucesso' => true, 'mensagem' => 'Senha alterada. Já pode entrar.', 'redirecionar' => caminho('/login')]);
    }
}
