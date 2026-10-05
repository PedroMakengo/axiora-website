<?php

namespace App\Models;

use Core\Model;

class HeroSlide extends Model
{
    public function todosAtivos(): array
    {
        return $this->db->query("SELECT * FROM hero_slides WHERE ativo = 1 ORDER BY ordem ASC, id ASC")->fetchAll();
    }

    public function todos(): array
    {
        return $this->db->query("SELECT * FROM hero_slides ORDER BY ordem ASC, id ASC")->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM hero_slides WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO hero_slides (rotulo, titulo, titulo_destaque, texto, imagem, botao_texto, mensagem_whatsapp, ordem)
             VALUES (:rotulo, :titulo, :titulo_destaque, :texto, :imagem, :botao_texto, :mensagem_whatsapp, :ordem)"
        );
        $stmt->execute($this->parametros($dados));
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE hero_slides SET rotulo = :rotulo, titulo = :titulo, titulo_destaque = :titulo_destaque, texto = :texto,
                    imagem = :imagem, botao_texto = :botao_texto, mensagem_whatsapp = :mensagem_whatsapp, ordem = :ordem
             WHERE id = :id"
        );
        return $stmt->execute($this->parametros($dados) + ['id' => $id]);
    }

    private function parametros(array $dados): array
    {
        return [
            'rotulo'            => $dados['rotulo'],
            'titulo'            => $dados['titulo'],
            'titulo_destaque'   => $dados['titulo_destaque'] ?: null,
            'texto'             => $dados['texto'] ?: null,
            'imagem'            => $dados['imagem'],
            'botao_texto'       => $dados['botao_texto'] ?: 'Falar no WhatsApp',
            'mensagem_whatsapp' => $dados['mensagem_whatsapp'] ?: null,
            'ordem'             => (int) ($dados['ordem'] ?? 0),
        ];
    }

    public function atualizarEstado(int $id, int $ativo): bool
    {
        $stmt = $this->db->prepare("UPDATE hero_slides SET ativo = :ativo WHERE id = :id");
        return $stmt->execute(['ativo' => $ativo, 'id' => $id]);
    }

    public function remover(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM hero_slides WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
