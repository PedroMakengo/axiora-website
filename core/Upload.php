<?php

namespace Core;

// =====================================================================
// Core\Upload — validação e gravação de imagens enviadas por formulário
// (capas de artigos, slides do hero, serviços, avatares, etc.).
// =====================================================================

class Upload
{
    private const TIPOS_PERMITIDOS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const TAMANHO_MAXIMO = 4 * 1024 * 1024; // 4MB

    /** Tipo MIME detectado pelo conteúdo (nunca o enviado pelo browser, que é falsificável). */
    private static function tipoReal(array $ficheiro): string
    {
        if (!is_uploaded_file($ficheiro['tmp_name'] ?? '')) {
            throw new \RuntimeException('Ficheiro inválido.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return (string) $finfo->file($ficheiro['tmp_name']);
    }

    /** Confirma que é mesmo uma imagem descodificável do tipo indicado, com dimensões razoáveis. */
    private static function imagemValida(string $caminho, string $tipo): bool
    {
        $info = @getimagesize($caminho);
        if (!$info || ($info['mime'] ?? '') !== $tipo) {
            return false;
        }
        [$largura, $altura] = $info;
        return $largura > 0 && $altura > 0 && $largura <= 8000 && $altura <= 8000;
    }

    /** Só letras, números, hífen e barra — nunca ".." nem caminhos absolutos. */
    private static function pastaSegura(string $pasta): string
    {
        $pasta = trim($pasta, '/');
        if ($pasta === '' || !preg_match('#^[a-z0-9\-]+(/[a-z0-9\-]+)*$#i', $pasta)) {
            throw new \RuntimeException('Destino de upload inválido.');
        }
        return $pasta;
    }

    /**
     * Valida e move um ficheiro de $_FILES para assets/uploads/{pasta}, devolvendo
     * o caminho relativo (ex: "assets/uploads/blog/abc123.jpg") ou null se não
     * foi enviado nenhum ficheiro. Lança uma exception com mensagem amigável em
     * caso de erro de validação — o controlador deve apanhar e devolver ao formulário.
     */
    public static function imagem(?array $ficheiro, string $pasta): ?string
    {
        if (empty($ficheiro) || $ficheiro['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($ficheiro['error'] === UPLOAD_ERR_INI_SIZE || $ficheiro['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new \RuntimeException('A imagem não pode exceder 4MB.');
        }
        if ($ficheiro['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Não foi possível receber o ficheiro. Tente novamente.');
        }

        if ($ficheiro['size'] > self::TAMANHO_MAXIMO) {
            throw new \RuntimeException('A imagem não pode exceder 4MB.');
        }

        $tipoReal = self::tipoReal($ficheiro);
        if (!isset(self::TIPOS_PERMITIDOS[$tipoReal]) || !self::imagemValida($ficheiro['tmp_name'], $tipoReal)) {
            throw new \RuntimeException('Formato inválido — envie uma imagem JPG, PNG ou WEBP.');
        }

        $extensao = self::TIPOS_PERMITIDOS[$tipoReal];
        $nomeFicheiro = bin2hex(random_bytes(12)) . '.' . $extensao;

        $pastaDestino = CAMINHO_RAIZ . '/assets/uploads/' . self::pastaSegura($pasta);
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        $caminhoCompleto = $pastaDestino . '/' . $nomeFicheiro;
        if (!move_uploaded_file($ficheiro['tmp_name'], $caminhoCompleto)) {
            throw new \RuntimeException('Não foi possível guardar a imagem no servidor.');
        }

        return 'assets/uploads/' . self::pastaSegura($pasta) . '/' . $nomeFicheiro;
    }

    /**
     * Remove um ficheiro previamente enviado (usado ao substituir uma imagem antiga).
     * Só apaga caminhos dentro de assets/uploads — nunca imagens fixas do tema
     * (ex.: assets/images/hero/...), usadas pelos conteúdos iniciais.
     */
    public static function remover(?string $caminhoRelativo): void
    {
        if (!$caminhoRelativo || str_contains($caminhoRelativo, '..') || !str_starts_with($caminhoRelativo, 'assets/uploads/')) {
            return;
        }
        $caminhoCompleto = CAMINHO_RAIZ . '/' . $caminhoRelativo;
        if (is_file($caminhoCompleto)) {
            @unlink($caminhoCompleto);
        }
    }
}
