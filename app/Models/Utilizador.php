<?php

namespace App\Models;

use Core\Model;

class Utilizador extends Model
{
    /** Custo do bcrypt — password_needs_rehash() actualiza hashes antigos no login. */
    private const HASH_OPCOES = ['cost' => 12];

    /** Hash de referência para gastar o mesmo tempo quando o email não existe (evita enumeração por tempo). */
    private const HASH_FICTICIO = '$2y$12$FViTB.JLNDCVmhK/h7bFRO.ks823X2k3Czm/QGXoKdfjCROZ.NFKO';

    public function porEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizadores WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM utilizadores WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function emailExiste(string $email): bool
    {
        return $this->porEmail($email) !== null;
    }

    public function emailExisteNoutroUtilizador(string $email, int $idAtual): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM utilizadores WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute(['email' => $email, 'id' => $idAtual]);
        return (bool) $stmt->fetch();
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO utilizadores (nome, email, senha_hash, telefone, tipo)
                VALUES (:nome, :email, :senha_hash, :telefone, :tipo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nome'       => $dados['nome'],
            'email'      => $dados['email'],
            'senha_hash' => password_hash($dados['senha'], PASSWORD_DEFAULT, self::HASH_OPCOES),
            'telefone'   => $dados['telefone'] ?? null,
            'tipo'       => $dados['tipo'] ?? 'funcionario',
        ]);
        return (int) $this->db->lastInsertId();
    }


    public function verificarSenha(string $senha, string $hash): bool
    {
        return password_verify($senha, $hash);
    }

    /** Gasta o mesmo tempo que uma verificação real — usado quando o email não existe. */
    public function verificarSenhaFicticia(string $senha): void
    {
        password_verify($senha, self::HASH_FICTICIO);
    }

    /** Actualiza o hash se o algoritmo/custo por omissão tiver mudado. */
    public function rehashSeNecessario(int $id, string $senha, string $hash): void
    {
        if (password_needs_rehash($hash, PASSWORD_DEFAULT, self::HASH_OPCOES)) {
            $this->atualizarSenha($id, $senha);
        }
    }

    public function atualizarSenha(int $utilizadorId, string $novaSenha): bool
    {
        $stmt = $this->db->prepare("UPDATE utilizadores SET senha_hash = :hash WHERE id = :id");
        return $stmt->execute([
            'hash' => password_hash($novaSenha, PASSWORD_DEFAULT, self::HASH_OPCOES),
            'id'   => $utilizadorId,
        ]);
    }

    public function atualizarPerfil(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare("UPDATE utilizadores SET nome = :nome, telefone = :telefone WHERE id = :id");
        return $stmt->execute([
            'nome'     => $dados['nome'],
            'telefone' => $dados['telefone'] ?? null,
            'id'       => $id,
        ]);
    }

    public function atualizarAvatar(int $id, string $caminho): bool
    {
        $stmt = $this->db->prepare("UPDATE utilizadores SET avatar = :avatar WHERE id = :id");
        return $stmt->execute(['avatar' => $caminho, 'id' => $id]);
    }

    /**
     * Edição completa a partir do painel administrativo: nome, email, telefone e tipo.
     * A senha só é alterada se vier preenchida (tratado à parte pelo controlador).
     */
    public function atualizarCompleto(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE utilizadores SET nome = :nome, email = :email, telefone = :telefone, tipo = :tipo WHERE id = :id"
        );
        return $stmt->execute([
            'nome'     => $dados['nome'],
            'email'    => $dados['email'],
            'telefone' => $dados['telefone'] ?: null,
            'tipo'     => $dados['tipo'],
            'id'       => $id,
        ]);
    }

    // ---------------- Recuperação de senha ----------------

    /**
     * Gera um token de recuperação. Só o hash SHA-256 fica na BD — quem ler a
     * tabela (backup, fuga de dados) não consegue usar os links. Pedidos
     * anteriores ainda válidos deixam de funcionar.
     */
    public function criarTokenRecuperacao(int $utilizadorId): string
    {
        $this->invalidarTokensRecuperacao($utilizadorId);

        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $stmt = $this->db->prepare(
            "INSERT INTO tokens_recuperacao_senha (utilizador_id, token, expira_em) VALUES (:uid, :token, :expira)"
        );
        $stmt->execute(['uid' => $utilizadorId, 'token' => hash('sha256', $token), 'expira' => $expira]);

        return $token;
    }

    public function validarTokenRecuperacao(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $stmt = $this->db->prepare(
            "SELECT * FROM tokens_recuperacao_senha WHERE token = :token AND usado = 0 AND expira_em > NOW() LIMIT 1"
        );
        $stmt->execute(['token' => hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    /** Invalida todos os tokens de recuperação ainda activos de um utilizador. */
    public function invalidarTokensRecuperacao(int $utilizadorId): void
    {
        $stmt = $this->db->prepare("UPDATE tokens_recuperacao_senha SET usado = 1 WHERE utilizador_id = :uid AND usado = 0");
        $stmt->execute(['uid' => $utilizadorId]);
    }

    public function marcarTokenUsado(int $tokenId): void
    {
        $stmt = $this->db->prepare("UPDATE tokens_recuperacao_senha SET usado = 1 WHERE id = :id");
        $stmt->execute(['id' => $tokenId]);
    }

    public function registarLog(?int $utilizadorId, string $acao, ?string $detalhes = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO logs_atividade (utilizador_id, acao, detalhes, ip) VALUES (:uid, :acao, :detalhes, :ip)"
        );
        $stmt->execute([
            'uid'      => $utilizadorId,
            'acao'     => $acao,
            'detalhes' => $detalhes,
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Histórico de actividade de um utilizador (login, alterações de perfil,
     * etc.) — usado na vista de detalhe do utilizador no admin.
     */
    public function logsDoUtilizador(int $id, int $limite = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM logs_atividade WHERE utilizador_id = :id ORDER BY criado_em DESC LIMIT :limite"
        );
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Últimas acções de todos os utilizadores do painel — dashboard. */
    public function atividadeRecente(int $limite = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.acao, l.detalhes, l.criado_em, u.nome
             FROM logs_atividade l
             LEFT JOIN utilizadores u ON u.id = l.utilizador_id
             WHERE l.acao NOT IN ('login_falhado')
             ORDER BY l.criado_em DESC, l.id DESC
             LIMIT :limite"
        );
        $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ---------------- Gestão administrativa (permissões) ----------------

    public function listarTodos(): array
    {
        return $this->db->query(
            "SELECT id, nome, email, telefone, tipo, avatar, ativo, criado_em FROM utilizadores ORDER BY criado_em DESC"
        )->fetchAll();
    }



    public function contarPorTipo(string $tipo, bool $apenasAtivos = false): int
    {
        $sql = "SELECT COUNT(*) AS total FROM utilizadores WHERE tipo = :tipo";
        if ($apenasAtivos) {
            $sql .= " AND ativo = 1";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tipo' => $tipo]);
        return (int) $stmt->fetch()['total'];
    }

    public function atualizarPermissoes(int $id, ?string $tipo, ?int $ativo): bool
    {
        $campos = [];
        $parametros = ['id' => $id];

        if ($tipo !== null) {
            $campos[] = 'tipo = :tipo';
            $parametros['tipo'] = $tipo;
        }
        if ($ativo !== null) {
            $campos[] = 'ativo = :ativo';
            $parametros['ativo'] = $ativo;
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE utilizadores SET " . implode(', ', $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($parametros);
    }


    /**
     * Apaga a conta. Permissões e tokens saem por
     * ON DELETE CASCADE; os registos de log e os artigos ficam (autor/utilizador_id passa a NULL).
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM utilizadores WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
