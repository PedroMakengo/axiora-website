<?php
// =====================================================================
// config/db.php — Ligação PDO à base de dados (MySQL/MariaDB)
// =====================================================================

// Credenciais lidas do .env / ambiente (ver .env.example) — nunca escritas no código.
define('DB_HOST', (string) env('DB_HOST', 'localhost'));
define('DB_PORTA', (int) env('DB_PORTA', 3306));
define('DB_NOME', (string) env('DB_NOME', ''));
define('DB_USER', (string) env('DB_USER', ''));
define('DB_SENHA', (string) env('DB_SENHA', ''));
define('DB_CHARSET', 'utf8mb4');

/**
 * Cria uma ligação nova — usada por pdo() e pelo instalador (database/instalar.php).
 * Lança PDOException se não conseguir ligar.
 */
function novaLigacaoPdo(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORTA . ';dbname=' . DB_NOME . ';charset=' . DB_CHARSET;
    $ligacao = new PDO($dsn, DB_USER, DB_SENHA, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // NOW()/CURDATE() do MySQL na mesma hora do site (Africa/Luanda), mesmo
    // que o servidor de BD esteja noutro fuso (ex.: UTC no contentor).
    $ligacao->exec("SET time_zone = '" . date('P') . "'");
    return $ligacao;
}

function pdo(): PDO
{
    static $instancia = null;

    if ($instancia === null) {
        try {
            $instancia = novaLigacaoPdo();
        } catch (PDOException $e) {
            error_log('Erro de ligação à base de dados: ' . $e->getMessage());
            http_response_code(503);
            if (AMBIENTE === 'local') {
                die('Erro de ligação à base de dados: ' . htmlspecialchars($e->getMessage()));
            }
            die('Ocorreu um erro. Tente novamente mais tarde.');
        }
    }

    return $instancia;
}
