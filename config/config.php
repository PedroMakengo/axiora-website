<?php
// =====================================================================
// config/config.php — Configuração geral do site
// =====================================================================

// Variáveis sensíveis (BD, SMTP) vêm do .env ou do ambiente do servidor
// (Coolify/Docker) — nunca do código.
require_once dirname(__DIR__) . '/core/Env.php';
carregarEnv(dirname(__DIR__));

// Ambiente: "local" | "producao". Na falta de .env assume produção (mais seguro).
define('AMBIENTE', env('APP_AMBIENTE', 'producao') === 'local' ? 'local' : 'producao');

// ---------------------------------------------------------------------
// Caminho base — subpasta onde o index.php está montado.
// Ex.: em http://localhost/axiora-website/  ->  BASE = "/axiora-website"
//      na raiz do domínio (Docker, php -S)  ->  BASE = ""
// Detectado automaticamente, para o site funcionar em qualquer pasta
// sem alterar código nem o .htaccess.
// ---------------------------------------------------------------------
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE', rtrim($scriptDir === '/' || $scriptDir === '.' ? '' : $scriptDir, '/'));

/**
 * Devolve um caminho interno já prefixado com a subpasta base.
 * Usar em href/src/action e redireccionamentos: caminho('/blog').
 */
function caminho(string $p = '/'): string
{
    return BASE . '/' . ltrim($p, '/');
}

/**
 * Data em português: dataPt('2026-10-04') → "4 de outubro de 2026".
 */
function dataPt(?string $data, bool $curta = false): string
{
    if (!$data) {
        return '';
    }
    $meses = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    $t = strtotime($data);
    $mes = $meses[(int) date('n', $t)];
    return $curta
        ? date('j', $t) . ' ' . mb_substr($mes, 0, 3) . ' ' . date('Y', $t)
        : date('j', $t) . ' de ' . $mes . ' de ' . date('Y', $t);
}

// URL base absoluta (sem barra final) — usada nos canónicos, Open Graph
// e sitemap. Em local é detectada a partir do pedido; em produção fixa-se
// no domínio real (APP_URL). No Coolify, APP_URL recebe SERVICE_FQDN_APP,
// que pode vir sem esquema — nesse caso assume-se https.
if (AMBIENTE === 'local') {
    $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('URL_BASE', $esquema . '://' . $host . BASE);
} else {
    $urlApp = trim((string) env('APP_URL', 'https://www.axiora.site'));
    $urlApp = explode(',', $urlApp)[0]; // Coolify pode listar vários domínios separados por vírgula
    if ($urlApp !== '' && !preg_match('#^https?://#i', $urlApp)) {
        $urlApp = 'https://' . $urlApp;
    }
    define('URL_BASE', rtrim($urlApp ?: 'https://www.axiora.site', '/'));
}

define('NOME_SITE', 'Axiora — Comércio Geral e Prestação de Serviços');
define('NOME_CURTO', 'Axiora');
define('NOME_EMPRESA', 'Axiora — Comércio Geral e Prestação de Serviços LDA');
define('DESCRICAO_SITE', 'Axiora é uma empresa de referência em Angola na prestação de serviços: assessoria de viagens, gestão de viaturas, canalização, electricidade, correio e câmbio.');

// Caminho absoluto da raiz do projecto
define('CAMINHO_RAIZ', dirname(__DIR__));

date_default_timezone_set('Africa/Luanda');

error_reporting(AMBIENTE === 'local' ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', AMBIENTE === 'local' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('expose_php', '0');

// Pedido feito por HTTPS (directo ou atrás de proxy — Coolify/Traefik, CDN)?
define('PEDIDO_HTTPS',
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') == 443
    || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
);

// Sessão — cookie só acessível por HTTP (sem JavaScript), SameSite=Lax contra
// CSRF, Secure em HTTPS e modo estrito (rejeita IDs de sessão inventados).
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.sid_length', '48');
    ini_set('session.sid_bits_per_character', '6');
    session_name('AXIORASESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE === '' ? '/' : BASE . '/',
        'secure'   => PEDIDO_HTTPS,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Cabeçalhos de segurança HTTP enviados em todas as respostas dinâmicas.
if (!headers_sent() && PHP_SAPI !== 'cli') {
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
    if (PEDIDO_HTTPS && AMBIENTE !== 'local') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ---------------------------------------------------------------------
// Envio de email (Core\Mailer — cliente SMTP próprio, sem dependências).
// Usado na recuperação de senha do painel. Enquanto MAIL_HOST estiver
// vazio, os emails não são enviados — o Mailer regista a tentativa em
// error_log() e devolve false, sem interromper o fluxo do utilizador.
// ---------------------------------------------------------------------
define('MAIL_HOST', (string) env('MAIL_HOST', ''));
define('MAIL_PORT', (int) env('MAIL_PORT', 465));
define('MAIL_ENCRIPTACAO', (string) env('MAIL_ENCRIPTACAO', 'ssl')); // 'tls' (porta 587) ou 'ssl' (porta 465)
define('MAIL_UTILIZADOR', (string) env('MAIL_UTILIZADOR', ''));
define('MAIL_SENHA', (string) env('MAIL_SENHA', ''));
define('MAIL_DE', (string) env('MAIL_DE', env('MAIL_UTILIZADOR', '')));
define('MAIL_DE_NOME', NOME_CURTO);
