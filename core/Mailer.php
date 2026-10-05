<?php

namespace Core;

// =====================================================================
// Core\Mailer — cliente SMTP mínimo (sem dependências/composer), usado
// para todas as confirmações por e-mail do site. Se MAIL_HOST não
// estiver configurado, ou o envio falhar por qualquer razão, regista em
// error_log() e devolve false — nunca deve interromper o fluxo do
// utilizador que despoletou o envio.
// =====================================================================

class Mailer
{
    public static function enviar(string $paraEmail, string $paraNome, string $assunto, string $corpoHtml): bool
    {
        if (!filter_var($paraEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!defined('MAIL_HOST') || MAIL_HOST === '') {
            error_log("Mailer: MAIL_HOST não configurado — email para {$paraEmail} não enviado (assunto: {$assunto}).");
            return false;
        }

        try {
            self::enviarSmtp($paraEmail, $paraNome, $assunto, $corpoHtml);
            return true;
        } catch (\Throwable $e) {
            error_log('Mailer: falha ao enviar email para ' . $paraEmail . ' — ' . $e->getMessage());
            return false;
        }
    }

    private static function enviarSmtp(string $paraEmail, string $paraNome, string $assunto, string $corpoHtml): void
    {
        $host         = MAIL_HOST;
        $porta        = defined('MAIL_PORT') && MAIL_PORT ? (int) MAIL_PORT : 587;
        $encriptacao  = defined('MAIL_ENCRIPTACAO') ? MAIL_ENCRIPTACAO : 'tls';
        $utilizador   = defined('MAIL_UTILIZADOR') ? MAIL_UTILIZADOR : '';
        $senha        = defined('MAIL_SENHA') ? MAIL_SENHA : '';
        $de           = (defined('MAIL_DE') && MAIL_DE) ? MAIL_DE : $utilizador;
        $deNome       = (defined('MAIL_DE_NOME') && MAIL_DE_NOME) ? MAIL_DE_NOME : NOME_SITE;
        $nomeAnfitriao = $_SERVER['SERVER_NAME'] ?? 'localhost';

        $prefixo = $encriptacao === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client(
            $prefixo . $host . ':' . $porta,
            $codigoErro,
            $mensagemErro,
            15,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new \RuntimeException("Não foi possível ligar a {$host}:{$porta} — {$mensagemErro}");
        }

        try {
            self::lerResposta($socket, 220);
            self::comando($socket, 'EHLO ' . $nomeAnfitriao, 250);

            if ($encriptacao === 'tls') {
                self::comando($socket, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('Falha ao activar STARTTLS.');
                }
                self::comando($socket, 'EHLO ' . $nomeAnfitriao, 250);
            }

            if ($utilizador !== '') {
                self::comando($socket, 'AUTH LOGIN', 334);
                self::comando($socket, base64_encode($utilizador), 334);
                self::comando($socket, base64_encode($senha), 235);
            }

            self::comando($socket, 'MAIL FROM:<' . $de . '>', 250);
            self::comando($socket, 'RCPT TO:<' . $paraEmail . '>', 250);
            self::comando($socket, 'DATA', 354);

            $mensagem = self::montarMensagem($paraEmail, $paraNome, $de, $deNome, $assunto, $corpoHtml);
            fwrite($socket, $mensagem);
            self::lerResposta($socket, 250);

            self::comando($socket, 'QUIT', 221);
        } finally {
            fclose($socket);
        }
    }

    private static function montarMensagem(string $paraEmail, string $paraNome, string $de, string $deNome, string $assunto, string $corpoHtml): string
    {
        $paraNome = self::limparCabecalho($paraNome);
        $deNome   = self::limparCabecalho($deNome);
        $assunto  = self::limparCabecalho($assunto);

        // O corpo em HTML costuma vir numa única linha muito longa (sem
        // quebras) — em "8bit"/texto puro, alguns relays de email quebram ou
        // corrompem linhas longas a meio de tags. Base64 evita esse problema
        // por completo (é seguro para qualquer conteúdo/comprimento) e
        // dispensa "dot-stuffing" (o alfabeto base64 nunca produz uma linha
        // a começar por ".").
        $corpoCodificado = chunk_split(base64_encode($corpoHtml));

        $cabecalhos = [
            'From: ' . $deNome . ' <' . $de . '>',
            'To: ' . $paraNome . ' <' . $paraEmail . '>',
            'Subject: =?UTF-8?B?' . base64_encode($assunto) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            'Date: ' . date('r'),
        ];

        return implode("\r\n", $cabecalhos) . "\r\n\r\n" . $corpoCodificado . ".\r\n";
    }

    private static function comando($socket, string $comando, int $codigoEsperado): string
    {
        fwrite($socket, $comando . "\r\n");
        return self::lerResposta($socket, $codigoEsperado);
    }

    private static function lerResposta($socket, int $codigoEsperado): string
    {
        $resposta = '';
        do {
            $linha = fgets($socket, 515);
            if ($linha === false) {
                break;
            }
            $resposta .= $linha;
        } while (isset($linha[3]) && $linha[3] === '-');

        $codigo = (int) substr($resposta, 0, 3);
        if ($codigo !== $codigoEsperado) {
            throw new \RuntimeException("Resposta SMTP inesperada (esperava {$codigoEsperado}): " . trim($resposta));
        }

        return $resposta;
    }

    private static function limparCabecalho(string $valor): string
    {
        return trim(str_replace(["\r", "\n"], '', $valor));
    }

    /**
     * Envelope HTML simples (logótipo + cores da marca), reutilizado por
     * todos os e-mails transaccionais do site.
     */
    public static function template(string $titulo, string $introHtml, ?array $cta = null): string
    {
        $nomeSite = defined('NOME_SITE') ? NOME_SITE : '';
        $logoUrl  = defined('URL_BASE') ? URL_BASE . '/assets/images/logo.png' : '';

        $botao = '';
        if ($cta) {
            $botao = '<tr><td style="padding:26px 0 4px">'
                . '<a href="' . htmlspecialchars($cta['url']) . '" style="display:inline-block;background:#0b1f33;color:#ffffff;'
                . 'text-decoration:none;font-weight:600;padding:12px 22px;border-radius:8px;font-size:14px">'
                . htmlspecialchars($cta['texto']) . '</a></td></tr>';
        }

        return '<!DOCTYPE html><html lang="pt-AO"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
            . '<body style="margin:0;padding:32px 16px;background:#f4f7fa;font-family:Arial,Helvetica,sans-serif;color:#0e1b2b">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8ef">'
            . '<tr><td style="background:#0b1f33;padding:20px 28px">'
            . ($logoUrl
                ? '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($nomeSite) . '" height="34" style="display:block;background:#fff;border-radius:6px;padding:4px 8px">'
                : '<strong style="color:#ffffff;font-size:16px">' . htmlspecialchars($nomeSite) . '</strong>')
            . '</td></tr>'
            . '<tr><td style="padding:28px">'
            . '<h1 style="font-size:19px;margin:0 0 14px;color:#0b1f33;font-family:Georgia,serif">' . htmlspecialchars($titulo) . '</h1>'
            . '<div style="font-size:14px;line-height:1.7;color:#314155">' . $introHtml . '</div>'
            . $botao
            . '</td></tr>'
            . '<tr><td style="padding:18px 28px;border-top:1px solid #e2e8ef;font-size:12px;color:#627285">'
            . htmlspecialchars($nomeSite) . ' — este é um email automático, não é necessário responder.'
            . '</td></tr>'
            . '</table></body></html>';
    }
}
