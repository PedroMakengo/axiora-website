<?php

namespace Core;

// =====================================================================
// Core\RateLimiter — limite de pedidos por janela de tempo, gravado em
// ficheiros (storage/ratelimit), sem tabelas nem extensões extra — funciona
// em qualquer alojamento partilhado. Usado contra força bruta (login,
// recuperação de senha) e abuso de formulários públicos / assistente IA.
// =====================================================================

class RateLimiter
{
    private static function pasta(): string
    {
        $pasta = CAMINHO_RAIZ . '/storage/ratelimit';
        if (!is_dir($pasta)) {
            @mkdir($pasta, 0700, true);
        }
        return $pasta;
    }

    private static function ficheiro(string $chave): string
    {
        return self::pasta() . '/' . hash('sha256', $chave) . '.json';
    }

    /**
     * Lê as tentativas ainda dentro da janela, aplica $alterar e grava — tudo
     * sob bloqueio exclusivo, para pedidos em paralelo não se anularem.
     */
    private static function comBloqueio(string $chave, int $janela, callable $alterar): array
    {
        $fp = @fopen(self::ficheiro($chave), 'c+');
        if (!$fp) {
            return [];
        }

        flock($fp, LOCK_EX);
        $conteudo = stream_get_contents($fp);
        $tentativas = json_decode($conteudo ?: '[]', true);
        if (!is_array($tentativas)) {
            $tentativas = [];
        }

        $limite = time() - $janela;
        $tentativas = array_values(array_filter($tentativas, fn ($t) => is_int($t) && $t > $limite));
        $tentativas = $alterar($tentativas);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($tentativas));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $tentativas;
    }

    /** Número de tentativas registadas na janela. */
    public static function tentativas(string $chave, int $janela): int
    {
        return count(self::comBloqueio($chave, $janela, fn ($t) => $t));
    }

    /** Já atingiu o limite? (não regista nova tentativa) */
    public static function excedido(string $chave, int $maximo, int $janela): bool
    {
        return self::tentativas($chave, $janela) >= $maximo;
    }

    /** Regista uma tentativa. */
    public static function registar(string $chave, int $janela): void
    {
        self::comBloqueio($chave, $janela, function ($t) {
            $t[] = time();
            return $t;
        });
    }

    /**
     * Regista e devolve true se o pedido ainda está dentro do limite.
     * Uso típico para formulários: if (!RateLimiter::permitir(...)) → 429.
     */
    public static function permitir(string $chave, int $maximo, int $janela): bool
    {
        $permitido = false;
        self::comBloqueio($chave, $janela, function ($t) use ($maximo, &$permitido) {
            if (count($t) < $maximo) {
                $t[] = time();
                $permitido = true;
            }
            return $t;
        });
        return $permitido;
    }

    /** Segundos até a tentativa mais antiga sair da janela. */
    public static function segundosAteLibertar(string $chave, int $janela): int
    {
        $t = self::comBloqueio($chave, $janela, fn ($t) => $t);
        if (empty($t)) {
            return 0;
        }
        return max(1, (min($t) + $janela) - time());
    }

    /** Limpa as tentativas (ex.: após login com sucesso). */
    public static function limpar(string $chave): void
    {
        @unlink(self::ficheiro($chave));
    }

    /** IP do cliente (REMOTE_ADDR — não confia em cabeçalhos falsificáveis). */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Limpeza ocasional de ficheiros antigos (1 em cada 200 pedidos). */
    public static function recolherLixo(int $idadeMaxima = 86400): void
    {
        if (random_int(1, 200) !== 1) {
            return;
        }
        foreach (glob(self::pasta() . '/*.json') ?: [] as $f) {
            if (@filemtime($f) < time() - $idadeMaxima) {
                @unlink($f);
            }
        }
    }
}
