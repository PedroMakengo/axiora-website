<?php

namespace App\Models;

use Core\Model;

// =====================================================================
// Secções do site (CMS da homepage). A estrutura vem de config/secoes.php;
// a BD guarda os valores (secoes_site) e os itens repetíveis (secao_itens).
// =====================================================================

class SecaoSite extends Model
{
    /** Definição de todas as secções (config/secoes.php). */
    public static function definicoes(): array
    {
        static $definicoes = null;
        if ($definicoes === null) {
            $definicoes = require CAMINHO_RAIZ . '/config/secoes.php';
        }
        return $definicoes;
    }

    public static function definicao(string $chave): ?array
    {
        return self::definicoes()[$chave] ?? null;
    }

    /**
     * Todas as secções prontas para o site: ['chave' => ['campos' => [...],
     * 'grupos' => ['grupo' => [itens activos]], 'visivel' => bool]].
     * Lido uma vez por pedido; sem BD (ou antes da instalação) usa o conteúdo inicial.
     */
    public static function publicas(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $linhas = [];
        $itens = [];
        try {
            $db = pdo();
            foreach ($db->query("SELECT chave, conteudo, visivel FROM secoes_site")->fetchAll() as $linha) {
                $linhas[$linha['chave']] = $linha;
            }
            foreach ($db->query("SELECT * FROM secao_itens WHERE ativo = 1 ORDER BY ordem ASC, id ASC")->fetchAll() as $item) {
                $itens[$item['secao']][$item['grupo']][] = (json_decode($item['dados'], true) ?: []) + ['id' => (int) $item['id']];
            }
        } catch (\Throwable $e) {
            // Tabelas ainda não criadas: o site mostra o conteúdo inicial.
        }

        $cache = [];
        foreach (self::definicoes() as $chave => $def) {
            $guardado = isset($linhas[$chave]) ? (json_decode($linhas[$chave]['conteudo'], true) ?: []) : [];
            $campos = [];
            foreach ($def['campos'] as $campo => $defCampo) {
                $campos[$campo] = array_key_exists($campo, $guardado) ? $guardado[$campo] : ($defCampo['padrao'] ?? '');
            }

            $grupos = [];
            foreach ($def['grupos'] ?? [] as $grupo => $defGrupo) {
                // Secção nunca gravada na BD → itens iniciais; depois disso, só o que está na BD.
                $grupos[$grupo] = isset($linhas[$chave]) ? ($itens[$chave][$grupo] ?? []) : ($defGrupo['padrao'] ?? []);
            }

            $cache[$chave] = [
                'campos'  => $campos,
                'grupos'  => $grupos,
                'visivel' => isset($linhas[$chave]) ? (bool) $linhas[$chave]['visivel'] : true,
            ];
        }
        return $cache;
    }

    // ---------------- Painel ----------------

    /** Valores actuais de uma secção (com o conteúdo inicial nos campos em falta). */
    public function obter(string $chave): array
    {
        $def = self::definicao($chave);
        $stmt = $this->db->prepare("SELECT conteudo, visivel FROM secoes_site WHERE chave = :chave");
        $stmt->execute(['chave' => $chave]);
        $linha = $stmt->fetch();
        $guardado = $linha ? (json_decode($linha['conteudo'], true) ?: []) : [];

        $campos = [];
        foreach ($def['campos'] as $campo => $defCampo) {
            $campos[$campo] = array_key_exists($campo, $guardado) ? $guardado[$campo] : ($defCampo['padrao'] ?? '');
        }
        return ['campos' => $campos, 'visivel' => $linha ? (bool) $linha['visivel'] : true, 'existe' => (bool) $linha];
    }

