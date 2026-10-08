<?php

namespace Core;

// =====================================================================
// Core\Html — limpeza do HTML vindo do editor de artigos (Quill).
//
// Mesmo sendo escrito por utilizadores do painel, o conteúdo é mostrado
// a todos os visitantes do blog: passa sempre por uma lista branca de
// tags e atributos. Tudo o resto (scripts, estilos inline, eventos
// onclick, data: URIs, iframes de outros sites...) é removido.
// =====================================================================

class Html
{
    /** Tags permitidas => atributos permitidos em cada uma. */
    private const PERMITIDOS = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'pre' => [], 'code' => [],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt'],
        'iframe' => ['src'],
        'span' => [],
    ];

    /** Níveis mais restritos para os textos do site (editor simples do CMS). */
    private const NIVEIS = [
        'rico'  => ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a', 'ul', 'ol', 'li', 'h3', 'blockquote'],
        'curto' => ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a'],
        'lista' => ['ul', 'ol', 'li', 'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a'],
    ];

    /** Tags removidas juntamente com todo o conteúdo. */
    private const REMOVER_COM_CONTEUDO = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'svg', 'math', 'template'];

    /** Vídeos incorporados: só YouTube e Vimeo (o botão "vídeo" do editor). */
    private const IFRAME_PERMITIDO = '#^https://(www\.)?(youtube\.com|youtube-nocookie\.com)/embed/[A-Za-z0-9_\-]+|^https://player\.vimeo\.com/video/[0-9]+#';

    /**
     * Limpa HTML pela lista branca. $nivel: 'completo' (artigos do blog),
     * 'rico', 'curto' ou 'lista' (textos do site). Tags fora do nível são
     * removidas mas o texto delas mantém-se.
     */
    public static function limpar(string $html, string $nivel = 'completo'): string
    {
        $permitidos = isset(self::NIVEIS[$nivel])
            ? array_intersect_key(self::PERMITIDOS, array_flip(self::NIVEIS[$nivel]))
            : self::PERMITIDOS;

        $html = trim($html);
        if ($html === '' || $html === '<p><br></p>') {
            return '';
        }

        $documento = new \DOMDocument('1.0', 'UTF-8');
        $anterior = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="UTF-8"><div id="raiz">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);

        $raiz = $documento->getElementById('raiz');
        if (!$raiz) {
            return '';
        }

        self::limparNo($raiz, $permitidos);

        $saida = '';
        foreach (iterator_to_array($raiz->childNodes) as $filho) {
            $saida .= $documento->saveHTML($filho);
        }
        return trim($saida);
    }

    private static function limparNo(\DOMNode $no, array $permitidos): void
    {
        foreach (iterator_to_array($no->childNodes) as $filho) {
            if ($filho instanceof \DOMComment || $filho instanceof \DOMProcessingInstruction) {
                $no->removeChild($filho);
                continue;
            }
            if (!$filho instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($filho->nodeName);

            // Elementos de interface do Quill 2 (marcadores das listas) não fazem parte do conteúdo.
            if (in_array($tag, self::REMOVER_COM_CONTEUDO, true) || ($tag === 'span' && str_contains($filho->getAttribute('class'), 'ql-ui'))) {
                $no->removeChild($filho);
                continue;
            }

            if (!array_key_exists($tag, $permitidos)) {
                // Tag não permitida: mantém o texto, descarta a tag.
                self::limparNo($filho, $permitidos);
                while ($filho->firstChild) {
                    $no->insertBefore($filho->firstChild, $filho);
                }
                $no->removeChild($filho);
                continue;
            }

            // Quill 2 grava listas com marcadores como <ol><li data-list="bullet">.
            if ($tag === 'ol') {
                $primeiroLi = null;
                foreach ($filho->childNodes as $c) {
                    if ($c instanceof \DOMElement && strtolower($c->nodeName) === 'li') {
                        $primeiroLi = $c;
                        break;
                    }
                }
                if ($primeiroLi && $primeiroLi->getAttribute('data-list') === 'bullet') {
                    $filho = self::renomear($filho, 'ul');
                    $tag = 'ul';
                }
            }

            $classeAlinhamento = self::classeAlinhamento($filho->getAttribute('class'));
            self::limparAtributos($filho, $permitidos[$tag]);
            if ($classeAlinhamento !== '' && in_array($tag, ['p', 'h2', 'h3', 'h4', 'li', 'blockquote'], true)) {
                $filho->setAttribute('class', $classeAlinhamento);
            }

            if (!self::validarElemento($filho, $tag)) {
                $no->removeChild($filho);
                continue;
            }

            self::limparNo($filho, $permitidos);
        }
    }

    private static function limparAtributos(\DOMElement $elemento, array $permitidos): void
    {
        foreach (iterator_to_array($elemento->attributes) as $atributo) {
            if (!in_array(strtolower($atributo->nodeName), $permitidos, true)) {
                $elemento->removeAttribute($atributo->nodeName);
            }
        }
    }

    /** Valida URLs de links, imagens e vídeos. Devolve false para remover o elemento. */
    private static function validarElemento(\DOMElement $elemento, string $tag): bool
    {
        if ($tag === 'a') {
            $href = trim($elemento->getAttribute('href'));
            if ($href === '' || !self::urlSegura($href, true)) {
                $elemento->removeAttribute('href');
            }
            if (preg_match('#^https?://#i', $href)) {
                $elemento->setAttribute('target', '_blank');
                $elemento->setAttribute('rel', 'noopener nofollow');
            } else {
                $elemento->removeAttribute('target');
                $elemento->removeAttribute('rel');
            }
            return true;
        }

        if ($tag === 'img') {
            $src = trim($elemento->getAttribute('src'));
            if ($src === '' || !self::urlSegura($src, false)) {
                return false;
            }
            $elemento->setAttribute('loading', 'lazy');
            return true;
        }

        if ($tag === 'iframe') {
            $src = trim($elemento->getAttribute('src'));
            if (!preg_match(self::IFRAME_PERMITIDO, $src)) {
                return false;
            }
            $elemento->setAttribute('allowfullscreen', '');
            $elemento->setAttribute('loading', 'lazy');
            $elemento->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            return true;
        }

        return true;
    }

    /** http(s), caminhos relativos ao site e (só em links) mailto:/tel:. Nunca javascript: nem data:. */
    private static function urlSegura(string $url, bool $link): bool
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }
        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
        if ($link && preg_match('#^(mailto:|tel:)#i', $url)) {
            return true;
        }
        // Caminho interno ("/blog/..." ou "assets/uploads/..."), sem esquema nem "//outro-site".
        return (bool) preg_match('#^(/(?![/\\\\])|assets/|\#)#', $url) && !preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url);
    }

    private static function classeAlinhamento(string $classe): string
    {
        return preg_match('/\bql-align-(center|right|justify)\b/', $classe, $m) ? 'ql-align-' . $m[1] : '';
    }

    private static function renomear(\DOMElement $elemento, string $novaTag): \DOMElement
    {
        $novo = $elemento->ownerDocument->createElement($novaTag);
        while ($elemento->firstChild) {
            $novo->appendChild($elemento->firstChild);
        }
        $elemento->parentNode->replaceChild($novo, $elemento);
        return $novo;
    }

    /**
     * Texto de um campo "curto" para mostrar dentro de um parágrafo/título já
     * existente no layout: limpa e troca os parágrafos por quebras de linha.
     * Também aceita texto simples antigo (é escapado correctamente).
     */
    public static function inline(?string $html): string
    {
        $html = self::limpar((string) $html, 'curto');
        $html = preg_replace('#</p>\s*<p[^>]*>#i', '<br>', $html);
        $html = preg_replace('#</?p[^>]*>#i', '', $html);
        return trim(preg_replace('#(<br>\s*)+$#i', '', $html));
    }

    /** Campo "rico" (vários parágrafos/listas) já limpo, pronto a imprimir. */
    public static function bloco(?string $html): string
    {
        $html = trim((string) $html);
        if ($html !== '' && !preg_match('/^</', $html)) {
            $html = '<p>' . htmlspecialchars($html) . '</p>'; // texto simples antigo
        }
        return self::limpar($html, 'rico');
    }

    /**
     * Itens de um campo "lista" (cada marcador do editor), já limpos e em HTML
     * inline — para o layout desenhar cada item com o seu ícone. Sem lista,
     * usa cada parágrafo/linha como item.
     */
    public static function itensLista(?string $html): array
    {
        $html = self::limpar((string) $html, 'lista');
        if ($html === '') {
            return [];
        }
        if (preg_match_all('#<li[^>]*>(.*?)</li>#is', $html, $m)) {
            $itens = $m[1];
        } else {
            $itens = preg_split('#</p>\s*<p[^>]*>|<br\s*/?>|\R#i', $html);
        }
        $itens = array_map(fn ($i) => trim(preg_replace('#</?(p|ul|ol)[^>]*>#i', '', $i)), $itens);
        return array_values(array_filter($itens, fn ($i) => trim(strip_tags($i)) !== ''));
    }

    /** Texto simples (sem tags) — para resumos automáticos e tempo de leitura. */
    public static function texto(string $html): string
    {
        $texto = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $texto));
    }
}
