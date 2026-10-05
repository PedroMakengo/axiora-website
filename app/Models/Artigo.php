<?php

namespace App\Models;

use Core\Model;

class Artigo extends Model
{
    /** Um artigo só é público quando está "publicado" e a data de publicação já chegou (permite agendar). */
    private const PUBLICO = "a.estado = 'publicado' AND a.publicado_em IS NOT NULL AND a.publicado_em <= NOW()";

    private const CAMPOS_LISTA = "a.id, a.titulo, a.slug, a.resumo, a.imagem, a.destaque, a.publicado_em, a.visualizacoes,
                                  a.categoria_id, c.nome AS categoria_nome, c.slug AS categoria_slug,
                                  CHAR_LENGTH(a.conteudo) AS tamanho_conteudo";

    // ---------------- Site público ----------------

    /**
     * Listagem paginada do blog, com filtro opcional por categoria e pesquisa.
     * Devolve ['itens' => [...], 'total' => n].
     */
    public function publicados(int $pagina = 1, int $porPagina = 9, ?int $categoriaId = null, string $pesquisa = ''): array
    {
        $condicoes = [self::PUBLICO];
        $parametros = [];

        if ($categoriaId !== null) {
            $condicoes[] = 'a.categoria_id = :categoria';
            $parametros['categoria'] = $categoriaId;
        }
        if ($pesquisa !== '') {
            $condicoes[] = '(a.titulo LIKE :q1 OR a.resumo LIKE :q2)';
            $termo = '%' . addcslashes($pesquisa, '%_\\') . '%';
            $parametros['q1'] = $termo;
            $parametros['q2'] = $termo;
        }

        $where = implode(' AND ', $condicoes);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM blog_artigos a WHERE {$where}");
        $stmt->execute($parametros);
        $total = (int) $stmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT " . self::CAMPOS_LISTA . "
             FROM blog_artigos a
             LEFT JOIN blog_categorias c ON c.id = a.categoria_id
             WHERE {$where}
             ORDER BY a.publicado_em DESC, a.id DESC
             LIMIT :limite OFFSET :offset"
        );
        foreach ($parametros as $chave => $valor) {
            $stmt->bindValue(':' . $chave, $valor, is_int($valor) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, ($pagina - 1) * $porPagina), \PDO::PARAM_INT);
        $stmt->execute();