    /** Resumo de todas as secções para a listagem do painel. */
    public function resumo(): array
    {
        $estado = [];
        foreach ($this->db->query("SELECT chave, visivel, atualizado_em FROM secoes_site")->fetchAll() as $linha) {
            $estado[$linha['chave']] = $linha;
        }
        $contagens = [];
        foreach ($this->db->query("SELECT secao, COUNT(*) AS total FROM secao_itens GROUP BY secao")->fetchAll() as $linha) {
            $contagens[$linha['secao']] = (int) $linha['total'];
        }

        $resumo = [];
        foreach (self::definicoes() as $chave => $def) {
            $resumo[$chave] = $def + [
                'visivel'       => isset($estado[$chave]) ? (bool) $estado[$chave]['visivel'] : true,
                'atualizado_em' => $estado[$chave]['atualizado_em'] ?? null,
                'total_itens'   => $contagens[$chave] ?? 0,
            ];
        }
        return $resumo;
    }

    public function guardar(string $chave, array $campos): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO secoes_site (chave, conteudo) VALUES (:chave, :conteudo)
             ON DUPLICATE KEY UPDATE conteudo = VALUES(conteudo)"
        );
        $stmt->execute(['chave' => $chave, 'conteudo' => json_encode($campos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function definirVisivel(string $chave, int $visivel): void
    {
        $this->garantirLinha($chave);
        $stmt = $this->db->prepare("UPDATE secoes_site SET visivel = :visivel WHERE chave = :chave");
        $stmt->execute(['visivel' => $visivel, 'chave' => $chave]);
    }

    /** Cria a linha da secção (com os valores actuais) se ainda não existir. */
    private function garantirLinha(string $chave): void
    {
        $atual = $this->obter($chave);
        if (!$atual['existe']) {
            $this->guardar($chave, $atual['campos']);
        }
    }

    // ---------------- Itens ----------------

    public function itens(string $secao, string $grupo): array
    {
        $stmt = $this->db->prepare("SELECT * FROM secao_itens WHERE secao = :secao AND grupo = :grupo ORDER BY ordem ASC, id ASC");
        $stmt->execute(['secao' => $secao, 'grupo' => $grupo]);
        return array_map(function ($item) {
            $item['dados'] = json_decode($item['dados'], true) ?: [];
            return $item;
        }, $stmt->fetchAll());
    }

    public function itemPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM secao_itens WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        if (!$item) {
            return null;
        }
        $item['dados'] = json_decode($item['dados'], true) ?: [];
        return $item;
    }

    public function criarItem(string $secao, string $grupo, array $dados, int $ordem): int
    {
        $this->garantirLinha($secao);
        $stmt = $this->db->prepare("INSERT INTO secao_itens (secao, grupo, dados, ordem) VALUES (:secao, :grupo, :dados, :ordem)");
        $stmt->execute([
            'secao' => $secao,
            'grupo' => $grupo,
            'dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ordem' => $ordem,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizarItem(int $id, array $dados, int $ordem): void
    {
        $stmt = $this->db->prepare("UPDATE secao_itens SET dados = :dados, ordem = :ordem WHERE id = :id");
        $stmt->execute(['dados' => json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'ordem' => $ordem, 'id' => $id]);
    }

    public function estadoItem(int $id, int $ativo): void
    {
        $stmt = $this->db->prepare("UPDATE secao_itens SET ativo = :ativo WHERE id = :id");
        $stmt->execute(['ativo' => $ativo, 'id' => $id]);
    }

    public function removerItem(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM secao_itens WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /**
     * Grava o conteúdo inicial (config/secoes.php) das secções que ainda não
     * existem na BD — chamado pelo instalador. Nunca altera secções já gravadas.
     * Devolve o número de secções criadas.
     */
    public function semearIniciais(): int
    {
        $criadas = 0;
        foreach (self::definicoes() as $chave => $def) {
            if ($this->obter($chave)['existe']) {
                continue;
            }
            $campos = [];
            foreach ($def['campos'] as $campo => $defCampo) {
                $campos[$campo] = $defCampo['padrao'] ?? '';
            }
            $this->guardar($chave, $campos);
            foreach ($def['grupos'] ?? [] as $grupo => $defGrupo) {
                foreach ($defGrupo['padrao'] ?? [] as $i => $item) {
                    $this->criarItem($chave, $grupo, $item, $i + 1);
                }
            }
            $criadas++;
        }
        return $criadas;
    }
}
