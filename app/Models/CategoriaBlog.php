<?php

namespace App\Models;

use Core\Model;

class CategoriaBlog extends Model
{
    /** Categorias activas com o número de artigos publicados (barra lateral do blog). */
    public function ativasComContagem(): array
    {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM blog_artigos a
                             WHERE a.categoria_id = c.id AND a.estado = 'publicado' AND a.publicado_em <= NOW()) AS total_artigos
                FROM blog_categorias c
                WHERE c.ativo = 1
                ORDER BY c.ordem ASC, c.nome ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function todas(): array
    {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM blog_artigos a WHERE a.categoria_id = c.id) AS total_artigos
                FROM blog_categorias c
                ORDER BY c.ordem ASC, c.nome ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_categorias WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function porSlugAtiva(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_categorias WHERE slug = :slug AND ativo = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function slugExiste(string $slug, ?int $excluirId = null): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM blog_categorias WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt->execute(['slug' => $slug, 'id' => $excluirId ?? 0]);
        return (bool) $stmt->fetch();
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO blog_categorias (nome, slug, descricao, ordem) VALUES (:nome, :slug, :descricao, :ordem)"
        );
        $stmt->execute([
            'nome'      => $dados['nome'],
            'slug'      => $dados['slug'],
            'descricao' => $dados['descricao'] ?: null,
            'ordem'     => (int) ($dados['ordem'] ?? 0),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE blog_categorias SET nome = :nome, slug = :slug, descricao = :descricao, ordem = :ordem WHERE id = :id"
        );
        return $stmt->execute([
            'nome'      => $dados['nome'],
            'slug'      => $dados['slug'],
            'descricao' => $dados['descricao'] ?: null,
            'ordem'     => (int) ($dados['ordem'] ?? 0),
            'id'        => $id,
        ]);
    }

    public function atualizarEstado(int $id, int $ativo): bool
    {
        $stmt = $this->db->prepare("UPDATE blog_categorias SET ativo = :ativo WHERE id = :id");
        return $stmt->execute(['ativo' => $ativo, 'id' => $id]);
    }

    /** Os artigos da categoria ficam "sem categoria" (ON DELETE SET NULL). */
    public function remover(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_categorias WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
