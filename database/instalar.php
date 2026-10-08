<?php
// =====================================================================
// database/instalar.php — Instalação / actualização da base de dados.
//
// Uso (só pela linha de comandos):
//     php database/instalar.php            → instala/actualiza
//     php database/instalar.php --esperar  → espera até 60s pela BD (Docker)
//
// 1. Se a BD estiver vazia, importa database/schema.sql.
// 2. Aplica, uma única vez e por ordem, os ficheiros novos de
//    database/atualizacoes/*.sql (registados na tabela `migracoes`).
// 3. Se não existir nenhum administrador, cria-o a partir de
//    ADMIN_NOME / ADMIN_EMAIL / ADMIN_SENHA (.env ou ambiente). Sem
//    ADMIN_SENHA, gera uma senha aleatória e mostra-a UMA vez no output
//    (no Coolify: separador "Logs" do serviço app).
//
// No contentor Docker corre automaticamente em cada arranque
// (docker/entrypoint.sh) — é seguro repetir: não apaga nada.
// =====================================================================

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../core/polyfills.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/db.php';

$esperar = in_array('--esperar', $argv, true);

function escrever(string $mensagem): void
{
    fwrite(STDOUT, '[instalar] ' . $mensagem . PHP_EOL);
}

/** Divide um ficheiro .sql em instruções (terminadas por ";" no fim da linha). */
function instrucoesSql(string $ficheiro): array
{
    $sql = (string) file_get_contents($ficheiro);
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql); // BOM
    $partes = preg_split('/;\s*(?:\r?\n|$)/', $sql);

    $instrucoes = [];
    foreach ($partes as $parte) {
        $semComentarios = trim(preg_replace('/^\s*--.*$/m', '', $parte));
        if ($semComentarios !== '') {
            $instrucoes[] = $parte;
        }
    }
    return $instrucoes;
}

function executarFicheiro(PDO $db, string $ficheiro): void
{
    foreach (instrucoesSql($ficheiro) as $instrucao) {
        $db->exec($instrucao);
    }
}

// ---------------------------------------------------------------------
// Ligação (com espera opcional — no Docker a BD pode ainda estar a arrancar)
// ---------------------------------------------------------------------
$db = null;
$limite = time() + ($esperar ? 60 : 0);
do {
    try {
        $db = novaLigacaoPdo();
    } catch (PDOException $e) {
        if (time() >= $limite) {
            escrever('ERRO: não foi possível ligar à base de dados (' . DB_HOST . ':' . DB_PORTA . '/' . DB_NOME . '): ' . $e->getMessage());
            exit(1);
        }
        escrever('À espera da base de dados...');
        sleep(2);
    }
} while ($db === null);

// ---------------------------------------------------------------------
// 1. Estrutura inicial
// ---------------------------------------------------------------------
$db->exec("CREATE TABLE IF NOT EXISTS `migracoes` (
    `ficheiro` varchar(190) NOT NULL,
    `aplicado_em` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ficheiro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$instalacaoNova = !$db->query("SHOW TABLES LIKE 'utilizadores'")->fetch();
$atualizacoes = glob(__DIR__ . '/atualizacoes/*.sql') ?: [];
sort($atualizacoes);

if ($instalacaoNova) {
    escrever('Base de dados vazia — a importar database/schema.sql...');
    executarFicheiro($db, __DIR__ . '/schema.sql');

    // O schema.sql já traz a estrutura mais recente: as actualizações
    // existentes ficam marcadas como aplicadas.
    $marcar = $db->prepare("INSERT IGNORE INTO migracoes (ficheiro) VALUES (:f)");
    foreach ($atualizacoes as $ficheiro) {
        $marcar->execute(['f' => basename($ficheiro)]);
    }
    escrever('Estrutura e conteúdos iniciais criados.');
}

// ---------------------------------------------------------------------
// 2. Actualizações pendentes
// ---------------------------------------------------------------------
$aplicadas = $db->query("SELECT ficheiro FROM migracoes")->fetchAll(PDO::FETCH_COLUMN);
foreach ($atualizacoes as $ficheiro) {
    $nome = basename($ficheiro);
    if (in_array($nome, $aplicadas, true)) {
        continue;
    }
    escrever("A aplicar actualização {$nome}...");
    executarFicheiro($db, $ficheiro);
    $db->prepare("INSERT INTO migracoes (ficheiro) VALUES (:f)")->execute(['f' => $nome]);
}

// ---------------------------------------------------------------------
// 2b. Conteúdo inicial das secções do site (config/secoes.php) — só as
//     secções que ainda não existem na BD; nunca altera o que já foi editado.
// ---------------------------------------------------------------------
if ($db->query("SHOW TABLES LIKE 'secoes_site'")->fetch()) {
    require_once __DIR__ . '/../core/autoload.php';
    $criadas = (new \App\Models\SecaoSite())->semearIniciais();
    if ($criadas > 0) {
        escrever("Conteúdo inicial de {$criadas} secção(ões) do site gravado.");
    }
}

// ---------------------------------------------------------------------
// 3. Administrador inicial
// ---------------------------------------------------------------------
$temAdmin = (int) $db->query("SELECT COUNT(*) FROM utilizadores WHERE tipo = 'administrador'")->fetchColumn() > 0;
if (!$temAdmin) {
    $nome  = trim((string) env('ADMIN_NOME', 'Administrador')) ?: 'Administrador';
    $email = strtolower(trim((string) env('ADMIN_EMAIL', 'admin@axiora.site'))) ?: 'admin@axiora.site';
    $senha = (string) env('ADMIN_SENHA', '');
    $gerada = false;

    if ($senha === '') {
        $senha = substr(strtr(base64_encode(random_bytes(18)), '+/=', 'xyz'), 0, 16) . '7a';
        $gerada = true;
    }

    $stmt = $db->prepare(
        "INSERT INTO utilizadores (nome, email, senha_hash, tipo) VALUES (:nome, :email, :hash, 'administrador')
         ON DUPLICATE KEY UPDATE tipo = 'administrador', ativo = 1"
    );
    $stmt->execute([
        'nome'  => $nome,
        'email' => $email,
        'hash'  => password_hash($senha, PASSWORD_DEFAULT, ['cost' => 12]),
    ]);

    // rowCount(): 1 = conta nova; 2 = já existia com este email (promovida, senha mantida).
    if ($stmt->rowCount() !== 1) {
        escrever("A conta {$email} já existia e passou a administrador (senha mantida).");
    } else {
        escrever("Administrador criado: {$email}");
    }
    if ($gerada && $stmt->rowCount() === 1) {
        escrever("Senha gerada (guarde-a e altere-a após o primeiro login): {$senha}");
    }
}

escrever('Base de dados pronta.');
