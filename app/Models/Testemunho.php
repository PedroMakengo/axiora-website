<?php

namespace App\Models;

use Core\Model;

class Testemunho extends Model
{
    public function todosAtivos(int $limite = 6): array
    {
        $stmt = $this->db->prepare("SELECT * FROM testemunhos WHERE ativo = 1 ORDER BY ordem ASC, id ASC LIMIT :limite");
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function todos(): array
    {
        return $this->db->query("SELECT * FROM testemunhos ORDER BY ordem ASC, id ASC")->fetchAll();
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO testemunhos (nome, descricao, texto, estrelas, ordem) VALUES (:nome, :descricao, :texto, :estrelas, :ordem)"
        );
        $stmt->execute($this->parametros($dados));
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE testemunhos SET nome = :nome, descricao = :descricao, texto = :texto, estrelas = :estrelas, ordem = :ordem WHERE id = :id"
        );
        return $stmt->execute($this->parametros($dados) + ['id' => $id]);
    }

    private function parametros(array $dados): array
    {
        return [
            'nome'      => $dados['nome'],
            'descricao' => $dados['descricao'] ?: null,
            'texto'     => $dados['texto'],
            'estrelas'  => max(1, min(5, (int) ($dados['estrelas'] ?? 5))),
            'ordem'     => (int) ($dados['ordem'] ?? 0),
        ];
    }

    public function atualizarEstado(int $id, int $ativo): bool
    {
        $stmt = $this->db->prepare("UPDATE testemunhos SET ativo = :ativo WHERE id = :id");
        return $stmt->execute(['ativo' => $ativo, 'id' => $id]);
    }

    public function remover(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM testemunhos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
