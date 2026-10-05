<?php

namespace App\Models;

use Core\Model;

class ConfiguracaoSite extends Model
{
    /** Redes sociais geríveis nas Definições, pela ordem em que aparecem no site: chave => [nome, ícone MDI] */
    public const REDES = [
        'facebook'  => ['Facebook', 'mdi-facebook'],
        'instagram' => ['Instagram', 'mdi-instagram'],
        'linkedin'  => ['LinkedIn', 'mdi-linkedin'],
        'tiktok'    => ['TikTok', 'mdi-music-note'],
    ];

    /** Valores usados se a BD ainda não tiver a chave (ex.: antes da instalação). */
    private const OMISSAO = [
        'telefone'          => '+244 938 070 748',
        'whatsapp'          => '244938070748',
        'mensagem_whatsapp' => 'Olá Axiora! Gostaria de ser atendido.',
        'email'             => 'geral@axiora.site',
        'endereco'          => 'São Paulo, edifício perto das bombas da Pumangol — Luanda, Angola',
        'endereco_curto'    => 'São Paulo, Luanda',
        'horario'           => 'Seg–Sex 08h–18h · Sáb 09h–13h',
        'horario_completo'  => 'Seg–Sex: 08h00–18h00 · Sábado: 09h00–13h00',
        'mapa_embed'        => '',
    ];

    /** Definições para o site público (cabeçalho, rodapé, contacto), lidas uma só vez por pedido */
    public static function publicas(): array
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = array_merge(self::OMISSAO, array_filter((new self())->todas(), fn ($v) => $v !== null));
            } catch (\Throwable $e) {
                $cache = self::OMISSAO;
            }
        }
        return $cache;
    }

    /** Link wa.me com mensagem pré-preenchida (ou a mensagem geral das Definições). */
    public static function linkWhatsapp(?string $mensagem = null): string
    {
        $config = self::publicas();
        $numero = preg_replace('/\D+/', '', (string) ($config['whatsapp'] ?? ''));
        $texto = trim((string) ($mensagem ?: ($config['mensagem_whatsapp'] ?? '')));
        return 'https://wa.me/' . $numero . ($texto !== '' ? '?text=' . rawurlencode($texto) : '');
    }

    /** Telefone só com dígitos e "+", para links tel:. */
    public static function telefoneLink(): string
    {
        return preg_replace('/[^\d+]/', '', (string) (self::publicas()['telefone'] ?? ''));
    }

    public function todas(): array
    {
        $linhas = $this->db->query("SELECT chave, valor FROM configuracoes_site")->fetchAll();
        $mapa = [];
        foreach ($linhas as $linha) {
            $mapa[$linha['chave']] = $linha['valor'];
        }
        return $mapa;
    }

    public function guardar(array $dados): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO configuracoes_site (chave, valor) VALUES (:chave, :valor)
             ON DUPLICATE KEY UPDATE valor = :valor2"
        );
        foreach ($dados as $chave => $valor) {
            $stmt->execute(['chave' => $chave, 'valor' => $valor, 'valor2' => $valor]);
        }
    }
}
