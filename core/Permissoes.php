<?php

namespace Core;

// =====================================================================
// Core\Permissoes — matriz de permissões por módulo × acção do painel
// administrativo. O tipo 'administrador' tem sempre acesso total; só o
// tipo 'funcionario' é limitado pela matriz gravada na tabela `permissoes`.
// =====================================================================

class Permissoes
{
    public const MODULOS = [
        'dashboard'     => 'Dashboard',
        'blog'          => 'Blog',
        'conteudo'      => 'Conteúdo do site',
        'utilizadores'  => 'Utilizadores',
        'definicoes'    => 'Definições',
    ];

    public const ACOES = [
        'ver'      => 'Ver',
        'criar'    => 'Criar',
        'editar'   => 'Editar',
        'eliminar' => 'Eliminar',
    ];

    private const CHAVE_SESSAO = 'permissoes';

    /**
     * Página de entrada de cada módulo no painel — usada para levar o
     * funcionário à primeira área a que tem acesso.
     */
    public const PAGINAS = [
        'dashboard'    => '/admin',
        'blog'         => '/admin/blog',
        'conteudo'     => '/admin/conteudo/secoes',
        'utilizadores' => '/admin/utilizadores',
        'definicoes'   => '/admin/definicoes',
    ];

    public static function tem(string $modulo, string $acao = 'ver'): bool
    {
        if (Auth::ehTipo('administrador')) {
            return true;
        }
        if (!Auth::autenticado()) {
            return false;
        }

        // Relê a matriz uma vez por pedido: permissões alteradas pelo admin
        // aplicam-se logo, sem o funcionário ter de sair e voltar a entrar.
        static $recarregado = false;
        if (!$recarregado && Auth::ehTipo('funcionario')) {
            $recarregado = true;
            try {
                self::carregarNaSessao((int) Auth::id());
            } catch (\Throwable $e) {
                // Sem BD, fica a matriz carregada no login.
            }
        }

        $detalhe = $_SESSION[self::CHAVE_SESSAO][$modulo] ?? null;
        return !empty($detalhe[$acao]);
    }

    /**
     * Primeira página do painel que o utilizador pode ver, ou null se nenhuma.
     */
    public static function primeiraPagina(): ?string
    {
        foreach (self::PAGINAS as $modulo => $rota) {
            if (self::tem($modulo, 'ver')) {
                return $rota;
            }
        }
        return null;
    }

    /**
     * Carrega a matriz de permissões do utilizador para a sessão — chamado no login.
     */
    public static function carregarNaSessao(int $utilizadorId): void
    {
        $linhas = pdo()->prepare("SELECT modulo, ver, criar, editar, eliminar FROM permissoes WHERE utilizador_id = :id");
        $linhas->execute(['id' => $utilizadorId]);

        $mapa = [];
        foreach ($linhas->fetchAll() as $linha) {
            $mapa[$linha['modulo']] = [
                'ver'      => (bool) $linha['ver'],
                'criar'    => (bool) $linha['criar'],
                'editar'   => (bool) $linha['editar'],
                'eliminar' => (bool) $linha['eliminar'],
            ];
        }

        $_SESSION[self::CHAVE_SESSAO] = $mapa;
    }

    /**
     * Devolve a matriz completa (todos os módulos, com zeros por omissão)
     * de um utilizador — usado para pré-preencher o formulário no admin.
     */
    public static function matrizDoUtilizador(int $utilizadorId): array
    {
        $linhas = pdo()->prepare("SELECT modulo, ver, criar, editar, eliminar FROM permissoes WHERE utilizador_id = :id");
        $linhas->execute(['id' => $utilizadorId]);

        $guardadas = [];
        foreach ($linhas->fetchAll() as $linha) {
            $guardadas[$linha['modulo']] = $linha;
        }

        $matriz = [];
        foreach (self::MODULOS as $chave => $rotulo) {
            $matriz[$chave] = [
                'ver'      => (bool) ($guardadas[$chave]['ver'] ?? false),
                'criar'    => (bool) ($guardadas[$chave]['criar'] ?? false),
                'editar'   => (bool) ($guardadas[$chave]['editar'] ?? false),
                'eliminar' => (bool) ($guardadas[$chave]['eliminar'] ?? false),
            ];
        }

        return $matriz;
    }

    /**
     * Grava a matriz de permissões de um utilizador (substitui a anterior).
     * $permissoesPost tem o formato de $_POST['perm'][modulo][acao] = '1'.
     */
    public static function guardar(int $utilizadorId, array $permissoesPost): void
    {
        $db = pdo();
        $db->prepare("DELETE FROM permissoes WHERE utilizador_id = :id")->execute(['id' => $utilizadorId]);

        $stmt = $db->prepare(
            "INSERT INTO permissoes (utilizador_id, modulo, ver, criar, editar, eliminar)
             VALUES (:uid, :modulo, :ver, :criar, :editar, :eliminar)"
        );

        foreach (array_keys(self::MODULOS) as $modulo) {
            $linha = $permissoesPost[$modulo] ?? [];
            $temAlgo = !empty($linha['ver']) || !empty($linha['criar']) || !empty($linha['editar']) || !empty($linha['eliminar']);
            if (!$temAlgo) {
                continue;
            }
            $stmt->execute([
                'uid'      => $utilizadorId,
                'modulo'   => $modulo,
                'ver'      => !empty($linha['ver']) ? 1 : 0,
                'criar'    => !empty($linha['criar']) ? 1 : 0,
                'editar'   => !empty($linha['editar']) ? 1 : 0,
                'eliminar' => !empty($linha['eliminar']) ? 1 : 0,
            ]);
        }
    }
}
