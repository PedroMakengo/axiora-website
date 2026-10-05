<?php

namespace App\Models;

use Core\Model;

class Servico extends Model
{
    public function todosAtivos(): array
    {
        return $this->db->query("SELECT * FROM servicos WHERE ativo = 1 ORDER BY ordem ASC, id ASC")->fetchAll();
    }

    public function todos(): array
    {
        return $this->db->query("SELECT * FROM servicos ORDER BY ordem ASC, id ASC")->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM servicos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO servicos (titulo, descricao, imagem, imagem_alt, mensagem_whatsapp, ordem)
             VALUES (:titulo, :descricao, :imagem, :imagem_alt, :mensagem_whatsapp, :ordem)"
        );
        $stmt->execute($this->parametros($dados));
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE servicos SET titulo = :titulo, descricao = :descricao, imagem = :imagem, imagem_alt = :imagem_alt,
                    mensagem_whatsapp = :mensagem_whatsapp, ordem = :ordem
             WHERE id = :id"
        );
        return $stmt->execute($this->parametros($dados) + ['id' => $id]);
    }

    private function parametros(array $dados): array
    {
        return [
            'titulo'            => $dados['titulo'],
            'descricao'         => $dados['descricao'],
            'imagem'            => $dados['imagem'] ?: null,
            'imagem_alt'        => $dados['imagem_alt'] ?: null,
            'mensagem_whatsapp' => $dados['mensagem_whatsapp'] ?: null,
            'ordem'             => (int) ($dados['ordem'] ?? 0),
        ];
    }

    public function atualizarEstado(int $id, int $ativo): bool
    {
        $stmt = $this->db->prepare("UPDATE servicos SET ativo = :ativo WHERE id = :id");
        return $stmt->execute(['ativo' => $ativo, 'id' => $id]);
    }

    public function remover(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM servicos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