        return ['itens' => $stmt->fetchAll(), 'total' => $total];
    }

    public function recentes(int $limite = 3, ?int $excluirId = null): array
    {
        $stmt = $this->db->prepare(
            "SELECT " . self::CAMPOS_LISTA . "
             FROM blog_artigos a
             LEFT JOIN blog_categorias c ON c.id = a.categoria_id
             WHERE " . self::PUBLICO . " AND a.id != :excluir
             ORDER BY a.destaque DESC, a.publicado_em DESC, a.id DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':excluir', $excluirId ?? 0, \PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Mais recentes por data (sem dar prioridade aos destaques) — barra lateral. */
    public function ultimos(int $limite = 4, ?int $excluirId = null): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.titulo, a.slug, a.imagem, a.publicado_em
             FROM blog_artigos a
             WHERE " . self::PUBLICO . " AND a.id != :excluir
             ORDER BY a.publicado_em DESC, a.id DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':excluir', $excluirId ?? 0, \PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Artigos da mesma categoria (ou, se não houver, os mais recentes). */
    public function relacionados(array $artigo, int $limite = 3): array
    {
        $resultado = [];
        if (!empty($artigo['categoria_id'])) {
            $stmt = $this->db->prepare(
                "SELECT " . self::CAMPOS_LISTA . "
                 FROM blog_artigos a
                 LEFT JOIN blog_categorias c ON c.id = a.categoria_id
                 WHERE " . self::PUBLICO . " AND a.categoria_id = :categoria AND a.id != :id
                 ORDER BY a.publicado_em DESC
                 LIMIT :limite"
            );
            $stmt->bindValue(':categoria', (int) $artigo['categoria_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':id', (int) $artigo['id'], \PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetchAll();
        }

        if (count($resultado) < $limite) {
            $vistos = array_merge([(int) $artigo['id']], array_map('intval', array_column($resultado, 'id')));
            foreach ($this->recentes($limite + count($vistos)) as $outro) {
                if (count($resultado) >= $limite) {
                    break;
                }
                if (!in_array((int) $outro['id'], $vistos, true)) {
                    $resultado[] = $outro;
                }
            }
        }
        return $resultado;
    }

    public function porSlugPublicado(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.nome AS categoria_nome, c.slug AS categoria_slug, u.nome AS autor_nome
             FROM blog_artigos a
             LEFT JOIN blog_categorias c ON c.id = a.categoria_id
             LEFT JOIN utilizadores u ON u.id = a.autor_id
             WHERE a.slug = :slug AND " . self::PUBLICO . "
             LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function incrementarVisualizacoes(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE blog_artigos SET visualizacoes = visualizacoes + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function paraSitemap(): array
    {
        return $this->db->query(
            "SELECT a.slug, a.atualizado_em, a.publicado_em FROM blog_artigos a WHERE " . self::PUBLICO . " ORDER BY a.publicado_em DESC"
        )->fetchAll();
    }

    // ---------------- Painel administrativo ----------------

    public function todosAdmin(): array
    {
        return $this->db->query(
            "SELECT a.id, a.titulo, a.slug, a.imagem, a.estado, a.destaque, a.visualizacoes, a.publicado_em, a.criado_em, a.atualizado_em,
                    c.nome AS categoria_nome, u.nome AS autor_nome
             FROM blog_artigos a
             LEFT JOIN blog_categorias c ON c.id = a.categoria_id
             LEFT JOIN utilizadores u ON u.id = a.autor_id
             ORDER BY COALESCE(a.publicado_em, a.criado_em) DESC, a.id DESC"
        )->fetchAll();
    }

    public function porId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_artigos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function slugExiste(string $slug, ?int $excluirId = null): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM blog_artigos WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt->execute(['slug' => $slug, 'id' => $excluirId ?? 0]);
        return (bool) $stmt->fetch();
    }

    /** Acrescenta -2, -3... até o slug ficar livre. */
    public function slugUnico(string $slug, ?int $excluirId = null): string
    {
        $base = $slug;
        $n = 2;
        while ($this->slugExiste($slug, $excluirId)) {
            $slug = mb_substr($base, 0, 140) . '-' . $n++;
        }
        return $slug;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO blog_artigos (categoria_id, autor_id, titulo, slug, resumo, conteudo, imagem, meta_descricao, estado, destaque, publicado_em)
             VALUES (:categoria_id, :autor_id, :titulo, :slug, :resumo, :conteudo, :imagem, :meta_descricao, :estado, :destaque, :publicado_em)"
        );
        $stmt->execute($this->parametros($dados) + ['autor_id' => $dados['autor_id'] ?? null]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE blog_artigos SET categoria_id = :categoria_id, titulo = :titulo, slug = :slug, resumo = :resumo,
                    conteudo = :conteudo, imagem = :imagem, meta_descricao = :meta_descricao, estado = :estado,
                    destaque = :destaque, publicado_em = :publicado_em
             WHERE id = :id"
        );
        return $stmt->execute($this->parametros($dados) + ['id' => $id]);
    }

    private function parametros(array $dados): array
    {
        return [
            'categoria_id'   => $dados['categoria_id'] ?: null,
            'titulo'         => $dados['titulo'],
            'slug'           => $dados['slug'],
            'resumo'         => $dados['resumo'] ?: null,
            'conteudo'       => $dados['conteudo'],
            'imagem'         => $dados['imagem'] ?: null,
            'meta_descricao' => $dados['meta_descricao'] ?: null,
            'estado'         => $dados['estado'] === 'publicado' ? 'publicado' : 'rascunho',
            'destaque'       => !empty($dados['destaque']) ? 1 : 0,
            'publicado_em'   => $dados['publicado_em'] ?: null,
        ];
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_artigos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Indicadores do dashboard. */
    public function resumo(): array
    {
        $linha = $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(" . str_replace('a.', '', self::PUBLICO) . ") AS publicados,
                    SUM(estado = 'rascunho') AS rascunhos,
                    SUM(estado = 'publicado' AND publicado_em > NOW()) AS agendados,
                    COALESCE(SUM(visualizacoes), 0) AS visualizacoes
             FROM blog_artigos"
        )->fetch();
        return array_map('intval', $linha ?: []);
    }

    public function maisLidos(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.titulo, a.slug, a.visualizacoes, a.publicado_em
             FROM blog_artigos a
             WHERE " . self::PUBLICO . "
             ORDER BY a.visualizacoes DESC, a.publicado_em DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Minutos estimados de leitura (≈ 200 palavras por minuto). */
    public static function minutosLeitura(string $conteudoOuTamanho): int
    {
        $palavras = ctype_digit($conteudoOuTamanho)
            ? (int) $conteudoOuTamanho / 6.5   // aproximação a partir do nº de caracteres do HTML
            : count(preg_split('/\s+/u', \Core\Html::texto($conteudoOuTamanho), -1, PREG_SPLIT_NO_EMPTY));
        return max(1, (int) round($palavras / 200));
    }
}
